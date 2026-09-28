// Never cache authenticated HTML or CSRF tokens. The open table keeps its state locally.
self.addEventListener('install',()=>self.skipWaiting());
self.addEventListener('activate',event=>event.waitUntil(
 caches.keys().then(keys=>Promise.all(keys.filter(k=>k.startsWith('eldritch-arena')).map(k=>caches.delete(k)))).then(()=>self.clients.claim())
));
