FROM php:7.3-fpm

LABEL maintainer="VATSIM United Kingdom"

# Install composer
RUN apt-get -yqq update \
    && apt-get -yqq install --no-install-recommends unzip apt-utils \
    # fetch the installer.
    && curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    # verify the signature of the install.
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    # check if the installer is alid.
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) \
    !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-\
    setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    # run the composer setup.
    && php /tmp/composer-setup.php \
        --no-ansi \
        --install-dir=/usr/local/bin \
        --filename=composer \
        --snapshot \
    # remove temporary files.
    && rm -f /tmp/composer-setup.*

# install the missing extensions not in the base image.
RUN docker-php-ext-install \
    bcmath \
    pcntl \
    pdo_mysql

# copy the source files (bear in mind the context on the docker-compose)
COPY . /srv/app/

# set the current directory to the source.
WORKDIR /srv/app

# install composer dependencies.
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# grant permission to the files to the nginx service.
RUN chown -R www-data:www-data /srv/app