<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="theme-color" content="#d58c00">  
        <link rel="icon" type="image/png" sizes="192x192" href="https://raspa.bggames.site/storage/uploads/pbP7W9bKZmZBKOJjH57iaqG538RiKUzTBjueUzVe.png">
      
      <!-- Meta Tags Essenciais -->
        <title>RaspouGanhou - A raspadinha número 1 do Brasil</title>
        <meta name="description" content="Jogue na CorujaBET e descubra o melhor cassino online ao vivo! Aposte em esportes e cassino com bônus exclusivos. Depósitos rápidos via Pix.">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="RaspouGanhou - A raspadinha número 1 do Brasil">
        <meta property="og:description" content="Raspe na RaspouGanhou! A raspadinha número 1 do Brasil. Depósitos via Pix e saques instantâneos.">
        <meta property="og:image" content="https://raspa.bggames.site/storage/uploads/pbP7W9bKZmZBKOJjH57iaqG538RiKUzTBjueUzVe.png">
        <meta property="og:url" content="https://corujabet.site">
        <meta property="og:site_name" content="Coruja BET">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="RaspouGanhou - A raspadinha número 1 do Brasil">
        <meta name="twitter:description" content="Entre no mundo das apostas com CorujaBET! Cassino ao vivo, jogos exclusivos e bônus especiais para novos jogadores.">
        <meta name="twitter:image" content="https://raspa.bggames.site/storage/uploads/pbP7W9bKZmZBKOJjH57iaqG538RiKUzTBjueUzVe.png">

        <!-- SEO Keywords -->
        <meta name="keywords" content="Cassino online, apostas esportivas, jogos de azar, slots, roleta, blackjack, poker, cassino ao vivo, bônus cassino, CorujaBET, apostas com Pix">

        <!-- Robots (Indexação) -->
        <meta name="robots" content="noindex, nofollow">

        <!-- Canonical -->
        <link rel="canonical" href="https://corujabet.site">

        @php $setting = \Helper::getSetting() @endphp
        @if(!empty($setting['software_favicon']))
            <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/storage/' . $setting['software_favicon']) }}">
        @endif

        <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700&family=Roboto+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100&display=swap" rel="stylesheet">        
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="manifest" href="/manifest.json">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


        <title>{{ env('APP_NAME') }}</title>
        
        <!-- Código Base do Pixel do Facebook -->
        <script>
          !function(f,b,e,v,n,t,s)
          {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};
          if(!f._fbq)f._fbq=n;
          n.push=n;
          n.loaded=!0;
          n.version='2.0';
          n.queue=[];
          t=b.createElement(e);
          t.async=!0;
          t.src=v;
          s=b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t,s)}
          (window, document,'script',
          'https://connect.facebook.net/en_US/fbevents.js');
          
          fbq('init', "{{ env('FB_ID') }}"); // Substitua pelo seu ID do Pixel
          fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
          src="https://www.facebook.com/tr?id={{ env('FB_ID') }}&ev=PageView&noscript=1"
        /></noscript>


        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php $custom = \Helper::getCustom() @endphp
        <style>
            body{
                /* font-family: "'Roboto', sans-serif"; */
                font-family: "Montserrat", sans-serif;
            }
            :root {
                --ci-primary-color: {{ $custom['primary_color'] }};
                --ci-primary-opacity-color: {{ $custom['primary_opacity_color'] }};
                --ci-secundary-color: {{ $custom['secundary_color'] }};
                --ci-gray-dark: {{ $custom['gray_dark_color'] }};
                --ci-gray-light: {{ $custom['gray_light_color'] }};
                --ci-gray-medium: {{ $custom['gray_medium_color'] }};
                --ci-gray-over: {{ $custom['gray_over_color'] }};
                --title-color: {{ $custom['title_color'] }};
                --text-color: {{ $custom['text_color'] }};
                --sub-text-color: {{ $custom['sub_text_color'] }};
                --placeholder-color: {{ $custom['placeholder_color'] }};
                --background-color: {{ $custom['background_color'] }};
                --standard-color: #1C1E22;
                --shadow-color: #111415;
                --page-shadow: linear-gradient(to right, #111415, rgba(17, 20, 21, 0));
                --autofill-color: #f5f6f7;
                --yellow-color: #FFBF39;
                --yellow-dark-color: #d7a026;
                --border-radius: {{ $custom['border_radius'] }};
                --tw-border-spacing-x: 0;
                --tw-border-spacing-y: 0;
                --tw-translate-x: 0;
                --tw-translate-y: 0;
                --tw-rotate: 0;
                --tw-skew-x: 0;
                --tw-skew-y: 0;
                --tw-scale-x: 1;
                --tw-scale-y: 1;
                --tw-scroll-snap-strictness: proximity;
                --tw-ring-offset-width: 0px;
                --tw-ring-offset-color: #fff;
                --tw-ring-color: rgba(59,130,246,.5);
                --tw-ring-offset-shadow: 0 0 #0000;
                --tw-ring-shadow: 0 0 #0000;
                --tw-shadow: 0 0 #0000;
                --tw-shadow-colored: 0 0 #0000;

                --input-primary: {{ $custom['input_primary'] }};
                --input-primary-dark: {{ $custom['input_primary_dark'] }};

                --carousel-banners: {{ $custom['carousel_banners'] }};
                --carousel-banners-dark: {{ $custom['carousel_banners_dark'] }};


                --sidebar-color: {{ $custom['sidebar_color'] }} !important;
                --sidebar-color-dark: {{ $custom['sidebar_color_dark'] }} !important;


                --navtop-color {{ $custom['navtop_color'] }};
                --navtop-color-dark: {{ $custom['navtop_color_dark'] }};


                --side-menu {{ $custom['side_menu'] }};
                --side-menu-dark: {{ $custom['side_menu_dark'] }};

                --footer-color {{ $custom['footer_color'] }};
                --footer-color-dark: {{ $custom['footer_color_dark'] }};

                --card-color {{ $custom['card_color'] }};
                --card-color-dark: {{ $custom['card_color_dark'] }};
            }
            .navtop-color{
                background-color: {{ $custom['sidebar_color'] }} !important;
            }
            :is(.dark .navtop-color) {
                background-color: {{ $custom['sidebar_color_dark'] }} !important;
            }

            .bg-base {
                background-color: {{ $custom['background_base'] }};
            }
            :is(.dark .bg-base) {
                background-color: {{ $custom['background_base_dark'] }};
            }
            /* Oculta a scrollbar em navegadores WebKit */
            ::-webkit-scrollbar {
              display: none !important;
            }
            
            /* Para garantir que o conteúdo ainda seja rolável */
            html, body {
              scrollbar-width: none !important; /* Firefox */
              -ms-overflow-style: none !important; /* Internet Explorer e Edge */
            }
        </style>

        @if(!empty($custom['custom_css']))
            <style>
                {!! $custom['custom_css'] !!}
            </style>
        @endif

        @if(!empty($custom['custom_header']))
            {!! $custom['custom_header'] !!}
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
      <script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'pt',
      includedLanguages: 'en,es,fr,de,it',  // idiomas que quer oferecer
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "115d1f72-90d3-46d2-9e56-b3760f6b74c4",
    });
  });
</script>

    </head>
    <body color-theme="dark" class="bg-base text-gray-800 dark:text-gray-300 ">
        <div id="viperpro"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.0.0/datepicker.min.js"></script>
        <script>
            window.Livewire?.on('copiado', (texto) => {
                navigator.clipboard.writeText(texto).then(() => {
                    Livewire.emit('copiado');
                });
            });

            window._token = '{{ csrf_token() }}';
            window.custom = @json($custom);
            //if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.remove('dark')
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.remove('light')
                document.documentElement.classList.add('dark')
            }
        </script>

        @if(!empty($custom['custom_js']))
            <script>
                {!! $custom['custom_js'] !!}
            </script>
        @endif

        @if(!empty($custom['custom_body']))
            {!! $custom['custom_body'] !!}
        @endif

        @if(!empty($custom))
            <script>
                const custom = {!! json_encode($custom)  !!};
            </script>
        @endif
     
      
      <script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
      .then(function(registration) {
        console.log('Service Worker registrado com sucesso:', registration.scope);
      })
      .catch(function(error) {
        console.log('Falha no registro do Service Worker:', error);
      });
  }
</script>

<!-- Popup de Demonstração -->
<div id="demoPopup" class="popup-overlay">
    <div class="popup-box">
        <div class="popup-close" onclick="document.getElementById('demoPopup').style.display='none'">&times;</div>
        <h2>🚨 ESSE SITE É UMA DEMONSTRAÇÃO</h2>
        <p>Para adquirir ele por apenas <strong>R$97,90</strong>, clique no botão abaixo:</p>
        <br>
        <a href="https://bio.queminvestecresce.com.br/" target="_blank">ACESSAR AQUI</a>
    </div>
</div>

<!-- Estilo do Pop-up -->
<style>
    .popup-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .popup-box {
        position: relative;
        background: #222;
        padding: 30px 20px 20px;
        border-radius: 12px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        font-family: Arial, sans-serif;
        color: #fff;
    }

    .popup-box h2 {
        font-size: 20px;
        color: #d58c00;
        margin-bottom: 20px;
    }

    .popup-box p {
        color: #fff;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .popup-box a {
        display: inline-block;
        background: #d58c00;
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 6px;
        transition: background 0.3s ease;
    }

    .popup-box a:hover {
        background: #b57400;
    }

    .popup-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        color: white;
        cursor: pointer;
    }
</style>



    </body>
</html>
