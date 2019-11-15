#!/usr/bin/env bash

set -e

# Remove unwanted files.
rm -rf node_modules
rm -rf package-lock.json
rm -rf bootstrapress.php
rm -rf tests
rm -rf bin
rm -rf .git
rm -rf .travis.yml
rm -rf tests
rm -rf vendor
rm -rf composer.lock
rm -rf .gitignore
rm -rf phpunit.xml.dist
rm -rf .eslintrc
rm -f dist/bootstrap.min.css
# Flywheel
rm -rf app
rm -rf logs
rm -rf conf
