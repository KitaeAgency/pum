#!/bin/bash
set -e
cd "`dirname "$0"`"

./reset.sh
phpunit -c app
