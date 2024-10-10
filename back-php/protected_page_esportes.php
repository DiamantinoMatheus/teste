<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o CAPTCHA já foi verificado
if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
    // Redirecionar para a página do CAPTCHA apenas se ainda não foi verificado
    header('Location: .././Forms/esportes.php');  // Redirecionar para a página do CAPTCHA
    exit();
}

// O resto da lógica para a página, caso o CAPTCHA já tenha sido verificado
