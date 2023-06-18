FROM php:8.0-fpm

ENV COMPOSER_HOME /tmp

RUN apt-get update && \
    apt-get install -y git locales libpq-dev libzip-dev unzip libicu-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) intl gettext zip pdo pdo_pgsql

RUN echo 'en_GB.UTF-8 UTF-8' >> /etc/locale.gen && \
    echo 'fr_FR.UTF-8 UTF-8' >> /etc/locale.gen && \
    locale-gen

COPY --from=composer/composer /usr/bin/composer /usr/bin/composer
