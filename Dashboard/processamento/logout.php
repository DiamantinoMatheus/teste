<?php

// Start session
if (!session_id()) {
    session_start();
}

// Destroi completamente a sessão
session_destroy();

// Redireciona para a página de login
header("Location: ../login.php");
