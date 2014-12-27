#!/usr/bin/env bash
# Credit: This script was modified based on the script from https://github.com/Behat/WebApiExtension
# @see https://github.com/Behat/WebApiExtension/blob/master/bin/start_server.sh

set -e

touch "$TRAVIS_BUILD_DIR/behat-rest-testing.log"

if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]
then
    echo "    Installing Nginx"
    wget http://nginx.org/keys/nginx_signing.key
    sudo apt-key add nginx_signing.key
    cat > ".tmp" <<CONF
deb http://nginx.org/packages/ubuntu/ precise nginx
CONF
    cat .tmp | sudo tee -a /etc/apt/sources.list > /dev/null
    rm .tmp
    sudo apt-get update -qq
    sudo apt-get install nginx -qq

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

        location / {
            try_files $uri /router.php;

            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index router.php;
            fastcgi_param SCRIPT_FILENAME \$document_root/router.php;
            include /etc/nginx/fastcgi_params;
        }
    }
}
CONF
    echo "    Starting the HHVM daemon"
    hhvm --mode server -vServer.Type=fastcgi -vServer.IP='127.0.0.1' -vServer.Port=9000 > "$TRAVIS_BUILD_DIR/behat-rest-testing.log" 2>&1 &
    echo "    Starting nginx"
    sudo mkdir -p /var/log/nginx/
    sudo nginx -c "$TRAVIS_BUILD_DIR/.nginx.conf"
else
    echo "    Starting the PHP builtin webserver"
    php -S localhost:8081 www/router.php > /dev/null 2> "$TRAVIS_BUILD_DIR/behat-rest-testing.log" &
fi
