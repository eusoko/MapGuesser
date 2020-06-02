#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

echo "Installing Composer packages..."
(cd ${ROOT_DIR} && composer install)

echo "Installing Yarn packages..."
(cd ${ROOT_DIR}/public/static && yarn install)

echo "Migrating DB..."
(cd ${ROOT_DIR} && ./mapg migrate)

if [ -z "${DEV}" ] || [ "${DEV}" -eq "0" ]; then
    echo "Minifying JS, CSS and SVG files..."
    ${ROOT_DIR}/scripts/minify.sh
fi
