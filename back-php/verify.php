<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sua chave secreta do reCAPTCHA
    $secretKey = '6LdcnV0qAAAAAO0dhcpdmD_65NLVsz4doG8L5Xly';
    $response = $_POST['g-recaptcha-response'];

    // Verificação do reCAPTCHA
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$response}");
    $responseData = json_decode($verifyResponse);

    // Verificar se o CAPTCHA foi verificado com sucesso
    if ($responseData->success) {
        $_SESSION['captcha_verified'] = true; // Define a variável de sessão para verdadeiro
        // Redirecionar ou continuar o processamento do formulário
        header(header: 'location: .././Forms/giros.php');
        // Aqui você pode redirecionar para outra página
    } else {
        // CAPTCHA falhou
        header(header: 'location: .././Forms/recaptcha-verification-giros.html');
        // Redirecionar ou lidar com o erro
        $_SESSION['captcha_verified'] = false; // Define a variável de sessão para falso
    }
} else {
    // Acesso não permitido
    echo 'Método não permitido.';
    header(header: 'location: .././Forms/recaptcha-verification-giros.html');
}
