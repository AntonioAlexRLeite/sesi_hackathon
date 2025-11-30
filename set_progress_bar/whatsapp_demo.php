<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp SESI - Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #d1d7db; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .phone-frame {
            width: 380px; height: 700px; background: #fff; border-radius: 20px; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: flex; flex-direction: column;
            border: 10px solid #333; position: relative;
        }

        /* Cabeçalho Zap */
        .header { background-color: #008069; color: white; padding: 10px 15px; display: flex; align-items: center; gap: 10px; }
        .avatar { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #008069; font-size: 20px; }
        .user-info h4 { margin: 0; font-size: 16px; }
        .user-info span { font-size: 12px; opacity: 0.8; }

        /* Área de Chat */
        .chat-area { flex: 1; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-color: #e5ddd5; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }

        .message { max-width: 80%; padding: 8px 12px; border-radius: 8px; font-size: 14px; position: relative; line-height: 1.4; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .message.sent { align-self: flex-end; background-color: #dcf8c6; border-top-right-radius: 0; }
        .message.received { align-self: flex-start; background-color: #fff; border-top-left-radius: 0; }
        
        .time { font-size: 10px; color: #999; float: right; margin-top: 5px; margin-left: 10px; }
        
        .link-card {
            display: block; text-decoration: none; background: #f0f0f0; border-left: 4px solid #008069;
            padding: 10px; margin-top: 5px; border-radius: 4px; color: #333;
        }
        .link-card strong { color: #005594; display: block; margin-bottom: 3px; }
        .link-card:hover { background: #e9e9e9; }

        /* Input */
        .input-area { background: #f0f0f0; padding: 10px; display: flex; align-items: center; gap: 10px; }
        input { flex: 1; padding: 10px; border-radius: 20px; border: none; outline: none; }
        button { background: none; border: none; color: #555; font-size: 20px; cursor: pointer; }
        .btn-send { color: #008069; }

    </style>
</head>
<body>

    <div class="phone-frame">
        <div class="header">
            <div class="avatar"><i class="fas fa-robot"></i></div>
            <div class="user-info">
                <h4>SESI Inteligente</h4>
                <span>Online agora</span>
            </div>
            <div style="margin-left: auto;"><i class="fas fa-ellipsis-v"></i></div>
        </div>

        <div class="chat-area" id="chatBox">
            <div class="message received">
                Olá! Sou a IA do SESI no WhatsApp. 🤖<br>
                Posso ajudar com: Status, Exames ou <strong>Iniciar Novo Contrato</strong>.
                <span class="time">10:00</span>
            </div>
        </div>

        <div class="input-area">
            <i class="far fa-smile"></i>
            <input type="text" id="msgInput" placeholder="Mensagem" onkeypress="handleEnter(event)">
            <button onclick="enviar()" class="btn-send"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');

        function addMsg(text, type, isHtml = false) {
            const div = document.createElement('div');
            div.className = `message ${type}`;
            if(isHtml) div.innerHTML = text + `<span class="time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>`;
            else div.innerHTML = text + `<span class="time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>`;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function enviar() {
            const input = document.getElementById('msgInput');
            const txt = input.value.trim();
            if(!txt) return;

            addMsg(txt, 'sent');
            input.value = "";

            // Simulação da Inteligência (Lógica da Demo)
            setTimeout(() => {
                responder(txt.toLowerCase());
            }, 1000);
        }

        function responder(txt) {
            // Lógica 1: Iniciar Contrato (O Link da M1)
            if(txt.includes("iniciar") || txt.includes("contrato") || txt.includes("cadastro") || txt.includes("m1")) {
                const link = `
                    Certo! Para agilizar, preencha os dados da empresa e funcionários neste link seguro:
                    <a href="onboarding.php" target="_blank" class="link-card">
                        <strong>📄 Formulário SESI Digital (M1)</strong>
                        Clique para preencher agora
                    </a>
                    Assim que terminar, me avise aqui!
                `;
                addMsg(link, 'received', true);
            }
            // Lógica 2: Usuário avisa que terminou
            else if(txt.includes("pronto") || txt.includes("terminei") || txt.includes("enviei")) {
                addMsg("Estou verificando no sistema... ⏳", 'received');
                
                // Verifica no banco de dados real (via fetch no bot_brain ou simulado)
                setTimeout(() => {
                    addMsg("✅ Confirmado! Recebemos a planilha da sua empresa. O processo de PGR já foi iniciado. <br>Você pode acompanhar o status digitando 'Status'.", 'received', true);
                }, 2000);
            }
            // Lógica 3: Status
            else if(txt.includes("status")) {
                // Aqui poderíamos conectar com o bot_brain.php real para pegar o dado do banco
                addMsg("Consultando base de dados... 🔍<br><strong>Status Atual:</strong> Aguardando envio da Planilha M1.", 'received', true);
            }
            // Padrão
            else {
                addMsg("Desculpe, sou uma IA em treinamento. Tente dizer 'Quero iniciar um contrato'.", 'received');
            }
        }

        function handleEnter(e) { if(e.key === 'Enter') enviar(); }
    </script>

</body>
</html>