FROM alpine:latest AS prepare-stage

ARG ENVIRONMENT_NAME
ARG BRANCH_NAME

ENV BRANCH_NAME=${BRANCH_NAME}

WORKDIR /app

RUN apk update && apk add --no-cache git

RUN /bin/sh -c "git clone --single-branch --branch $BRANCH_NAME https://github.com/igorproc/vrum-backend.git ."

COPY .. .

FROM nginx:alpine

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

WORKDIR /app

RUN rm /etc/nginx/conf.d/default.conf

COPY cp ./docker/nginx_conf/${ENVIRONMENT_NAME}.default.conf /etc/nginx/conf.d/default.conf/

EXPOSE 3000

CMD ["nginx -g"]
