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

    return html`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Commenti</h2>
        <p class="copy">I commenti Core saranno caricati per questa pagina.</p>
        <span class="meta">${config?.siteCode ?? 'Core'} · ${config?.sourceUrl ?? 'config'}</span>
      </section>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'core-comments-widget': CoreCommentsWidget;
  }
}
