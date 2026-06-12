const ERP_CACHE = 'erp-mobile-General Trading ERP Store-v1';
const OFFLINE_URL = '/offline.php';
const CORE_ASSETS = [
  '/',
  '/mobile/index.php',
  '/mobile/customer.php',
  '/mobile/employee.php',
  '/mobile/technician.php',
  '/offline.php'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(ERP_CACHE)
      .then(cache => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
      .catch(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== ERP_CACHE).map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;
  event.respondWith(
    fetch(request)
      .then(response => {
        const copy = response.clone();
        caches.open(ERP_CACHE).then(cache => cache.put(request, copy)).catch(() => {});
        return response;
      })
      .catch(() => caches.match(request).then(cached => cached || caches.match(OFFLINE_URL)))
  );
});

self.addEventListener('push', event => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) { data = { title: 'ERP Notification', body: event.data ? event.data.text() : '' }; }
  const title = data.title || 'ERP Notification';
  const options = {
    body: data.body || 'New ERP update available.',
    icon: '/assets/img/pwa-icon-192.png',
    badge: '/assets/img/pwa-icon-192.png',
    data: { url: data.url || '/mobile/index.php' }
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/mobile/index.php';
  event.waitUntil(clients.openWindow(url));
});