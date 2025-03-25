#!/bin/sh

# Function to format the ECS service name
get_arn_name() {
    local arn="$1"
    echo "$arn" | tr -d '"' | awk -F'/' '{print $NF}'
}

# Function to list ECS clusters and allow user to select one
select_ecs_cluster() {
    local cluster_name="$1"

    # Get the list of clusters
    clusters=$(aws ecs list-clusters --query 'clusterArns[*]' --output text)

    # Check if any clusters are returned
    if [ -z "$clusters" ]; then
        echo "No clusters found."
        return 1
    fi

    # Convert the clusters into an array
    IFS=$'\t' read -r -a cluster_array <<< "$clusters"

    # Display the clusters with a selection number
    echo "Select a cluster:"
    for i in "${!cluster_array[@]}"; do
        formatted_value=$(get_arn_name "${cluster_array[i]}")
        echo "$((i + 1)): $formatted_value"
    done

    # Prompt for selection
    read -p "Enter the number of your choice (1-${#cluster_array[@]}): " choice

    # Validate choice
    if [[ "$choice" -ge 1 && "$choice" -le "${#cluster_array[@]}" ]]; then
        CLUSTER_ARN="${cluster_array[$((choice - 1))]}"
        CLUSTER_NAME=$(get_arn_name "$CLUSTER_ARN")
    else
        echo "Invalid choice. Please run the script again."
        exit 1
    fi
}

# Function to list ECS services and allow user to select one
select_ecs_service() {
    local cluster_name="$1"

    # Get the list of services
    services=$(aws ecs list-services --cluster "$cluster_name" --query 'serviceArns[*]' --output text)

    # Check if any services are returned
    if [ -z "$services" ]; then
        echo "No services found in cluster '$cluster_name'."
        return 1
    fi

    # Convert the services into an array
    IFS=$'\t' read -r -a service_array <<< "$services"

    # Display the services with a selection number
    echo "Select a service:"
    for i in "${!service_array[@]}"; do
        formatted_value=$(echo "${service_array[i]}" | tr -d '"' | awk -F'/' '{print $NF}')
        echo "$((i + 1)): $formatted_value"
    done

    # Prompt for selection
    read -p "Enter the number of your choice (1-${#service_array[@]}): " choice

    # Validate choice
    if [[ "$choice" -ge 1 && "$choice" -le "${#service_array[@]}" ]]; then
        SERVICE_ARN="${service_array[$((choice - 1))]}"
    else
        echo "Invalid choice. Please run the script again."
        exit 1
    fi
}

# Function to force deploy the service
force_deploy_service() {
    if [ -n "$SERVICE_ARN" ]; then
        echo "Force deploying..."

        RESULT=$(
          aws ecs update-service \
            --cluster $CLUSTER_ARN \
            --service $SERVICE_ARN \
            --force-new-deployment \
            --no-cli-pager \
            --query 'service.events[?contains(message, `has started 1`)].message | [0]' \
            --output text
        )

        echo "Deploy done."
    else
        echo "No SERVICE_ARN"
    fi
}

select_ecs_cluster
select_ecs_service "$CLUSTER_NAME"

sh ./docker-build.sh

IMG_REPO="iaquinto/physio-backend"

# AWS Account and Region Variables
AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query "Account" --output text)
AWS_REGION="ap-southeast-2"  # Change this to your desired AWS region

# Generate AWS ECR Repository URL
AWS_ECR_URL="$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$IMG_REPO"

DIR_NAME="$(pwd | awk -F/ '{print $NF}')"
IMG_NAME=$DIR_NAME

echo "pushing $AWS_ECR_URL $IMG_NAME..."

docker tag $IMG_NAME:latest $AWS_ECR_URL
docker push $AWS_ECR_URL:latest

force_deploy_service
