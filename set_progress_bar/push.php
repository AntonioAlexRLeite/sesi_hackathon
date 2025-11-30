<?php
// push.php - Envia eventos para o navegador (Server-Sent Events)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Pega o status atual
$lastStatus = null;

while (true) {
    // Limpa o cache de arquivo do PHP para ler o valor real
    clearstatcache();
    
    // Lê o arquivo status.txt
    if (file_exists('status.txt')) {
        $currentStatus = file_get_contents('status.txt');
    } else {
        $currentStatus = 1;
    }

    // Se o status mudou ou é a primeira execução, envia o dado
    if ($currentStatus != $lastStatus) {
        // Formato específico do SSE: "data: MENSAGEM \n\n"
        echo "data: " . $currentStatus . "\n\n";
        
        // Força o envio do buffer para o navegador imediatamente
        if (ob_get_level() > 0) ob_end_flush();
        flush();
        
        $lastStatus = $currentStatus;
    }

    // Espera 1 segundo antes de verificar de novo (para não travar o servidor)
    sleep(1);
    
    // Fecha a conexão se o cliente desconectar
    if (connection_aborted()) break;
}
?>