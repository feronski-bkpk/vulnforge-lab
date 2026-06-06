<?php
session_start();

$db = new SQLite3('/var/www/html/xss.db');

// Простая авторизация
if (isset($_GET['login'])) {
    $_SESSION['user'] = $_GET['login'];
    $_SESSION['role'] = 'user';
    header('Location: index.php');
    exit;
}

// Сохранение комментария
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $username = $_SESSION['user'] ?? 'Гость';
    $comment = $_POST['comment'];
    
    $stmt = $db->prepare("INSERT INTO comments (username, comment) VALUES (:username, :comment)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
    $stmt->execute();
    $saved = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>XSS | Хранимая инъекция</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Гостевая книга</h1>
        
        <div class="task">
            <strong>Задание:</strong> Оставьте комментарий с JavaScript-кодом, который выполняется у всех посетителей страницы.<br>
            <strong>Цель (сложная):</strong> Украдите cookie пользователя через XSS.
        </div>
        
        <?php if (!isset($_SESSION['user'])): ?>
            <div class="info">
                <strong>Войдите чтобы оставлять комментарии:</strong><br>
                <a href="?login=admin">Войти как admin</a> | 
                <a href="?login=user1">Войти как user1</a> |
                <a href="?login=guest">Войти как guest</a>
            </div>
        <?php else: ?>
            <div class="success">
                Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>
                <br><small>Ваша сессия: <?php echo session_id(); ?></small>
            </div>
            
            <form method="POST">
                <textarea name="comment" placeholder="Ваш комментарий..." rows="4"></textarea>
                <button type="submit">Отправить</button>
            </form>
        <?php endif; ?>
        
        <?php if ($saved): ?>
            <div class="success">Комментарий сохранён! <a href="index.php">Обновить страницу</a></div>
        <?php endif; ?>
        
        <h2>Комментарии</h2>
        <?php
        $result = $db->query("SELECT * FROM comments ORDER BY id DESC");
        $count = 0;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $count++;
            echo "<div class='comment'>";
            echo "<strong>" . htmlspecialchars($row['username']) . "</strong> ";
            echo "<small>" . $row['created_at'] . "</small><br>";
            echo "<p>" . $row['comment'] . "</p>";
            echo "</div>";
        }
        if ($count == 0) {
            echo "<p>Пока нет комментариев. Будьте первым!</p>";
        }
        ?>
        
        <div class="nav">
            <a href="search.php">→ К поиску (Reflected XSS)</a>
        </div>
    </div>
</body>
</html>
