<!DOCTYPE html>
<html>
<head>
    <title>XSS | Отражённая инъекция</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Поиск по сайту</h1>
        
        <div class="task">
            <strong>Задание:</strong> Выполните JavaScript через параметр URL.
        </div>
        
        <form method="GET">
            <input type="text" name="q" placeholder="Поиск...">
            <button type="submit">Найти</button>
        </form>
        
        <?php if (isset($_GET['q'])): ?>
            <div class="result">
                <h3>Результаты поиска для: <?php echo $_GET['q']; // УЯЗВИМОСТЬ ?></h3>
                <p>По вашему запросу ничего не найдено.</p>
            </div>
        <?php endif; ?>
        
        <div class="nav">
            <a href="index.php">← К гостевой книге (Stored XSS)</a>
        </div>
    </div>
</body>
</html>
