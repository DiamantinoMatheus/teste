<?php
session_start();

$secret_key = getenv('SECRET_KEY');

function encrypt_email($email, $key) {
    $iv = openssl_random_pseudo_bytes(16);
    return base64_encode(openssl_encrypt($email, 'aes-256-cbc', $key, 0, $iv) . '::' . $iv);
}

function hash_email($email) {
    return hash('sha256', $email);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/giros.php");
        exit();
    }

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $codigo = str_replace(' ', '', filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!$nome || !$email || !$codigo) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/giros.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Criptografa o e-mail fornecido
        $email_encrypted = encrypt_email($email, $secret_key);
        // Gera o hash do e-mail
        $email_hashed = hash_email($email);

        // Verifica se o e-mail já foi utilizado
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM giros WHERE email_hash = :email_hash");
        $checkStmt->execute([':email_hash' => $email_hashed]);

        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'O código ou e-mail informado já foi utilizado. Por favor, forneça um código ou e-mail diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/giros.php");
            exit();
        }

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO giros (nome, email, email_hash, codigo) VALUES (:nome, :email, :email_hash, :codigo)");
        $stmt->execute([':nome' => $nome, ':email' => $email_encrypted, ':email_hash' => $email_hashed, ':codigo' => $codigo]);

        $_SESSION['message'] = $stmt->rowCount() ? 'Formulário enviado com sucesso!' : 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = $stmt->rowCount() ? 'success' : 'error';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = 'error';
    }

    header("Location: ../../Forms/giros.php");
    exit();
}
?>
