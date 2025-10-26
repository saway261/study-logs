FROM php:8.2-apache

# 必要パッケージ & PHP拡張
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql intl zip opcache \
 && a2enmod rewrite headers

# Apache ドキュメントルートを public/ に
ARG APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
 && sed -ri "s!<Directory /var/www/html>!<Directory ${APACHE_DOCUMENT_ROOT}>!g" /etc/apache2/apache2.conf \
 && echo '<Directory "/var/www/html/public">\n    AllowOverride All\n    Require all granted\n</Directory>' > /etc/apache2/conf-available/app.conf \
 && a2enconf app

# Composer を導入
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# タイムゾーンはアプリ側（Symfony）でも Asia/Tokyo を使う
ENV TZ=Asia/Tokyo
