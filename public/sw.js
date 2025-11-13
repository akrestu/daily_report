/**
 * SiGAP Service Worker v3.0
 * Provides offline support and caching strategies for PWA
 * Updated: 2025-11-13 - Fixed precaching errors
 */

// Cache version for manual updates
const CACHE_VERSION = 'v3.0.0';

// Import Workbox from CDN
importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js');

// Check if Workbox loaded successfully
if (workbox) {
  console.log('Workbox loaded successfully - Version:', CACHE_VERSION);

  // Enable skip waiting and client claim
  workbox.core.skipWaiting();
  workbox.core.clientsClaim();

  // Clean up old precaches
  workbox.precaching.cleanupOutdatedCaches();

  // PRECACHING STRATEGY
  // Precache important static assets
  // Only precache offline.html - icons will be cached on-demand via StaleWhileRevalidate
  workbox.precaching.precacheAndRoute([
    { url: '/offline.html', revision: '1.2' },
  ]);

  // CACHING STRATEGIES

  // 1. Cache-First for static assets (CSS, JS, Fonts)
  workbox.routing.registerRoute(
    ({ request }) =>
      request.destination === 'style' ||
      request.destination === 'script' ||
      request.destination === 'font',
    new workbox.strategies.CacheFirst({
      cacheName: 'static-assets',
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 100,
          maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
          purgeOnQuotaError: true,
        }),
      ],
    })
  );

  // 2. Cache-First for images with expiration
  workbox.routing.registerRoute(
    ({ request }) => request.destination === 'image',
    new workbox.strategies.CacheFirst({
      cacheName: 'images',
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 60,
          maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
        }),
      ],
    })
  );

  // 3. Stale-While-Revalidate for PWA icons
  workbox.routing.registerRoute(
    ({ url }) => url.pathname.startsWith('/icons/'),
    new workbox.strategies.StaleWhileRevalidate({
      cacheName: 'pwa-icons',
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 20,
          maxAgeSeconds: 365 * 24 * 60 * 60, // 1 year
        }),
      ],
    })
  );

  // 4. Network-First for API requests with timeout
  workbox.routing.registerRoute(
    ({ url }) =>
      url.pathname.startsWith('/api/') ||
      url.pathname.includes('/daily-reports') ||
      url.pathname.includes('/dashboard') ||
      url.pathname.includes('/notifications'),
    new workbox.strategies.NetworkFirst({
      cacheName: 'api-cache',
      networkTimeoutSeconds: 5,
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 50,
          maxAgeSeconds: 5 * 60, // 5 minutes
        }),
      ],
    })
  );

  // 5. Cache Google Fonts stylesheets
  workbox.routing.registerRoute(
    ({ url }) => url.origin === 'https://fonts.googleapis.com',
    new workbox.strategies.StaleWhileRevalidate({
      cacheName: 'google-fonts-stylesheets',
    })
  );

  // 6. Cache Google Fonts webfonts
  workbox.routing.registerRoute(
    ({ url }) => url.origin === 'https://fonts.gstatic.com',
    new workbox.strategies.CacheFirst({
      cacheName: 'google-fonts-webfonts',
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 30,
          maxAgeSeconds: 365 * 24 * 60 * 60, // 1 year
        }),
      ],
    })
  );

  // 7. Cache CDN resources (Bootstrap, FontAwesome, etc.)
  workbox.routing.registerRoute(
    ({ url }) =>
      url.origin === 'https://cdn.jsdelivr.net' ||
      url.origin === 'https://cdnjs.cloudflare.com',
    new workbox.strategies.CacheFirst({
      cacheName: 'cdn-resources',
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 50,
          maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
        }),
      ],
    })
  );

  // 8. Network-First for HTML pages
  workbox.routing.registerRoute(
    ({ request }) => request.destination === 'document',
    new workbox.strategies.NetworkFirst({
      cacheName: 'pages',
      networkTimeoutSeconds: 5,
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 50,
          maxAgeSeconds: 24 * 60 * 60, // 24 hours
        }),
      ],
    })
  );

  // OFFLINE FALLBACK
  // Set catch handler for offline scenarios
  workbox.routing.setCatchHandler(({ event }) => {
    // Return the offline page for navigation requests
    if (event.request.destination === 'document') {
      return caches.match('/offline.html');
    }

    // Return a placeholder for images
    if (event.request.destination === 'image') {
      return caches.match('/icons/icon-192x192.png');
    }

    // Return a generic error for other requests
    return Response.error();
  });

  // Listen for messages from clients
  self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
      self.skipWaiting();
    }
    
    // Handle cache clearing request
    if (event.data && event.data.type === 'CLEAR_OLD_CACHES') {
      caches.keys().then(cacheNames => {
        cacheNames.forEach(cacheName => {
          if (cacheName.includes('v1') || cacheName.includes('1.1')) {
            console.log('PWA: Clearing old cache:', cacheName);
            caches.delete(cacheName);
          }
        });
      });
    }
  });

  console.log('SiGAP Service Worker: All routes registered');
} else {
  console.error('Workbox failed to load');
}
