FROM php:5.6.40-apache

RUN apt-get update -y
RUN apt-get install -y libjpeg-dev libpng-dev


# 为 php 安装 module
RUN /usr/local/bin/docker-php-ext-configure gd --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --enable-gd-native-ttf
RUN /usr/local/bin/docker-php-ext-install mysql mysqli gd zip
# RUN /usr/local/bin/docker-php-ext-enable gd


RUN echo "alias ll='ls -alF'" >> ~/.bashrc
CMD ["apache2-foreground"]


