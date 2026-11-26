(function () {
  'use strict';
  var form = document.querySelector('[data-fn-form]');
  var failure = 'Не удалось отправить заявку. Попробуйте ещё раз или свяжитесь с нами по телефону, указанному на сайте.';
  var success = 'Спасибо, заявка на проверку фазировки отправлена. Специалист свяжется с вами по указанному телефону.';
  var source = {};
  var allowed = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid', 'gclid'];
  var query = new URLSearchParams(window.location.search);
  // Retain the last tagged landing page for an untagged internal service visit.
  try { source = JSON.parse(sessionStorage.getItem('nwd_service_attribution') || '{}'); } catch (ignored) { source = {}; }
  if (!source || typeof source !== 'object' || Array.isArray(source)) { source = {}; }
  if (allowed.some(function (key) { return query.has(key); })) { source = {}; }
  allowed.forEach(function (key) {
    var value = query.get(key);
    if (value && value.length <= 256 && !/[\u0000-\u001f\u007f]/.test(value)) { source[key] = value; }
  });
  var clean = {};
  allowed.forEach(function (key) { if (typeof source[key] === 'string' && source[key].length <= 256) { clean[key] = source[key]; } });
  if (navigator.globalPrivacyControl || navigator.doNotTrack === '1') { clean = {}; }
  try { if (Object.keys(clean).length) { sessionStorage.setItem('nwd_service_attribution', JSON.stringify(clean)); } } catch (ignored) { /* Storage may be disabled. */ }
  function goal(name) {
    if (typeof window.ym === 'function') {
      window.ym(114341229, 'reachGoal', name, { service: 'Проверка фазировки электрооборудования', url: window.location.origin + window.location.pathname });
    }
  }
  var price = document.querySelector('[data-fn-price]');
  if (price) { price.addEventListener('click', function (event) {
    goal('fazirovka_price_click');
    var target = document.getElementById('raschet-stoimosti');
    if (target) {
      // The legacy mobile-menu handler also intercepts hash links.
      event.preventDefault();
      event.stopPropagation();
      target.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
    }
  }, true); }

  if (!form || !window.diafan_ajax || !window.jQuery) { return; }
  form.elements.nwd_attribution.value = JSON.stringify(clean);
  var previous = window.diafan_ajax.before.feedback_add;
  var previousSuccess = window.diafan_ajax.success.feedback_add;
  var sent = new Set();
  function element(raw) { return raw && raw.jquery ? raw[0] : raw; }
  function message(text, ok) {
    var box = form.querySelector('[data-lead-error]');
    box.className = ok ? 'nwd-lead-success' : 'nwd-lead-error';
    box.setAttribute('role', ok ? 'status' : 'alert');
    box.textContent = text;
  }
  function fieldError(input, text) {
    if (!input) { message(text, false); return; }
    var box = document.getElementById(input.getAttribute('aria-describedby'));
    input.setAttribute('aria-invalid', 'true');
    if (box) { box.textContent = text; box.style.display = 'block'; }
  }
  function clearErrors() {
    form.querySelectorAll('.errors').forEach(function (box) { box.textContent = ''; box.style.display = 'none'; });
    form.querySelectorAll('[aria-invalid]').forEach(function (input) { input.removeAttribute('aria-invalid'); });
    message('', false);
  }
  function validate() {
    clearErrors();
    var phone = form.querySelector('[data-fn-field="phone"]');
    var email = form.querySelector('[data-fn-field="email"]');
    var value = phone.value.trim();
    var digits = value.replace(/\D/g, '');
    if (!value) { fieldError(phone, 'Укажите телефон для связи.'); }
    else if (!/^\+?[0-9 ()\-]+$/.test(value) || digits.length < 10 || digits.length > 15 || value.length > 40) { fieldError(phone, 'Проверьте номер телефона.'); }
    if (email.value.trim() && !email.validity.valid) { fieldError(email, 'Проверьте адрес электронной почты.'); }
    if (!form.elements.nwd_consent.checked) { fieldError(form.elements.nwd_consent, 'Подтвердите согласие на обработку персональных данных.'); }
    var invalid = form.querySelector('[aria-invalid="true"]');
    if (invalid) { invalid.focus(); return false; }
    return true;
  }
  window.diafan_ajax.before.feedback_add = function (raw) {
    if (element(raw) === form && !validate()) { return false; }
    // The common handler requires a trusted submit-button activation and blocks duplicates/autofill.
    return previous ? previous(raw) : false;
  };
  window.diafan_ajax.success.feedback_add = function (raw, response) {
    if (element(raw) !== form) { return previousSuccess ? previousSuccess(raw, response) : undefined; }
    clearErrors();
    if (response && response.result === 'success') {
      var request = form.elements.nwd_request_id.value;
      if (!sent.has(request)) { goal('fazirovka_form_submit'); sent.add(request); }
      form.reset();
      var bytes = new Uint8Array(16);window.crypto.getRandomValues(bytes);
      form.elements.nwd_request_id.value = Array.from(bytes, function (n) { return ('0' + n.toString(16)).slice(-2); }).join('');
      form.elements.nwd_attribution.value = JSON.stringify(clean);
      message(success, true);
    } else {
      var errors = response && response.errors;
      if (errors && Object.keys(errors).length) {
        Object.keys(errors).forEach(function (key) { fieldError(key === '0' ? null : form.elements.namedItem(key), errors[key]); });
        var invalid = form.querySelector('[aria-invalid="true"]');if (invalid) { invalid.focus(); }
      } else { message(failure, false); }
    }
    // Keep this inline form present; native handling and pop-up acknowledgement apply to other forms.
    return false;
  };
  window.jQuery(document).on('order_ajax_submit.error', function (event, raw) {
    if (element(raw) === form) { message(failure, false); }
  });
}());
