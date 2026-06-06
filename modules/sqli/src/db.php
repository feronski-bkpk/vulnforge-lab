<?php
$db_path = '/var/www/html/database.sqlite';

try {
    $conn = new SQLite3($db_path);
    $conn->enableExceptions(true);
} catch (Exception $e) {
    die("<div class='error'>Ошибка подключения к базе данных.</div>");
}

function safe_query($conn, $query) {
    try {
        return $conn->query($query);
    } catch (Exception $e) {
        echo "<div class='error'>Ошибка SQL: " . htmlspecialchars($e->getMessage()) . "</div>";
        return false;
    }
}
?>
