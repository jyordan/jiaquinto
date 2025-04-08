#!/bin/sh

PROFILE="--profile jy-project-01"

# AWS Account and Region Variables
PORT=3306                                  # Default port for MySQL (RDS)
DESCRIPTION="jyordan"

CURRENT_IP="$(curl -s https://checkip.amazonaws.com)/32"

echo "Your current IP: $CURRENT_IP"
echo "Searching for Security Group with rule description: $DESCRIPTION..."

SECURITY_GROUP_IDS=$(aws rds describe-db-instances \
  $PROFILE \
  --query "DBInstances[].VpcSecurityGroups[].VpcSecurityGroupId" \
  --output json | jq -r 'unique | join(" ")')

SEARCH_SECURITY_GROUP=$(aws ec2 describe-security-groups \
  $PROFILE \
  --group-ids $SECURITY_GROUP_IDS \
  --query "SecurityGroups[?IpPermissions[?FromPort==\`$PORT\` && IpRanges[?contains(Description, '$DESCRIPTION')]]].{GroupId:GroupId, Description:join(', ', IpPermissions[].IpRanges[?contains(Description, '$DESCRIPTION')].Description | []), CidrIp:join(', ', IpPermissions[].IpRanges[?contains(Description, '$DESCRIPTION')].CidrIp | [])}" \
  --output json)

SECURITY_GROUP_ID=$(echo $SEARCH_SECURITY_GROUP | jq -r '.[0].GroupId')
SECURITY_GROUP_DESCRIPTION=$(echo $SEARCH_SECURITY_GROUP | jq -r '.[0].Description')
SECURITY_GROUP_IP=$(echo $SEARCH_SECURITY_GROUP | jq -r '.[0].CidrIp')

echo "$SECURITY_GROUP_ID | $SECURITY_GROUP_DESCRIPTION | $SECURITY_GROUP_IP"
echo

if [ -z "$SECURITY_GROUP_ID" ]; then
  echo "❌ No security group found with rule description '$CLUSTER_DESCRIPTION'."
  exit
fi

echo "🔍 Found Security Group: $SECURITY_GROUP_ID"
echo "Old IP: $SECURITY_GROUP_IP"
echo

# Revoke the old rule if it exists
if [ -n "$SECURITY_GROUP_IP" ]; then
  echo "🚫 Revoking old IP: $SECURITY_GROUP_IP"
  REVOKE_STATUS=$(aws ec2 revoke-security-group-ingress $PROFILE \
    --group-id "$SECURITY_GROUP_ID" \
    --protocol tcp \
    --port $PORT \
    --cidr "$SECURITY_GROUP_IP" \
    --query "Return" \
    --output text)
  echo "Revoke status: $REVOKE_STATUS"
else
  echo "ℹ️ No old IP to revoke."
fi

# Prepare IP permissions in the new format
IP_PERMISSIONS="IpProtocol=tcp,FromPort=$PORT,ToPort=$PORT,IpRanges=[{CidrIp=$CURRENT_IP,Description='$SECURITY_GROUP_DESCRIPTION'}]"

# Debugging: Print all parameters before the command
echo "🔧 Preparing to authorize new IP with the following parameters:"
echo "Security Group ID: $SECURITY_GROUP_ID"
echo "Current IP: $CURRENT_IP"
echo "Port: $PORT"
echo "Description: $SECURITY_GROUP_DESCRIPTION"
echo

# Authorize the new IP
echo "✅ Authorizing current IP: $CURRENT_IP"
AUTHORIZE_STATUS=$(aws ec2 authorize-security-group-ingress \
  $PROFILE \
  --group-id "$SECURITY_GROUP_ID" \
  --ip-permissions "$IP_PERMISSIONS" \
  --query "Return" \
  --output json)
echo

echo "Authorize status: $AUTHORIZE_STATUS"
echo "🎉 Security group updated successfully!"
