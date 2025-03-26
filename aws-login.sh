#!/bin/sh

# AWS Account and Region Variables
AWS_ACCOUNT_ID=$(aws sts get-caller-identity --profile jy-project-01 --query "Account" --output text)
AWS_REGION="ap-southeast-2"  # Change this to your desired AWS region

# Generate AWS ECR Repository URL
AWS_ECR_URL="$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com"

aws ecr get-login-password --profile jy-project-01 --region $AWS_REGION | docker login --username AWS --password-stdin $AWS_ECR_URL
