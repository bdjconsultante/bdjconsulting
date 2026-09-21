/* ==========================================================
   BDJ Consulting — Pixels publicitaires (chargés APRÈS consentement)
   ==========================================================
   1) Renseignez les identifiants ci-dessous.
      Laisser une chaîne vide ('') désactive ce pixel.
   2) Rien n'est chargé tant que le visiteur n'a pas cliqué « Accepter ».
   ========================================================== */
(function () {
  'use strict';

  var IDS = {
    meta:     '',   // Pixel Meta (Facebook/Instagram) — ex. '1234567890123456'
    tiktok:   '',   // Pixel TikTok — ex. 'C1A2B3D4E5F6G7H8'
    linkedin: '',   // LinkedIn Insight Tag (Partner ID) — ex. '1234567'
    x:        '',   // Pixel X (Twitter) — ex. 'o1abcd'
    ga4:      '',   // Google Analytics 4 — ex. 'G-XXXXXXXXXX'
    gads:     ''    // Google Ads — ex. 'AW-XXXXXXXXX'
  };

  var CHOICE_KEY = 'bdj_cookie_choice_v2';

  function accepted() {
    try { return localStorage.getItem(CHOICE_KEY) === 'accepted'; } catch (e) { return false; }
  }

  function inject(src) {
    var s = document.createElement('script');
    s.async = true;
    s.src = src;
    document.head.appendChild(s);
    return s;
  }

  function loadGoogle() {
    if (!IDS.ga4 && !IDS.gads) return;
    if (window.__bdjGoogle) return;
    window.__bdjGoogle = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    inject('https://www.googletagmanager.com/gtag/js?id=' + (IDS.ga4 || IDS.gads));
    if (IDS.ga4) window.gtag('config', IDS.ga4);
    if (IDS.gads) window.gtag('config', IDS.gads);
  }

  function loadMeta() {
    if (!IDS.meta || window.__bdjMeta) return;
    window.__bdjMeta = true;
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return;
      n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
      if (!f._fbq) f._fbq = n;
      n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
      t = b.createElement(e); t.async = !0; t.src = v;
      s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    window.fbq('init', IDS.meta);
    window.fbq('track', 'PageView');
  }

  function loadTiktok() {
    if (!IDS.tiktok || window.__bdjTiktok) return;
    window.__bdjTiktok = true;
    !function (w, d, t) {
      w.TiktokAnalyticsObject = t;
      var ttq = w[t] = w[t] || [];
      ttq.methods = ['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'];
      ttq.setAndDefer = function (o, e) { o[e] = function () { o.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; };
      for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
      ttq.instance = function (o) { var e = ttq._i[o] || []; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(e, ttq.methods[i]); return e; };
      ttq.load = function (e, n) {
        var url = 'https://analytics.tiktok.com/i18n/pixel/events.js';
        ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = url;
        ttq._t = ttq._t || {}; ttq._t[e] = +new Date;
        ttq._o = ttq._o || {}; ttq._o[e] = n || {};
        var s = d.createElement('script'); s.type = 'text/javascript'; s.async = !0;
        s.src = url + '?sdkid=' + e + '&lib=' + t;
        var a = d.getElementsByTagName('script')[0]; a.parentNode.insertBefore(s, a);
      };
    }(window, document, 'ttq');
    window.ttq.load(IDS.tiktok);
    window.ttq.page();
  }

  function loadLinkedIn() {
    if (!IDS.linkedin || window.__bdjLinkedIn) return;
    window.__bdjLinkedIn = true;
    window._linkedin_partner_id = IDS.linkedin;
    window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
    window._linkedin_data_partner_ids.push(IDS.linkedin);
    inject('https://snap.licdn.com/li.lms-analytics/insight.min.js');
  }

  function loadX() {
    if (!IDS.x || window.__bdjX) return;
    window.__bdjX = true;
    !function (e, t, n) {
      e.twq || (e.twq = function () { e.twq.exe ? e.twq.exe.apply(e.twq, arguments) : e.twq.queue.push(arguments); },
        e.twq.version = '1.1', e.twq.queue = []);
      var s = t.createElement(n); s.async = !0; s.src = 'https://static.ads-twitter.com/uwt.js';
      var a = t.getElementsByTagName(n)[0]; a.parentNode.insertBefore(s, a);
    }(window, document, 'script');
    window.twq('config', IDS.x);
  }

  function loadAll() {
    loadMeta();
    loadTiktok();
    loadLinkedIn();
    loadX();
    loadGoogle();
  }

  /* Fire a conversion event across whichever pixels are loaded.
     No-op if the visitor refused consent (no trackers present). */
  function track(name, params) {
    params = params || {};
    try { if (typeof window.gtag === 'function') window.gtag('event', name, params); } catch (e) {}
    try { if (typeof window.fbq === 'function') window.fbq('track', name === 'generate_lead' ? 'Lead' : name, params); } catch (e) {}
    try { if (window.ttq && typeof window.ttq.track === 'function') window.ttq.track(name === 'generate_lead' ? 'Contact' : name, params); } catch (e) {}
    try { if (typeof window.twq === 'function') window.twq('event', name, params); } catch (e) {}
  }

  window.BDJTracking = { load: loadAll, ids: IDS, track: track };

  if (accepted()) loadAll();
})();
