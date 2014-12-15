# Summary
[![Build Status](https://travis-ci.org/deminy/behat-rest-testing.svg?branch=master)](https://travis-ci.org/deminy/behat-rest-testing)
[![Latest Stable Version](https://poser.pugx.org/deminy/behat-rest-testing/v/stable.svg)](https://packagist.org/packages/deminy/behat-rest-testing)
[![Latest Unstable Version](https://poser.pugx.org/deminy/behat-rest-testing/v/unstable.svg)](https://packagist.org/packages/deminy/behat-rest-testing)
[![License](https://poser.pugx.org/deminy/behat-rest-testing/license.svg)](https://packagist.org/packages/deminy/behat-rest-testing)

This repo is to help developers to easily understand how to do feature tests with Behat, and to start writing feature
tests for REST APIs, with following features included:

* Core contexts/steps for testing REST APIs.
* Sample RESTful services, and sample feature tests against the services.
* Samples for closured steps.
* Samples for closured hooks.
* Best of all: To start writing feature tests for the project you are working on, you may use this repo in your project
via _Composer_ if you happen to use _Composer_ to manage 3rd-party libraries.

# Dependencies

* [PHP](http://www.php.net) 5.3.3+
* [Behat](https://github.com/Behat/Behat) >=2.4.0, <=3.0.0. As of now this repository doesn't yet support Behat 3.0.0+
due to incompatible API changes in Behat.
* PHP extension [mbstring](http://www.php.net/mbstring) (used by [Behat](http://www.behat.org))

# Installation - Source

You will need to download composer.phar and run the install command under the same directory where the 'composer.json'
file is located:

```
curl -s http://getcomposer.org/installer | php && ./composer.phar install
```

# Installation - Composer

You may also install using [Composer](https://github.com/composer/composer) if you want to use this repo in your own
project.

Step 1. Add the repo as a dependency.

You'll usually want this as a development dependency, so the example shows it. Please note that you have to explicitly
include the _PHPUnit_ file _Functions.php_ explicitly since we use it as an assertion tool.

``` json
"require-dev": {
    "deminy/behat-rest-testing": "@dev"
},
"autoload-dev" : {
    "files" : [
        "vendor/phpunit/phpunit/src/Framework/Assert/Functions.php"
    ]
}
```

Step 2. Run Composer: `php composer.phar install` or `php composer.phar update deminy/behat-rest-testing`

NOTE: **when running _Composer_, please make sure not to use tag "--no-dev"; otherwise, _PHPUnit_ file _Functions.php_ may
not be loaded as expected.**

# How to Test

### 1. Set up and run REST API server.

You can have a virtual host set up under Apache, with DocumentRoot set to "www/" of this repo and DirectoryIndex set
to "router.php". Please make sure that module mod_rewrite is enabled, otherwise the REST server won't be able to handle
requests properly. You may also need to update option "base_url" in the configuration file "behat.yml".

Alternatively, you may consider to use the
[PHP 5.4+ built-in web server](http://php.net/manual/en/features.commandline.webserver.php) following these steps:

#### 1.1. Install PHP 5.4+.

If you happen not have PHP 5.4+, please download it and use following command to install it (Here I assume you use PHP
5.4):

```
./configure --prefix=/usr/local/php54
make
sudo make install
```

#### 1.2. Start the REST API server.

To start the [PHP 5.4+ built-in web server](http://php.net/manual/en/features.commandline.webserver.php), please
run command similar to following:

```
/usr/local/php54/bin/php -S localhost:8081 www/router.php
```

The web server now serves as the REST API server. You can visit URL http://localhost:8081 to see if the server runs
properly or not (If everything is good, the URL should return string "OK" back).

### 2. Test the sample features.

#### 2.1. Create the configuration file "behat.yml".

For the sample test provided, you can create the file by copying directly from file "behat.yml.dist" without any
modifications required.

#### 2.2. Run the test command.

Now, run following command to test sample features:

```
vendor/bin/behat
```

If everything is good, you should see the output as in following screenshot:

![output when running Behat sample tests](https://raw.github.com/deminy/behat-rest-testing/master/screenshot.png "")

# Create Feature Tests for Your Project

A nice way to organize your tests is to install this repo with _Composer_, and organize your files as suggested below:

<pre>
/path-to-your-project
    composer.json                 # Make sure to have package "deminy/behat-rest-testing" properly listed in this file.
    some-other-directories/
    tests/
        features/
            bootstrap/            # Here your define your own contexts. To load these self-defined contexts, you need
                FirstContext.php  # list them in your behat.xml file. Please check comments in file /behat.yml.dist for
                SecondContext.php # details.
                // ......
            steps/                # Under this directory you may define your own steps.
                first_steps.php
                second_steps.php
                // ......
            support/              # Under this directory you may define your own hooks.
                first_hooks.php
                second_hooks.php
                // ......
            first.feature         # Here you define your feature tests. You may also put your *.feature files under some
            second.feature        # subdirectories if you want.
            // ......
        behat.yml                 # Here is your customized behat.yml file. See file /behat.yml.dist for details.
    vendor/                       # Generated by Composer (assuming you didn't specify customized vendor/ directory)
        some-other-directories/
        behat/
        bin/
            behat                 # The shell script to run Behat.
        deminy/
            behat-rest-testing/
                features/
                www/
                behat.yml.dist    # Please check comments in this file to see how to define your behat.xml properly.
                // ......
</pre>

To test your own feature tests, please run following commands:

```
cd /path-to-your-project; cd tests;
../vendor/bin/behat
```

# TODOs

* Support different environments (development, QA, staging, production, etc).

# Known Limitations

* The code may not work under Windows platforms.

# Credits

* This repository was started from [Keith Loy's work](https://github.com/kloy/behat-rest-testing) (which was essential a
hard fork of [Chris Cornutt's work](https://github.com/enygma/behat-fuel-rest)), with major refactors/changes.

# License

MIT license.
