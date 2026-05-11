import { pushContextFor, serviceWorkerScopeFor, serviceWorkerUrlFor } from './config';
import type { PushSubscriptionPayload, StarAtlasCoreConfig } from './types';

export async function registerCoreServiceWorker(config: StarAtlasCoreConfig): Promise<ServiceWorkerRegistration> {
  if (!('serviceWorker' in navigator)) {
    throw new Error('service-worker-unsupported');
  }

  return navigator.serviceWorker.register(serviceWorkerUrlFor(config), {
    scope: serviceWorkerScopeFor(config),
    updateViaCache: 'none',
  });
}

export async function subscribeToCorePush(config: StarAtlasCoreConfig): Promise<PushSubscriptionPayload> {
  if (!('PushManager' in window)) {
    throw new Error('push-unsupported');
  }

  if (!config.vapidPublicKey) {
    throw new Error('missing-vapid-public-key');
  }

  const registration = await registerCoreServiceWorker(config);
  const existing = await registration.pushManager.getSubscription();
  const subscription =
    existing ??
    (await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(config.vapidPublicKey),
    }));

  const payload = pushPayloadFor(config, subscription, Boolean(existing));

  await sendPushSubscription(config, payload);

  return payload;
}

export async function sendPushSubscription(
  config: StarAtlasCoreConfig,
  payload: PushSubscriptionPayload,
): Promise<void> {
  const response = await fetch(`${config.apiBaseUrl}/push/subscriptions`, {
    body: JSON.stringify(payload),
    credentials: 'omit',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    method: 'POST',
  });

  if (!response.ok) {
    throw new Error(`push-subscription-failed:${response.status}`);
  }
}

export function pushPayloadFor(
  config: StarAtlasCoreConfig,
  subscription: PushSubscription,
  legacyReconfirmation: boolean,
): PushSubscriptionPayload {
  return {
    site_code: config.siteCode,
    origin: config.origin,
    service_worker_url: serviceWorkerUrlFor(config),
    service_worker_scope: serviceWorkerScopeFor(config),
    subscription: subscription.toJSON(),
    context: pushContextFor(config),
    legacy_reconfirmation: legacyReconfirmation,
  };
}

function urlBase64ToUint8Array(base64String: string): ArrayBuffer {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = `${base64String}${padding}`.replace(/-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; i += 1) {
    outputArray[i] = rawData.charCodeAt(i);
  }

  return outputArray.buffer.slice(
    outputArray.byteOffset,
    outputArray.byteOffset + outputArray.byteLength,
  ) as ArrayBuffer;
}
