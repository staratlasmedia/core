import { css } from 'lit';

export const widgetStyles = css`
  :host {
    color: #18212f;
    display: block;
    font-family:
      Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    line-height: 1.45;
  }

  .core-widget {
    background: #ffffff;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgb(15 23 42 / 8%);
    box-sizing: border-box;
    max-width: 100%;
    padding: 16px;
  }

  .compact {
    display: inline-flex;
    padding: 0;
  }

  .title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px;
  }

  .copy {
    color: #4d5b6b;
    font-size: 14px;
    margin: 0 0 12px;
  }

  .meta {
    color: #657386;
    font-size: 12px;
    margin: 8px 0 0;
  }

  button {
    align-items: center;
    background: #0f766e;
    border: 0;
    border-radius: 6px;
    color: #ffffff;
    cursor: pointer;
    display: inline-flex;
    font: inherit;
    font-size: 14px;
    font-weight: 700;
    gap: 8px;
    justify-content: center;
    min-height: 38px;
    padding: 9px 14px;
  }

  button:disabled {
    background: #92a2b4;
    cursor: not-allowed;
  }

  .secondary {
    background: #26384d;
  }

  .status {
    color: #566579;
    display: block;
    font-size: 12px;
    margin-top: 10px;
  }
`;
