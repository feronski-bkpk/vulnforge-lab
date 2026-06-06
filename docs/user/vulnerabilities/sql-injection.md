# SQL-инъекции (SQL Injection) — CWE-89

## Что это такое?

SQL-инъекция — это уязвимость, при которой злоумышленник может внедрить свой SQL-код в запрос к базе данных. Это происходит, когда приложение берёт пользовательский ввод и вставляет его в SQL-запрос без проверки и экранирования.

## Как это работает?

### Нормальный запрос

Представьте форму входа. Пользователь вводит:
- Логин: `admin`
- Пароль: `secret123`

Приложение строит запрос:
```sql
SELECT * FROM users WHERE username = 'admin' AND password = 'secret123'
```

Если пользователь с таким логином и паролем существует — вход разрешён.

### Инъекция

Злоумышленник вводит в поле логина:
```
admin' OR '1'='1' --
```

Запрос становится:
```sql
SELECT * FROM users WHERE username = 'admin' OR '1'='1' --' AND password = '...'
```

**Разбор по частям:**
1. `username = 'admin'` — ищем пользователя admin
2. `OR '1'='1'` — ИЛИ условие, которое всегда истинно (1 всегда равен 1)
3. `--` — комментарий в SQL. Всё, что после `--`, игнорируется
4. `AND password = '...'` — эта часть закомментирована и не выполняется

**Результат:** запрос находит пользователя admin, полностью игнорируя проверку пароля.

## Типы SQL-инъекций

### 1. Error-Based SQL Injection

Основана на анализе сообщений об ошибках базы данных. Злоумышленник намеренно вызывает ошибки, чтобы понять структуру БД.

**Пример:**
```
Ввод: admin'
Ошибка: unrecognized token: "'"
```

Из ошибки видно, что используется SQLite и что кавычка ломает синтаксис.

**Что можно узнать через ошибки:**
- Тип базы данных (MySQL, PostgreSQL, SQLite, MSSQL)
- Названия таблиц и колонок
- Версию СУБД

**Защита:** отключить вывод ошибок в production (display_errors = Off).

### 2. UNION-Based SQL Injection

Использует оператор UNION для объединения результатов легитимного запроса с данными, которые хочет получить злоумышленник.

**Пример:**
```sql
SELECT username, email, role FROM users WHERE username LIKE '%' 
UNION SELECT password, email, role FROM users WHERE '1'='1%'
```

**Требования для UNION:**
- Количество колонок должно совпадать
- Типы данных должны быть совместимы

**Как определить количество колонок:**
- `ORDER BY 1--`, `ORDER BY 2--`, `ORDER BY 3--` (пока не будет ошибки)
- `UNION SELECT NULL--`, `UNION SELECT NULL, NULL--` и т.д.

**Защита:** параметризованные запросы, валидация типов.

### 3. Boolean-Based Blind SQL Injection

Используется, когда приложение не показывает ошибки и не выводит данные напрямую. Злоумышленник задаёт вопросы "да/нет" и анализирует разницу в ответах.

**Пример:**
```
?id=1 AND 1=1  → пользователь отображается (TRUE)
?id=1 AND 1=2  → пользователь не найден (FALSE)
```

**Извлечение данных посимвольно:**
```sql
AND SUBSTR(secret_flag, 1, 1) = 'F'  -- Первый символ 'F'?
AND SUBSTR(secret_flag, 2, 1) = 'L'  -- Второй символ 'L'?
AND SUBSTR(secret_flag, 3, 1) = 'A'  -- Третий символ 'A'?
```

**Защита:** параметризованные запросы, ограничение длины ввода.

### 4. Time-Based Blind SQL Injection

Когда приложение не показывает разницы в ответах, но можно измерить задержку.

**Пример (SQLite):**
```sql
1 AND (SELECT CASE WHEN (1=1) THEN randomblob(100000000) ELSE 0 END)
```

Если условие истинно — запрос выполняется дольше.

**Защита:** таймауты на запросы, параметризация.

## Почему это опасно?

| Угроза | Описание | Impact |
|--------|----------|--------|
| Обход аутентификации | Вход без пароля | Высокий |
| Кража данных | Извлечение всех записей БД | Критический |
| Модификация данных | Изменение, удаление записей | Критический |
| Выполнение команд | В некоторых СУБД возможен RCE | Критический |
| DoS | Уничтожение таблиц, блокировка | Высокий |

## Как найти уязвимость?

### Ручной поиск

1. **Ищите точки ввода:** формы, GET-параметры, заголовки, cookie
2. **Введите спецсимволы:** `'`, `"`, `;`, `--`, `/*`
3. **Анализируйте ответ:** ошибка? изменение поведения?
4. **Подтвердите:** `OR 1=1` vs `OR 1=2`

### Признаки уязвимости

- Сообщения об ошибках SQL
- Разное поведение для `AND 1=1` и `AND 1=2`
- Результаты, не соответствующие ожидаемым

### Инструменты

```bash
# VulnScanner Pro
./vulnscanner http://target.com/login.php

# sqlmap (автоматизация)
sqlmap -u "http://target.com/page.php?id=1" --dbs
```

## Как исправить?

### 1. Параметризованные запросы (Best Practice)

**Было (уязвимо):**
```php
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);
```

**Стало (безопасно):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindValue(':username', $username);
$stmt->execute();
```

### 2. Экранирование (менее надёжно)

```php
$username = mysqli_real_escape_string($conn, $username);
$query = "SELECT * FROM users WHERE username = '$username'";
```

### 3. Валидация ввода

```php
// Ожидаем число — проверяем
if (!is_numeric($id)) {
    die("Неверный формат ID");
}

// Ожидаем email — валидируем
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Неверный формат email");
}
```

### 4. Принцип минимальных привилегий

Создайте пользователя БД с минимально необходимыми правами:
```sql
-- Только SELECT на нужные таблицы
GRANT SELECT ON myapp.users TO 'app_user'@'localhost';

-- Без прав на DROP, ALTER, UPDATE (если не нужно)
```

### 5. WAF (Web Application Firewall)

Используйте ModSecurity, Cloudflare WAF или аналоги для фильтрации SQL-инъекций на уровне HTTP.

## Практика в стенде

В VulnForge Lab доступны три задания:

| Задание | URL | Сложность |
|---------|-----|-----------|
| Обход аутентификации | `/login.php` | Лёгкая |
| UNION-инъекция | `/index.php?search=` | Средняя |
| Слепая инъекция | `/profile.php?id=` | Сложная |

**Запуск через CLI:**
```bash
./vulnforge task start 1    # Обход аутентификации
./vulnforge task start 2    # UNION
./vulnforge task start 3    # Blind
```

## CVSS Score: 9.8 (Critical)

```
CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H
```

- **AV:N** — доступна удалённо
- **AC:L** — низкая сложность атаки
- **PR:N** — не требует привилегий
- **UI:N** — не требует взаимодействия с пользователем
- **S:U** — влияет на тот же компонент
- **C:H/I:H/A:H** — высокое влияние на конфиденциальность, целостность, доступность

## Дополнительные материалы

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [CWE-89](https://cwe.mitre.org/data/definitions/89.html)
- [PortSwigger Web Security Academy](https://portswigger.net/web-security/sql-injection)
- [SQLite Injection Cheat Sheet](https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/SQL%20Injection/SQLite%20Injection.md)
