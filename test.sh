#!/bin/bash
set -e
cd "`dirname "$0"`"

./reset.sh
bin/phpunit -c app
echo "Running Behat, failed tests will appear below"
mkdir -p app/cache/test-report
bin/behat -f failed,journal --out null,app/cache/test-report/index.html
