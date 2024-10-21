<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chave de criptografia (mantenha isso seguro, use uma variável de ambiente)
$secret_key = 'sua_chave_super_secreta'; // NÃO armazene isso diretamente no código em produção!

// Função para criptografar o e-mail
function encrypt_email($email, $key)
{
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted_email = openssl_encrypt($email, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted_email . '::' . $iv);
}

// Função para descriptografar o e-mail
function decrypt_email($encrypted_email, $key)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_email), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
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

        // Criptografa o e-mail fornecido
        $email_encrypted = encrypt_email($email, $secret_key);

        // Verifica se o e-mail já foi utilizado
        $checkStmt = $conn->prepare("SELECT email FROM giros");
        $checkStmt->execute();
        $email_exists = false;

        // Loop para verificar se algum e-mail descriptografado bate com o fornecido
        while ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $stored_email_encrypted = $row['email'];
            $stored_email_decrypted = decrypt_email($stored_email_encrypted, $secret_key);

            if ($stored_email_decrypted === $email) {
                $email_exists = true;
                break;
            }
        }

        // Verifica se o e-mail já foi utilizado
        if ($email_exists) {
            $_SESSION['message'] = 'O código ou e-mail informado já foi utilizado. Por favor, forneça um código ou e-mail diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/giros.php");
            exit();
        }

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO giros (nome, email, codigo) VALUES (:nome, :email, :codigo)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email_encrypted); // Salva o e-mail criptografado
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
?>
