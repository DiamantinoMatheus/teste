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

    // Processar o upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem = $_FILES['imagem'];
        
        // Verifica se o arquivo é uma imagem
        $imagemTipo = mime_content_type($imagem['tmp_name']);
        if (strpos($imagemTipo, 'image/') !== 0) {
            $_SESSION['message'] = 'Erro: O arquivo enviado não é uma imagem.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/giros.php");
            exit();
        }

        // Define o diretório para armazenar a imagem (baseado no ID ou data)
        $diretorioImagens = __DIR__ . '/../../uploads/' . date('Y-m-d'); // Pasta baseada na data
        if (!is_dir($diretorioImagens)) {
            mkdir($diretorioImagens, 0755, true); // Cria a pasta se não existir
        }

        $nomeImagem = uniqid() . '-' . basename($imagem['name']); // Gera um nome único para a imagem
        $caminhoImagem = $diretorioImagens . '/' . $nomeImagem;

        // Move a imagem para o diretório de uploads
        if (!move_uploaded_file($imagem['tmp_name'], $caminhoImagem)) {
            $_SESSION['message'] = 'Erro ao mover o arquivo para o diretório de uploads.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/giros.php");
            exit();
        }
    } else {
        $caminhoImagem = null; // Se não houver imagem, pode definir como nulo
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

        // Criptografa o e-mail antes de salvar
        $email_criptografado = encrypt_email($email, $secret_key);

        // Insere os dados no banco
        $stmt = $conn->prepare("INSERT INTO giros (nome, email, codigo, imagem) VALUES (:nome, :email, :codigo, :imagem)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email_criptografado); // Salva o e-mail criptografado
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':imagem', $nomeImagem); // Salva o nome da imagem

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
