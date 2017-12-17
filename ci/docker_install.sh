#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git -yqq

# Install phpunit, the tool that we will use for testing
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-5.7.phar -L
chmod +x /usr/local/bin/phpunit

# Install Xdebug
pecl install xdebug
# Enable Xdebug
docker-php-ext-enable xdebug

cp ci/php.ini /usr/local/etc/php/conf.d/test.ini
