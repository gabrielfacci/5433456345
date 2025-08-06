<x-filament-panels::page>
    <div class="flex items-start justify-center min-h-screen bg-black pt-16">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-gray-900 rounded-lg shadow-lg p-8 text-white">
                <div class="text-center mb-6">
                    <div class="mx-auto h-20 w-20 bg-primary-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="h-12 w-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-5xl font-bold text-white mb-3">
                        Verificação de Segurança
                    </h2>
                    <p class="text-gray-300">
                        Por favor, insira sua senha de 2FA para acessar o painel administrativo.
                    </p>
                </div>

                <form wire:submit="verify" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                        >
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" wire:loading wire:target="verify" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificar e Acessar
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400">
                        Esta verificação é necessária para proteger o acesso administrativo.
                    </p>
                </div>
            </div>

            <!-- Rodapé -->
            <div class="text-center mt-8 text-gray-400 text-sm">
    Desenvolvido por 
    <a href="https://www.youtube.com/@queminvestecresce"
   target="_blank"
   rel="noopener noreferrer"
   class="text-orange-custom">
   QIC Business
</a>
</div>

<!-- Texto Chamativo -->
<div class="text-center mt-4">
    <div class="bg-gradient-to-r from-red-600 to-orange-600 text-white px-4 py-3 rounded-lg shadow-lg border-2 border-yellow-400 animate-pulse">
        <div class="flex items-center justify-center space-x-2">
            <span class="font-bold text-lg">⚠️ ATENÇÃO ⚠️</span>
        </div>
        <p class="text-sm font-semibold mt-2 leading-tight">
            SCRIPT LIBERADO POR QIC BUSINESS<br>
            <span class="text-yellow-300">SE VOCÊ ADQUIRIU EM OUTRO LUGAR TOMOU GOLPE</span>
        </p>
    </div>
</div>
    </div>

    <style>
    .fi-page {
        background: transparent !important;
    }

    .fi-sidebar {
        display: none !important;
    }

    .fi-header {
        display: none !important;
    }

    .fi-main {
        padding: 0 !important;
    }

    .fi-main-ctn {
        padding: 0 !important;
    }

    /* 👇 AQUI definimos a classe personalizada */
    .text-orange-custom {
        color: #f97316 !important; /* laranja semelhante ao Tailwind orange-500 */
    }

    .text-orange-custom:hover {
        text-decoration: underline;
    }
</style>

</x-filament-panels::page>