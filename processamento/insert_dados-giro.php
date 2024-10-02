<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verifique se o token CSRF enviado corresponde ao token na sessão
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/giros.php");
        exit();
    }

    // Obtém e valida os valores do POST
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);

    // Verifica se todos os dados são válidos
    if (!$nome || !$email || !$codigo) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/giros.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        // Cria uma nova conexão PDO
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o código ou e-mail já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM giros WHERE codigo = :codigo OR email = :email");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = 'O código ou e-mail informado já foi utilizado. Por favor, forneça um código ou e-mail diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/giros.php");
            exit();
        }

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO giros (nome, email, codigo) VALUES (:nome, :email, :codigo)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':codigo', $codigo);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Formulário enviado com sucesso!';
            $_SESSION['messageClass'] = 'success';
        } else {
            $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
            $_SESSION['messageClass'] = 'error';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = 'error';
    }

    // Redireciona para a página do formulário
    header("Location: ../../Forms/giros.php");
    exit();
}
