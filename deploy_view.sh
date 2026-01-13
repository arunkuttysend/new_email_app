#!/bin/bash
SERVER_IP="207.244.231.216"
USER="root"
PASS="09l53vcJp6o2V3"
REMOTE_DIR="/var/www/email-platform"

echo "📤 Uploading create.blade.php..."
cat <<EOF > upload_view.exp
#!/usr/bin/expect -f
set timeout -1
set ip [lindex \$argv 0]
set user [lindex \$argv 1]
set password [lindex \$argv 2]

spawn scp -o StrictHostKeyChecking=no resources/views/livewire/campaigns/create.blade.php \$user@\$ip:$REMOTE_DIR/resources/views/livewire/campaigns/create.blade.php
expect {
    "password:" { send "\$password\r"; exp_continue }
    eof
}
EOF

chmod +x upload_view.exp
./upload_view.exp $SERVER_IP $USER $PASS

echo "🧹 Clearing view cache..."
cat <<EOF > clear_cache.exp
#!/usr/bin/expect -f
set timeout -1
set ip [lindex \$argv 0]
set user [lindex \$argv 1]
set password [lindex \$argv 2]

spawn ssh -o StrictHostKeyChecking=no \$user@\$ip "cd $REMOTE_DIR && docker compose -f docker-compose.prod.yml exec -T app php artisan view:clear"
expect {
    "password:" { send "\$password\r"; exp_continue }
    eof
}
EOF

chmod +x clear_cache.exp
./clear_cache.exp $SERVER_IP $USER $PASS

rm upload_view.exp clear_cache.exp
echo "✅ Deployment of view complete!"
