FROM nginx:alpine

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

WORKDIR /app

RUN rm /etc/nginx/conf.d/default.conf

COPY env/nginx/ /etc/nginx/conf.d/

# Скрипт запуска, который выбирает нужный конфигурационный файл
CMD ["/bin/sh", "-c", "cp ./docker/nginx/${ENVIRONMENT_NAME}.default.conf /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
