# Проверка металлосвязи

Обновляется существующая страница `/services/zamer-metallosvyazi/`.
Тексты публикации, шесть блоков, пять вопросов FAQ, форма и SEO соответствуют
ТЗ «010». Предисловие, указания редактору, Wordstat и нормативная справка
не публикуются. Сохраняются URL и ID действующей страницы.

Новый первый экран показывает измерение у корпуса оборудования. Состав услуги
представлен иллюстрацией соединения и четырьмя группами защитных цепей.
Ситуации для проверки собраны в карточки с иллюстрациями сбоку, этапы —
в сетку 2 × 2. Содержание протокола выделено отдельным тёмным подразделом.
На телефоне цена и CTA идут перед фотографией. Общие шапка, меню, подвал,
шрифты и форма приёма заявок сохраняются. Плашка «Заказать звонок» открывает
существующее окно `#modal`. Нативные зависимости этого окна подключаются
также в мобильном режиме CMS и при отключённой галерейной анимации.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `CONTINUITY_LOCAL_OK`. Откройте локальный URL страницы с Ctrl+F5.
Установщик проверяет версии файлов, сохраняет копии, запускает проверки PHP,
форм, рендера и изменения нативной записи. Bootstrap отсутствующей записи
разрешён только в локальном режиме `NWD_LOCAL=1`.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\continuity\publish-github.ps1" -ProjectRoot $project
```

После зелёных GitHub checks выполнить напечатанную команду в SSH-консоли Beget.
Она закреплена на опубликованный коммит. Результат: `CONTINUITY_DEPLOY_OK=<коммит>`.
Команда также сохраняется в `$env:TEMP\nwd-continuity-beget.txt`.

## Данные и восстановление

Через действующий rewrite определяется запись site, shop_category или shop.
Обновляются тема, имя, title, description, canonical, индексация, анонс
и единственная цена товара до 9900. Несколько вариантов цены останавливают
обновление до записи. Старый текст и медиа сохраняются в базе для отката,
но не выводятся новым шаблоном. Старые общие интервалы и универсальные
значения сопротивления не публикуются.

Существующая запись sitemap сохраняется; недостающая добавляется только
для этого маршрута. Проверяются четыре внутренних маршрута, обратная ссылка
в каталоге лаборатории и ссылка из комплекса испытаний. В списке комплексных
испытаний анкор приведён к написанию «Проверка металлосвязи», как требует ТЗ.

Резервная копия Beget находится вне document root, локальная — в
`.local/patch-backups`. При ошибке восстанавливаются данные страницы и файлы.
Независимая правка записи редактором после установки блокирует автоматический
откат этой записи. API, статьи, авторизация и черновики не перезапускаются
и не изменяются.

## Заявки и проверка

Режим `continuity` использует действующую форму 27. Обязателен телефон;
имя, почта и сведения об объекте необязательны. Согласие изначально не отмечено.
Название «Проверка металлосвязи» и canonical задаёт сервер. Рекламные параметры
ограничены действующим списком. Поля, услуга и URL передаются в нативную
почтовую очередь и центральный сервис. Повтор не создаёт дубликат; при ошибке
введённые данные сохраняются и кнопка снова доступна. Событие
`metallosvyaz_form_submit` вызывается после подтверждения сервера,
`metallosvyaz_price_click` — при переходе к форме.

Проверки охватывают нативный рендер CMS и мобильный шаблон, libxml 2.7.8,
сохранение заявки и очередей, SEO, ссылки, цену, миграцию трёх типов записи,
сохранение редакторских правок и откат. Вёрстка проверяется в браузере на
компьютере, планшете и телефоне. Изолированные тесты не отправляют реальные
письма; фактическая доставка проверяется согласованной тестовой заявкой
после публикации.

## Изображения

Фотография создана встроенным генератором в режиме генерации. Финальный файл:
`custom/energo/images/continuity/proverka-metallosvyazi.webp`
(1448 × 1086, WebP). Декоративные SVG продолжают визуальный язык сайта:
`custom/energo/images/continuity/illustrations.svg`.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: premium documentary industrial hero photograph for an established electrical laboratory service website, landscape 4:3 at least 1448x1086. Primary request: an electrical laboratory technician carrying out a protective bonding continuity test using a low-resistance testing instrument at industrial equipment. Make this composition distinct from a man writing a report at a desk, a person testing breaker modules on a workbench, a kneeling cable test operator, and a generic handheld yellow multimeter at a distribution board. Scene: a bright orderly industrial machine room. Foreground on the right is the neutral grey metal enclosure of an industrial motor control unit with its small de-energized side service compartment open; an accessible protective bonding bar and tidy green-yellow bonding conductors are visible behind internal covers, with no live parts exposed. A securely fastened green-yellow bonding conductor links the enclosure to its protective terminal. Subject: a professional middle-aged male technician in plain dark navy workwear, white hard hat and clear safety glasses, standing comfortably around the centre-left of the frame, seen from a natural three-quarter angle, looking down at the instrument. He operates a compact professional grey low-resistance tester resting on a waist-high equipment ledge, with two neatly routed thin test leads clipped to two separated accessible metal bonding points on the enclosure and protective bonding bar. Show the testing instrument, the safe closed metal body, the protective conductor and the technician's hands clearly; do not depict handling bare live conductors. Composition: a medium-wide shot at torso height, show his complete helmet, shoulders, elbows and upper torso comfortably within the frame with at least 18–22 percent real room space to the left of his silhouette. No shoulder, back, helmet or hand squeezed against an edge. The person, small tester, protective conductor and metal enclosure form one balanced group in a credible working environment. Natural soft daylight, realistic anatomy, painted steel, matte polymer, crisp believable details and a restrained graphite, stone-grey and navy palette; the green-yellow conductor is a modest real equipment detail. Neutral readable exposure, no cinematic darkness. The instrument display is discreet and unreadable. Absolutely no readable numbers, measurements, labels, text overlays, signatures, branding, logos, watermark, sparks or glowing effects. No yellow multimeter, no loose cables or clutter, no CGI or cartoon aesthetic.
```
