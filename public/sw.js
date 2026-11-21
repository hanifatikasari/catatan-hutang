/**
 * Service Worker for Catatan Hutang PWA
 * Caches app shell, manages offline fallbacks, and triggers background sync.
 */

const CACHE_NAME = 'catatan-hutang-v1';

const STATIC_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/css/layout.css',
    '/css/app.css',
    '/css/pages/dashboard.css',
    '/css/pages/pelanggan-index.css',
    '/css/pages/pelanggan-form.css',
    '/css/pages/pelanggan-hutang.css',
    '/css/pages/utang-index.css',
    '/css/pages/utang-form.css',
    '/css/pages/utang-detail.css',
    '/css/pages/laporan-index.css',
    '/css/pages/laporan-print.css',
    '/js/app.js',
    '/js/db.js',
    '/js/sync.js',
    '/icons/icon-96.png',
    '/icons/icon-144.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-192.png',
    '/icons/icon-maskable-512.png',
    '/icons/icon.svg',
    '/favicon.ico'
];

// Install Event: precache core app shell
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Attempt to cache all assets gracefully (don't fail completely if one asset has different path)
            return Promise.allSettled(
                STATIC_ASSETS.map(url => cache.add(url).catch(err => {
                    console.warn('[SW] Failed to cache asset on install:', url, err);
                }))
            );
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event: handle navigation and static requests
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests for caching
    if (request.method !== 'GET') {
        return;
    }

    // Ignore cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // 1. Navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(networkResponse => {
                    // Update cache with fresh version
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Network failed: try cache
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // If page is not in cache, return dedicated offline page
                    const offlinePage = await caches.match('/offline.html');
                    return offlinePage || new Response('Aplikasi sedang offline.', {
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // 2. Static assets (CSS, JS, images, fonts)
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font' ||
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/icons/')
    ) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                const fetchPromise = fetch(request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return networkResponse;
                }).catch(() => null);

                // Return cached version immediately if available, otherwise wait for network
                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // 3. Default Network-First with cache fallback
    event.respondWith(
        fetch(request)
            .then(response => {
                if (response && response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                }
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Background Sync Event (Sync API)
self.addEventListener('sync', event => {
    if (event.tag === 'sync-utang-offline') {
        event.waitUntil(
            self.clients.matchAll().then(clients => {
                clients.forEach(client => {
                    client.postMessage({ type: 'TRIGGER_OFFLINE_SYNC' });
                });
            })
        );
    }
});
