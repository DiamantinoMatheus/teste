<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chave de criptografia (mantenha isso seguro, use uma variável de ambiente)
$secret_key = 'sua_chave_super_secreta'; // NÃO armazene isso diretamente no código em produção!

// Função para criptografar o CPF
function encrypt_cpf($cpf, $key)
{
    // Vetor de inicialização (IV) de 16 bytes (deve ser único para cada criptografia)
    $iv = openssl_random_pseudo_bytes(16);
    // Criptografa o CPF
    $encrypted_cpf = openssl_encrypt($cpf, 'aes-256-cbc', $key, 0, $iv);
    // Retorna o IV junto com o CPF criptografado, pois ele será necessário para descriptografar
    return base64_encode($encrypted_cpf . '::' . $iv);
}

// Função para descriptografar o CPF (usada ao exportar)
function decrypt_cpf($encrypted_cpf, $key)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_cpf), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Verifique se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifique se o token CSRF enviado corresponde ao token na sessão
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    // Obtém e valida os valores do POST
    $nome_completo = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $placar_rm_villareal = filter_input(INPUT_POST, 'villareal', FILTER_SANITIZE_SPECIAL_CHARS);
    $placar_bahia_flamengo = filter_input(INPUT_POST, 'bahia', FILTER_SANITIZE_SPECIAL_CHARS);
    $placar_rb_braga_palmeiras = filter_input(INPUT_POST, 'palmeiras', FILTER_SANITIZE_SPECIAL_CHARS);

    // Verifica se todos os dados são válidos
    if (!$nome_completo || !$cpf || !$codigo || !$placar_rm_villareal || !$placar_bahia_flamengo || !$placar_rb_braga_palmeiras) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        // Cria uma nova conexão PDO
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Criptografa o CPF antes de verificar
        $cpf_encrypted = encrypt_cpf($cpf, $secret_key);

        // Verifica se o CPF já existe no banco de dados
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE cpf = :cpf");
        $checkStmt->bindParam(':cpf', $cpf_encrypted);
        $checkStmt->execute();
        $cpf_exists = $checkStmt->fetchColumn();

        if ($cpf_exists > 0) {
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

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO esportes (nome_completo, cpf, id_conta_reals, 
            placar_exato_rm_villareal, placar_exato_bahia_flamengo, placar_exato_rb_braga_palmeiras) 
            VALUES (:nome, :cpf, :codigo, :placar_rm_villareal, :placar_bahia_flamengo, :placar_rb_braga_palmeiras)");

        $stmt->bindParam(':nome', $nome_completo);
        $stmt->bindParam(':cpf', $cpf_encrypted); // Salva o CPF criptografado
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':placar_rm_villareal', $placar_rm_villareal);
        $stmt->bindParam(':placar_bahia_flamengo', $placar_bahia_flamengo);
        $stmt->bindParam(':placar_rb_braga_palmeiras', $placar_rb_braga_palmeiras);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Dados inseridos com sucesso!';
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
    header("Location: ../../Forms/esportes.php");
    exit();
}
?>
