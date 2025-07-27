<?php
header('Content-Type: application/json');

// Configurações de e-mail
$to_email = "contato@portalweb.com"; // Altere para seu e-mail
$from_email = "noreply@portalweb.com";

// Função para sanitizar dados
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para validar e-mail
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para registrar log
function log_message($message) {
    $log_file = 'contact_log.txt';
    $current_time = date('Y-m-d H:i:s');
    $log_entry = "[{$current_time}] {$message}" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Capturar e sanitizar dados do formulário
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');
        
        // Validações
        $errors = array();
        
        if (empty($name)) {
            $errors[] = "Nome é obrigatório";
        } elseif (strlen($name) < 2) {
            $errors[] = "Nome deve ter pelo menos 2 caracteres";
        }
        
        if (empty($email)) {
            $errors[] = "E-mail é obrigatório";
        } elseif (!validate_email($email)) {
            $errors[] = "E-mail inválido";
        }
        
        if (empty($subject)) {
            $errors[] = "Assunto é obrigatório";
        } elseif (strlen($subject) < 5) {
            $errors[] = "Assunto deve ter pelo menos 5 caracteres";
        }
        
        if (empty($message)) {
            $errors[] = "Mensagem é obrigatória";
        } elseif (strlen($message) < 10) {
            $errors[] = "Mensagem deve ter pelo menos 10 caracteres";
        }
        
        // Se há erros, retornar erro
        if (!empty($errors)) {
            $response = array(
                'success' => false,
                'message' => 'Erro de validação: ' . implode(', ', $errors)
            );
        } else {
            // Preparar e-mail
            $email_subject = "Contato do Site: " . $subject;
            $email_body = "
            Nova mensagem recebida do formulário de contato:
            
            Nome: {$name}
            E-mail: {$email}
            Assunto: {$subject}
            
            Mensagem:
            {$message}
            
            ---
            Enviado em: " . date('d/m/Y H:i:s') . "
            IP do remetente: " . $_SERVER['REMOTE_ADDR'] . "
            ";
            
            $headers = array(
                'From' => $from_email,
                'Reply-To' => $email,
                'X-Mailer' => 'PHP/' . phpversion(),
                'Content-Type' => 'text/plain; charset=UTF-8'
            );
            
            $headers_string = '';
            foreach ($headers as $key => $value) {
                $headers_string .= $key . ': ' . $value . "\r\n";
            }
            
            // Tentar enviar e-mail
            if (mail($to_email, $email_subject, $email_body, $headers_string)) {
                // Salvar mensagem em arquivo (backup)
                $backup_data = array(
                    'timestamp' => date('Y-m-d H:i:s'),
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                
                $backup_file = 'messages_backup.json';
                $existing_data = array();
                
                if (file_exists($backup_file)) {
                    $existing_content = file_get_contents($backup_file);
                    $existing_data = json_decode($existing_content, true) ?: array();
                }
                
                $existing_data[] = $backup_data;
                file_put_contents($backup_file, json_encode($existing_data, JSON_PRETTY_PRINT));
                
                log_message("Mensagem enviada com sucesso de: {$email}");
                
                $response = array(
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
                );
            } else {
                log_message("Erro ao enviar e-mail de: {$email}");
                
                $response = array(
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem. Tente novamente mais tarde.'
                );
            }
        }
        
    } catch (Exception $e) {
        log_message("Exceção capturada: " . $e->getMessage());
        
        $response = array(
            'success' => false,
            'message' => 'Erro interno do servidor. Tente novamente mais tarde.'
        );
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'Método de requisição inválido.'
    );
}

// Se a requisição é AJAX, retornar JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo json_encode($response);
    exit;
}

// Se não é AJAX, redirecionar com mensagem
if ($response['success']) {
    header('Location: index.html?message=success');
} else {
    header('Location: index.html?message=error&details=' . urlencode($response['message']));
}
exit;
?>

<?php
// Página de administração para visualizar mensagens (opcional)
if (isset($_GET['admin']) && $_GET['admin'] === 'messages') {
    // Verificar se é admin (implementar autenticação adequada)
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_id'] !== 'admin@portal.com') {
        header('HTTP/1.0 403 Forbidden');
        exit('Acesso negado');
    }
    
    $backup_file = 'messages_backup.json';
    $messages = array();
    
    if (file_exists($backup_file)) {
        $content = file_get_contents($backup_file);
        $messages = json_decode($content, true) ?: array();
        $messages = array_reverse($messages); // Mais recentes primeiro
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mensagens Recebidas - Admin</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .admin-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
                background: #f8f9fa;
                min-height: 100vh;
            }
            
            .admin-header {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .message-card {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #667eea;
            }
            
            .message-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .message-meta {
                font-size: 0.9rem;
                color: #666;
            }
            
            .message-content {
                line-height: 1.6;
            }
            
            .back-link {
                display: inline-block;
                background: #667eea;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-bottom: 1rem;
            }
            
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }
            
            .stat-box {
                background: white;
                padding: 1.5rem;
                border-radius: 10px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <div class="admin-header">
                <a href="dashboard.php" class="back-link">← Voltar ao Dashboard</a>
                <h1>Mensagens Recebidas</h1>
                
                <div class="stats">
                    <div class="stat-box">
                        <h3><?php echo count($messages); ?></h3>
                        <p>Total de Mensagens</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo count(array_filter($messages, function($m) { return strtotime($m['timestamp']) > strtotime('-7 days'); })); ?></h3>
                        <p>Últimos 7 dias</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo count(array_unique(array_column($messages, 'email'))); ?></h3>
                        <p>E-mails únicos</p>
                    </div>
                </div>
            </div>
            
            <?php if (empty($messages)): ?>
                <div class="message-card">
                    <p>Nenhuma mensagem recebida ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="message-card">
                    <div class="message-header">
                        <strong><?php echo htmlspecialchars($msg['subject']); ?></strong>
                        <span class="message-meta"><?php echo date('d/m/Y H:i', strtotime($msg['timestamp'])); ?></span>
                    </div>
                    <div class="message-meta" style="margin-bottom: 1rem;">
                        <strong>De:</strong> <?php echo htmlspecialchars($msg['name']); ?> 
                        &lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;
                        <br><strong>IP:</strong> <?php echo htmlspecialchars($msg['ip']); ?>
                    </div>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>