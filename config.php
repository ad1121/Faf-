<?php
// Configurações do Portal Web

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_web');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações de E-mail
define('MAIL_HOST', 'localhost');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'contato@portalweb.com');
define('MAIL_PASSWORD', '');
define('MAIL_FROM', 'contato@portalweb.com');
define('MAIL_FROM_NAME', 'Portal Web');

// Configurações do Site
define('SITE_NAME', 'Portal Web');
define('SITE_URL', 'http://localhost/portal');
define('SITE_DESCRIPTION', 'Sua solução completa para gestão online');

// Configurações de Segurança
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos

// Configurações de Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', 'uploads/');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Função para conectar ao banco de dados
function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em produção, registre o erro e mostre uma mensagem genérica
            error_log("Erro de conexão com o banco: " . $e->getMessage());
            die("Erro de conexão com o banco de dados.");
        }
    }
    
    return $pdo;
}

// Função para criar as tabelas necessárias
function createTables() {
    $pdo = getConnection();
    
    // Tabela de usuários
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Tabela de mensagens de contato
    $sql_messages = "
    CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(45),
        read_status BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Tabela de logs do sistema
    $sql_logs = "
    CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    // Tabela de configurações
    $sql_settings = "
    CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    try {
        $pdo->exec($sql_users);
        $pdo->exec($sql_messages);
        $pdo->exec($sql_logs);
        $pdo->exec($sql_settings);
        
        // Inserir usuário admin padrão se não existir
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['admin@portal.com']);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                'Administrador',
                'admin@portal.com',
                password_hash('admin123', PASSWORD_DEFAULT),
                'admin'
            ]);
        }
        
        // Inserir usuário comum padrão se não existir
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['user@portal.com']);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                'Usuário Teste',
                'user@portal.com',
                password_hash('user123', PASSWORD_DEFAULT),
                'user'
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao criar tabelas: " . $e->getMessage());
        return false;
    }
}

// Função para registrar log do sistema
function logActivity($user_id, $action, $description = '', $ip_address = null) {
    try {
        $pdo = getConnection();
        $ip_address = $ip_address ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $description, $ip_address]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
        return false;
    }
}

// Função para sanitizar dados
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

// Função para validar e-mail
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para gerar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para validar token CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Função para verificar se o usuário é admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Função para redirecionar
function redirect($url, $permanent = false) {
    $status_code = $permanent ? 301 : 302;
    header("Location: $url", true, $status_code);
    exit();
}

// Função para formatar data em português
function formatDate($date, $format = 'd/m/Y H:i') {
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

// Função para upload de arquivo
function uploadFile($file, $destination_path = null) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Parâmetros de upload inválidos.');
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('Nenhum arquivo foi enviado.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Arquivo muito grande.');
        default:
            throw new RuntimeException('Erro desconhecido no upload.');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Arquivo excede o tamanho máximo permitido.');
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), ALLOWED_EXTENSIONS)) {
        throw new RuntimeException('Tipo de arquivo não permitido.');
    }
    
    $destination_path = $destination_path ?: UPLOAD_PATH;
    if (!is_dir($destination_path)) {
        mkdir($destination_path, 0755, true);
    }
    
    $filename = uniqid() . '.' . $extension;
    $full_path = $destination_path . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $full_path)) {
        throw new RuntimeException('Falha ao mover o arquivo.');
    }
    
    return [
        'filename' => $filename,
        'path' => $full_path,
        'original_name' => $file['name'],
        'size' => $file['size'],
        'mime_type' => $mime_type
    ];
}

// Função para enviar e-mail
function sendEmail($to, $subject, $message, $from = null) {
    $from = $from ?: MAIL_FROM;
    
    $headers = [
        'From: ' . MAIL_FROM_NAME . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// Inicializar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar timeout da sessão
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Criar tabelas na primeira execução
if (!file_exists('.installed')) {
    if (createTables()) {
        file_put_contents('.installed', date('Y-m-d H:i:s'));
    }
}
?>