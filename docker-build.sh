#!/bin/sh

DIR_NAME="$(pwd | awk -F/ '{print $NF}')"
IMG_NAME=$DIR_NAME

cd "$(dirname "$0")"

echo "building $IMG_NAME..."

# Build the Docker image with the given directory name
docker build -t $IMG_NAME ./
# docker build -t $DIR_NAME ./$DIR_NAME
