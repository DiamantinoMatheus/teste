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
        { id: 'formulario2', url: '../Dashboard/processamento/obterStatusFormulario2.php' },
        { id: 'formulario5', url: '../Dashboard/processamento/obterStatusFormulario5.php' },
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
    // Remove letras e os caracteres indesejados, incluindo o hífen (-)
    this.value = this.value.replace(/[a-zA-Z.,()$¨%@!&*/:€£¥•‘’”“[\]~´`-ÇçáàéèÈÁÉÈÀóòÓÒúùÚÙÍÌíì'"+=-_^#@{}/|;]/g, '');
});

function mostrarFormulario(tipo) {
    // Seleciona os formulários
    const formularioGiro = document.getElementById('formularioGiro');
    const formularioPremiacao = document.getElementById('formularioPremiacao');
    const formularioEsportes = document.getElementById('formularioEsportes');
    const formularioTicket = document.getElementById('formularioTicket');

    // Oculta ambos os formulários
    formularioGiro.style.display = 'none';
    formularioPremiacao.style.display = 'none';
    formularioEsportes.style.display = 'none';
    formularioTicket.style.display = 'none';

    // Mostra o formulário correspondente
    if (tipo === 'giro') {
        formularioGiro.style.display = 'block';
    } else if (tipo === 'premiacao') {
        formularioPremiacao.style.display = 'block';
    } else if (tipo === 'esportes') {
        formularioEsportes.style.display = 'block';
    } else if (tipo === 'ticket') {
        formularioTicket.style.display = 'block';
    }
}

function setupFormListeners() {
    const formGiros = document.querySelector("input[name='eventoId3']").closest('.form-status');
    const formPremios = document.querySelector("input[name='eventoId1']").closest('.form-status');
    const formEsportes = document.querySelector("input[name='eventoId4']").closest('.form-status');
    const formTicket = document.querySelector("input[name='eventoId5']").closest('.form-status');

    const submitGirosButton = document.getElementById("submitGiros");
    const submitPremiosButton = document.getElementById("submitPremios");
    const submitEsportesButton = document.getElementById("submitPremioEsportes");
    const submitTicketButton = document.getElementById("submitPremioTicket");

    // Função para trocar ícone no clique
    function toggleIcon(iconId) {
        const icon = document.getElementById(iconId);
        if (icon.classList.contains('fa-unlock')) {
            icon.classList.remove('fa-unlock');
            icon.classList.add('fa-lock');
            localStorage.setItem(iconId, 'locked'); // Salva o estado como "locked"
        } else {
            icon.classList.remove('fa-lock');
            icon.classList.add('fa-unlock');
            localStorage.setItem(iconId, 'unlocked'); // Salva o estado como "unlocked"
        }
    }

    // Função para enviar o formulário
    function sendForm(form) {
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(response => {
            // Lidar com a resposta se necessário
            console.log("Formulário enviado com sucesso");
        }).catch(error => {
            console.error("Erro ao enviar o formulário", error);
        });
    }

    // Eventos de clique
    submitGirosButton.addEventListener('click', (event) => {
        toggleIcon('icon-giros');
        sendForm(formGiros);
    });

    submitPremiosButton.addEventListener('click', (event) => {
        toggleIcon('icon-premios');
        sendForm(formPremios);
    });

    submitEsportesButton.addEventListener('click', (event) => {
        toggleIcon('icon-esportes');
        sendForm(formEsportes);
    });

    submitTicketButton.addEventListener('click', (event) => {
        toggleIcon('icon-ticket');
        sendForm(formTicket);
    });

    // Restaura o estado do ícone ao carregar a página
    function restoreIconState(iconId) {
        const state = localStorage.getItem(iconId);
        const icon = document.getElementById(iconId);
        if (state === 'locked') {
            icon.classList.remove('fa-unlock');
            icon.classList.add('fa-lock');
        } else {
            icon.classList.remove('fa-lock');
            icon.classList.add('fa-unlock');
        }
    }

    // Chama a função para restaurar o estado ao carregar a página
    restoreIconState('icon-giros');
    restoreIconState('icon-premios');
    restoreIconState('icon-esportes');
    restoreIconState('icon-ticket');
}

setupFormListeners(); // Chama a função para configurar os ouvintes