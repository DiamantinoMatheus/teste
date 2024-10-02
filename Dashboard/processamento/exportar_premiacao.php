<?php
// Inicia a sessão
if (!session_id()) {
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

try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para selecionar os dados da tabela 'premiacao'
    $sql = "SELECT nome, email, codigo, whatsapp FROM premiacao"; // Ajuste a tabela conforme necessário
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Define o cabeçalho do arquivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="usuarios_premiacao.csv"');

    // Abre a saída para escrita
    $output = fopen('php://output', 'w');

    // Escreve o BOM para UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Escreve o cabeçalho do CSV
    fputcsv($output, ['Nome', 'Email', 'Codigo', 'WhatsApp'], ';'); // Usando ponto e vírgula como delimitador

    // Escreve os dados no CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ';'); // Usando ponto e vírgula como delimitador
    }

    // Fecha a conexão e o output
    fclose($output);
    exit();
} catch (PDOException $e) {
    echo "Erro ao exportar dados: " . $e->getMessage();
}
