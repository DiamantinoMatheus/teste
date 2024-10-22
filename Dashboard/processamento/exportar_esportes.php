<?php
// Define o fuso horário para o horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão
require_once __DIR__ . '/../../back-php/conexao.php';
// Função para carregar variáveis do arquivo .env
function load_env($file) {
    if (file_exists($file)) {
        $lines = file($file);
        foreach ($lines as $line) {
            // Remove comentários e espaços em branco
            $line = trim($line);
            if (strpos($line, '#') === 0 || empty($line)) {
                continue;
            }
            // Divide a linha em chave e valor
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Define a variável de ambiente
            putenv("$key=$value");
        }
    }
}

// Carrega as variáveis do .env
load_env(__DIR__ . '/keys/SECRET_KEY.env');

// Obtém a chave secreta do ambiente
$secret_key = getenv('SECRET_KEY');

// Função para descriptografar o CPF

function decrypt_data($data, $key)
{
    // Verifica se os dados estão no formato esperado
    $data = base64_decode($data);
    if ($data === false) {
        return null; // Retorna nulo se a decodificação falhar
    }
    
    $parts = explode('::', $data);
    
    // Verifica se a divisão resultou em duas partes
    if (count($parts) !== 2) {
        return null; // Retorna nulo se não houver duas partes
    }

    list($encrypted_data, $iv) = $parts;
    
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}


try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para selecionar os dados da tabela 'esportes'
    // Ajuste o horário para o fuso de São Paulo (UTC-3) caso o horário esteja em UTC no banco de dados
    $sql = "SELECT nome_completo, cpf, id_conta_reals, placar_primeiro_jogo,
            placar_segundo_jogo, placar_terceiro_jogo, 
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
    fputcsv($output, ['Nome', 'CPF', 'ID Reals', 'Placar Primeiro Jogo', 'Placar Segundo Jogo', 'Placar Terceiro Jogo', 'Data/Hora'], ';');

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
