<?php
// Define o fuso horário para o horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão
require_once __DIR__ . '/../../back-php/conexao.php';

// Função para descriptografar o CPF
function decrypt_data($data)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'AES-256-CBC', 'sua_chave_super_secreta', 0, $iv); // Usando a mesma chave secreta e método
}

try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para selecionar os dados da tabela 'esportes'
    // Ajuste o horário para o fuso de São Paulo (UTC-3) caso o horário esteja em UTC no banco de dados
    $sql = "SELECT nome_completo, cpf, id_conta_reals, placar_exato_rm_villareal,
placar_exato_bahia_flamengo, placar_exato_rb_braga_palmeiras, 
DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '-03:00'), '%Y-%m-%d %H:%i:%s') as created_at 
FROM esportes";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Define o cabeçalho do arquivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="usuarios_esportes.csv"');

    // Abre a saída para escrita
    $output = fopen('php://output', 'w');

    // Escreve o BOM para UTF-8 (caso necessário para compatibilidade de encoding)
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Escreve o cabeçalho do CSV
    fputcsv($output, ['Nome', 'CPF', 'ID Reals', 'Placar RM vs Villareal', 'Placar Bahia vs Flamengo', 'Placar Braga vs Palmeiras', 'Data/Hora'], ';');

    // Escreve os dados no CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Descriptografa o CPF antes de escrever no CSV
        $row['cpf'] = decrypt_data(trim($row['cpf'])); // Remove espaços extras no CPF

        // Remove espaços em branco extras nas bordas de cada dado
        $row = array_map('trim', $row);

        // Escreve a linha no CSV
        fputcsv($output, $row, ';');
    }

    // Fecha a conexão e o output
    fclose($output);
    exit();
} catch (PDOException $e) {
    echo "Erro ao exportar dados: " . $e->getMessage();
}
?>