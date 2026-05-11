export type CoreWidgetStatus =
  | 'idle'
  | 'ready'
  | 'working'
  | 'configured'
  | 'missing-config'
  | 'missing-vapid-public-key'
  | 'unsupported'
  | 'subscribed'
  | 'sent'
  | 'error';

export interface StarAtlasCoreConfig {
  siteCode: string;
  origin: string;
  language: string;
  section?: string;
  sourceUrl: string;
  sourceTitle?: string;
  apiBaseUrl: string;
  serviceWorkerUrl?: string;
  serviceWorkerScope?: string;
  vapidPublicKey?: string;
  wpTerms?: unknown[];
}

export interface PushSubscriptionContext {
  source_url: string;
  source_title?: string;
  language: string;
  section?: string;
  wp_terms_json?: unknown[];
  referrer?: string;
  utm_json?: Record<string, string>;
}

export interface PushSubscriptionPayload {
  site_code: string;
  origin: string;
  service_worker_url: string;
  service_worker_scope: string;
  subscription: PushSubscriptionJSON;
  context: PushSubscriptionContext;
  legacy_reconfirmation: boolean;
}

export interface AuthFlowOptions {
  mode: 'popup' | 'redirect' | 'silent';
  state: string;
  nonce: string;
  returnUrl: string;
}

declare global {
  interface Window {
    StarAtlasCore?: Partial<StarAtlasCoreConfig>;
  }
}
