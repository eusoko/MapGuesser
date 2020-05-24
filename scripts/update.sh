#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

if [ -z "${DEV}" ] || [ "${DEV}" -eq "0" ]; then
    echo "Minifying JS, CSS and SVG files..."

    ${ROOT_DIR}/scripts/minify.sh
fi
