<?php
// Start session
if (!session_id()) {
    session_start();
}

// // Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['senha'])) {
    include_once(__DIR__ . '/../../back-php/conexao.php');

    // Conectar ao banco de dados
    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erro na conexão: " . $e->getMessage();
        exit();
    }

    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // Verificar se o e-mail já está registrado
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Este e-mail já está registrado.";
        } else {
            // Preparar e executar a consulta para inserir o novo usuário
            $stmt = $conn->prepare("INSERT INTO user (email, senha) VALUES (:email, :senha)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senhaHash);

            // Verificar se a consulta foi executada com sucesso
            if ($stmt->execute()) {
                echo "Usuário cadastrado com sucesso!";
                header("Location: ../funcionario.php");
                exit();
            } else {
                echo "Erro ao cadastrar o usuário: " . print_r($stmt->errorInfo(), true);
            }
        }
    } catch (PDOException $e) {
        echo "Erro na execução da consulta: " . $e->getMessage();
    }
}
