<?php
// diagnostico.php - Teste Geral do Sistema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🕵️ Diagnóstico do Sistema SESI</h1>";

// 1. Teste de Arquivos
echo "<h3>1. Verificando Arquivos:</h3>";
if (file_exists('db.php')) {
    echo "<p style='color:green'>✅ db.php encontrado.</p>";
} else {
    die("<p style='color:red'>❌ ERRO CRÍTICO: O arquivo db.php não existe nesta pasta.</p>");
}

if (file_exists('status.txt')) {
    echo "<p style='color:green'>✅ status.txt encontrado.</p>";
} else {
    echo "<p style='color:orange'>⚠️ status.txt não encontrado (O bot não saberá o status).</p>";
}

// 2. Teste de Conexão com Banco
echo "<h3>2. Testando Conexão com Banco de Dados:</h3>";
try {
    include 'db.php';
    
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
        echo "<p style='color:green'>✅ Conexão MySQL realizada com sucesso!</p>";
        echo "Host: " . $conn->host_info;
    } else {
        throw new Exception("A variável \$conn não foi criada corretamente no db.php");
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERRO DE CONEXÃO: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se a senha no arquivo <b>db.php</b> está igual à do Workbench.</p>";
    exit; // Para aqui se não tiver banco
}

// 3. Teste da Tabela
echo "<h3>3. Verificando Tabela 'atendimentos':</h3>";
$sql = "SELECT count(*) as total FROM atendimentos";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color:green'>✅ Tabela encontrada! Total de registros: " . $row['total'] . "</p>";
} else {
    echo "<p style='color:red'>❌ ERRO: A tabela 'atendimentos' não existe ou tem erro.</p>";
    echo "Erro MySQL: " . $conn->error;
}

echo "<hr><h3>🏁 Conclusão:</h3>";
echo "<p>Se você viu todos os ✅ verdes acima, o problema está apenas no arquivo do bot.</p>";
?>