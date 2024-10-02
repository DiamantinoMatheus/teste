<?php
// Função para carregar variáveis de ambiente a partir de um arquivo .env
function loadEnv($file)
{
    if (!file_exists($file)) {
        throw new Exception("Arquivo .env não encontrado: " . realpath($file));
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Remove espaços em branco e ignora linhas de comentário
        $line = trim($line);
        if ($line[0] === '#' || empty($line)) {
            continue;
        }

        // Divide a linha em chave e valor
        [$key, $value] = explode('=', $line, 2) + [NULL, NULL];
        $key = trim($key);
        $value = trim($value);

        if (!empty($key)) {
            // Define a variável de ambiente
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Carrega variáveis de ambiente do arquivo .env
$envFile = __DIR__ . '/Admin/span.env';
if (file_exists($envFile)) {
    try {
        loadEnv($envFile);
    } catch (Exception $e) {
        die("Erro ao carregar variáveis de ambiente: " . $e->getMessage());
    }
} else {
    die("Arquivo .env não encontrado.");
}

$hostname = filter_var($_ENV['DB_HOST'] ?? 'localhost', FILTER_SANITIZE_SPECIAL_CHARS);
$username = filter_var($_ENV['DB_USER'] ?? 'root', FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_var($_ENV['DB_PASS'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
$database = filter_var($_ENV['DB_NAME'] ?? 'default_db', FILTER_SANITIZE_SPECIAL_CHARS);



// Tenta criar a conexão com o banco de dados usando PDO
try {
    $dsn = "mysql:host=$hostname;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    // Configura o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em caso de erro na conexão, exibe a mensagem de erro e encerra o script
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Exemplo de uso da conexão
// $stmt = $pdo->query('SELECT * FROM sua_tabela');
// $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// print_r($results);