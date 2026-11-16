(function () {
  'use strict';

  function initServiceCallback() {
    var page = document.querySelector('.page-responsible');
    if (!page || !('IntersectionObserver' in window)) { return; }
    var callback = page.querySelector('.home-callback');
    var form = page.querySelector('.find-cost__form');
    if (!callback || !form) { return; }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        callback.classList.toggle('home-callback--paused', entry.isIntersecting);
      });
    }, { threshold: 0 });
    observer.observe(form);
  }

  function initPage() {
    initServiceCallback();
    if (!('IntersectionObserver' in window) ||
        !window.matchMedia('(prefers-reduced-motion: no-preference)').matches) { return; }
    var sections = document.querySelectorAll('.page-responsible .service-scope, .page-responsible .service-promise');
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in-view');
          observer.unobserve(entry.target);
        }
      });
    });
    sections.forEach(function (section) { observer.observe(section); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPage);
  } else {
    initPage();
  }
}());
