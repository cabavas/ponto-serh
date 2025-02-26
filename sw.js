const CACHE_NAME = 'ponto-serh-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/login.php',
    '/registrarPonto.php',
    '/assets/*',
    '/manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
  ];
  

self.addEventListener('fetch', event => {
    event.respondWith(
      caches.match(event.request)
        .then(response => {
          // Try network first, then cache
          return fetch(event.request)
            .then(networkResponse => {
              // Cache the new response
              const responseToCache = networkResponse.clone();
              caches.open(CACHE_NAME).then(cache => {
                cache.put(event.request, responseToCache);
              });
              return networkResponse;
            })
            .catch(() => {
              // If network fails, use cache
              return response;
            });
        })
    );
  });
  


const THREE_YEARS_MS = 3 * 365 * 24 * 60 * 60 * 1000;

function cleanOldRecords() {
    const cutoffDate = new Date().getTime() - THREE_YEARS_MS;

    caches.open(CACHE_NAME).then(cache => {
        cache.keys().then(requests => {
            requests.forEach(request => {
                // Check timestamp in request URL or metadata
                if (request.timestamp < cutoffDate) {
                    cache.delete(request);
                }
            });
        });
    });

    // Clean IndexedDB records
    const dbRequest = indexedDB.open('pontoDB');
    dbRequest.onsuccess = (event) => {
        const db = event.target.result;
        const transaction = db.transaction(['entries'], 'readwrite');
        const store = transaction.objectStore('entries');

        const request = store.index('timestamp').openCursor(IDBKeyRange.upperBound(cutoffDate));
        request.onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                store.delete(cursor.primaryKey);
                cursor.continue();
            }
        };
    };
}

// Run cleanup periodically
setInterval(cleanOldRecords, 24 * 60 * 60 * 1000); // Once per day
