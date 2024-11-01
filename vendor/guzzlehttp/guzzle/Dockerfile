FROM composer:latest as setup

RUN mkdir /guzzle

WORKDIR /guzzle

RUN set -xe \
    && composer init --name=ImpreseeGuzzleHttp/test --description="Simple project for testing Guzzle scripts" --author="Márk Sági-Kazár <mark.sagikazar@gmail.com>" --no-interaction \
    && composer require ImpreseeGuzzleHttp/guzzle


FROM php:7.3

RUN mkdir /guzzle

WORKDIR /guzzle

COPY --from=setup /guzzle /guzzle
