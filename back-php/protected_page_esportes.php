<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário passou pelo CAPTCHA
if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
    // Redirecionar para a página de verificação do CAPTCHA
    header('Location: .././Forms/esportes.php');
    exit();
}

// Aqui você pode adicionar o conteúdo protegido da página
echo 'Bem-vindo à página protegida!';
