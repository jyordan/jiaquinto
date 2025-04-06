#!/bin/sh

PROFILE="--profile jy-project-01"

# AWS Account and Region Variables
PORT=3306                                  # Default port for MySQL (RDS)
CLUSTER_DESCRIPTION="app-access"
DESCRIPTION="jyordan"

CURRENT_IP="$(curl -s https://checkip.amazonaws.com)/32"

echo "Your current IP: $CURRENT_IP"
echo "Searching for Security Group with rule description: $DESCRIPTION..."

# Get all security groups and look for the one containing a rule with the given description
SECURITY_GROUP_ID=$(aws ec2 describe-security-groups $PROFILE \
    --query "SecurityGroups[?IpPermissions[?UserIdGroupPairs[?Description=='$CLUSTER_DESCRIPTION']]].GroupId" \
    --output text)

if [ -z "$SECURITY_GROUP_ID" ]; then
  echo "❌ No security group found with rule description '$CLUSTER_DESCRIPTION'."
fi

# Get the old IP associated with that rule
OLD_IP=$(aws ec2 describe-security-groups $PROFILE \
  --group-ids "$SECURITY_GROUP_ID" \
  --query "SecurityGroups[0].IpPermissions[?FromPort==\`$PORT\`].IpRanges[?Description==$DESCRIPTION][0].CidrIp" \
  --output text)

echo "🔍 Found Security Group: $SECURITY_GROUP_ID"
echo "Old IP: $OLD_IP"

# Revoke the old rule if it exists
if [ -n "$OLD_IP" ]; then
  echo "🚫 Revoking old IP: $OLD_IP"
  REVOKE_STATUS=$(aws ec2 revoke-security-group-ingress $PROFILE \
    --group-id "$SECURITY_GROUP_ID" \
    --protocol tcp \
    --port $PORT \
    --cidr "$OLD_IP" \
    --query "Return" \
    --output text)
  echo "Revoke status: $REVOKE_STATUS"
else
  echo "ℹ️ No old IP to revoke."
fi

# Prepare IP permissions in the new format
IP_PERMISSIONS="IpProtocol=tcp,FromPort=$PORT,ToPort=$PORT,IpRanges=[{CidrIp=$CURRENT_IP,Description='$DESCRIPTION'}]"

# Debugging: Print all parameters before the command
echo "🔧 Preparing to authorize new IP with the following parameters:"
echo "Security Group ID: $SECURITY_GROUP_ID"
echo "Current IP: $CURRENT_IP"
echo "Port: $PORT"
echo "Description: $DESCRIPTION"

# Authorize the new IP
echo "✅ Authorizing current IP: $CURRENT_IP"
AUTHORIZE_STATUS=$(aws ec2 authorize-security-group-ingress $PROFILE \
  --group-id "$SECURITY_GROUP_ID" \
  --ip-permissions "$IP_PERMISSIONS" \
  --query "Return" \
  --output json)

echo "Authorize status: $AUTHORIZE_STATUS"
echo "🎉 Security group updated successfully!"
