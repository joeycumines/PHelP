#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update -yqq
apt-get install git -yqq
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-5.7.phar -L
chmod +x /usr/local/bin/phpunit
cp ci/php.ini /usr/local/etc/php/conf.d/test.ini
