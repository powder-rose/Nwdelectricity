# Приёмо-сдаточные и эксплуатационные испытания

Обновляется существующая страница
`/services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya/`.
Текст публикации, шесть блоков, пять вопросов FAQ, форма и SEO соответствуют
ТЗ «008». Указания редактору, Wordstat и нормативная справка не публикуются.

Первый экран содержит новый кадр специалиста у распределительного щита.
Два сценария испытаний показаны равными карточками; состав программы —
иллюстрацией щита и семью проверками. Четыре этапа собраны в сетку 2 × 2,
технический отчёт выделен тёмным блоком. Сохраняются общие шапка, меню,
подвал и шрифты. FAQ использует доступные нативные details со стрелками.
Плашка «Заказать звонок» открывает существующее окно `#modal`.
На телефоне цена и CTA идут перед фото. На планшете первый экран перестраивается.
Переход к форме обрабатывается локально, чтобы старый обработчик мобильного меню
не перехватывал якорь. При отключённом JS сохраняется обычная ссылка-якорь.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `COMMISSIONING_LOCAL_OK`. Откройте локальный URL страницы с Ctrl+F5.
Установщик проверяет версии файлов, сохраняет копии, запускает проверки PHP,
форм, рендера и изменения нативной записи. Bootstrap отсутствующей записи
разрешён только в локальном режиме `NWD_LOCAL=1`.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\commissioning\publish-github.ps1" -ProjectRoot $project
```

После зелёных GitHub checks выполнить напечатанную команду в SSH-консоли Beget.
Она закреплена на опубликованный коммит. Результат: `COMMISSIONING_DEPLOY_OK=<коммит>`.

## Данные и восстановление

Через действующий rewrite определяется запись site, shop_category или shop.
URL и ID сохраняются. Обновляются тема, имя, title, description, canonical,
индексация, анонс и единственная цена товара до 9900. Несколько вариантов цены
останавливают обновление до записи. Старые основной текст и медиа остаются
в базе для восстановления, но не выводятся новым шаблоном.

Существующая запись sitemap сохраняется; недостающая добавляется только для
этого маршрута. Проверяются семь внутренних маршрутов и действующая обратная
ссылка в группе «Комплексные испытания и документация» каталога лаборатории.

Резервная копия Beget находится вне document root, локальная — в
`.local/patch-backups`. При ошибке восстанавливаются данные страницы и файлы.
Последующая независимая правка записи редактором блокирует её автоматический
откат. API, статьи, авторизация и черновики не перезапускаются и не изменяются.

## Заявки и проверка

Режим `commissioning` использует действующую форму 27. Обязателен телефон;
имя, почта и данные объекта необязательны. Согласие изначально не отмечено.
Название услуги и canonical задаёт сервер. Рекламные параметры ограничены
действующим списком. Поля, услуга и URL передаются в нативную почтовую очередь
и центральный сервис. Повтор не создаёт дубликат; при ошибке данные сохраняются
и отправка доступна снова. Событие `ispytaniya_elektroustanovok_form_submit`
вызывается после успеха сервера, `ispytaniya_elektroustanovok_price_click` —
при переходе к форме.

Проверки охватывают нативный рендер CMS и мобильный шаблон, libxml 2.7.8,
сохранение заявки и очередей, SEO, ссылки, цену, миграцию трёх типов записи,
сохранение редакторских правок и откат. Изолированные тесты не отправляют
реальных писем; фактическая доставка проверяется согласованной тестовой
заявкой после публикации.

## Изображения

Фотография создана встроенным генератором изображений в режиме генерации.
Финальный файл:
`custom/energo/images/commissioning/priemo-sdatochnye-i-ekspluatatsionnye-ispytaniya.webp`
(1448 × 1086, WebP). Декоративные иллюстрации продолжают SVG-стиль сайта:
`custom/energo/images/commissioning/illustrations.svg`.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset: premium documentary industrial photograph for a commercial electrical testing service webpage, landscape 4:3, at least 1448x1086. Subject: an electrical laboratory specialist with a measuring instrument beside an industrial distribution switchboard, distinctly different from a kneeling technician at floor-standing cable test equipment or a circuit-breaker workbench. A middle-aged technician in plain dark navy workwear, white hard hat and clear safety glasses stands comfortably in the middle-left of a spacious modern electrical room, three-quarter view. His complete helmet, shoulders, both arms and legs are comfortably inside the frame; keep 18–22 percent of real room space to the left of his entire silhouette. The technician occupies roughly 30–55 percent of the image width, rather than touching an edge. He is reading and adjusting a compact professional grey-and-blue electrical installation test instrument resting on a waist-height dark service trolley in the central foreground. Its test leads are neatly routed to protected test points of the large grey distribution board on the right; the board shows orderly breaker rows and cable organization behind safe internal covers, no exposed live conductor being touched. The technician looks down at the measuring instrument, one hand gently operating a control, the other resting on the trolley. Quiet controlled industrial inspection scene. Camera at torso level, medium-wide composition with the person, instrument and panel forming a balanced group and generous believable surroundings. Crisp real materials, matte polymer, painted metal, soft window light, restrained graphite/stone-grey/navy palette, natural skin and realistic proportions. No yellow handheld multimeter, no cartoon or CGI look, no sparks, no glow. Absolutely no text, readable numbers, logos, branding, watermark or captions.
```
