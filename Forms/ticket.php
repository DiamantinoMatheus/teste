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
$titulo = ' ';
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
    $queryUsers = "SELECT id, banner, titulo, imagem, interative FROM eventos_ticket LIMIT 1";
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
                <form id="formulario5" class="form" method="POST" action="../Dashboard/processamento/insert_dados-ticket.php" onsubmit="return handleSubmit()">
                    <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">
                    <input type="hidden" name="id" value="' . (isset($id) ? htmlspecialchars($id, ENT_QUOTES, 'UTF-8') : '') . '">

                    <div class="form-group">
                        <label for="rg">RG</label>
                        <input type="text" name="rg" id="rg" placeholder="Informe seu RG" maxlength="12"  oninput="aplicarMascaraRG(this)" required>                    
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" name="nome" id="nome" placeholder="Informe seu nome completo" value="' . (isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '') . '" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <input type="text" name="endereco" id="endereco" placeholder="Informe seu endereço" value="' . (isset($endereco) ? htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8') : '') . '" maxlength="150">
                    </div>

                    <div class="form-group">
                        <label for="instagram">Instagram (@)</label>
                        <input type="text" name="instagram" id="instagram" placeholder="Informe seu Instagram" value="' . (isset($instagram) ? htmlspecialchars($instagram, ENT_QUOTES, 'UTF-8') : '') . '" maxlength="50">
                    </div>
                    <div class="imagens">' . $imagem . '</div>
                    <div class="form-group">
                        <button type="submit">Enviar</button>
                    </div>
                </form>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
    <link rel="stylesheet" href="../Dashboard/css/ticket.css">
    <title>Formulário - Reals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="centro" style="background-image: url(../img/bg.webp); background-repeat: repeat-y;">

    <div class="banner">

    </div>
    <div class="container">
        <div class="header" style="margin-top: 30px;">
            <h1 class="titulo"></h1>
            <h2><strong class="regras1"><em>SEGUIMOS JUNTOS 🐯🤩</em></strong></h2>
            <p>
                Para celebrar a parceira entre Amazonas e Reals, serão sorteados <strong>CEM PARES DE
                    INGRESSOS!!</strong> <br>
                Preencha corretamente com seus dados, até as 17 horas de hoje (18/10) <br>os ganhadores já receberam um
                retorno!
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
            function aplicarMascaraRG(element) {
                let rg = element.value;

                // Remove tudo que não é dígito
                rg = rg.replace(/\D/g, '');

                // Aplica a máscara: 99.999.999-9
                if (rg.length > 2) {
                    rg = rg.replace(/^(\d{2})(\d)/, '$1.$2');
                }
                if (rg.length > 6) {
                    rg = rg.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                }
                if (rg.length > 10) {
                    rg = rg.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
                }

                // Atualiza o valor do campo com a máscara aplicada
                element.value = rg;
            }
        </script>
        <script src="comand.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.6/jquery.inputmask.min.js"></script>


</body>

</html>