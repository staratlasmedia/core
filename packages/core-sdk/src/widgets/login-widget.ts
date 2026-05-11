import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import { authMessageOrigin, createSilentCheckFrame, openPopupLogin } from '../auth';
import { requireCoreConfig } from '../config';
import { widgetStyles } from '../styles';
import type { CoreWidgetStatus } from '../types';

@customElement('core-login-widget')
export class CoreLoginWidget extends LitElement {
  static styles = widgetStyles;

  @state() private status: CoreWidgetStatus = 'idle';
  @state() private message = '';

  connectedCallback(): void {
    super.connectedCallback();

    const config = requireCoreConfig(this);
    this.status = config ? 'ready' : 'missing-config';

    if (config) {
      window.addEventListener('message', this.onAuthMessage);
    }
  }

  disconnectedCallback(): void {
    window.removeEventListener('message', this.onAuthMessage);
    super.disconnectedCallback();
  }

  render() {
    const disabled = this.status === 'missing-config';

    return html`
      <div class="core-widget compact">
        <button class="secondary" ?disabled=${disabled} @click=${this.startLogin}>Accedi</button>
      </div>
      ${this.message ? html`<span class="status">${this.message}</span>` : ''}
    `;
  }

  private startLogin(): void {
    const config = requireCoreConfig(this);

    if (!config) {
      this.status = 'missing-config';
      this.message = 'Configurazione Core mancante';
      return;
    }

    this.status = openPopupLogin(config) === 'popup' ? 'working' : 'ready';
    this.message = this.status === 'working' ? 'Login Core in corso' : '';
  }

  private onAuthMessage = (event: MessageEvent): void => {
    const config = requireCoreConfig(this);

    if (!config || event.origin !== authMessageOrigin(config)) {
      return;
    }

    if (event.data?.type === 'star-atlas-core:auth-complete') {
      this.status = 'ready';
      this.message = 'Sessione aggiornata';
      this.dispatchEvent(
        new CustomEvent('core-auth-complete', {
          bubbles: true,
          composed: true,
          detail: event.data,
        }),
      );
    }
  };

  createSilentCheckFrame(): HTMLIFrameElement | null {
    const config = requireCoreConfig(this);

    return config ? createSilentCheckFrame(config) : null;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'core-login-widget': CoreLoginWidget;
  }
}
