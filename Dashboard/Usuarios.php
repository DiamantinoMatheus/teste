<?php
// Iniciar a sessão
if (!session_id()) {
    session_start();
}

// Incluir o arquivo de conexão
require_once '../back-php/conexao.php';

try {
    // Conectar ao banco de dados
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para contar os eventos de ambas as tabelas
    $sqlGiros = "SELECT COUNT(id) FROM giros";
    $sqlPremiacao = "SELECT COUNT(id) FROM premiacao";
    $sqlEsportes = "SELECT COUNT(id) FROM esportes";
    $sqlTicket = "SELECT COUNT(id) FROM ticket";

    // Executar as consultas
    $stmtGiros = $conn->prepare($sqlGiros);
    $stmtGiros->execute();
    $giroCount = $stmtGiros->fetchColumn();

    $stmtPremiacao = $conn->prepare($sqlPremiacao);
    $stmtPremiacao->execute();
    $premiacaoCount = $stmtPremiacao->fetchColumn();

    $stmtEsportes = $conn->prepare($sqlEsportes);
    $stmtEsportes->execute();
    $EsportesCount = $stmtEsportes->fetchColumn();

    $stmtTicket = $conn->prepare($sqlTicket);
    $stmtTicket->execute();
    $TicketCount = $stmtTicket->fetchColumn();
} catch (PDOException $e) {
    echo "Erro ao recuperar eventos: " . $e->getMessage();
} finally {
    // Fechar a conexão
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteio de Registros</title>
    <link rel="stylesheet" href="css/users.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a href="dash.php" class="navbar-brand">
                <img src="../img/logo.webp" class="logo" alt="Imagem do Evento" loading="lazy" width="auto"
                    height="auto"> </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto nav-list justify-content-center barra">
                    <li class="nav-item nav-item-custom link">
                        <a href="dash.php" class="button1 sair">Dashboard</a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./funcionario.php" class="button1 sair">Funcionários</a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./Usuarios.php" class="button1 sair">Usuários</a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./processamento/logout.php" class="button1 sair">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Exportar Dados</h5>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center"
                style="height: 100%; flex-direction: column;">
                <div class="mb-3 d-flex justify-content-around w-100">
                    <div class="text-center">
                        <p><strong><?php echo $giroCount; ?></strong></p>
                        <form method="post" action="../Dashboard/processamento/exportar_giros.php">
                            <button name="exportar" value="giros" class="btn_envio">Exportar Giros</button>
                        </form>
                    </div>
                    <div class="text-center">
                        <p><strong><?php echo $premiacaoCount; ?></strong></p>
                        <form method="post" action="../Dashboard/processamento/exportar_premiacao.php">
                            <button name="exportar" value="premiacao" class="btn_envio">Exportar Premiação</button>
                        </form>
                    </div>
                    <div class="text-center">
                        <p><strong><?php echo $EsportesCount; ?></strong></p>
                        <form method="post" action="../Dashboard/processamento/exportar_esportes.php">
                            <button name="exportar" value="esportes" class="btn_envio">Exportar Esportes</button>
                        </form>
                    </div>
                    <div class="text-center">
                        <p><strong><?php echo $TicketCount; ?></strong></p>
                        <form method="post" action="../Dashboard/processamento/exportar_ticket.php">
                            <button name="exportar" value="ticket" class="btn_envio">Exportar Ticket</button>
                        </form>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="text-center"><i style="margin-right:5px" class="bi bi-person-fill-x mb-2"></i>Exclusão de
                Registros</h5>
            <div class="button-container d-flex align-items-center mt-4">
                <script>
                    function confirmDelete() {
                        return confirm("Você realmente quer deletar os registros?");
                    }
                </script>

                <form method="POST" action="./processamento/delete_users_giros.php" class="form-status"
                    onsubmit="return confirmDelete();">
                    <input type="hidden" name="eventoId3" value="6">
                    <button class="btn mb-2" type="submit">
                        <i class="bi bi-trash3-fill"></i> Giros
                    </button>
                </form>

                <form method="POST" action="./processamento/delete_users_premiacao.php" class="form-status"
                    onsubmit="return confirmDelete();">
                    <input type="hidden" name="eventoId1" value="7">
                    <button class="btn mb-2" type="submit">
                        <i class="bi bi-trash3-fill"></i> Prêmios
                    </button>
                </form>

                <form method="POST" action="./processamento/delete_users_esportes.php" class="form-status"
                    onsubmit="return confirmDelete();">
                    <input type="hidden" name="eventoId4" value="1">
                    <button class="btn mb-2" type="submit">
                        <i class="bi bi-trash3-fill"></i> Esportes
                    </button>
                </form>

                <form method="POST" action="./processamento/delete_users_ticket.php" class="form-status"
                    onsubmit="return confirmDelete();">
                    <input type="hidden" name="eventoId5" value="1">
                    <button class="btn mb-2" type="submit">
                        <i class="bi bi-trash3-fill"></i> Ticket
                    </button>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh+WyJ0I72fLevddux1FRXr+8f77kyJyE05bM"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>