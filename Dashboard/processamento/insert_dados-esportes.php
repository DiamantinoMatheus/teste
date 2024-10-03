<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificação do token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    // Função para verificar se há sinais proibidos
    function contains_invalid_characters($input)
    {
        return strpos($input, '>') !== false || strpos($input, '<') !== false;
    }

    // Sanitização dos campos
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_NUMBER_INT);
    $villareal = filter_input(INPUT_POST, 'villareal', FILTER_SANITIZE_SPECIAL_CHARS);
    $bahia = filter_input(INPUT_POST, 'bahia', FILTER_SANITIZE_SPECIAL_CHARS);
    $palmeiras = filter_input(INPUT_POST, 'palmeiras', FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);

    // Verificação de caracteres proibidos
    if (
        contains_invalid_characters($nome) || contains_invalid_characters($cpf) || contains_invalid_characters($codigo) ||
        contains_invalid_characters($villareal) || contains_invalid_characters($bahia) || contains_invalid_characters($palmeiras)
    ) {
        $_SESSION['message'] = 'Erro: Campos contêm caracteres inválidos (> ou <).';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    // Verificação se todos os campos obrigatórios foram preenchidos
    if (!$nome || !$cpf || !$codigo || !$villareal || !$bahia || !$palmeiras) {
        $_SESSION['message'] = 'Dados inválidos. Por favor, preencha todos os campos corretamente.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/esportes.php");
        exit();
    }

    // Conexão ao banco de dados
    include_once(__DIR__ . '/../../back-php/conexao.php');

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificação se o CPF já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE cpf = :cpf");
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        $cpfCount = $stmt->fetchColumn();

        if ($cpfCount > 0) {
            $_SESSION['message'] = 'O CPF informado já foi utilizado. Por favor, forneça um CPF diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        // Verificação se o código já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM esportes WHERE id_conta_reals = :codigo");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $codigoCount = $stmt->fetchColumn();

        if ($codigoCount > 0) {
            $_SESSION['message'] = 'O código informado já foi utilizado. Por favor, forneça um código diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
            exit();
        }

        // Inserção dos dados no banco
        $stmt = $conn->prepare("INSERT INTO esportes (nome_completo, cpf, id_conta_reals, placar_exato_rm_villareal, placar_exato_bahia_flamengo, placar_exato_rb_braga_palmeiras) 
                                VALUES (:nome, :cpf, :codigo, :villareal, :bahia, :palmeiras)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':villareal', $villareal);
        $stmt->bindParam(':bahia', $bahia);
        $stmt->bindParam(':palmeiras', $palmeiras);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Formulário enviado com sucesso!';
            $_SESSION['messageClass'] = 'success';
            header("Location: ../../Forms/esportes.php");
        } else {
            $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/esportes.php");
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Ocorreu um erro ao enviar o formulário. Tente novamente.';
        $_SESSION['messageClass'] = 'error';
    }

    exit();
}
