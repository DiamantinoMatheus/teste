<?php
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

try {
    // Deletar todos os usuários das tabelas
    $pdo->exec("DELETE FROM premiacao");
    
    // Redirecionar de volta com uma mensagem de sucesso
    header("Location: ../Usuarios.php?message=Todos os usuários foram deletados com sucesso.");
    exit;
} catch (PDOException $e) {
    // Redirecionar com uma mensagem de erro
    header("Location: ../dash.php?message=Erro ao deletar usuários: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    exit;
}
