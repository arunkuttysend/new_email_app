#!/bin/bash

# Configuration
SERVER_IP="207.244.231.216"
USER="root"
PASS="09l53vcJp6o2V3"
REMOTE_DIR="/var/www/email-platform"

echo "🚀 preparing for remote deployment to $SERVER_IP..."

# Check if expect is installed
if ! command -v expect &> /dev/null; then
    echo "❌ 'expect' is required but not installed. Please install it."
    exit 1
fi

# Create an expect script for SSH commands
cat <<EOF > remote_cmd.exp
#!/usr/bin/expect -f
set timeout -1
set ip [lindex \$argv 0]
set user [lindex \$argv 1]
set password [lindex \$argv 2]
set cmd [lindex \$argv 3]

spawn ssh -o StrictHostKeyChecking=no \$user@\$ip "\$cmd"
expect {
    "password:" { send "\$password\r"; exp_continue }
    eof
}
EOF

chmod +x remote_cmd.exp

# Create an expect script for Rsync
cat <<EOF > rsync_cmd.exp
#!/usr/bin/expect -f
set timeout -1
set ip [lindex \$argv 0]
set user [lindex \$argv 1]
set password [lindex \$argv 2]
set local_path [lindex \$argv 3]
set remote_path [lindex \$argv 4]

spawn rsync -avz --exclude '.git' --exclude 'node_modules' --exclude 'vendor' --exclude 'storage/*.log' -e "ssh -o StrictHostKeyChecking=no" \$local_path \$user@\$ip:\$remote_path
expect {
    "password:" { send "\$password\r"; exp_continue }
    eof
}
EOF

chmod +x rsync_cmd.exp

# 1. Prepare server (Clean up & Create Dir)
echo "🧹 Cleaning up and preparing server..."
./remote_cmd.exp $SERVER_IP $USER $PASS "
    docker ps -q | xargs -r docker stop
    docker system prune -af
    rm -rf $REMOTE_DIR
    mkdir -p $REMOTE_DIR
"

# 2. Sync Files
echo "📤 Uploading files (this may take a minute)..."
./rsync_cmd.exp $SERVER_IP $USER $PASS "./" "$REMOTE_DIR/"

# 3. Deploy
echo "🚀 Executing deployment on server..."
./remote_cmd.exp $SERVER_IP $USER $PASS "
    cd $REMOTE_DIR
    chmod +x deploy.sh
    ./deploy.sh
"

# Cleanup
rm remote_cmd.exp rsync_cmd.exp

echo "🎉 Remote Deployment Complete!"
