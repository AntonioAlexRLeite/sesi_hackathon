<?php
// salvar_risco.php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Demo: ID 1
$empresa_id = 1; 

$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    $stmt = $conn->prepare("INSERT INTO riscos_preliminares (empresa_id, tipo_risco, descricao, local_uso, detalhes) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issss", 
        $empresa_id,
        $input['tipo'],
        $input['nome'],
        $input['local'],
        $input['obs']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>