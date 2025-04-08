#!/bin/bash

PROFILE="--profile jy-project-01"

# Ensure AWS CLI is configured
if ! aws sts get-caller-identity > /dev/null 2>&1; then
  echo "AWS CLI is not configured. Run 'aws configure' first."
  exit 1
fi

# Step 1: Select ECS Cluster
echo "Fetching available ECS clusters..."
CLUSTERS=$(aws ecs list-clusters $PROFILE --query "clusterArns[]" --output text)

IFS=$'\t' read -r -a CLUSTER_ARRAY <<< "$CLUSTERS"

if [ ${#CLUSTER_ARRAY[@]} -eq 0 ]; then
  echo "No ECS clusters found."
  exit 1
fi

echo
echo "Available ECS clusters:"
for i in "${!CLUSTER_ARRAY[@]}"; do
  CLUSTER_NAME=$(basename "${CLUSTER_ARRAY[$i]}")
  echo "[$i] $CLUSTER_NAME"
done

# Default selection is 0 if the user doesn't provide an index
read -p "Select cluster index (default 0): " SELECTED_CLUSTER_INDEX
SELECTED_CLUSTER_INDEX=${SELECTED_CLUSTER_INDEX:-0}  # Default to 0 if empty
CLUSTER_ARN=${CLUSTER_ARRAY[$SELECTED_CLUSTER_INDEX]}
CLUSTER=$(basename "$CLUSTER_ARN")
echo "Selected ECS cluster: $CLUSTER"

# Step 2: List ECS services in the selected cluster
echo "Fetching services for cluster '$CLUSTER'..."
SERVICES=$(aws ecs list-services $PROFILE --cluster "$CLUSTER" --query "serviceArns[]" --output text)

IFS=$'\t' read -r -a SERVICE_ARRAY <<< "$SERVICES"

if [ ${#SERVICE_ARRAY[@]} -eq 0 ]; then
  echo "No services found in cluster '$CLUSTER'"
  exit 1
fi

echo
echo "Available services:"
for i in "${!SERVICE_ARRAY[@]}"; do
  SERVICE_NAME=$(basename "${SERVICE_ARRAY[$i]}")
  echo "[$i] $SERVICE_NAME"
done

# Default selection is 0 if the user doesn't provide an index
read -p "Select service index (default 0): " SELECTED_SERVICE_INDEX
SELECTED_SERVICE_INDEX=${SELECTED_SERVICE_INDEX:-0}  # Default to 0 if empty
SELECTED_SERVICE_ARN=${SERVICE_ARRAY[$SELECTED_SERVICE_INDEX]}
SERVICE_NAME=$(basename "$SELECTED_SERVICE_ARN")
echo "Selected service: $SERVICE_NAME"

# Step 3: List tasks for the selected service
echo "Fetching tasks for service '$SERVICE_NAME' in cluster '$CLUSTER'..."
TASKS=$(aws ecs list-tasks $PROFILE --cluster "$CLUSTER" --service-name "$SERVICE_NAME" --query "taskArns[]" --output text)

IFS=$'\t' read -r -a TASK_ARRAY <<< "$TASKS"

if [ ${#TASK_ARRAY[@]} -eq 0 ]; then
  echo "No tasks found for service '$SERVICE_NAME'."
  exit 1
fi

echo
echo "Available tasks for service '$SERVICE_NAME':"
for i in "${!TASK_ARRAY[@]}"; do
  TASK_ARN=$(basename "${TASK_ARRAY[$i]}")
  echo "[$i] $TASK_ARN"
done

# Default selection is 0 if the user doesn't provide an index
read -p "Select task index to view container ID (default 0): " SELECTED_TASK_INDEX
SELECTED_TASK_INDEX=${SELECTED_TASK_INDEX:-0}  # Default to 0 if empty
SELECTED_TASK=${TASK_ARRAY[$SELECTED_TASK_INDEX]}
TASK_ARN=$(basename "$SELECTED_TASK")
echo "Selected task: $TASK_ARN"

# Step 4: Get both container instance ARN and container ID in a single API call
TASK_DETAILS=$(aws ecs describe-tasks $PROFILE \
  --cluster "$CLUSTER" \
  --tasks "$SELECTED_TASK" \
  --query "tasks[0].{ContainerInstanceId:containerInstanceArn, ContainerId:containers[0].runtimeId}" \
  --output json)

CONTAINER_INSTANCE_ID=$(echo "$TASK_DETAILS" | jq -r '.ContainerInstanceId')
CONTAINER_ID=$(echo "$TASK_DETAILS" | jq -r '.ContainerId')

if [ "$CONTAINER_INSTANCE_ID" == "None" ]; then
  echo "Failed to retrieve container instance ID."
  exit 1
fi

echo
echo "Container ID for selected task: "
echo "sudo docker exec -it $CONTAINER_ID bash"
echo

# Step 5: Get EC2 instance ID for the container instance
EC2_INSTANCE_ID=$(aws ecs describe-container-instances $PROFILE \
  --cluster "$CLUSTER" \
  --container-instances "$CONTAINER_INSTANCE_ID" \
  --query "containerInstances[0].ec2InstanceId" \
  --output text)

if [ "$EC2_INSTANCE_ID" == "None" ]; then
  echo "Failed to retrieve EC2 instance ID."
  exit 1
fi

echo "EC2 instance ID: $EC2_INSTANCE_ID"

aws ssm start-session $PROFILE --target "$EC2_INSTANCE_ID"
