(function () {
  'use strict';
  function init() {
    var directory = document.querySelector('.page-services .service-directory');
    if (!directory) { return; }
    var tools = directory.querySelector('[data-service-tools]');
    var input = directory.querySelector('#service-directory-search');
    var status = directory.querySelector('[data-service-status]');
    var empty = directory.querySelector('[data-service-empty]');
    var clear = directory.querySelector('[data-service-clear]');
    var tabs = directory.querySelector('[role="tablist"]');
    var buttons = Array.prototype.slice.call(directory.querySelectorAll('[data-service-tab]'));
    var groups = Array.prototype.slice.call(directory.querySelectorAll('[data-service-group]')).map(function (group) {
      return { element:group, id:group.getAttribute('data-service-group'), count:group.querySelector('[data-service-group-count]'), items:Array.prototype.slice.call(group.querySelectorAll('.service-directory-list>li')).map(function (item) { return { element:item, text:normalize(item.textContent) }; }) };
    });
    if (!tools || !input || !status || !empty || !clear || !tabs || !groups.length || !buttons.length) { return; }
    var active = groups[0].id;
    groups.forEach(function (group) {
      group.element.setAttribute('role','tabpanel');
      group.element.setAttribute('aria-labelledby',group.id+'-tab');
      if (location.hash==='#'+group.id) { active=group.id; }
    });
    function normalize(value) { return value.toLowerCase().replace(/ё/g,'е').replace(/\s+/g,' ').trim(); }
    function term(value) {
      if (value.length<5 || !/^[а-я]+$/.test(value)) { return value; }
      return value.replace(/(?:ениями|аниями|ением|анием|ениях|аниях|ениям|аниям|ение|ения|ению|ении|ений|ание|ания|анию|ании|аний|ами|ями|ого|ому|ов|ам|ям|ах|ях|ом|ем|ой|ая|ые|ое|ую|ий|ый|ей|ия|ие|ии|ию|и|ы|а|я|е|у|о|ь)$/,'');
    }
    function plural(count) { var last=count%10,teen=count%100; return count+' '+(last===1&&teen!==11?'услуга':last>=2&&last<=4&&(teen<12||teen>14)?'услуги':'услуг'); }
    function update() {
      var query=normalize(input.value), terms=query?query.split(' ').map(term):[], total=0;
      groups.forEach(function (group) {
        var allowed=active===group.id, count=0;
        group.items.forEach(function (item) {
          var matches=allowed&&terms.every(function (word) { return item.text.indexOf(word)!==-1; });
          item.element.hidden=!matches;
          if(matches){count++;}
        });
        group.element.hidden=!allowed;
        group.count.textContent=plural(count); total+=count;
      });
      buttons.forEach(function (button) {
        var selected=button.getAttribute('data-service-tab')===active;
        button.setAttribute('aria-selected',String(selected));
        button.setAttribute('tabindex',selected?'0':'-1');
      });
      status.textContent=(query?'Найдено: ':'')+plural(total);
      clear.hidden=!input.value;
      empty.hidden=total!==0;
    }
    function select(button) { active=button.getAttribute('data-service-tab'); input.value=''; update(); }
    function orientation() { tabs.setAttribute('aria-orientation',window.matchMedia('(max-width: 540px)').matches?'vertical':'horizontal'); }
    buttons.forEach(function (button,index) {
      button.addEventListener('click',function () { select(button); });
      button.addEventListener('keydown',function (event) {
        if (event.altKey || event.ctrlKey || event.metaKey) { return; }
        var vertical=tabs.getAttribute('aria-orientation')==='vertical', next=index;
        if (event.key==='Home') { next=0; }
        else if (event.key==='End') { next=buttons.length-1; }
        else if (event.key===(vertical?'ArrowDown':'ArrowRight')) { next=(index+1)%buttons.length; }
        else if (event.key===(vertical?'ArrowUp':'ArrowLeft')) { next=(index+buttons.length-1)%buttons.length; }
        else { return; }
        event.preventDefault(); select(buttons[next]); buttons[next].focus();
      });
    });
    input.addEventListener('input',update);
    input.form.addEventListener('submit',function (event) { event.preventDefault(); update(); });
    clear.addEventListener('click',function () { input.value=''; update(); input.focus(); });
    directory.querySelector('[data-service-reset]').addEventListener('click',function () { input.value='';update();input.focus(); });
    orientation();
    window.addEventListener('resize',orientation);
    tools.hidden=false;
    directory.classList.add('service-directory--enhanced');
    update();
  }
  if (document.readyState==='loading') { document.addEventListener('DOMContentLoaded',init); } else { init(); }
}());
