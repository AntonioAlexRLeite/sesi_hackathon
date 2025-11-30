<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coleta SESI Planilha E1 - IA OCR</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        .custom-scroll::-webkit-scrollbar { width: 8px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #10B981; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #059669; }

        .required-marker { color: #DC2626; font-weight: bold; margin-left: 2px; }
        
        /* Animação de Carregamento para o OCR */
        .scan-line {
            width: 100%;
            height: 2px;
            background: #10B981;
            position: absolute;
            top: 0;
            left: 0;
            animation: scan 2s infinite linear;
            display: none;
        }
        @keyframes scan {
            0% { top: 0; }
            50% { top: 100%; }
            100% { top: 0; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl mr-2">📝</span>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight">SESI Planilha E1 <span class="text-emerald-600">com IA</span></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-slate-500 hidden sm:block">
                        <span class="font-semibold text-emerald-600" id="totalRecordsDisplay">0</span> registros
                    </div>
                    <button onclick="scrollToExport()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                        Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <section class="bg-white rounded-xl shadow-lg border border-indigo-100 overflow-hidden relative">
            <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center">
                <h2 class="text-white text-lg font-semibold flex items-center">
                    <span class="mr-2">📸</span> Preenchimento Automático (IA)
                </h2>
                <span class="text-indigo-200 text-xs">Beta</span>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">Carregue uma foto de documento (RG, CNH ou Ficha de Registro) para a IA tentar extrair os dados.</p>
                
                <div class="border-2 border-dashed border-indigo-300 rounded-lg p-6 flex flex-col items-center justify-center bg-indigo-50 hover:bg-indigo-100 transition-colors relative" id="dropZone">
                    <div class="scan-line" id="scanLine"></div>
                    <input type="file" id="documentImage" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="processImage(this)">
                    <span class="text-4xl mb-2">📤</span>
                    <p class="text-indigo-700 font-medium">Clique ou arraste a foto do documento aqui</p>
                    <p class="text-xs text-indigo-400 mt-1" id="ocrStatus">Aguardando imagem...</p>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="prose max-w-none">
                <h2 class="text-lg font-semibold text-slate-900 mb-2">Instruções Planilha E1</h2>
                <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <h3 class="font-bold text-sm text-slate-700 mb-2">LEGENDA DA PLANILHA</h3>
                    <ul class="text-sm space-y-1">
                        <li class="flex items-center"><span class="text-red-600 font-bold mr-2 text-md">🔴</span><span class="font-semibold text-red-700">Obrigatórios</span>: Campos com <span class="required-marker">*</span>.</li>
                        <li class="flex items-center"><span class="text-gray-500 font-bold mr-2 text-md">⚪</span><span class="text-gray-700">Opcionais</span>.</li>
                        <li class="flex items-center"><span class="text-blue-500 font-bold mr-2 text-md">🔵</span><span class="text-gray-700">Condicionais</span>.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-lg border border-emerald-100 overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                <h2 class="text-white text-lg font-semibold flex items-center">
                    <span class="mr-2">👤</span> Dados do Funcionário
                </h2>
                <button type="button" onclick="clearForm()" class="text-slate-300 hover:text-white text-sm underline">Limpar</button>
            </div>
            
            <div class="p-6">
                <form id="eSocialForm">
                    <div class="max-h-[700px] overflow-y-auto custom-scroll scroll-smooth p-2" id="formScrollArea">
                        <div id="dynamicFieldsContainer" class="space-y-8">
                            </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center">
                            <span class="mr-2">💾</span> Salvar Registro
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="previewSection">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">Registros (<span id="countDisplay">0</span>)</h3>
                <button onclick="clearAllData()" class="text-red-500 hover:text-red-700 text-sm font-medium">Excluir Tudo</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ações</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Matrícula</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Cargo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">CPF</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Admissão</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody" class="bg-white divide-y divide-slate-200">
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Nenhum registro ainda.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="flex justify-center pb-12" id="exportSection">
            <button onclick="downloadCSV()" id="mainExportBtn" class="group relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-emerald-600 to-green-500 group-hover:from-emerald-600 group-hover:to-green-500 hover:text-white focus:ring-4 focus:outline-none focus:ring-green-200 opacity-50 cursor-not-allowed" disabled>
                <span class="relative px-8 py-3.5 transition-all ease-in duration-75 bg-white rounded-md group-hover:bg-opacity-0 text-lg font-bold">
                    📥 Baixar Tabela CSV (SESI E1)
                </span>
            </button>
        </section>

    </main>

    <div id="toast" class="fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-xl transform translate-y-20 opacity-0 transition-all duration-300 flex items-center z-50 text-white">
        <span id="toastMsg">Mensagem</span>
    </div>

    <script>
        // --- 1. DEFINIÇÃO DOS CAMPOS (SESI E1 - 112 COLUNAS) ---
        const HEADERS_LIST = [
            "Cod.Unid", "Nome Unidade", "Cod.Setor", "Nome Setor", "Cod.Cargo", "Nome Cargo", 
            "Matrícula", "Cod Funcionário", "Nome Funcionário", "Dt.Nascimento", "Sexo", "Situação", 
            "Dt.Admissão", "Dt.Demissão", "Estado Civil", "Pis/Pasep", "Contratação", "Rg", 
            "UF-RG", "CPF", "CTPS", "Endereço", "Bairro", "Cidade", "UF", "Cep", "Tel", 
            "Naturalidade", "Cor", "E-mail", "Deficiencia", "CBO", "GFIP", "Endereço Unidade", 
            "Bairro Unidade", "Cidade Unidade", "Estado Unidade", "Cep Unidade", "CNPJ Unidade", 
            "Inscrição Unidade", "Tel1 Unidade", "Tel2 Unidade", "Tel3 Unidade", "Tel4 Unidade", 
            "Contato Unid", "Cnae", "Número Endereço Funcionário", "Complemento Endereço Funcionário", 
            "Razão Social Unid.", "Nome da Mae do Funcionário", "Cod.Centro Custo", "Dt. Ultima Movimentação", 
            "Cod. Unidade contratante", "Razão Social", "CNPJ", "Turno", "Dt.Emissão.Cart.Prof", 
            "Série CTPS", "CNAE 2.0", "CNAE Livre", "Descrição CNAE Livre", "CEI", "Função", 
            "CNAE 7", "Tipo de CNAE Utilizado", "Descrição Detalhada do Cargo", "Nº endereço Unidade", 
            "Complemento endereço Unidade", "Regime de Revezamento", "Orgão Expedidor do RG", "Campo Livre 1", 
            "Campo Livre 2", "Campo Livre 3", "Telefone SMS", "Grau de Risco", "UF CTPS", "Nome Centro Custo", 
            "Autoriza SMS", "Endereço Cobrança Unidade", "Número Endereço Cobrança Unidade", 
            "Bairro Cobrança Unidade", "Cidade Cobrança Unidade", "Estado Cobrança Unidade", 
            "Cep Cobrança Unidade", "Complemento Endereço Cobrança Unidade", "Remuneração Mensal (R$)", 
            "Telefone Comercial", "Telefone Celular", "Data Emissão RG", "Código do País de Nascimento", 
            "Origem Descrição Detalhada", "Unidade Contratante", "Escolaridade", "Código Categoria (eSocial)", 
            "Matrícula RH", "Gênero", "Nome Social", "Tipo de Admissão", "Grau de Instrução", 
            "Nome do Pai do Funcionário", "Tipo de Vínculo", "Nome do Turno", "Campo Livre 4", 
            "CPF Unidade", "CAEPF Unidade", "Tipo Sanguíneo", "Dt. Inicio Periodo Aquisitivo", 
            "Dt. Fim Periodo Aquisitivo", "CNO Unidade", "Desconsiderar para o eSocial", 
            "Dt. Validade RG", "Desconsiderar Unidade para o eSocial"
        ];

        const REQUIRED_INDICES = [0, 1, 5, 6, 8, 9, 10, 12, 19, 21, 23, 24, 47];
        const DATE_INDICES = [9, 12, 13, 51, 56, 88, 106, 107, 110];
        
        const SECTIONS = {
            0: { title: "1. Dados da Unidade e Cargo" },
            8: { title: "2. Identificação e Documentos (Funcionário)" },
            21: { title: "3. Endereço e Contato" },
            33: { title: "4. Dados da Empresa (Unidade)" },
            50: { title: "5. Dados Contratuais e Profissionais" },
            79: { title: "6. Cobrança" },
            86: { title: "7. Outros Dados" }
        };

        let db = [];

        // --- 2. LÓGICA DE IA / OCR (IMAGEM PARA TEXTO) ---
        async function processImage(input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            const statusText = document.getElementById('ocrStatus');
            const scanLine = document.getElementById('scanLine');

            // UI Update
            statusText.innerText = "Processando imagem com IA... Aguarde.";
            statusText.className = "text-xs text-emerald-600 font-bold mt-1 animate-pulse";
            scanLine.style.display = 'block';

            try {
                // Inicializa o Worker do Tesseract
                const worker = await Tesseract.createWorker('por'); // 'por' para Português
                
                // Reconhece o texto
                const ret = await worker.recognize(file);
                const text = ret.data.text;
                
                await worker.terminate();

                console.log("Texto extraído:", text); // Para debug no console
                fillFormFromText(text);

                statusText.innerText = "Dados extraídos com sucesso! Verifique o formulário.";
                statusText.className = "text-xs text-green-600 font-bold mt-1";
                showToast("Dados extraídos da imagem!", "success");

            } catch (error) {
                console.error(error);
                statusText.innerText = "Erro ao ler imagem. Tente uma foto mais nítida.";
                statusText.className = "text-xs text-red-500 mt-1";
                showToast("Falha no reconhecimento da imagem.", "error");
            } finally {
                scanLine.style.display = 'none';
                input.value = ""; // Reset input
            }
        }

        // Lógica de Regex para encontrar dados no texto bagunçado do OCR
        function fillFormFromText(text) {
            // Limpeza básica
            const cleanText = text.replace(/\n/g, " ").toUpperCase(); 

            // 1. CPF (Padrão: 000.000.000-00 ou 00000000000)
            const cpfMatch = text.match(/\d{3}\.?\d{3}\.?\d{3}-?\d{2}/);
            if (cpfMatch) setVal('f_19', cpfMatch[0].replace(/[^\d]/g, "")); // Indice 19 = CPF

            // 2. Data de Nascimento (Procura padrão de data próximo a palavra Nascimento)
            // Regex simples de data: \d{2}/\d{2}/\d{4}
            const dates = text.match(/\d{2}\/\d{2}\/\d{4}/g);
            if (dates && dates.length > 0) {
                // Assume a primeira data encontrada como nascimento se houver dúvida, 
                // ou tenta achar a data mais antiga (provavelmente nascimento)
                // Para simplificar, pegamos a primeira data encontrada no documento.
                // O formato do input date é YYYY-MM-DD
                const parts = dates[0].split('/');
                const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                setVal('f_9', isoDate); // Indice 9 = Dt Nascimento
            }

            // 3. PIS (Padrão: 000.00000.00-0)
            const pisMatch = text.match(/\d{3}\.?\d{5}\.?\d{2}-?\d{1}/);
            if (pisMatch) setVal('f_15', pisMatch[0].replace(/[^\d]/g, "")); // Indice 15 = PIS

            // 4. RG (Padrão genérico, varia muito, tenta pegar sequencia numérica grande)
            const rgMatch = text.match(/\d{2}\.?\d{3}\.?\d{3}-?[0-9X]/);
            if (rgMatch) setVal('f_17', rgMatch[0]); // Indice 17 = RG

            // 5. Nome (Muito difícil com OCR genérico sem layout fixo)
            // Tentativa: Pegar a linha que contem "NOME" e limpar
            const lines = text.split('\n');
            for (let i = 0; i < lines.length; i++) {
                if (lines[i].toUpperCase().includes("NOME")) {
                    // Tenta pegar o nome na mesma linha ou na próxima
                    let nameCandidate = lines[i].replace(/NOME/i, "").replace(/DO|DA|DE/i, "").replace(/[:.]/g, "").trim();
                    if (nameCandidate.length < 3 && i + 1 < lines.length) {
                        nameCandidate = lines[i+1].trim();
                    }
                    if (nameCandidate.length > 3) {
                        setVal('f_8', nameCandidate); // Indice 8 = Nome Funcionario
                        break;
                    }
                }
            }
        }

        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el) {
                el.value = val;
                // Efeito visual para mostrar que foi preenchido
                el.classList.add('bg-emerald-50', 'border-emerald-500');
                setTimeout(() => el.classList.remove('bg-emerald-50', 'border-emerald-500'), 2000);
            }
        }

        // --- 3. LÓGICA DO FORMULÁRIO (GERAÇÃO E EXPORTAÇÃO) ---

        function showToast(msg, type) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.textContent = msg;
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-xl transform transition-all duration-300 flex items-center z-50 text-white ${type === 'success' ? 'bg-emerald-600' : 'bg-red-600'}`;
            setTimeout(() => toast.classList.remove('translate-y-20', 'opacity-0'), 10);
            setTimeout(() => toast.classList.add('translate-y-20', 'opacity-0'), 3000);
        }

        function init() {
            renderForm();
            updatePreviewTable();
        }

        function renderForm() {
            const container = document.getElementById('dynamicFieldsContainer');
            let currentGrid = null;

            HEADERS_LIST.forEach((header, index) => {
                if (SECTIONS[index]) {
                    const sectionDiv = document.createElement('div');
                    sectionDiv.className = "pt-6 mb-4 col-span-full";
                    sectionDiv.innerHTML = `
                        <h3 class="text-xl font-bold text-slate-800 border-b border-emerald-500 pb-1 mb-4 mt-2">
                            ${SECTIONS[index].title}
                        </h3>
                    `;
                    container.appendChild(sectionDiv);
                    
                    currentGrid = document.createElement('div');
                    currentGrid.className = "grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6";
                    container.appendChild(currentGrid);
                }

                if (!currentGrid) {
                    currentGrid = document.createElement('div');
                    currentGrid.className = "grid grid-cols-1 md:grid-cols-2 gap-4";
                    container.appendChild(currentGrid);
                }

                const wrapper = document.createElement('div');
                const isRequired = REQUIRED_INDICES.includes(index);
                
                const label = document.createElement('label');
                label.className = "block text-sm font-medium text-slate-700 mb-1";
                label.innerHTML = `${index + 1}. ${header} ${isRequired ? '<span class="required-marker">*</span>' : ''}`;
                
                let input;
                // Campo Sexo
                if (index === 10) { 
                    input = document.createElement('select');
                    input.innerHTML = `<option value="">Selecione</option><option value="M">Masculino</option><option value="F">Feminino</option>`;
                } 
                // Campo Situação Padrão
                else if (index === 11) { 
                     input = document.createElement('input');
                     input.type = 'text';
                     input.value = 'A'; // Padrão Ativo
                } else {
                    input = document.createElement('input');
                    input.type = DATE_INDICES.includes(index) ? 'date' : 'text';
                }

                input.id = `f_${index}`;
                input.name = `f_${index}`;
                input.className = "w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm border p-2.5 transition-all";
                if (isRequired) input.required = true;

                // Valores Padrão para facilitar (Matriz, SP, Códigos)
                if (index === 0) input.value = "001";
                if (index === 1) input.value = "Matriz";
                if (index === 18) input.value = "SP";
                if (index === 24) input.value = "SP";
                if (index === 90) input.value = "105";

                wrapper.appendChild(label);
                wrapper.appendChild(input);
                currentGrid.appendChild(wrapper);
            });
        }

        document.getElementById('eSocialForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const row = new Array(HEADERS_LIST.length).fill("");
            HEADERS_LIST.forEach((_, i) => {
                const el = document.getElementById(`f_${i}`);
                if (el) row[i] = el.value.trim();
            });
            db.push(row);
            updatePreviewTable();
            toggleExportBtn();
            this.reset();
            // Resetar padrões
            document.getElementById('f_0').value = "001";
            document.getElementById('f_1').value = "Matriz";
            document.getElementById('f_11').value = "A";
            document.getElementById('f_18').value = "SP";
            document.getElementById('f_24').value = "SP";
            document.getElementById('f_90').value = "105";
            showToast("Registro adicionado com sucesso!", "success");
            document.getElementById('formScrollArea').scrollTop = 0;
        });

        function updatePreviewTable() {
            const tbody = document.getElementById('previewBody');
            const countDisplay = document.getElementById('countDisplay');
            const totalRecordsDisplay = document.getElementById('totalRecordsDisplay');
            
            countDisplay.innerText = db.length;
            totalRecordsDisplay.innerText = db.length;

            if (db.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Nenhum registro adicionado ainda.</td></tr>`;
                return;
            }

            const recent = db.slice().reverse().slice(0, 5);
            tbody.innerHTML = recent.map((row, idx) => {
                const originalIdx = db.length - 1 - idx;
                return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="removeRecord(${originalIdx})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2 py-1 rounded">Excluir</button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${row[6]}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-semibold">${row[8]}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">${row[5]}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">${row[19]}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">${formatDate(row[12])}</td>
                </tr>
                `;
            }).join('');
        }

        function removeRecord(index) {
            if (confirm("Remover este registro?")) {
                db.splice(index, 1);
                updatePreviewTable();
                toggleExportBtn();
                showToast("Registro removido.", "error");
            }
        }

        function clearAllData() {
            if (confirm("ATENÇÃO: Isso apagará TODOS os registros inseridos. Continuar?")) {
                db = [];
                updatePreviewTable();
                toggleExportBtn();
                showToast("Todos os dados foram limpos.", "error");
            }
        }

        function clearForm() {
            if (confirm("Limpar formulário atual?")) {
                document.getElementById('eSocialForm').reset();
            }
        }

        function formatDate(dateStr) {
            if (!dateStr || !dateStr.includes('-')) return dateStr;
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        function toggleExportBtn() {
            const btn = document.getElementById('mainExportBtn');
            if (db.length > 0) {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('cursor-pointer', 'opacity-100');
            } else {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.remove('cursor-pointer', 'opacity-100');
            }
        }

        function scrollToExport() {
            document.getElementById('exportSection').scrollIntoView({ behavior: 'smooth' });
        }

        function downloadCSV() {
            if (db.length === 0) {
                showToast("Nenhum dado para exportar!", "error");
                return;
            }
            // CSV com Separador de ponto e vírgula (Padrão Excel Brasileiro) pode ser melhor, mas usaremos vírgula padrão global.
            // Cabeçalho
            let csvContent = "Modelo 1" + ",".repeat(HEADERS_LIST.length - 1) + "\n";
            csvContent += HEADERS_LIST.map(h => `"${h}"`).join(",") + "\n";

            // Dados
            db.forEach(row => {
                const formattedRow = row.map((val, idx) => {
                    if (val && DATE_INDICES.includes(idx)) {
                        return formatDate(val);
                    }
                    if (typeof val === 'string') {
                        const clean = val.replace(/"/g, '""').replace(/\n/g, ' ');
                        if (clean.includes(',') || clean.includes('"')) {
                            return `"${clean}"`;
                        }
                        return clean;
                    }
                    return val;
                });
                csvContent += formattedRow.join(",") + "\n";
            });

            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", "SESI_E1_Importacao.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast("Download iniciado!", "success");
        }

        init();
    </script>
</body>
</html>