<?php
// Carrega as variáveis de ambiente e configura a conexão
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

header('Content-Type: application/json');

try {
    // Consulta para verificar o status do formulário
    $query = "SELECT interative FROM eventos_esportes LIMIT 1"; // Ajuste a tabela e a coluna conforme necessário
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Verifique se há resultados
    if ($stmt->rowCount() == 1) {
        $status = $stmt->fetchColumn();

        // Retornar o status em formato JSON
        echo json_encode(['status' => $status]);
    } else {
        echo json_encode(['status' => 2]); // Se não houver resultados, considere como fechado
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Fechando a conexão
$pdo = null;
