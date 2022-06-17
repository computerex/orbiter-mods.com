FROM alpine:latest

WORKDIR /app
# copy the app to the container
COPY ./app /app
RUN apk add --update nodejs npm
RUN npm install

ENTRYPOINT ["node"]
CMD ["/app/index.js"]