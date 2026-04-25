FROM dunglas/frankenphp:1.3-builder-php8.3-alpine

WORKDIR /app

RUN apk add --no-cache git unzip bash postgresql-dev \
    && docker-php-ext-install pdo_pgsql

COPY . /app

# 🔹 Install Composer globally (official method)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN wget https://get.symfony.com/cli/installer -O - | bash
ENV PATH="/root/.symfony5/bin:$PATH"


RUN composer install --no-interaction --no-progress --prefer-dist

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV SERVER_NAME=debarras.localhost
ENV SERVER_ROOT=/app/public

RUN chown -R www-data:www-data /app

EXPOSE 80 443

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

