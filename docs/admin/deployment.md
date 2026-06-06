# Развёртывание VulnForge Lab

## Системные требования

- Docker Engine 20.10+
- Docker Compose 2.0+
- 2 ГБ свободной оперативной памяти
- Linux (рекомендуется) / macOS / Windows с WSL2

## Установка

```bash
git clone <репозиторий>
cd vulnforge-lab
echo "127.0.0.1 sqli.vulnforge.local xss.vulnforge.local" | sudo tee -a /etc/hosts
./vulnforge up
```

## Сброс стенда

Для полного сброса всех данных:

```bash
./vulnforge down
docker compose down -v
./vulnforge up
```

## Мониторинг

```bash
docker stats            # Нагрузка контейнеров
docker logs vf-sqli     # Логи модуля SQLi
docker logs vf-xss      # Логи модуля XSS
docker logs vf-nginx    # Логи прокси
```

## Безопасность

Стенд содержит УЯЗВИМЫЕ приложения. Меры предосторожности:

1. Не публикуйте порты наружу (только localhost)
2. Не размещайте реальные данные в стенде
3. Используйте стенд только в изолированной сети
4. После использования остановите стенд: `./vulnforge down`
