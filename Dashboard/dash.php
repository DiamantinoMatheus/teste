<?php

// Start session
if (!session_id()) {
    session_start();
}

// // Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
require_once "../Dashboard/processamento/Auth.php";

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">

    <title>Painel</title>
    <link rel="stylesheet" href="css/dashs.css">
    <link rel="icon" href="./img/logo.png" />

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
            <a href="dash.php" class="navbar-brand">
                <img src="../img/logo.webp" alt="logo" class="logo" />
            </a>
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
        <h1 class="mt-4 mb-4 titulo">Painel Geral</h1>
        <?php
        $total_ids = 0; // Inicializa a variável para armazenar o total de IDs

        try {
            // Cria uma nova conexão PDO
            $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
            // Define o modo de erro PDO para exceções
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Consulta SQL para contar o número total de registros em ambas as tabelas
            $sql = "
        SELECT 
            (SELECT COUNT(*) FROM eventos_premiacao) AS count_premiacao,
            (SELECT COUNT(*) FROM eventos_giros) AS count_giros
    ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calcula o total combinando as contagens das duas tabelas
            $total_ids = ($result['count_premiacao'] !== null ? $result['count_premiacao'] : 0) +
                ($result['count_giros'] !== null ? $result['count_giros'] : 0);
        } catch (PDOException $e) {
            echo "Erro ao recuperar dados: " . $e->getMessage();
        } finally {
            // Fecha a conexão com o banco de dados
            $conn = null;
        }

        ?>


        <?php
        $total_ids_user = 0; // Inicializa a variável para armazenar o total de IDs da tabela user

        try {
            // Cria uma nova conexão PDO
            $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
            // Define o modo de erro PDO para exceções
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Consulta SQL para contar o número total de IDs na tabela user
            $sql = "SELECT COUNT(id) AS total_ids_user FROM user";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se o resultado não é nulo e define o valor padrão se necessário
            $total_ids_user = $result['total_ids_user'] !== null ? $result['total_ids_user'] : 0;
        } catch (PDOException $e) {
            echo "Erro ao recuperar dados: " . $e->getMessage();
        } finally {
            // Fecha a conexão com o banco de dados
            $conn = null;
        }


        ?>

        <?php
        // Cria uma nova conexão PDO
        try {
            $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
            // Define o modo de erro PDO para exceções
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // ID do evento que você deseja buscar
            $idEvento = 1; // Ajuste conforme necessário

            // Primeira consulta
            $queryGiros = "SELECT formulario_aberto FROM eventos_giros WHERE id = :idEvento LIMIT 1";
            $stmtGiros = $conn->prepare($queryGiros);
            $stmtGiros->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmtGiros->execute();
            $resultGiros = $stmtGiros->fetch(PDO::FETCH_ASSOC);
            $estadoGiros = $resultGiros ? $resultGiros['formulario_aberto'] : 2; // 1 para aberto, 2 para fechado

            // Segunda consulta
            $queryPremios = "SELECT interative FROM eventos_premiacao WHERE id = :idEvento LIMIT 1"; // Ajuste o nome da tabela conforme necessário
            $stmtPremios = $conn->prepare($queryPremios);
            $stmtPremios->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmtPremios->execute();
            $resultPremios = $stmtPremios->fetch(PDO::FETCH_ASSOC);
            $estadoPremios = $resultPremios ? $resultPremios['formulario_aberto'] : 2; // 1 para aberto, 2 para fechado

            // Terceira consulta
            $queryEsportes = "SELECT interative FROM eventos_esportes WHERE id = :idEvento LIMIT 1"; // Ajuste o nome da tabela conforme necessário
            $stmtEsportes = $conn->prepare($queryEsportes);
            $stmtEsportes->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmtEsportes->execute();
            $resultEsportes = $stmtEsportes->fetch(PDO::FETCH_ASSOC);
            $estadoEsportes = $resultEsportes ? $resultEsportes['interative'] : 2; // 1 para aberto, 2 para fechado

        } catch (PDOException $e) {
            echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
            $estadoGiros = $estadoPremios = $estadoEsportes = 3; // Valor padrão em caso de erro
        }
        ?>



        <div class="row d-flex justify-content-left">
            <!-- Card para Lives -->
            <div class="col-md-12 mb-4 card-container ">
                <div class="card">
                    <div class="card-body ">
                        <i class="bi bi-clipboard-pulse fa-2x mb-2"></i>
                        <h5 class="card-title">Formulários</h5>
                        <!-- Container flexível para os botões -->
                        <div class="button-container">
                            <!-- Botão para Status de Giros com ícone -->
                            <form method="POST" action="./processamento/statusForms.php" class="form-status">
                                <input type="hidden" name="eventoId3" value="6">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-giros"></i> Giros
                                </button>
                            </form>

                            <!-- Botão para Status de Prêmios com ícone -->
                            <form method="POST" action="./processamento/statusForms_Premios.php" class="form-status">
                                <input type="hidden" name="eventoId1" value="7">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-premios"></i> Prêmios
                                </button>
                            </form>

                            <!-- Botão para Status de Prêmios com ícone -->
                            <form method="POST" action="./processamento/statusForms_Esportes.php" class="form-status">
                                <input type="hidden" name="eventoId4" value="1">
                                <button class="button-option" type="submit">
                                    <i class="fas fa-unlock" id="icon-premios"></i> Esportes
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <script>
                function setupFormListeners() {
                    const formGiros = document.querySelector("input[name='eventoId3']").closest('.form-status');
                    const formPremios = document.querySelector("input[name='eventoId1']").closest('.form-status');
                    const formEsportes = document.querySelector("input[name='eventoId4']").closest('.form-status');

                    const submitGirosButton = document.getElementById("submitGiros");
                    const submitPremiosButton = document.getElementById("submitPremios");
                    const submitEsportesButton = document.getElementById("submitPremioEsportes");

                    // Função para trocar ícone no clique
                    function toggleIcon(iconId) {
                        const icon = document.getElementById(iconId);
                        if (icon.classList.contains('fa-unlock')) {
                            icon.classList.remove('fa-unlock');
                            icon.classList.add('fa-lock');
                            localStorage.setItem(iconId, 'locked'); // Salva o estado como "locked"
                        } else {
                            icon.classList.remove('fa-lock');
                            icon.classList.add('fa-unlock');
                            localStorage.setItem(iconId, 'unlocked'); // Salva o estado como "unlocked"
                        }
                    }

                    // Função para enviar o formulário
                    function sendForm(form) {
                        const formData = new FormData(form);
                        fetch(form.action, {
                            method: 'POST',
                            body: formData
                        }).then(response => {
                            // Lidar com a resposta se necessário
                            console.log("Formulário enviado com sucesso");
                        }).catch(error => {
                            console.error("Erro ao enviar o formulário", error);
                        });
                    }

                    // Eventos de clique
                    submitGirosButton.addEventListener('click', (event) => {
                        toggleIcon('icon-giros');
                        sendForm(formGiros);
                    });

                    submitPremiosButton.addEventListener('click', (event) => {
                        toggleIcon('icon-premios');
                        sendForm(formPremios);
                    });

                    submitEsportesButton.addEventListener('click', (event) => {
                        toggleIcon('icon-esportes');
                        sendForm(formEsportes);
                    });

                    // Restaura o estado do ícone ao carregar a página
                    function restoreIconState(iconId) {
                        const state = localStorage.getItem(iconId);
                        const icon = document.getElementById(iconId);
                        if (state === 'locked') {
                            icon.classList.remove('fa-unlock');
                            icon.classList.add('fa-lock');
                        } else {
                            icon.classList.remove('fa-lock');
                            icon.classList.add('fa-unlock');
                        }
                    }

                    // Chama a função para restaurar o estado ao carregar a página
                    restoreIconState('icon-giros');
                    restoreIconState('icon-premios');
                    restoreIconState('icon-esportes');
                }

                setupFormListeners(); // Chama a função para configurar os ouvintes
            </script>

            <!-- <div class="container mt-5">
                <h2 class="mb-4 titulo">Adicionar Evento</h2>
                <div class="form-group">
                    <label for="tipoFormulario" class="mb-3">Selecione o tipo de formulário:</label>
                    <select id="tipoFormulario" class="form-control" onchange="mostrarFormulario(this.value)">
                        <option value="">Escolha...</option>
                        <option value="giro">Formulário de Giro</option>
                        <option value="premiacao">Formulário de Premiação</option>
                        <option value="esportes">Formulário de Esportes</option>
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
            </div> -->

            <?php
            require_once '../back-php/conexao.php'; // Inclua o arquivo de conexão

            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Consulta SQL para recuperar eventos de todas as tabelas
                $sql = "
        SELECT id, titulo, imagem, banner, 'premiacao' AS tipo FROM eventos_premiacao
        UNION ALL
        SELECT id, titulo, imagem, banner, 'giros' AS tipo FROM eventos_giros
        UNION ALL
        SELECT id, titulo, imagem, banner, 'esportes' AS tipo FROM eventos_esportes
    ";

                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Erro ao recuperar eventos: " . $e->getMessage();
            } finally {
                $conn = null;
            }
            ?>


            <div class="container">
                <h3 class="titulo mt-4 mb-4">Listagem de Eventos</h3>

                <div class="row">
                    <?php if (!empty($eventos)) : ?>
                        <?php foreach ($eventos as $evento) : ?>
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-4"> <!-- Ajustando a largura -->
                                <div class="card">
                                    <div class="d-flex mt-1 mb-1 justify-content-center">
                                        <button type="button" class="button-edit delete-button" data-bs-toggle="modal" data-bs-target="#editModal<?php echo htmlspecialchars($evento['id']); ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                    </div>

                                    <!-- Exibição do Banner -->
                                    <?php if (!empty($evento['banner'])) : ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($evento['banner']); ?>" class="card-img-top" alt="Banner do Evento" style="height: 50%; object-fit: cover;"> <!-- Diminuindo a altura -->
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <h5 class="card-title" style="text-align: center;"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                        <p>REGRAS:<br>
                                            - Estar inscrito no CANAL do TELEGRAM;<br>
                                            - Seguir a REALS no INSTAGRAM;<br>
                                            - Preencher o formulário abaixo CORRETAMENTE;<br>
                                            - Caso não esteja cumprindo as 3 regras, NÃO RECEBERÁ AS PREMIAÇÕES.<br>

                                            <br><br>
                                            Preencha somente UMA ÚNICA VEZ o formulário com seus DADOS CORRETOS utilizados na REALS BET.
                                            Caso não tenha conta na Reals Bet, CADASTRE-SE AQUI!
                                        </p>
                                        <!-- Exibição da Imagem do Evento -->
                                        <div class="conteudo col-lg-5">
                                            <?php if (!empty($evento['imagem'])) : ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($evento['imagem']); ?>" class="img-thumbnail" alt="Imagem do Evento" style="height: 50%; object-fit: cover;"> <!-- Diminuindo a altura -->
                                            <?php endif; ?>
                                        </div>

                                        <!-- Modal de Editar -->
                                        <div class="modal fade" id="editModal<?php echo $evento['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $evento['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?php echo $evento['id']; ?>">Editar Evento</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="./processamento/editar_evento.php" method="POST" enctype="multipart/form-data">
                                                            <div class="mb-3">
                                                                <label for="titulo<?php echo $evento['id']; ?>" class="form-label">Título:</label>
                                                                <input type="text" class="form-control" id="titulo<?php echo $evento['id']; ?>" name="titulo" value="<?php echo htmlspecialchars($evento['titulo']); ?>" required>
                                                                <input type="hidden" name="id" value="<?php echo $evento['id']; ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="banner<?php echo $evento['id']; ?>" class="form-label">Banner:</label>
                                                                <input type="file" class="form-control" id="banner<?php echo $evento['id']; ?>" name="banner">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="imagem<?php echo $evento['id']; ?>" class="form-label">Imagem:</label>
                                                                <input type="file" class="form-control" id="imagem<?php echo $evento['id']; ?>" name="imagem">
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
                    <?php else : ?>
                        <p class="text-center">Nenhum evento encontrado.</p>
                    <?php endif; ?>
                </div>
            </div>



            </form>
        </div>
        <!-- Agenda Form Fim -->

    </main>

    <script>
        function mostrarFormulario(tipo) {
            // Seleciona os formulários
            const formularioGiro = document.getElementById('formularioGiro');
            const formularioPremiacao = document.getElementById('formularioPremiacao');
            const formularioEsportes = document.getElementById('formularioEsportes');

            // Oculta ambos os formulários
            formularioGiro.style.display = 'none';
            formularioPremiacao.style.display = 'none';
            formularioEsportes.style.display = 'none';

            // Mostra o formulário correspondente
            if (tipo === 'giro') {
                formularioGiro.style.display = 'block';
            } else if (tipo === 'premiacao') {
                formularioPremiacao.style.display = 'block';
            } else if (tipo === 'esportes') {
                formularioEsportes.style.display = 'block';
            }
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh+WyJ0I72fLevddux1FRXr+8f77kyJyE05bM"
        crossorigin="anonymous"></script>

</body>

</html>