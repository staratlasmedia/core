import { LitElement, css, html } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';

declare global {
  interface Window {
    StarAtlasCore?: StarAtlasCoreConfig;
  }
}

export interface StarAtlasCoreConfig {
  siteCode: string;
  origin: string;
  language: string;
  section?: string;
  sourceUrl: string;
  sourceTitle?: string;
}

@customElement('star-atlas-core-status')
export class StarAtlasCoreStatus extends LitElement {
  @property({ type: String }) apiBase = 'https://core.staratlasmedia.com/api/v1';

  @state() private status = 'idle';

  static styles = css`
    :host {
      display: inline-block;
      font-family: ui-sans-serif, system-ui, sans-serif;
    }

    span {
      color: #1f2937;
      font-size: 14px;
    }
  `;

  connectedCallback(): void {
    super.connectedCallback();
    this.status = window.StarAtlasCore?.siteCode ? 'configured' : 'missing-config';
  }

  render() {
    return html`<span data-core-status=${this.status}>Core SDK ${this.status}</span>`;
  }
}

export function getCoreConfig(): StarAtlasCoreConfig | undefined {
  return window.StarAtlasCore;
}
