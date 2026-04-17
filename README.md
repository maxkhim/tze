## Запуск проекта

```bash
git clone https://github.com/maxkhim/tze
cd ./tze
sudo docker compose up -d --remove-orphans --build
```

Проверка

```http request
http://127.0.0.1:8088/api/v1/products
```

или 

```http request
http://ип_сервера:8088/api/v1/products
```