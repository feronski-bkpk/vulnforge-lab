<?php
$db = new SQLite3('/var/www/html/xss.db');
$db->exec("CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
echo "XSS DB initialized!\n";
