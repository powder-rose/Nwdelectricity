# Проверка систем молниезащиты

Обновляется существующая страница `/services/proverka-sistem-molniezaschity/`.
Публикационные тексты и порядок шести блоков взяты из ТЗ «011».
Предисловие, указания редактору, Wordstat и нормативная справка не публикуются.
URL и ID сохраняются; новая запись допускается только для отсутствующей
локальной страницы при `NWD_LOCAL=1`.

Первый экран: фото осмотра токоотвода снаружи здания, стартовая цена и CTA.
Состав работ — четыре отдельные карточки и архитектурная иллюстрация системы.
Ситуации для проверки — три тёмных блока в композиции 2 + 1. Далее этапы,
отдельный подраздел о техническом отчёте и две условные карточки: оформление
акта и актуализация паспорта. Формулировки и условия сохранены из ТЗ.
FAQ использует обычные шевроны. Цена и кнопка на телефоне идут до фотографии.
Общие шапка, меню, подвал, шрифты и плашка «Заказать звонок» сохраняются.
Плашка открывает существующую форму `#modal`; нативные зависимости Fancybox
подключаются также в мобильном режиме CMS и при отключённой анимации галереи.

## Установка

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат: `LIGHTNING_LOCAL_OK`. Откройте локальную страницу с Ctrl+F5.
Установщик проверяет хеши исходников, делает резервные копии, запускает
проверки PHP, форм, рендера и миграции. Запись в БД определяется по rewrite,
а не по заранее угаданному ID.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\lightning\publish-github.ps1" -ProjectRoot $project
```

Публикация использует автора владельца, ограниченный список файлов,
проверку TLS и обычный push без force. После успешной публикации скрипт
печатает закреплённую на SHA команду Beget и сохраняет её в
`$env:TEMP\nwd-lightning-beget.txt`. Выполнить её в SSH-консоли Beget.
Сервер проверяет зелёные GitHub checks и целостность релиза до установки.
Результат: `LIGHTNING_DEPLOY_OK=<коммит>`.

## Данные и откат

На существующей записи site, shop_category или shop обновляются тема, имя,
title, description, canonical, индексация, анонс и единственная цена товара
до 9900. Несколько вариантов цены останавливают обновление до записи.
Старые текст и медиа остаются в базе для отката и не выводятся новым шаблоном.
Универсальные интервалы осмотров, сроки для ЛЭП и утверждение об акте как
допуске к эксплуатации из прежней страницы не публикуются.

Проверяются четыре профильных внутренних маршрута и уже существующая
обратная ссылка «Проверка систем молниезащиты» из каталога лаборатории.
Содержимое других услуг не редактируется. Существующая запись sitemap
сохраняется, отсутствующая добавляется только для текущего маршрута.

Локальные копии — в `.local/patch-backups`, серверные — вне document root.
Публичная проверка сверяет HTML и байты ассетов. При ошибке восстанавливаются
файлы и запись страницы. Независимая редакторская правка после установки
блокирует автоматический откат этой записи. API, статьи и черновики не
перезапускаются и не меняются.

## Заявки и проверки

Режим `lightning` работает через существующую форму 27. Обязателен телефон;
имя, почта и сведения об объекте необязательны. Согласие не отмечено заранее.
Услугу «Проверка систем молниезащиты» и canonical определяет сервер.
Рекламные параметры ограничены разрешённым списком. Заявка и её контекст
передаются в нативную почтовую очередь и центральный сервис.
При ошибке поля и идентификатор повтора сохраняются, кнопка разблокируется.
Повтор не создаёт дубликат. Событие `molniezashchita_price_click`
срабатывает при переходе к форме, `molniezashchita_form_submit` — после
подтверждения сервера.

Проверки: PHP 7.3.33 / libxml 2.7.8, точный текст ТЗ, нативный рендер и
полный desktop/mobile шаблон CMS, обработка формы и ошибки, очереди,
DOM-контракты JavaScript, SEO и публичный валидатор, миграция трёх типов
записей, цена, откат, защиты установщиков и закреплённая команда публикации.
Адаптеры изолируют БД, Docker, Git transport и внешние отправки.
Реальная доставка писем и заявок этими проверками не подтверждается.

Автоматический браузерный просмотр в этой среде был заблокирован политикой
доступа браузера. Проверка реальной вёрстки на desktop/mobile остаётся для
локального просмотра перед публикацией; успешный рендер CMS не заменяет её.

## Изображения

Фотография создана встроенным генератором в режиме новой генерации.
Финальный файл:
`custom/energo/images/lightning/proverka-sistemy-molniezashchity.webp`
(1448 × 1086, WebP). ALT взят из ТЗ. Векторные иллюстрации продолжают
существующий стиль сайта, декоративны и не являются монтажной схемой:
`custom/energo/images/lightning/illustrations.svg`.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: 4:3 documentary service-page hero photograph. Primary request: an electrical laboratory specialist visually inspecting an accessible lightning protection down conductor and bolted test joint outside an industrial building. Scene: understated grey stone/concrete industrial facade, vertical bare metal strip on wall with a realistic bolted inspection joint at waist/chest height; calm overcast daytime, subtle industrial depth beyond. Subject: one professional adult man wearing a clean white hard hat, navy work jacket with discreet grey reflective bands, clear protective glasses and light grey work gloves, focused on the bolted joint, gently inspecting the connector with one gloved hand, natural anatomy. Composition: horizontal 4:3 photograph, comfortable medium-wide three-quarter upper-body view, worker around the middle-left of the image with at least 18 percent visible breathing room to his left, whole helmet, shoulders and elbows inside the frame; the vertical conductor and joint clearly visible to his right, no person pressed against the left edge, no awkward crop. Style: premium restrained real documentary industrial photography, natural textures, believable hands and hardware, muted neutral grey and dark navy, soft even light, realistic skin and metal. No readable text, numbers, brands, logos or watermarks. No dramatic lightning, storms, glowing wires, sparks, rooftop work, helmets cropped, invented gauges, electrical cabinet or papers. It is an exterior visual inspection, not a staged indoor measurement.
```
