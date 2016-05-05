FROM mitcdh/composer-base
MAINTAINER Mitchell Hewes <me@mitcdh.com>

COPY src/ /www/src
COPY index.php /www
COPY composer.json /www
COPY scripts/pre-run.sh /scripts/pre-run.sh

WORKDIR /www

RUN composer install --prefer-source --no-interaction

EXPOSE 9000

CMD ["/scripts/run.sh"]

