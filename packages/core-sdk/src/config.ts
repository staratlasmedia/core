import type { PushSubscriptionContext, StarAtlasCoreConfig } from './types';

const DEFAULT_API_BASE_URL = 'https://core.staratlasmedia.com/api/v1';

const ATTRIBUTE_MAP = {
  siteCode: 'site-code',
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
  const globalConfig = window.StarAtlasCore ?? {};
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
