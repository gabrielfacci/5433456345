<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status da Distribuição</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .progress-bar {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">🎰 Sistema de Distribuição</h1>
                <p class="text-gray-600">Monitoramento em tempo real</p>
            </div>

            <!-- Status Card -->
            <div class="status-card rounded-lg shadow-lg p-6 mb-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold" id="modo-atual">Carregando...</h2>
                        <p class="text-blue-100" id="status-text">Verificando status...</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-blue-100">Última Atualização</div>
                        <div class="font-mono text-lg" id="ultima-atualizacao">--:--:--</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Arrecadação -->
                    <div class="bg-white bg-opacity-20 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-2">💰 Arrecadação</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Total:</span>
                                <span class="font-mono" id="total-arrecadado">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Meta:</span>
                                <span class="font-mono" id="meta-arrecadacao">R$ 0,00</span>
                            </div>
                            <div class="w-full bg-gray-300 rounded-full h-2">
                                <div class="progress-bar h-2 rounded-full transition-all duration-500" 
                                     id="progresso-arrecadacao" style="width: 0%"></div>
                            </div>
                            <div class="text-center text-sm" id="percentual-arrecadacao">0%</div>
                        </div>
                    </div>

                    <!-- Distribuição -->
                    <div class="bg-white bg-opacity-20 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-2">🎁 Distribuição</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Total:</span>
                                <span class="font-mono" id="total-distribuido">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Meta:</span>
                                <span class="font-mono" id="meta-distribuicao">R$ 0,00</span>
                            </div>
                            <div class="w-full bg-gray-300 rounded-full h-2">
                                <div class="progress-bar h-2 rounded-full transition-all duration-500" 
                                     id="progresso-distribuicao" style="width: 0%"></div>
                            </div>
                            <div class="text-center text-sm" id="percentual-distribuicao">0%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log de Atividades -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4">📋 Log de Atividades</h3>
                <div id="log-atividades" class="space-y-2 max-h-64 overflow-y-auto">
                    <div class="text-gray-500 text-center">Aguardando atividades...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ultimaMensagem = '';
        
        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }

        function adicionarLog(mensagem, tipo = 'info') {
            const log = document.getElementById('log-atividades');
            const timestamp = new Date().toLocaleTimeString('pt-BR');
            
            const cores = {
                'info': 'text-blue-600',
                'success': 'text-green-600',
                'warning': 'text-yellow-600',
                'error': 'text-red-600'
            };
            
            const logEntry = document.createElement('div');
            logEntry.className = `text-sm ${cores[tipo]}`;
            logEntry.innerHTML = `<span class="font-mono">[${timestamp}]</span> ${mensagem}`;
            
            log.insertBefore(logEntry, log.firstChild);
            
            // Manter apenas os últimos 20 logs
            const logs = log.children;
            if (logs.length > 20) {
                log.removeChild(logs[logs.length - 1]);
            }
        }

        function atualizarStatus() {
            fetch('/api/distribution/status')
                .then(response => response.json())
                .then(data => {
                    // Atualizar modo atual
                    const modoElement = document.getElementById('modo-atual');
                    const modoText = data.modo === 'arrecadacao' ? '💰 Modo: Arrecadação' : '🎁 Modo: Distribuição';
                    modoElement.textContent = modoText;
                    
                    // Atualizar valores
                    document.getElementById('total-arrecadado').textContent = formatarMoeda(data.total_arrecadado);
                    document.getElementById('total-distribuido').textContent = formatarMoeda(data.total_distribuido);
                    document.getElementById('meta-arrecadacao').textContent = formatarMoeda(data.meta_arrecadacao);
                    document.getElementById('meta-distribuicao').textContent = formatarMoeda(data.meta_distribuicao);
                    
                    // Atualizar progresso
                    const progressoArrecadacao = document.getElementById('progresso-arrecadacao');
                    const percentualArrecadacao = document.getElementById('percentual-arrecadacao');
                    const progressoDistribuicao = document.getElementById('progresso-distribuicao');
                    const percentualDistribuicao = document.getElementById('percentual-distribuicao');
                    
                    if (data.modo === 'arrecadacao') {
                        progressoArrecadacao.style.width = Math.min(data.progresso_arrecadacao, 100) + '%';
                        percentualArrecadacao.textContent = Math.min(data.progresso_arrecadacao, 100).toFixed(1) + '%';
                        progressoDistribuicao.style.width = '0%';
                        percentualDistribuicao.textContent = '0%';
                    } else {
                        progressoArrecadacao.style.width = '100%';
                        percentualArrecadacao.textContent = '100%';
                        progressoDistribuicao.style.width = Math.min(data.progresso_distribuicao, 100) + '%';
                        percentualDistribuicao.textContent = Math.min(data.progresso_distribuicao, 100).toFixed(1) + '%';
                    }
                    
                    // Atualizar última atualização
                    document.getElementById('ultima-atualizacao').textContent = data.ultima_atualizacao || '--:--:--';
                    
                    // Verificar se houve mudança
                    if (data.mudou && data.mensagem !== ultimaMensagem) {
                        adicionarLog(data.mensagem, 'success');
                        ultimaMensagem = data.mensagem;
                        
                        // Notificação visual
                        const statusText = document.getElementById('status-text');
                        statusText.textContent = data.mensagem;
                        statusText.className = 'text-green-200 pulse';
                        setTimeout(() => {
                            statusText.className = 'text-blue-100';
                        }, 3000);
                    }
                    
                    // Log de verificação
                    adicionarLog(`Verificação automática - ${data.modo}`, 'info');
                })
                .catch(error => {
                    console.error('Erro ao atualizar status:', error);
                    adicionarLog('Erro ao conectar com o servidor', 'error');
                });
        }

        // Atualizar a cada 10 segundos
        setInterval(atualizarStatus, 10000);
        
        // Primeira atualização
        atualizarStatus();
        
        // Adicionar log inicial
        adicionarLog('Sistema iniciado - Polling automático ativo', 'info');
    </script>
</body>
</html> 