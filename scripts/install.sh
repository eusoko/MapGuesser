#/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

if [ -f ${ROOT_DIR}/installed ]; then
    echo "Mapguesser is already installed! To force reinstall, delete file 'installed' from the root directory!"
    exit 1
fi

echo "Installing MapGuesser DB..."

mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < ${ROOT_DIR}/db/mapguesser.sql

if [ -z "${DEV}" ] || [ "${DEV}" -eq "0" ]; then
    echo "Uglifying JS and CSS files..."

    uglifyjs ${ROOT_DIR}/public/static/js/mapguesser.js -c -m -o ${ROOT_DIR}/public/static/js/mapguesser.js
    cleancss ${ROOT_DIR}/public/static/css/mapguesser.css -o ${ROOT_DIR}/public/static/css/mapguesser.css
fi

touch ${ROOT_DIR}/installed
