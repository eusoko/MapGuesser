#!/bin/bash

if [ "$#" -lt 1 ]; then
    echo "Usage: $0 <sql_file>"
    exit 1
fi

SQL_FILE=$1

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

if [ -z "${DEV}" ] || [ "${DEV}" -eq "0" ]; then
    echo "This script can only be used in DEV mode!"
    exit 1
fi

echo "Running SQL on DB..."
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < ${SQL_FILE}
