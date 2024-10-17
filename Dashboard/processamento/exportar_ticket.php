<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir a chave de criptografia
$secret_key = 'sua-chave-secreta'; // Substitua por uma chave mais segura e mantenha isso seguro!
date_default_timezone_set('America/Sao_Paulo'); // Ajuste para o fuso desejado

// Função para descriptografar os dados
function decrypt_data($data, $key)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);
}

include_once(__DIR__ . '/../../back-php/conexao.php');

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Seleciona os dados a serem exportados
    $stmt = $conn->prepare("SELECT rg, nome, endereco, instagram, created_at FROM ticket");
    $stmt->execute();

    // Obtém os resultados
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializa um array para os dados descriptografados
    $decrypted_data = [];

    // Descriptografa os dados
    foreach ($results as $row) {
        // Converte a data para o fuso horário de São Paulo
        $date = new DateTime($row['created_at'], new DateTimeZone('UTC')); // Supondo que esteja armazenado em UTC
        $date->setTimezone(new DateTimeZone('America/Sao_Paulo'));

        $decrypted_data[] = [
            'rg' => decrypt_data($row['rg'], $secret_key),
            'nome' => decrypt_data($row['nome'], $secret_key),
            'endereco' => decrypt_data($row['endereco'], $secret_key),
            'instagram' => decrypt_data($row['instagram'], $secret_key),
            'created_at' => $date->format('Y-m-d H:i:s'), // Formata para o padrão desejado
        ];
    }

    // Para exportar como CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dados_exportados.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['RG', 'Nome', 'Endereço', 'Instagram', 'Data/Hora']); // Cabeçalhos do CSV

    foreach ($decrypted_data as $data) {
        fputcsv($output, $data);
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
