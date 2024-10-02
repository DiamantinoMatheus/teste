<?php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de configuração
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtém e sanitiza os valores enviados no formulário de login
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

    // Mensagens de depuração (desativar em produção)
    // echo "<pre>Dados enviados:</pre>";
    // var_dump($email);
    // var_dump($senha);

    try {
        // Cria uma nova conexão PDO
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepara a consulta SQL para verificar o usuário pelo e-mail
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Mensagens de depuração (desativar em produção)
        // echo "<pre>Consulta SQL executada: ";
        // var_dump($stmt->queryString);
        // echo "</pre>";

        // Verifica se a consulta retornou algum resultado
        if ($stmt->rowCount() > 0) {
            // Obtém os dados do usuário
            $dadosEmail = $stmt->fetch(PDO::FETCH_ASSOC);

            // Mensagens de depuração (desativar em produção)
            // echo "<pre>Dados do banco de dados:</pre>";
            // var_dump($dadosEmail);

            // Verifica a senha fornecida usando password_verify
            if (password_verify($senha, $dadosEmail['senha'])) {
                // Armazena os dados do usuário na sessão
                $_SESSION['email'] = $dadosEmail['email'];

                // Mensagem de sucesso
                // echo "<pre>Senha verificada com sucesso!</pre>";

                // Redireciona para o dashboard
                header("Location: ../dash.php");
                exit();
            } else {
                // Senha incorreta, redireciona para a página de login
                $_SESSION['error'] = "Senha incorreta. Tente novamente.";
                // echo "<pre>Senha incorreta.</pre>";
                header("Location: ../login.php");
                exit();
            }
        } else {
            // Email não encontrado, redireciona para a página de login
            $_SESSION['error'] = "Email não encontrado. Tente novamente.";
            // echo "<pre>Email não encontrado.</pre>";
            header("Location: ../login.php");
            exit();
        }
    } catch (PDOException $e) {
        // Trata erros de conexão PDO
        // Exibe mensagem genérica
        $_SESSION['error'] = "Ocorreu um erro. Tente novamente mais tarde.";
        // Registra o erro detalhado para a equipe de desenvolvimento
        error_log("Erro na conexão: " . $e->getMessage());
        header("Location: ../login.php");
        exit();
    }
}

// Verifica se o usuário está autenticado
if (isset($_SESSION['email'])) {
    // Mensagem de sucesso (opcional)
    // echo "<pre>Usuário autenticado: " . $_SESSION['email'] . "</pre>";
} else {
    header("Location: ../login.php");
    exit();
}
?>
