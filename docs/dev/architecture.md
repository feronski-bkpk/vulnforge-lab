# Архитектура VulnForge Lab

## Общая структура

```
vulnforge-lab/
├── docker-compose.yml        # Оркестрация контейнеров
├── vulnforge                 # CLI-утилита управления
├── nginx/
│   └── nginx.conf            # Конфигурация прокси
├── modules/
│   ├── sqli/                 # Модуль SQL-инъекций
│   │   ├── Dockerfile
│   │   └── src/
│   │       ├── db.php        # Подключение к БД
│   │       ├── login.php     # Форма входа
│   │       ├── index.php     # Поиск пользователей
│   │       ├── profile.php   # Профиль пользователя
│   │       └── style.css     # Стили
│   └── xss/                  # Модуль XSS
│       ├── Dockerfile
│       └── src/
└── docs/                     # Документация
```

## Добавление нового модуля

1. Создайте директорию `modules/имя_модуля/src/`
2. Создайте `Dockerfile`
3. Добавьте сервис в `docker-compose.yml`
4. Добавьте маршрут в `nginx/nginx.conf`
5. Добавьте задание в CLI (`vulnforge`)
6. Напишите документацию

## Технологический стек

- **Веб-сервер:** PHP 8.1 + Apache
- **База данных:** SQLite (встроенная, не требует отдельного сервера)
- **Прокси:** Nginx
- **Контейнеризация:** Docker + Docker Compose
- **CLI:** Bash
