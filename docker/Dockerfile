FROM ubuntu:focal

ENV DEBIAN_FRONTEND noninteractive

# Install Nginx, PHP and further necessary packages
RUN apt update --fix-missing
RUN apt install -y curl git unzip mariadb-client nginx \
    php-apcu php-xdebug php7.4-cli php7.4-curl php7.4-fpm php7.4-mbstring php7.4-mysql php7.4-zip php7.4-xml

# Configure Nginx with PHP
RUN mkdir -p /run/php
COPY configs/nginx.conf /etc/nginx/sites-available/default
RUN echo "xdebug.remote_enable = 1" >> /etc/php/7.4/mods-available/xdebug.ini
RUN echo "xdebug.remote_autostart = 1" >> /etc/php/7.4/mods-available/xdebug.ini
RUN echo "xdebug.remote_connect_back = 1" >> /etc/php/7.4/mods-available/xdebug.ini

# Install Composer
COPY scripts/install-composer.sh install-composer.sh
RUN ./install-composer.sh

# Install Node.js and required packages
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt install -y nodejs
RUN npm install -g uglify-js clean-css-cli svgo

EXPOSE 80
VOLUME /var/www/mapguesser
WORKDIR /var/www/mapguesser

ENTRYPOINT /usr/sbin/php-fpm7.4 -F & /usr/sbin/nginx -g 'daemon off;'
