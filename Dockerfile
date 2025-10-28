# ---- Dockerfile (no .htaccess; use vhost + FallbackResource) ----
FROM php:8.2-apache

# System deps & PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql intl zip opcache \
 && a2enmod rewrite headers

# Set timezone (PHP & OS)
ENV TZ=Asia/Tokyo

# Avoid Apache ServerName warning
RUN echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# Provide a dedicated vhost that routes all non-existing paths to index.php
# (no .htaccess needed; clean URLs work)
RUN cat >/etc/apache2/sites-available/symfony.conf <<'CONF'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Require all granted
        Options FollowSymLinks
        AllowOverride None
        FallbackResource /index.php
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
CONF
RUN a2dissite 000-default && a2ensite symfony

# Composer (from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App workdir
WORKDIR /var/www/html
