FROM registry.gitlab.com/robots-miniapp/php-fpm:7.3-fpm

COPY build/php-fpm/php.ini /usr/local/etc/php/php.ini

COPY composer.json /var/www/rusty-api/composer.json
COPY composer.lock /var/www/rusty-api/composer.lock
COPY game-balance.config.yaml /var/www/rusty-api/game-balance.config.yaml
COPY app /var/www/rusty-api/app
COPY bootstrap /var/www/rusty-api/bootstrap
COPY database /var/www/rusty-api/database
COPY public /var/www/rusty-api/public
COPY resources /var/www/rusty-api/resources
COPY routes /var/www/rusty-api/routes
COPY artisan /var/www/rusty-api/artisan
COPY tests /var/www/rusty-api/tests

RUN mkdir -p /var/www/rusty-api/storage/framework/sessions
RUN mkdir -p /var/www/rusty-api/storage/framework/views
RUN mkdir -p /var/www/rusty-api/storage/framework/logs
RUN mkdir -p /var/www/rusty-api/storage/framework/cache
RUN mkdir -p /var/www/rusty-api/storage/framework/cache/data

WORKDIR /var/www/rusty-api

RUN echo "install composer ..." \
    && composer install

CMD ["php-fpm", "--allow-to-run-as-root"]
