<?php
session_start();
require 'db.php'; 

$logado = isset($_SESSION['usuario_id']);
$area = $_SESSION['area_ativa'] ?? 'geral'; 

// Definições de tema
if ($area == 'saude') {
    $temaCor = '#28a745'; $temaIcone = 'fa-heartbeat'; $tituloArea = "Saúde Ocupacional";
    $areaOposta = "seguranca"; $nomeOposto = "Segurança"; 
} elseif ($area == 'cadastro') {
    $temaCor = '#ff9800'; $temaIcone = 'fa-rocket'; $tituloArea = "Onboarding M1"; 
    $areaOposta = "seguranca"; $nomeOposto = "Segurança";
} else {
    $temaCor = '#005594'; $temaIcone = 'fa-hard-hat'; $tituloArea = "Segurança do Trabalho";
    $areaOposta = "saude"; $nomeOposto = "Saúde";
}

// Lógica de BI (Mantida)
$kpi_conformidade = 0; $kpi_agendamentos = 0; $lista_agendamentos = [];
if ($logado) {
    $resAgenda = $conn->query("SELECT count(*) FROM agendamentos_exames WHERE empresa_id = 1 AND status = 'Solicitado'");
    $kpi_agendamentos = $resAgenda ? $resAgenda->fetch_row()[0] : 0;
    $resLista = $conn->query("SELECT c.nome_completo, a.data_preferencia FROM agendamentos_exames a JOIN colaboradores c ON a.colaborador_id = c.id WHERE a.empresa_id = 1 AND a.data_preferencia >= CURDATE() ORDER BY a.data_preferencia ASC LIMIT 3");
    $kpi_conformidade = 85; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal SESI - <?= $tituloArea ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: <?= $temaCor ?>; --bg: #f4f7f6; --white: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* NAVBAR */
        .navbar { background-color: var(--primary); color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: background 0.3s; }
        .logo { font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        .area-switcher button { 
            background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); color: white; 
            padding: 6px 15px; border-radius: 20px; cursor: pointer; font-size: 13px; margin-left: 15px; display: flex; align-items: center; gap: 5px;
        }
        .area-switcher button:hover { background: rgba(255,255,255,0.4); }

        .main-container { flex: 1; padding: 40px; max-width: 1200px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        /* SELEÇÃO INICIAL */
        .selection-screen { display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 50px; }
        .selection-card { background: white; width: 280px; padding: 30px; border-radius: 15px; text-align: center; cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 3px solid transparent; transition: 0.3s; }
        .selection-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        .sel-icon { font-size: 50px; margin-bottom: 20px; }
        
        .card-sst:hover { border-color: #005594; } .card-sst .sel-icon { color: #005594; }
        .card-saude:hover { border-color: #28a745; } .card-saude .sel-icon { color: #28a745; }
        .card-cadastro { border-color: #eee; } .card-cadastro:hover { border-color: #ff9800; } .card-cadastro .sel-icon { color: #ff9800; }

        /* LOGIN / CADASTRO BOX */
        .auth-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; margin: 50px auto; display: none; animation: fadeIn 0.5s; }
        .auth-box.active { display: block; }
        .auth-box input { width: 90%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .btn-primary { background: var(--primary); color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        
        /* DASHBOARD */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .nav-card { background: white; padding: 25px; border-radius: 15px; text-align: center; transition: 0.3s; text-decoration: none; color: #333; border-bottom: 4px solid transparent; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-card:hover { transform: translateY(-5px); border-bottom-color: var(--primary); }
        .nav-icon { font-size: 40px; color: var(--primary); margin-bottom: 15px; }
        
        .bi-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .bi-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .reminder-list { list-style: none; padding: 0; }
        .reminder-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        
        .hidden { display: none !important; }
        .text-link { color: var(--primary); cursor: pointer; text-decoration: underline; font-size: 14px; margin-top: 15px; display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo"><i class="fas <?= $temaIcone ?>"></i> SESI - <?= $tituloArea ?></div>
        <?php if ($logado): ?>
            <div style="display: flex; align-items: center;">
                <span style="margin-right: 10px;">Olá, <strong><?= $_SESSION['usuario_nome'] ?></strong></span>
                
                <form action="auth.php" method="POST" class="area-switcher">
                    <input type="hidden" name="acao" value="trocar_area">
                    <input type="hidden" name="nova_area" value="<?= $areaOposta ?>">
                    <button type="submit">Ir para <?= $nomeOposto ?> <i class="fas fa-exchange-alt"></i></button>
                </form>

                <form action="auth.php" method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="logout">
                    <button type="submit" style="background:none; border:none; color:white; cursor:pointer; font-weight:bold; margin-left:15px; font-size:14px;">Sair <i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="main-container">
        
        <?php if (!$logado): ?>
            
            <div id="selection-screen" class="selection-screen">
                <div class="selection-card card-sst" onclick="showAuth('seguranca')">
                    <div class="sel-icon"><i class="fas fa-hard-hat"></i></div>
                    <h3>Segurança do Trabalho</h3>
                    <p style="color:#666; font-size:13px;">Login para Acompanhar Laudos</p>
                </div>
                
                <div class="selection-card card-saude" onclick="showAuth('saude')">
                    <div class="sel-icon"><i class="fas fa-heartbeat"></i></div>
                    <h3>Saúde Ocupacional</h3>
                    <p style="color:#666; font-size:13px;">Login para Agendar Exames</p>
                </div>
                
                <div class="selection-card card-cadastro" onclick="showAuth('cadastro')">
                    <div class="sel-icon"><i class="fas fa-file-import"></i></div>
                    <h3>Novo Cliente / M1</h3>
                    <p style="color:#666; font-size:13px;">Primeiro Acesso: Enviar Planilha</p>
                </div>
            </div>

            <div id="auth-box" class="auth-box">
                <button onclick="hideAuth()" style="background:none; border:none; color:#666; cursor:pointer; float:left;"><i class="fas fa-arrow-left"></i> Voltar</button>
                <h2 style="color: var(--primary); clear:both;" id="auth-title">Acesso Indústria</h2>
                
                <div id="form-login">
                    <form action="auth.php" method="POST">
                        <input type="hidden" name="acao" value="login">
                        <input type="hidden" name="area_escolhida" id="input-area-login" value="geral">
                        <input type="hidden" name="redirect_to" id="input-redirect-login" value="index.php">
                        
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Senha" required>
                        <button type="submit" class="btn-primary">Entrar</button>
                    </form>
                    <span class="text-link" onclick="toggleForm('cadastro')">Não tem conta? Cadastre-se</span>
                </div>

                <div id="form-cadastro" class="hidden">
                    <h3 style="color:var(--primary); margin-top:0;">Cadastro Inicial</h3>
                    <p style="font-size:13px; color:#666;">Crie sua conta para enviar a M1.</p>
                    <form action="auth.php" method="POST">
                        <input type="hidden" name="acao" value="cadastro">
                        <input type="hidden" name="area_escolhida" id="input-area-cadastro" value="seguranca">
                        <input type="hidden" name="redirect_to" id="input-redirect-cadastro" value="index.php">
                        
                        <input type="text" name="nome" placeholder="Nome da Empresa" required>
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Crie uma senha" required>
                        <button type="submit" class="btn-primary">Cadastrar e Enviar M1</button>
                    </form>
                    <span class="text-link" onclick="toggleForm('login')">Já tenho conta. Fazer Login</span>
                </div>
            </div>

        <?php else: ?>
            <h2 style="color:#666; margin-top:0;"><i class="fas fa-chart-pie"></i> Painel de Gestão</h2>
            <div class="bi-section">
                <div class="bi-card"><h3>Conformidade SST</h3><canvas id="chartConformidade" style="max-height:150px;"></canvas></div>
                <div class="bi-card"><h3>Próximos Exames</h3><ul class="reminder-list">
                    <?php if(isset($resLista) && $resLista->num_rows > 0): while($item = $resLista->fetch_assoc()): ?>
                    <li class="reminder-item"><span><?= substr($item['nome_completo'], 0, 15) ?>...</span><span style="background:#eefbff; color:var(--primary); padding:2px 5px; font-weight:bold;"><?= date('d/m', strtotime($item['data_preferencia'])) ?></span></li>
                    <?php endwhile; else: echo "<li class='reminder-item' style='color:#999;'>Sem exames próximos.</li>"; endif; ?>
                </ul></div>
            </div>

            <h2 style="color:#666;"><i class="fas fa-th-large"></i> Acesso Rápido</h2>
            <div class="dashboard-grid">
                
                <a href="onboarding.php" class="nav-card" style="border-bottom-color: #ff9800;">
                    <div class="nav-icon" style="color: #ff9800;"><i class="fas fa-file-import"></i></div>
                    <h3>Enviar Planilha M1</h3>
                </a>

                <a href="set_progress_bar.php" class="nav-card <?= $area == 'saude' ? 'hidden' : '' ?>">
                    <div class="nav-icon"><i class="fas fa-tasks"></i></div>
                    <h3>Acompanhar PGR</h3>
                </a>

                <a href="pre_analise.php" class="nav-card <?= $area == 'saude' ? 'hidden' : '' ?>">
                    <div class="nav-icon" style="color: #673ab7;"><i class="fas fa-microscope"></i></div>
                    <h3>Pré-Análise de Riscos</h3>
                </a>

                <a href="agendamento.php" class="nav-card <?= $area == 'seguranca' ? 'hidden' : '' ?>">
                    <div class="nav-icon" style="color: #e91e63;"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Agendar Exames</h3>
                </a>

                <a href="chat_sesi.php" class="nav-card">
                    <div class="nav-icon" style="color: #9c27b0;"><i class="fas fa-robot"></i></div>
                    <h3>Central de Ajuda</h3>
                </a>

                <a href="academy.php" class="nav-card">
                    <div class="nav-icon" style="color: #666;"><i class="fas fa-play-circle"></i></div>
                    <h3>Treinamentos</h3>
                </a>

                <a href="admin_dashboard.php" class="nav-card" style="border-bottom-color: #333;">
                    <div class="nav-icon" style="color: #333;"><i class="fas fa-cogs"></i></div>
                    <h3>Gestão Admin</h3>
                </a>
            </div>
            
            <script>
                const ctx = document.getElementById('chartConformidade').getContext('2d');
                new Chart(ctx, { type: 'doughnut', data: { labels: ['Em dia', 'Vencidos'], datasets: [{ data: [85, 15], backgroundColor: ['#28a745', '#dc3545'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } } });
            </script>
        <?php endif; ?>
    </div>

    <script>
        function showAuth(tipo) {
            document.getElementById('selection-screen').style.display = 'none';
            document.getElementById('auth-box').classList.add('active');
            
            const title = document.getElementById('auth-title');
            const btns = document.querySelectorAll('.btn-primary');
            
            // Reseta para padrão (Dashboard)
            document.getElementById('input-redirect-login').value = 'index.php';
            document.getElementById('input-redirect-cadastro').value = 'index.php';

            if (tipo === 'saude') {
                title.innerText = "Acesso Saúde"; title.style.color = "#28a745";
                btns.forEach(b => b.style.backgroundColor = "#28a745");
                document.getElementById('input-area-login').value = 'saude';
                toggleForm('login');
            } 
            else if (tipo === 'seguranca') {
                title.innerText = "Acesso Segurança"; title.style.color = "#005594";
                btns.forEach(b => b.style.backgroundColor = "#005594");
                document.getElementById('input-area-login').value = 'seguranca';
                toggleForm('login');
            }
            else if (tipo === 'cadastro') {
                // LÓGICA DO CARTÃO LARANJA (M1)
                title.innerText = "Novo Cliente / M1"; title.style.color = "#ff9800";
                btns.forEach(b => b.style.backgroundColor = "#ff9800");
                
                document.getElementById('input-area-cadastro').value = 'seguranca';
                document.getElementById('input-area-login').value = 'seguranca'; 
                
                // FORÇA IR PARA ONBOARDING NOS DOIS CASOS
                document.getElementById('input-redirect-cadastro').value = 'onboarding.php';
                document.getElementById('input-redirect-login').value = 'onboarding.php'; 
                
                toggleForm('cadastro'); // Abre direto no cadastro
            }
        }

        function hideAuth() {
            document.getElementById('selection-screen').style.display = 'flex';
            document.getElementById('auth-box').classList.remove('active');
        }

        function toggleForm(formName) {
            if(formName === 'cadastro') {
                document.getElementById('form-login').classList.add('hidden');
                document.getElementById('form-cadastro').classList.remove('hidden');
            } else {
                document.getElementById('form-cadastro').classList.add('hidden');
                document.getElementById('form-login').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>