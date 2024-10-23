<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir uma chave de criptografia e um método
define('ENCRYPTION_KEY', 'sua-chave-secreta'); // Troque por uma chave secreta segura
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Função para criptografar dados
function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Função para sanitizar dados, removendo < e >
function sanitize_input($data) {
    return str_replace(['<', '>'], '', $data); // Remove apenas < e >
}

// Função para descriptografar dados
function decrypt_data($data) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica o token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/ticket.php");
        exit();
    }

    // Sanitizando os dados de entrada e depois criptografando
    $rg = encrypt_data(filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS));
    $nome = encrypt_data(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
    $endereco = encrypt_data(filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS));
    $instagram = encrypt_data(filter_input(INPUT_POST, 'instagram', FILTER_SANITIZE_SPECIAL_CHARS));

    // Verifica se todos os campos obrigatórios foram preenchidos
    if (!$rg || !$nome) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos obrigatórios.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/ticket.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o RG já foi utilizado (comparação feita com o RG criptografado)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE rg = :rg");
        $stmt->bindParam(':rg', $rg);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = 'O RG informado já foi utilizado. Por favor, forneça um RG diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/ticket.php");
            exit();
        }
        
        // Adicionando um delay de 1 segundo antes de enviar os dados para o banco
        sleep(1); 

        // Inserindo os dados criptografados no banco de dados
        $stmt = $conn->prepare("INSERT INTO ticket (rg, nome, endereco, instagram) VALUES (:rg, :nome, :endereco, :instagram)");
        $stmt->bindParam(':rg', $rg);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':instagram', $instagram);

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

    header("Location: ../../Forms/ticket.php");
    exit();
}
