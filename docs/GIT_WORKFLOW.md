# Работа с Git

## Начальное состояние

В полном подготовленном архиве Git уже инициализирован: основная ветка `main`,
первый коммит содержит исходный код и настройки репозитория.
Remote `origin` не задан, поскольку удалённый репозиторий ещё не создан.
Повторно выполнять `git init` и добавлять весь бэкап не требуется.

После распаковки в отдельную папку открой её в WebStorm. Если Git не определяется,
проверь установленный Git в **Settings → Version Control → Git** и нажми **Test**.
Локальные настройки IDE не включаются в историю.

```powershell
git status --short
git branch --show-current
git log -1 --oneline
git remote -v
```

Ожидается чистое рабочее дерево, `main`, один коммит и отсутствие remote.
Папки с кэшем и контентом могут существовать на диске — это нормально.

## Создать GitHub-репозиторий из WebStorm

1. **Git → GitHub → Share Project on GitHub**.
2. Войти в аккаунт владельца `powder-rose`, если IDE ещё не подключена.
3. Указать `nwdelectricity`, remote `origin`, включить **Private**.
4. Нажать **Share** и дождаться отправки коммита.

После этого:

```powershell
git remote -v
git status -sb
```

Основной адрес будет соответствовать `powder-rose/nwdelectricity`, если при создании
выбраны эти аккаунт и имя. Репозиторий не создавался автоматически в процессе
подготовки архива: подключённый доступ не предоставляет операцию создания новых репозиториев.

## Если пустой репозиторий создан вручную

На GitHub создай **private**-репозиторий `nwdelectricity` без автоматически
добавленных README, лицензии и `.gitignore`. Затем в папке проекта:

```powershell
git remote add origin https://github.com/powder-rose/nwdelectricity.git
git push -u origin main
```

Если `origin` уже существует или в удалённом репозитории есть коммиты, сначала
проверить `git remote -v` и историю. Не применять `--force` для обхода расхождения.
Создание репозитория не публикует сайт: для сайта остаётся отдельный процесс деплоя.

## Обычная задача

```powershell
git switch main
git pull --ff-only
git switch -c feat/header
```

После редактирования добавлять связанные файлы по именам, затем смотреть индекс:

```powershell
git add custom/energo/themes/blocks/header.php custom/energo/css/style.css
git diff --cached --stat
git diff --cached
git commit -m "feat: update site header"
git push -u origin feat/header
```

Создать pull request в `main`, проверить CI и просмотр сайта локально.
Названия веток — короткие и без цифр: `feat/header`, `fix/mobile-menu`,
`chore/local-environment`. `git pull --ff-only` предполагает уже настроенный remote
и отслеживание ветки после первой публикации.

## Настройки автора

Первый коммит создан с GitHub noreply-адресом владельца. В `.git/config` не записаны
имя и почта для будущих коммитов: используются настройки Git на твоём компьютере.
Проверить свои настройки можно локально:

```powershell
git config user.name
git config user.email
```

Не присылать почту или конфиги авторизации в чат для обычной проверки Git.

## Что не нужно добавлять

Не использовать `git add -f` для рабочего `config.php`, кэша, `userfls/`, дампов
и вложенной `public_html/`. Перечень исключений записан в корневом `.gitignore`.
Отдельно сохранять свежие бэкапы базы и медиа. История Git хранит исходники,
но не заменяет полную резервную копию работающего сайта.
