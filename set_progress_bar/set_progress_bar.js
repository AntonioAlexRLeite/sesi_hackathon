// set_progress_bar.js
let currentStep = 2; // Variável global acessada pelo notification.js
const totalSteps = 5;

const statusMessages = [
    "Contrato assinado! Aguardando início.",
    "Aguardando envio da Planilha M1 pela empresa.",
    "Técnico de segurança agendado para visita.",
    "Documentos (PGR) em elaboração pela equipe técnica.",
    "PCMSO concluído! Você já pode agendar exames."
];

function updateUI() {
    // Atualiza a barra de progresso (linha verde)
    const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.getElementById('progressFill').style.width = progressPercentage + '%';

    // Atualiza o texto de status
    document.getElementById('statusText').innerText = statusMessages[currentStep - 1];

    // Atualiza as classes dos passos (ícones)
    for (let i = 1; i <= totalSteps; i++) {
        const stepElement = document.getElementById('step' + i);
        
        if (stepElement) { // Verificação de segurança
            // Remove classes antigas
            stepElement.classList.remove('active', 'completed');

            if (i < currentStep) {
                stepElement.classList.add('completed');
                stepElement.querySelector('.step-icon').innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === currentStep) {
                stepElement.classList.add('active');
                const icons = ['fa-file-signature', 'fa-file-excel', 'fa-user-shield', 'fa-clipboard-check', 'fa-notes-medical'];
                stepElement.querySelector('.step-icon').innerHTML = `<i class="fas ${icons[i-1]}"></i>`;
            } else {
                const icons = ['fa-file-signature', 'fa-file-excel', 'fa-user-shield', 'fa-clipboard-check', 'fa-notes-medical'];
                stepElement.querySelector('.step-icon').innerHTML = `<i class="fas ${icons[i-1]}"></i>`;
            }
        }
    }
}

function nextStep() {
    if (currentStep < totalSteps) {
        currentStep++;
        updateUI();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateUI();
    }
}

// Inicializa ao carregar
updateUI();

