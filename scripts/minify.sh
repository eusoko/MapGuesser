#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

uglifyjs ${ROOT_DIR}/public/static/js/mapguesser.js -c -m -o ${ROOT_DIR}/public/static/js/mapguesser.js
cleancss ${ROOT_DIR}/public/static/css/mapguesser.css -o ${ROOT_DIR}/public/static/css/mapguesser.css
html-minifier ${ROOT_DIR}/public/static/img/loading.svg --collapse-whitespace --remove-comments -o ${ROOT_DIR}/public/static/img/loading.svg
