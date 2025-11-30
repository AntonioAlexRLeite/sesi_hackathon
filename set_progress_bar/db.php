<?php
// db.php - Conexão via MySQLi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sesi_hackathon";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checa a conexão
if ($conn->connect_error) {
    // Não mata o script, apenas lança erro para o try/catch pegar
    throw new Exception("Falha na conexão MySQL: " . $conn->connect_error); 
}

// Garante caracteres corretos (Acentos, etc)
$conn->set_charset("utf8");
?>