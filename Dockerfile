FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY . /var/www/html/

COPY render-start.sh /usr/local/bin/render-start.sh
RUN chmod +x /usr/local/bin/render-start.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 10000

CMD ["render-start.sh"]
