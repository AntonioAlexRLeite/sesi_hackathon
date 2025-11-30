<?php
// bot_brain.php - Cérebro com IA Generativa (Versão Estável Gemini Pro)
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'db.php';

// ==========================================================
// CONFIGURAÇÃO DA IA
// ==========================================================
// ⚠️ COLE SUA CHAVE AQUI DENTRO DAS ASPAS:
$apiKey = "AIzaSyAzbMIlG1VppiDQoN0Lc0YZ-YvCFMhG_jY"; 

// MUDANÇA FEITA: Trocamos para 'gemini-pro' que é mais estável
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;

// ==========================================================
// 1. COLETAR CONTEXTO
// ==========================================================

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$msgUsuario = $input['mensagem'] ?? '';

// Dados padrão caso o banco falhe
$statusTexto = "Não identificado";
$previsao = "A verificar";
$nomeCliente = "Cliente";

if (isset($conn) && !$conn->connect_error) {
    // Tenta pegar dados da empresa ID 1 (Demo)
    $sql = "SELECT razao_social, status, previsao_entrega FROM empresas WHERE id = 1";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $nomeCliente = $row['razao_social'];
        $previsao = $row['previsao_entrega'] ? date('d/m/Y', strtotime($row['previsao_entrega'])) : "Sem data";
        
        $mapaStatus = [1=>"Contrato Fechado", 2=>"Aguardando M1", 3=>"Visita Técnica", 4=>"Elaboração PGR", 5=>"Concluído"];
        $statusTexto = $mapaStatus[$row['status']] ?? "Em andamento";
    }
}

// ==========================================================
// 2. PROMPT DO SISTEMA
// ==========================================================

$systemPrompt = "
Aja como 'BIA', a assistente virtual do SESI.
Contexto do Cliente Atual:
- Empresa: $nomeCliente
- Status: $statusTexto
- Previsão: $previsao

Regras:
1. Responda de forma curta, educada e humana.
2. Use emojis.
3. Se perguntarem status, use os dados acima.
4. Se for sobre saúde/exames, oriente usar o menu 'Saúde'.

Mensagem do Cliente:
$msgUsuario
";

// ==========================================================
// 3. CHAMADA API (Com Correção SSL e Modelo Pro)
// ==========================================================

if ($msgUsuario) {
    $data = [
        "contents" => [
            [ "parts" => [ ["text" => $systemPrompt] ] ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Desativa a verificação de SSL para rodar no XAMPP sem erro
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $jsonResponse = json_decode($response, true);
    
    if (isset($jsonResponse['candidates'][0]['content']['parts'][0]['text'])) {
        $respostaFinal = $jsonResponse['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Se der erro, mostra o motivo técnico para facilitar o ajuste
        $erroMsg = isset($jsonResponse['error']['message']) ? $jsonResponse['error']['message'] : "Erro desconhecido";
        $respostaFinal = "Desculpe, erro na IA: " . $erroMsg;
        if($curlError) $respostaFinal .= " | Curl: " . $curlError;
    }
} else {
    $respostaFinal = "Olá! Como posso ajudar?";
}

// ==========================================================
// 4. SALVAR E RESPONDER
// ==========================================================

$departamento = "Automatico";
if (stripos($msgUsuario, 'exame') !== false) $departamento = "Saude";
if (stripos($msgUsuario, 'pgr') !== false) $departamento = "Seguranca";

if (isset($conn) && !$conn->connect_error && $msgUsuario) {
    $stmt = $conn->prepare("INSERT INTO atendimentos (mensagem_cliente, resposta_bot, departamento_destino) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $msgUsuario, $respostaFinal, $departamento);
        $stmt->execute();
    }
}

ob_clean();
echo json_encode(['resposta' => $respostaFinal]);
exit;
?>