<?php
require_once '../vendor/autoload.php'; // Inclui o autoload do Composer

// Inclua as dependências do Google Cloud
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

/**
 * Cria uma avaliação para analisar o risco de uma ação da interface do usuário.
 * @param string $recaptchaKey A chave do reCAPTCHA associada ao site/app
 * @param string $token O token gerado obtido do cliente.
 * @param string $project Seu ID do projeto Google Cloud.
 * @param string $action A ação correspondente ao token.
 */
function create_assessment(
    string $recaptchaKey,
    string $token,
    string $project,
    string $action
): void {
    // Cria o cliente reCAPTCHA.
    $client = new RecaptchaEnterpriseServiceClient();
    $projectName = $client->projectName($project);

    // Define as propriedades do evento a serem rastreadas.
    $event = (new Event())
        ->setSiteKey($recaptchaKey)
        ->setToken($token);

    // Constrói o pedido de avaliação.
    $assessment = (new Assessment())
        ->setEvent($event);

    try {
        $response = $client->createAssessment(
            $projectName,
            $assessment
        );

        // Verifica se o token é válido.
        if ($response->getTokenProperties()->getValid() == false) {
            printf('A chamada CreateAssessment() falhou porque o token era inválido pelo seguinte motivo: ');
            printf(InvalidReason::name($response->getTokenProperties()->getInvalidReason()));
            return;
        }

        // Verifica se a ação esperada foi executada.
        if ($response->getTokenProperties()->getAction() == $action) {
            // Obtem a pontuação de risco.
            printf('A pontuação para a ação de proteção é: %s', $response->getRiskAnalysis()->getScore());
        } else {
            printf('O atributo de ação na sua tag reCAPTCHA não corresponde à ação que você espera.');
        }
    } catch (exception $e) {
        printf('A chamada CreateAssessment() falhou com o seguinte erro: %s', $e->getMessage());
    }
}

// Parâmetros para a verificação
create_assessment(
    'YOUR_RECAPTCHA_KEY',                // Substitua pela sua chave do reCAPTCHA
    $_POST['g-recaptcha-response'],      // O token gerado pelo reCAPTCHA
    'YOUR_PROJECT_ID',                   // Seu ID do projeto Google Cloud
    'submit'                             // A ação definida no front-end
);
