<?php
// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    // Redireciona para a página de login
    header("Location: login.php");
    exit();
}

// Inclui o arquivo de conexão
require_once __DIR__ . '/../../back-php/conexao.php'; // Ajuste o caminho conforme necessário

// Chave de criptografia (mantenha isso seguro, use uma variável de ambiente)
$secret_key = 'sua_chave_super_secreta'; // NÃO armazene isso diretamente no código em produção!

// Função para descriptografar o e-mail (igual à que criamos anteriormente)
function decrypt_email($encrypted_email, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_email), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Define o fuso horário para São Paulo
date_default_timezone_set('America/Sao_Paulo');

try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para selecionar os dados da tabela 'giros'
    $sql = "SELECT nome, email, codigo, created_at FROM giros"; // Ajuste a tabela conforme necessário
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Define o cabeçalho do arquivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="usuarios_giros_gratis.csv"');

    // Abre a saída para escrita
    $output = fopen('php://output', 'w');

    // Escreve o BOM para UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Escreve o cabeçalho do CSV
    fputcsv($output, ['Nome', 'Email', 'Codigo', 'Data/Hora'], separator: ';'); // Usando ponto e vírgula como delimitador

    // Escreve os dados no CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Descriptografa o e-mail antes de exportar
        $row['email'] = decrypt_email($row['email'], $secret_key);

        // Se os dados estão em UTC, converta para o horário de São Paulo
        $dateTime = new DateTime($row['created_at'], new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $row['created_at'] = $dateTime->format('d/m/Y H:i:s');

        // Escreve a linha no CSV
        fputcsv($output, $row, ';'); // Usando ponto e vírgula como delimitador
    }

    // Fecha a conexão e o output
    fclose($output);
    exit();
} catch (PDOException $e) {
    echo "Erro ao exportar dados: " . $e->getMessage();
}
