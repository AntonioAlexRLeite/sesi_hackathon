<?php
// auth.php - Login e Cadastro com Redirecionamento e Troca de Área
session_start();
require 'db.php';

$acao = $_POST['acao'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$nome = $_POST['nome'] ?? '';
$area = $_POST['area_escolhida'] ?? 'geral'; 

// CAPTURA O DESTINO (O segredo: se não vier nada, vai pro index.php)
$destino = $_POST['redirect_to'] ?? 'index.php';

if ($acao === 'cadastro') {
    // Verifica e-mail duplicado
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) { header("Location: index.php?erro=email_existe"); exit; }

    // Cria usuário
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $hash);
    
    if ($stmt->execute()) {
        $_SESSION['usuario_id'] = $conn->insert_id;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['area_ativa'] = $area;
        
        // REDIRECIONA PARA ONDE O FORMULÁRIO PEDIU (M1 ou Index)
        header("Location: $destino"); 
    } else { header("Location: index.php?erro=falha_cadastro"); }

} elseif ($acao === 'login') {
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $row['nome'];
            $_SESSION['area_ativa'] = $area; // Atualiza a área
            
            // REDIRECIONA
            header("Location: $destino");
            exit;
        }
    }
    header("Location: index.php?erro=credenciais_invalidas");

} elseif ($acao === 'trocar_area') {
    // Lógica do Botão do Cabeçalho
    $_SESSION['area_ativa'] = $_POST['nova_area'];
    header("Location: index.php");

} elseif ($acao === 'logout') {
    session_destroy();
    header("Location: index.php");
}
?>