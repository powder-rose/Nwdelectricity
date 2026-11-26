# Проверка автоматических выключателей

Обновляется существующая страница `/services/proverka-avtomaticheskikh-vyklyuchateley/`.
Шесть блоков, тексты, четыре вопроса FAQ, поля формы и SEO взяты из ТЗ «006».
Редакторские указания, нормативные ссылки и Wordstat не публикуются.

Первый экран показывает испытание выключателя на лабораторном стенде. Блок о
проверке содержит полный маркированный список и отдельное объяснение прогрузки.
Ситуации представлены четырьмя карточками, этапы — сеткой 2×2, протокол — отдельным
подразделом. Цена и форма используют общий визуальный язык NWD. Общие шапка,
меню, подвал и шрифты сохраняются. На телефоне цена и CTA предшествуют фотографии.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot 'C:\Users\user\WebstormProjects\nwdelectricity'
```

Ожидается `BREAKERS_LOCAL_OK`. Откройте с Ctrl+F5:
`http://localhost:8080/services/proverka-avtomaticheskikh-vyklyuchateley/`.

Установщик проверяет версии зависимостей и файлов, сохраняет резервную копию,
проверяет PHP, формы и нативный desktop/mobile рендер. При отсутствии записи
в старом локальном экспорте разрешён bootstrap только с `NWD_LOCAL=1`.
На production отсутствующая страница не создаётся.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File '.\tools\breakers\publish-github.ps1' -ProjectRoot (Get-Location).Path
```

Скрипт публикует согласованные файлы с авторством владельца и печатает точную
команду Beget, закреплённую на коммит. После зелёных GitHub checks выполните её
в существующей SSH-консоли. Итог: `BREAKERS_DEPLOY_OK=<коммит>`.

## Данные и восстановление

Через rewrite находится исходная запись site, shop_category или shop.
URL и ID сохраняются. Обновляются тема, имя, title, description, canonical,
индексация и анонс. Единственная цена товара обновляется до 9900; наличие
нескольких вариантов останавливает изменение до записи. Основной старый текст
и медиа остаются в БД для восстановления, новый шаблон их не выводит.

Существующий sitemap сохраняется. Недостающая запись добавляется только для
этого URL. Проверяются смежные опубликованные маршруты комплексных испытаний,
электролаборатории и технического отчёта, а также готовая обратная ссылка в
каталоге лаборатории. Другие услуги не редактируются.

Копии Beget сохраняются вне document root; локальные — в `.local/patch-backups`.
При ошибке откатываются данные страницы и файлы. Независимая последующая правка
записи редактором блокирует автоматическое восстановление этой записи.
Статьи, локальные черновики, настройки авторизации и API не затрагиваются.

## Заявки и проверка

Нативная форма 27 использует режим `breakers`. Обязателен телефон; имя, почта и
перечень аппаратов необязательны. Согласие изначально не отмечено. Название
услуги и canonical задаёт сервер; рекламные параметры ограничены действующим
списком. Очереди письма и центрального сервиса получают поля, название услуги
и URL. Повторы не создают дубликаты заявки и уведомления.

События `avtomaty_price_click` и `avtomaty_form_submit` фиксируют переход к форме
и успешный ответ сервера. При ошибке значения сохраняются, кнопка становится
доступной. Подписи и сообщения соответствуют ТЗ.

Проверяются форма и очереди, три типа нативных записей, цена, откат, SEO,
структура, ссылки и полный рендер CMS. Проверки H1 учитывают libxml 2.7.8 на Beget.
Изолированные тесты не отправляют реальные письма. Доставку рабочими каналами
проверяют согласованной тестовой заявкой после публикации.

## Иллюстрации и фотография

Фотография создана встроенным генератором изображений. Финальный файл:
`custom/energo/images/breakers/proverka-avtomaticheskikh-vyklyuchateley.webp`
(1448 × 1086, WebP). Векторные декоративные иллюстрации:
`custom/energo/images/breakers/illustrations.svg`.

Точный запрос для фотографии:

```text
Use case: photorealistic-natural. Asset: high quality 4:3 editorial commercial website photograph for an electrical testing laboratory. A clearly different setup from standing technicians at cabinets or outdoor earth testing: a clean industrial testing workbench photographed obliquely from slightly above. The main subject in sharp focus is a chunky professional three-pole molded-case circuit breaker, charcoal housing with a black toggle, being tested with a compact primary current injection test unit, grey metal chassis with a dark control face, physical rotary control and discreet unreadable display, at the same bench. Proper thick red and black insulated test leads and secure terminal connections, neatly routed. At the left edge a real middle-aged electrical laboratory technician in plain navy workwear and clear protective glasses is visible in three-quarter profile, shoulders and gloved hands; he concentrates on the apparatus and adjusts a control. No helmet needed in this bench laboratory setting. The circuit breaker and tester occupy the foreground and lower centre; the technician is secondary, not a portrait. Background: restrained blurred grey laboratory shelving and pale walls, no exposed live cabinet. Authentic real materials, tactile matte plastic and brushed metal, soft directional daylight, sophisticated graphite and neutral stone palette with subtle navy clothing and small cable accents, crisp documentary industrial photography, no theatrical sparks or glow. Preserve a readable, well lit detailed scene, suitable for slight dark site grading. Landscape 4:3, at least 1448x1086 quality. Absolutely no logos, branding, lettering, readable numbers, watermarks or text overlays. No handheld yellow multimeter, no fantasy device, no CGI appearance.
```
