<?php
// Carrega as variáveis de ambiente e configura a conexão
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$status = isset($data['status']) ? $data['status'] : 1; // 1 é aberto, 0 é fechado

try {
    // Atualiza o status no banco de dados
    $sql = "UPDATE eventos_giros SET formulario_aberto = ? WHERE id = 6"; // Assumindo que o formulário tem um ID fixo
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Fechando a conexão
$pdo = null;
