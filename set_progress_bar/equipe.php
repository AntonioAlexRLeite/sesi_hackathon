<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Busca a equipe vinculada à empresa (Demo ID 1)
$sql = "SELECT * FROM equipe_sesi WHERE empresa_id = 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Time SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #005594; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { color: var(--primary); margin: 0; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        .team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }

        .member-card {
            background: white; border-radius: 15px; overflow: hidden; text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: transform 0.3s;
        }
        .member-card:hover { transform: translateY(-10px); }

        .avatar-area {
            background: linear-gradient(135deg, #005594 0%, #00a4e4 100%);
            padding: 30px 0; margin-bottom: 50px; position: relative;
        }
        
        .avatar-img {
            width: 100px; height: 100px; border-radius: 50%; border: 4px solid white;
            background: #eee; object-fit: cover;
            position: absolute; bottom: -50px; left: 50%; transform: translateX(-50%);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .info-area { padding: 0 20px 30px 20px; }
        .info-area h3 { margin: 0; color: #333; }
        .role { color: #00a4e4; font-weight: bold; font-size: 14px; margin-bottom: 20px; display: block; }

        .btn-contact {
            display: block; width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px;
            text-decoration: none; font-weight: bold; transition: 0.2s;
        }
        .btn-whatsapp { background: #25d366; color: white; }
        .btn-whatsapp:hover { background: #1da851; }
        
        .btn-email { background: #f0f2f5; color: #333; }
        .btn-email:hover { background: #e4e6eb; }

    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-users"></i> Meu Time de Sucesso</h1>
                <p>Fale diretamente com os especialistas que cuidam da sua conta.</p>
            </div>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="team-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="member-card">
                    <div class="avatar-area">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['nome']) ?>&background=random&size=128" class="avatar-img">
                    </div>
                    <div class="info-area">
                        <h3><?= $row['nome'] ?></h3>
                        <span class="role"><?= $row['funcao'] ?></span>
                        
                        <a href="https://wa.me/<?= $row['whatsapp'] ?>" target="_blank" class="btn-contact btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> Chamar no Zap
                        </a>
                        <a href="mailto:<?= $row['email'] ?>" class="btn-contact btn-email">
                            <i class="far fa-envelope"></i> Enviar E-mail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>