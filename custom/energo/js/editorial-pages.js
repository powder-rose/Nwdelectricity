(function () {
  'use strict';

  function initEditorialPage() {
    var page = document.querySelector('.page-editorial');
    if (!page) { return; }
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var citySection = page.querySelector('.representatives');
    if (citySection) {
      var cityList = citySection.querySelector('.representatives__list');
      var cityToggle = citySection.querySelector('.representatives__spoiler-more');
      if (cityList && cityToggle && cityList.children.length > 18) {
        cityList.id = cityList.id || 'editorial-cities';
        cityToggle.setAttribute('role', 'button');
        cityToggle.setAttribute('aria-controls', cityList.id);
        cityToggle.setAttribute('aria-expanded', 'false');
        citySection.classList.add('representatives--compact');
        cityToggle.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          var expanded = cityToggle.getAttribute('aria-expanded') === 'true';
          cityToggle.setAttribute('aria-expanded', String(!expanded));
          cityToggle.textContent = expanded ? 'Смотреть все города' : 'Свернуть';
          citySection.classList.toggle('representatives--expanded', !expanded);
        }, true);
        cityToggle.addEventListener('keydown', function (event) {
          if (event.key === ' ') { event.preventDefault(); cityToggle.click(); }
        });
      }
    }

    if (!reducedMotion && 'IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.08 });
      Array.prototype.forEach.call(page.querySelectorAll('.about-visual, .single-article__custom-list > li'), function (element) {
        element.classList.add('editorial-reveal');
        observer.observe(element);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditorialPage);
  } else {
    initEditorialPage();
  }
}());
