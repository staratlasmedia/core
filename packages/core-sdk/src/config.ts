import type { PushSubscriptionContext, StarAtlasCoreConfig } from './types';

const DEFAULT_API_BASE_URL = 'https://core.staratlasmedia.com/api/v1';

const ATTRIBUTE_MAP = {
  siteCode: 'site-code',
  pushGroupCode: 'push-group-code',
  bridgeInstallationId: 'bridge-installation-id',
  origin: 'origin',
  language: 'language',
  section: 'section',
  sourceUrl: 'source-url',
  sourceTitle: 'source-title',
  apiBaseUrl: 'api-base-url',
  serviceWorkerUrl: 'service-worker-url',
  serviceWorkerScope: 'service-worker-scope',
  vapidPublicKey: 'vapid-public-key',
} as const;

export function getCoreConfig(element?: Element): Partial<StarAtlasCoreConfig> {
  const globalConfig = normalizeConfig((window.StarAtlasCore ?? {}) as Record<string, unknown>);
  const attributeConfig = element ? configFromAttributes(element) : {};

  return {
    apiBaseUrl: DEFAULT_API_BASE_URL,
    origin: window.location.origin,
    sourceUrl: window.location.href,
    language: document.documentElement.lang || 'it',
    ...globalConfig,
    ...attributeConfig,
  };
}

export function requireCoreConfig(element?: Element): StarAtlasCoreConfig | null {
  const config = getCoreConfig(element);

  if (!config.siteCode || !config.origin || !config.language || !config.sourceUrl || !config.apiBaseUrl) {
    return null;
  }

  return {
    siteCode: config.siteCode,
    pushGroupCode: config.pushGroupCode,
    bridgeInstallationId: config.bridgeInstallationId,
    origin: config.origin,
    language: config.language,
    section: config.section,
    sourceUrl: config.sourceUrl,
    sourceTitle: config.sourceTitle,
    apiBaseUrl: trimTrailingSlash(config.apiBaseUrl),
    serviceWorkerUrl: config.serviceWorkerUrl,
    serviceWorkerScope: config.serviceWorkerScope,
    vapidPublicKey: config.vapidPublicKey,
    wpTerms: config.wpTerms,
    comments: normalizeCommentsConfig(config.comments),
  };
}

export function serviceWorkerUrlFor(config: StarAtlasCoreConfig): string {
  if (config.serviceWorkerUrl) {
    return config.serviceWorkerUrl;
  }

  return config.section === 'en' || config.language === 'en' ? '/en/smart_sw.js' : '/smart_sw.js';
}

export function serviceWorkerScopeFor(config: StarAtlasCoreConfig): string {
  if (config.serviceWorkerScope) {
    return config.serviceWorkerScope;
  }

  return config.section === 'en' || config.language === 'en' ? '/en/' : '/';
}

export function pushContextFor(config: StarAtlasCoreConfig): PushSubscriptionContext {
  return {
    source_url: config.sourceUrl,
    source_title: config.sourceTitle,
    language: config.language,
    section: config.section,
    wp_terms_json: config.wpTerms,
    referrer: document.referrer || undefined,
    utm_json: utmParamsFromLocation(),
  };
}

function configFromAttributes(element: Element): Partial<StarAtlasCoreConfig> {
  const config: Partial<StarAtlasCoreConfig> = {};
  const writableConfig = config as Record<string, string>;

  for (const [key, attribute] of Object.entries(ATTRIBUTE_MAP)) {
    const value = element.getAttribute(attribute);

    if (value) {
      writableConfig[key] = value;
    }
  }

  return config;
}

function normalizeConfig(config: Record<string, unknown>): Partial<StarAtlasCoreConfig> {
  return {
    siteCode: stringValue(config.siteCode ?? config.site_code),
    pushGroupCode: stringValue(config.pushGroupCode ?? config.push_group_code),
    bridgeInstallationId: stringValue(config.bridgeInstallationId ?? config.bridge_installation_id),
    origin: stringValue(config.origin),
    language: stringValue(config.language),
    section: stringValue(config.section),
    sourceUrl: stringValue(config.sourceUrl ?? config.source_url),
    sourceTitle: stringValue(config.sourceTitle ?? config.source_title),
    apiBaseUrl: stringValue(config.apiBaseUrl ?? config.core_api_base),
    serviceWorkerUrl: stringValue(config.serviceWorkerUrl ?? config.registration_service_worker_url ?? config.service_worker_url),
    serviceWorkerScope: stringValue(config.serviceWorkerScope ?? config.registration_service_worker_scope ?? config.service_worker_scope),
    vapidPublicKey: stringValue(config.vapidPublicKey ?? config.vapid_public_key),
    wpTerms: Array.isArray(config.wpTerms) ? config.wpTerms : Array.isArray(config.wp_terms_json) ? config.wp_terms_json : undefined,
    comments: typeof config.comments === 'object' && config.comments !== null
      ? normalizeCommentsConfig(config.comments as Record<string, unknown>)
      : undefined,
  };
}

function normalizeCommentsConfig(config?: Partial<StarAtlasCoreConfig['comments']> | Record<string, unknown>): StarAtlasCoreConfig['comments'] | undefined {
  if (!config) {
    return undefined;
  }

  const source = config as Record<string, unknown>;
  const enabled = Boolean(source.enabled ?? source.comments_enabled ?? source.commentsEnabled ?? false);

  return {
    enabled,
    commentsEnabled: enabled,
    requireLogin: Boolean(source.requireLogin ?? source.require_login ?? true),
    allowGuest: Boolean(source.allowGuest ?? source.allow_guest ?? false),
    requireModeration: Boolean(source.requireModeration ?? source.require_moderation ?? true),
    maxDepth: numberValue(source.maxDepth ?? source.max_depth, 3),
    maxLength: numberValue(source.maxLength ?? source.max_length, 2000),
    minLength: numberValue(source.minLength ?? source.min_length, 2),
    threadEndpoint: stringValue(source.threadEndpoint ?? source.thread_endpoint),
    commentsEndpoint: stringValue(source.commentsEndpoint ?? source.comments_endpoint),
    postEndpoint: stringValue(source.postEndpoint ?? source.post_endpoint),
    reactionEndpoint: stringValue(source.reactionEndpoint ?? source.reaction_endpoint),
    reportEndpoint: stringValue(source.reportEndpoint ?? source.report_endpoint),
    statusEndpoint: stringValue(source.statusEndpoint ?? source.status_endpoint),
    loginRequiredMessage: stringValue(source.loginRequiredMessage ?? source.login_required_message),
    disabledMessage: stringValue(source.disabledMessage ?? source.disabled_message),
    debugPlaceholder: Boolean(source.debugPlaceholder ?? source.debug_placeholder ?? false),
  };
}

function stringValue(value: unknown): string | undefined {
  return typeof value === 'string' && value !== '' ? value : undefined;
}

function numberValue(value: unknown, fallback: number): number {
  const numeric = typeof value === 'number' ? value : Number(value);

  return Number.isFinite(numeric) ? numeric : fallback;
}

function trimTrailingSlash(value: string): string {
  return value.replace(/\/+$/, '');
}

function utmParamsFromLocation(): Record<string, string> | undefined {
  const utm: Record<string, string> = {};
  const params = new URLSearchParams(window.location.search);

  for (const [key, value] of params.entries()) {
    if (key.startsWith('utm_') && value) {
      utm[key] = value;
    }
  }

  return Object.keys(utm).length > 0 ? utm : undefined;
}
