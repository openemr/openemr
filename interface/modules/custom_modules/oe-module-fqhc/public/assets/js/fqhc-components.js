/**
 * FQHC Design System — Web Component library
 *
 * Dependency-free custom elements (no build step, no framework runtime) for the
 * FQHC UI. Each component uses Shadow DOM for style encapsulation and consumes
 * the design tokens from tokens.css (CSS custom properties cross the shadow
 * boundary). These islands mount inside the server-rendered OpenEMR shell
 * without touching any certified page.
 *
 * Components:
 *   <fqhc-page-header heading="..." subheading="...">      [slot: actions]
 *   <fqhc-card heading="..." span-wide>                    [default slot]
 *   <fqhc-field-row label="..." value="...">               (value="" => em-dash)
 *   <fqhc-status-badge variant="success|warning|danger|info|neutral">text</>
 *   <fqhc-empty-state message="...">                       [default slot]
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(() => {
  const html = (strings, ...values) => strings.reduce((acc, s, i) => acc + s + (values[i] ?? ''), '');

  // Helpers are declared before customElements.define(...) because define()
  // upgrades already-parsed elements synchronously and immediately calls
  // render(); anything render() touches must already be initialized (a const
  // declared lower in this IIFE would still be in its temporal dead zone).
  const ALLOWED_VARIANTS = ['success', 'warning', 'danger', 'info', 'neutral'];

  function cssClass(variant) {
    return ALLOWED_VARIANTS.includes(variant) ? variant : 'neutral';
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  /** Shared base: renders a <style> + template into an open shadow root once. */
  class FqhcElement extends HTMLElement {
    constructor() {
      super();
      this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
      if (!this._rendered) {
        this.shadowRoot.innerHTML = this.render();
        this._rendered = true;
      }
    }

    render() {
      return '';
    }
  }

  customElements.define('fqhc-page-header', class extends FqhcElement {
    render() {
      const heading = this.getAttribute('heading') ?? '';
      const subheading = this.getAttribute('subheading') ?? '';
      return html`
        <style>
          :host { display: block; margin-bottom: var(--fqhc-space-5); }
          .wrap {
            display: flex; flex-wrap: wrap; gap: var(--fqhc-space-3);
            align-items: flex-end; justify-content: space-between;
            padding-bottom: var(--fqhc-space-4);
            border-bottom: 1px solid var(--fqhc-border);
          }
          h1 {
            margin: 0; font-family: var(--fqhc-font-sans);
            font-size: var(--fqhc-font-size-2xl);
            font-weight: var(--fqhc-font-weight-semibold);
            color: var(--fqhc-text); letter-spacing: -0.01em;
          }
          p {
            margin: var(--fqhc-space-1) 0 0; color: var(--fqhc-text-muted);
            font-family: var(--fqhc-font-sans); font-size: var(--fqhc-font-size-sm);
          }
        </style>
        <div class="wrap">
          <div>
            <h1>${escapeHtml(heading)}</h1>
            ${subheading ? `<p>${escapeHtml(subheading)}</p>` : ''}
          </div>
          <slot name="actions"></slot>
        </div>
      `;
    }
  });

  customElements.define('fqhc-card', class extends FqhcElement {
    render() {
      const heading = this.getAttribute('heading') ?? '';
      return html`
        <style>
          :host { display: block; }
          .card {
            background: var(--fqhc-surface-card);
            border: 1px solid var(--fqhc-border);
            border-radius: var(--fqhc-radius-lg);
            box-shadow: var(--fqhc-shadow-sm);
            overflow: hidden; height: 100%;
            transition: box-shadow var(--fqhc-transition), transform var(--fqhc-transition);
          }
          .card:hover { box-shadow: var(--fqhc-shadow-md); }
          .head {
            margin: 0;
            padding: var(--fqhc-space-4) var(--fqhc-space-5);
            border-bottom: 1px solid var(--fqhc-border);
            font-family: var(--fqhc-font-sans);
            font-size: var(--fqhc-font-size-xs);
            font-weight: var(--fqhc-font-weight-semibold);
            text-transform: uppercase; letter-spacing: 0.06em;
            color: var(--fqhc-color-primary-strong);
          }
          .body { padding: var(--fqhc-space-2) var(--fqhc-space-5) var(--fqhc-space-4); }
        </style>
        <div class="card">
          ${heading ? `<h2 class="head">${escapeHtml(heading)}</h2>` : ''}
          <div class="body"><slot></slot></div>
        </div>
      `;
    }
  });

  customElements.define('fqhc-field-row', class extends FqhcElement {
    render() {
      const label = this.getAttribute('label') ?? '';
      const value = this.getAttribute('value');
      const hasValue = value !== null && value.trim() !== '';
      return html`
        <style>
          :host { display: block; }
          .row {
            display: flex; gap: var(--fqhc-space-4); justify-content: space-between;
            align-items: baseline;
            padding: var(--fqhc-space-3) 0;
            border-bottom: 1px solid var(--fqhc-surface-sunken);
            font-family: var(--fqhc-font-sans);
          }
          :host(:last-of-type) .row { border-bottom: none; }
          .label { color: var(--fqhc-text-muted); font-size: var(--fqhc-font-size-sm); }
          .value {
            color: var(--fqhc-text); font-weight: var(--fqhc-font-weight-medium);
            font-size: var(--fqhc-font-size-base); text-align: right;
          }
          .value.empty { color: var(--fqhc-border-strong); font-weight: var(--fqhc-font-weight-regular); }
        </style>
        <div class="row">
          <span class="label">${escapeHtml(label)}</span>
          <span class="value ${hasValue ? '' : 'empty'}">${hasValue ? escapeHtml(value) : '—'}</span>
        </div>
      `;
    }
  });

  customElements.define('fqhc-status-badge', class extends FqhcElement {
    render() {
      const variant = this.getAttribute('variant') ?? 'neutral';
      return html`
        <style>
          :host { display: inline-block; }
          .badge {
            display: inline-flex; align-items: center; gap: var(--fqhc-space-1);
            padding: 2px var(--fqhc-space-3);
            border-radius: var(--fqhc-radius-pill);
            font-family: var(--fqhc-font-sans);
            font-size: var(--fqhc-font-size-xs);
            font-weight: var(--fqhc-font-weight-semibold);
            line-height: 1.6;
          }
          .success { background: var(--fqhc-color-success-soft); color: var(--fqhc-color-success); }
          .warning { background: var(--fqhc-color-warning-soft); color: var(--fqhc-color-warning); }
          .danger  { background: var(--fqhc-color-danger-soft);  color: var(--fqhc-color-danger); }
          .info    { background: var(--fqhc-color-info-soft);    color: var(--fqhc-color-info); }
          .neutral { background: var(--fqhc-color-neutral-soft); color: var(--fqhc-color-neutral); }
        </style>
        <span class="badge ${cssClass(variant)}"><slot></slot></span>
      `;
    }
  });

  customElements.define('fqhc-empty-state', class extends FqhcElement {
    render() {
      const message = this.getAttribute('message') ?? '';
      return html`
        <style>
          :host { display: block; }
          .empty {
            display: flex; flex-direction: column; align-items: center; gap: var(--fqhc-space-2);
            text-align: center;
            padding: var(--fqhc-space-5) var(--fqhc-space-4);
            border: 1px dashed var(--fqhc-border-strong);
            border-radius: var(--fqhc-radius-md);
            background: var(--fqhc-surface-sunken);
            color: var(--fqhc-text-muted);
            font-family: var(--fqhc-font-sans); font-size: var(--fqhc-font-size-sm);
          }
          .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--fqhc-border-strong);
          }
        </style>
        <div class="empty">
          <span class="dot" aria-hidden="true"></span>
          <span>${escapeHtml(message)}</span>
          <slot></slot>
        </div>
      `;
    }
  });
})();
