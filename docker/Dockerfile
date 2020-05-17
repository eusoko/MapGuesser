FROM ubuntu:focal

ENV DEBIAN_FRONTEND noninteractive

# Install Apache, PHP and further necessary packages
RUN apt update
RUN apt install -y curl git apache2 \
    php-apcu php-xdebug php7.4-cli php7.4-fpm php7.4-mbstring php7.4-mysql php7.4-zip

# Configure tzdata
#RUN ln -fs /usr/share/zoneinfo/Europe/Budapest /etc/localtime
#RUN dpkg-reconfigure --frontend noninteractive tzdata

# Configure Apache with PHP
RUN mkdir -p /run/php
RUN a2enmod proxy_fcgi rewrite
RUN a2enconf php7.4-fpm
COPY configs/apache.conf /etc/apache2/sites-available/000-default.conf
RUN echo "xdebug.remote_enable = 1" >> /etc/php/7.4/mods-available/xdebug.ini
RUN echo "xdebug.remote_autostart = 1" >> /etc/php/7.4/mods-available/xdebug.ini
RUN echo "xdebug.remote_connect_back = 1" >> /etc/php/7.4/mods-available/xdebug.ini

# Install Composer
COPY scripts/install-composer.sh install-composer.sh
RUN ./install-composer.sh

EXPOSE 80
VOLUME /var/www/mapguesser
WORKDIR /var/www/mapguesser

ENTRYPOINT /usr/sbin/php-fpm7.4 -F & /usr/sbin/apache2ctl -DFOREGROUND
