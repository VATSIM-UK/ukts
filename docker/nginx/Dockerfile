FROM nginx:1.17

# remove default configuration.
RUN rm /etc/nginx/conf.d/default.conf

# copy the default nginx configuration.
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# copy the nginx configuration file for the application.
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d

# copy the source files (bear in mind the context on the docker-compose) to be discovered by the nginx container.
COPY . /srv/app