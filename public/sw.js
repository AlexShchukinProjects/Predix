// Service Worker — Aviatix PWA (inspections offline support)
const CACHE_NAME    = 'aviatix-v4';
const STATIC_PRECACHE = [
    '/manifest.webmanifest',
    '/icons/LogoA.png',
];

/* ── Install: pre-cache critical static assets ── */
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function (cache) { return cache.addAll(STATIC_PRECACHE).catch(function () {}); })
    );
    self.skipWaiting();
});

/* ── Activate: purge old caches ── */
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys
                    .filter(function (k) { return k.startsWith('aviatix-') && k !== CACHE_NAME; })
                    .map(function (k) { return caches.delete(k); })
            );
        })
    );
    self.clients.claim();
});

/* ── Fetch ── */
self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') return;

    var url        = new URL(event.request.url);
    var sameOrigin = url.origin === self.location.origin;

    // Skip cross-origin (CDN fonts, etc.)
    if (!sameOrigin) return;

    var path = url.pathname;

    // Always go straight to network for auth / API / vite HMR / hot-reload
    if (
        path.startsWith('/login') ||
        path.startsWith('/logout') ||
        path.startsWith('/register') ||
        path.startsWith('/password') ||
        path.startsWith('/sanctum') ||
        path.startsWith('/api/') ||
        path === '/hot'
    ) {
        event.respondWith(fetch(event.request));
        return;
    }

    var isNavigation  = event.request.mode === 'navigate';
    var isStaticAsset = ['style', 'script', 'image', 'font'].indexOf(event.request.destination) !== -1;

    if (isNavigation) {
        /*
         * Navigation (HTML pages): network-first.
         * On success cache the response so the page is available offline.
         * Only cache inspection-related pages to keep storage lean.
         */
        var cacheThisPage =
            path.startsWith('/modules/inspections/') ||
            path === '/modules/inspections/active';

        event.respondWith(
            fetch(event.request)
                .then(function (response) {
                    if (cacheThisPage && response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) {
                            cache.put(event.request, clone);
                        });
                    }
                    return response;
                })
                .catch(function () {
                    return caches.match(event.request).then(function (cached) {
                        if (cached) return cached;
                        return new Response(
                            '<html><body style="font-family:sans-serif;text-align:center;padding:60px"><h2>Нет подключения к сети</h2><p>Страница недоступна офлайн. Вернитесь при наличии интернета.</p></body></html>',
                            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                        );
                    });
                })
        );
        return;
    }

    if (isStaticAsset) {
        /*
         * Static assets (CSS, JS, images, fonts): cache-first.
         * Vite uses content-hashed filenames, so caching is safe.
         */
        event.respondWith(
            caches.match(event.request).then(function (cached) {
                if (cached) return cached;
                return fetch(event.request).then(function (response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(event.request, clone); });
                    }
                    return response;
                }).catch(function () { return cached || new Response('', { status: 503 }); });
            })
        );
        return;
    }

    // Everything else: network only, silent fallback to cache
    event.respondWith(
        fetch(event.request).catch(function () { return caches.match(event.request); })
    );
});

/* ── Push notifications (existing) ── */
self.addEventListener('push', function (event) {
    var options = {
        body:     event.data ? event.data.text() : 'Новое сообщение',
        icon:     '/icons/LogoA.png',
        badge:    '/icons/LogoA.png',
        vibrate:  [200, 100, 200],
        data:     { dateOfArrival: Date.now(), primaryKey: 1 }
    };
    event.waitUntil(self.registration.showNotification('Aviatix', options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(clients.openWindow('/chat'));
});

/* ── Badge update messages (existing) ── */
self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'UPDATE_BADGE') {
        var count = event.data.count || 0;
        if ('setAppBadge' in self.registration) {
            count > 0
                ? self.registration.setAppBadge(count).catch(function () {})
                : self.registration.clearAppBadge().catch(function () {});
        }
    }
});
