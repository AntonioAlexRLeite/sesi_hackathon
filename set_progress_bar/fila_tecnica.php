<?php
require 'db.php';

// Consulta: Traz as empresas, ordenando Urgência (1) primeiro, depois as mais antigas
$sql = "SELECT * FROM empresas ORDER BY urgencia DESC, data_cadastro ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SESI - Fila Técnica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #005594; }
        
        .card-fila {
            background: white; padding: 20px; margin-bottom: 15px; border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
            border-left: 5px solid #ccc; /* Cor padrão */
        }

        /* Estilo para URGENTE */
        .urgente {
            border-left: 5px solid #dc3545; /* Vermelho */
            background-color: #fff5f5;
        }
        
        .urgente .tag {
            background: #dc3545; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;
            display: inline-block; margin-bottom: 5px;
        }

        .info h3 { margin: 0 0 5px 0; color: #333; }
        .info p { margin: 0; color: #666; font-size: 14px; }
        
        .actions button {
            padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;
        }
        .btn-iniciar { background: #00a4e4; color: white; }
        .btn-ver { background: #e0e0e0; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-list-ol"></i> Fila de Processos (PGR/PCMSO)</h1>
        <p>Gestão de prioridades e ordem de chegada.</p>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                
                <div class="card-fila <?= $row['urgencia'] == 1 ? 'urgente' : '' ?>">
                    
                    <div class="info">
                        <?php if($row['urgencia'] == 1): ?>
                            <div class="tag"><i class="fas fa-exclamation-circle"></i> EMERGÊNCIA</div>
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($row['razao_social']) ?></h3>
                        <p>CNPJ: <?= htmlspecialchars($row['cnpj']) ?> | Resp: <?= htmlspecialchars($row['responsavel']) ?></p>
                        <p><small>Entrada: <?= date('d/m/Y H:i', strtotime($row['data_cadastro'])) ?></small></p>
                    </div>

                    <div class="actions">
                        <button class="btn-ver">Ver M1</button>
                        <button class="btn-iniciar">Iniciar PGR</button>
                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma empresa na fila.</p>
        <?php endif; ?>
    </div>
</body>
</html>