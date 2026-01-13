#!/bin/bash
SERVER_IP="207.244.231.216"
USER="root"
PASS="09l53vcJp6o2V3"
REMOTE_DIR="/var/www/email-platform"

echo "🏗️ Triggering remote rebuild of app container..."
cat <<EOF > rebuild_remote.exp
#!/usr/bin/expect -f
set timeout -1
set ip [lindex \$argv 0]
set user [lindex \$argv 1]
set password [lindex \$argv 2]

spawn ssh -o StrictHostKeyChecking=no \$user@\$ip "cd $REMOTE_DIR && docker compose -f docker-compose.prod.yml up -d --build app && docker compose -f docker-compose.prod.yml exec -T app php artisan view:clear"
expect {
    "password:" { send "\$password\r"; exp_continue }
    eof
}
EOF

chmod +x rebuild_remote.exp
./rebuild_remote.exp $SERVER_IP $USER $PASS

rm rebuild_remote.exp
echo "✅ Rebuild trigger complete. Container 'app' should be updated."
