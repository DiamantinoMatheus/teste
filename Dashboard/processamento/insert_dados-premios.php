<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/premiadas.php");
        exit();
    }

    // Sanitizando os dados de entrada
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);
    $zap = filter_input(INPUT_POST, 'zap', FILTER_SANITIZE_SPECIAL_CHARS); // WhatsApp

    if (!$nome || !$email || !$codigo || !$zap) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/premiadas.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    // Chave de criptografia
    $secret_key = 'sua_chave_super_secreta'; // Substitua por uma chave forte e segura

    // Função para criptografar dados
    function encrypt_data($data, $key) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    // Criptografando o e-mail
    $email_criptografado = encrypt_data($email, $secret_key);

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o código ou e-mail já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM premiacao WHERE codigo = :codigo OR email = :email");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':email', $email_criptografado); // Verifica o e-mail criptografado
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = 'O código ou e-mail informado já foi utilizado. Por favor, forneça um código ou e-mail diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/premiadas.php");
            exit();
        }

        // Inserindo os dados criptografados no banco de dados
        $stmt = $conn->prepare("INSERT INTO premiacao (nome, email, codigo, whatsapp) VALUES (:nome, :email, :codigo, :zap)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email_criptografado); // Insere o e-mail criptografado
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':zap', $zap); // Insere o WhatsApp sem criptografia

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

    header("Location: ../../Forms/premiadas.php");
    exit();
}
