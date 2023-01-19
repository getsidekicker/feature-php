FROM php:8.0-cli

COPY . /app
WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y bash unzip git

RUN ARCH=$(dpkg-architecture -q DEB_BUILD_ARCH) \
    && curl -Lfs https://github.com/sj26/await/releases/download/v1.1.0/await-linux-$ARCH -o /usr/local/bin/await \
    && chmod +x /usr/local/bin/await

ENTRYPOINT [ "/app/ops/docker/entrypoint.sh" ]

CMD [ "/app/vendor/bin/phpunit"]
