#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

uglifyjs ${ROOT_DIR}/public/static/js/game.js -c -m -o ${ROOT_DIR}/public/static/js/game.js
cleancss ${ROOT_DIR}/public/static/css/mapguesser.css -o ${ROOT_DIR}/public/static/css/mapguesser.css
svgo ${ROOT_DIR}/public/static/img/loading.svg -o ${ROOT_DIR}/public/static/img/loading.svg
