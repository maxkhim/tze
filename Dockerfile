FROM php:8.4-fpm

# Установка системных зависимостей и расширений PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    wget \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql zip

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Копирование файлов приложения
COPY . .

# Установка зависимостей Composer
RUN composer setup
#RUN composer update

# Права на storage и cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

#RUN php artisan migrate --force
#RUN php artisan db:seed --force

#CMD ["php", "artisan", "migrate", "--force"]
#CMD ["php", "artisan", "db:seed", "--force"]

EXPOSE 8088
RUN echo "✅ Сборка образа успешно завершена! Версия 1.0.0"
# Команда по умолчанию (переопределяется в docker-compose)

#CMD ["php", "artisan", "db:seed", "--force"]
#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8088"]