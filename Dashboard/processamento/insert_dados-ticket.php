<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica o token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = 'Erro: Token CSRF inválido.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/ticket.php");
        exit();
    }

    // Sanitizando os dados de entrada
    $rg = filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS);
    $instagram = filter_input(INPUT_POST, 'instagram', FILTER_SANITIZE_SPECIAL_CHARS);

    // Função para verificar se há caracteres proibidos
    function contains_prohibited_chars($input) {
        return preg_match('/[<>]/', $input);  // Proíbe os caracteres < e >
    }

    // Verifica se há caracteres proibidos nos campos
    if (contains_prohibited_chars($rg) || contains_prohibited_chars($nome) || contains_prohibited_chars($endereco) || contains_prohibited_chars($instagram)) {
        $_SESSION['message'] = 'Erro: Caracteres inválidos detectados nos campos.';
        $_SESSION['messageClass'] = 'error';
        header("Location: ../../Forms/ticket.php");
        exit();
    }

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

        // Verifica se o RG já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE rg = :rg");
        $stmt->bindParam(':rg', $rg);
        $stmt->execute();
        $count_rg = $stmt->fetchColumn();

        if ($count_rg > 0) {
            $_SESSION['message'] = 'O RG informado já foi utilizado. Por favor, forneça um RG diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/ticket.php");
            exit();
        }

        // Verifica se o Instagram já foi utilizado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE instagram = :instagram");
        $stmt->bindParam(':instagram', $instagram);
        $stmt->execute();
        $count_instagram = $stmt->fetchColumn();

        if ($count_instagram > 0) {
            $_SESSION['message'] = 'O Instagram informado já foi utilizado. Por favor, forneça um Instagram diferente.';
            $_SESSION['messageClass'] = 'error';
            header("Location: ../../Forms/ticket.php");
            exit();
        }

        // Inserindo os dados no banco de dados
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
