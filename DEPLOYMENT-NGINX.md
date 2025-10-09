# AWS Instance Monitor - Deployment Guide dengan Nginx

## Overview

Aplikasi Laravel AWS Instance Monitor dengan setup nginx yang sudah dikonfigurasi untuk mengatasi masalah 504 Gateway Timeout.

## Files yang Dibutuhkan

-   `deployment/user-data-nginx.sh` - Script user data yang sudah include nginx fix
-   Terraform configuration (optional)
-   Security Group untuk RDS MySQL (sudah Anda handle)

## Quick Deployment

### 1. Menggunakan User Data Script yang Sudah Fixed

Copy isi file `deployment/user-data-nginx.sh` ke User Data instance Anda di AWS Console. Script ini akan:

1. **Fix nginx configuration** (dari script Anda)

    - Uncomment PHP block di nginx config
    - Update PHP version dari 7.4 ke 8.2
    - Test dan restart nginx
    - Jika masih 504, replace dengan Laravel-optimized config

2. **Deploy Laravel application**
    - Clone repository dari GitHub
    - Install dependencies dengan Composer
    - Setup environment variables
    - Generate "Server X" naming berdasarkan instance ID
    - Configure nginx untuk Laravel

### 2. Launch Instance Setup

1. **AMI**: Ubuntu 22.04 LTS (karena script menggunakan apt)
2. **Instance Type**: t3.micro atau sesuai kebutuhan
3. **Security Group**:
    - HTTP (80): 0.0.0.0/0
    - SSH (22): Your IP
4. **User Data**: Paste script dari `deployment/user-data-nginx.sh`

### 3. Load Balancer Configuration

```bash
# Health Check Settings
Path: /instance
Port: 80
Protocol: HTTP
Healthy threshold: 2
Unhealthy threshold: 3
Timeout: 5 seconds
Interval: 30 seconds
Success codes: 200
```

## Manual Verification

Setelah instance launch (tunggu 5-10 menit), check:

### 1. SSH ke Instance

```bash
ssh -i your-key.pem ubuntu@instance-ip

# Check logs
sudo tail -f /var/log/user-data.log
sudo tail -f /var/log/user-data-fix.log

# Check services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm

# Test nginx config
sudo nginx -t
```

### 2. Test Endpoints

```bash
# Test basic PHP
curl http://localhost/info.php

# Test Laravel app
curl http://localhost/instance

# Check HTTP status
curl -I http://localhost/instance
```

### 3. Verify Application

-   Access `http://YOUR_INSTANCE_IP/instance`
-   Check instance name shows "Server 1", "Server 2", etc.
-   Verify real AWS metadata (not localhost)
-   Test "Debug Info" button

## Nginx Configuration Details

Script akan membuat nginx config optimal untuk Laravel:

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html/public;
    index index.php index.html index.htm;

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

    # Static file caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Environment Variables

Script akan otomatis set environment variables:

```bash
INSTANCE_NAME="Server 1"          # Auto-generated
AWS_INSTANCE_ID="i-1234567890abcdef0"
AWS_AVAILABILITY_ZONE="us-east-1a"
AWS_INSTANCE_TYPE="t3.micro"
AWS_REGION="us-east-1"
```

## Database Configuration

Karena Anda sudah handle security group untuk RDS MySQL, aplikasi siap connect ke database. Update file `.env` manually jika perlu:

```bash
# SSH ke instance dan edit
sudo nano /var/www/html/.env

# Update database settings
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.region.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

## Auto Scaling Group Setup

### Launch Template

1. Create Launch Template menggunakan script `user-data-nginx.sh`
2. Configure:
    - AMI: Ubuntu 22.04 LTS
    - Instance type: t3.micro
    - Security groups: Web server + database access
    - User data: Script content

### Auto Scaling Group

```bash
Min size: 2
Max size: 5
Desired: 2
Health check type: ELB
Health check grace period: 300 seconds
```

## Monitoring & Troubleshooting

### Debug Button

Aplikasi include debug button yang menampilkan:

-   AWS detection methods
-   Environment variables
-   System information
-   Raw metadata responses

### Log Files

```bash
# User data execution
sudo tail -f /var/log/user-data.log

# Nginx fix log
sudo tail -f /var/log/user-data-fix.log

# Laravel application
sudo tail -f /var/www/html/storage/logs/laravel.log

# Nginx error log
sudo tail -f /var/log/nginx/error.log

# PHP-FPM log
sudo tail -f /var/log/php8.2-fpm.log
```

### Common Issues

#### 1. 504 Gateway Timeout

Script automatically handles this, but manual fix:

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

#### 2. Showing Localhost Data

Check debug info di aplikasi atau manual check:

```bash
curl http://169.254.169.254/latest/meta-data/instance-id
env | grep AWS
```

#### 3. Laravel 500 Error

```bash
# Check permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 775 /var/www/html/storage

# Clear cache
cd /var/www/html
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

## Load Testing

Aplikasi include stress test feature untuk trigger auto scaling:

1. Access `/instance` page
2. Click "Start Stress Test"
3. Monitor CPU usage increase
4. Watch Auto Scaling Group add new instances
5. Verify load balancer distributes traffic

## Security Notes

-   Script disables default nginx test page
-   Laravel app protected dengan proper .htaccess rules
-   Debug endpoint hanya accessible jika aplikasi dalam debug mode
-   Database credentials secured via environment variables

## Production Optimizations

After deployment, untuk production:

```bash
# Update .env
APP_ENV=production
APP_DEBUG=false

# Clear and cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup HTTPS (dengan SSL certificate)
# Configure CloudFront (optional)
# Setup monitoring dengan CloudWatch
```

## Support

Jika ada issues:

1. Check debug info via aplikasi
2. Review log files di `/var/log/`
3. Test nginx config: `sudo nginx -t`
4. Verify metadata service: `curl http://169.254.169.254/latest/meta-data/`
