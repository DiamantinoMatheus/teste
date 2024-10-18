<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifique se os campos esperados estão definidos e não estão vazios
    if (isset($_POST['id']) && isset($_POST['email']) && isset($_POST['senha'])) {
        $id = $_POST['id'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        try {
            $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
            // Define o modo de erro PDO para exceções
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Hash da senha
            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

            $sqlUpdate = "UPDATE user SET email = :email, senha = :senha WHERE id = :id";
            
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha_hashed); // Armazenar o hash da senha

            // Executa a atualização
            $stmt->execute();
            
            header("Location: ../funcionario.php");
        } catch (PDOException $e) {
            echo "Erro ao atualizar: " . $e->getMessage();
        }

        // Fecha a conexão com o banco de dados
        $conn = null;
    } else {
        echo "Erro: Dados incompletos ou não enviados.";
    }
}

// Verifica se o usuário está autenticado
if (isset($_SESSION['email'])) {
    // Mensagem de sucesso (opcional)
    // echo "<pre>Usuário autenticado: " . $_SESSION['email'] . "</pre>";
} else {
    header("Location: ../login.php");
    exit();
}
?>
