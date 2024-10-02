<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/login.css">
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault(); // Desabilita o menu de contexto (botão direito do mouse)
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault(); // Desabilita F12 e Ctrl+Shift+I (para abrir as ferramentas de desenvolvimento)
            }
        });
    </script>
</head>

<body>

    <section class="vh-100" style="background-image: url(../img/bg.webp); background-position: center; background-size: cover;">
        <div class="container-fluid h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-9 col-lg-6 col-xl-5 order-2 order-xl-1">
                    <img src="../img/logo.webp" class="img-fluid" alt="simples">
                </div>
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1 order-1 order-xl-2">
                    <form action="./processamento/Auth.php" method="POST">
                        <!-- Email input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="form3Example3" style="color: white;">Email address</label>
                            <input type="email" id="form3Example3" name="email" class="form-control form-control-lg"
                                pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                                title="Email inválido"
                                required>
                        </div>

                        <!-- Password input -->
                        <div class="form-outline mb-3">
                            <label class="form-label" for="form3Example4" style="color: white;">Password</label>
                            <input type="password" id="form3Example4" name="senha" class="form-control form-control-lg"
                                title="A senha deve ter pelo menos 6 dígitos numéricos e não pode conter caracteres especiais"
                                required>
                        </div>


                        <div class="text-center text-lg-start mt-4 pt-2">
                            <input type="submit" value="Acessar" class="btn" style="padding-left: 2.5rem; padding-right: 2.5rem; color: white; background-color: #00FEAD;" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

</body>

</html>