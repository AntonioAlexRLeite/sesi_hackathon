<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Busca os colaboradores cadastrados desta empresa (Demo ID 1)
$sql = "SELECT id, nome_completo, cargo FROM colaboradores WHERE empresa_id = 1";
$result = $conn->query($sql);

// Busca histórico de agendamentos
$sqlHist = "SELECT a.*, c.nome_completo 
            FROM agendamentos_exames a 
            JOIN colaboradores c ON a.colaborador_id = c.id 
            ORDER BY a.data_criacao DESC";
$resHist = $conn->query($sqlHist);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agendamento de Exames - SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #28a745; --bg: #f4f7f6; } /* Verde Saúde */
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .header { grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { color: var(--primary); margin: 0; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        /* Form Card */
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px; }

        label { display: block; margin-top: 15px; font-weight: 600; color: #555; }
        select, input { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        
        .btn-submit { 
            background: var(--primary); color: white; border: none; padding: 15px; width: 100%; 
            border-radius: 5px; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; 
        }
        .btn-submit:hover { filter: brightness(90%); }

        /* Lista Histórico */
        .history-list { list-style: none; padding: 0; }
        .history-item { 
            background: #fff; border-left: 4px solid #ccc; padding: 15px; margin-bottom: 10px; 
            border-radius: 5px; display: flex; justify-content: space-between; align-items: center;
        }
        .status-Solicitado { border-left-color: #ffc107; }
        .status-Confirmado { border-left-color: #28a745; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #eee; }
    </style>
</head>
<body>

    <div class="header"> <div style="max-width: 1000px; margin: 0 auto; width: 100%; display: flex; justify-content: space-between;">
            <div>
                <h1><i class="fas fa-calendar-check"></i> Agendar Exames</h1>
                <p>Marque exames para seus colaboradores cadastrados.</p>
            </div>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </div>

    <div class="container">
        
        <div class="card">
            <h3><i class="fas fa-user-plus"></i> Nova Solicitação</h3>
            
            <form id="formAgenda">
                <label>Colaborador:</label>
                <select id="colaborador" required>
                    <option value="">Selecione...</option>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['nome_completo']} - {$row['cargo']}</option>";
                        }
                    } else {
                        echo "<option disabled>Nenhum colaborador cadastrado (Faça o Onboarding)</option>";
                    }
                    ?>
                </select>

                <label>Tipo de Exame:</label>
                <select id="tipo" required>
                    <option value="Admissional">Admissional</option>
                    <option value="Periódico">Periódico</option>
                    <option value="Demissional">Demissional</option>
                    <option value="Retorno ao Trabalho">Retorno ao Trabalho</option>
                </select>

                <label>Data de Preferência:</label>
                <input type="date" id="data" required>

                <label>Turno:</label>
                <select id="turno">
                    <option value="Manhã">Manhã (08h - 12h)</option>
                    <option value="Tarde">Tarde (13h - 17h)</option>
                </select>

                <button type="submit" class="btn-submit">Confirmar Agendamento</button>
            </form>
        </div>

        <div class="card" style="background: #f9f9f9;">
            <h3><i class="fas fa-history"></i> Meus Agendamentos</h3>
            
            <?php if ($resHist->num_rows == 0): ?>
                <p style="color: #999; text-align: center; margin-top: 30px;">Nenhum agendamento realizado.</p>
            <?php else: ?>
                <ul class="history-list">
                    <?php while($hist = $resHist->fetch_assoc()): ?>
                        <li class="history-item status-<?= $hist['status'] ?>">
                            <div>
                                <strong><?= $hist['nome_completo'] ?></strong><br>
                                <small><?= $hist['tipo_exame'] ?> | <?= date('d/m/Y', strtotime($hist['data_preferencia'])) ?> (<?= $hist['turno'] ?>)</small>
                            </div>
                            <span class="badge"><?= $hist['status'] ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

    <script>
        document.getElementById('formAgenda').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.querySelector('.btn-submit');
            btn.innerText = "Processando...";
            btn.disabled = true;

            const payload = {
                colaborador_id: document.getElementById('colaborador').value,
                tipo: document.getElementById('tipo').value,
                data: document.getElementById('data').value,
                turno: document.getElementById('turno').value
            };

            try {
                const res = await fetch('salvar_agendamento.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
                const json = await res.json();

                if (json.success) {
                    alert('✅ Agendamento solicitado com sucesso! Você receberá a confirmação em breve.');
                    location.reload(); // Recarrega para mostrar na lista
                } else {
                    alert('Erro: ' + json.message);
                }
            } catch (err) {
                alert('Erro de conexão');
            }
            
            btn.innerText = "Confirmar Agendamento";
            btn.disabled = false;
        });
    </script>

</body>
</html>