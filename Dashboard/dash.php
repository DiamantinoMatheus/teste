<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// // Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
require_once "../Dashboard/processamento/Auth.php";

require_once '../back-php/conexao.php'; // Inclua o arquivo de conexão

try {
    // Cria uma nova conexão PDO
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para recuperar eventos de todas as tabelas
    $sqlEventos = "
        SELECT id, titulo, imagem, banner, 'premiacao' AS tipo FROM eventos_premiacao
        UNION ALL
        SELECT id, titulo, imagem, banner, 'giros' AS tipo FROM eventos_giros
        UNION ALL
        SELECT id, titulo, imagem, banner, 'esportes' AS tipo FROM eventos_esportes
        UNION ALL
        SELECT id, titulo, imagem, banner, 'ticket' AS tipo FROM eventos_ticket
    ";

    $stmtEventos = $conn->prepare($sqlEventos);
    $stmtEventos->execute();
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    // ID do evento que você deseja buscar
    $idEvento = 1; // Ajuste conforme necessário

    // Consultas para estados dos eventos e contagens
    $sqlEstados = "
        SELECT 
            (SELECT formulario_aberto FROM eventos_giros WHERE id = :idEvento LIMIT 1) AS estadoGiros,
            (SELECT interative FROM eventos_premiacao WHERE id = :idEvento LIMIT 1) AS estadoPremios,
            (SELECT interative FROM eventos_esportes WHERE id = :idEvento LIMIT 1) AS estadoEsportes,
            (SELECT interative FROM eventos_ticket WHERE id = :idEvento LIMIT 1) AS estadoTicket,
            (SELECT COUNT(*) FROM eventos_premiacao) AS count_premiacao,
            (SELECT COUNT(*) FROM eventos_giros) AS count_giros,
            (SELECT COUNT(id) FROM user) AS total_ids_user
    ";

    $stmtEstados = $conn->prepare($sqlEstados);
    $stmtEstados->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
    $stmtEstados->execute();
    $resultEstados = $stmtEstados->fetch(PDO::FETCH_ASSOC);

    // Extraindo os resultados
    $estadoGiros = $resultEstados['estadoGiros'] ?? 2; // 1 para aberto, 2 para fechado
    $estadoPremios = $resultEstados['estadoPremios'] ?? 2; // 1 para aberto, 2 para fechado
    $estadoEsportes = $resultEstados['estadoEsportes'] ?? 2; // 1 para aberto, 2 para fechado
    $estadoTicket = $resultEstados['estadoTicket'] ?? 2; // 1 para aberto, 2 para fechado

    // Calcula o total combinando as contagens das duas tabelas
    $total_ids = ($resultEstados['count_premiacao'] ?? 0) + ($resultEstados['count_giros'] ?? 0);
    $total_ids_user = $resultEstados['total_ids_user'] ?? 0;

} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . htmlspecialchars($e->getMessage());
    // Definindo valores padrão em caso de erro
    $estadoGiros = $estadoPremios = $estadoEsportes = $estadoTicket = 3; // 3 para erro
    $total_ids = $total_ids_user = 0;
} finally {
    // Fecha a conexão com o banco de dados
    $conn = null;
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">

    <title>Painel</title>
    <link rel="stylesheet" href="./css/dash.css" media="print" onload="this.media='all'" rel="preload">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@latest/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <!-- Barra de navegação -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <img src="../img/logo.webp" class="logo" alt="Imagem do Evento" loading="lazy"
                style="width: 100px; height: auto;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto nav-list justify-content-center barra">
                    <li class="nav-item nav-item-custom link">
                        <a href="dash.php" class="button1 sair">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./funcionario.php" class="button1 sair">
                            Funcionários
                        </a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./Usuarios.php" class="button1 sair">
                            Usuários
                        </a>
                    </li>
                    <li class="nav-item nav-item-custom link">
                        <a href="./processamento/logout.php" class="button1 sair">
                            Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Fim da Barra de Navegação -->


    <main class="container">

        <div class="row d-flex justify-content-left  mt-4">
            <!-- Card para Lives -->
            <div class="col-md-12 mb-4 card-container ">
                <div class="card">
                    <div class="card-body ">
                        <h5 style="padding: 10px; font-size: 25px;" class="card-title text-center"> <i
                                style="margin-right: 10px;" class="bi bi-clipboard-pulse"></i>Formulários</h5>
                        <!-- Container flexível para os botões -->
                        <div class="button-container" style="margin-top: 20px;">
                            <!-- Botão para Status de Prêmios com ícone -->
                            <form method="POST" action="./processamento/statusForms_Premios.php" class="form-status">
                                <input type="hidden" name="eventoId1" value="7">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-premios"></i> Prêmios
                                </button>
                            </form>

                            <!-- Botão para Status de Giros com ícone -->
                            <form method="POST" action="./processamento/statusForms.php" class="form-status">
                                <input type="hidden" name="eventoId3" value="6">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-giros"></i> Giros
                                </button>
                            </form>

                            <!-- Botão para Status de Prêmios com ícone -->
                            <form method="POST" action="./processamento/statusForms_Esportes.php" class="form-status">
                                <input type="hidden" name="eventoId4" value="1">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-premios"></i> Esportes
                                </button>
                            </form>

                            <!-- Botão para Status de Ticket com ícone -->
                            <form method="POST" action="./processamento/statusForms_Ticket.php" class="form-status">
                                <input type="hidden" name="eventoId5" value="3">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-premios"></i> Ticket
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <!-- <div class="container mt-5">
                <h2 class="mb-4 titulo">Adicionar Evento</h2>
                <div class="form-group">
                    <label for="tipoFormulario" class="mb-3">Selecione o tipo de formulário:</label>
                    <select id="tipoFormulario" class="form-control" onchange="mostrarFormulario(this.value)">
                        <option value="">Escolha...</option>
                        <option value="giro">Formulário de Giro</option>
                        <option value="premiacao">Formulário de Premiação</option>
                        <option value="esportes">Formulário de Esportes</option>
                        <option value="ticket">Formulário de Ticket</option>
                    </select>
                </div>
            </div>

            <div id="formularioGiro" style="display: none; margin-top: 20px;">
                <form action="./processamento/adicionar_evento_giros.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 mt-3">
                        <label for="imagemGiro" class="form-label">Banner:</label>
                        <input type="file" class="form-control" id="imagemGiro" name="banner" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="tituloGiro" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="tituloGiro" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagemGiro" class="form-label">Imagem:</label>
                        <input type="file" class="form-control" id="imagemGiro" name="imagem" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn_envio">Adicionar Evento de Giro</button>
                </form>
            </div>

            <div id="formularioPremiacao" style="display: none; margin-top: 20px;">
                <form action="./processamento/adicionar_evento_premios.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imagemPremiacao" class="form-label">Banner:</label>
                        <input type="file" class="form-control" id="imagemPremiacao" name="banner" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="tituloPremiacao" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="tituloPremiacao" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagemPremiacao" class="form-label">Imagem:</label>
                        <input type="file" class="form-control" id="imagemPremiacao" name="imagem" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn_envio">Adicionar Evento de Premiação</button>
                </form>
            </div>

            <div id="formularioEsportes" style="display: none; margin-top: 20px;">
                <form action="./processamento/adicionar_evento_esportes.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imagemEsportes" class="form-label">Banner:</label>
                        <input type="file" class="form-control" id="imagemEsportes" name="banner" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="tituloPremiacao" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="tituloPremiacao" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagemEsportes" class="form-label">Imagem:</label>
                        <input type="file" class="form-control" id="imagemEsportes" name="imagem" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn_envio">Adicionar Evento de Esportes</button>
                </form>
            </div>

            <div id="formularioTicket" style="display: none; margin-top: 20px;">
                <form action="./processamento/adicionar_evento_ticket.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imagemTicket" class="form-label">Banner:</label>
                        <input type="file" class="form-control" id="imagemTicket" name="banner" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="tituloTicket" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="tituloTicket" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagemTicket" class="form-label">Imagem:</label>
                        <input type="file" class="form-control" id="imagemTicket" name="imagem" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn_envio">Adicionar Evento de Ticket</button>
                </form>
            </div> -->

            <div class="container">
    <div class="row">
        <?php if ($eventos && count($eventos) > 0): ?>
            <?php foreach ($eventos as $evento): ?>
                <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
                    <div class="card">
                        <div class="d-flex mt-1 mb-1 justify-content-center">
                            <button type="button" class="button-edit delete-button" data-bs-toggle="modal" 
                                data-bs-target="#editModal<?= htmlspecialchars($evento['id']); ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                        </div>

                        <!-- Exibição do Banner -->
                        <?php if (!empty($evento['banner'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($evento['banner']); ?>" 
                                class="card-img-top" alt="Imagem do Evento" 
                                style="height: 50%; object-fit: cover;" loading="lazy">
                        <?php endif; ?>

                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($evento['titulo']); ?></h5>

                            <!-- Modal de Editar -->
                            <div class="modal fade" id="editModal<?= $evento['id']; ?>" tabindex="-1" 
                                aria-labelledby="editModalLabel<?= $evento['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $evento['id']; ?>">Editar Evento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="./processamento/editar_evento.php" method="POST" 
                                                enctype="multipart/form-data">
                                                <input type="hidden" name="id" value="<?= $evento['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="titulo<?= $evento['id']; ?>" class="form-label">Título:</label>
                                                    <input type="text" class="form-control" id="titulo<?= $evento['id']; ?>" 
                                                        name="titulo" value="<?= htmlspecialchars($evento['titulo']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="banner<?= $evento['id']; ?>" class="form-label">Banner:</label>
                                                    <input type="file" class="form-control" id="banner<?= $evento['id']; ?>" 
                                                        name="banner">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="imagem<?= $evento['id']; ?>" class="form-label">Imagem:</label>
                                                    <input type="file" class="form-control" id="imagem<?= $evento['id']; ?>" 
                                                        name="imagem">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="button-delete button-option">Salvar Mudanças</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Nenhum evento encontrado.</p>
        <?php endif; ?>
    </div>
</div>
            </form>
        </div>
        <!-- Agenda Form Fim -->

    </main>

    <script defer>
       
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"
        defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh+WyJ0I72fLevddux1FRXr+8f77kyJyE05bM" crossorigin="anonymous"
        defer></script>

</body>

</html>