<?php

if (!session_id()) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// Inclua o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém e sanitiza os dados do formulário
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Inicializa variáveis para armazenar o conteúdo dos arquivos
    $imagemBlob = null;
    $bannerBlob = null;

    // Função para converter arquivo em blob
    function arquivoParaBlob($file)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            return file_get_contents($file['tmp_name']);
        } else {
            throw new Exception('Erro ao carregar o arquivo.');
        }
    }

    // Verifica e converte a imagem para blob se fornecida
    if (!empty($_FILES['imagem']['name'])) {
        try {
            $imagemBlob = arquivoParaBlob($_FILES['imagem']);
        } catch (Exception $e) {
            die('Erro ao processar a imagem: ' . $e->getMessage());
        }
    }

    // Verifica e converte o banner para blob se fornecido
    if (!empty($_FILES['banner']['name'])) {
        try {
            $bannerBlob = arquivoParaBlob($_FILES['banner']);
        } catch (Exception $e) {
            die('Erro ao processar o banner: ' . $e->getMessage());
        }
    }

    // Atualiza o evento na tabela eventos_premiacao
    try {
        // Usa a conexão PDO definida no arquivo de conexão
        global $pdo;

        // Prepara a query de atualização para eventos_premiacao
        $sql = "UPDATE eventos_premiacao SET titulo = :titulo";
        if ($bannerBlob !== null) {
            $sql .= ", banner = :banner";
        }
        if ($imagemBlob !== null) {
            $sql .= ", imagem = :imagem";
        }
        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        if ($bannerBlob !== null) {
            $stmt->bindParam(':banner', $bannerBlob, PDO::PARAM_LOB);
        }
        if ($imagemBlob !== null) {
            $stmt->bindParam(':imagem', $imagemBlob, PDO::PARAM_LOB);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar o evento na tabela eventos_premiacao.');
        }

        // Prepara a query de atualização para eventos_giros
        $sql = "UPDATE eventos_giros SET titulo = :titulo";
        if ($bannerBlob !== null) {
            $sql .= ", banner = :banner";
        }
        if ($imagemBlob !== null) {
            $sql .= ", imagem = :imagem";
        }
        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        if ($bannerBlob !== null) {
            $stmt->bindParam(':banner', $bannerBlob, PDO::PARAM_LOB);
        }
        if ($imagemBlob !== null) {
            $stmt->bindParam(':imagem', $imagemBlob, PDO::PARAM_LOB);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar o evento na tabela eventos_giros.');
        }

        // Prepara a query de atualização para eventos_esportes
        $sql = "UPDATE eventos_esportes SET titulo = :titulo";
        if ($bannerBlob !== null) {
            $sql .= ", banner = :banner";
        }
        if ($imagemBlob !== null) {
            $sql .= ", imagem = :imagem";
        }
        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        if ($bannerBlob !== null) {
            $stmt->bindParam(':banner', $bannerBlob, PDO::PARAM_LOB);
        }
        if ($imagemBlob !== null) {
            $stmt->bindParam(':imagem', $imagemBlob, PDO::PARAM_LOB);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar o evento na tabela eventos_esportes.');
        }

        // Redireciona após sucesso
        header("Location: ../dash.php");
        exit();
    } catch (PDOException $e) {
        die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }
} else {
    echo "Método de requisição inválido.";
}
