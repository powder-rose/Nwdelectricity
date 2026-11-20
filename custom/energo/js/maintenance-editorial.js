(function () {
  'use strict';
  function init() {
    var page = document.querySelector('.page-maintenance');
    if (!page || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) { return; }
    // Content is visible by default, including without JavaScript or if the observer fails.
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) { return; }
        entry.target.classList.add('maintenance-reveal', 'is-entering');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08 });
    Array.prototype.forEach.call(page.querySelectorAll('[data-maintenance-reveal]'), function (section) { observer.observe(section); });
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
}());
