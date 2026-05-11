import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import { requireCoreConfig, serviceWorkerScopeFor, serviceWorkerUrlFor } from '../config';
import { subscribeToCorePush } from '../push';
import { widgetStyles } from '../styles';
import type { CoreWidgetStatus } from '../types';

@customElement('core-push-widget')
export class CorePushWidget extends LitElement {
  static styles = widgetStyles;

  @state() private status: CoreWidgetStatus = 'idle';
  @state() private message = '';

  connectedCallback(): void {
    super.connectedCallback();

    const config = requireCoreConfig(this);
    this.status = config ? 'ready' : 'missing-config';

    if (config && (!('serviceWorker' in navigator) || !('PushManager' in window))) {
      this.status = 'unsupported';
    }
  }

  render() {
    const config = requireCoreConfig(this);
    const disabled = this.status === 'missing-config' || this.status === 'unsupported' || this.status === 'working';

    return html`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Notifiche</h2>
        <p class="copy">Ricevi gli aggiornamenti da ${config?.siteCode ?? 'Core'}.</p>
        <button ?disabled=${disabled} @click=${this.subscribe}>Attiva notifiche</button>
        <span class="status">${this.statusText()}</span>
      </section>
    `;
  }

  private async subscribe(): Promise<void> {
    const config = requireCoreConfig(this);

    if (!config) {
      this.status = 'missing-config';
      this.message = 'Configurazione Core mancante';
      return;
    }

    if (!config.vapidPublicKey) {
      this.status = 'missing-vapid-public-key';
      this.message = 'Chiave pubblica VAPID mancante';
      return;
    }

    this.status = 'working';
    this.message = `Service Worker ${serviceWorkerUrlFor(config)} · scope ${serviceWorkerScopeFor(config)}`;

    try {
      const payload = await subscribeToCorePush(config);
      this.status = payload.legacy_reconfirmation ? 'subscribed' : 'sent';
      this.message = payload.legacy_reconfirmation ? 'Iscrizione riconfermata' : 'Iscrizione inviata';
      this.dispatchEvent(
        new CustomEvent('core-push-subscribed', {
          bubbles: true,
          composed: true,
          detail: payload,
        }),
      );
    } catch (error) {
      this.status = 'error';
      this.message = error instanceof Error ? error.message : 'Errore push';
    }
  }

  private statusText(): string {
    if (this.message) {
      return this.message;
    }

    if (this.status === 'unsupported') {
      return 'Push non supportato';
    }

    if (this.status === 'missing-config') {
      return 'Configurazione Core mancante';
    }

    return 'Pronto';
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'core-push-widget': CorePushWidget;
  }
}
