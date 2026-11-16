(function () {
  'use strict';

  function initPriceCatalog() {
    var page = document.querySelector('.page-prices');
    if (!page) { return; }
    var catalog = page.querySelector('.price-catalog');
    if (!catalog) { return; }
    var navigation = catalog.querySelector('.global-cats--price');
    if (!navigation) { return; }
    var tabs = Array.prototype.slice.call(navigation.querySelectorAll('[data-tab]'));
    var panels = tabs.map(function (tab) {
      var target = tab.getAttribute('data-tab');
      return /^#[A-Za-z][A-Za-z0-9_-]*$/.test(target) ? catalog.querySelector(target) : null;
    });
    if (!tabs.length || panels.some(function (panel) { return !panel; })) { return; }

    navigation.setAttribute('role', 'tablist');
    navigation.setAttribute('aria-label', 'Разделы прайс-листа');
    var navigationLayout = window.matchMedia('(min-width: 881px)');
    function updateOrientation() {
      navigation.setAttribute('aria-orientation', navigationLayout.matches ? 'vertical' : 'horizontal');
    }
    updateOrientation();
    if (navigationLayout.addEventListener) { navigationLayout.addEventListener('change', updateOrientation); }
    else { navigationLayout.addListener(updateOrientation); }
    Array.prototype.forEach.call(navigation.children, function (item) {
      item.setAttribute('role', 'presentation');
    });

    function activate(index, moveFocus) {
      tabs.forEach(function (tab, i) {
        var selected = i === index;
        tab.classList.toggle('current', selected);
        tab.setAttribute('aria-selected', String(selected));
        tab.setAttribute('tabindex', selected ? '0' : '-1');
        panels[i].hidden = !selected;
        panels[i].style.removeProperty('display');
        panels[i].style.removeProperty('opacity');
      });
      if (moveFocus) { tabs[index].focus(); }
    }

    tabs.forEach(function (tab, index) {
      var panel = panels[index];
      tab.id = tab.id || 'price-tab-' + (index + 1);
      tab.setAttribute('role', 'tab');
      tab.setAttribute('aria-label', tab.textContent.trim());
      tab.setAttribute('aria-controls', panel.id);
      panel.setAttribute('role', 'tabpanel');
      panel.setAttribute('aria-labelledby', tab.id);
      panel.setAttribute('tabindex', '0');
      var title = document.createElement('h2');
      title.className = 'price-catalog__title';
      title.textContent = tab.textContent.trim();
      panel.insertBefore(title, panel.firstChild);
      Array.prototype.forEach.call(panel.querySelectorAll('table'), function (table) {
        table.setAttribute('role', 'table');
        Array.prototype.forEach.call(table.querySelectorAll('tbody'), function (group) { group.setAttribute('role', 'rowgroup'); });
        Array.prototype.forEach.call(table.querySelectorAll('tr'), function (row) { row.setAttribute('role', 'row'); });
        Array.prototype.forEach.call(table.querySelectorAll('th'), function (cell) { cell.setAttribute('role', 'columnheader'); });
        Array.prototype.forEach.call(table.querySelectorAll('td'), function (cell) { cell.setAttribute('role', 'cell'); });
      });
      tab.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        activate(index, false);
      }, true);
      tab.addEventListener('keydown', function (event) {
        var next = index;
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') { next = (index + 1) % tabs.length; }
        else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') { next = (index + tabs.length - 1) % tabs.length; }
        else if (event.key === 'Home') { next = 0; }
        else if (event.key === 'End') { next = tabs.length - 1; }
        else if (event.key !== ' ') { return; }
        event.preventDefault();
        activate(next, true);
      });
    });
    catalog.classList.add('price-catalog--ready');
    var initial = tabs.findIndex(function (tab) { return tab.getAttribute('data-tab') === window.location.hash; });
    activate(initial < 0 ? 0 : initial, false);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPriceCatalog);
  } else {
    initPriceCatalog();
  }
}());
