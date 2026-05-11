<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\ServiceWorker;

use StarAtlas\CoreBridge\Utils\Options;

final class ServiceWorkerController
{
    public function __construct(private readonly Options $options) {}

    public function serve(): void
    {
        nocache_headers();
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Service-Worker-Allowed: '.esc_url_raw((string) $this->options->configValue('registration_service_worker_scope', '/')));

        echo $this->worker();
        exit;
    }

    private function worker(): string
    {
        return <<<'JS'
'use strict';

self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
  if (!event.data) {
    return;
  }

  event.waitUntil((async function () {
    let payload = {};

    try {
      payload = event.data.json();
    } catch (error) {
      payload = {};
    }

    const notification = payload.notification || {};
    const title = notification.title || '';
    const options = {
      body: notification.body || '',
      icon: notification.icon || undefined,
      badge: notification.badge || undefined,
      image: notification.image || undefined,
      tag: notification.tag || undefined,
      data: {
        url: notification.url || '/'
      }
    };

    await self.registration.showNotification(title, options);
  })());
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  const url = event.notification && event.notification.data ? event.notification.data.url : '/';
  event.waitUntil(self.clients.openWindow(url));
});
JS;
    }
}
