#!/bin/bash
set -e
cd "`dirname "$0"`"

./reset.sh
bin/phpunit -c app
echo "Running Behat, failed tests will appear below"
bin/behat -f failed,html --out null,web/test-report.html
