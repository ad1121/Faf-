<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Usuário';
$user_email = $_SESSION['user_id'] ?? '';

// Dados simulados do dashboard
$stats = [
    'total_visits' => 12453,
    'active_users' => 234,
    'total_projects' => 45,
    'messages' => 18
];

$recent_activities = [
    ['action' => 'Novo usuário cadastrado', 'time' => '2 minutos atrás', 'type' => 'user'],
    ['action' => 'Projeto atualizado', 'time' => '15 minutos atrás', 'type' => 'project'],
    ['action' => 'Mensagem recebida', 'time' => '1 hora atrás', 'type' => 'message'],
    ['action' => 'Backup realizado', 'time' => '3 horas atrás', 'type' => 'system'],
    ['action' => 'Nova funcionalidade adicionada', 'time' => '1 dia atrás', 'type' => 'feature']
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portal Web</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background: #f8f9fa;
            padding-top: 80px;
        }
        
        .dashboard-header {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-title {
            font-size: 2rem;
            color: #333;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        
        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .main-content,
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .content-header {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
        }
        
        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .recent-activities {
            list-style: none;
            padding: 0;
        }
        
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background 0.3s ease;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .activity-icon.user { background: #28a745; }
        .activity-icon.project { background: #007bff; }
        .activity-icon.message { background: #ffc107; color: #333; }
        .activity-icon.system { background: #6c757d; }
        .activity-icon.feature { background: #17a2b8; }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-action {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .quick-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .quick-action:hover {
            transform: translateY(-3px);
            color: white;
        }
        
        .quick-action-icon {
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
            
            .dashboard-nav {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <h1 class="dashboard-title">Dashboard</h1>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <div>
                            <div><strong><?php echo htmlspecialchars($user_name); ?></strong></div>
                            <div style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars($user_email); ?></div>
                        </div>
                        <a href="login.php?logout=1" class="logout-btn">Sair</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-icon">👥</span>
                    <div class="stat-number" data-target="<?php echo $stats['total_visits']; ?>">0</div>
                    <div class="stat-label">Total de Visitas</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">🟢</span>
                    <div class="stat-number" data-target="<?php echo $stats['active_users']; ?>">0</div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">📁</span>
                    <div class="stat-number" data-target="<?php echo $stats['total_projects']; ?>">0</div>
                    <div class="stat-label">Projetos</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">💬</span>
                    <div class="stat-number" data-target="<?php echo $stats['messages']; ?>">0</div>
                    <div class="stat-label">Mensagens</div>
                </div>
            </div>
            
            <!-- Main Dashboard Content -->
            <div class="dashboard-content">
                <div class="main-content">
                    <h2 class="content-header">Análise de Desempenho</h2>
                    <div class="chart-placeholder">
                        Gráfico de Analytics (Integração com Chart.js)
                    </div>
                    
                    <div class="quick-actions">
                        <a href="#" class="quick-action">
                            <span class="quick-action-icon">➕</span>
                            Novo Projeto
                        </a>
                        <a href="#" class="quick-action">
                            <span class="quick-action-icon">📊</span>
                            Relatórios
                        </a>
                        <a href="#" class="quick-action">
                            <span class="quick-action-icon">⚙️</span>
                            Configurações
                        </a>
                        <a href="index.html" class="quick-action">
                            <span class="quick-action-icon">🏠</span>
                            Site Principal
                        </a>
                    </div>
                </div>
                
                <div class="sidebar">
                    <h3 class="content-header">Atividades Recentes</h3>
                    <ul class="recent-activities">
                        <?php foreach ($recent_activities as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon <?php echo $activity['type']; ?>">
                                <?php
                                $icons = [
                                    'user' => '👤',
                                    'project' => '📁',
                                    'message' => '💬',
                                    'system' => '🔧',
                                    'feature' => '⭐'
                                ];
                                echo $icons[$activity['type']] ?? '📋';
                                ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></div>
                                <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animate statistics counters
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                element.textContent = Math.floor(current).toLocaleString('pt-BR');
                
                if (current >= target) {
                    element.textContent = target.toLocaleString('pt-BR');
                    clearInterval(timer);
                }
            }, 16);
        }
        
        // Initialize counter animations
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            // Intersection observer for animation trigger
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.dataset.target);
                        animateCounter(entry.target, target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            statNumbers.forEach(stat => observer.observe(stat));
        });
        
        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('pt-BR');
            const dateString = now.toLocaleDateString('pt-BR');
            
            document.title = `Dashboard - ${timeString}`;
        }
        
        setInterval(updateClock, 1000);
        
        // Simulate real-time updates
        function simulateRealTimeUpdates() {
            const activities = document.querySelector('.recent-activities');
            const newActivities = [
                'Novo login detectado',
                'Sistema atualizado',
                'Backup concluído',
                'Nova mensagem recebida'
            ];
            
            setInterval(() => {
                const randomActivity = newActivities[Math.floor(Math.random() * newActivities.length)];
                const newItem = document.createElement('li');
                newItem.className = 'activity-item';
                newItem.style.opacity = '0';
                newItem.innerHTML = `
                    <div class="activity-icon system">🔧</div>
                    <div class="activity-content">
                        <div class="activity-action">${randomActivity}</div>
                        <div class="activity-time">Agora</div>
                    </div>
                `;
                
                activities.insertBefore(newItem, activities.firstChild);
                
                // Animate in
                setTimeout(() => {
                    newItem.style.transition = 'opacity 0.5s ease';
                    newItem.style.opacity = '1';
                }, 100);
                
                // Remove old items to keep list manageable
                const items = activities.querySelectorAll('.activity-item');
                if (items.length > 8) {
                    items[items.length - 1].remove();
                }
            }, 30000); // Add new activity every 30 seconds
        }
        
        simulateRealTimeUpdates();
        
        // Quick action confirmations
        document.querySelectorAll('.quick-action').forEach(action => {
            action.addEventListener('click', function(e) {
                if (!this.href.includes('index.html')) {
                    e.preventDefault();
                    const actionName = this.textContent.trim();
                    alert(`Funcionalidade "${actionName}" será implementada em breve!`);
                }
            });
        });
    </script>
</body>
</html>