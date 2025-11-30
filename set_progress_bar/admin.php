<?php
// admin.php - CORRIGIDO
// 1. Processa o formulário se houver POST
if (isset($_POST['status'])) {
    file_put_contents('status.txt', $_POST['status']);
    $mensagem = "Status atualizado para etapa " . $_POST['status'];
}

// 2. Lê o status atual com segurança (AQUI ESTAVA O ERRO)
if (file_exists('status.txt')) {
    $atual = file_get_contents('status.txt');
} else {
    $atual = 1; // Valor padrão se o arquivo não existir
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin SESI</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #333; color: white; }
        .card { background: #444; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; }
        select, button { padding: 10px; margin-top: 10px; width: 100%; }
        button { background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold;}
    </style>
</head>
<body>
    <div class="card">
        <h2>👷 Área Técnica SESI</h2>
        <p>Atualize o andamento do contrato da Indústria.</p>
        
        <?php if(isset($mensagem)) echo "<p style='color: #90ee90'>$mensagem</p>"; ?>

        <form method="POST">
            <label>Status Atual do Processo:</label>
            <select name="status">
                <option value="1" <?= $atual == 1 ? 'selected' : '' ?>>1. Contrato Fechado</option>
                <option value="2" <?= $atual == 2 ? 'selected' : '' ?>>2. Aguardando Planilha M1</option>
                <option value="3" <?= $atual == 3 ? 'selected' : '' ?>>3. Visita Técnica</option>
                <option value="4" <?= $atual == 4 ? 'selected' : '' ?>>4. Elaboração PGR</option>
                <option value="5" <?= $atual == 5 ? 'selected' : '' ?>>5. PCMSO Pronto</option>
            </select>
            <button type="submit">Atualizar Cliente (Push)</button>
        </form>
    </div>
</body>
</html>