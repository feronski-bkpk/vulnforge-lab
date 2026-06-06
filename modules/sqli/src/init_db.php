<?php
$db_path = '/var/www/html/database.sqlite';

if (file_exists($db_path)) {
    unlink($db_path);
}

$db = new SQLite3($db_path);
$db->enableExceptions(true);

$db->exec("
    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        email TEXT,
        role TEXT DEFAULT 'user',
        secret_flag TEXT
    )
");

$db->exec("
    INSERT INTO users (username, password, email, role, secret_flag) VALUES
    ('admin', 'SuperSecretPass123!', 'admin@vulnforge.local', 'admin', 'FLAG{SQL1_1nj3ct10n_M4st3r}'),
    ('john.doe', 'password123', 'john@example.com', 'user', 'FLAG{us3r_fl4g_n0t_us3ful}'),
    ('jane.smith', 'qwerty', 'jane@example.com', 'user', 'FLAG{an0th3r_us3r_fl4g}')
");

echo "Database initialized!\n";
