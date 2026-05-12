const N = globalThis, J = N.ShadowRoot && (N.ShadyCSS === void 0 || N.ShadyCSS.nativeShadow) && "adoptedStyleSheets" in Document.prototype && "replace" in CSSStyleSheet.prototype, Z = /* @__PURE__ */ Symbol(), ne = /* @__PURE__ */ new WeakMap();
let fe = class {
  constructor(e, s, r) {
    if (this._$cssResult$ = !0, r !== Z) throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");
    this.cssText = e, this.t = s;
  }
  get styleSheet() {
    let e = this.o;
    const s = this.t;
    if (J && e === void 0) {
      const r = s !== void 0 && s.length === 1;
      r && (e = ne.get(s)), e === void 0 && ((this.o = e = new CSSStyleSheet()).replaceSync(this.cssText), r && ne.set(s, e));
    }
    return e;
  }
  toString() {
    return this.cssText;
  }
};
const Pe = (t) => new fe(typeof t == "string" ? t : t + "", void 0, Z), Ue = (t, ...e) => {
  const s = t.length === 1 ? t[0] : e.reduce((r, i, n) => r + ((o) => {
    if (o._$cssResult$ === !0) return o.cssText;
    if (typeof o == "number") return o;
    throw Error("Value passed to 'css' function must be a 'css' function result: " + o + ". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.");
  })(i) + t[n + 1], t[0]);
  return new fe(s, t, Z);
}, xe = (t, e) => {
  if (J) t.adoptedStyleSheets = e.map((s) => s instanceof CSSStyleSheet ? s : s.styleSheet);
  else for (const s of e) {
    const r = document.createElement("style"), i = N.litNonce;
    i !== void 0 && r.setAttribute("nonce", i), r.textContent = s.cssText, t.appendChild(r);
  }
}, oe = J ? (t) => t : (t) => t instanceof CSSStyleSheet ? ((e) => {
  let s = "";
  for (const r of e.cssRules) s += r.cssText;
  return Pe(s);
})(t) : t;
const { is: ke, defineProperty: Oe, getOwnPropertyDescriptor: Te, getOwnPropertyNames: Me, getOwnPropertySymbols: Re, getPrototypeOf: je } = Object, D = globalThis, ae = D.trustedTypes, Ne = ae ? ae.emptyScript : "", Le = D.reactiveElementPolyfillSupport, U = (t, e) => t, L = { toAttribute(t, e) {
  switch (e) {
    case Boolean:
      t = t ? Ne : null;
      break;
    case Object:
    case Array:
      t = t == null ? t : JSON.stringify(t);
  }
  return t;
}, fromAttribute(t, e) {
  let s = t;
  switch (e) {
    case Boolean:
      s = t !== null;
      break;
    case Number:
      s = t === null ? null : Number(t);
      break;
    case Object:
    case Array:
      try {
        s = JSON.parse(t);
      } catch {
        s = null;
      }
  }
  return s;
} }, Q = (t, e) => !ke(t, e), ce = { attribute: !0, type: String, converter: L, reflect: !1, useDefault: !1, hasChanged: Q };
Symbol.metadata ??= /* @__PURE__ */ Symbol("metadata"), D.litPropertyMetadata ??= /* @__PURE__ */ new WeakMap();
let w = class extends HTMLElement {
  static addInitializer(e) {
    this._$Ei(), (this.l ??= []).push(e);
  }
  static get observedAttributes() {
    return this.finalize(), this._$Eh && [...this._$Eh.keys()];
  }
  static createProperty(e, s = ce) {
    if (s.state && (s.attribute = !1), this._$Ei(), this.prototype.hasOwnProperty(e) && ((s = Object.create(s)).wrapped = !0), this.elementProperties.set(e, s), !s.noAccessor) {
      const r = /* @__PURE__ */ Symbol(), i = this.getPropertyDescriptor(e, r, s);
      i !== void 0 && Oe(this.prototype, e, i);
    }
  }
  static getPropertyDescriptor(e, s, r) {
    const { get: i, set: n } = Te(this.prototype, e) ?? { get() {
      return this[s];
    }, set(o) {
      this[s] = o;
    } };
    return { get: i, set(o) {
      const c = i?.call(this);
      n?.call(this, o), this.requestUpdate(e, c, r);
    }, configurable: !0, enumerable: !0 };
  }
  static getPropertyOptions(e) {
    return this.elementProperties.get(e) ?? ce;
  }
  static _$Ei() {
    if (this.hasOwnProperty(U("elementProperties"))) return;
    const e = je(this);
    e.finalize(), e.l !== void 0 && (this.l = [...e.l]), this.elementProperties = new Map(e.elementProperties);
  }
  static finalize() {
    if (this.hasOwnProperty(U("finalized"))) return;
    if (this.finalized = !0, this._$Ei(), this.hasOwnProperty(U("properties"))) {
      const s = this.properties, r = [...Me(s), ...Re(s)];
      for (const i of r) this.createProperty(i, s[i]);
    }
    const e = this[Symbol.metadata];
    if (e !== null) {
      const s = litPropertyMetadata.get(e);
      if (s !== void 0) for (const [r, i] of s) this.elementProperties.set(r, i);
    }
    this._$Eh = /* @__PURE__ */ new Map();
    for (const [s, r] of this.elementProperties) {
      const i = this._$Eu(s, r);
      i !== void 0 && this._$Eh.set(i, s);
    }
    this.elementStyles = this.finalizeStyles(this.styles);
  }
  static finalizeStyles(e) {
    const s = [];
    if (Array.isArray(e)) {
      const r = new Set(e.flat(1 / 0).reverse());
      for (const i of r) s.unshift(oe(i));
    } else e !== void 0 && s.push(oe(e));
    return s;
  }
  static _$Eu(e, s) {
    const r = s.attribute;
    return r === !1 ? void 0 : typeof r == "string" ? r : typeof e == "string" ? e.toLowerCase() : void 0;
  }
  constructor() {
    super(), this._$Ep = void 0, this.isUpdatePending = !1, this.hasUpdated = !1, this._$Em = null, this._$Ev();
  }
  _$Ev() {
    this._$ES = new Promise((e) => this.enableUpdating = e), this._$AL = /* @__PURE__ */ new Map(), this._$E_(), this.requestUpdate(), this.constructor.l?.forEach((e) => e(this));
  }
  addController(e) {
    (this._$EO ??= /* @__PURE__ */ new Set()).add(e), this.renderRoot !== void 0 && this.isConnected && e.hostConnected?.();
  }
  removeController(e) {
    this._$EO?.delete(e);
  }
  _$E_() {
    const e = /* @__PURE__ */ new Map(), s = this.constructor.elementProperties;
    for (const r of s.keys()) this.hasOwnProperty(r) && (e.set(r, this[r]), delete this[r]);
    e.size > 0 && (this._$Ep = e);
  }
  createRenderRoot() {
    const e = this.shadowRoot ?? this.attachShadow(this.constructor.shadowRootOptions);
    return xe(e, this.constructor.elementStyles), e;
  }
  connectedCallback() {
    this.renderRoot ??= this.createRenderRoot(), this.enableUpdating(!0), this._$EO?.forEach((e) => e.hostConnected?.());
  }
  enableUpdating(e) {
  }
  disconnectedCallback() {
    this._$EO?.forEach((e) => e.hostDisconnected?.());
  }
  attributeChangedCallback(e, s, r) {
    this._$AK(e, r);
  }
  _$ET(e, s) {
    const r = this.constructor.elementProperties.get(e), i = this.constructor._$Eu(e, r);
    if (i !== void 0 && r.reflect === !0) {
      const n = (r.converter?.toAttribute !== void 0 ? r.converter : L).toAttribute(s, r.type);
      this._$Em = e, n == null ? this.removeAttribute(i) : this.setAttribute(i, n), this._$Em = null;
    }
  }
  _$AK(e, s) {
    const r = this.constructor, i = r._$Eh.get(e);
    if (i !== void 0 && this._$Em !== i) {
      const n = r.getPropertyOptions(i), o = typeof n.converter == "function" ? { fromAttribute: n.converter } : n.converter?.fromAttribute !== void 0 ? n.converter : L;
      this._$Em = i;
      const c = o.fromAttribute(s, n.type);
      this[i] = c ?? this._$Ej?.get(i) ?? c, this._$Em = null;
    }
  }
  requestUpdate(e, s, r, i = !1, n) {
    if (e !== void 0) {
      const o = this.constructor;
      if (i === !1 && (n = this[e]), r ??= o.getPropertyOptions(e), !((r.hasChanged ?? Q)(n, s) || r.useDefault && r.reflect && n === this._$Ej?.get(e) && !this.hasAttribute(o._$Eu(e, r)))) return;
      this.C(e, s, r);
    }
    this.isUpdatePending === !1 && (this._$ES = this._$EP());
  }
  C(e, s, { useDefault: r, reflect: i, wrapped: n }, o) {
    r && !(this._$Ej ??= /* @__PURE__ */ new Map()).has(e) && (this._$Ej.set(e, o ?? s ?? this[e]), n !== !0 || o !== void 0) || (this._$AL.has(e) || (this.hasUpdated || r || (s = void 0), this._$AL.set(e, s)), i === !0 && this._$Em !== e && (this._$Eq ??= /* @__PURE__ */ new Set()).add(e));
  }
  async _$EP() {
    this.isUpdatePending = !0;
    try {
      await this._$ES;
    } catch (s) {
      Promise.reject(s);
    }
    const e = this.scheduleUpdate();
    return e != null && await e, !this.isUpdatePending;
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
    let e = !1;
    const s = this._$AL;
    try {
      e = this.shouldUpdate(s), e ? (this.willUpdate(s), this._$EO?.forEach((r) => r.hostUpdate?.()), this.update(s)) : this._$EM();
    } catch (r) {
      throw e = !1, this._$EM(), r;
    }
    e && this._$AE(s);
  }
  willUpdate(e) {
  }
  _$AE(e) {
    this._$EO?.forEach((s) => s.hostUpdated?.()), this.hasUpdated || (this.hasUpdated = !0, this.firstUpdated(e)), this.updated(e);
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
  shouldUpdate(e) {
    return !0;
  }
  update(e) {
    this._$Eq &&= this._$Eq.forEach((s) => this._$ET(s, this[s])), this._$EM();
  }
  updated(e) {
  }
  firstUpdated(e) {
  }
};
w.elementStyles = [], w.shadowRootOptions = { mode: "open" }, w[U("elementProperties")] = /* @__PURE__ */ new Map(), w[U("finalized")] = /* @__PURE__ */ new Map(), Le?.({ ReactiveElement: w }), (D.reactiveElementVersions ??= []).push("2.1.2");
const X = globalThis, le = (t) => t, H = X.trustedTypes, ue = H ? H.createPolicy("lit-html", { createHTML: (t) => t }) : void 0, _e = "$lit$", $ = `lit$${Math.random().toFixed(9).slice(2)}$`, $e = "?" + $, He = `<${$e}>`, A = document, x = () => A.createComment(""), k = (t) => t === null || typeof t != "object" && typeof t != "function", Y = Array.isArray, We = (t) => Y(t) || typeof t?.[Symbol.iterator] == "function", F = `[ 	
\f\r]`, P = /<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g, he = /-->/g, de = />/g, y = RegExp(`>|${F}(?:([^\\s"'>=/]+)(${F}*=${F}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`, "g"), pe = /'/g, ge = /"/g, be = /^(?:script|style|textarea|title)$/i, Be = (t) => (e, ...s) => ({ _$litType$: t, strings: e, values: s }), f = Be(1), C = /* @__PURE__ */ Symbol.for("lit-noChange"), d = /* @__PURE__ */ Symbol.for("lit-nothing"), me = /* @__PURE__ */ new WeakMap(), v = A.createTreeWalker(A, 129);
function ye(t, e) {
  if (!Y(t) || !t.hasOwnProperty("raw")) throw Error("invalid template strings array");
  return ue !== void 0 ? ue.createHTML(e) : e;
}
const Ie = (t, e) => {
  const s = t.length - 1, r = [];
  let i, n = e === 2 ? "<svg>" : e === 3 ? "<math>" : "", o = P;
  for (let c = 0; c < s; c++) {
    const a = t[c];
    let h, p, l = -1, m = 0;
    for (; m < a.length && (o.lastIndex = m, p = o.exec(a), p !== null); ) m = o.lastIndex, o === P ? p[1] === "!--" ? o = he : p[1] !== void 0 ? o = de : p[2] !== void 0 ? (be.test(p[2]) && (i = RegExp("</" + p[2], "g")), o = y) : p[3] !== void 0 && (o = y) : o === y ? p[0] === ">" ? (o = i ?? P, l = -1) : p[1] === void 0 ? l = -2 : (l = o.lastIndex - p[2].length, h = p[1], o = p[3] === void 0 ? y : p[3] === '"' ? ge : pe) : o === ge || o === pe ? o = y : o === he || o === de ? o = P : (o = y, i = void 0);
    const _ = o === y && t[c + 1].startsWith("/>") ? " " : "";
    n += o === P ? a + He : l >= 0 ? (r.push(h), a.slice(0, l) + _e + a.slice(l) + $ + _) : a + $ + (l === -2 ? c : _);
  }
  return [ye(t, n + (t[s] || "<?>") + (e === 2 ? "</svg>" : e === 3 ? "</math>" : "")), r];
};
class O {
  constructor({ strings: e, _$litType$: s }, r) {
    let i;
    this.parts = [];
    let n = 0, o = 0;
    const c = e.length - 1, a = this.parts, [h, p] = Ie(e, s);
    if (this.el = O.createElement(h, r), v.currentNode = this.el.content, s === 2 || s === 3) {
      const l = this.el.content.firstChild;
      l.replaceWith(...l.childNodes);
    }
    for (; (i = v.nextNode()) !== null && a.length < c; ) {
      if (i.nodeType === 1) {
        if (i.hasAttributes()) for (const l of i.getAttributeNames()) if (l.endsWith(_e)) {
          const m = p[o++], _ = i.getAttribute(l).split($), j = /([.?@])?(.*)/.exec(m);
          a.push({ type: 1, index: n, name: j[2], strings: _, ctor: j[1] === "." ? ze : j[1] === "?" ? qe : j[1] === "@" ? Ve : z }), i.removeAttribute(l);
        } else l.startsWith($) && (a.push({ type: 6, index: n }), i.removeAttribute(l));
        if (be.test(i.tagName)) {
          const l = i.textContent.split($), m = l.length - 1;
          if (m > 0) {
            i.textContent = H ? H.emptyScript : "";
            for (let _ = 0; _ < m; _++) i.append(l[_], x()), v.nextNode(), a.push({ type: 2, index: ++n });
            i.append(l[m], x());
          }
        }
      } else if (i.nodeType === 8) if (i.data === $e) a.push({ type: 2, index: n });
      else {
        let l = -1;
        for (; (l = i.data.indexOf($, l + 1)) !== -1; ) a.push({ type: 7, index: n }), l += $.length - 1;
      }
      n++;
    }
  }
  static createElement(e, s) {
    const r = A.createElement("template");
    return r.innerHTML = e, r;
  }
}
function E(t, e, s = t, r) {
  if (e === C) return e;
  let i = r !== void 0 ? s._$Co?.[r] : s._$Cl;
  const n = k(e) ? void 0 : e._$litDirective$;
  return i?.constructor !== n && (i?._$AO?.(!1), n === void 0 ? i = void 0 : (i = new n(t), i._$AT(t, s, r)), r !== void 0 ? (s._$Co ??= [])[r] = i : s._$Cl = i), i !== void 0 && (e = E(t, i._$AS(t, e.values), i, r)), e;
}
class De {
  constructor(e, s) {
    this._$AV = [], this._$AN = void 0, this._$AD = e, this._$AM = s;
  }
  get parentNode() {
    return this._$AM.parentNode;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  u(e) {
    const { el: { content: s }, parts: r } = this._$AD, i = (e?.creationScope ?? A).importNode(s, !0);
    v.currentNode = i;
    let n = v.nextNode(), o = 0, c = 0, a = r[0];
    for (; a !== void 0; ) {
      if (o === a.index) {
        let h;
        a.type === 2 ? h = new R(n, n.nextSibling, this, e) : a.type === 1 ? h = new a.ctor(n, a.name, a.strings, this, e) : a.type === 6 && (h = new Fe(n, this, e)), this._$AV.push(h), a = r[++c];
      }
      o !== a?.index && (n = v.nextNode(), o++);
    }
    return v.currentNode = A, i;
  }
  p(e) {
    let s = 0;
    for (const r of this._$AV) r !== void 0 && (r.strings !== void 0 ? (r._$AI(e, r, s), s += r.strings.length - 2) : r._$AI(e[s])), s++;
  }
}
class R {
  get _$AU() {
    return this._$AM?._$AU ?? this._$Cv;
  }
  constructor(e, s, r, i) {
    this.type = 2, this._$AH = d, this._$AN = void 0, this._$AA = e, this._$AB = s, this._$AM = r, this.options = i, this._$Cv = i?.isConnected ?? !0;
  }
  get parentNode() {
    let e = this._$AA.parentNode;
    const s = this._$AM;
    return s !== void 0 && e?.nodeType === 11 && (e = s.parentNode), e;
  }
  get startNode() {
    return this._$AA;
  }
  get endNode() {
    return this._$AB;
  }
  _$AI(e, s = this) {
    e = E(this, e, s), k(e) ? e === d || e == null || e === "" ? (this._$AH !== d && this._$AR(), this._$AH = d) : e !== this._$AH && e !== C && this._(e) : e._$litType$ !== void 0 ? this.$(e) : e.nodeType !== void 0 ? this.T(e) : We(e) ? this.k(e) : this._(e);
  }
  O(e) {
    return this._$AA.parentNode.insertBefore(e, this._$AB);
  }
  T(e) {
    this._$AH !== e && (this._$AR(), this._$AH = this.O(e));
  }
  _(e) {
    this._$AH !== d && k(this._$AH) ? this._$AA.nextSibling.data = e : this.T(A.createTextNode(e)), this._$AH = e;
  }
  $(e) {
    const { values: s, _$litType$: r } = e, i = typeof r == "number" ? this._$AC(e) : (r.el === void 0 && (r.el = O.createElement(ye(r.h, r.h[0]), this.options)), r);
    if (this._$AH?._$AD === i) this._$AH.p(s);
    else {
      const n = new De(i, this), o = n.u(this.options);
      n.p(s), this.T(o), this._$AH = n;
    }
  }
  _$AC(e) {
    let s = me.get(e.strings);
    return s === void 0 && me.set(e.strings, s = new O(e)), s;
  }
  k(e) {
    Y(this._$AH) || (this._$AH = [], this._$AR());
    const s = this._$AH;
    let r, i = 0;
    for (const n of e) i === s.length ? s.push(r = new R(this.O(x()), this.O(x()), this, this.options)) : r = s[i], r._$AI(n), i++;
    i < s.length && (this._$AR(r && r._$AB.nextSibling, i), s.length = i);
  }
  _$AR(e = this._$AA.nextSibling, s) {
    for (this._$AP?.(!1, !0, s); e !== this._$AB; ) {
      const r = le(e).nextSibling;
      le(e).remove(), e = r;
    }
  }
  setConnected(e) {
    this._$AM === void 0 && (this._$Cv = e, this._$AP?.(e));
  }
}
class z {
  get tagName() {
    return this.element.tagName;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  constructor(e, s, r, i, n) {
    this.type = 1, this._$AH = d, this._$AN = void 0, this.element = e, this.name = s, this._$AM = i, this.options = n, r.length > 2 || r[0] !== "" || r[1] !== "" ? (this._$AH = Array(r.length - 1).fill(new String()), this.strings = r) : this._$AH = d;
  }
  _$AI(e, s = this, r, i) {
    const n = this.strings;
    let o = !1;
    if (n === void 0) e = E(this, e, s, 0), o = !k(e) || e !== this._$AH && e !== C, o && (this._$AH = e);
    else {
      const c = e;
      let a, h;
      for (e = n[0], a = 0; a < n.length - 1; a++) h = E(this, c[r + a], s, a), h === C && (h = this._$AH[a]), o ||= !k(h) || h !== this._$AH[a], h === d ? e = d : e !== d && (e += (h ?? "") + n[a + 1]), this._$AH[a] = h;
    }
    o && !i && this.j(e);
  }
  j(e) {
    e === d ? this.element.removeAttribute(this.name) : this.element.setAttribute(this.name, e ?? "");
  }
}
class ze extends z {
  constructor() {
    super(...arguments), this.type = 3;
  }
  j(e) {
    this.element[this.name] = e === d ? void 0 : e;
  }
}
class qe extends z {
  constructor() {
    super(...arguments), this.type = 4;
  }
  j(e) {
    this.element.toggleAttribute(this.name, !!e && e !== d);
  }
}
class Ve extends z {
  constructor(e, s, r, i, n) {
    super(e, s, r, i, n), this.type = 5;
  }
  _$AI(e, s = this) {
    if ((e = E(this, e, s, 0) ?? d) === C) return;
    const r = this._$AH, i = e === d && r !== d || e.capture !== r.capture || e.once !== r.once || e.passive !== r.passive, n = e !== d && (r === d || i);
    i && this.element.removeEventListener(this.name, this, r), n && this.element.addEventListener(this.name, this, e), this._$AH = e;
  }
  handleEvent(e) {
    typeof this._$AH == "function" ? this._$AH.call(this.options?.host ?? this.element, e) : this._$AH.handleEvent(e);
  }
}
class Fe {
  constructor(e, s, r) {
    this.element = e, this.type = 6, this._$AN = void 0, this._$AM = s, this.options = r;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  _$AI(e) {
    E(this, e);
  }
}
const Ke = X.litHtmlPolyfillSupport;
Ke?.(O, R), (X.litHtmlVersions ??= []).push("3.3.2");
const Ge = (t, e, s) => {
  const r = s?.renderBefore ?? e;
  let i = r._$litPart$;
  if (i === void 0) {
    const n = s?.renderBefore ?? null;
    r._$litPart$ = i = new R(e.insertBefore(x(), n), n, void 0, s ?? {});
  }
  return i._$AI(t), i;
};
const ee = globalThis;
class b extends w {
  constructor() {
    super(...arguments), this.renderOptions = { host: this }, this._$Do = void 0;
  }
  createRenderRoot() {
    const e = super.createRenderRoot();
    return this.renderOptions.renderBefore ??= e.firstChild, e;
  }
  update(e) {
    const s = this.render();
    this.hasUpdated || (this.renderOptions.isConnected = this.isConnected), super.update(e), this._$Do = Ge(s, this.renderRoot, this.renderOptions);
  }
  connectedCallback() {
    super.connectedCallback(), this._$Do?.setConnected(!0);
  }
  disconnectedCallback() {
    super.disconnectedCallback(), this._$Do?.setConnected(!1);
  }
  render() {
    return C;
  }
}
b._$litElement$ = !0, b.finalized = !0, ee.litElementHydrateSupport?.({ LitElement: b });
const Je = ee.litElementPolyfillSupport;
Je?.({ LitElement: b });
(ee.litElementVersions ??= []).push("4.2.2");
const q = (t) => (e, s) => {
  s !== void 0 ? s.addInitializer(() => {
    customElements.define(t, e);
  }) : customElements.define(t, e);
};
const Ze = { attribute: !0, type: String, converter: L, reflect: !1, hasChanged: Q }, Qe = (t = Ze, e, s) => {
  const { kind: r, metadata: i } = s;
  let n = globalThis.litPropertyMetadata.get(i);
  if (n === void 0 && globalThis.litPropertyMetadata.set(i, n = /* @__PURE__ */ new Map()), r === "setter" && ((t = Object.create(t)).wrapped = !0), n.set(s.name, t), r === "accessor") {
    const { name: o } = s;
    return { set(c) {
      const a = e.get.call(this);
      e.set.call(this, c), this.requestUpdate(o, a, t, !0, c);
    }, init(c) {
      return c !== void 0 && this.C(o, void 0, t, c), c;
    } };
  }
  if (r === "setter") {
    const { name: o } = s;
    return function(c) {
      const a = this[o];
      e.call(this, c), this.requestUpdate(o, a, t, !0, c);
    };
  }
  throw Error("Unsupported decorator location: " + r);
};
function Xe(t) {
  return (e, s) => typeof s == "object" ? Qe(t, e, s) : ((r, i, n) => {
    const o = i.hasOwnProperty(n);
    return i.constructor.createProperty(n, r), o ? Object.getOwnPropertyDescriptor(i, n) : void 0;
  })(t, e, s);
}
function S(t) {
  return Xe({ ...t, state: !0, attribute: !1 });
}
const Ye = "https://core.staratlasmedia.com/api/v1", et = {
  siteCode: "site-code",
  pushGroupCode: "push-group-code",
  bridgeInstallationId: "bridge-installation-id",
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
function ve(t) {
  const e = rt(window.StarAtlasCore ?? {}), s = t ? st(t) : {};
  return {
    apiBaseUrl: Ye,
    origin: window.location.origin,
    sourceUrl: window.location.href,
    language: document.documentElement.lang || "it",
    ...e,
    ...s
  };
}
function g(t) {
  const e = ve(t);
  return !e.siteCode || !e.origin || !e.language || !e.sourceUrl || !e.apiBaseUrl ? null : {
    siteCode: e.siteCode,
    pushGroupCode: e.pushGroupCode,
    bridgeInstallationId: e.bridgeInstallationId,
    origin: e.origin,
    language: e.language,
    section: e.section,
    sourceUrl: e.sourceUrl,
    sourceTitle: e.sourceTitle,
    apiBaseUrl: it(e.apiBaseUrl),
    serviceWorkerUrl: e.serviceWorkerUrl,
    serviceWorkerScope: e.serviceWorkerScope,
    vapidPublicKey: e.vapidPublicKey,
    wpTerms: e.wpTerms,
    comments: Ae(e.comments)
  };
}
function te(t) {
  return t.serviceWorkerUrl ? t.serviceWorkerUrl : t.section === "en" || t.language === "en" ? "/en/smart_sw.js" : "/smart_sw.js";
}
function se(t) {
  return t.serviceWorkerScope ? t.serviceWorkerScope : t.section === "en" || t.language === "en" ? "/en/" : "/";
}
function tt(t) {
  return {
    source_url: t.sourceUrl,
    source_title: t.sourceTitle,
    language: t.language,
    section: t.section,
    wp_terms_json: t.wpTerms,
    referrer: document.referrer || void 0,
    utm_json: nt()
  };
}
function st(t) {
  const e = {}, s = e;
  for (const [r, i] of Object.entries(et)) {
    const n = t.getAttribute(i);
    n && (s[r] = n);
  }
  return e;
}
function rt(t) {
  return {
    siteCode: u(t.siteCode ?? t.site_code),
    pushGroupCode: u(t.pushGroupCode ?? t.push_group_code),
    bridgeInstallationId: u(t.bridgeInstallationId ?? t.bridge_installation_id),
    origin: u(t.origin),
    language: u(t.language),
    section: u(t.section),
    sourceUrl: u(t.sourceUrl ?? t.source_url),
    sourceTitle: u(t.sourceTitle ?? t.source_title),
    apiBaseUrl: u(t.apiBaseUrl ?? t.core_api_base),
    serviceWorkerUrl: u(t.serviceWorkerUrl ?? t.registration_service_worker_url ?? t.service_worker_url),
    serviceWorkerScope: u(t.serviceWorkerScope ?? t.registration_service_worker_scope ?? t.service_worker_scope),
    vapidPublicKey: u(t.vapidPublicKey ?? t.vapid_public_key),
    wpTerms: Array.isArray(t.wpTerms) ? t.wpTerms : Array.isArray(t.wp_terms_json) ? t.wp_terms_json : void 0,
    comments: typeof t.comments == "object" && t.comments !== null ? Ae(t.comments) : void 0
  };
}
function Ae(t) {
  if (!t)
    return;
  const e = t, s = !!(e.enabled ?? e.comments_enabled ?? e.commentsEnabled ?? !1);
  return {
    enabled: s,
    commentsEnabled: s,
    requireLogin: !!(e.requireLogin ?? e.require_login ?? !0),
    allowGuest: !!(e.allowGuest ?? e.allow_guest ?? !1),
    requireModeration: !!(e.requireModeration ?? e.require_moderation ?? !0),
    maxDepth: K(e.maxDepth ?? e.max_depth, 3),
    maxLength: K(e.maxLength ?? e.max_length, 2e3),
    minLength: K(e.minLength ?? e.min_length, 2),
    threadEndpoint: u(e.threadEndpoint ?? e.thread_endpoint),
    commentsEndpoint: u(e.commentsEndpoint ?? e.comments_endpoint),
    postEndpoint: u(e.postEndpoint ?? e.post_endpoint),
    reactionEndpoint: u(e.reactionEndpoint ?? e.reaction_endpoint),
    reportEndpoint: u(e.reportEndpoint ?? e.report_endpoint),
    statusEndpoint: u(e.statusEndpoint ?? e.status_endpoint),
    loginRequiredMessage: u(e.loginRequiredMessage ?? e.login_required_message),
    disabledMessage: u(e.disabledMessage ?? e.disabled_message),
    debugPlaceholder: !!(e.debugPlaceholder ?? e.debug_placeholder ?? !1)
  };
}
function u(t) {
  return typeof t == "string" && t !== "" ? t : void 0;
}
function K(t, e) {
  const s = typeof t == "number" ? t : Number(t);
  return Number.isFinite(s) ? s : e;
}
function it(t) {
  return t.replace(/\/+$/, "");
}
function nt() {
  const t = {}, e = new URLSearchParams(window.location.search);
  for (const [s, r] of e.entries())
    s.startsWith("utm_") && r && (t[s] = r);
  return Object.keys(t).length > 0 ? t : void 0;
}
const V = Ue`
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
var ot = Object.defineProperty, at = Object.getOwnPropertyDescriptor, we = (t, e, s, r) => {
  for (var i = r > 1 ? void 0 : r ? at(e, s) : e, n = t.length - 1, o; n >= 0; n--)
    (o = t[n]) && (i = (r ? o(e, s, i) : o(i)) || i);
  return r && i && ot(e, s, i), i;
};
let W = class extends b {
  constructor() {
    super(...arguments), this.status = "idle";
  }
  connectedCallback() {
    super.connectedCallback(), this.status = g(this) ? "ready" : "missing-config";
  }
  render() {
    const t = g(this);
    if (!t)
      return f`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">Configurazione commenti mancante.</p>
        </section>
      `;
    const e = t.comments;
    return e?.enabled ? e.requireLogin ? (this.status = "login-required", f`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">${e.loginRequiredMessage ?? "Accedi per commentare."}</p>
          <core-login-widget></core-login-widget>
        </section>
      `) : f`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Commenti</h2>
        <p class="copy">I commenti Core saranno caricati per questa pagina.</p>
        <span class="meta">${t.siteCode} · ${t.sourceUrl}</span>
      </section>
    ` : (this.status = "disabled", e?.debugPlaceholder ? f`
        <section class="core-widget" data-status=${this.status}>
          <h2 class="title">Commenti</h2>
          <p class="copy">${e.disabledMessage ?? "I commenti non sono disponibili per questa pagina."}</p>
          <span class="meta">${t.siteCode} · disabled</span>
        </section>
      ` : f``);
  }
};
W.styles = V;
we([
  S()
], W.prototype, "status", 2);
W = we([
  q("core-comments-widget")
], W);
function G(t, e) {
  const s = new URL(`${t.apiBaseUrl}/auth/start`);
  return s.searchParams.set("site_code", t.siteCode), s.searchParams.set("origin", t.origin), s.searchParams.set("mode", e.mode), s.searchParams.set("state", e.state), s.searchParams.set("nonce", e.nonce), s.searchParams.set("return_url", e.returnUrl), s.toString();
}
function ct(t) {
  const e = window.open("about:blank", "star_atlas_core_login", "popup,width=520,height=720"), s = B(), r = B(), i = G(t, {
    mode: "popup",
    state: s,
    nonce: r,
    returnUrl: `${t.origin}/core-auth/callback`
  });
  return e ? (e.location.href = i, "popup") : (window.location.assign(
    G(t, {
      mode: "redirect",
      state: s,
      nonce: r,
      returnUrl: `${t.origin}/core-auth/callback`
    })
  ), "redirect");
}
function lt(t) {
  const e = document.createElement("iframe"), s = B(), r = B();
  return e.hidden = !0, e.title = "Core session check", e.src = G(t, {
    mode: "silent",
    state: s,
    nonce: r,
    returnUrl: window.location.href
  }), e;
}
function ut(t) {
  return new URL(t.apiBaseUrl).origin;
}
function B() {
  const t = new Uint8Array(16);
  return crypto.getRandomValues(t), Array.from(t, (e) => e.toString(16).padStart(2, "0")).join("");
}
var ht = Object.defineProperty, dt = Object.getOwnPropertyDescriptor, re = (t, e, s, r) => {
  for (var i = r > 1 ? void 0 : r ? dt(e, s) : e, n = t.length - 1, o; n >= 0; n--)
    (o = t[n]) && (i = (r ? o(e, s, i) : o(i)) || i);
  return r && i && ht(e, s, i), i;
};
let T = class extends b {
  constructor() {
    super(...arguments), this.status = "idle", this.message = "", this.onAuthMessage = (t) => {
      const e = g(this);
      !e || t.origin !== ut(e) || t.data?.type === "star-atlas-core:auth-complete" && (this.status = "ready", this.message = "Sessione aggiornata", this.dispatchEvent(
        new CustomEvent("core-auth-complete", {
          bubbles: !0,
          composed: !0,
          detail: t.data
        })
      ));
    };
  }
  connectedCallback() {
    super.connectedCallback();
    const t = g(this);
    this.status = t ? "ready" : "missing-config", t && window.addEventListener("message", this.onAuthMessage);
  }
  disconnectedCallback() {
    window.removeEventListener("message", this.onAuthMessage), super.disconnectedCallback();
  }
  render() {
    const t = this.status === "missing-config";
    return f`
      <div class="core-widget compact">
        <button class="secondary" ?disabled=${t} @click=${this.startLogin}>Accedi</button>
      </div>
      ${this.message ? f`<span class="status">${this.message}</span>` : ""}
    `;
  }
  startLogin() {
    const t = g(this);
    if (!t) {
      this.status = "missing-config", this.message = "Configurazione Core mancante";
      return;
    }
    this.status = ct(t) === "popup" ? "working" : "ready", this.message = this.status === "working" ? "Login Core in corso" : "";
  }
  createSilentCheckFrame() {
    const t = g(this);
    return t ? lt(t) : null;
  }
};
T.styles = V;
re([
  S()
], T.prototype, "status", 2);
re([
  S()
], T.prototype, "message", 2);
T = re([
  q("core-login-widget")
], T);
async function Ce(t) {
  if (!("serviceWorker" in navigator))
    throw new Error("service-worker-unsupported");
  return navigator.serviceWorker.register(te(t), {
    scope: se(t),
    updateViaCache: "none"
  });
}
async function Ee(t) {
  if (!("PushManager" in window))
    throw new Error("push-unsupported");
  if (!t.vapidPublicKey)
    throw new Error("missing-vapid-public-key");
  const e = await Ce(t), s = await e.pushManager.getSubscription(), r = s ?? await e.pushManager.subscribe({
    userVisibleOnly: !0,
    applicationServerKey: mt(t.vapidPublicKey)
  }), i = gt(t, r, !!s);
  return await pt(t, i), i;
}
async function pt(t, e) {
  const s = await fetch(`${t.apiBaseUrl}/push/subscriptions`, {
    body: JSON.stringify(e),
    credentials: "omit",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json"
    },
    method: "POST"
  });
  if (!s.ok)
    throw new Error(`push-subscription-failed:${s.status}`);
}
function gt(t, e, s) {
  return {
    site_code: t.siteCode,
    origin: t.origin,
    service_worker_url: te(t),
    service_worker_scope: se(t),
    subscription: e.toJSON(),
    context: tt(t),
    legacy_reconfirmation: s
  };
}
function mt(t) {
  const e = "=".repeat((4 - t.length % 4) % 4), s = `${t}${e}`.replace(/-/g, "+").replace(/_/g, "/"), r = window.atob(s), i = new Uint8Array(r.length);
  for (let n = 0; n < r.length; n += 1)
    i[n] = r.charCodeAt(n);
  return i.buffer.slice(
    i.byteOffset,
    i.byteOffset + i.byteLength
  );
}
var ft = Object.defineProperty, _t = Object.getOwnPropertyDescriptor, ie = (t, e, s, r) => {
  for (var i = r > 1 ? void 0 : r ? _t(e, s) : e, n = t.length - 1, o; n >= 0; n--)
    (o = t[n]) && (i = (r ? o(e, s, i) : o(i)) || i);
  return r && i && ft(e, s, i), i;
};
let M = class extends b {
  constructor() {
    super(...arguments), this.status = "idle", this.message = "";
  }
  connectedCallback() {
    super.connectedCallback();
    const t = g(this);
    this.status = t ? "ready" : "missing-config", t && (!("serviceWorker" in navigator) || !("PushManager" in window)) && (this.status = "unsupported");
  }
  render() {
    const t = g(this), e = this.status === "missing-config" || this.status === "unsupported" || this.status === "working";
    return f`
      <section class="core-widget" data-status=${this.status}>
        <h2 class="title">Notifiche</h2>
        <p class="copy">Ricevi gli aggiornamenti da ${t?.siteCode ?? "Core"}.</p>
        <button ?disabled=${e} @click=${this.subscribe}>Attiva notifiche</button>
        <span class="status">${this.statusText()}</span>
      </section>
    `;
  }
  async subscribe() {
    const t = g(this);
    if (!t) {
      this.status = "missing-config", this.message = "Configurazione Core mancante";
      return;
    }
    if (!t.vapidPublicKey) {
      this.status = "missing-vapid-public-key", this.message = "Chiave pubblica VAPID mancante";
      return;
    }
    this.status = "working", this.message = `Service Worker ${te(t)} · scope ${se(t)}`;
    try {
      const e = await Ee(t);
      this.status = e.legacy_reconfirmation ? "subscribed" : "sent", this.message = e.legacy_reconfirmation ? "Iscrizione riconfermata" : "Iscrizione inviata", this.dispatchEvent(
        new CustomEvent("core-push-subscribed", {
          bubbles: !0,
          composed: !0,
          detail: e
        })
      );
    } catch (e) {
      this.status = "error", this.message = e instanceof Error ? e.message : "Errore push";
    }
  }
  statusText() {
    return this.message ? this.message : this.status === "unsupported" ? "Push non supportato" : this.status === "missing-config" ? "Configurazione Core mancante" : "Pronto";
  }
};
M.styles = V;
ie([
  S()
], M.prototype, "status", 2);
ie([
  S()
], M.prototype, "message", 2);
M = ie([
  q("core-push-widget")
], M);
var $t = Object.defineProperty, bt = Object.getOwnPropertyDescriptor, Se = (t, e, s, r) => {
  for (var i = r > 1 ? void 0 : r ? bt(e, s) : e, n = t.length - 1, o; n >= 0; n--)
    (o = t[n]) && (i = (r ? o(e, s, i) : o(i)) || i);
  return r && i && $t(e, s, i), i;
};
let I = class extends b {
  constructor() {
    super(...arguments), this.status = "idle";
  }
  connectedCallback() {
    super.connectedCallback(), this.status = g(this) ? "configured" : "missing-config";
  }
  render() {
    return f`<span class="status" data-core-status=${this.status}>Core SDK ${this.status}</span>`;
  }
};
I.styles = V;
Se([
  S()
], I.prototype, "status", 2);
I = Se([
  q("star-atlas-core-status")
], I);
const At = {
  getConfig: ve,
  requireConfig: g,
  registerServiceWorker: Ce,
  subscribeToPush: Ee
};
export {
  At as StarAtlasCoreSdk,
  G as buildAuthUrl,
  lt as createSilentCheckFrame,
  ve as getCoreConfig,
  ct as openPopupLogin,
  tt as pushContextFor,
  gt as pushPayloadFor,
  Ce as registerCoreServiceWorker,
  g as requireCoreConfig,
  pt as sendPushSubscription,
  se as serviceWorkerScopeFor,
  te as serviceWorkerUrlFor,
  Ee as subscribeToCorePush
};
