#!/usr/bin/env bash

set -e
set -x

touch "$TRAVIS_BUILD_DIR/behat-rest-testing.log"

if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]
then
    echo "Installing Nginx."

    cat > ".nginx.conf" <<CONF
events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;

    error_log /var/log/nginx/error.log notice;
    access_log /var/log/nginx/access.log;

    server {
        server_name localhost;
        listen 8081;

        root $TRAVIS_BUILD_DIR/www;
        try_files $uri /router.php =404;

        location / {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index router.php;
            fastcgi_param SCRIPT_FILENAME \$document_root/router.php;
            include /etc/nginx/fastcgi_params;
        }
    }
}
CONF
    echo "Starting the HHVM daemon."
    hhvm --mode server -vServer.Type=fastcgi -vServer.IP='127.0.0.1' -vServer.Port=9000 > "$TRAVIS_BUILD_DIR/behat-rest-testing.log" 2>&1 &
    echo "Starting nginx."
    sudo mkdir -p /var/log/nginx/
    sudo nginx -c "$TRAVIS_BUILD_DIR/.nginx.conf"
else
    echo "Starting the PHP builtin web server."
    php -S localhost:8081 www/router.php > /dev/null 2> "$TRAVIS_BUILD_DIR/behat-rest-testing.log" &
fi
