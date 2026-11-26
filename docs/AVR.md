# Проверка и испытания устройств АВР

Обновляется существующий URL `/services/ispytanie-ustroystv-avr/`.
Публикационные тексты и порядок шести блоков взяты из ТЗ «013».
Предисловие, Wordstat, указания редактору и нормативная справка не публикуются.
ID и URL сохраняются. Создание отсутствующей записи допускается только
локально, с явным bootstrap и `NWD_LOCAL=1`.

Первый экран — специалист у закрытого шкафа АВР с планшетом.
Пять сценариев расположены в сетке вместе с иллюстрацией переключения вводов.
Ситуации для проверки выделены тёмным блоком с тремя иллюстрациями;
этапы — сеткой 2 × 2. Подраздел о протоколе оформлен отдельной вставкой
с иллюстрацией результатов пяти сценариев. FAQ содержит четыре вопроса,
использует обычные шевроны и управляется с клавиатуры.
В мобильной версии цена и кнопка идут перед фото, карточки — одним столбцом.

Общие шапка, меню, подвал, шрифты и фиксированная плашка «Заказать звонок»
сохраняются. Плашка открывает существующую форму `#modal`.
Нативные зависимости Fancybox доступны также на мобильном шаблоне CMS
и при выключенной анимации галереи.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Ожидается `AVR_LOCAL_OK`. Откройте локальную страницу с Ctrl+F5.
Установщик проверяет хеши исходников, создаёт резервные копии, проверяет
PHP, формы, полный рендер CMS и изменение нативной записи страницы.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\avr\publish-github.ps1" -ProjectRoot $project
```

Публикация использует автора владельца, TLS и обычный push без force.
Скрипт печатает закреплённую на опубликованный SHA команду Beget
и сохраняет её в `$env:TEMP\nwd-avr-beget.txt`.
После зелёных GitHub checks выполните её в SSH-консоли Beget.
Результат: `AVR_DEPLOY_OK=<коммит>`.

## Данные, цены и откат

Через действующий rewrite определяется запись site, shop_category или shop.
Обновляются тема, имя, title, description, canonical, индексация и анонс.
Единственная нативная цена товара заменяется на 9900.
Несколько вариантов цены останавливают обновление до записи.
Старые текст, таблица и медиа сохраняются в базе для отката, но не выводятся
новым шаблоном. Цены 121, 234 и 1066 рублей не выводятся новой страницей.
Карточка и SEO получают актуальные данные. Настройка, монтаж и ремонт
не добавляются в состав функциональной проверки.

Проверяются действующие маршруты лаборатории, комплексных испытаний
и технического отчёта, а также существующая ссылка «Испытания устройств АВР»
в группе «Испытания защитной аппаратуры» каталога.
Другие услуги не редактируются. Существующая запись sitemap сохраняется,
отсутствующая добавляется только для текущего URL.

Резервные копии — в `.local/patch-backups` локально и вне document root
на Beget. Публичная проверка сверяет HTML и байты ассетов.
При ошибке восстанавливаются файлы и запись страницы.
Независимая редакторская правка после установки блокирует автоматический
откат этой записи. API, статьи, авторизация и черновики не перезапускаются.

## Заявки и проверки

Режим `avr` использует существующую форму 27. Телефон обязателен;
имя, почта и сведения об объекте необязательны. Согласие изначально не отмечено.
Сервер передаёт название услуги «Испытания устройств АВР» согласно ТЗ;
оно отличается от полного H1 «Проверка и испытания устройств АВР».
Canonical задаёт сервер. Рекламные параметры ограничены разрешённым списком.
Заявка попадает в нативную почтовую очередь и центральный сервис.
Повтор не создаёт дубликат. При ошибке поля и идентификатор запроса
сохраняются, кнопка становится доступна снова.
Событие `avr_price_click` срабатывает при переходе к форме,
`avr_form_submit` — после успешного ответа сервера.

Проверки: PHP 7.3.33 / libxml 2.7.8, точная копия публикации,
нативный рендер и полный desktop/mobile шаблон CMS, форма и ошибки, очереди,
DOM-контракты JavaScript, SEO и цена 9900, публичный валидатор,
миграция трёх типов записей, сохранение редакторских правок и откат.
Изолированные адаптеры заменяют БД, Docker, Git transport и внешние отправки.
Фактическая доставка писем и реальная production БД ими не подтверждаются.

Браузерный просмотр в этой среде недоступен из-за политики доступа.
Визуальная проверка реальной страницы на desktop/mobile остаётся для
локального просмотра перед публикацией. Рендер CMS не заменяет её.
Векторные иллюстрации проверены отдельно.

## Изображения

Фотография создана встроенным генератором в режиме новой генерации.
Финальный файл:
`custom/energo/images/avr/proverka-i-ispytanie-avr.webp`
(1448 × 1086, WebP). ALT соответствует ТЗ.
Декоративные иллюстрации продолжают векторный стиль сайта:
`custom/energo/images/avr/illustrations.svg`.
Изображение переключения показывает выбор одного ввода, а не параллельное
включение источников; это иллюстрация, не монтажная схема.
Протокол на иллюстрации не является реальным документом или результатом
испытаний конкретного объекта.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: landscape 4:3 documentary hero photograph for an automatic transfer switch functional testing service. Primary request: an electrical laboratory specialist inspecting the safe external control panel of an automatic transfer switch cabinet in a clean industrial electrical room. Scene/backdrop: a contemporary neutral grey low-voltage switchgear room, closed matte grey switchgear cabinets, orderly environment, no exposed live copper or wiring. The AVR/ATS cabinet occupies the left side of the image and has a plausible small flush-mounted controller, two discreet source-status indicator groups, a central selector and a few small status lamps; all labels and display content are too small or out of focus to read, with no text overlay. Subject: one middle-aged professional man in dark navy workwear with narrow grey reflective bands, white hard hat and clear safety glasses, positioned comfortably around centre-right, in a natural three-quarter side profile facing left toward the cabinet. He is checking the cabinet indications while holding a plain compact dark diagnostic tablet at waist level with both hands; its screen is angled away and unreadable. Composition: medium-wide waist-up documentary shot with the complete helmet, shoulders, both elbows and hands comfortably within the frame; at least 12 percent clear room space above the helmet and 15 percent to the right of his back, no edge-cropped person. The cabinet and person are a balanced working group with useful physical space between them. This must look different from a worker touching test probes to a distribution board, an outdoor cable tracing scene, a kneeling cable-test operator, and a man writing a report at a desk. Style: restrained premium industrial documentary photography, soft even daylight, realistic materials, natural skin texture, credible hands and control hardware, neutral concrete-grey and navy colours, readable exposure. Avoid logos, readable words or numbers, fake performance readouts, watermarks, high-voltage handling, exposed live parts, sparks, glowing electricity, dramatic darkness, circular metal detectors or a yellow multimeter.
```
