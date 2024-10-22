<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para carregar variáveis do arquivo .env
function load_env($file) {
    if (file_exists($file)) {
        foreach (file($file) as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }
}

// Carrega as variáveis do .env
load_env(__DIR__ . '/keys/SECRET_KEY.env');
$secret_key = getenv('SECRET_KEY');

function encrypt_email($email, $key) {
    $iv = openssl_random_pseudo_bytes(16);
    return base64_encode(openssl_encrypt($email, 'aes-256-cbc', $key, 0, $iv) . '::' . $iv);
}

function hash_email($email) {
    return hash('sha256', $email);
}

function validar_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        if ($cpf[$c] != ((10 * $d) % 11) % 10) return false;
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/premiadas.php");
        exit();
    }

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $codigo = str_replace(' ', '', filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS));
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$nome || !$email || !$codigo || !validar_cpf($cpf)) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/premiadas.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email_encrypted = encrypt_email($email, $secret_key);
        $email_hashed = hash_email($email);
        $cpf_encrypted = encrypt_email($cpf, $secret_key); // Você pode criar uma função específica para CPF, se preferir
        $cpf_hashed = hash_email($cpf); // Utilize hash_email também para o CPF

        // Verifica se o e-mail ou CPF já foram utilizados
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) FROM premiacao 
            WHERE email_hash = :email_hash OR cpf_hash = :cpf_hash
        ");
        $checkStmt->execute([':email_hash' => $email_hashed, ':cpf_hash' => $cpf_hashed]);

        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'O código, e-mail ou CPF informado já foi utilizado. Por favor, forneça dados diferentes.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/premiadas.php");
            exit();
        }

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO premiacao (nome, email, email_hash, codigo, cpf, cpf_hash) VALUES (:nome, :email, :email_hash, :codigo, :cpf, :cpf_hash)");
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email_encrypted,
            ':email_hash' => $email_hashed,
            ':codigo' => $codigo,
            ':cpf' => $cpf_encrypted,
            ':cpf_hash' => $cpf_hashed
        ]);

        $_SESSION['message'] = $stmt->rowCount() ? 'Formulário enviado com sucesso!' : 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = $stmt->rowCount() ? 'success' : 'error';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = 'error';
    }

    header("Location: ../../Forms/premiadas.php");
    exit();
}
?>
