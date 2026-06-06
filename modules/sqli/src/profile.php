<?php
include 'db.php';

$user_data = null;
$debug_query = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT id, username, email, role FROM users WHERE id = $id";
    $debug_query = $query;
    
    $result = @$conn->query($query);
    
    if ($result) {
        $user_data = $result->fetchArray(SQLITE3_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL-инъекции | Профиль пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Профиль пользователя</h1>
        
        <div class="task">
            <strong>Задание (Слепая инъекция):</strong> Извлеките секретный флаг администратора посимвольно, используя разницу в ответах TRUE/FALSE. Флаг НЕ отображается в интерфейсе — его можно получить только через слепую инъекцию.
        </div>
        
        <?php if ($user_data): ?>
            <div class="profile">
                <p><strong>ID:</strong> <?php echo htmlspecialchars($user_data['id']); ?></p>
                <p><strong>Имя:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                <p><strong>Роль:</strong> <?php echo htmlspecialchars($user_data['role']); ?></p>
            </div>
        <?php else: ?>
            <p>Пользователь не найден.</p>
        <?php endif; ?>
        
        <?php if ($debug_query): ?>
        <div class="query-box">
            <strong>Запрос:</strong><br><code><?php echo htmlspecialchars($debug_query); ?></code>
        </div>
        <?php endif; ?>
        
        <div class="nav">
            <a href="login.php">← К форме входа</a>
            <a href="index.php">← К поиску пользователей</a>
        </div>
    </div>
</body>
</html>
