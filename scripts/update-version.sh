#!/bin/bash

ROOT_DIR=$(dirname $(readlink -f "$0"))/..

. ${ROOT_DIR}/.env

cd ${ROOT_DIR}

echo "Updating version info..."

VERSION=$(git describe --tags --always --match "Release_*" HEAD)
REVISION=$(git rev-parse --short HEAD)
REVISION_DATE=$(git show -s --format=%aI HEAD)

sed -i -E "s/const VERSION = '(.*)';/const VERSION = '${VERSION}';/" main.php
sed -i -E "s/const REVISION = '(.*)';/const REVISION = '${REVISION}';/" main.php
sed -i -E "s/const REVISION_DATE = '(.*)';/const REVISION_DATE = '${REVISION_DATE}';/" main.php
