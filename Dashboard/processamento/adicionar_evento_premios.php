<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

include_once(__DIR__ . '/../../back-php/conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST["titulo"]) &&
        isset($_FILES["imagem"]) &&
        isset($_FILES["banner"])
    ) {
        $titulo = $_POST["titulo"];

        // Processamento da imagem
        $imagem = null;
        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == UPLOAD_ERR_OK) {
            $imagem = file_get_contents($_FILES["imagem"]["tmp_name"]);
        }

        // Processamento do banner
        $banner = null;
        if (isset($_FILES["banner"]) && $_FILES["banner"]["error"] == UPLOAD_ERR_OK) {
            $banner = file_get_contents($_FILES["banner"]["tmp_name"]);
        }

        try {
            $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Inicia uma transação
            $conn->beginTransaction();

            // Remove todos os registros existentes
            $conn->exec("DELETE FROM eventos_premiacao");

            // Query SQL para inserir o evento no banco de dados
            $sql = "INSERT INTO eventos_premiacao (titulo, imagem, banner) VALUES (:titulo, :imagem, :banner)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':imagem', $imagem, PDO::PARAM_LOB);
            $stmt->bindParam(':banner', $banner, PDO::PARAM_LOB);

            // Executa a query
            $stmt->execute();

            // Confirma a transação
            $conn->commit();

            header("Location: ../dash.php");
            exit(); // Adicionando exit() para interromper a execução após o redirecionamento
        } catch (PDOException $e) {
            // Reverte a transação em caso de erro
            $conn->rollBack();
            echo "Erro ao adicionar o evento: " . $e->getMessage();
        } finally {
            // Fecha a conexão com o banco de dados
            $conn = null;
        }
    } else {
        echo "Por favor, preencha todos os campos obrigatórios e envie os arquivos.";
    }
} else {
    echo "Acesso inválido ao script.";
}
