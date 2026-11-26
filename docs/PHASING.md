# Проверка фазировки электрооборудования

Обновляется существующий URL `/services/proverka-fazirovki/`.
Публикационные тексты и порядок шести блоков взяты из ТЗ «015 —
Проверка фазировки электрооборудования+». Редакторские указания,
нормативная справка и таблицы SEO не выводятся как контент страницы.
Сохраняются URL и ID действующей записи. Создание отсутствующей записи
допускается только локально, с явным bootstrap и `NWD_LOCAL=1`.

H1 и название услуги: «Проверка фазировки электрооборудования».
Последний элемент хлебных крошек: «Проверка фазировки».
Цена в hero, блоке стоимости, метаданных и Service — от 9 900 ₽.
Точная формулировка включённых работ из публикационных блоков ТЗ:
«В стоимость входят выезд специалиста, проверка фазировки в согласованном
объёме и оформление технического отчёта».
Дополнительная редакторская формулировка про выезд отдельно не добавляется.

## Композиция

На первом экране — новая фотография специалиста с компактным прибором
у распределительного щита. Цена и кнопка находятся перед фото в мобильном
порядке чтения.

Две задачи проверки имеют самостоятельные панели: чередование фаз —
на тёмном фоне с тремя синусоидами, соответствие жил — на светлом фоне
с парными контактами. Ссылка на фазировку РУ завершает этот блок.
Следующий раздел — три ситуации с рисунками кабельного барабана,
трёхфазного двигателя и ключа: одна широкая карточка и две ниже.
Далее идут четыре этапа и светлая вставка о протоколе.
Тёмный блок стоимости содержит форму. FAQ состоит из четырёх вопросов
с обычными шевронами и управлением с клавиатуры.
На узких экранах карточки складываются в один столбец.

Сохраняются общие шапка, меню, подвал, шрифты и фиксированная плашка
«Заказать звонок». Плашка открывает существующую форму `#modal`.
Нативные зависимости Fancybox доступны также на мобильном шаблоне CMS
и при выключенной анимации галереи.

## Локальная установка

Скачайте архив в «Загрузки» и выполните в PowerShell:

```powershell
$project = "C:\Users\user\WebstormProjects\nwdelectricity"
$patch = Join-Path $env:TEMP ("nwd-phasing-" + [guid]::NewGuid().ToString("N"))
Expand-Archive -LiteralPath "$env:USERPROFILE\Downloads\Nwdelectricity_Phasing.zip" -DestinationPath $patch
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Ожидается `PHASING_LOCAL_OK`.
Откройте `http://localhost:8080/services/proverka-fazirovki/` с Ctrl+F5.
Нужна уже настроенная локальная среда проекта с работающим Docker.
Установщик проверяет хеши исходников, создаёт резервные копии, проверяет
PHP, формы, полный рендер CMS и изменение нативной записи страницы.
Независимые изменения исходников вызывают остановку до замены файлов.

## GitHub и Beget

После локального просмотра:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\phasing\publish-github.ps1" -ProjectRoot $project
```

Публикация использует автора владельца, TLS и обычный push без force.
Скрипт печатает закреплённую на опубликованный SHA команду Beget
и сохраняет её в `$env:TEMP\nwd-phasing-beget.txt`.
После зелёных GitHub checks выполните её в SSH-консоли Beget.
Повторно показать команду можно так:

```powershell
Get-Content "$env:TEMP\nwd-phasing-beget.txt" -Raw
```

Успешный результат: `PHASING_DEPLOY_OK=<коммит>`.
Установка патча сама не публикует GitHub и не меняет рабочий сервер.

## Данные, границы услуги и откат

Через rewrite определяется действующая запись site, shop_category или shop.
Обновляются тема, имя, title, description, canonical, индексация и анонс.
Единственная нативная цена товара заменяется на 9900.
Несколько вариантов цены останавливают обновление до записи.
Старые текст, таблица и медиа сохраняются в базе для отката, но не выводятся
новым шаблоном. Карточка и SEO получают актуальные данные.

Страница различает чередование фаз и соответствие жил кабеля.
Класс напряжения и метод определяются по объекту и согласованной программе.
На эту общую услугу не перенесено ограничение «до 1 кВ» с прошлой страницы;
также не добавлены 6/10 кВ и обещание допуска оборудования к параллельной работе.
Прежние указания о касании токоведущих частей и обещание бесплатного выезда
в новую публикацию не входят.

Проверяются существующие маршруты лаборатории, фазировки РУ,
комплексных испытаний и технического отчёта, а также точный обратный анкор
в группе «Испытания защитной аппаратуры» каталога лаборатории.
Запись страницы фазировки РУ и её SEO не редактируются.
Существующая запись sitemap сохраняется, отсутствующая добавляется
только для текущего URL.

Резервные копии — в `.local/patch-backups` локально и вне document root
на Beget. Публичная проверка сверяет HTML и байты ассетов.
При ошибке после активации восстанавливаются файлы и запись страницы.
Независимая редакторская правка после установки блокирует автоматический
откат этой записи. API, статьи, авторизация и черновики не перезапускаются.

## Заявки и проверки

Режим `phasing` использует существующую форму 27.
Телефон обязателен; имя, почта и описание объекта необязательны.
Согласие изначально не отмечено.
Название услуги и canonical задаёт сервер.
Рекламные параметры ограничены разрешённым списком.
Заявка передаётся в нативную почтовую очередь и центральный сервис.
Повтор не создаёт дубликат. При ошибке поля и идентификатор запроса сохраняются,
кнопка снова доступна.
Событие `fazirovka_price_click` срабатывает при переходе к форме,
`fazirovka_form_submit` — после успешного ответа сервера.

Проверки: PHP 7.3.33 / libxml 2.7.8, точная копия публикации,
нативный рендер и полный desktop/mobile шаблон CMS, форма и ошибки,
DOM-контракты JS, SEO, цена 9900, публичный валидатор,
миграция трёх типов записей, сохранение редакторских правок и откат.
Изолированные адаптеры заменяют БД, Docker, Git transport и внешние отправки.
Фактическая доставка писем и production БД ими не подтверждаются.

Браузерный просмотр в этой среде недоступен из-за политики доступа.
Визуальная проверка реальной страницы на desktop/mobile остаётся для
локального просмотра перед публикацией. Рендер CMS не заменяет её.
Векторные иллюстрации проверены отдельно.

## Изображения

Фотография создана встроенным генератором в режиме новой генерации.
Финальный файл:
`custom/energo/images/phasing/proverka-fazirovki-elektrooborudovaniya.webp`
(1448 × 1086, WebP). ALT соответствует ТЗ.
Декоративные иллюстрации:
`custom/energo/images/phasing/illustrations.svg`.
Три синусоиды построены математически с одинаковой амплитудой и
сдвигом фаз 120°. Рисунки не являются результатами конкретного измерения
или монтажными инструкциями; показания приборов не выдумываются.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: horizontal 4:3 documentary service-page hero photograph. Primary request: an electrical laboratory specialist checking phase sequence at a protected low-voltage distribution cabinet using a compact professional phase-sequence tester. Scene/backdrop: clean industrial electrical room with a neutral-grey distribution cabinet on the left; primary power components stay behind closed internal protective barriers, with a dedicated small recessed touch-safe three-point test block accessible on the front. Three neatly routed insulated dark-grey test leads connect this front test block to a palm-sized rectangular graphite-grey phase-sequence tester held at waist/chest height; the instrument has three discreet indicator lamps and an unreadable small screen, no visible measurement values or labels. Subject: one professional adult man in navy workwear with narrow grey reflective strips, white hard hat, clear safety glasses and light-grey gloves, placed comfortably around centre-right. Camera sees him from a natural three-quarter rear/side angle, with a partial face profile, looking down at the small tester in his gloved hands rather than at the camera. One hand supports the tester and the other steadies its lower edge; no contact with exposed live conductors. Composition: medium-wide waist-up shot with the complete helmet, both shoulders, elbows, hands and tester comfortably inside the image, at least 12 percent headroom and 18 percent room space to the right of his back. The distribution panel and the man form one balanced working group. The small three-lead phase-sequence tester should read clearly as a portable instrument, distinct from a large relay-injection suitcase on a trolley, a man holding a tablet at an ATS cabinet, an outdoor cable locator and a generic yellow multimeter with two probes. Style: premium restrained industrial documentary photography, soft natural even lighting, realistic textures, credible anatomy and instrument leads, neutral steel-grey, charcoal and navy palette, readable exposure, no cinematic darkness. Avoid readable words, numbers, logos, brands, signatures, watermark, sparks, glowing electricity, exposed busbars, green-yellow earth wire used as a phase lead, messy wiring, red theatrical lighting, yellow multimeter, laptop or papers.
```
