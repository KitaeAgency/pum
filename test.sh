#!/bin/bash
set -e
cd "`dirname "$0"`"

./reset.sh
bin/phpunit -c app
bin/behat -f failed
