(function () {
  'use strict';

  function initHomeEnhancements() {
    var serviceItems = document.querySelectorAll('.page-home .group-content--block .custom-list__item');
    if ('IntersectionObserver' in window && serviceItems.length) {
      var activeService = null;
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            if (activeService) { activeService.classList.remove('is-current'); }
            activeService = entry.target;
            activeService.classList.add('is-current');
          } else if (activeService === entry.target) {
            activeService.classList.remove('is-current');
            activeService = null;
          }
        });
      }, { rootMargin: '-35% 0px -50% 0px', threshold: 0 });
      Array.prototype.forEach.call(serviceItems, function (item) { observer.observe(item); });
    }

    var citySection = document.querySelector('.page-home .representatives');
    if (citySection) {
      var cityList = citySection.querySelector('.representatives__list');
      var cityToggle = citySection.querySelector('.representatives__spoiler-more');
      if (cityList && cityToggle && cityList.children.length > 18) {
        cityList.id = cityList.id || 'home-cities';
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

    var clients = document.querySelector('.page-home .clients');
    var list = clients && clients.querySelector('.clients__list');
    if (list) {
      list.setAttribute('tabindex', '0');
      list.setAttribute('aria-label', 'Логотипы клиентов');
    }
    if (list && list.children.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      Array.prototype.slice.call(list.children).forEach(function (item) {
        var copy = item.cloneNode(true);
        copy.setAttribute('aria-hidden', 'true');
        list.appendChild(copy);
      });
      clients.classList.add('clients--marquee');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomeEnhancements);
  } else {
    initHomeEnhancements();
  }
}());
