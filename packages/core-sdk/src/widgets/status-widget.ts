import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import { requireCoreConfig } from '../config';
import { widgetStyles } from '../styles';
import type { CoreWidgetStatus } from '../types';

@customElement('star-atlas-core-status')
export class StarAtlasCoreStatus extends LitElement {
  static styles = widgetStyles;

  @state() private status: CoreWidgetStatus = 'idle';

  connectedCallback(): void {
    super.connectedCallback();
    this.status = requireCoreConfig(this) ? 'configured' : 'missing-config';
  }

  render() {
    return html`<span class="status" data-core-status=${this.status}>Core SDK ${this.status}</span>`;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'star-atlas-core-status': StarAtlasCoreStatus;
  }
}
