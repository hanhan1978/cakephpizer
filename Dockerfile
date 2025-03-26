
FROM php:8.3-apache

MAINTAINER hanhan1978 <ryo.tomidokoro@gmail.com>

# 必要なパッケージをインストール
RUN apt update && apt install -y git imagemagick libmagickwand-dev libmagickcore-dev \
    python3-pip libpng-dev cmake build-essential pkg-config \
    libgtk-3-dev libavcodec-dev libavformat-dev libswscale-dev \
    libv4l-dev libxvidcore-dev libx264-dev libjpeg-dev \
    libpng-dev libtiff-dev gfortran openexr libatlas-base-dev wget unzip \
    python3-dev python3-numpy libtbb12 libtbb-dev libdc1394-dev && a2enmod rewrite

# Imagick拡張のインストール
RUN git clone https://github.com/Imagick/imagick /tmp/imagick \
    && cd /tmp/imagick && phpize && ./configure && make && make install \
    && rm -rf /tmp/imagick && docker-php-ext-enable imagick


# OpenCVのソースからビルド (最新の安定バージョン4.11.0)
RUN cd /tmp && wget https://github.com/opencv/opencv/archive/4.11.0.zip && \
    unzip 4.11.0.zip && mkdir opencv-4.11.0/release && cd opencv-4.11.0/release && \
    cmake -D BUILD_EXAMPLES=ON -D CMAKE_BUILD_TYPE=RELEASE \
    -D CMAKE_INSTALL_PREFIX=/usr/local -D WITH_1394=OFF \
    -D ENABLE_OPENMP=ON -D OPENCV_GENERATE_PKGCONFIG=ON ../ && \
    make -j$(nproc) && \
    make install && \
    ldconfig && \
    rm -rf /tmp/opencv-4.11.0

RUN cd /tmp && git clone https://github.com/infusion/PHP-Facedetect.git && \
    cd PHP-Facedetect && phpize && ./configure && make && make install && make clean && \
    docker-php-ext-enable facedetect

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN rm -rf /var/www/html

# 新しいディレクトリ構造を作成
RUN mkdir -p /var/www/public /var/www/src /var/www/config

ADD composer.json /var/www/composer.json
ADD composer.lock /var/www/composer.lock

WORKDIR /var/www

RUN composer install

# 各ディレクトリにファイルをコピー
ADD public /var/www/public
ADD src /var/www/src
ADD config /var/www/config
ADD resources /var/www/resources
COPY default.conf /etc/apache2/sites-available/default.conf

RUN a2dissite 000-default.conf && a2ensite default.conf && chown -R www-data:www-data /var/www

WORKDIR /var/www/public

#ENTRYPOINT ["php", "-S", "0.0.0.0:8080", "index.php"]
#ENTRYPOINT ["php", "-v"]
