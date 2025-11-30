<?php
// salvar_m1.php - VERSÃO FINAL COM CÁLCULO DE PRAZO (SLA)
require 'db.php'; 

header('Content-Type: application/json');

// Habilita relatório de erros do MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Recebe o JSON do Javascript
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception("Nenhum dado recebido.");
    }

    // Inicia Transação
    $conn->begin_transaction();

    // =================================================================
    // --- 1. Inserir Empresa (ALTERADO AQUI) ---
    // =================================================================
    
    // A. Define o prazo padrão (15 dias)
    $prazo = date('Y-m-d', strtotime('+15 days'));
    
    // B. Captura a urgência
    $urgenciaValor = $input['empresa']['urgencia'] ? 1 : 0;

    // C. Se for urgente, reduz o prazo para 5 dias
    if ($urgenciaValor == 1) {
        $prazo = date('Y-m-d', strtotime('+5 days'));
    }

    // D. Prepara a query INCLUINDO a coluna 'previsao_entrega'
    $stmt = $conn->prepare("INSERT INTO empresas (razao_social, cnpj, responsavel, email, urgencia, previsao_entrega) VALUES (?, ?, ?, ?, ?, ?)");
    
    // E. Faz o vínculo: "ssssis" = 4 Strings, 1 Inteiro (urgencia), 1 String (prazo)
    $stmt->bind_param("ssssis", 
        $input['empresa']['razao'], 
        $input['empresa']['cnpj'], 
        $input['empresa']['responsavel'], 
        $input['empresa']['email'],
        $urgenciaValor,
        $prazo
    );
    
    $stmt->execute();
    $empresaId = $conn->insert_id; // Pega o ID gerado
    $stmt->close();

    // =================================================================
    // --- 2. Inserir Colaboradores (Loop) ---
    // =================================================================
    $stmtColab = $conn->prepare("INSERT INTO colaboradores (empresa_id, nome_completo, cpf, data_nascimento, cargo) VALUES (?, ?, ?, ?, ?)");

    // Variáveis auxiliares
    $nome = ""; $cpf = ""; $nasc = ""; $cargo = "";

    $stmtColab->bind_param("issss", $empresaId, $nome, $cpf, $nasc, $cargo);

    foreach ($input['colaboradores'] as $colab) {
        $nome = $colab['nome'];
        $cpf = $colab['cpf'];
        $nasc = $colab['nascimento'];
        $cargo = $colab['cargo'];
        
        $stmtColab->execute();
    }
    
    $stmtColab->close();

    // Confirma as alterações
    $conn->commit();
    
    // Atualiza o status.txt para simular avanço imediato (Opcional)
    // file_put_contents('status.txt', '2'); 
    
    echo json_encode(['success' => true, 'message' => 'Onboarding concluído! Prazo estimado: ' . date('d/m/Y', strtotime($prazo))]);

} catch (Exception $e) {
    // Se der erro, desfaz tudo
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

$conn->close();
?>