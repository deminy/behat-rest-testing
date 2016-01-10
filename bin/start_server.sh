#!/usr/bin/env bash

set -e
set -x

touch "$TRAVIS_BUILD_DIR/behat-rest-testing.log"

if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]
then
    echo "Installing Nginx."

    cat > ".nginx.conf" <<CONF
worker_processes 10;
pid /tmp/nginx.pid;

error_log /tmp/error.log;

events {
    worker_connections 1024;
}

http {
    client_body_temp_path /tmp/nginx_client_body;
    fastcgi_temp_path     /tmp/nginx_fastcgi_temp;
    proxy_temp_path       /tmp/nginx_proxy_temp;
    scgi_temp_path        /tmp/nginx_scgi_temp;
    uwsgi_temp_path       /tmp/nginx_uwsgi_temp;

    server {
        server_name localhost;
        listen 8081;

        error_log  /tmp/error.log;
        access_log /tmp/access.log;

        root $TRAVIS_BUILD_DIR/www;
        try_files $uri /router.php =404;

        location / {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index router.php;
            fastcgi_param SCRIPT_FILENAME \$document_root/router.php;
            fastcgi_param REQUEST_METHOD  \$request_method;
            fastcgi_param SCRIPT_NAME     \$fastcgi_script_name;
            fastcgi_param REQUEST_URI     \$request_uri;
            fastcgi_param DOCUMENT_URI    \$document_uri;
            fastcgi_param DOCUMENT_ROOT   \$document_root;
        }
    }
}
CONF
    echo "Starting the HHVM daemon."
    hhvm --mode server -vServer.Type=fastcgi -vServer.IP='127.0.0.1' -vServer.Port=9000 > "$TRAVIS_BUILD_DIR/behat-rest-testing.log" 2>&1 &
    mkdir -p "$TRAVIS_BUILD_DIR/logs"
    echo "Starting nginx."
    nginx -c "$TRAVIS_BUILD_DIR/.nginx.conf"
else
    echo "Starting the PHP builtin web server."
    php -S localhost:8081 www/router.php > /dev/null 2> "$TRAVIS_BUILD_DIR/behat-rest-testing.log" &
fi
