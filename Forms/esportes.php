<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$messageClass = '';

// Verifica se a mensagem está definida na sessão
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    // Verifica se a classe da mensagem está definida
    $messageClass = isset($_SESSION['message_class']) ? $_SESSION['message_class'] : '';
    unset($_SESSION['message']);
    unset($_SESSION['message_class']);
}

// Inclui o arquivo de conexão com o banco de dados
require_once '../back-php/conexao.php';

// Verifica se a conexão foi estabelecida
if (!isset($pdo)) {
    die("Erro: A conexão com o banco de dados não foi estabelecida.");
}

// Inicializa variáveis para evitar erros de variável não definida
$banner = '';
$titulo = 'Título não disponível';
$imagem = '<p>Imagem não disponível</p>';
$csrf_token = '';
$htmlFormulario = '<p>O formulário não está disponível no momento.</p>'; // Valor padrão para evitar erros de variável não definida

// Gera um token CSRF se não estiver presente na sessão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    // Consulta os dados do evento, incluindo o status de formulário_aberto
    $queryUsers = "SELECT id, banner, titulo, imagem, interative FROM eventos_esportes LIMIT 1";
    $stmtUsers = $pdo->prepare($queryUsers);
    $stmtUsers->execute();
    $result = $stmtUsers->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Converte os dados binários da imagem para Base64
        $banner = $result['banner'] ? '<img src="data:image/jpeg;base64,' . base64_encode($result['banner']) . '" alt="Banner do Evento">' : '';
        $imagem = $result['imagem'] ? '<img src="data:image/jpeg;base64,' . base64_encode($result['imagem']) . '" alt="Imagem do Evento">' : '';
        $titulo = htmlspecialchars($result['titulo'], ENT_QUOTES, 'UTF-8');

        // Verifica o estado do formulário
        $formularioAberto = $result['interative'];

        if ($formularioAberto) {
            $siteKey = '6LdcnV0qAAAAAMGGUszs1Qfy90aWwRoVtWNmiUIM'; // Substitua pela sua site key do reCAPTCHA
            $secretKey = '6LdcnV0qAAAAAO0dhcpdmD_65NLVsz4doG8L5Xly'; // Substitua pela sua secret key do reCAPTCHA
            $htmlFormulario = '
            <form id="formulario2" class="form" method="POST" action="../Dashboard/processamento/insert_dados-esportes.php" enctype="multipart/form-data" onsubmit="return handleSubmit()">
                <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">
                <input type="hidden" name="id" value="' . (isset($id) ? htmlspecialchars($id, ENT_QUOTES, 'UTF-8') : '') . '">
                
                <div class="form-group">
                    <input type="text" name="nome" id="nome" placeholder="Informe seu nome" 
                        value="' . (isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '') . '" 
                        required pattern="[A-Za-z\s]+" title="Digite apenas letras e espaços.">
                </div>

                <div class="form-group">
                    <input type="text" id="cpf" name="cpf" placeholder="Informe seu CPF" 
                        value="' . (isset($cpf) ? htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8') : '') . '" 
                        required oninput="mascaraCPF(this)" 
                        maxlength="14" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">
                </div>

                <div class="form-group">
                    <h4> Palmeiras x Fortaleza - 26/10 </h4>
                    <input type="text" id="primeiro_jogo" name="primeiro_jogo" placeholder="Placar exato - Exemplo placar 2x5" 
                        value="' . (isset($primeiro_jogo) ? htmlspecialchars($primeiro_jogo, ENT_QUOTES, 'UTF-8') : '') . '" 
                    oninput="mascaraPlacar(this)" maxlength="3" pattern="\d+x\d+" required>
                </div>
                <div class="form-group">
                    <h4> Vitória x Fluminense - 26/10 </h4>
                    <input type="text" id="segundo_jogo" name="segundo_jogo" placeholder="Placar exato - Exemplo placar 3x1" 
                        value="' . (isset($segundo_jogo) ? htmlspecialchars($segundo_jogo, ENT_QUOTES, 'UTF-8') : '') . '" 
                        oninput="mascaraPlacar(this)" maxlength="3" pattern="\d+x\d+" required>
                </div>
                <div class="form-group">
                    <h4> Real Madrid x Barcelona - 26/10 </h4>
                    <input type="text" id="terceiro_jogo" name="terceiro_jogo" placeholder="Placar exato - Exemplo placar 4x4" 
                        value="' . (isset($terceiro_jogo) ? htmlspecialchars($terceiro_jogo, ENT_QUOTES, 'UTF-8') : '') . '" 
                        oninput="mascaraPlacar(this)" maxlength="3" pattern="\d+x\d+" required>
                </div>
                <div class="form-group">
                    <h4> Internacional x Atlético Mineiro - 26/10 </h4>
                    <input type="text" id="quarto_jogo" name="quarto_jogo" placeholder="Placar exato - Exemplo placar 4x4" 
                        value="' . (isset($quarto_jogo) ? htmlspecialchars($quarto_jogo, ENT_QUOTES, 'UTF-8') : '') . '" 
                        oninput="mascaraPlacar(this)" maxlength="3" pattern="\d+x\d+" required>
                </div>

                <h1>ID DA SUA CONTA REALS - <strong class="regras"><em>SOMENTE OS NÚMEROS, NÃO COLOCAR "ID#"</em></strong></h1>
                <p>Acesse "MENU" ➜ "CARTEIRA/PERFIL" ➜ DIGITE <strong><em>SOMENTE OS NÚMEROS</em></strong> DA ID QUE APARECER</p>
                <div class="imagens">
                    ' . $imagem . '
                </div>
                <div class="form-group foto">
                    <input type="text" id="codigo" name="codigo" placeholder="Digite seu ID aqui" 
                        value="' . (isset($codigo) ? htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') : '') . '">
                </div>
                
                <!-- Adiciona o widget do reCAPTCHA -->
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>
                </div>
                
                <div class="form-group">
                    <button type="submit">Enviar</button>
                </div>
            </form>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>

            <script>
            function handleSubmit() {
                const recaptchaResponse = grecaptcha.getResponse(); // Obtém a resposta do reCAPTCHA

                if (!recaptchaResponse) {
                    alert("Por favor, complete o CAPTCHA.");
                    return false; // Impede o envio do formulário
                }

                return true; // Permite o envio do formulário
            }
            </script>
        ';

        } else {
            // Exibe mensagem de formulário fechado
            $htmlFormulario = '<p>O formulário está fechado no momento.</p>';
        }
    }
} catch (PDOException $e) {
    $message = "Erro na consulta: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $messageClass = 'error';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://static.pl-01.cdn-platform.com/themes/1.1.7/reals.bet/icons/favicon.ico">
    <link rel="stylesheet" href="../Dashboard/css/style.css">
    <title>Formulário - Reals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="centro" style="background-image: url(../img/bg.webp); background-repeat: repeat-y;">

    <div class="banner">
        <?php echo $banner; ?>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="titulo"><?php echo $titulo; ?></h1>
            <h2><strong class="regras1"><em>REGRAS:</em></strong></h2>
            <p>
                Estar inscrito no <a href="https://t.me/comunidadereals">CANAL do TELEGRAM</a>;<br>
                Seguir a <a href="https://www.instagram.com/reals.bet/">REALS no INSTAGRAM</a>;<br>
                Preencher o formulário abaixo <strong class="regras">CORRETAMENTE</strong>;<br>
                Caso não esteja cumprindo as 3 regras, <strong class="regras">NÃO RECEBERÁ AS
                    PREMIAÇÕES</strong>.<br><br><br>
                Preencha somente UMA <strong>ÚNICA VEZ</strong> o formulário com seus <strong>DADOS CORRETOS</strong>
                utilizados na <a href="https://realsbet.com/signup">REALS BET</a>.<br>
                Caso não tenha conta na Reals Bet, <a href="https://realsbet.com/signup">CADASTRE-SE AQUI!</a>
            </p>
        </div>

        <p id="message" class="message" class="<?php echo htmlspecialchars($messageClass); ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>

        <div id="form-container">
            <?php echo $htmlFormulario; ?>
        </div>

        <div class="footer">
            <p>&copy; 2024 Reals. Todos os direitos reservados.</p>
            <button id="shareBtn" class="compartilhar"><i class="fas fa-share-alt"></i> Compartilhar com os seus
                amigos</button>
        </div>

        <!-- Modal de Compartilhamento -->
        <div id="shareModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Compartilhe com seus amigos</h2>
                <div class="social-share">
                    <a id="whatsappShare" href="#" target="_blank" title="Compartilhar no WhatsApp"><i
                            class="fab fa-whatsapp icon"></i> WhatsApp</a>
                    <a id="facebookShare" href="#" target="_blank" title="Compartilhar no Facebook"><i
                            class="fab fa-facebook-f icon"></i> Facebook</a>
                    <a id="emailShare" href="#" target="_blank" title="Compartilhar por E-mail"><i
                            class="fas fa-envelope icon"></i> E-mail</a>
                </div>
                <div class="share-link">
                    <input type="text" id="shareUrl" readonly>
                    <button id="copyLink">Copiar</button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("copyLink").addEventListener("click", function () {
                    const shareInput = document.getElementById("shareUrl");

                    if (shareInput) {
                        shareInput.select();
                        shareInput.setSelectionRange(0, 99999);

                        try {
                            const successful = document.execCommand("copy");
                            if (successful) {
                                console.log('Link copiado com sucesso!');
                            } else {
                                console.error('Falha ao copiar o link.');
                            }
                        } catch (error) {
                            console.error('Erro ao copiar o link:', error);
                        }
                    } else {
                        console.error('Elemento com ID "shareUrl" não encontrado.');
                    }
                });
            });
        </script>
        <script src="comands.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.6/jquery.inputmask.min.js"></script>
        <script>
            function mascaraCPF(cpf) {
                // Remove qualquer caractere que não seja número
                cpf.value = cpf.value.replace(/\D/g, "");

                // Insere pontos e hífen conforme o CPF vai sendo digitado
                cpf.value = cpf.value.replace(/(\d{3})(\d)/, "$1.$2");
                cpf.value = cpf.value.replace(/(\d{3})(\d)/, "$1.$2");
                cpf.value = cpf.value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            }

            function mascaraPlacar(input) {
                // Remove qualquer caractere que não seja número ou "x"
                input.value = input.value.replace(/[^\dx]/g, "");

                // Limita a quantidade de dígitos a 5 (2 dígitos, 1 'x' e 2 dígitos)
                if (input.value.length > 5) {
                    input.value = input.value.slice(0, 3);
                }

                // Garante que tenha apenas um "x"
                if ((input.value.match(/x/g) || []).length > 1) {
                    input.value = input.value.replace(/x/g, '').replace(/^(\d{1,2})(\d{1,2})/, "$1x$2");
                }

                // Adiciona 'x' entre os dois números se ainda não houver
                if (input.value.length === 2 && !input.value.includes('x')) {
                    input.value = input.value.replace(/(\d{1})(\d{1})/, "$1x$2");
                }
            }

            function validarID(input) {
                // Bloquear caracteres não numéricos e simbolos < e >
                input.value = input.value.replace(/[^0-9]/g, '');

                // Verificar se o comprimento é 10
                const errorSpan = document.getElementById('codigo-error');
                if (input.value.length === 10) {
                    errorSpan.style.display = 'none'; // Ocultar erro
                } else {
                    errorSpan.style.display = 'block'; // Mostrar erro
                }
            }
        </script>

</body>

</html>