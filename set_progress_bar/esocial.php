<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Busca os envios da empresa (Demo ID 1)
$sql = "SELECT * FROM esocial_envios WHERE empresa_id = 1 ORDER BY data_envio DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Monitor e-Social - SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gov-green: #004d32; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 10px; }
        .logo-esocial { color: var(--gov-green); font-weight: 900; letter-spacing: -1px; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        /* Card de Resumo */
        .summary-card {
            background: linear-gradient(135deg, #004d32 0%, #006642 100%);
            color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,77,50,0.2);
        }
        .summary-text h2 { margin: 0; font-size: 20px; }
        .summary-text p { margin: 5px 0 0 0; opacity: 0.9; font-size: 14px; }
        .status-big { font-size: 14px; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; }

        /* Tabela */
        .table-container { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; color: #666; font-weight: 600; text-align: left; padding: 15px; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #333; font-size: 14px; }
        
        /* Badges de Status */
        .badge { padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .badge-Processado { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-Pendente { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-Erro { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .recibo { font-family: monospace; background: #eee; padding: 2px 5px; border-radius: 3px; color: #555; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-server logo-esocial"></i> Monitor de Conformidade e-Social</h1>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="summary-card">
            <div class="summary-text">
                <h2>Status de Conformidade</h2>
                <p>O SESI está monitorando seus prazos legais.</p>
            </div>
            <div class="status-big"><i class="fas fa-check-circle"></i> Em dia</div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Descrição / Colaborador</th>
                        <th>Status do Envio</th>
                        <th>Recibo Governo</th>
                        <th>Data Processamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $row['tipo_evento'] ?></strong></td>
                            <td>
                                <?= $row['descricao'] ?><br>
                                <small style="color:#888;"><?= $row['funcionario_nome'] ?></small>
                            </td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                            <td>
                                <?php if($row['numero_recibo']): ?>
                                    <span class="recibo"><?= $row['numero_recibo'] ?></span>
                                <?php else: ?>
                                    <span style="color:#999;">--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $row['data_envio'] ? date('d/m/Y H:i', strtotime($row['data_envio'])) : 'Aguardando' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>