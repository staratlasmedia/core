import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import { requireCoreConfig } from '../config';
import { widgetStyles } from '../styles';
import type { CoreWidgetStatus } from '../types';

@customElement('core-comments-widget')
export class CoreCommentsWidget extends LitElement {
  static styles = widgetStyles;

  @state() private status: CoreWidgetStatus = 'idle';

  connectedCallback(): void {
    super.connectedCallback();
    this.status = requireCoreConfig(this) ? 'ready' : 'missing-config';
  }

  render() {
    const config = requireCoreConfig(this);

    if (!config) {
      return html`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">Configurazione commenti mancante.</p>
        </section>
      `;
    }

    const comments = config.comments;

    if (!comments?.enabled) {
      this.status = 'disabled';

      if (!comments?.debugPlaceholder) {
        return html``;
      }

      return html`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">${comments.disabledMessage ?? 'I commenti non sono disponibili per questa pagina.'}</p>
          <span class="meta">${config.siteCode} · disabled</span>
        </section>
      `;
    }

    if (comments.requireLogin) {
      this.status = 'login-required';

      return html`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">${comments.loginRequiredMessage ?? 'Accedi per commentare.'}</p>
          <core-login-widget></core-login-widget>
        </section>
      `;
    }

    return html`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Commenti</h2>
        <p class="copy">I commenti Core saranno caricati per questa pagina.</p>
        <span class="meta">${config.siteCode} · ${config.sourceUrl}</span>
      </section>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'core-comments-widget': CoreCommentsWidget;
  }
}
