FROM trafex/php-nginx:3.9.0
USER root
RUN apk add php84-iconv php84-pecl-yaml
USER nobody
