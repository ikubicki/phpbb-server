FROM php:apache
# RUN apt update && apk add build-base
# RUN apk add postgresql postgresql-dev \
#   && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
#   && docker-php-ext-install pdo pdo_pgsql pgsql
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"
RUN apt-get update
RUN apt-get install -y libzip-dev libcurl4-openssl-dev pkg-config libssl-dev telnet
RUN pecl install mongodb
RUN docker-php-ext-install zip
RUN docker-php-ext-enable mongodb
RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
RUN a2enmod rewrite
COPY . /var/www
COPY ./htdocs /var/www/html
WORKDIR /var/www
RUN composer install --prefer-source --no-interaction