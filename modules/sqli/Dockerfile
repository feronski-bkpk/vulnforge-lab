FROM php:8.1-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

COPY src/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/database.sqlite \
    && chown www-data:www-data /var/www/html/database.sqlite \
    && chmod 666 /var/www/html/database.sqlite

# Инициализируем БД при сборке
RUN php -r '
$db = new SQLite3("/var/www/html/database.sqlite");
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    role TEXT DEFAULT \"user\",
    secret_flag TEXT
)");
$db->exec("INSERT INTO users (username, password, email, role, secret_flag) VALUES
    (\"admin\", \"SuperSecretPass123!\", \"admin@vulnforge.local\", \"admin\", \"FLAG{SQL1_1nj3ct10n_M4st3r}\"),
    (\"john.doe\", \"password123\", \"john@example.com\", \"user\", \"FLAG{us3r_fl4g_n0t_us3ful}\"),
    (\"jane.smith\", \"qwerty\", \"jane@example.com\", \"user\", \"FLAG{an0th3r_us3r_fl4g}\")");
'

EXPOSE 80
