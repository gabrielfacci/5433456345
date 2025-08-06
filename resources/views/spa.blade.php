<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BG Games</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <div id="app"></div>
    
    <!-- Indicador do Sistema de Distribuição -->
    <div id="distribution-indicator" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 8px 12px; background: rgba(0,0,0,0.8); color: white; border-radius: 8px; font-family: Arial, sans-serif; font-size: 12px; min-width: 150px; backdrop-filter: blur(10px);">
        <div style="display: flex; align-items: center;">
            <span id="mode-icon" style="margin-right: 8px; font-size: 16px;">💰</span>
            <div>
                <div id="mode-title" style="font-weight: bold;">Arrecadação</div>
                <div id="mode-progress" style="opacity: 0.8; font-size: 10px;">0%</div>
            </div>
        </div>
        <div id="last-update" style="text-align: center; margin-top: 4px; font-size: 10px; opacity: 0.6;"></div>
        <div id="auto-status" style="text-align: center; margin-top: 2px; font-size: 9px; color: #10b981;">🔄 AUTO</div>
    </div>

    <!-- Botão de Teste Manual (apenas para debug) -->
    <div id="test-button" style="position: fixed; top: 80px; right: 20px; z-index: 9999;">
        <button onclick="testManual()" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
            🧪 Teste Manual
        </button>
        <button onclick="testForce()" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-top: 5px;">
            🔥 Forçar Processamento
        </button>
        <div id="auto-status-indicator" style="background: #1f2937; color: white; padding: 4px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; text-align: center;">
            ⏰ Aguardando...
        </div>
    </div>
    
    <!-- Sistema de Distribuição Automático -->
    <script>
        // Automação do Sistema de Distribuição
        let distributionInterval = null;
        let lastUpdateTime = null;
        let timeUpdateInterval = null;
        let autoCounter = 0;
        
        function updateIndicator(data) {
            console.log('🎯 Atualizando indicador visual com dados:', data);
            
            const indicator = document.getElementById('distribution-indicator');
            const modeIcon = document.getElementById('mode-icon');
            const modeTitle = document.getElementById('mode-title');
            const modeProgress = document.getElementById('mode-progress');
            const lastUpdate = document.getElementById('last-update');
            const autoStatus = document.getElementById('auto-status');
            
            if (data.status === 'processed') {
                // Mostra o indicador
                indicator.style.display = 'block';
                
                // Atualiza ícone e título
                if (data.modo === 'arrecadacao') {
                    modeIcon.textContent = '💰';
                    modeTitle.textContent = 'Arrecadação';
                    indicator.style.borderLeft = '4px solid #ef4444';
                } else {
                    modeIcon.textContent = '🎁';
                    modeTitle.textContent = 'Distribuição';
                    indicator.style.borderLeft = '4px solid #10b981';
                }
                
                // Calcula progresso (simplificado)
                const progress = data.modo === 'arrecadacao' 
                    ? Math.min(100, (data.total_arrecadado / 50) * 100)  // Meta fixa de R$ 50
                    : Math.min(100, (data.total_distribuido / 12.5) * 100); // Meta fixa de R$ 12.50
                
                modeProgress.textContent = `${progress.toFixed(1)}%`;
                
                // Atualiza timestamp
                lastUpdateTime = new Date();
                lastUpdate.textContent = 'Agora';
                
                // Atualiza status automático
                autoStatus.textContent = `🔄 AUTO #${autoCounter} (10s)`;
                
                console.log('✅ Indicador atualizado com sucesso');
            } else {
                console.log('⚠️ Sistema inativo ou erro - não atualizando indicador');
            }
        }
        
        function updateTimeDisplay() {
            const lastUpdate = document.getElementById('last-update');
            if (lastUpdateTime && lastUpdate) {
                const now = new Date();
                const diff = Math.floor((now - lastUpdateTime) / 1000); // segundos
                
                if (diff < 60) {
                    lastUpdate.textContent = `${diff}s atrás`;
                } else if (diff < 3600) {
                    lastUpdate.textContent = `${Math.floor(diff / 60)}min atrás`;
                } else {
                    lastUpdate.textContent = `${Math.floor(diff / 3600)}h atrás`;
                }
            }
        }
        
        async function processDistribution() {
            console.log('🔄 Iniciando processamento automático do sistema de distribuição...');
            
            try {
                const response = await fetch('/api/distribution/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                console.log('📡 Resposta do servidor:', response.status);
                
                const data = await response.json();
                console.log('📊 Dados recebidos:', data);
                
                // Atualiza indicador visual
                updateIndicator(data);
                
                if (data.status === 'processed' && data.changed) {
                    console.log(`🔄 Sistema de Distribuição mudou para: ${data.modo.toUpperCase()}`);
                }
                
                // Log opcional do status
                if (data.status === 'processed') {
                    console.log(`💰 Distribuição: ${data.modo} - Arrecadado: R$ ${data.total_arrecadado} - Distribuído: R$ ${data.total_distribuido}`);
                } else if (data.status === 'inactive') {
                    console.log('⚠️ Sistema inativo, tentando processamento forçado...');
                    // Tenta o endpoint forçado
                    await processDistributionForce();
                }
                
            } catch (error) {
                console.error('❌ Erro ao processar distribuição:', error);
                console.log('🔄 Tentando processamento forçado como backup...');
                await processDistributionForce();
            }
        }

        async function processDistributionForce() {
            try {
                const forceResponse = await fetch('/api/distribution/force');
                const forceData = await forceResponse.json();
                console.log('📊 Dados do processamento forçado:', forceData);
                
                if (forceData.status === 'forced') {
                    console.log('✅ Processamento forçado executado!');
                    updateIndicator(forceData);
                }
            } catch (error) {
                console.error('❌ Erro no processamento forçado:', error);
            }
        }
        
        // Teste imediato para verificar se o script está funcionando
        console.log('🔧 Script de distribuição carregado!');
        console.log('🔧 Timestamp:', new Date().toLocaleTimeString());
        
        // Inicia automação quando a página carrega
        window.addEventListener('load', function() {
            console.log('🚀 Página carregada, iniciando sistema de distribuição...');
            console.log('🚀 Timestamp:', new Date().toLocaleTimeString());
            
            // Teste imediato
            console.log('🧪 Teste imediato do sistema...');
            processDistribution();
            
            // Primeira execução após 2 segundos
            setTimeout(() => {
                console.log('⏰ Primeira execução do sistema de distribuição...');
                console.log('⏰ Timestamp:', new Date().toLocaleTimeString());
                processDistribution();
                
                // Executa a cada 30 segundos (30000ms)
                distributionInterval = setInterval(() => {
                    autoCounter++;
                    console.log(`⏰ Execução automática #${autoCounter} do sistema de distribuição...`);
                    console.log(`⏰ Timestamp: ${new Date().toLocaleTimeString()}`);
                    
                    // Atualiza indicador de status
                    const statusIndicator = document.getElementById('auto-status-indicator');
                    if (statusIndicator) {
                        statusIndicator.textContent = `🔄 Executando #${autoCounter}...`;
                        statusIndicator.style.background = '#10b981';
                    }
                    
                    processDistribution();
                }, 30000);
                
                // Atualiza o tempo a cada segundo
                timeUpdateInterval = setInterval(updateTimeDisplay, 1000);
                
                                    console.log('🤖 Sistema de Distribuição automático iniciado (a cada 30 segundos)');
            }, 2000);
        });

                // Também inicia quando o DOM está pronto (backup)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM carregado, iniciando sistema de distribuição...');
            console.log('📄 Timestamp:', new Date().toLocaleTimeString());
            
                            // Executa a cada 30 segundos
                if (!distributionInterval) {
                    distributionInterval = setInterval(() => {
                        autoCounter++;
                        console.log(`⏰ Execução automática (DOM) #${autoCounter} do sistema de distribuição...`);
                        console.log(`⏰ Timestamp: ${new Date().toLocaleTimeString()}`);
                        
                        // Atualiza indicador de status
                        const statusIndicator = document.getElementById('auto-status-indicator');
                        if (statusIndicator) {
                            statusIndicator.textContent = `🔄 Executando #${autoCounter}...`;
                            statusIndicator.style.background = '#10b981';
                        }
                        
                        processDistribution();
                    }, 30000);
                }
        });

        // Sistema de backup adicional - executa sempre
        setInterval(() => {
            if (!distributionInterval) {
                console.log('🔄 Sistema de backup ativado...');
                autoCounter++;
                console.log(`⏰ Backup automático #${autoCounter} do sistema de distribuição...`);
                console.log(`⏰ Timestamp: ${new Date().toLocaleTimeString()}`);
                
                // Atualiza indicador de status
                const statusIndicator = document.getElementById('auto-status-indicator');
                if (statusIndicator) {
                    statusIndicator.textContent = `🔄 Backup #${autoCounter}...`;
                    statusIndicator.style.background = '#f59e0b';
                }
                
                processDistribution();
            }
        }, 30000);
        
        // Função de teste manual
        async function testManual() {
            console.log('🧪 Teste manual iniciado...');
            
            // Primeiro testa o endpoint GET
            try {
                console.log('🔍 Testando endpoint GET...');
                const testResponse = await fetch('/api/distribution/test');
                const testData = await testResponse.json();
                console.log('📊 Dados do teste GET:', testData);
                
                if (testData.status === 'found') {
                    console.log('✅ Sistema encontrado, ativo:', testData.ativo);
                    
                    if (testData.ativo) {
                        console.log('🔄 Sistema ativo, testando processamento...');
                        processDistribution();
                    } else {
                        console.log('⚠️ Sistema inativo! Ative no Filament primeiro.');
                    }
                } else {
                    console.log('❌ Sistema não encontrado');
                }
            } catch (error) {
                console.error('❌ Erro no teste GET:', error);
            }
        }

        // Função de teste forçado
        async function testForce() {
            console.log('🔥 Teste forçado iniciado...');
            
            try {
                console.log('🔍 Testando endpoint FORCE...');
                const forceResponse = await fetch('/api/distribution/force');
                const forceData = await forceResponse.json();
                console.log('📊 Dados do teste FORCE:', forceData);
                
                if (forceData.status === 'forced') {
                    console.log('✅ Processamento forçado executado!');
                    console.log('- Modo:', forceData.modo);
                    console.log('- Arrecadado:', forceData.total_arrecadado);
                    console.log('- Distribuído:', forceData.total_distribuido);
                    console.log('- Mudou:', forceData.changed);
                    
                    // Atualiza indicador visual
                    updateIndicator(forceData);
                } else {
                    console.log('❌ Erro no processamento forçado');
                }
            } catch (error) {
                console.error('❌ Erro no teste FORCE:', error);
            }
        }

        // Cleanup quando a página for fechada
        window.addEventListener('beforeunload', function() {
            if (distributionInterval) {
                clearInterval(distributionInterval);
            }
            if (timeUpdateInterval) {
                clearInterval(timeUpdateInterval);
            }
        });
    </script>
    
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html> 