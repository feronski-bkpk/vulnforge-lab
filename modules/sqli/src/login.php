<?php
session_start();
include 'db.php';

$error = '';
$debug_query = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $debug_query = $query;
        
        $result = safe_query($conn, $query);
        
        if ($result) {
            $user = $result->fetchArray(SQLITE3_ASSOC);
            if ($user) {
                $_SESSION['user'] = $user;
                $success = true;
                $debug_query = $query;
            } else {
                $error = "Неверное имя пользователя или пароль.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL-инъекции | Вход в систему</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Вход в систему</h1>
        
        <div class="task">
            <strong>Задание:</strong> Войдите в систему как администратор, не зная пароля.
        </div>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>Вход выполнен!</strong><br>
                Пользователь: <?php echo htmlspecialchars($user['username']); ?><br>
                Роль: <?php echo htmlspecialchars($user['role']); ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <br><br>
                    <details>
                        <summary>Показать флаг</summary>
                        <code><?php echo htmlspecialchars($user['secret_flag']); ?></code>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Войти</button>
        </form>
        
        <?php if ($debug_query): ?>
        <div class="query-box">
            <strong>Выполненный запрос:</strong><br>
            <code><?php echo htmlspecialchars($debug_query); ?></code>
        </div>
        <?php endif; ?>
        
        <div class="nav">
            <a href="index.php">→ К поиску пользователей</a>
            <a href="profile.php?id=1">→ К профилю</a>
        </div>
    </div>
</body>
</html>
