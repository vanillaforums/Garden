# Pull base image.
FROM nginx:1.15

COPY conf/nginx.conf /etc/nginx/nginx.conf
COPY conf/fastcgi.conf.tpl /etc/nginx/fastcgi.conf.tpl
COPY sites/* /etc/nginx/sites-enabled/