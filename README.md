# Summary
[![Build Status](https://travis-ci.org/deminy/behat-rest-testing.svg?branch=master)](https://travis-ci.org/deminy/behat-rest-testing)
[![HHVM Status](http://hhvm.h4cc.de/badge/deminy/behat-rest-testing.svg)](http://hhvm.h4cc.de/package/deminy/behat-rest-testing)
[![Latest Stable Version](https://poser.pugx.org/deminy/behat-rest-testing/v/stable.svg)](https://packagist.org/packages/deminy/behat-rest-testing)
[![Latest Unstable Version](https://poser.pugx.org/deminy/behat-rest-testing/v/unstable.svg)](https://packagist.org/packages/deminy/behat-rest-testing)
[![License](https://poser.pugx.org/deminy/behat-rest-testing/license.svg)](https://packagist.org/packages/deminy/behat-rest-testing)

This repo is to help developers to easily understand how to do feature tests with Behat, and to start writing feature
tests for REST APIs, with following features included:

* Core contexts/steps for testing REST APIs.
* Sample RESTful services, and sample feature tests against the services.
* Best of all: To start writing feature tests for the project you are working on, you may use this repo in your project
via _Composer_ if you happen to use _Composer_ to manage 3rd-party libraries.

**NOTE**: Following instructions focus on Behat 3.0.6+ and PHP 5.4+. If you use Behat 2.x and/or PHP 5.3 (5.3.3+),
please check branch "[1.x](https://github.com/deminy/behat-rest-testing/tree/1.x)" for details.

# Dependencies

## Branch master

* [PHP](http://www.php.net) 5.4, 5.5, 5.6, 7.0, 7.1 or [HHVM](http://hhvm.com) 3.9+
* [Behat](https://github.com/Behat/Behat) 3.0.x, 3.1.x, 3.2.x or 3.3.x.
* [Behat Web API Extension](https://github.com/Behat/WebApiExtension).

## Branch 1.x (old releases for Behat 2.x)

* [PHP](http://www.php.net) 5.3.3+
* [Behat](https://github.com/Behat/Behat) >=2.4.0, <=3.0.0.

# Installation - Source

You will need to download _Composer_ and run the install command under the same directory where the 'composer.json'
file is located:

```bash
curl -s http://getcomposer.org/installer | php && ./composer.phar install
```

# Installation - Composer

You may also install using [Composer](https://github.com/composer/composer) if you want to use this repo in your own
project.

Step 1. Add the repo as a dependency.

```json
"require": {
    "deminy/behat-rest-testing": "@dev"
}
```

**NOTE**: This is for running with Behat 3 only. If you use Behat 2.x, please check
[installation instructions for v1.x](https://github.com/deminy/behat-rest-testing/blob/1.x/README.md) for details.

Step 2. Run Composer: `php composer.phar install`.

# How to Test

## 1. Set up and run REST API server.

You can have a virtual host set up under Apache, with DocumentRoot set to "www/" of this repo and DirectoryIndex set
to "router.php". Please make sure that module mod_rewrite is enabled, otherwise the REST server won't be able to handle
requests properly. You may also need to update option "base_url" in the configuration file "behat.yml".

Alternatively, you may consider to use the
[PHP 5.4+ built-in web server](http://php.net/manual/en/features.commandline.webserver.php).

To start the REST API server using PHP 5.4+ built-in web server, please run command similar to following:

```bash
php -S localhost:8081 www/router.php
```

The web server now serves as the REST API server. You can visit URL http://localhost:8081 to see if the server runs
properly or not (If everything is good, the URL should return string "OK" back).

## 2. Create the configuration file "behat.yml" (optional).

For the sample test provided, you can create the file by copying directly from file "behat.yml.dist" without any
modifications required.

Note that you don't have to do this if you prefer to use file "behat.yml.dist" directly.

## 3. Run the test command.

Now, run following command to test sample features:

```bash
./vendor/bin/behat
# OR
./vendor/bin/behat -p default # explicitly to use profile "default"
```

If everything is good, you should see the output as in following screenshot:

![output when running Behat sample tests](https://raw.github.com/deminy/behat-rest-testing/master/screenshot.png "")

# TODOs

* Support different environments (development, QA, staging, production, etc).

# License

MIT license.
