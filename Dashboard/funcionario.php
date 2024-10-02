<?php

// Start session
if (!session_id()) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    // Redireciona para a página de login
    header("Location: login.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="./css/funcionario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" crossorigin="anonymous">
    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@latest/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Adicionei o link do DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <!-- Removi links redundantes do Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@latest/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.x -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbs5qhs9jH7P3v7qbJSvfF5pGPGwFZ1YVx4f7IUq/gxhUpcfTtv9aH8P5K5tA==" crossorigin="anonymous">
</head>

<body>
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
                        <a href="./dash.php" class="button1 sair">
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


    <main class="container">
        <h1 class="titulo">Usuário</h1>
        <?php
        // Continue com o restante do código
        include '../back-php/conexao.php';

        try {
            // Cria uma nova conexão PDO
            $query = "SELECT id, email, senha FROM user";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }
        ?>

        <script>
            $(document).ready(function() {
                // Ative o DataTable
                $('#userTable').DataTable();
            });
        </script>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Opções</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) : ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <button type="button" class="button-option" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="button-option" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal Editar -->
        <?php foreach ($results as $row) { ?>
            <!-- Modal Editar -->
            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="editModalLabel">Editar Usuário</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Formulário para Editar Live -->
                            <form method="POST" action="./processamento/EditUser.php" enctype="multipart/form-data">
                                <!-- Campo oculto para o ID -->
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="senha" class="form-label">Senha:</label>
                                    <input type="password" class="form-control" id="senha" name="senha" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="button-option">Editar</button>
                                    <button type="button" class="button-option" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fim do Modal Editar -->
        <?php } ?>



        <!-- Modal Excluir -->
        <?php foreach ($results as $row) { ?>
            <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Você tem certeza que deseja excluir "<?php echo $row['email']; ?>"?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button-option" data-bs-dismiss="modal">Cancelar</button>
                            <a href="./processamento/DelUser.php?id=<?php echo $row['id']; ?>" class="button-option">Excluir</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        </tbody>
        </table>

    </main>

    <!-- Bootstrap Bundle (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- jQuery 3.6.0 (mais recente e não slim) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5/7kkm7A8aWYZy9gMw8gntU2S3ZXGrWb8p/V6aYaa" crossorigin="anonymous"></script>

    <!-- Popper.js 2.11.7 (mais recente) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9+tfM3u3fg+dfkIkD5+ImVb7w7Lpx0ZV4z9B7WyJ5FrslPmFciP+" crossorigin="anonymous"></script>

    <!-- DataTables 1.11.5 -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-sA7sJ9FJr6P1n5kl6Y59iK+9zjz6Ir5E5/VK9udqzMQcX9QjIQl0TPTbVR+yDfxP" crossorigin="anonymous"></script>

    <!-- Bootstrap Datepicker 1.9.0 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha384-T5fs9jbZ/9lAk7RyB9RfJmH5Ls5w6DFRvMEH2nZfF2Dsmc6BRnT0Pz7C0T7Zm0J5A" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.pt-BR.min.js" integrity="sha384-5YwAj6qkZ8vsn5K9i9jZP0CqAPz1PL9W9a1j4HQQQvm6tX9W7zmD9XKjC+Yw9WjtP" crossorigin="anonymous"></script>


</body>

</html>