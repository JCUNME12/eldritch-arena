const CACHE_NAME = 'eldritch-arena-static-v2';
const STATIC_URLS = ['/manifest.json'];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_URLS))
      .then(() => self.skipWaiting()),
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => Promise.all(
        cacheNames
          .filter(cacheName => cacheName.startsWith('eldritch-arena-') && cacheName !== CACHE_NAME)
          .map(cacheName => caches.delete(cacheName)),
      ))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);

  if (
    request.method !== 'GET'
    || request.mode === 'navigate'
    || url.origin !== self.location.origin
    || !STATIC_URLS.includes(url.pathname)
  ) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then(response => {
        if (response.ok) {
          const cachedResponse = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, cachedResponse));
        }

        return response;
      })
      .catch(() => caches.match(request)),
  );
});
