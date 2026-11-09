# ERP Core

Основата на DS ERP: хост приложение, в което се копират останалите модули. Дава автентикация, потребители, роли, файлове, споделен CRUD слой и ляво меню.

## Функционалност

- Вход, изход, възстановяване на парола, двуфакторна автентикация (2FA)
- Потребители и потребителски групи с RBAC
- Файлове и асоциации към други ентитети
- Системни настройки и информационна страница
- Базови класове за CRUD: `CoreBaseController`, `CoreEntity`, форми за редакция и търсене
- Ляво меню (`Menu\Builder`) и преводи (bg/en)
- Команда `core:create-module` за скелетиране на нов модул

## Интеграция в системата

Това **е** хостът. Останалите модули се сливат в същите пътища (`src/`, `templates/`, `translations/`, `config/roles/`).

- Меню: Табло (`dashboard`), Администрация → Потребители, Групи, Информация
- Роли: `ROLE_USER_*`, `ROLE_USER_GROUP_*` в `config/roles/user.yaml` и `config/roles/core.yaml`
- Маршрути: `/login`, `/users`, `/user-groups`, `/information`, `/files`, `/`

След копиране на друг модул се добавят роли, миграции и елемент в `src/Menu/Builder.php`.

## Структура

- `src/Controller/Core/`, `src/Controller/User/` — CRUD, файлове, вход
- `src/Entity/Core/`, `src/Entity/User/` — `CoreEntity`, `File`, `Setting`, `BaseUser`, `UserGroup`
- `src/Service/Core/` — файлове, роли, имейл, преводи, контекст
- `src/Menu/Builder.php` — ляво меню
- `src/Command/Core/CreateCoreModule.php` — генератор на модули
- `templates/core/`, `templates/user/`
- `config/roles/`, `config/packages/`

## Зависимости

Няма други ERP модули. Composer зависимостите са в `composer.json` (Scheb 2FA, Knp Menu/Paginator и др.).

## Документация

- [docs/core/README.md](docs/core/README.md)
- [docs/core/installation-guide.md](docs/core/installation-guide.md)
- [docs/core/quick-start.md](docs/core/quick-start.md)
- [docs/core/module_integration_guide.md](docs/core/module_integration_guide.md)
- [docs/core/simple_custom_module_development.md](docs/core/simple_custom_module_development.md)
