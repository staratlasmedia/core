export type CoreWidgetStatus =
  | 'idle'
  | 'ready'
  | 'working'
  | 'configured'
  | 'missing-config'
  | 'missing-vapid-public-key'
  | 'disabled'
  | 'login-required'
  | 'empty'
  | 'unsupported'
  | 'subscribed'
  | 'sent'
  | 'error';

export interface CommentsConfig {
  enabled: boolean;
  commentsEnabled?: boolean;
  requireLogin: boolean;
  allowGuest: boolean;
  requireModeration: boolean;
  maxDepth: number;
  maxLength: number;
  minLength: number;
  threadEndpoint?: string;
  commentsEndpoint?: string;
  postEndpoint?: string;
  reactionEndpoint?: string;
  reportEndpoint?: string;
  statusEndpoint?: string;
  loginRequiredMessage?: string;
  disabledMessage?: string;
  debugPlaceholder?: boolean;
}

export interface StarAtlasCoreConfig {
  siteCode: string;
  pushGroupCode?: string;
  bridgeInstallationId?: string;
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
  comments?: CommentsConfig;
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
