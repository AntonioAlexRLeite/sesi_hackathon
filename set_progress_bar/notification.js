//Notification.js
// --- CÓDIGO DE CONEXÃO PUSH (SSE) ---

        // 1. Solicita permissão para Notificações do Navegador ao carregar
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }

        // 2. Conecta ao arquivo PHP que envia os eventos
        const evtSource = new EventSource("push.php");

        // 3. Quando receber uma mensagem do servidor...
        evtSource.onmessage = function(event) {
            const novoPasso = parseInt(event.data);

            // Só atualiza se o passo for diferente do atual
            if (novoPasso !== currentStep) {
                currentStep = novoPasso;
                updateUI(); // Atualiza a barra visualmente
                
                // Dispara Notificação de Navegador (Push Visual)
                enviarNotificacaoNavegador(currentStep);
            }
        };

        function enviarNotificacaoNavegador(passo) {
            // Verifica permissão
            if (Notification.permission === "granted") {
                // Títulos baseados no passo
                const titulos = [
                    "", 
                    "✅ Contrato Iniciado", 
                    "⚠️ Pendência: Planilha M1", 
                    "👷 Técnico a Caminho", 
                    "📄 PGR em Andamento", 
                    "🎉 Tudo Pronto!"
                ];
                
                const notificacao = new Notification("SESI Informa:", {
                    body: titulos[passo] + " - O status do seu serviço mudou.",
                    icon: "https://cdn-icons-png.flaticon.com/512/1089/1089129.png" // Ícone genérico de fábrica
                });
            }
        }