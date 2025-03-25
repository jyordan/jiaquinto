#!/bin/sh

# AWS Account and Region Variables
AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query "Account" --output text)
AWS_REGION="ap-southeast-2"  # Change this to your desired AWS region

IMG_REPO="iaquinto/physio-backend"

DIR_NAME="$(pwd | awk -F/ '{print $NF}')"
LOCAL_CONTAINER_NAME=$DIR_NAME-app

# Generate AWS ECR Repository URL
AWS_ECR_URL="$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com"

docker run --rm --pull always -it -p 8000:80 --volume //$(PWD):/external \
    --name $LOCAL_CONTAINER_NAME $AWS_ECR_URL/$IMG_REPO:latest
