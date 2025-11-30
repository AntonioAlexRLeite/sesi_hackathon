<?php
// set_progress_bar.php - Tracker com SLA e Cofre Digital
require 'db.php';
session_start();

// Simulação: Pegamos a empresa ID 1 (Na versão final, pegaria da Sessão: $_SESSION['empresa_id'])
$empresa_id = 1; 

$sql = "SELECT previsao_entrega, status FROM empresas WHERE id = $empresa_id";
$res = $conn->query($sql);

// Variáveis padrão para evitar erro visual se não houver dados
$textoPrazo = "--";
$corPrazo = "#ccc";
$dataTexto = "";
$empresa = ['status' => 1, 'previsao_entrega' => null]; // Valor default

if ($res && $res->num_rows > 0) {
    $empresa = $res->fetch_assoc();

    // Se tiver data de previsão, faz o cálculo do SLA
    if (!empty($empresa['previsao_entrega'])) {
        $dataHoje = new DateTime();
        $dataPrazo = new DateTime($empresa['previsao_entrega']);
        $intervalo = $dataHoje->diff($dataPrazo);
        
        $sinal = $intervalo->format('%r'); // "-" se atrasado
        $dias = $intervalo->format('%a');

        // Lógica de Cores e Texto do Prazo
        if ($empresa['status'] == 5) {
            $textoPrazo = "Concluído!";
            $corPrazo = "#28a745"; // Verde
            $dataTexto = "Entregue em " . date('d/m/Y', strtotime($empresa['previsao_entrega']));
        } elseif ($sinal == "-") {
            $textoPrazo = "Atrasado (" . $dias . " dias)";
            $corPrazo = "#dc3545"; // Vermelho
            $dataTexto = "Era para: " . date('d/m/Y', strtotime($empresa['previsao_entrega']));
        } else {
            $textoPrazo = $dias . " dias restantes";
            $corPrazo = "#005594"; // Azul SESI
            $dataTexto = "Data Alvo: " . date('d/m/Y', strtotime($empresa['previsao_entrega']));
        }
    } else {
        $textoPrazo = "Calculando...";
        $corPrazo = "#999";
        $dataTexto = "Aguardando início";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SESI Tracker - Acompanhamento de Pedido</title>
    <link rel="stylesheet" href="set_progress_bar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="tracker-container">
        <div class="header">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1><i class="fas fa-hard-hat"></i> Portal SESI Indústria</h1>
                    <p>Contrato #98234 - Acompanhamento de Serviços SST</p>
                </div>
                <a href="index.php" style="text-decoration:none; color:#666; font-weight:bold;"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
        </div>

        <div style="display: flex; justify-content: center; margin-bottom: 40px;">
            <div style="background: #fff; padding: 20px 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-align: center; border-top: 5px solid <?= $corPrazo ?>; min-width: 250px;">
                <span style="color: #666; font-size: 14px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Previsão de Entrega</span>
                <div style="font-size: 36px; font-weight: 800; color: <?= $corPrazo ?>; margin: 10px 0;">
                    <?= $textoPrazo ?>
                </div>
                <small style="color: #999; font-weight: 500;"><i class="far fa-calendar-alt"></i> <?= $dataTexto ?></small>
            </div>
        </div>

        <div class="progress-track">
            <div class="progress-fill" id="progressFill"></div>

            <div class="step completed" id="step1">
                <div class="step-icon"><i class="fas fa-file-signature"></i></div>
                <div class="step-content">
                    <div class="step-label">Contrato Fechado</div>
                    <span class="step-date">20/11/2025</span>
                    <button class="btn-action" onclick="alert('Abrindo Contrato em PDF...')"><i class="fas fa-eye"></i> Ver</button>
                </div>
            </div>

            <div class="step <?= $empresa['status'] >= 2 ? 'completed' : 'active' ?>" id="step2">
                <div class="step-icon"><i class="fas fa-file-excel"></i></div>
                <div class="step-content">
                    <div class="step-label">Envio de Dados</div>
                    <span class="step-date"><?= $empresa['status'] >= 2 ? 'Enviado' : 'Pendente' ?></span>
                </div>
            </div>

            <div class="step <?= $empresa['status'] >= 3 ? 'completed' : '' ?> <?= $empresa['status'] == 2 ? 'active' : '' ?>" id="step3">
                <div class="step-icon"><i class="fas fa-user-shield"></i></div>
                <div class="step-content">
                    <div class="step-label">Visita Técnica</div>
                    <span class="step-date"><?= $empresa['status'] >= 3 ? 'Realizada' : 'Aguardando' ?></span>
                </div>
            </div>

            <div class="step <?= $empresa['status'] >= 4 ? 'completed' : '' ?> <?= $empresa['status'] == 3 ? 'active' : '' ?>" id="step4">
                <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="step-content">
                    <div class="step-label">Elaboração PGR</div>
                    
                    <?php if ($empresa['status'] >= 4): ?>
                        <span class="step-date" style="color: green;">Disponível</span>
                        <div class="actions-group">
                            <a href="#" class="btn-download" onclick="alert('Baixando PGR.pdf...')" title="Baixar PGR"><i class="fas fa-file-pdf"></i> PDF</a>
                            <button class="btn-validate" onclick="validarDoc('PGR', this)"><i class="fas fa-check-circle"></i> Validar</button>
                        </div>
                    <?php else: ?>
                        <span class="step-date">Em andamento</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="step <?= $empresa['status'] >= 5 ? 'completed' : '' ?> <?= $empresa['status'] == 4 ? 'active' : '' ?>" id="step5">
                <div class="step-icon"><i class="fas fa-notes-medical"></i></div>
                <div class="step-content">
                    <div class="step-label">PCMSO Pronto</div>
                    
                    <?php if ($empresa['status'] >= 5): ?>
                        <span class="step-date" style="color: green;">Entregue</span>
                        <div class="actions-group">
                            <a href="#" class="btn-download" onclick="alert('Baixando PCMSO.pdf...')" title="Baixar PCMSO"><i class="fas fa-file-pdf"></i> PDF</a>
                            <button class="btn-validate" onclick="validarDoc('PCMSO', this)"><i class="fas fa-check-circle"></i> Validar</button>
                        </div>
                    <?php else: ?>
                        <span class="step-date">Aguardando PGR</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="text-align: center; background: #eefbff; padding: 15px; border-radius: 8px; border: 1px solid #b8daff; color: #004085; margin-top: 30px;">
            <i class="fas fa-info-circle"></i> <strong>Status Atual:</strong> 
            <span id="statusText">Verifique as etapas acima.</span>
        </div>

    </div>

    <script src="set_progress_bar.js"></script>
    <script src="notification.js"></script>
    
    <script>
        // Função exclusiva para validar documentos (Exigência do SESI)
        function validarDoc(doc, btn) {
            if(confirm("Confirma que recebeu e validou o documento " + doc + "?")) {
                // Aqui você chamaria o PHP para gravar no banco
                alert("✅ Documento " + doc + " validado com sucesso! O SESI foi notificado.");
                
                // Atualiza visualmente o botão
                btn.innerHTML = '<i class="fas fa-check-double"></i> Validado';
                btn.disabled = true;
                btn.style.background = "#ccc";
                btn.style.cursor = "default";
            }
        }
    </script>
</body>
</html>