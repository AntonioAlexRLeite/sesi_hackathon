<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimento SESI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #e5ddd5; display: flex; justify-content: center; height: 100vh; margin: 0; }
        
        .chat-container {
            width: 100%; max-width: 400px; background: #fff; display: flex; flex-direction: column;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); height: 100%;
        }

        .chat-header {
            background-color: #005594; color: white; padding: 15px; display: flex; align-items: center; gap: 10px;
        }
        
        .chat-box {
            flex: 1; padding: 20px; overflow-y: auto; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); /* Fundo estilo Zap */
        }

        .message {
            max-width: 80%; margin-bottom: 10px; padding: 10px 15px; border-radius: 10px; font-size: 14px; line-height: 1.4; position: relative;
        }

        .msg-bot {
            background-color: #fff; align-self: flex-start; border-bottom-left-radius: 0; float: left; clear: both;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .msg-user {
            background-color: #dcf8c6; align-self: flex-end; border-bottom-right-radius: 0; float: right; clear: both;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .input-area {
            padding: 10px; background: #f0f0f0; display: flex; gap: 10px; border-top: 1px solid #ddd;
        }

        input {
            flex: 1; padding: 10px; border-radius: 20px; border: 1px solid #ccc; outline: none;
        }

        button {
            background-color: #005594; color: white; border: none; padding: 10px 15px; border-radius: 50%; cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="chat-container">
        <div class="chat-header">
            <i class="fas fa-robot" style="font-size: 24px;"></i>
            <div>
                <strong>Assistente SESI</strong><br>
                <small>Online agora</small>
            </div>
        </div>

        <div class="chat-box" id="chatBox">
            <div class="message msg-bot">
                Olá! Sou a IA do SESI. Como posso ajudar sua indústria hoje?
            </div>
        </div>

        <div class="input-area">
            <input type="text" id="msgInput" placeholder="Digite sua mensagem..." onkeypress="handleEnter(event)">
            <button onclick="enviarMensagem()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');

        function appendMessage(text, sender) {
            const div = document.createElement('div');
            div.classList.add('message', sender === 'user' ? 'msg-user' : 'msg-bot');
            div.innerText = text;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight; // Rola para baixo
        }

        async function enviarMensagem() {
            const input = document.getElementById('msgInput');
            const msg = input.value.trim();
            
            if (msg === "") return;

            // 1. Mostra mensagem do usuário
            appendMessage(msg, 'user');
            input.value = "";

            // 2. Mostra "Digitando..."
            const loadingDiv = document.createElement('div');
            loadingDiv.classList.add('message', 'msg-bot');
            loadingDiv.innerText = "...";
            loadingDiv.id = "loading";
            chatBox.appendChild(loadingDiv);

            try {
                // 3. Envia para o PHP
                const response = await fetch('bot_brain.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensagem: msg })
                });

                // --- AQUI ESTÁ A MUDANÇA PARA DESCOBRIR O ERRO ---
                const text = await response.text(); // Pega o texto bruto antes de virar JSON
                
                try {
                    const data = JSON.parse(text); // Tenta converter
                    document.getElementById('loading').remove();
                    appendMessage(data.resposta, 'bot');
                } catch (e) {
                    // SE DER ERRO, MOSTRA O QUE O PHP CUSPIU
                    document.getElementById('loading').remove();
                    console.log("Erro Bruto:", text); // Mostra no console
                    alert("ERRO DO PHP:\n\n" + text); // Mostra na tela
                }

            } catch (error) {
                document.getElementById('loading').remove();
                appendMessage("Erro de rede (Servidor desligado?).", 'bot');
            }
        }

        function handleEnter(e) {
            if (e.key === 'Enter') enviarMensagem();
        }
    </script>

</body>
</html>