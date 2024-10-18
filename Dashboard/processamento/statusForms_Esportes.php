<?php

// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

try {
    // Verifica se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEvento = isset($_POST['eventoId4']) ? intval($_POST['eventoId4']) : null;

        if ($idEvento === null) {
            throw new Exception("ID do evento não fornecido.");
        }

        // Busca o status atual
        $queryStatus = "SELECT interative FROM eventos_esportes WHERE id = :idEvento LIMIT 1";
        $stmtStatus = $pdo->prepare($queryStatus);
        $stmtStatus->execute(['idEvento' => $idEvento]);
        $statusAtual = $stmtStatus->fetchColumn();

        if ($statusAtual === false) {
            throw new Exception("Evento não encontrado.");
        }

        // Alterna o status (se estiver 1, muda para 2, e vice-versa)
        $novoStatusEvento = ($statusAtual == 1) ? 2 : 1;

        // Atualiza o status do formulário
        $queryAtualiza = "UPDATE eventos_esportes SET interative = :novoStatusEvento WHERE id = :idEvento";
        $stmtAtualiza = $pdo->prepare($queryAtualiza);
        $stmtAtualiza->execute(['novoStatusEvento' => $novoStatusEvento, 'idEvento' => $idEvento]);

        // Redireciona para a página do dashboard
        header("Location: ../dash.php");
        exit;
    }

    // Verifica se a requisição é do tipo GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['eventoId1'])) {
        $idEvento = intval($_GET['eventoId4']);

        // Busca o status atual
        $queryStatus = "SELECT interative FROM eventos_esportes WHERE id = :idEvento LIMIT 1";
        $stmtStatus = $pdo->prepare($queryStatus);
        $stmtStatus->execute(['idEvento' => $idEvento]);
        $statusAtual = $stmtStatus->fetchColumn();

        // Retornar o status atual como JSON
        header('Content-Type: application/json');
        echo json_encode(['status1' => $statusAtual]);
        exit; // Adicione exit para evitar a execução de código adicional
    }
} catch (Exception $e) {
    echo "Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
