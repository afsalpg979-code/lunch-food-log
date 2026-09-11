const CACHE = 'lunch-food-log-pwa-v2';
const APP_SHELL = [
  './',
  './index.php',
  './manifest.json',
  './assets/app.css',
  './assets/meal-alert.js',
  './assets/icon.svg'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE)
      .then(cache => cache.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE && key.startsWith('lunch-food-log-pwa-'))
        .map(key => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // Network-first for pages so food data stays fresh; cached shell is the offline fallback.
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).then(response => {
        if (response.ok) {
          const copy = response.clone();
          caches.open(CACHE).then(cache => cache.put(request, copy));
        }
        return response;
      }).catch(() => caches.match(request).then(cached => cached || caches.match('./index.php')))
    );
    return;
  }

  // Cache-first for static assets for faster repeat loads.
  event.respondWith(
    caches.match(request).then(cached => cached || fetch(request).then(response => {
      if (response.ok) {
        const copy = response.clone();
        caches.open(CACHE).then(cache => cache.put(request, copy));
      }
      return response;
    }))
  );
});

self.addEventListener('message', event => {
  if (event.data?.type === 'SHOW_MEAL_NOTIFICATION') {
    const meal = event.data.meal || 'Meal';
    event.waitUntil(self.registration.showNotification(`🍽️ ${meal} Time`, {
      body: `It's time for ${meal}. Don't forget to record your food.`,
      icon: './assets/icon.svg',
      badge: './assets/icon.svg',
      tag: `meal-${meal}`,
      renotify: true,
      vibrate: [300, 150, 300, 150, 500],
      requireInteraction: true,
      data: { url: './index.php' },
      actions: [
        { action: 'open', title: 'Open Food Log' },
        { action: 'dismiss', title: 'Dismiss' }
      ]
    }));
  }
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.action === 'dismiss') return;
  event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
    for (const client of list) {
      if ('focus' in client) return client.focus();
    }
    return clients.openWindow(event.notification.data?.url || './index.php');
  }));
});
