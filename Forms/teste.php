<?php

// Verificar se o usuário passou pelo CAPTCHA
if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
    // Redirecionar para a página de verificação do CAPTCHA
    header('Location: recaptcha-verification.html');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>

<body>
    <h1>DEU CERTO</h1>
</body>

</html>