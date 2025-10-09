#!/bin/bash

# AWS EC2 User Data Script untuk Laravel Application dengan Instance Monitoring
# Script ini akan dijalankan saat instance pertama kali boot

# Update sistem
yum update -y

# Install Apache, PHP, dan ekstensi yang diperlukan
yum install -y httpd php php-mysql php-xml php-zip php-gd php-mbstring php-curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Git
yum install -y git

# Start dan enable Apache
systemctl start httpd
systemctl enable httpd

# Clone atau copy aplikasi Laravel Anda
cd /var/www/html
# Contoh: git clone https://github.com/your-username/your-laravel-app.git .

# Set permission untuk Laravel
chown -R apache:apache /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Install dependencies Laravel
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Set environment variables untuk instance
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id 2>/dev/null || echo "i-unknown")
AZ=$(curl -s http://169.254.169.254/latest/meta-data/placement/availability-zone 2>/dev/null || echo "unknown-zone")
INSTANCE_TYPE=$(curl -s http://169.254.169.254/latest/meta-data/instance-type 2>/dev/null || echo "unknown")
LOCAL_IP=$(curl -s http://169.254.169.254/latest/meta-data/local-ipv4 2>/dev/null || echo "127.0.0.1")
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "N/A")

# Buat file .env dari .env.example
cp .env.example .env

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

echo "INSTANCE_NAME=\"$INSTANCE_NAME\"" >> .env

# Tambahkan informasi instance lainnya ke .env
echo "AWS_INSTANCE_ID=$INSTANCE_ID" >> .env
echo "AWS_AVAILABILITY_ZONE=$AZ" >> .env
echo "AWS_INSTANCE_TYPE=$INSTANCE_TYPE" >> .env
echo "AWS_REGION=${AZ%?}" >> .env

# Set as system environment variables juga
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

# Configure database (sesuaikan dengan setup Anda)
echo "DB_CONNECTION=mysql" >> .env
echo "DB_HOST=your-rds-endpoint.region.rds.amazonaws.com" >> .env
echo "DB_PORT=3306" >> .env
echo "DB_DATABASE=your_database_name" >> .env
echo "DB_USERNAME=your_username" >> .env
echo "DB_PASSWORD=your_password" >> .env

# Run Laravel migrations (jika diperlukan)
# php artisan migrate --force

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configure Apache untuk Laravel
cat > /etc/httpd/conf.d/laravel.conf << 'EOL'
<VirtualHost *:80>
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog /var/log/httpd/laravel_error.log
    CustomLog /var/log/httpd/laravel_access.log combined
</VirtualHost>
EOL

# Restart Apache
systemctl restart httpd

# Install CloudWatch agent untuk monitoring CPU (opsional)
wget https://s3.amazonaws.com/amazoncloudwatch-agent/amazon_linux/amd64/latest/amazon-cloudwatch-agent.rpm
rpm -U ./amazon-cloudwatch-agent.rpm

# Configure CloudWatch agent
cat > /opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json << 'EOL'
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
            "disk": {
                "measurement": [
                    "used_percent"
                ],
                "metrics_collection_interval": 60,
                "resources": [
                    "*"
                ]
            },
            "diskio": {
                "measurement": [
                    "io_time"
                ],
                "metrics_collection_interval": 60,
                "resources": [
                    "*"
                ]
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
EOL

# Start CloudWatch agent
/opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl -a fetch-config -m ec2 -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json -s

echo "Laravel installation completed!"
echo "Instance Name: Web-Server-${AZ}-${INSTANCE_ID:0:8}"
echo "Access your application at: http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)"