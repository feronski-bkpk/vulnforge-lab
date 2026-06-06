<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL-инъекции | Поиск пользователей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Поиск пользователей</h1>
        
        <div class="task">
            <strong>Задание:</strong> Извлеките пароли всех пользователей через UNION-инъекцию.
        </div>
        
        <form method="GET">
            <input type="text" name="search" placeholder="Введите имя пользователя...">
            <button type="submit">Найти</button>
        </form>

        <?php
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            
            $query = "SELECT username, email, role FROM users WHERE username LIKE '%$search%'";
            
            echo "<div class='query-box'><strong>Выполненный запрос:</strong><br><code>" . htmlspecialchars($query) . "</code></div>";
            
            $result = @$conn->query($query);
            
            if (!$result) {
                echo "<div class='error'>Ошибка SQL: " . htmlspecialchars($conn->lastErrorMsg()) . "</div>";
            } else {
                $rows = [];
                while ($row = @$result->fetchArray(SQLITE3_ASSOC)) {
                    $rows[] = $row;
                }
                
                if (count($rows) > 0) {
                    echo "<table>
                            <tr>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Роль</th>
                            </tr>";
                    foreach ($rows as $row) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['role']) . "</td>
                              </tr>";
                    }
                    echo "</table>";
                    echo "<div class='info'>Найдено записей: " . count($rows) . "</div>";
                } else {
                    echo "<p>Пользователи не найдены.</p>";
                }
            }
        }
        ?>
        
        <div class="nav">
            <a href="login.php">← К форме входа</a>
            <a href="profile.php?id=1">→ К профилю</a>
        </div>
    </div>
</body>
</html>
