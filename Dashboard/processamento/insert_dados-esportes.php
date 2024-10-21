<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chave de criptografia (mantenha isso seguro, use uma variável de ambiente)
$secret_key = 'sua_chave_super_secreta'; // NÃO armazene isso diretamente no código em produção!

// Função para criptografar o CPF
function encrypt_cpf($cpf, $key)
{
    $iv = openssl_random_pseudo_bytes(16); // Vetor de inicialização (IV)
    $encrypted_cpf = openssl_encrypt($cpf, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted_cpf . '::' . $iv); // Retorna CPF criptografado + IV
}

// Função para descriptografar o CPF
function decrypt_cpf($encrypted_cpf, $key)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_cpf), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Função para validar CPF
function validar_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf); // Remove caracteres não numéricos
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false; // CPF inválido
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

// Verifique se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    $nome_completo = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
    $placar_primeiro_jogo = filter_input(INPUT_POST, 'primeiro_jogo', FILTER_SANITIZE_SPECIAL_CHARS);
    $placar_segundo_jogo = filter_input(INPUT_POST, 'segundo_jogo', FILTER_SANITIZE_SPECIAL_CHARS);
    $placar_terceiro_jogo = filter_input(INPUT_POST, 'terceiro_jogo', FILTER_SANITIZE_SPECIAL_CHARS);

    $codigo = str_replace(' ', '', $codigo);

    if (!validar_cpf($cpf)) {
        $_SESSION['message'] = 'CPF inválido. Por favor, insira um CPF válido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    if (!$nome_completo || !$cpf || !$codigo || !$placar_primeiro_jogo || !$placar_segundo_jogo || !$placar_terceiro_jogo) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Criptografa o CPF fornecido
        $cpf_encrypted = encrypt_cpf($cpf, $secret_key);

        // Verifica se o CPF já existe no banco de dados (comparando CPF descriptografado)
        $checkStmt = $conn->prepare("SELECT cpf FROM esportes");
        $checkStmt->execute();
        $cpf_exists = false;

        // Loop para verificar se algum CPF descriptografado bate com o fornecido
        while ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $stored_cpf_encrypted = $row['cpf'];
            $stored_cpf_decrypted = decrypt_cpf($stored_cpf_encrypted, $secret_key);

            if ($stored_cpf_decrypted === $cpf) {
                $cpf_exists = true;
                break;
            }
        }

        if ($cpf_exists) {
            $_SESSION['message'] = 'O CPF já está registrado.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        // Verifica se o id_conta_reals já existe na tabela
        $checkIdStmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE id_conta_reals = :codigo");
        $checkIdStmt->bindParam(':codigo', $codigo);
        $checkIdStmt->execute();
        $id_exists = $checkIdStmt->fetchColumn();

        if ($id_exists > 0) {
            $_SESSION['message'] = 'O ID da conta já está registrado. Por favor, forneça um ID diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        // Insere os dados no banco com os novos nomes de placar
        $stmt = $conn->prepare("INSERT INTO esportes (nome_completo, cpf, id_conta_reals, 
            placar_primeiro_jogo, placar_segundo_jogo, placar_terceiro_jogo) 
            VALUES (:nome, :cpf, :codigo, :placar_primeiro_jogo, :placar_segundo_jogo, :placar_terceiro_jogo)");

        $stmt->bindParam(':nome', $nome_completo);
        $stmt->bindParam(':cpf', $cpf_encrypted);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':placar_primeiro_jogo', $placar_primeiro_jogo);
        $stmt->bindParam(':placar_segundo_jogo', $placar_segundo_jogo);
        $stmt->bindParam(':placar_terceiro_jogo', $placar_terceiro_jogo);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Formulário enviado com sucesso!';
            $_SESSION['messageClass'] = 'success';
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
