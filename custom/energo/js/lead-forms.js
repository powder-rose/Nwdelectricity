(function () {
  'use strict';
  var attempts = new WeakMap();
  var active = new WeakSet();

  function isLeadForm(form) {
    return form && form.tagName === 'FORM' &&
      form.querySelector('input[name="module"][value="feedback"]') &&
      form.querySelector('input[name="action"][value="add"]');
  }

  function newRequest(form) {
    var input = form.querySelector('[name="nwd_request_id"]');
    if (!input) { return; }
    var bytes = new Uint8Array(16);
    window.crypto.getRandomValues(bytes);
    input.value = Array.prototype.map.call(bytes, function (n) {
      return ('0' + n.toString(16)).slice(-2);
    }).join('');
  }

  function feedbackMessage(form, message, success) {
    var box = form.querySelector('[data-lead-error]');
    if (!box) {
      box = document.createElement('p');
            box.setAttribute('data-lead-error', '');
      form.appendChild(box);
    }
    box.className = success ? 'nwd-lead-success' : 'nwd-lead-error';
    box.setAttribute('role', success ? 'status' : 'alert');
    box.textContent = message;
  }

  function unlock(form) {
    if (!isLeadForm(form)) { return; }
    active.delete(form);
    form.removeAttribute('aria-busy');
    form.querySelectorAll('[data-lead-disabled]').forEach(function (button) {
      button.disabled = false;
      button.removeAttribute('data-lead-disabled');
    });
  }

  document.querySelectorAll('form').forEach(function (form) {
    if (isLeadForm(form)) { newRequest(form); }
  });

  // Pointer, touch and keyboard activation of the submit button all produce a trusted click.
  document.addEventListener('click', function (event) {
    var button = event.target.closest('button, input[type="submit"], input[type="image"]');
    if (!button || !isLeadForm(button.form) || button.type !== 'submit') { return; }
    var form = button.form;
    if (!event.isTrusted || active.has(form)) {
      event.preventDefault();
      event.stopImmediatePropagation();
      return;
    }
    if (!form.querySelector('[name="nwd_request_id"]').value) { newRequest(form); }
    var attempt = {};
    attempts.set(form, attempt);
    window.setTimeout(function () {
      if (attempts.get(form) === attempt) { attempts.delete(form); }
    }, 0);
  }, true);

  // Enter used to accept a browser autocomplete suggestion must never send a lead.
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && event.target.tagName === 'INPUT' &&
        !/^(submit|button|checkbox|radio)$/.test(event.target.type) && isLeadForm(event.target.form)) {
      event.preventDefault();
    }
  }, true);

  document.addEventListener('submit', function (event) {
    if (!isLeadForm(event.target)) { return; }
    if (!attempts.has(event.target)) {
      event.preventDefault();
      event.stopImmediatePropagation();
    }
  }, true);

  // This also guards jQuery-triggered submit events, which bypass native capture listeners.
  var previous = window.diafan_ajax.before.feedback_add;
  window.diafan_ajax.before.feedback_add = function (form) {
    form = form.jquery ? form[0] : form;
    if (!attempts.has(form) || active.has(form)) { return false; }
    attempts.delete(form);
    var consent = form.querySelector('[name="nwd_consent"]');
    if (!consent || !consent.checked) {
      feedbackMessage(form, 'Подтвердите согласие на обработку персональных данных.');
      if (consent) { consent.focus(); }
      return false;
    }
    if (!form.reportValidity()) { return false; }
    if (previous && previous(form) === false) { return false; }
    feedbackMessage(form, '');
    active.add(form);
    form.setAttribute('aria-busy', 'true');
    form.querySelectorAll('button[type="submit"],input[type="submit"]').forEach(function (button) {
      if (!button.disabled) {
        button.disabled = true;
        button.setAttribute('data-lead-disabled', '');
      }
    });
  };

  var onSuccess = window.diafan_ajax.success.feedback_add;
  window.diafan_ajax.success.feedback_add = function (form, response) {
    if (response.result === 'success') {
      feedbackMessage(form.jquery ? form[0] : form, 'Заявка отправлена. Мы свяжемся с вами в ближайшее время.', true);
      // DIAFAN clears the inputs after its success callback returns.
      window.setTimeout(function () { newRequest(form.jquery ? form[0] : form); }, 0);
    }
    if (onSuccess && window.jQuery.fancybox) { return onSuccess(form, response); }
  };

  window.jQuery(document).on('order_ajax_submit.after_last order_ajax_submit.error', function (event, form) {
    unlock(form && form.jquery ? form[0] : form);
    if (event.type === 'order_ajax_submit' && event.namespace === 'error' && isLeadForm(form && form.jquery ? form[0] : form)) {
      feedbackMessage(form.jquery ? form[0] : form, 'Не удалось получить ответ сервера. Попробуйте ещё раз.');
    }
  });
}());
