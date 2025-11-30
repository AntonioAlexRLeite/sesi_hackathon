<?php
session_start();
require 'db.php';

// Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM treinamentos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SESI Academy - Tutoriais</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #005594; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        
        .container { max-width: 1100px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { color: var(--primary); margin: 0; }
        .btn-voltar { text-decoration: none; color: #666; font-weight: bold; }

        /* Grid de Vídeos */
        .video-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }
        
        .video-card { 
            background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .video-card:hover { transform: translateY(-5px); }

        .video-wrapper { position: relative; padding-bottom: 56.25%; /* 16:9 Ratio */ height: 0; background: #000; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

        .video-info { padding: 20px; }
        .video-cat { 
            background: #e3f2fd; color: var(--primary); padding: 4px 8px; border-radius: 4px; 
            font-size: 12px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-bottom: 10px;
        }
        h3 { margin: 0 0 10px 0; font-size: 18px; color: #333; }
        p { color: #666; font-size: 14px; margin: 0; line-height: 1.5; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-graduation-cap"></i> SESI Academy</h1>
                <p>Aprenda a usar o InfoSesi com vídeos rápidos de 2 minutos.</p>
            </div>
            <a href="index.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="video-grid">
            <?php while($video = $result->fetch_assoc()): ?>
                <div class="video-card">
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/<?= $video['youtube_id'] ?>" allowfullscreen></iframe>
                    </div>
                    <div class="video-info">
                        <span class="video-cat"><?= $video['categoria'] ?></span>
                        <h3><?= $video['titulo'] ?></h3>
                        <p><?= $video['descricao'] ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>