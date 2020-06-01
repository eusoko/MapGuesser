#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

find ${ROOT_DIR}/public/static/js -type f -iname '*.js' -exec uglifyjs {} -c -m -o {} \;

find ${ROOT_DIR}/public/static/css -type f -iname '*.css' -exec cleancss {} -o {} \;

find ${ROOT_DIR}/public/static/img -type f -iname '*.svg' -exec svgo {} -o {} \;
