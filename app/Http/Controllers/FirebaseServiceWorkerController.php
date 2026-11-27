<?php

namespace App\Http\Controllers;

use App\Services\FirebaseCloudMessagingService;
use Illuminate\Http\Response;

class FirebaseServiceWorkerController extends Controller
{
    public function legacy(): Response
    {
        return $this->legacyCleanupResponse('/');
    }

    public function admin(FirebaseCloudMessagingService $firebase): Response
    {
        $allowedScope = '/admin/';

        if (!$firebase->webPushEnabled()) {
            return $this->legacyCleanupResponse($allowedScope);
        }

        $config = $firebase->webConfig();

        $script = <<<'JS'
self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

const ADMIN_DEFAULT_PATH = '/admin';

const toAdminUrl = function (value) {
  let url;

  try {
    url = new URL(value || ADMIN_DEFAULT_PATH, self.location.origin);
  } catch (error) {
    url = new URL(ADMIN_DEFAULT_PATH, self.location.origin);
  }

  if (url.origin !== self.location.origin || !url.pathname.startsWith('/admin')) {
    return new URL(ADMIN_DEFAULT_PATH, self.location.origin).href;
  }

  return url.href;
};

self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  const targetUrl = toAdminUrl(event.notification?.data?.link || ADMIN_DEFAULT_PATH);

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      const target = new URL(targetUrl);

      for (const client of clientList) {
        try {
          const current = new URL(client.url);

          if (current.origin === target.origin && current.pathname.startsWith('/admin')) {
            if ('focus' in client) {
              client.focus();
            }

            if ('navigate' in client && client.url !== targetUrl) {
              return client.navigate(targetUrl);
            }

            return client;
          }
        } catch (error) {
          // Abaikan URL client yang tidak valid.
        }
      }

      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});

importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: %s,
  authDomain: %s,
  projectId: %s,
  storageBucket: %s,
  messagingSenderId: %s,
  appId: %s,
  measurementId: %s
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
  const data = payload?.data || {};
  const title = data.title || 'Admin Purplestuff';
  const link = toAdminUrl(data.link || ADMIN_DEFAULT_PATH);
  const options = {
    body: data.message || 'Ada notifikasi baru.',
    icon: data.icon || '/favicon.ico',
    badge: data.badge || '/favicon.ico',
    tag: data.tag || data.notification_id || 'admin-notification',
    renotify: true,
    data: {
      link: link
    }
  };

  self.registration.showNotification(title, options);
});
JS;

        $script = sprintf(
            $script,
            json_encode($config['apiKey'], JSON_UNESCAPED_SLASHES),
            json_encode($config['authDomain'], JSON_UNESCAPED_SLASHES),
            json_encode($config['projectId'], JSON_UNESCAPED_SLASHES),
            json_encode($config['storageBucket'], JSON_UNESCAPED_SLASHES),
            json_encode($config['messagingSenderId'], JSON_UNESCAPED_SLASHES),
            json_encode($config['appId'], JSON_UNESCAPED_SLASHES),
            json_encode($config['measurementId'], JSON_UNESCAPED_SLASHES),
        );

        return $this->javascriptResponse($script, $allowedScope);
    }

    private function legacyCleanupResponse(string $allowedScope): Response
    {
        $script = <<<'JS'
self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.registration.unregister());
});
JS;

        return $this->javascriptResponse($script, $allowedScope);
    }

    private function javascriptResponse(string $script, string $allowedScope): Response
    {
        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => $allowedScope,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
