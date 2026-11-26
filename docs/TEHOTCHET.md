# Технический отчёт по электроизмерениям

Обновляется существующая страница
`/services/tekhnicheskiy-otchet-po-elektroizmereniyam/`.
Тексты публикации, шесть блоков, пять вопросов FAQ, форма и SEO соответствуют
ТЗ «009». Указания редактору, Wordstat и нормативная справка не публикуются.

Первый экран показывает Николая Бойкова за оформлением результатов измерений.
Внешность основана на действующем портрете с сайта; сцена с документами создана
генератором изображений. Изображение не содержит читаемых данных заказчиков.
Состав отчёта оформлен иллюстрацией папки и четырьмя сведениями, протоколы —
отдельным тёмным подразделом. Три ситуации показаны иллюстрированными блоками,
четыре этапа — на тёмном фоне. Общие шапка, меню, подвал и шрифты сохраняются.
Плашка «Заказать звонок» открывает существующее окно `#modal`.
Цена отдельного отчёта отсутствует: оформление входит в стоимость испытаний.
На телефоне коммерческая строка и CTA расположены перед фотографией.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `TEHOTCHET_LOCAL_OK`. Откройте локальный URL страницы с Ctrl+F5.
Установщик проверяет версии файлов, сохраняет копии, запускает проверки PHP,
форм, рендера и изменения нативной записи. Bootstrap отсутствующей записи
разрешён только в локальном режиме `NWD_LOCAL=1`.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\tehotchet\publish-github.ps1" -ProjectRoot $project
```

После зелёных GitHub checks выполнить напечатанную команду в SSH-консоли Beget.
Она закреплена на опубликованный коммит. Результат: `TEHOTCHET_DEPLOY_OK=<коммит>`.
Команда также сохраняется в `$env:TEMP\nwd-tehotchet-beget.txt`.

## Данные и восстановление

Через действующий rewrite определяется запись site, shop_category или shop.
URL и ID сохраняются. Обновляются тема, имя, title, description, canonical,
индексация и анонс. Основной старый текст и медиа сохраняются в базе для отката,
но не выводятся новым шаблоном. Нативные числовые цены не меняются и не создаются.
На странице нет таблицы старых расценок; Service не содержит offers или price.

Существующая запись sitemap сохраняется; недостающая добавляется только для
этого маршрута. Проверяются внутренние маршруты лаборатории и комплексных
испытаний и действующая обратная ссылка в группе
«Комплексные испытания и документация» каталога лаборатории.

Резервная копия Beget находится вне document root, локальная — в
`.local/patch-backups`. При ошибке восстанавливаются данные страницы и файлы.
Независимая правка записи редактором после установки блокирует её автоматический
откат. API, статьи, авторизация и черновики не перезапускаются и не изменяются.

## Заявки и проверка

Режим `tehotchet` использует действующую форму 27. Обязателен телефон; имя,
почта и сведения об объекте необязательны. Согласие изначально не отмечено.
Сервер передаёт название «Электроизмерения и испытания с техническим отчётом»
и canonical страницы в уведомления и центральный сервис. Рекламные параметры
ограничены действующим списком. Повтор не создаёт дубликат; при ошибке введённые
данные сохраняются и кнопка снова доступна. Событие `tehotchet_form_submit`
вызывается после успеха сервера, `tehotchet_calc_click` — при переходе к форме.

Проверки охватывают нативный рендер CMS и мобильный шаблон, libxml 2.7.8,
сохранение заявки и очередей, SEO без числовой цены, внутренние ссылки,
миграцию трёх типов записи, сохранение редакторских правок и откат.
Изолированные тесты не отправляют реальные письма; фактическая доставка
проверяется согласованной тестовой заявкой после публикации.

## Изображения

Финальный файл:
`custom/energo/images/tehotchet/tekhnicheskiy-otchet-po-elektroizmereniyam.webp`
(1448 × 1086, WebP). Режим — встроенная генерация, затем редактирование с
сохранением внешности по референсу
`custom/energo/images/blog/nikolai-boykov-blog.webp`.
Декоративные SVG: `custom/energo/images/tehotchet/illustrations.svg`.

Запрос исходного кадра:

```text
Use case: photorealistic-natural. Asset type: landscape 4:3 premium documentary photograph for an established electrical testing laboratory service website, at least 1448x1086. Primary request: an electrical laboratory specialist preparing a technical report from completed electrical measurement results, distinctly different from a technician standing at a switchboard, kneeling with cable equipment, or testing breakers. Scene: a quiet practical electrical laboratory office with a clean neutral grey worktable near a soft daylight window, subtle shelves of technical binders blurred in the background. Subject: a middle-aged male electrical laboratory specialist in plain dark navy workwear, seated comfortably near the middle of the frame, three-quarter view from slightly above the table, attentively writing a result with a pen in a paper protocol. On the desk: a modest tidy stack of technical papers with unrecognizable soft grey table lines, a closed slim report folder, and a compact grey and muted-blue professional electrical installation test instrument with its leads neatly coiled, clearly secondary to the reporting work. Hands anatomically correct, pen held naturally, no electrical testing activity taking place. Composition: the specialist, hands, papers and instrument form one balanced group; show comfortable real room and tabletop breathing space around the person, particularly to the left of his silhouette, never press his back or shoulder to an edge. The report and the working hands are the visual focus; camera is at a modest elevated three-quarter angle, no posed gaze at camera. Natural skin, soft window light, matte paper and polymer textures, crisp believable detail, restrained graphite, warm stone-grey and navy palette consistent with a premium industrial website. Keep the scene well lit and readable, no moody colour cast. Absolutely no readable text, numbers, signatures, stamps, real client or object information, logos, branding, watermark, captions, glow or sparks. No yellow handheld multimeter, helmet on the desk, cartoon or CGI look.
```

Запрос замены персонажа на Николая Бойкова:

```text
Use case: identity-preserve. Image 1 is the edit target: the laboratory report preparation photograph. Image 2 is the identity reference: Nikolai Boykov's portrait used on the existing website. Replace ONLY the man in Image 1 with Nikolai Boykov from Image 2, preserving his recognizable light blue eyes, short side-swept light brown/blond hair, light ginger-blond beard and moustache, facial proportions, age and natural skin texture. He is looking down at the protocol as he writes, a natural three-quarter seated working pose, not looking at camera. Keep the same dark navy laboratory workwear from the target; do not copy the suit or portrait background from the reference. Preserve the entire original laboratory office, daylight window, grey desk, papers without legible text, closed navy report folder and grey-blue measuring instrument with coiled leads, as well as the composition, body position, room margins, hands operating the pen, realistic neutral lighting, and documentary photographic style. Keep Nikolai comfortably away from the left image edge with generous room space. Change the man's identity only and make his expression focused and calm. No added captions, logos, watermarks, readable client data, signatures or stamps. Landscape 4:3, high quality at least 1448x1086.
```
