# Проверка релейной аппаратуры

Обновляется существующий URL `/services/proverka-releynoy-apparatury/`.
Публикационные тексты и порядок шести блоков взяты из ТЗ «014».
Указания редактору, SEO-комментарии и нормативная справка не публикуются.
Сохраняются URL и ID действующей записи. Создание отсутствующей записи
допускается только локально, с явным bootstrap и `NWD_LOCAL=1`.

На первом экране — специалист с испытательным прибором у релейного шкафа.
Объём до 1 кВ выделен внутри точного текста вводного абзаца.
Состав проверки оформлен четырьмя текстовыми группами под широкой иллюстрацией
«испытательный прибор → реле → выходная команда». Для телефона используется
компактная версия рисунка. Блок ситуаций имеет асимметричную композицию:
одна тёмная карточка слева, две светлые справа.
Далее идут четыре этапа и тёмная вставка о протоколе.
FAQ содержит пять вопросов, обычные шевроны и управление с клавиатуры.
В мобильной версии цена и кнопка идут перед фото, текстовые карточки — столбцом.

Общие шапка, меню, подвал, шрифты и фиксированная плашка «Заказать звонок»
сохраняются. Плашка открывает существующую форму `#modal`.
Нативные зависимости Fancybox доступны также на мобильном шаблоне CMS
и при выключенной анимации галереи.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Ожидается `RELAY_LOCAL_OK`. Откройте локальную страницу с Ctrl+F5.
Установщик проверяет хеши исходников, создаёт резервные копии, проверяет
PHP, формы, полный рендер CMS и изменение нативной записи страницы.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\relay\publish-github.ps1" -ProjectRoot $project
```

Публикация использует автора владельца, TLS и обычный push без force.
Скрипт печатает закреплённую на опубликованный SHA команду Beget
и сохраняет её в `$env:TEMP\nwd-relay-beget.txt`.
После зелёных GitHub checks выполните её в SSH-консоли Beget.
Результат: `RELAY_DEPLOY_OK=<коммит>`.

## Данные, цены и откат

Через rewrite определяется действующая запись site, shop_category или shop.
Обновляются тема, имя, title, description, canonical, индексация и анонс.
Единственная нативная цена товара заменяется на 9900.
Несколько вариантов цены останавливают обновление до записи.
Старые текст, таблица и медиа сохраняются в базе для отката, но не выводятся
новым шаблоном. Карточка и SEO получают актуальные данные.
Старая таблица 213 / 1421 рублей, рекомендация проверки раз в три года
и обещание устранения дефектов в день проверки отсутствуют в новой публикации.

Объём услуги ограничен аппаратурой до 1 кВ. Проверка выполняется по заданным
уставкам и согласованным функциям. Условия фактических отключений уточняются
заранее. Расчёт и изменение уставок, наладка и ремонт остаются отдельными
задачами согласно границам услуги в ТЗ.

Проверяются маршруты лаборатории, комплексных испытаний и технического отчёта,
а также ссылка «Проверка релейной аппаратуры» в группе «Испытания защитной
аппаратуры» каталога. Другие услуги не редактируются.
Существующая запись sitemap сохраняется, отсутствующая добавляется только
для текущего URL.

Резервные копии — в `.local/patch-backups` локально и вне document root
на Beget. Публичная проверка сверяет HTML и байты ассетов.
При ошибке восстанавливаются файлы и запись страницы.
Независимая редакторская правка после установки блокирует автоматический
откат этой записи. API, статьи, авторизация и черновики не перезапускаются.

## Заявки и проверки

Режим `relay` использует существующую форму 27.
Телефон обязателен; имя, почта и сведения об оборудовании необязательны.
Согласие изначально не отмечено.
Название «Проверка релейной аппаратуры» и canonical задаёт сервер.
Рекламные параметры ограничены разрешённым списком.
Заявка передаётся в нативную почтовую очередь и центральный сервис.
Повтор не создаёт дубликат. При ошибке поля и идентификатор запроса сохраняются,
кнопка снова доступна.
Событие `releynaya_apparatura_price_click` срабатывает при переходе к форме,
`releynaya_apparatura_form_submit` — после успешного ответа сервера.

Проверки: PHP 7.3.33 / libxml 2.7.8, точная копия публикации,
нативный рендер и полный desktop/mobile шаблон CMS, форма и ошибки, очереди,
DOM-контракты JS, SEO, цена 9900 и видимый объём до 1 кВ, публичный валидатор,
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
`custom/energo/images/relay/proverka-releynoy-apparatury.webp`
(1448 × 1086, WebP). ALT соответствует ТЗ.
Декоративные иллюстрации продолжают векторный стиль сайта:
`custom/energo/images/relay/illustrations.svg`.
Рисунок испытательного сигнала и реакции реле поясняет состав проверки;
он не является монтажной схемой или реальным результатом измерения.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: horizontal 4:3 premium documentary hero photograph for a low-voltage relay apparatus testing service. Primary request: an electrical laboratory specialist operating a professional secondary-injection relay test set beside a relay control cabinet. Scene/backdrop: clean neutral-grey industrial electrical room, relay apparatus panel on the right with small protective relays and a dedicated touch-safe test connector block; all primary power parts remain fully behind closed protective barriers, no bare live busbars or handling high-voltage wiring. In the foreground a compact professional grey secondary-injection test case rests on a stable waist-height equipment trolley, its front control panel has several orderly colour-coded low-voltage test sockets, a few connected red and black insulated test leads, small controls and an unreadable screen. The few neat leads run to the relay test connector block on the panel. Subject: one professional adult man in plain navy workwear, white hard hat, clear safety glasses and light-grey gloves, positioned around centre-left in a natural three-quarter view facing right, looking at the testing instrument and gently operating a control with one gloved hand, other hand resting naturally on the instrument's side. Composition: medium-wide view showing the specialist, test case, trolley top and relay cabinet together as a credible working group. Complete helmet, shoulders, elbows, gloved hands and test instrument comfortably within the frame, at least 18 percent genuine room space left of the person's silhouette and 12 percent headroom above the helmet. No person pushed against the left edge. Distinct from a man holding a tablet at a closed ATS cabinet, an outdoor cable tracing worker, or a worker with handheld yellow multimeter probes. Visual style: restrained contemporary industrial documentary photography, realistic painted steel and polymer, natural skin texture and anatomy, soft even light, readable neutral exposure, muted navy, steel-grey and charcoal palette with modest real red/black lead colours. No legible words, numbers, brands, logos, fake readout values or watermarks. No sparks, glowing electricity, dramatic darkness, tangled cables, uncovered live power parts, yellow multimeter, papers or laptop.
```
