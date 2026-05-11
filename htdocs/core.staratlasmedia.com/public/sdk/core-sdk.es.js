const H = globalThis, K = H.ShadowRoot && (H.ShadyCSS === void 0 || H.ShadyCSS.nativeShadow) && "adoptedStyleSheets" in Document.prototype && "replace" in CSSStyleSheet.prototype, J = /* @__PURE__ */ Symbol(), rt = /* @__PURE__ */ new WeakMap();
let ft = class {
  constructor(t, e, r) {
    if (this._$cssResult$ = !0, r !== J) throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");
    this.cssText = t, this.t = e;
  }
  get styleSheet() {
    let t = this.o;
    const e = this.t;
    if (K && t === void 0) {
      const r = e !== void 0 && e.length === 1;
      r && (t = rt.get(e)), t === void 0 && ((this.o = t = new CSSStyleSheet()).replaceSync(this.cssText), r && rt.set(e, t));
    }
    return t;
  }
  toString() {
    return this.cssText;
  }
};
const Ct = (s) => new ft(typeof s == "string" ? s : s + "", void 0, J), St = (s, ...t) => {
  const e = s.length === 1 ? s[0] : t.reduce((r, i, n) => r + ((o) => {
    if (o._$cssResult$ === !0) return o.cssText;
    if (typeof o == "number") return o;
    throw Error("Value passed to 'css' function must be a 'css' function result: " + o + ". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.");
  })(i) + s[n + 1], s[0]);
  return new ft(e, s, J);
}, Et = (s, t) => {
  if (K) s.adoptedStyleSheets = t.map((e) => e instanceof CSSStyleSheet ? e : e.styleSheet);
  else for (const e of t) {
    const r = document.createElement("style"), i = H.litNonce;
    i !== void 0 && r.setAttribute("nonce", i), r.textContent = e.cssText, s.appendChild(r);
  }
}, it = K ? (s) => s : (s) => s instanceof CSSStyleSheet ? ((t) => {
  let e = "";
  for (const r of t.cssRules) e += r.cssText;
  return Ct(e);
})(s) : s;
const { is: Pt, defineProperty: Ut, getOwnPropertyDescriptor: xt, getOwnPropertyNames: Ot, getOwnPropertySymbols: kt, getPrototypeOf: Tt } = Object, z = globalThis, nt = z.trustedTypes, Mt = nt ? nt.emptyScript : "", Rt = z.reactiveElementPolyfillSupport, E = (s, t) => s, N = { toAttribute(s, t) {
  switch (t) {
    case Boolean:
      s = s ? Mt : null;
      break;
    case Object:
    case Array:
      s = s == null ? s : JSON.stringify(s);
  }
  return s;
}, fromAttribute(s, t) {
  let e = s;
  switch (t) {
    case Boolean:
      e = s !== null;
      break;
    case Number:
      e = s === null ? null : Number(s);
      break;
    case Object:
    case Array:
      try {
        e = JSON.parse(s);
      } catch {
        e = null;
      }
  }
  return e;
} }, Z = (s, t) => !Pt(s, t), ot = { attribute: !0, type: String, converter: N, reflect: !1, useDefault: !1, hasChanged: Z };
Symbol.metadata ??= /* @__PURE__ */ Symbol("metadata"), z.litPropertyMetadata ??= /* @__PURE__ */ new WeakMap();
let v = class extends HTMLElement {
  static addInitializer(t) {
    this._$Ei(), (this.l ??= []).push(t);
  }
  static get observedAttributes() {
    return this.finalize(), this._$Eh && [...this._$Eh.keys()];
  }
  static createProperty(t, e = ot) {
    if (e.state && (e.attribute = !1), this._$Ei(), this.prototype.hasOwnProperty(t) && ((e = Object.create(e)).wrapped = !0), this.elementProperties.set(t, e), !e.noAccessor) {
      const r = /* @__PURE__ */ Symbol(), i = this.getPropertyDescriptor(t, r, e);
      i !== void 0 && Ut(this.prototype, t, i);
    }
  }
  static getPropertyDescriptor(t, e, r) {
    const { get: i, set: n } = xt(this.prototype, t) ?? { get() {
      return this[e];
    }, set(o) {
      this[e] = o;
    } };
    return { get: i, set(o) {
      const c = i?.call(this);
      n?.call(this, o), this.requestUpdate(t, c, r);
    }, configurable: !0, enumerable: !0 };
  }
  static getPropertyOptions(t) {
    return this.elementProperties.get(t) ?? ot;
  }
  static _$Ei() {
    if (this.hasOwnProperty(E("elementProperties"))) return;
    const t = Tt(this);
    t.finalize(), t.l !== void 0 && (this.l = [...t.l]), this.elementProperties = new Map(t.elementProperties);
  }
  static finalize() {
    if (this.hasOwnProperty(E("finalized"))) return;
    if (this.finalized = !0, this._$Ei(), this.hasOwnProperty(E("properties"))) {
      const e = this.properties, r = [...Ot(e), ...kt(e)];
      for (const i of r) this.createProperty(i, e[i]);
    }
    const t = this[Symbol.metadata];
    if (t !== null) {
      const e = litPropertyMetadata.get(t);
      if (e !== void 0) for (const [r, i] of e) this.elementProperties.set(r, i);
    }
    this._$Eh = /* @__PURE__ */ new Map();
    for (const [e, r] of this.elementProperties) {
      const i = this._$Eu(e, r);
      i !== void 0 && this._$Eh.set(i, e);
    }
    this.elementStyles = this.finalizeStyles(this.styles);
  }
  static finalizeStyles(t) {
    const e = [];
    if (Array.isArray(t)) {
      const r = new Set(t.flat(1 / 0).reverse());
      for (const i of r) e.unshift(it(i));
    } else t !== void 0 && e.push(it(t));
    return e;
  }
  static _$Eu(t, e) {
    const r = e.attribute;
    return r === !1 ? void 0 : typeof r == "string" ? r : typeof t == "string" ? t.toLowerCase() : void 0;
  }
  constructor() {
    super(), this._$Ep = void 0, this.isUpdatePending = !1, this.hasUpdated = !1, this._$Em = null, this._$Ev();
  }
  _$Ev() {
    this._$ES = new Promise((t) => this.enableUpdating = t), this._$AL = /* @__PURE__ */ new Map(), this._$E_(), this.requestUpdate(), this.constructor.l?.forEach((t) => t(this));
  }
  addController(t) {
    (this._$EO ??= /* @__PURE__ */ new Set()).add(t), this.renderRoot !== void 0 && this.isConnected && t.hostConnected?.();
  }
  removeController(t) {
    this._$EO?.delete(t);
  }
  _$E_() {
    const t = /* @__PURE__ */ new Map(), e = this.constructor.elementProperties;
    for (const r of e.keys()) this.hasOwnProperty(r) && (t.set(r, this[r]), delete this[r]);
    t.size > 0 && (this._$Ep = t);
  }
  createRenderRoot() {
    const t = this.shadowRoot ?? this.attachShadow(this.constructor.shadowRootOptions);
    return Et(t, this.constructor.elementStyles), t;
  }
  connectedCallback() {
    this.renderRoot ??= this.createRenderRoot(), this.enableUpdating(!0), this._$EO?.forEach((t) => t.hostConnected?.());
  }
  enableUpdating(t) {
  }
  disconnectedCallback() {
    this._$EO?.forEach((t) => t.hostDisconnected?.());
  }
  attributeChangedCallback(t, e, r) {
    this._$AK(t, r);
  }
  _$ET(t, e) {
    const r = this.constructor.elementProperties.get(t), i = this.constructor._$Eu(t, r);
    if (i !== void 0 && r.reflect === !0) {
      const n = (r.converter?.toAttribute !== void 0 ? r.converter : N).toAttribute(e, r.type);
      this._$Em = t, n == null ? this.removeAttribute(i) : this.setAttribute(i, n), this._$Em = null;
    }
  }
  _$AK(t, e) {
    const r = this.constructor, i = r._$Eh.get(t);
    if (i !== void 0 && this._$Em !== i) {
      const n = r.getPropertyOptions(i), o = typeof n.converter == "function" ? { fromAttribute: n.converter } : n.converter?.fromAttribute !== void 0 ? n.converter : N;
      this._$Em = i;
      const c = o.fromAttribute(e, n.type);
      this[i] = c ?? this._$Ej?.get(i) ?? c, this._$Em = null;
    }
  }
  requestUpdate(t, e, r, i = !1, n) {
    if (t !== void 0) {
      const o = this.constructor;
      if (i === !1 && (n = this[t]), r ??= o.getPropertyOptions(t), !((r.hasChanged ?? Z)(n, e) || r.useDefault && r.reflect && n === this._$Ej?.get(t) && !this.hasAttribute(o._$Eu(t, r)))) return;
      this.C(t, e, r);
    }
    this.isUpdatePending === !1 && (this._$ES = this._$EP());
  }
  C(t, e, { useDefault: r, reflect: i, wrapped: n }, o) {
    r && !(this._$Ej ??= /* @__PURE__ */ new Map()).has(t) && (this._$Ej.set(t, o ?? e ?? this[t]), n !== !0 || o !== void 0) || (this._$AL.has(t) || (this.hasUpdated || r || (e = void 0), this._$AL.set(t, e)), i === !0 && this._$Em !== t && (this._$Eq ??= /* @__PURE__ */ new Set()).add(t));
  }
  async _$EP() {
    this.isUpdatePending = !0;
    try {
      await this._$ES;
    } catch (e) {
      Promise.reject(e);
    }
    const t = this.scheduleUpdate();
    return t != null && await t, !this.isUpdatePending;
  }
  scheduleUpdate() {
    return this.performUpdate();
  }
  performUpdate() {
    if (!this.isUpdatePending) return;
    if (!this.hasUpdated) {
      if (this.renderRoot ??= this.createRenderRoot(), this._$Ep) {
        for (const [i, n] of this._$Ep) this[i] = n;
        this._$Ep = void 0;
      }
      const r = this.constructor.elementProperties;
      if (r.size > 0) for (const [i, n] of r) {
        const { wrapped: o } = n, c = this[i];
        o !== !0 || this._$AL.has(i) || c === void 0 || this.C(i, void 0, n, c);
      }
    }
    let t = !1;
    const e = this._$AL;
    try {
      t = this.shouldUpdate(e), t ? (this.willUpdate(e), this._$EO?.forEach((r) => r.hostUpdate?.()), this.update(e)) : this._$EM();
    } catch (r) {
      throw t = !1, this._$EM(), r;
    }
    t && this._$AE(e);
  }
  willUpdate(t) {
  }
  _$AE(t) {
    this._$EO?.forEach((e) => e.hostUpdated?.()), this.hasUpdated || (this.hasUpdated = !0, this.firstUpdated(t)), this.updated(t);
  }
  _$EM() {
    this._$AL = /* @__PURE__ */ new Map(), this.isUpdatePending = !1;
  }
  get updateComplete() {
    return this.getUpdateComplete();
  }
  getUpdateComplete() {
    return this._$ES;
  }
  shouldUpdate(t) {
    return !0;
  }
  update(t) {
    this._$Eq &&= this._$Eq.forEach((e) => this._$ET(e, this[e])), this._$EM();
  }
  updated(t) {
  }
  firstUpdated(t) {
  }
};
v.elementStyles = [], v.shadowRootOptions = { mode: "open" }, v[E("elementProperties")] = /* @__PURE__ */ new Map(), v[E("finalized")] = /* @__PURE__ */ new Map(), Rt?.({ ReactiveElement: v }), (z.reactiveElementVersions ??= []).push("2.1.2");
const G = globalThis, at = (s) => s, j = G.trustedTypes, ct = j ? j.createPolicy("lit-html", { createHTML: (s) => s }) : void 0, gt = "$lit$", $ = `lit$${Math.random().toFixed(9).slice(2)}$`, $t = "?" + $, Ht = `<${$t}>`, b = document, P = () => b.createComment(""), U = (s) => s === null || typeof s != "object" && typeof s != "function", Q = Array.isArray, Nt = (s) => Q(s) || typeof s?.[Symbol.iterator] == "function", q = `[ 	
\f\r]`, S = /<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g, lt = /-->/g, ht = />/g, _ = RegExp(`>|${q}(?:([^\\s"'>=/]+)(${q}*=${q}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`, "g"), ut = /'/g, pt = /"/g, mt = /^(?:script|style|textarea|title)$/i, jt = (s) => (t, ...e) => ({ _$litType$: s, strings: t, values: e }), x = jt(1), A = /* @__PURE__ */ Symbol.for("lit-noChange"), u = /* @__PURE__ */ Symbol.for("lit-nothing"), dt = /* @__PURE__ */ new WeakMap(), y = b.createTreeWalker(b, 129);
function _t(s, t) {
  if (!Q(s) || !s.hasOwnProperty("raw")) throw Error("invalid template strings array");
  return ct !== void 0 ? ct.createHTML(t) : t;
}
const Dt = (s, t) => {
  const e = s.length - 1, r = [];
  let i, n = t === 2 ? "<svg>" : t === 3 ? "<math>" : "", o = S;
  for (let c = 0; c < e; c++) {
    const a = s[c];
    let h, p, l = -1, f = 0;
    for (; f < a.length && (o.lastIndex = f, p = o.exec(a), p !== null); ) f = o.lastIndex, o === S ? p[1] === "!--" ? o = lt : p[1] !== void 0 ? o = ht : p[2] !== void 0 ? (mt.test(p[2]) && (i = RegExp("</" + p[2], "g")), o = _) : p[3] !== void 0 && (o = _) : o === _ ? p[0] === ">" ? (o = i ?? S, l = -1) : p[1] === void 0 ? l = -2 : (l = o.lastIndex - p[2].length, h = p[1], o = p[3] === void 0 ? _ : p[3] === '"' ? pt : ut) : o === pt || o === ut ? o = _ : o === lt || o === ht ? o = S : (o = _, i = void 0);
    const g = o === _ && s[c + 1].startsWith("/>") ? " " : "";
    n += o === S ? a + Ht : l >= 0 ? (r.push(h), a.slice(0, l) + gt + a.slice(l) + $ + g) : a + $ + (l === -2 ? c : g);
  }
  return [_t(s, n + (s[e] || "<?>") + (t === 2 ? "</svg>" : t === 3 ? "</math>" : "")), r];
};
class O {
  constructor({ strings: t, _$litType$: e }, r) {
    let i;
    this.parts = [];
    let n = 0, o = 0;
    const c = t.length - 1, a = this.parts, [h, p] = Dt(t, e);
    if (this.el = O.createElement(h, r), y.currentNode = this.el.content, e === 2 || e === 3) {
      const l = this.el.content.firstChild;
      l.replaceWith(...l.childNodes);
    }
    for (; (i = y.nextNode()) !== null && a.length < c; ) {
      if (i.nodeType === 1) {
        if (i.hasAttributes()) for (const l of i.getAttributeNames()) if (l.endsWith(gt)) {
          const f = p[o++], g = i.getAttribute(l).split($), R = /([.?@])?(.*)/.exec(f);
          a.push({ type: 1, index: n, name: R[2], strings: g, ctor: R[1] === "." ? Wt : R[1] === "?" ? zt : R[1] === "@" ? Bt : B }), i.removeAttribute(l);
        } else l.startsWith($) && (a.push({ type: 6, index: n }), i.removeAttribute(l));
        if (mt.test(i.tagName)) {
          const l = i.textContent.split($), f = l.length - 1;
          if (f > 0) {
            i.textContent = j ? j.emptyScript : "";
            for (let g = 0; g < f; g++) i.append(l[g], P()), y.nextNode(), a.push({ type: 2, index: ++n });
            i.append(l[f], P());
          }
        }
      } else if (i.nodeType === 8) if (i.data === $t) a.push({ type: 2, index: n });
      else {
        let l = -1;
        for (; (l = i.data.indexOf($, l + 1)) !== -1; ) a.push({ type: 7, index: n }), l += $.length - 1;
      }
      n++;
    }
  }
  static createElement(t, e) {
    const r = b.createElement("template");
    return r.innerHTML = t, r;
  }
}
function w(s, t, e = s, r) {
  if (t === A) return t;
  let i = r !== void 0 ? e._$Co?.[r] : e._$Cl;
  const n = U(t) ? void 0 : t._$litDirective$;
  return i?.constructor !== n && (i?._$AO?.(!1), n === void 0 ? i = void 0 : (i = new n(s), i._$AT(s, e, r)), r !== void 0 ? (e._$Co ??= [])[r] = i : e._$Cl = i), i !== void 0 && (t = w(s, i._$AS(s, t.values), i, r)), t;
}
class Lt {
  constructor(t, e) {
    this._$AV = [], this._$AN = void 0, this._$AD = t, this._$AM = e;
  }
  get parentNode() {
    return this._$AM.parentNode;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  u(t) {
    const { el: { content: e }, parts: r } = this._$AD, i = (t?.creationScope ?? b).importNode(e, !0);
    y.currentNode = i;
    let n = y.nextNode(), o = 0, c = 0, a = r[0];
    for (; a !== void 0; ) {
      if (o === a.index) {
        let h;
        a.type === 2 ? h = new M(n, n.nextSibling, this, t) : a.type === 1 ? h = new a.ctor(n, a.name, a.strings, this, t) : a.type === 6 && (h = new It(n, this, t)), this._$AV.push(h), a = r[++c];
      }
      o !== a?.index && (n = y.nextNode(), o++);
    }
    return y.currentNode = b, i;
  }
  p(t) {
    let e = 0;
    for (const r of this._$AV) r !== void 0 && (r.strings !== void 0 ? (r._$AI(t, r, e), e += r.strings.length - 2) : r._$AI(t[e])), e++;
  }
}
class M {
  get _$AU() {
    return this._$AM?._$AU ?? this._$Cv;
  }
  constructor(t, e, r, i) {
    this.type = 2, this._$AH = u, this._$AN = void 0, this._$AA = t, this._$AB = e, this._$AM = r, this.options = i, this._$Cv = i?.isConnected ?? !0;
  }
  get parentNode() {
    let t = this._$AA.parentNode;
    const e = this._$AM;
    return e !== void 0 && t?.nodeType === 11 && (t = e.parentNode), t;
  }
  get startNode() {
    return this._$AA;
  }
  get endNode() {
    return this._$AB;
  }
  _$AI(t, e = this) {
    t = w(this, t, e), U(t) ? t === u || t == null || t === "" ? (this._$AH !== u && this._$AR(), this._$AH = u) : t !== this._$AH && t !== A && this._(t) : t._$litType$ !== void 0 ? this.$(t) : t.nodeType !== void 0 ? this.T(t) : Nt(t) ? this.k(t) : this._(t);
  }
  O(t) {
    return this._$AA.parentNode.insertBefore(t, this._$AB);
  }
  T(t) {
    this._$AH !== t && (this._$AR(), this._$AH = this.O(t));
  }
  _(t) {
    this._$AH !== u && U(this._$AH) ? this._$AA.nextSibling.data = t : this.T(b.createTextNode(t)), this._$AH = t;
  }
  $(t) {
    const { values: e, _$litType$: r } = t, i = typeof r == "number" ? this._$AC(t) : (r.el === void 0 && (r.el = O.createElement(_t(r.h, r.h[0]), this.options)), r);
    if (this._$AH?._$AD === i) this._$AH.p(e);
    else {
      const n = new Lt(i, this), o = n.u(this.options);
      n.p(e), this.T(o), this._$AH = n;
    }
  }
  _$AC(t) {
    let e = dt.get(t.strings);
    return e === void 0 && dt.set(t.strings, e = new O(t)), e;
  }
  k(t) {
    Q(this._$AH) || (this._$AH = [], this._$AR());
    const e = this._$AH;
    let r, i = 0;
    for (const n of t) i === e.length ? e.push(r = new M(this.O(P()), this.O(P()), this, this.options)) : r = e[i], r._$AI(n), i++;
    i < e.length && (this._$AR(r && r._$AB.nextSibling, i), e.length = i);
  }
  _$AR(t = this._$AA.nextSibling, e) {
    for (this._$AP?.(!1, !0, e); t !== this._$AB; ) {
      const r = at(t).nextSibling;
      at(t).remove(), t = r;
    }
  }
  setConnected(t) {
    this._$AM === void 0 && (this._$Cv = t, this._$AP?.(t));
  }
}
class B {
  get tagName() {
    return this.element.tagName;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  constructor(t, e, r, i, n) {
    this.type = 1, this._$AH = u, this._$AN = void 0, this.element = t, this.name = e, this._$AM = i, this.options = n, r.length > 2 || r[0] !== "" || r[1] !== "" ? (this._$AH = Array(r.length - 1).fill(new String()), this.strings = r) : this._$AH = u;
  }
  _$AI(t, e = this, r, i) {
    const n = this.strings;
    let o = !1;
    if (n === void 0) t = w(this, t, e, 0), o = !U(t) || t !== this._$AH && t !== A, o && (this._$AH = t);
    else {
      const c = t;
      let a, h;
      for (t = n[0], a = 0; a < n.length - 1; a++) h = w(this, c[r + a], e, a), h === A && (h = this._$AH[a]), o ||= !U(h) || h !== this._$AH[a], h === u ? t = u : t !== u && (t += (h ?? "") + n[a + 1]), this._$AH[a] = h;
    }
    o && !i && this.j(t);
  }
  j(t) {
    t === u ? this.element.removeAttribute(this.name) : this.element.setAttribute(this.name, t ?? "");
  }
}
class Wt extends B {
  constructor() {
    super(...arguments), this.type = 3;
  }
  j(t) {
    this.element[this.name] = t === u ? void 0 : t;
  }
}
class zt extends B {
  constructor() {
    super(...arguments), this.type = 4;
  }
  j(t) {
    this.element.toggleAttribute(this.name, !!t && t !== u);
  }
}
class Bt extends B {
  constructor(t, e, r, i, n) {
    super(t, e, r, i, n), this.type = 5;
  }
  _$AI(t, e = this) {
    if ((t = w(this, t, e, 0) ?? u) === A) return;
    const r = this._$AH, i = t === u && r !== u || t.capture !== r.capture || t.once !== r.once || t.passive !== r.passive, n = t !== u && (r === u || i);
    i && this.element.removeEventListener(this.name, this, r), n && this.element.addEventListener(this.name, this, t), this._$AH = t;
  }
  handleEvent(t) {
    typeof this._$AH == "function" ? this._$AH.call(this.options?.host ?? this.element, t) : this._$AH.handleEvent(t);
  }
}
class It {
  constructor(t, e, r) {
    this.element = t, this.type = 6, this._$AN = void 0, this._$AM = e, this.options = r;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  _$AI(t) {
    w(this, t);
  }
}
const Vt = G.litHtmlPolyfillSupport;
Vt?.(O, M), (G.litHtmlVersions ??= []).push("3.3.2");
const qt = (s, t, e) => {
  const r = e?.renderBefore ?? t;
  let i = r._$litPart$;
  if (i === void 0) {
    const n = e?.renderBefore ?? null;
    r._$litPart$ = i = new M(t.insertBefore(P(), n), n, void 0, e ?? {});
  }
  return i._$AI(s), i;
};
const X = globalThis;
class m extends v {
  constructor() {
    super(...arguments), this.renderOptions = { host: this }, this._$Do = void 0;
  }
  createRenderRoot() {
    const t = super.createRenderRoot();
    return this.renderOptions.renderBefore ??= t.firstChild, t;
  }
  update(t) {
    const e = this.render();
    this.hasUpdated || (this.renderOptions.isConnected = this.isConnected), super.update(t), this._$Do = qt(e, this.renderRoot, this.renderOptions);
  }
  connectedCallback() {
    super.connectedCallback(), this._$Do?.setConnected(!0);
  }
  disconnectedCallback() {
    super.disconnectedCallback(), this._$Do?.setConnected(!1);
  }
  render() {
    return A;
  }
}
m._$litElement$ = !0, m.finalized = !0, X.litElementHydrateSupport?.({ LitElement: m });
const Ft = X.litElementPolyfillSupport;
Ft?.({ LitElement: m });
(X.litElementVersions ??= []).push("4.2.2");
const I = (s) => (t, e) => {
  e !== void 0 ? e.addInitializer(() => {
    customElements.define(s, t);
  }) : customElements.define(s, t);
};
const Kt = { attribute: !0, type: String, converter: N, reflect: !1, hasChanged: Z }, Jt = (s = Kt, t, e) => {
  const { kind: r, metadata: i } = e;
  let n = globalThis.litPropertyMetadata.get(i);
  if (n === void 0 && globalThis.litPropertyMetadata.set(i, n = /* @__PURE__ */ new Map()), r === "setter" && ((s = Object.create(s)).wrapped = !0), n.set(e.name, s), r === "accessor") {
    const { name: o } = e;
    return { set(c) {
      const a = t.get.call(this);
      t.set.call(this, c), this.requestUpdate(o, a, s, !0, c);
    }, init(c) {
      return c !== void 0 && this.C(o, void 0, s, c), c;
    } };
  }
  if (r === "setter") {
    const { name: o } = e;
    return function(c) {
      const a = this[o];
      t.call(this, c), this.requestUpdate(o, a, s, !0, c);
    };
  }
  throw Error("Unsupported decorator location: " + r);
};
function Zt(s) {
  return (t, e) => typeof e == "object" ? Jt(s, t, e) : ((r, i, n) => {
    const o = i.hasOwnProperty(n);
    return i.constructor.createProperty(n, r), o ? Object.getOwnPropertyDescriptor(i, n) : void 0;
  })(s, t, e);
}
function C(s) {
  return Zt({ ...s, state: !0, attribute: !1 });
}
const Gt = "https://core.staratlasmedia.com/api/v1", Qt = {
  siteCode: "site-code",
  origin: "origin",
  language: "language",
  section: "section",
  sourceUrl: "source-url",
  sourceTitle: "source-title",
  apiBaseUrl: "api-base-url",
  serviceWorkerUrl: "service-worker-url",
  serviceWorkerScope: "service-worker-scope",
  vapidPublicKey: "vapid-public-key"
};
function yt(s) {
  const t = window.StarAtlasCore ?? {}, e = s ? Yt(s) : {};
  return {
    apiBaseUrl: Gt,
    origin: window.location.origin,
    sourceUrl: window.location.href,
    language: document.documentElement.lang || "it",
    ...t,
    ...e
  };
}
function d(s) {
  const t = yt(s);
  return !t.siteCode || !t.origin || !t.language || !t.sourceUrl || !t.apiBaseUrl ? null : {
    siteCode: t.siteCode,
    origin: t.origin,
    language: t.language,
    section: t.section,
    sourceUrl: t.sourceUrl,
    sourceTitle: t.sourceTitle,
    apiBaseUrl: te(t.apiBaseUrl),
    serviceWorkerUrl: t.serviceWorkerUrl,
    serviceWorkerScope: t.serviceWorkerScope,
    vapidPublicKey: t.vapidPublicKey,
    wpTerms: t.wpTerms
  };
}
function Y(s) {
  return s.serviceWorkerUrl ? s.serviceWorkerUrl : s.section === "en" || s.language === "en" ? "/en/smart_sw.js" : "/smart_sw.js";
}
function tt(s) {
  return s.serviceWorkerScope ? s.serviceWorkerScope : s.section === "en" || s.language === "en" ? "/en/" : "/";
}
function Xt(s) {
  return {
    source_url: s.sourceUrl,
    source_title: s.sourceTitle,
    language: s.language,
    section: s.section,
    wp_terms_json: s.wpTerms,
    referrer: document.referrer || void 0,
    utm_json: ee()
  };
}
function Yt(s) {
  const t = {}, e = t;
  for (const [r, i] of Object.entries(Qt)) {
    const n = s.getAttribute(i);
    n && (e[r] = n);
  }
  return t;
}
function te(s) {
  return s.replace(/\/+$/, "");
}
function ee() {
  const s = {}, t = new URLSearchParams(window.location.search);
  for (const [e, r] of t.entries())
    e.startsWith("utm_") && r && (s[e] = r);
  return Object.keys(s).length > 0 ? s : void 0;
}
const V = St`
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
var se = Object.defineProperty, re = Object.getOwnPropertyDescriptor, bt = (s, t, e, r) => {
  for (var i = r > 1 ? void 0 : r ? re(t, e) : t, n = s.length - 1, o; n >= 0; n--)
    (o = s[n]) && (i = (r ? o(t, e, i) : o(i)) || i);
  return r && i && se(t, e, i), i;
};
let D = class extends m {
  constructor() {
    super(...arguments), this.status = "idle";
  }
  connectedCallback() {
    super.connectedCallback(), this.status = d(this) ? "ready" : "missing-config";
  }
  render() {
    const s = d(this);
    return x`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Commenti</h2>
        <p class="copy">I commenti Core saranno caricati per questa pagina.</p>
        <span class="meta">${s?.siteCode ?? "Core"} · ${s?.sourceUrl ?? "config"}</span>
      </section>
    `;
  }
};
D.styles = V;
bt([
  C()
], D.prototype, "status", 2);
D = bt([
  I("core-comments-widget")
], D);
function F(s, t) {
  const e = new URL(`${s.apiBaseUrl}/auth/start`);
  return e.searchParams.set("site_code", s.siteCode), e.searchParams.set("origin", s.origin), e.searchParams.set("mode", t.mode), e.searchParams.set("state", t.state), e.searchParams.set("nonce", t.nonce), e.searchParams.set("return_url", t.returnUrl), e.toString();
}
function ie(s) {
  const t = window.open("about:blank", "star_atlas_core_login", "popup,width=520,height=720"), e = L(), r = L(), i = F(s, {
    mode: "popup",
    state: e,
    nonce: r,
    returnUrl: `${s.origin}/core-auth/callback`
  });
  return t ? (t.location.href = i, "popup") : (window.location.assign(
    F(s, {
      mode: "redirect",
      state: e,
      nonce: r,
      returnUrl: `${s.origin}/core-auth/callback`
    })
  ), "redirect");
}
function ne(s) {
  const t = document.createElement("iframe"), e = L(), r = L();
  return t.hidden = !0, t.title = "Core session check", t.src = F(s, {
    mode: "silent",
    state: e,
    nonce: r,
    returnUrl: window.location.href
  }), t;
}
function oe(s) {
  return new URL(s.apiBaseUrl).origin;
}
function L() {
  const s = new Uint8Array(16);
  return crypto.getRandomValues(s), Array.from(s, (t) => t.toString(16).padStart(2, "0")).join("");
}
var ae = Object.defineProperty, ce = Object.getOwnPropertyDescriptor, et = (s, t, e, r) => {
  for (var i = r > 1 ? void 0 : r ? ce(t, e) : t, n = s.length - 1, o; n >= 0; n--)
    (o = s[n]) && (i = (r ? o(t, e, i) : o(i)) || i);
  return r && i && ae(t, e, i), i;
};
let k = class extends m {
  constructor() {
    super(...arguments), this.status = "idle", this.message = "", this.onAuthMessage = (s) => {
      const t = d(this);
      !t || s.origin !== oe(t) || s.data?.type === "star-atlas-core:auth-complete" && (this.status = "ready", this.message = "Sessione aggiornata", this.dispatchEvent(
        new CustomEvent("core-auth-complete", {
          bubbles: !0,
          composed: !0,
          detail: s.data
        })
      ));
    };
  }
  connectedCallback() {
    super.connectedCallback();
    const s = d(this);
    this.status = s ? "ready" : "missing-config", s && window.addEventListener("message", this.onAuthMessage);
  }
  disconnectedCallback() {
    window.removeEventListener("message", this.onAuthMessage), super.disconnectedCallback();
  }
  render() {
    const s = this.status === "missing-config";
    return x`
      <div class="core-widget compact">
        <button class="secondary" ?disabled=${s} @click=${this.startLogin}>Accedi</button>
      </div>
      ${this.message ? x`<span class="status">${this.message}</span>` : ""}
    `;
  }
  startLogin() {
    const s = d(this);
    if (!s) {
      this.status = "missing-config", this.message = "Configurazione Core mancante";
      return;
    }
    this.status = ie(s) === "popup" ? "working" : "ready", this.message = this.status === "working" ? "Login Core in corso" : "";
  }
  createSilentCheckFrame() {
    const s = d(this);
    return s ? ne(s) : null;
  }
};
k.styles = V;
et([
  C()
], k.prototype, "status", 2);
et([
  C()
], k.prototype, "message", 2);
k = et([
  I("core-login-widget")
], k);
async function vt(s) {
  if (!("serviceWorker" in navigator))
    throw new Error("service-worker-unsupported");
  return navigator.serviceWorker.register(Y(s), {
    scope: tt(s),
    updateViaCache: "none"
  });
}
async function At(s) {
  if (!("PushManager" in window))
    throw new Error("push-unsupported");
  if (!s.vapidPublicKey)
    throw new Error("missing-vapid-public-key");
  const t = await vt(s), e = await t.pushManager.getSubscription(), r = e ?? await t.pushManager.subscribe({
    userVisibleOnly: !0,
    applicationServerKey: ue(s.vapidPublicKey)
  }), i = he(s, r, !!e);
  return await le(s, i), i;
}
async function le(s, t) {
  const e = await fetch(`${s.apiBaseUrl}/push/subscriptions`, {
    body: JSON.stringify(t),
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    method: "POST"
  });
  if (!e.ok)
    throw new Error(`push-subscription-failed:${e.status}`);
}
function he(s, t, e) {
  return {
    site_code: s.siteCode,
    origin: s.origin,
    service_worker_url: Y(s),
    service_worker_scope: tt(s),
    subscription: t.toJSON(),
    context: Xt(s),
    legacy_reconfirmation: e
  };
}
function ue(s) {
  const t = "=".repeat((4 - s.length % 4) % 4), e = `${s}${t}`.replace(/-/g, "+").replace(/_/g, "/"), r = window.atob(e), i = new Uint8Array(r.length);
  for (let n = 0; n < r.length; n += 1)
    i[n] = r.charCodeAt(n);
  return i.buffer.slice(
    i.byteOffset,
    i.byteOffset + i.byteLength
  );
}
var pe = Object.defineProperty, de = Object.getOwnPropertyDescriptor, st = (s, t, e, r) => {
  for (var i = r > 1 ? void 0 : r ? de(t, e) : t, n = s.length - 1, o; n >= 0; n--)
    (o = s[n]) && (i = (r ? o(t, e, i) : o(i)) || i);
  return r && i && pe(t, e, i), i;
};
let T = class extends m {
  constructor() {
    super(...arguments), this.status = "idle", this.message = "";
  }
  connectedCallback() {
    super.connectedCallback();
    const s = d(this);
    this.status = s ? "ready" : "missing-config", s && (!("serviceWorker" in navigator) || !("PushManager" in window)) && (this.status = "unsupported");
  }
  render() {
    const s = d(this), t = this.status === "missing-config" || this.status === "unsupported" || this.status === "working";
    return x`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Notifiche</h2>
        <p class="copy">Ricevi gli aggiornamenti da ${s?.siteCode ?? "Core"}.</p>
        <button ?disabled=${t} @click=${this.subscribe}>Attiva notifiche</button>
        <span class="status">${this.statusText()}</span>
      </section>
    `;
  }
  async subscribe() {
    const s = d(this);
    if (!s) {
      this.status = "missing-config", this.message = "Configurazione Core mancante";
      return;
    }
    if (!s.vapidPublicKey) {
      this.status = "missing-vapid-public-key", this.message = "Chiave pubblica VAPID mancante";
      return;
    }
    this.status = "working", this.message = `Service Worker ${Y(s)} · scope ${tt(s)}`;
    try {
      const t = await At(s);
      this.status = t.legacy_reconfirmation ? "subscribed" : "sent", this.message = t.legacy_reconfirmation ? "Iscrizione riconfermata" : "Iscrizione inviata", this.dispatchEvent(
        new CustomEvent("core-push-subscribed", {
          bubbles: !0,
          composed: !0,
          detail: t
        })
      );
    } catch (t) {
      this.status = "error", this.message = t instanceof Error ? t.message : "Errore push";
    }
  }
  statusText() {
    return this.message ? this.message : this.status === "unsupported" ? "Push non supportato" : this.status === "missing-config" ? "Configurazione Core mancante" : "Pronto";
  }
};
T.styles = V;
st([
  C()
], T.prototype, "status", 2);
st([
  C()
], T.prototype, "message", 2);
T = st([
  I("core-push-widget")
], T);
var fe = Object.defineProperty, ge = Object.getOwnPropertyDescriptor, wt = (s, t, e, r) => {
  for (var i = r > 1 ? void 0 : r ? ge(t, e) : t, n = s.length - 1, o; n >= 0; n--)
    (o = s[n]) && (i = (r ? o(t, e, i) : o(i)) || i);
  return r && i && fe(t, e, i), i;
};
let W = class extends m {
  constructor() {
    super(...arguments), this.status = "idle";
  }
  connectedCallback() {
    super.connectedCallback(), this.status = d(this) ? "configured" : "missing-config";
  }
  render() {
    return x`<span class="status" data-core-status=${this.status}>Core SDK ${this.status}</span>`;
  }
};
W.styles = V;
wt([
  C()
], W.prototype, "status", 2);
W = wt([
  I("star-atlas-core-status")
], W);
const _e = {
  getConfig: yt,
  requireConfig: d,
  registerServiceWorker: vt,
  subscribeToPush: At
};
export {
  _e as StarAtlasCoreSdk,
  F as buildAuthUrl,
  ne as createSilentCheckFrame,
  yt as getCoreConfig,
  ie as openPopupLogin,
  Xt as pushContextFor,
  he as pushPayloadFor,
  vt as registerCoreServiceWorker,
  d as requireCoreConfig,
  le as sendPushSubscription,
  tt as serviceWorkerScopeFor,
  Y as serviceWorkerUrlFor,
  At as subscribeToCorePush
};
