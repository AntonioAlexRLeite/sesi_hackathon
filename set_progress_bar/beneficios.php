<?php
session_start();
require 'db.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Simula pegar serviços da empresa (Na demo, fixamos empresa_id=1 ou pegamos do usuário)
// Para o Hackathon, vamos pegar TUDO que está na tabela para mostrar visualmente
$sql = "SELECT * FROM servicos_contratados";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Benefícios - SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #005594; --sec: #00a4e4; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 1000px; margin: 0 auto; }
        .header { margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; }
        .header h1 { color: var(--primary); margin: 0; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        /* Grid de Cards */
        .grid-beneficios { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        
        .card { 
            background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            position: relative; overflow: hidden; transition: transform 0.3s; border-left: 5px solid transparent;
        }
        .card:hover { transform: translateY(-5px); }

        /* Cores por tipo */
        .tipo-Incluso { border-left-color: #28a745; }
        .tipo-Benefício-Extra { border-left-color: #ffc107; }
        .tipo-Desconto { border-left-color: var(--sec); }

        .card-icon { 
            font-size: 30px; margin-bottom: 15px; 
            width: 60px; height: 60px; background: #f0f8ff; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; color: var(--primary);
        }

        .badge { 
            position: absolute; top: 20px; right: 20px; padding: 5px 10px; border-radius: 20px; 
            font-size: 12px; font-weight: bold; color: white; text-transform: uppercase;
        }
        .badge-Incluso { background: #28a745; } /* Verde */
        .badge-Benefício { background: #ffc107; color: #333; } /* Amarelo */
        .badge-Desconto { background: var(--sec); } /* Azul */

        h3 { margin: 0 0 10px 0; color: #333; }
        p { color: #666; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-gift"></i> Sua Carteira de Serviços</h1>
                <p>Confira tudo o que seu contrato #98234 oferece.</p>
            </div>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="grid-beneficios">
            <?php while($row = $result->fetch_assoc()): ?>
                
                <div class="card tipo-<?= str_replace(' ', '-', $row['tipo']) ?>">
                    
                    <div class="badge badge-<?= explode(' ', $row['tipo'])[0] ?>">
                        <?= $row['tipo'] ?>
                    </div>

                    <div class="card-icon">
                        <i class="fas <?= $row['icone'] ?>"></i>
                    </div>
                    
                    <h3><?= $row['nome_servico'] ?></h3>
                    <p><?= $row['descricao'] ?></p>
                    
                </div>

            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>