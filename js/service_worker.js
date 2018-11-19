// Version: 1.0
var cacheName = 'syn-pwa-cache-v1';
var filesToCache = [
  '/',
  'manifest.json',
  'modules/custom/pwa/js/main.js',
  ];


// Listen for install event, set callback
self.addEventListener('install', function(event) {
  console.log('Service worker installing...');
  event.waitUntil(
    caches.open(cacheName).then(function(cache) {
      return cache.addAll(filesToCache);
    }).then(function() {
      return self.skipWaiting();
    })
  );
});

// Fired when the Service Worker starts up
self.addEventListener('activate', function(event) {
  console.log('Service Worker: Activating....');

  event.waitUntil(
    caches.keys().then(keyList => {
      return Promise.all(keyList.map(key => {
        if (key !== cacheName) {
          return caches.delete(key);
        }
      }));
    }));
    return self.clients.claim();
});

self.addEventListener('fetch', function(event) {
  console.log('Service Worker: Fetch', event.request.url);

  event.respondWith(
    caches.open(cacheName).then(function(cache) {
      return cache.match(event.request).then(function (response) {
        return response || fetch(event.request).then(function(response) {
          cache.put(event.request, response);
          return response;
        });
      }).catch(function() {
        // If both fail, show a generic fallback:
        return caches.match('/offline');
      });
    })
  );

});

/**
 * Chat messages, emails, document updates, settings changes, photo uploads… anything that you want to reach the server even if user navigates away or closes the tab.
 */
self.addEventListener('sync', function(event) {
  if (event.tag == 'synFirstSync') {
    event.waitUntil(
      caches.open(cacheName).then(function(cache) {
        return cache.addAll(filesToCache);
      }).then(function() {
        return self.skipWaiting();
      })
    );
  }
});

self.addEventListener('push', function(event) {
  console.log('[Service Worker] Push Received.');
  
  var body;

  if (event.data) {
    body = event.data.text();
  } else {
    body = 'Push message no payload';
  }

  console.log(`[Service Worker] Push had this data: "${body}"`);

  var options = {
    body: body,
    icon: 'modules/custom/pwa/images/icon_144.png',
    badge: 'modules/custom/pwa/images/icon_144.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: '1'
    },
    actions: [
      {action: 'explore', title: 'Explore this new world',
        icon: 'modules/custom/pwa/images/tick.png'},
      {action: 'close', title: 'Close',
        icon: 'modules/custom/pwa/images/xmark.png'},
    ]
  };
  event.waitUntil(
    self.registration.showNotification('Hello world!', options)
  );
});

self.addEventListener('notificationclick', function(event) {
  console.log('[Service Worker] Notification click Received.');

  var notification = event.notification;
  var action = event.action;

  if (action === 'close') {
    notification.close();
/*
  } else {
    event.waitUntil(
      clients.openWindow('https://mysyngentapwaacpqglwv4x.devcloud.acquia-sites.com/')
    );
*/
  }
});

self.addEventListener('notificationclose', function(event) {
  var notification = event.notification;
  var primaryKey = notification.data.primaryKey;
  console.log('Closed notification: ' + primaryKey);
});

