<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Busca riscos já cadastrados
$sql = "SELECT * FROM riscos_preliminares WHERE empresa_id = 1 ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pré-Análise de Riscos - SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #005594; --bg: #f4f7f6; --danger: #dc3545; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { color: var(--primary); margin: 0; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        /* Card de Aviso */
        .alert-box {
            background: #fff3cd; border-left: 5px solid #ffc107; padding: 20px; border-radius: 5px; margin-bottom: 30px; color: #856404;
            display: flex; align-items: start; gap: 15px;
        }

        /* Formulário */
        .form-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        
        .btn-add { 
            background: var(--primary); color: white; border: none; padding: 12px 25px; 
            border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 20px; 
        }
        .btn-add:hover { filter: brightness(90%); }

        /* Tabela de Itens */
        .risk-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .risk-table th { background: #eee; text-align: left; padding: 15px; color: #555; }
        .risk-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        
        .icon-type { width: 30px; text-align: center; display: inline-block; margin-right: 10px; }
        .quimico { color: #dc3545; } /* Vermelho */
        .fisico { color: #28a745; } /* Verde */
        .mecanico { color: #ffc107; } /* Amarelo */

    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-microscope"></i> Pré-Inventário de Riscos</h1>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="alert-box">
            <i class="fas fa-lightbulb fa-2x"></i>
            <div>
                <strong>Agilize sua Visita Técnica!</strong><br>
                Cadastre abaixo os produtos químicos (ex: Thinner, Ácidos) e máquinas ruidosas que sua empresa utiliza. 
                Assim, o técnico do SESI já levará os equipamentos de medição corretos, reduzindo o tempo da visita em até 50%.
            </div>
        </div>

        <div class="form-card">
            <h3 style="margin-top:0; color:#005594;">Adicionar Agente de Risco</h3>
            <form id="formRisco">
                <div class="form-grid">
                    <div>
                        <label>Tipo do Risco:</label>
                        <select id="tipo" required>
                            <option value="Químico">☢️ Produto Químico</option>
                            <option value="Físico">🔊 Ruído / Calor / Vibração</option>
                            <option value="Acidente">⚙️ Máquinas / Queda</option>
                        </select>
                    </div>
                    <div>
                        <label>Nome do Produto/Agente:</label>
                        <input type="text" id="nome" placeholder="Ex: Tolueno, Furadeira..." required>
                    </div>
                    <div>
                        <label>Local de Uso:</label>
                        <input type="text" id="local" placeholder="Ex: Galpão de Pintura" required>
                    </div>
                    <div>
                        <label>Detalhes / Quantidade:</label>
                        <input type="text" id="obs" placeholder="Ex: Uso diário, 5 litros/mês">
                    </div>
                </div>
                <button type="submit" class="btn-add"><i class="fas fa-plus-circle"></i> Adicionar ao Inventário</button>
            </form>
        </div>

        <table class="risk-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Local</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $icone = "fa-flask quimico";
                        if($row['tipo_risco'] == 'Físico') $icone = "fa-volume-up fisico";
                        if($row['tipo_risco'] == 'Acidente') $icone = "fa-cogs mecanico";
                    ?>
                        <tr>
                            <td><i class="fas <?= $icone ?> icon-type"></i> <?= $row['tipo_risco'] ?></td>
                            <td><strong><?= $row['descricao'] ?></strong></td>
                            <td><?= $row['local_uso'] ?></td>
                            <td style="color:#666;"><?= $row['detalhes'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:30px; color:#999;">Nenhum risco informado ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('formRisco').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.querySelector('.btn-add');
            const originalText = btn.innerHTML;
            btn.innerHTML = "Salvando...";
            btn.disabled = true;

            const payload = {
                tipo: document.getElementById('tipo').value,
                nome: document.getElementById('nome').value,
                local: document.getElementById('local').value,
                obs: document.getElementById('obs').value
            };

            try {
                const res = await fetch('salvar_risco.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
                const json = await res.json();

                if (json.success) {
                    location.reload(); // Recarrega para mostrar na tabela
                } else {
                    alert('Erro ao salvar: ' + json.message);
                }
            } catch (err) {
                alert('Erro de conexão.');
            }
            
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    </script>

</body>
</html>