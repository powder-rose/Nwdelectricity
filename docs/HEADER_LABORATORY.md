# Электроизмерительная лаборатория в шапке

В раздел «Услуги» добавлен пункт «Электроизмерительная лаборатория»:
`/services/elektroizmeritelnaya-laboratoriya/`.
Общий источник используется десктопным dropdown и мобильным меню.
Если пункт уже есть в данных CMS, он используется один раз с нужным названием
и прямой внутренней ссылкой. Если его нет, он добавляется при выводе под «Услугами».
Остальные разделы меню и записи БД не меняются.

## Установка

Скачайте `Nwdelectricity_Header_Laboratory.zip` в «Загрузки» и выполните:

```powershell
$project = "C:\Users\user\WebstormProjects\nwdelectricity"
$patch = Join-Path $env:TEMP ("nwd-header-laboratory-" + [guid]::NewGuid().ToString("N"))
Expand-Archive -LiteralPath "$env:USERPROFILE\Downloads\Nwdelectricity_Header_Laboratory.zip" -DestinationPath $patch
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$patch\apply.ps1" -ProjectRoot $project
```

Результат — `HEADER_LABORATORY_LOCAL_OK`.
Обновите `http://localhost:8080/` через Ctrl+F5 и откройте «Услуги».
Патч проверяет хеши и сохраняет старые файлы в `.local/patch-backups`.
Миграции базы и перезапуск API не требуются.

## GitHub и Beget

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$project\tools\header-laboratory\publish-github.ps1" -ProjectRoot $project
```

Используются автор владельца, проверяемый TLS и обычный push без force.
Скрипт печатает команду Beget, закреплённую на опубликованный SHA.
После зелёных GitHub checks вставьте её в SSH-консоль.
Повторно вывести команду:

```powershell
Get-Content "$env:TEMP\nwd-header-laboratory-beget.txt" -Raw
```

Результат — `HEADER_LABORATORY_DEPLOY_OK=<коммит>`.
На сервере заменяется только `service_navigation.php`, очищается генерируемый
файловый кэш CMS и проверяется единственная ссылка в обеих версиях шапки.
Страница лаборатории должна отвечать 200. При ошибке файл восстанавливается
из приватного бэкапа вне document root.

Нативные PHP-шаблоны проверены с изолированными данными меню: существующий
и отсутствующий пункт, дубли, текущая страница и постороннее подменю.
Проверены PHP 7.3, архив и команды установки/публикации с заменённым транспортом.
Браузерный просмотр здесь недоступен; фактический внешний вид нужно посмотреть локально.
