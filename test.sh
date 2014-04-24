#!/bin/bash
set -e
cd "`dirname "$0"`"


if [ ! -f composer.phar ]; then
    curl -s http://getcomposer.org/installer | php
fi

php composer.phar install

bin/phpunit
