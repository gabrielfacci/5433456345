const CACHE_NAME = 'bggames-v1';
const urlsToCache = [
  '/',
  '/offline' // rota fallback opcional
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        // Adicionar recursos um por um para evitar falhas em lote
        const cachePromises = urlsToCache.map(url => {
          return cache.add(url).catch(error => {
            console.warn('Failed to cache:', url, error);
            // Não falhar se um recurso não puder ser cacheado
            return Promise.resolve();
          });
        });
        
        return Promise.all(cachePromises);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Se a requisição foi bem-sucedida e for GET, cachear para uso offline
        if (response.status === 200 && event.request.method === 'GET') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Se a requisição falhou, tentar buscar do cache
        return caches.match(event.request).then(response => {
          if (response) {
            return response;
          }
          // Se não encontrar no cache, retornar página offline
          return caches.match('/offline');
        });
      })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
