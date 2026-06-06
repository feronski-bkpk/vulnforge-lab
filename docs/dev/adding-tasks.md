# Добавление собственных заданий в VulnForge

## Структура задания

Каждое задание состоит из:
1. **Уязвимого кода** (в модуле)
2. **Walkthrough** (в документации)
3. **Записи в CLI** (в `vulnforge`)

## Добавление SQL-инъекции

### 1. Создайте уязвимую страницу

Добавьте файл `modules/sqli/src/my_task.php`:

```php
<?php include 'db.php'; ?>

<h1>Моё задание</h1>
<div class="task">
    <strong>Задание:</strong> Найдите скрытого пользователя через SQL-инъекцию.
</div>

<?php
$id = $_GET['id'] ?? 1;
$query = "SELECT username FROM users WHERE id = $id"; // УЯЗВИМОСТЬ

$result = safe_query($conn, $query);
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<p>" . htmlspecialchars($row['username']) . "</p>";
}
?>
```

### 2. Добавьте запись в БД

В `modules/sqli/src/init_db.php` добавьте своего пользователя:

```sql
INSERT INTO users (username, password, role, secret_flag) 
VALUES ('hidden_admin', 'secret123', 'admin', 'FLAG{МОЙ_ФЛАГ}');
```

### 3. Создайте Walkthrough

В `docs/user/walkthroughs/my-task.md`:

```markdown
# Моё задание

## Шаг 1: Изучаем страницу
Откройте /my_task.php?id=1

## Шаг 2: Проверяем на инъекцию
Попробуйте id=1 AND 1=1

## Шаг 3: Извлекаем данные
...
```

### 4. Добавьте в CLI

В `vulnforge`, в функцию `task_list()`:

```bash
echo "  7. Моё задание (SQLi)             [средняя]"
```

В функцию `task_start()`:

```bash
7) echo "Откройте: http://sqli.vulnforge.local/my_task.php?id=1" ;;
```

## Шаблон уязвимой страницы

```php
<?php include 'db.php'; // или своё подключение ?>
<!DOCTYPE html>
<html>
<head>
    <title>Категория | Название задания</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Название задания</h1>
        
        <div class="task">
            <strong>Задание:</strong> Краткое описание того, что нужно сделать.
        </div>
        
        <!-- Ваш уязвимый код -->
        
        <div class="nav">
            <a href="index.php">← Назад</a>
        </div>
    </div>
</body>
</html>
```

## Важно

- Флаги должны быть в формате `FLAG{...}`
- Используйте `safe_query()` для обработки ошибок
- Добавляйте `htmlspecialchars()` только там, где НЕ нужна уязвимость
- Документируйте задание в walkthrough
