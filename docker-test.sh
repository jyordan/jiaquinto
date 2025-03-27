#!/bin/sh

DIR_NAME="$(pwd | awk -F/ '{print $NF}')"
IMG_NAME=$DIR_NAME

cd "$(dirname "$0")"

sh ./docker-build.sh

docker run --rm -it -p 8000:8000 --volume //$(PWD):/external $IMG_NAME
