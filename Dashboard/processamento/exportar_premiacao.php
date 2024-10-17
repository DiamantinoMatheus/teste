<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Definindo a chave de criptografia
$secret_key = 'sua_chave_super_secreta'; // NÃO armazene isso diretamente no código em produção!
date_default_timezone_set('America/Sao_Paulo'); // Ajuste para o fuso desejado

// Função para descriptografar os dados
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

// Inclui o arquivo de conexão
include_once(__DIR__ . '/../../back-php/conexao.php');

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para selecionar os dados da tabela 'premiacao'
    $stmt = $conn->prepare("SELECT nome, email, whatsapp, tempo_mercado, site_apostas, faturamento_medio, faturamento_maximo, created_at FROM premiacao");
    $stmt->execute();

    // Obtém os resultados
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializa um array para os dados descriptografados
    $decrypted_data = [];

    // Descriptografa os dados
    foreach ($results as $row) {
        $decrypted_data[] = [
            'nome' => decrypt_data($row['nome'], $secret_key) ?? 'Erro na descriptografia',
            'email' => decrypt_data($row['email'], $secret_key) ?? 'Erro na descriptografia',
            'whatsapp' => decrypt_data($row['whatsapp'], $secret_key) ?? 'Erro na descriptografia',
            'tempo_mercado' => decrypt_data($row['tempo_mercado'], $secret_key) ?? 'Erro na descriptografia',
            'site_apostas' => $row['site_apostas'], // Supondo que não precise de descriptografia
            'faturamento_medio' => $row['faturamento_medio'], // Supondo que não precise de descriptografia
            'faturamento_maximo' => $row['faturamento_maximo'], // Supondo que não precise de descriptografia
            'created_at' => (new DateTime($row['created_at'], new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone('America/Sao_Paulo'))
                ->format('Y-m-d H:i:s'), // Formata para o padrão desejado
        ];
    }

    // Para exportar como CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="usuarios_premiacao.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Nome', 'Email', 'Whatsapp', 'Tempo de Mercado', 'Site de Apostas', 'Faturamento Médio', 'Faturamento Máximo', 'Data/Hora'], ';'); // Cabeçalhos do CSV

    foreach ($decrypted_data as $data) {
        fputcsv($output, $data, ';'); // Usando ponto e vírgula como delimitador
    }

    fclose($output);
    exit(); // Importante para evitar que o restante da página seja enviado

} catch (PDOException $e) {
    $_SESSION['message'] = 'Ocorreu um erro ao acessar os dados.';
    $_SESSION['messageClass'] = 'error';
    header("Location: ../../Forms/ticket.php");
    exit();
}
?>
