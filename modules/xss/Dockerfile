FROM php:8.1-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

COPY src/ /var/www/html/

# Права на директорию и файл БД
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/xss.db \
    && chown www-data:www-data /var/www/html/xss.db \
    && chmod 666 /var/www/html/xss.db

# Создаём таблицы при сборке
RUN php -r '
$db = new SQLite3("/var/www/html/xss.db");
$db->exec("CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
'

EXPOSE 80
