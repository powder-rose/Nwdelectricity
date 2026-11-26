# Испытания силовых кабельных линий

Обновляется существующая страница
`/services/ispytanie-silovykh-kabelnykh-liniy-napryazheniem/`.
Текст публикации, шесть блоков, пять вопросов FAQ, форма и SEO соответствуют ТЗ
«007». Указания редактору, Wordstat и нормативная справка не публикуются.

Первый экран содержит новый кадр испытания кабеля на объекте. В составе услуги
три иллюстрированные операции и отдельный тёмный подраздел о выборе метода.
Ситуации для испытаний представлены тремя самостоятельными блоками; этапы —
сеткой 2 × 2; протокол — отдельным подразделом. Сохраняются общие шапка, меню,
подвал, шрифты, компонент FAQ и механизм приёма заявок. Плашка «Заказать звонок»
открывает существующее окно `#modal`. На телефоне цена и CTA идут перед фото.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `CABLES_LOCAL_OK`. Откройте локальный URL страницы с Ctrl+F5.
Установщик проверяет версии файлов, сохраняет копии, запускает проверки PHP,
форм, рендера и изменения нативной записи. Bootstrap отсутствующей записи
разрешён только в локальном режиме `NWD_LOCAL=1`.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\cables\publish-github.ps1" -ProjectRoot $project
```

После зелёных GitHub checks выполнить напечатанную команду в SSH-консоли Beget.
Она закреплена на опубликованный коммит. Результат: `CABLES_DEPLOY_OK=<коммит>`.

## Данные и восстановление

Через действующий rewrite определяется запись site, shop_category или shop.
URL и ID сохраняются. Обновляются тема, имя, title, description, canonical,
индексация, анонс и единственная цена товара до 9900. Несколько вариантов цены
останавливают обновление до записи. Старые основной текст и медиа остаются
в базе для восстановления, но не выводятся новым шаблоном.

Существующая запись sitemap сохраняется; недостающая добавляется только для
этого маршрута. Проверяются три внутренние ссылки и действующая обратная
ссылка в группе «Кабельные линии и диагностика» каталога электролаборатории.

Резервная копия Beget находится вне document root, локальная — в
`.local/patch-backups`. При ошибке восстанавливаются данные страницы и файлы.
Последующая независимая правка записи редактором блокирует её автоматический
откат. API, статьи, авторизация и черновики не перезапускаются и не изменяются.

## Заявки и проверка

Режим `cables` использует действующую форму 27. Обязателен телефон; имя, почта
и данные кабеля необязательны. Согласие изначально не отмечено. Название услуги
и canonical задаёт сервер. Рекламные параметры ограничены действующим списком.
Поля, услуга и URL передаются в нативную почтовую очередь и центральный сервис.
Повтор не создаёт дубликат; при ошибке данные сохраняются и отправка доступна
снова. Событие `kabelnye_linii_form_submit` вызывается после успеха сервера,
`kabelnye_linii_price_click` — при переходе к форме.

Проверки охватывают нативный рендер CMS и мобильный шаблон, libxml 2.7.8,
сохранение заявки и очередей, SEO, ссылки, цену, миграцию трёх типов записи,
сохранение редакторских правок и откат. Изолированные тесты не отправляют
реальных писем; фактическая доставка проверяется согласованной тестовой
заявкой после публикации.

## Изображения

Фотография создана встроенным генератором изображений. Финальный файл:
`custom/energo/images/cables/ispytanie-silovykh-kabelnykh-liniy.webp`
(1448 × 1086, WebP). Декоративные иллюстрации продолжают SVG-стиль сайта:
`custom/energo/images/cables/illustrations.svg`.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: 4:3 landscape editorial hero photograph for an established Russian electrical testing laboratory website. Primary request: a real electrical laboratory technician conducting a power cable test at an industrial facility, visually clearly different from a breaker-testing workbench, a person using a handheld multimeter at an open cabinet, or outdoor earthing measurements. Scene: a spacious clean concrete-floor industrial cable room, grey cable trays along a pale wall and several thick black power cables in the background. Foreground: a professional portable grey cable test instrument, low floor-standing rectangular case with a small dark control panel and discreet unreadable display, connected with a neatly routed heavy insulated lead to one securely supported disconnected cable termination. The test connection is enclosed in a modest mesh safety barrier; no bare energized conductor is touched. A middle-aged technician in plain navy workwear, white hard hat and clear protective glasses kneels beside the instrument on the accessible safe side, operating its controls, three-quarter side view. The cable, testing instrument and broad curve of the thick insulated test lead are the main visual subjects; the person is secondary. Documentary industrial photography, believable real equipment, brushed metal and matte polymer textures, crisp details and natural proportions, soft lateral daylight and restrained contrast. Palette graphite, neutral stone grey and subtle navy, small restrained cable accents. Keep the scene well lit and readable, no theatrical effects. 4:3 landscape, at least 1448x1086 quality. Absolutely no captions, text overlays, readable numbers, logos, branding, watermarks, sparks, glow, yellow handheld multimeter, decorative lightning, or CGI look.
```

Композиция уточнена: работник целиком находится ближе к центру, слева добавлено
пространство помещения. Финальный путь и формат сохранены. Встроенный генератор
использован в режиме редактирования исходного кадра. Запрос коррекции:

```text
Use case: precise-object-edit. Input image 1 is the edit target, the existing website photograph. Recompose this same photograph so the technician is no longer pressed against or cropped by the left edge. Pull the camera back moderately and aim slightly farther left, extending the industrial room naturally to show at least 15–20 percent of the frame width as real room and floor to the left of the technician's whole body. Place the technician's full kneeling silhouette comfortably around 38–42 percent of the image width; his helmet, back, elbow, knee and boot must all be fully inside the frame with clear breathing room. Keep the grey testing instrument in the central foreground and the cable termination and safety mesh barrier on the right. Reduce the visual dominance of the empty mesh background as needed to balance the composition; the person and instrument should form a centred group. Preserve the same person, kneeling activity, navy workwear, white hard hat, clear safety glasses, industrial cable testing setting, test instrument, insulated red lead, supported cable termination behind the safety mesh, realistic lighting and neutral grey/navy colour grading. This is solely a framing and spacing correction, not a new subject or a new setting. No blank margins, artificial solid padding, border, mirror duplication, captions, logos, readable lettering, sparks or watermarks. Photorealistic documentary industrial photograph, landscape 4:3, high quality, at least 1448x1086.
```
