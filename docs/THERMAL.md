# Тепловизионное обследование электрооборудования

Обновляется существующий основной URL:
`/services/teplovizionnoe-obsledovanie-elektrooborudovaniya/`.

Старый URL `/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya/`
получает прямой серверный 301 на основной адрес.
Тексты и порядок шести блоков взяты из ТЗ «016 — тпелрвизионный контроль».
Публикуется только подготовленная редакция: без редакторских указаний,
нормативной справки и SEO-таблиц. Пять FAQ присутствуют в HTML.
Указанную в ТЗ сверку поисковой статистики пропускаем по последующему
прямому указанию владельца «игнорируй».

## Оформление

Общая палитра, шрифты, шапка, меню и подвал сохранены.
Hero имеет новую фотографию с бесконтактной тепловизионной съёмкой.
Цена и кнопка идут перед фотографией на телефоне.

В блоке оборудования — одна крупная иллюстрация обследования и четыре
самостоятельные текстовые группы. Ситуации обращения оформлены четырьмя
карточками с одним основным тёмным акцентом.
Далее идут четыре этапа в сетке 2 × 2 и тёмная вставка о техническом отчёте.
Согласованная реальная термограмма не предоставлена: выдуманный результат
измерения и подпись к нему не добавляются.
Стоимость и форма расположены слева и справа, на телефоне — последовательно.
FAQ использует обычные шевроны и доступен с клавиатуры.

Фиксированная плашка «Заказать звонок» открывает существующую форму `#modal`.
Fancybox берётся из нативных зависимостей CMS и доступен на мобильном шаблоне
и при отключённой анимации галереи.

## Установка локально

Скачайте архив в «Загрузки» и выполните в PowerShell:

```powershell
$project = "C:\Users\user\WebstormProjects\nwdelectricity"
$patch = Join-Path $env:TEMP ("nwd-thermal-" + [guid]::NewGuid().ToString("N"))
Expand-Archive -LiteralPath "$env:USERPROFILE\Downloads\Nwdelectricity_Thermal.zip" -DestinationPath $patch
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Нужны существующая локальная конфигурация Docker и работающие контейнеры.
Ожидается `THERMAL_LOCAL_OK`. Откройте основной URL через
`http://localhost:8080/` и обновите Ctrl+F5.
Перенаправление на localhost сохраняет локальный origin.

Установщик проверяет хеши исходников, резервирует заменяемые файлы,
проверяет PHP и поведение и выполняет транзакционное изменение нативных данных.
Отсутствующая основная страница может создаваться только при локальном
bootstrap с `NWD_LOCAL=1`. Создание отсутствующих страниц в production отключено.
Отсутствие старого URL в давнем локальном дампе не мешает предпросмотру.
Рабочий сервер должен содержать обе исходные записи.

## GitHub и Beget

После локального просмотра:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\thermal\publish-github.ps1" -ProjectRoot $project
```

Скрипт использует автора владельца, проверяемый TLS и обычный push без force.
Он выводит команду Beget, закреплённую на опубликованный SHA, и сохраняет её
в `$env:TEMP\nwd-thermal-beget.txt`.
После зелёных GitHub checks выполните эту команду в SSH-консоли Beget.
Повторно вывести её:

```powershell
Get-Content "$env:TEMP\nwd-thermal-beget.txt" -Raw
```

Успешный результат — `THERMAL_DEPLOY_OK=<коммит>`.
Локальная установка сама не меняет GitHub и рабочий сайт.

## Цена, ссылки и нативные данные

Цена в hero и блоке стоимости — «От 4 900 ₽» с подписью
«с выездом и оформлением технического отчёта».
SEO Title, Description, H1, ALT и хлебные крошки соответствуют ТЗ.
В Service нижняя граница цены передаётся через `PriceSpecification.minPrice`;
фиксированная конечная цена не заявляется.

По rewrite определяется действующая запись site, shop_category или shop.
Сохраняются её ID и URL. Обновляются тема, метаданные, анонс карточки из ТЗ
и единственная нативная цена товара (4900).
Неоднозначные варианты цены останавливают обновление до записи.

Старая запись сохраняется для отката, но отключается в активном каталоге,
получает noindex и исключается из sitemap.
Ссылка в родительском каталоге уже ведёт на основной URL; проверяются точный
анкор и группа «Кабельные линии и диагностика».
Нативные ссылки меню переадресуются непосредственно на основную запись.

В текстовых и анонсных полях нативных разделов site, shop_category, shop,
news и clauses заменяются только ссылки на старый внутренний URL и цена
11000 в строках тепловизионного обследования/контроля.
Сохраняются остальные строки таблиц, внешний контент и другие тарифы.
Поддерживаются обычный и HTML-экранированный формат хранения.
Это также обновляет старую строку на странице технического отчёта, если она
осталась в нативном тексте. Новая версия технического отчёта не содержит
такой таблицы: отсутствующая строка заново не добавляется.

В статьях нового редактора ссылки преобразуются при публичном показе.
Payload, revision, API, сессии и черновики не изменяются и не перезапускаются.
Повторная публикация старого черновика не возвращает старую ссылку в
публичный HTML.

## Серверное перенаправление и откат

В `includes/init.php` добавлен узкий вызов из четырёх строк перед нативным
поиском перенаправлений. Это необходимое изменение ядра: штатный поиск
сравнивает полный REQUEST_URI, включая query string, и не обеспечивает
одинаковый 301 для размеченного старого адреса после отключения дубля.
Логика вынесена в `custom/energo/includes/thermal_redirect.php`.
Другие запросы продолжают штатный поиск; .htaccess и правила HTTPS не меняются.
Маркетинговые параметры сохраняются, production-цель — канонический HTTPS.
Чужой Host не может изменить адрес назначения.
Неизвестное переопределение Init вызывает остановку перед заменой данных.

Перед активацией проверяются исходники, PHP 7.3, формы и нативные маршруты.
После активации проверяются 200 и HTML основной страницы, прямой 301
старого адреса с параметрами и без, sitemap и точные байты ассетов.
Проверка sitemap поддерживает нативный sitemapindex.

Бэкапы находятся в `.local/patch-backups` локально и вне document root на Beget.
При неуспешной проверке восстанавливаются нативные изменения и файлы.
Для связанных записей сохраняются точные поля и удалённые строки sitemap.
Редакторские изменения после установки блокируют их автоматическую перезапись
при откате. Зависимые старые файлы восстанавливаются до удаления новых помощников.

## Форма и проверки

Используется нативная форма 27 с режимом `thermal`.
Телефон обязателен; имя, почта и описание объекта необязательны.
Согласие изначально не отмечено.
Название услуги и основной URL задаются сервером.
Рекламные параметры ограничены разрешённым списком.
Ошибки отображаются рядом с полями; при ошибке введённые данные и ID запроса
сохраняются, кнопка становится доступной. Повтор не создаёт дубликат.
События: `thermal_survey_price_click` и `thermal_survey_form_submit`.
Отправка фиксируется только после успешного ответа сервера.

Проверены PHP 7.3.33 / libxml 2.7.8, точность публикации, шесть блоков,
нативный desktop/mobile каркас, форма и ошибки, DOM-контракты JS,
SEO и стартовая цена, миграция трёх типов страниц, объединение записей,
реальный метод Init для 301, публичный вывод ссылок статей и откат.
В интеграционных сценариях БД, Docker, Git transport и внешние отправки
заменены изолированными адаптерами. Работа production БД и реальная доставка
писем этими проверками не подтверждаются.

Браузерный просмотр здесь недоступен из-за политики доступа.
Визуальную проверку действительной страницы на desktop/mobile нужно выполнить
локально перед публикацией; проверка рендера CMS её не заменяет.
Векторные иллюстрации просмотрены отдельно.

## Изображения

Фотография создана встроенным генератором в режиме новой генерации.
Предпочтение реальной фотографии из файла ТЗ заменено разрешённой владельцем
генерацией изображений в общей стилистике сайта.
Финальный файл:
`custom/energo/images/thermal/teplovizionnoe-obsledovanie-elektrooborudovaniya.webp`
(1448 × 1086, WebP).
Иллюстрации находятся в `custom/energo/images/thermal/illustrations.svg`.
Они декоративные и не изображают реальные термограммы или результаты измерения.

Точный запрос фотографии:

```text
Use case: photorealistic-natural. Asset type: horizontal 4:3 hero photograph for an electrical thermographic inspection service page. Primary request: a professional electrical laboratory specialist performing a non-contact thermal-camera survey of a distribution cabinet. Scene/backdrop: a clean neutral-grey industrial switchroom; an open outer distribution-cabinet door on the left reveals neatly organised insulated conductors, recessed connection housings and circuit breaker fronts; no bare busbars or exposed live metal, no contact with electrical components. Subject: one adult professional male in plain navy workwear with restrained grey reflective details, a white hard hat, clear safety glasses and light-grey gloves. He is positioned comfortably near the centre of the image and seen from a natural front-side three-quarter angle, facing the cabinet. He raises a compact professional graphite-grey pistol-grip thermal imaging camera to chest height and aims its round front infrared lens toward the equipment from a sensible distance. His right hand naturally holds the camera grip, left hand lightly supports it. The camera's rear screen is at an oblique unreadable angle: no fake thermal readout, numbers or temperature values. Composition: medium-wide waist-up shot with the complete helmet, both shoulders, elbows, gloved hands and camera fully inside the frame, at least 15 percent breathing room around the specialist's silhouette, enough space for the cabinet to read clearly. Balanced group of specialist and cabinet, subject not pressed against any edge. Distinct from a rear-view worker holding a three-lead phase-sequence tester or an operator with a large relay test suitcase. Photography style: premium understated industrial documentary, realistic anatomy and materials, soft even daylight-like lighting, readable neutral exposure, steel-grey, muted navy and charcoal palette. No cinematic darkness or heavy colour effects. Avoid: connected probes, test cables, a yellow multimeter, tablet, laptop, papers, readable text, labels, logos, watermarks, sparks, glowing electricity, fire, infrared overlay, thermal false-colour photograph, glass between the lens and the inspected components.
```
