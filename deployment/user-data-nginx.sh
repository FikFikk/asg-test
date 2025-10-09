#!/bin/bash

# AWS Instance Monitor - User Data Script with Nginx Fix
# This script combines nginx configuration fix with Laravel application deployment

# Log all output
exec > >(tee /var/log/user-data.log)
exec 2>&1

echo "=== Starting AWS Instance Monitor Setup at $(date) ==="

# First, run the nginx fix (from your existing script)
echo "=== Starting nginx fix at $(date) ===" >> /var/log/user-data-fix.log

# Wait for system to be ready
sleep 15

# Fix nginx config - uncomment PHP block
sed -i '/location ~ \\\.php\$ {/,/^[[:space:]]*#[[:space:]]*}/ {
    s/^[[:space:]]*#//
}' /etc/nginx/sites-available/default

# Fix PHP version from 7.4 to 8.2
sed -i 's/php7\.4-fpm\.sock/php8.2-fpm.sock/g' /etc/nginx/sites-available/default

# Ensure PHP-FPM is running
systemctl restart php8.2-fpm
sleep 2

# Test nginx config
nginx -t >> /var/log/user-data-fix.log 2>&1

# Restart nginx
systemctl restart nginx

# Wait and test
sleep 3
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
echo "HTTP Status after fix: $HTTP_STATUS" >> /var/log/user-data-fix.log

# If still broken, force complete config replacement for Laravel
if [ "$HTTP_STATUS" = "504" ] || [ "$HTTP_STATUS" = "403" ]; then
    echo "HTTP Status $HTTP_STATUS, applying Laravel nginx config..." >> /var/log/user-data-fix.log
    
    cat > /etc/nginx/sites-available/default << 'NGINX_EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html/public;
    index index.php index.html index.htm index.nginx-debian.html;

    server_name _;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
    
    # Additional Laravel optimizations
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
NGINX_EOF

    nginx -t && systemctl reload nginx
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
    echo "HTTP Status after Laravel config: $HTTP_STATUS" >> /var/log/user-data-fix.log
fi

echo "=== Nginx fix completed at $(date) ===" >> /var/log/user-data-fix.log

# Now install additional packages needed for Laravel
echo "Installing additional packages for Laravel..."
apt update
apt install -y git composer curl unzip php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-mysql

# Install Composer if not available
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Get instance metadata
echo "Retrieving instance metadata..."
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id 2>/dev/null || echo "i-unknown")
AZ=$(curl -s http://169.254.169.254/latest/meta-data/placement/availability-zone 2>/dev/null || echo "unknown-zone")
INSTANCE_TYPE=$(curl -s http://169.254.169.254/latest/meta-data/instance-type 2>/dev/null || echo "unknown")
LOCAL_IP=$(curl -s http://169.254.169.254/latest/meta-data/local-ipv4 2>/dev/null || echo "127.0.0.1")
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "N/A")

echo "Instance ID: $INSTANCE_ID"
echo "Availability Zone: $AZ"
echo "Instance Type: $INSTANCE_TYPE"

# Clone Laravel application
echo "Setting up Laravel application..."
cd /var/www
rm -rf html
git clone https://github.com/FikFikk/asg-test.git html

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Install Laravel dependencies
cd /var/www/html
sudo -u www-data composer install --no-dev --optimize-autoloader

# Create .env file
sudo -u www-data cp .env.example .env

# Generate application key
sudo -u www-data php artisan key:generate

# Generate Server name berdasarkan Instance ID
if [[ $INSTANCE_ID =~ ^i-[a-f0-9]+$ ]]; then
    # Extract hex dari instance ID dan convert ke decimal untuk server number
    HEX_PART=${INSTANCE_ID:2}
    LAST_4_CHARS=${HEX_PART: -4}
    DECIMAL_VALUE=$((16#$LAST_4_CHARS))
    SERVER_NUMBER=$(( ($DECIMAL_VALUE % 10) + 1 ))
    INSTANCE_NAME="Server $SERVER_NUMBER"
else
    INSTANCE_NAME="Server 1"
fi

echo "Generated Instance Name: $INSTANCE_NAME"

# Update .env with instance information
echo "INSTANCE_NAME=\"$INSTANCE_NAME\"" >> .env
echo "AWS_INSTANCE_ID=$INSTANCE_ID" >> .env
echo "AWS_AVAILABILITY_ZONE=$AZ" >> .env
echo "AWS_INSTANCE_TYPE=$INSTANCE_TYPE" >> .env
echo "AWS_REGION=${AZ%?}" >> .env

# Set as system environment variables
export INSTANCE_NAME="$INSTANCE_NAME"
export AWS_INSTANCE_ID="$INSTANCE_ID"
export AWS_AVAILABILITY_ZONE="$AZ"
export AWS_INSTANCE_TYPE="$INSTANCE_TYPE"
export AWS_REGION="${AZ%?}"

# Add to system environment
echo "export INSTANCE_NAME=\"$INSTANCE_NAME\"" >> /etc/environment
echo "export AWS_INSTANCE_ID=\"$INSTANCE_ID\"" >> /etc/environment
echo "export AWS_AVAILABILITY_ZONE=\"$AZ\"" >> /etc/environment
echo "export AWS_INSTANCE_TYPE=\"$INSTANCE_TYPE\"" >> /etc/environment
echo "export AWS_REGION=\"${AZ%?}\"" >> /etc/environment

# Configure database - Anda bilang sudah handle di security group
# Jadi kita skip DB config atau set default saja
echo "DB_CONNECTION=mysql" >> .env
echo "DB_HOST=127.0.0.1" >> .env
echo "DB_PORT=3306" >> .env
echo "DB_DATABASE=laravel" >> .env
echo "DB_USERNAME=laravel" >> .env
echo "DB_PASSWORD=laravel" >> .env

# Cache Laravel configurations
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create test PHP file
echo "<?php 
echo '<h1>Server: ' . (\$_ENV['INSTANCE_NAME'] ?? 'Unknown') . '</h1>';
echo '<p>Instance ID: ' . (\$_ENV['AWS_INSTANCE_ID'] ?? 'Unknown') . '</p>';
echo '<p>Server Time: ' . date('Y-m-d H:i:s') . '</p>';
phpinfo(); 
?>" > /var/www/html/public/info.php

# Restart services
systemctl restart php8.2-fpm
systemctl restart nginx

# Final test
sleep 5
FINAL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
echo "Final HTTP Status: $FINAL_STATUS" >> /var/log/user-data-fix.log

# Test Laravel application
LARAVEL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/instance)
echo "Laravel App Status: $LARAVEL_STATUS" >> /var/log/user-data-fix.log

echo "=== AWS Instance Monitor Setup completed at $(date) ==="
echo "Instance Name: $INSTANCE_NAME"
echo "Access your application at: http://$PUBLIC_IP/instance"
echo "PHP Info available at: http://$PUBLIC_IP/info.php"

# Optional: Install CloudWatch monitoring
if command -v amazon-cloudwatch-agent-ctl &> /dev/null; then
    echo "Setting up CloudWatch monitoring..."
    
    cat > /opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json << 'CW_EOF'
{
    "metrics": {
        "namespace": "Laravel/Instance",
        "metrics_collected": {
            "cpu": {
                "measurement": [
                    "cpu_usage_idle",
                    "cpu_usage_iowait", 
                    "cpu_usage_user",
                    "cpu_usage_system"
                ],
                "metrics_collection_interval": 60
            },
            "mem": {
                "measurement": [
                    "mem_used_percent"
                ],
                "metrics_collection_interval": 60
            }
        }
    }
}
CW_EOF

    /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl -a fetch-config -m ec2 -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json -s
fi

echo "Setup complete! Instance $INSTANCE_NAME is ready."