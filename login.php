<?php
session_start();

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'portal_web';
$username = 'root';
$password = '';

// Credenciais padrão (em produção, use banco de dados)
$default_users = [
    'admin@portal.com' => ['password' => 'admin123', 'name' => 'Administrador'],
    'user@portal.com' => ['password' => 'user123', 'name' => 'Usuário']
];

$error_message = '';
$success_message = '';

// Processar login
if ($_POST && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (isset($default_users[$email]) && $default_users[$email]['password'] === $password) {
        $_SESSION['user_id'] = $email;
        $_SESSION['user_name'] = $default_users[$email]['name'];
        $_SESSION['logged_in'] = true;
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = 'E-mail ou senha inválidos!';
    }
}

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    $success_message = 'Logout realizado com sucesso!';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Web</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-top: 1rem;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #764ba2;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef5350;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            text-align: left;
            font-size: 0.9rem;
            color: #666;
        }
        
        .demo-credentials h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2 class="login-title">Portal Web Login</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Digite seu e-mail" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Digite sua senha">
                </div>
                
                <button type="submit" name="login" class="login-btn">Entrar</button>
            </form>
            
            <div class="demo-credentials">
                <h4>Credenciais de Teste:</h4>
                <p><strong>Admin:</strong> admin@portal.com / admin123</p>
                <p><strong>Usuário:</strong> user@portal.com / user123</p>
            </div>
            
            <a href="index.html" class="back-link">← Voltar ao Site</a>
        </div>
    </div>
    
    <script>
        // Auto-fill demo credentials
        document.addEventListener('DOMContentLoaded', function() {
            const demoCredentials = document.querySelector('.demo-credentials');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            demoCredentials.addEventListener('click', function(e) {
                if (e.target.tagName === 'P') {
                    const text = e.target.textContent;
                    if (text.includes('admin@portal.com')) {
                        emailInput.value = 'admin@portal.com';
                        passwordInput.value = 'admin123';
                    } else if (text.includes('user@portal.com')) {
                        emailInput.value = 'user@portal.com';
                        passwordInput.value = 'user123';
                    }
                }
            });
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Por favor, insira um e-mail válido!');
                return false;
            }
        });
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>
</html>
