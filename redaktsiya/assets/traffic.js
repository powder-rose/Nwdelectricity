(function () {
  'use strict';

  var script = document.currentScript;
  if (!script || !window.crypto || !window.fetch || window.top !== window.self) return;
  if (navigator.globalPrivacyControl || navigator.doNotTrack === '1') return;
  var page = script.getAttribute('data-page');
  if (!page) return;

  // Preserve tagged landing attribution for the service form after internal navigation.
  var attributionKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid', 'gclid'];
  var landing = new URLSearchParams(location.search);
  var attribution = {};
  attributionKeys.forEach(function (key) {
    var value = landing.get(key);
    if (value && value.length <= 256 && !/[\u0000-\u001f\u007f]/.test(value)) { attribution[key] = value; }
  });
  if (Object.keys(attribution).length) {
    try { sessionStorage.setItem('nwd_service_attribution', JSON.stringify(attribution)); } catch (unavailable) {}
  }

  function randomId() {
    var bytes = crypto.getRandomValues(new Uint8Array(16));
    return Array.from(bytes, function (byte) { return ('0' + byte.toString(16)).slice(-2); }).join('');
  }

  var memory;
  function sessionId() {
    var now = Date.now();
    var state = memory;
    var storage;
    try {
      storage = window.localStorage;
      state = JSON.parse(storage.getItem('nwd.traffic.v1')) || state;
    } catch (ignore) {
      try {
        storage = window.sessionStorage;
        state = JSON.parse(storage.getItem('nwd.traffic.v1')) || state;
      } catch (unavailable) {}
    }
    if (!state || !/^[0-9a-f]{32}$/.test(state.id) || !(now - state.at >= 0 && now - state.at < 1800000)) {
      state = { id: randomId() };
    }
    state.at = now;
    memory = state;
    try { storage.setItem('nwd.traffic.v1', JSON.stringify(state)); } catch (ignore) {}
    return state.id;
  }

  function source() {
    var utm = new URLSearchParams(location.search).get('utm_source') || '';
    if (/^[a-z0-9а-яё_-]{1,64}$/i.test(utm)) return 'utm:' + utm.toLowerCase();
    if (!document.referrer) return 'direct';
    try {
      var host = new URL(document.referrer).hostname.toLowerCase().replace(/^www\./, '');
      return host === location.hostname.toLowerCase().replace(/^www\./, '') ? 'internal' : 'ref:' + host;
    } catch (ignore) { return 'direct'; }
  }

  function sendView(session) {
    var data = JSON.stringify({ event: randomId(), session: session, page: page, source: source() });
    function send() {
      return fetch('/redaktsiya/traffic.php', {
        method: 'POST', mode: 'same-origin', credentials: 'omit', referrerPolicy: 'no-referrer',
        keepalive: true, headers: { 'Content-Type': 'application/json' }, body: data
      });
    }
    function retry() { setTimeout(function () { send().catch(function () {}); }, 900); }
    send().then(function (response) { if (response.status >= 500) retry(); }, retry);
  }

  function record() {
    // Two newly opened tabs share their first session, even if neither has written it yet.
    if (navigator.locks && navigator.locks.request) {
      navigator.locks.request('nwd.traffic.v1', sessionId).then(sendView, function () { sendView(sessionId()); });
    } else {
      sendView(sessionId());
    }
  }

  // A prerender counts when the visitor actually opens it. History restores are new page views.
  if (document.prerendering) {
    document.addEventListener('prerenderingchange', record, { once: true });
  } else {
    record();
  }
  window.addEventListener('pageshow', function (event) { if (event.persisted) record(); });
}());
