## 🚀 Быстрый старт

```bash
git clone https://github.com/maxkhim/tze
cd ./tze
sudo docker compose up -d --remove-orphans --build
```

Приложение контейнеризировано: `app` (встроенный сервер), `queue` (очередь), `mariadb`, `redis`

Миграции выполняются автоматически при запуске контейнера

Очередь самостоятельно стартует и выполняется в отдельном контейнере

[Документация OpenAPI](openapi.json)

Проверка

```http request
http://127.0.0.1:8088/api/v1/products
```

или

```http request
http://ип_сервера:8088/api/v1/products
```

## Запуск теста

```bash
sudo docker compose exec app php artisan test ./tests/Feature/OrderCreationTest.php
```
