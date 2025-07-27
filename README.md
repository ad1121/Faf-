# Portal Web - Sistema Completo

Um portal web moderno e responsivo desenvolvido com HTML5, CSS3, JavaScript e PHP, oferecendo uma solução completa para gestão online.

## 🚀 Características

- **Design Moderno**: Interface responsiva com animações e efeitos visuais
- **Sistema de Login**: Autenticação segura com sessões PHP
- **Dashboard Interativo**: Painel administrativo com estatísticas em tempo real
- **Formulário de Contato**: Sistema completo de mensagens com validação
- **Banco de Dados**: Estrutura MySQL/MariaDB para persistência de dados
- **Segurança**: Proteção CSRF, sanitização de dados e logs de atividade
- **Mobile First**: Totalmente responsivo para todos os dispositivos

## 📋 Pré-requisitos

### Servidor Web
- **Apache** 2.4+ ou **Nginx** 1.18+
- **PHP** 7.4+ (recomendado PHP 8.0+)
- **MySQL** 5.7+ ou **MariaDB** 10.3+

### Extensões PHP Necessárias
- `mysqli` ou `pdo_mysql`
- `session`
- `json`
- `fileinfo`
- `mbstring`

## 🛠️ Instalação

### 1. Download e Extração
```bash
# Baixe o arquivo compactado e extraia no diretório do servidor web
# Por exemplo, no XAMPP: C:\xampp\htdocs\portal
# No WAMP: C:\wamp64\www\portal
# No Linux: /var/www/html/portal
```

### 2. Configuração do Banco de Dados

#### Opção A: Configuração Automática
1. Acesse o portal pelo navegador
2. O sistema criará automaticamente as tabelas necessárias
3. Usuários padrão serão criados automaticamente:
   - **Admin**: admin@portal.com / admin123
   - **Usuário**: user@portal.com / user123

#### Opção B: Configuração Manual
```sql
-- Criar banco de dados
CREATE DATABASE portal_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar o banco criado
USE portal_web;

-- As tabelas serão criadas automaticamente pelo sistema
```

### 3. Configuração do Servidor

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^?]*) index.php [NC,L,QSA]

# Configurações de segurança
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.json">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.log">
    Order Allow,Deny
    Deny from all
</Files>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/html/portal;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Proteger arquivos sensíveis
    location ~ \.(json|log|txt)$ {
        deny all;
    }
}
```

### 4. Configurações do PHP

#### Editar config.php
```php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_web');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

// Configurações de E-mail
define('MAIL_FROM', 'seu-email@dominio.