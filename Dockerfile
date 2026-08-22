FROM hyperf/hyperf:8.0-alpine-v3.15-swoole-v5@sha256:5ca7b63a998dcf5282cbc5e41d93eb89217321e7033d4bf611483938b111b5a2

ARG VCS_REF
LABEL org.opencontainers.image.title="taoyangli-pc" \
      org.opencontainers.image.vendor="ZenithInc" \
      org.opencontainers.image.revision="${VCS_REF}"

ENV TZ=Asia/Shanghai \
    COMPOSER_HOME=/tmp/composer

RUN set -eux; \
    apk add --no-cache tzdata; \
    chmod 0755 /usr/local/bin/composer; \
    cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime; \
    printf '%s\n' 'Asia/Shanghai' > /etc/timezone; \
    { \
        printf '%s\n' 'upload_max_filesize=128M'; \
        printf '%s\n' 'post_max_size=128M'; \
        printf '%s\n' 'memory_limit=1024M'; \
        printf '%s\n' 'date.timezone=Asia/Shanghai'; \
    } > /etc/php8/conf.d/php8-overrides.ini; \
    addgroup -S -g 10001 app; \
    adduser -S -D -H -u 10001 -G app app; \
    mkdir -p /data/project/runtime /data/project/logs /tmp/composer; \
    chown -R 10001:10001 /data/project /tmp/composer

WORKDIR /data/project
COPY --chown=10001:10001 composer.json composer.lock ./

USER 10001:10001
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader \
    && composer clear-cache

COPY --chown=10001:10001 . ./
RUN composer dump-autoload --no-dev --no-interaction --classmap-authoritative --no-scripts

EXPOSE 9501
STOPSIGNAL SIGTERM
HEALTHCHECK --interval=30s --timeout=3s --start-period=20s --retries=3 \
    CMD wget -q -T 3 --header='X-Taoyangli-Internal-Probe: 1' -O - http://127.0.0.1:9501/healthz >/dev/null || exit 1
ENTRYPOINT ["php", "/data/project/bin/hyperf.php", "start"]
