<?php
if (!session_id()) {
    session_start();
}

include_once(__DIR__ . '/../../back-php/conexao.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID inválido.";
    header("Location: ../login.php");
    exit();
}

$id_live = $_GET['id'];

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o registro existe
    $stmtSelect = $conn->prepare("SELECT * FROM user WHERE id = :id");
    $stmtSelect->bindParam(':id', $id_live, PDO::PARAM_INT);
    $stmtSelect->execute();

    if ($stmtSelect->rowCount() > 0) {
        // Deleta o registro
        $stmtDelete = $conn->prepare("DELETE FROM user WHERE id = :id");
        $stmtDelete->bindParam(':id', $id_live, PDO::PARAM_INT);
        $resultDelete = $stmtDelete->execute();

        if ($resultDelete) {
            $_SESSION['success_message'] = "Registro deletado com sucesso.";
        } else {
            $_SESSION['error_message'] = "Erro ao deletar registro.";
        }
    } else {
        $_SESSION['error_message'] = "Registro não encontrado.";
    }

    header('Location: ../funcionario.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erro na conexão: " . $e->getMessage();
    header("Location: ../funcionario.php");
    exit();
}
