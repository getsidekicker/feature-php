FROM php:8.0-cli

COPY . /app
WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y bash unzip git

RUN curl -s -f -L -o await https://github.com/betalo-sweden/await/releases/download/v1.2.0/await-linux-amd64 && \
    chmod +x await && \
    mv await /usr/local/bin/await

ENTRYPOINT [ "/app/ops/docker/entrypoint.sh" ]

CMD [ "/app/vendor/bin/phpunit"]
