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
    $zap = filter_input(INPUT_POST, 'zap', FILTER_SANITIZE_SPECIAL_CHARS); // WhatsApp
    $tempo_mercado = filter_input(INPUT_POST, 'tempo_mercado', FILTER_SANITIZE_SPECIAL_CHARS);
    $site_apostas = filter_input(INPUT_POST, 'site_apostas', FILTER_SANITIZE_SPECIAL_CHARS);
    $faturamento_medio = filter_input(INPUT_POST, 'faturamento_medio', FILTER_VALIDATE_FLOAT);
    $faturamento_maximo = filter_input(INPUT_POST, 'faturamento_maximo', FILTER_VALIDATE_FLOAT);

    if (!$nome || !$email || !$zap || !$tempo_mercado || !$site_apostas || $faturamento_medio === false || $faturamento_maximo === false) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/premiadas.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    // Chave de criptografia
    $secret_key = 'sua_chave_super_secreta'; // Substitua por uma chave forte e segura

    // Função para criptografar dados
    function encrypt_data($data, $key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    // Criptografando os dados
    $nome_criptografado = encrypt_data($nome, $secret_key);
    $email_criptografado = encrypt_data($email, $secret_key);
    $zap_criptografado = encrypt_data($zap, $secret_key);
    $tempo_mercado_criptografado = encrypt_data($tempo_mercado, $secret_key);

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o e-mail já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM premiacao WHERE email = :email");
        $stmt->bindParam(':email', $email_criptografado); // Verifica o e-mail criptografado
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = 'O e-mail informado já foi utilizado. Por favor, forneça um e-mail diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/premiadas.php");
            exit();
        }

        // Inserindo os dados criptografados no banco de dados
        $stmt = $conn->prepare("INSERT INTO premiacao (nome, email, whatsapp, tempo_mercado, site_apostas, faturamento_medio, faturamento_maximo) VALUES (:nome, :email, :zap, :tempo_mercado, :site_apostas, :faturamento_medio, :faturamento_maximo)");
        $stmt->bindParam(':nome', $nome_criptografado); // Insere o nome criptografado
        $stmt->bindParam(':email', $email_criptografado); // Insere o e-mail criptografado
        $stmt->bindParam(':zap', $zap_criptografado); // Insere o WhatsApp criptografado
        $stmt->bindParam(':tempo_mercado', $tempo_mercado_criptografado); // Insere o tempo de mercado criptografado
        $stmt->bindParam(':site_apostas', $site_apostas); // Insere o site de apostas sem criptografia
        $stmt->bindParam(':faturamento_medio', $faturamento_medio); // Insere o faturamento médio sem criptografia
        $stmt->bindParam(':faturamento_maximo', $faturamento_maximo); // Insere o faturamento máximo sem criptografia

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
?>
