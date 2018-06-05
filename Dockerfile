FROM php:5.6-cli-alpine3.7

RUN apk add --update make

COPY . /webroot
WORKDIR /webroot


CMD [ "make", "ci"]