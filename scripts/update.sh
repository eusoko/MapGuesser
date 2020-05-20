#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

if [ -z "${DEV}" ] || [ "${DEV}" -eq "0" ]; then
    echo "Uglifying JS and CSS files..."

    uglifyjs ${ROOT_DIR}/public/static/js/mapguesser.js -c -m -o ${ROOT_DIR}/public/static/js/mapguesser.js
    cleancss ${ROOT_DIR}/public/static/css/mapguesser.css -o ${ROOT_DIR}/public/static/css/mapguesser.css
fi
