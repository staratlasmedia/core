import type { AuthFlowOptions, StarAtlasCoreConfig } from './types';

export function buildAuthUrl(config: StarAtlasCoreConfig, options: AuthFlowOptions): string {
  const url = new URL(`${config.apiBaseUrl}/auth/start`);

  url.searchParams.set('site_code', config.siteCode);
  url.searchParams.set('origin', config.origin);
  url.searchParams.set('mode', options.mode);
  url.searchParams.set('state', options.state);
  url.searchParams.set('nonce', options.nonce);
  url.searchParams.set('return_url', options.returnUrl);

  return url.toString();
}

export function openPopupLogin(config: StarAtlasCoreConfig): 'popup' | 'redirect' {
  const popup = window.open('about:blank', 'star_atlas_core_login', 'popup,width=520,height=720');
  const state = randomToken();
  const nonce = randomToken();
  const authUrl = buildAuthUrl(config, {
    mode: 'popup',
    state,
    nonce,
    returnUrl: `${config.origin}/core-auth/callback`,
  });

  if (!popup) {
    window.location.assign(
      buildAuthUrl(config, {
        mode: 'redirect',
        state,
        nonce,
        returnUrl: `${config.origin}/core-auth/callback`,
      }),
    );

    return 'redirect';
  }

  popup.location.href = authUrl;
  return 'popup';
}

export function createSilentCheckFrame(config: StarAtlasCoreConfig): HTMLIFrameElement {
  const frame = document.createElement('iframe');
  const state = randomToken();
  const nonce = randomToken();

  frame.hidden = true;
  frame.title = 'Core session check';
  frame.src = buildAuthUrl(config, {
    mode: 'silent',
    state,
    nonce,
    returnUrl: window.location.href,
  });

  return frame;
}

export function authMessageOrigin(config: StarAtlasCoreConfig): string {
  return new URL(config.apiBaseUrl).origin;
}

function randomToken(): string {
  const bytes = new Uint8Array(16);
  crypto.getRandomValues(bytes);

  return Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
}
