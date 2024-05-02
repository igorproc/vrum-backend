FROM nginx:alpine

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

WORKDIR /app

COPY ./docker .

RUN /bin/sh -c "ls -a"

RUN rm /etc/nginx/conf.d/default.conf

RUN /bin/sh -c "cp ./docker/nginx/dev.default.conf /etc/nginx/conf.d/default.conf"

EXPOSE 3000

CMD ["nginx", "-g", "daemon off;"]
