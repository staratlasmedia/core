<?php

namespace App\Services\Push;

use App\Models\PushGroup;

class ServiceWorkerGenerator
{
    public function generate(PushGroup $pushGroup): string
    {
        $version = json_encode($pushGroup->sw_version ?: 'core-clean-v1', JSON_THROW_ON_ERROR);
        $groupCode = json_encode($pushGroup->code, JSON_THROW_ON_ERROR);

        return <<<JS
        'use strict';

        const CORE_SW_VERSION = {$version};
        const CORE_PUSH_GROUP = {$groupCode};

        self.addEventListener('install', (event) => {
            event.waitUntil(self.skipWaiting());
        });

        self.addEventListener('activate', (event) => {
            event.waitUntil(self.clients.claim());
        });

        self.addEventListener('push', (event) => {
            const payload = event.data ? event.data.json() : {};
            const notification = payload.notification || {};
            const title = notification.title || 'Star Atlas Media';
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

            event.waitUntil(self.registration.showNotification(title, options));
        });

        self.addEventListener('notificationclick', (event) => {
            event.notification.close();

            const url = event.notification.data && event.notification.data.url
                ? event.notification.data.url
                : '/';

            event.waitUntil(self.clients.openWindow(url));
        });
        JS;
    }
}
