document.addEventListener("DOMContentLoaded", function () {
    const pageUrl = encodeURIComponent(window.location.href);
    const pageTitle = encodeURIComponent(document.title);
    const shareLinks = {
        whatsapp: `https://wa.me/?text=${pageTitle}%20${pageUrl}`,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${pageUrl}`,
        email: `mailto:?subject=${pageTitle}&body=Confira%20este%20link:%20${pageUrl}`
    };

    // Configura os links de compartilhamento
    document.getElementById("whatsappShare").href = shareLinks.whatsapp;
    document.getElementById("facebookShare").href = shareLinks.facebook;
    document.getElementById("emailShare").href = shareLinks.email;
    document.getElementById("shareUrl").value = window.location.href;

    // Modal
    const modal = document.getElementById("shareModal");
    const btn = document.getElementById("shareBtn");
    const closeModal = () => modal.style.display = "none";

    btn.onclick = () => modal.style.display = "block";
    document.getElementsByClassName("close")[0].onclick = closeModal;
    window.onclick = (event) => {
        if (event.target === modal) closeModal();
    };

    // Verifica disponibilidade dos formulários
    const formUrls = [
        { id: 'formulario1', url: '../Dashboard/processamento/obterStatusFormulario1.php' },
        { id: 'formulario', url: '../Dashboard/processamento/obterStatusFormulario.php' },
        { id: 'formulario2', url: '../Dashboard/processamento/obterStatusFormulario2.php' }
    ];

    formUrls.forEach(({ id, url }) => verificarDisponibilidadeFormulario(id, url));

    function verificarDisponibilidadeFormulario(formularioId, url) {
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const formularioDiv = document.getElementById(formularioId);
                if (!formularioDiv) {
                    return console.error(`Elemento com ID '${formularioId}' não encontrado.`);
                }

                formularioDiv.style.display = data.status === 1 ? 'block' : 'none';
                console.log(`Formulário ${data.status === 1 ? 'aberto' : 'fechado'}.`);
            })
            .catch(error => console.error('Erro ao buscar o status do formulário:', error));
    }

    // Alterna a visibilidade dos formulários
    document.getElementById('toggleFormButton').addEventListener('click', function () {
        formUrls.forEach(({ id }) => {
            const formularioDiv = document.getElementById(id);
            if (formularioDiv) {
                const isVisible = formularioDiv.style.display !== 'none';
                const newStatus = isVisible ? 0 : 1;
                const url = isVisible
                    ? '../Dashboard/processamento/salvarStatusFormulario1.php' // Atualize conforme necessário
                    : '../Dashboard/processamento/salvarStatusFormulario2.php'; // Atualize conforme necessário

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: newStatus })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            formularioDiv.style.display = isVisible ? 'none' : 'block';
                        }
                    })
                    .catch(error => console.error('Erro ao atualizar o status do formulário:', error));
            }
        });
    });
});

// Função para bloquear os caracteres < e >
function bloquearSimbolos(event) {
    // Remove qualquer ocorrência dos caracteres < e >
    event.target.value = event.target.value.replace(/[<>]/g, '');
}

// Seleciona todos os inputs de texto
const inputsTexto = document.querySelectorAll('input[type="text"]');

// Adiciona o evento de input para cada campo
inputsTexto.forEach(function (input) {
    input.addEventListener('input', bloquearSimbolos);
});

document.getElementById('codigo').addEventListener('input', function (e) {
    // Remove letras e os caracteres .,()/:[]~´`'"+=-_^ e mantém apenas números
    this.value = this.value.replace(/[a-zA-Z.,()$¨%@!&*/:€£¥•‘’[\]~´`ÇçáàéèÈÁÉÈÀóòÓÒúùÚÙÍÌíì'"+=-_^#@{}/|;]/g, '');
});


function handleSubmit() {
    const recaptchaResponse = grecaptcha.getResponse(); // Obtém a resposta do reCAPTCHA

    if (!recaptchaResponse) {
        alert("Por favor, complete o CAPTCHA.");
        return false; // Impede o envio do formulário
    }

    return true; // Permite o envio do formulário
}