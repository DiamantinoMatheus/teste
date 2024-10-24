<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Regenera o token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Função para carregar variáveis do arquivo .env
function load_env($file) {
    if (file_exists($file)) {
        $lines = file($file);
        foreach ($lines as $line) {
            // Remove comentários e espaços em branco
            $line = trim($line);
            if (strpos($line, '#') === 0 || empty($line)) {
                continue;
            }
            // Divide a linha em chave e valor
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Define a variável de ambiente
            putenv("$key=$value");
        }
    }
}

// Carrega as variáveis do .env
load_env(__DIR__ . '/keys/SECRET_KEY.env');

// Obtém a chave secreta do ambiente
$secret_key = getenv('SECRET_KEY');

function encrypt_cpf($cpf, $key) {
    $iv = openssl_random_pseudo_bytes(16);
    return base64_encode(openssl_encrypt($cpf, 'aes-256-cbc', $key, 0, $iv) . '::' . $iv);
}

function hash_cpf($cpf) {
    return hash('sha256', $cpf);
}
function validar_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
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
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    $nome_completo = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo = str_replace(' ', '', filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS));
    $placares = array_map(function($field) {
        return filter_input(INPUT_POST, $field, FILTER_SANITIZE_SPECIAL_CHARS);
    }, ['primeiro_jogo', 'segundo_jogo', 'terceiro_jogo', 'quarto_jogo']);

    if (!validar_cpf($cpf) || !$nome_completo || !$codigo || in_array('', $placares)) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $cpf_encrypted = encrypt_cpf($cpf, $secret_key);
        $cpf_hashed = hash_cpf($cpf);

        $checkCpfStmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE cpf_hash = :cpf_hash");
        $checkCpfStmt->execute([':cpf_hash' => $cpf_hashed]);
        
        if ($checkCpfStmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'O CPF já está registrado.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        $checkIdStmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE id_conta_reals = :codigo");
        $checkIdStmt->execute([':codigo' => $codigo]);
        
        if ($checkIdStmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'O ID da conta já está registrado. Por favor, forneça um ID diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        // Adicionando um delay de 1 segundo antes de enviar os dados para o banco
        sleep(1); 

        $stmt = $conn->prepare("INSERT INTO esportes (nome_completo, cpf, cpf_hash, id_conta_reals, placar_primeiro_jogo, placar_segundo_jogo, placar_terceiro_jogo, placar_quarto_jogo) 
            VALUES (:nome, :cpf, :cpf_hash, :codigo, :placar_primeiro_jogo, :placar_segundo_jogo, :placar_terceiro_jogo, :placar_quarto_jogo)");

        $params = [
            ':nome' => $nome_completo,
            ':cpf' => $cpf_encrypted,
            ':cpf_hash' => $cpf_hashed,
            ':codigo' => $codigo,
            ':placar_primeiro_jogo' => $placares[0],
            ':placar_segundo_jogo' => $placares[1],
            ':placar_terceiro_jogo' => $placares[2],
            ':placar_quarto_jogo' => $placares[3]
        ];

        if ($stmt->execute($params)) {
            $_SESSION['message'] = 'Formulário enviado com sucesso!';
            $_SESSION['messageClass'] = 'success';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
            $_SESSION['messageClass'] = 'error';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Erro no banco de dados: ' . $e->getMessage();
        $_SESSION['messageClass'] = 'error';
    }

    header("Location: ../../Forms/esportes.php");
    exit();
}
?>
