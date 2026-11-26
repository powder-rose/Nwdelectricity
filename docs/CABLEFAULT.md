# Поиск места повреждения кабеля

Обновляется существующая страница `/services/poisk-mesta-povrezhdeniya-kabelya/`.
Публикационные тексты и порядок шести блоков взяты из ТЗ «012».
Предисловие, указания редактору, Wordstat и критерии приёмки не публикуются.
URL и ID сохраняются. Недостающая страница может создаваться только локально
с явным флагом bootstrap и `NWD_LOCAL=1`.

Первый экран — специалист с трассопоисковым приёмником на территории объекта,
стартовая цена и CTA перед фотографией на телефоне. Три пронумерованных этапа
поиска имеют отдельные иллюстрации: расстояние, трассировка и локализация.
Вскрытие грунта выделено тёмным блоком с иллюстрацией доступа к подземной линии.
Далее четыре этапа заказа, отдельное сообщение об ограничениях точности,
расчёт стоимости и четыре FAQ с обычными шевронами. Земляные работы и ремонт
согласуются отдельно — формулировки сохранены из ТЗ.

Общие шапка, меню, подвал, шрифты и плашка «Заказать звонок» сохраняются.
Плашка открывает существующую форму `#modal`; нативные зависимости Fancybox
доступны также на мобильном шаблоне CMS и при выключенной анимации галереи.

## Локальная установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `CABLEFAULT_LOCAL_OK`. Откройте локальный URL с Ctrl+F5.
Установщик проверяет исходники по хешам, создаёт резервные копии, проверяет
PHP, формы, нативный рендер CMS и изменение записи страницы.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\cablefault\publish-github.ps1" -ProjectRoot $project
```

Скрипт использует автора владельца, проверку TLS и push без force.
Он печатает команду Beget, закреплённую на опубликованном SHA, и сохраняет её
в `$env:TEMP\nwd-cablefault-beget.txt`. После зелёных GitHub checks выполните
напечатанную команду в SSH-консоли Beget. Ожидается
`CABLEFAULT_DEPLOY_OK=<коммит>`.

## Данные и восстановление

Действующая запись site, shop_category или shop определяется по rewrite.
Обновляются тема, имя, title, description, canonical, индексация и анонс.
Единственная цена товара в нативной таблице shop_price меняется на 19900.
Несколько вариантов цены останавливают установку до записи.
Старые текст и медиа остаются в базе для отката, но не выводятся новым шаблоном.
Метаданные и карточка получают новую цену. Старые 57 рублей и формулировка
о бесплатном выезде в новой публикации отсутствуют.

Проверяются действующие маршруты ремонта и лаборатории, а также существующая
обратная ссылка «Поиск места повреждения кабеля» в группе «Кабельные линии
и диагностика» каталога. Другие услуги не редактируются.
Существующая запись sitemap сохраняется; отсутствующая добавляется только
для текущего маршрута.

Копии хранятся в `.local/patch-backups` локально и вне document root на Beget.
Проверка после установки сверяет публичный HTML и байты ассетов.
При ошибке восстанавливаются файлы и запись страницы. Независимая
редакторская правка после установки блокирует автоматический откат записи.
API, статьи, авторизация и черновики не перезапускаются и не меняются.

## Заявки и проверки

Режим `cablefault` использует действующую форму 27. Обязателен телефон;
имя, почта и данные о кабеле необязательны. Согласие изначально не отмечено.
Название «Поиск места повреждения кабеля» и canonical задаёт сервер.
Рекламные параметры ограничены разрешённым списком.
Заявка передаётся в нативную почтовую очередь и центральный сервис.
Повтор не создаёт дубликат. При ошибке поля и идентификатор запроса сохраняются,
кнопка становится доступна снова. Событие `poisk_kabelya_price_click`
срабатывает при переходе к форме, `poisk_kabelya_form_submit` — после
успешного ответа сервера.

Проверки включают PHP 7.3.33 / libxml 2.7.8, точную копию публикации,
нативный рендер и полный desktop/mobile шаблон CMS, обработку формы и ошибок,
очереди, DOM-контракты JS, SEO и цену 19900, публичный валидатор, миграцию трёх
типов записи, сохранение редакторских правок, откат и защиту установщиков.
Изолированные адаптеры заменяют БД, Docker, Git transport и внешние отправки.
Они не подтверждают фактическую доставку писем и реальную работу production БД.

Браузерный просмотр в этой среде заблокирован политикой доступа.
Визуальная проверка реальной страницы на desktop/mobile остаётся для локального
просмотра перед публикацией. Рендер CMS не заменяет эту проверку.
Векторные иллюстрации проверены отдельно.

## Изображения

Фотография создана встроенным генератором в режиме новой генерации.
Финальный файл:
`custom/energo/images/cablefault/poisk-mesta-povrezhdeniya-kabelya.webp`
(1448 × 1086, WebP). ALT соответствует ТЗ.
Декоративные иллюстрации продолжают векторный стиль сайта:
`custom/energo/images/cablefault/illustrations.svg`.
Они объясняют последовательность поиска и доступ к кабелю, не являются
монтажными схемами или реальными измерениями.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: 4:3 horizontal documentary photograph for an electrical laboratory cable-fault-location service page. Primary request: a specialist tracing the route of a buried power cable with a professional handheld cable locator receiver on the paved grounds outside an industrial building. Scene/backdrop: clean neutral grey industrial courtyard with asphalt and a subtle concrete service walkway, understated industrial building in the background, soft overcast daylight. Subject: one adult professional man in navy workwear with narrow grey reflective bands, a white hard hat, clear safety glasses and light grey gloves. He is standing in a natural working posture, looking down at his professional cable locator, holding its top carrying handle at hip height. The locator is a credible long vertical rectangular aerial receiver with a slim flat sensor blade reaching close to the pavement, graphite housing and a modest muted yellow top control housing, a small unreadable screen; it is a buried-utility cable tracing instrument, not a circular metal detector and not a handheld multimeter. One hand on the locator, other arm relaxed. Composition: medium-wide full-body three-quarter view, person and tool form a balanced central group, at least 18 percent true space to the left of his silhouette and 15 percent to the right of the tool, complete helmet, shoulders, elbows and boots all comfortably inside the picture, with visible pavement below the receiver. Keep the person large enough to read on a service website but never crop him at an edge. Contemporary premium documentary photography, real natural textures and proportions, believable hands and receiver, restrained neutral concrete-grey and dark navy palette, soft light and readable exposure, no dramatic darkness. Avoid readable words, numbers, measurement readouts, logos or watermarks. No visible exposed power cable, trench, sparks, glowing cable routes, electrical cabinet, outdoor storm, lightning, circular search coil, theatrical effects or floating graphics.
```
