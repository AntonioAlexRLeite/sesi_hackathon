<?php
session_start();
require 'db.php';

// Segurança: Verifica se está logado (Pode remover para testes rápidos se quiser)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// --- COLETANDO DADOS DO BANCO PARA OS INDICADORES ---

// 1. Contagem por Status (Para o Gráfico de Gargalos)
// Ex: Quantas empresas estão paradas em cada etapa?
$sqlChart = "SELECT status, COUNT(*) as qtd FROM empresas GROUP BY status ORDER BY status";
$resChart = $conn->query($sqlChart);
$dataChart = [0, 0, 0, 0, 0, 0]; // Inicializa array
while($row = $resChart->fetch_assoc()) {
    $dataChart[$row['status']] = $row['qtd'];
}
// Converte para string pro JS ler (ex: "1, 5, 2, 0, 0")
$chartString = implode(",", array_slice($dataChart, 1)); 

// 2. KPIs Gerais
$totalEmpresas = $conn->query("SELECT count(*) FROM empresas")->fetch_row()[0];
$totalUrgentes = $conn->query("SELECT count(*) FROM empresas WHERE urgencia = 1 AND status < 5")->fetch_row()[0];
$totalChamados = $conn->query("SELECT count(*) FROM atendimentos")->fetch_row()[0];

// 3. Lista de Prioridades (Urgentes e Atrasados)
$sqlFila = "SELECT * FROM empresas WHERE urgencia = 1 AND status < 5 ORDER BY data_cadastro ASC LIMIT 5";
$resFila = $conn->query($sqlFila);

// 4. Últimos Atendimentos do Bot (Supervisão)
$sqlBot = "SELECT * FROM atendimentos ORDER BY data_hora DESC LIMIT 5";
$resBot = $conn->query($sqlBot);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gestão SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #005594; --sec: #00a4e4; --bg: #f4f7f6; --danger: #dc3545; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: var(--primary); color: white; min-height: 100vh; padding: 20px; position: fixed; }
        .sidebar h2 { margin-top: 0; font-size: 22px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 20px; }
        .menu a { display: block; color: white; text-decoration: none; padding: 15px; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .menu a:hover, .menu a.active { background: rgba(255,255,255,0.1); }
        .menu i { margin-right: 10px; width: 20px; }

        /* Conteúdo Principal */
        .main { margin-left: 250px; padding: 40px; width: 100%; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Cards de KPI */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; }
        .card-info h3 { margin: 0; font-size: 36px; color: var(--primary); }
        .card-info p { margin: 0; color: #666; }
        .card-icon { font-size: 40px; color: var(--sec); opacity: 0.5; }
        .card.danger { border-left: 5px solid var(--danger); }
        .card.danger h3 { color: var(--danger); }

        /* Gráfico e Tabelas */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .panel { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .panel h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { color: #666; font-weight: 600; }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; color: white; }
        .bg-vermelho { background: var(--danger); }
        .bg-azul { background: var(--sec); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="fas fa-hard-hat"></i> SESI Admin</h2>
        <div class="menu">
            <a href="#" class="active"><i class="fas fa-chart-line"></i> Visão Geral</a>
            <a href="fila_tecnica.php"><i class="fas fa-list"></i> Fila Técnica</a>
            <a href="admin.php"><i class="fas fa-cogs"></i> Simulador (Demo)</a>
            <a href="index.php"><i class="fas fa-home"></i> Voltar ao Portal</a>
        </div>
    </div>

    <div class="main">
        <div class="header-top">
            <div>
                <h1>Gestão Operacional</h1>
                <p style="color: #666;">Acompanhamento em tempo real dos processos de SST.</p>
            </div>
            <div>
                <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Relatório</button>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="card">
                <div class="card-info">
                    <h3><?= $totalEmpresas ?></h3>
                    <p>Empresas Ativas</p>
                </div>
                <div class="card-icon"><i class="fas fa-building"></i></div>
            </div>
            
            <div class="card danger">
                <div class="card-info">
                    <h3><?= $totalUrgentes ?></h3>
                    <p>Casos Críticos</p>
                </div>
                <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?= $totalChamados ?></h3>
                    <p>Interações Bot</p>
                </div>
                <div class="card-icon"><i class="fas fa-comments"></i></div>
            </div>
        </div>

        <div class="content-grid">
            
            <div class="panel">
                <h3><i class="fas fa-filter"></i> Funil de Processos (Gargalos)</h3>
                <canvas id="gargaloChart" style="max-height: 300px;"></canvas>
                <p style="font-size: 13px; color: #666; margin-top: 10px;">
                    *Este gráfico mostra onde as empresas estão "travadas". Se a barra "Aguardando M1" for a maior, precisamos cobrar os clientes.
                </p>
            </div>

            <div class="panel">
                <h3><i class="fas fa-fire" style="color: red;"></i> Atenção Imediata</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $resFila->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= substr($row['razao_social'], 0, 20) ?>...</strong><br>
                                <span style="color: red; font-size: 11px;">EMERGÊNCIA</span>
                            </td>
                            <td>
                                <a href="#" style="color: var(--primary); font-weight: bold; text-decoration: none;">Tratar ></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($resFila->num_rows == 0): ?>
                            <tr><td colspan="2" style="text-align:center; color:green;">Nenhuma emergência pendente!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <h3><i class="fas fa-robot"></i> Supervisão do Atendimento (Últimas Interações)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mensagem do Cliente</th>
                        <th>Resposta da IA</th>
                        <th>Direcionamento</th>
                        <th>Horário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($chat = $resBot->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $chat['id'] ?></td>
                        <td style="color: #555;">"<?= htmlspecialchars($chat['mensagem_cliente']) ?>"</td>
                        <td style="font-style: italic; color: #005594;"><?= substr(htmlspecialchars($chat['resposta_bot']), 0, 50) ?>...</td>
                        <td>
                            <?php if($chat['departamento_destino'] == 'Saude'): ?>
                                <span class="status-badge bg-azul">SAÚDE</span>
                            <?php elseif($chat['departamento_destino'] == 'Seguranca'): ?>
                                <span class="status-badge bg-vermelho">SEGURANÇA</span>
                            <?php else: ?>
                                <span style="color:#999; font-size: 11px;">Automático</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('H:i', strtotime($chat['data_hora'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('gargaloChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Contrato', 'Aguardando M1', 'Visita Técnica', 'Elaboração PGR', 'PCMSO Final'],
                datasets: [{
                    label: 'Empresas nesta etapa',
                    data: [<?= $chartString ?>], // Dados vindos do PHP
                    backgroundColor: [
                        '#e0e0e0', // Contrato
                        '#dc3545', // M1 (Gargalo comum - Vermelho)
                        '#ffc107', // Visita (Amarelo)
                        '#00a4e4', // PGR
                        '#28a745'  // PCMSO (Verde)
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, stepSize: 1 } },
                plugins: { legend: { display: false } }
            }
        });
    </script>

</body>
</html>