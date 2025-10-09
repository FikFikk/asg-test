# Quick Deployment Guide

## Untuk Deploy ke AWS EC2

### 1. Upload Code ke Server

```bash
# Clone atau upload code
git clone https://github.com/FikFikk/asg-test.git
cd asg-test

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 2. Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set basic configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Set Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache
```

### 4. AWS Metadata Detection

Aplikasi akan otomatis detect:

-   ✅ Instance ID
-   ✅ Instance Type
-   ✅ Availability Zone
-   ✅ Private IP
-   ✅ Public IP (jika ada)
-   ✅ Auto-generate Instance Name

### 5. Instance Naming

Format otomatis: `Web-Server-{AZ}-{SHORT_INSTANCE_ID}`

Contoh:

-   `Web-Server-us-east-1a-12345678`
-   `Web-Server-us-east-1b-87654321`

### 6. Load Balancer Testing

1. Deploy minimal 2 instance di different AZ
2. Setup Application Load Balancer
3. Access `/instance` page
4. Refresh multiple times → akan show different instances
5. Run stress test → trigger auto scaling

### 7. Auto Scaling Testing

1. Access monitoring page
2. Run CPU stress test (80-90% intensity)
3. Monitor CloudWatch alarms
4. Watch new instances launch
5. Verify load distribution

## URLs untuk Testing

-   **Main App**: `http://your-alb-dns/`
-   **Instance Monitor**: `http://your-alb-dns/instance`
-   **CPU API**: `http://your-alb-dns/instance/cpu`
-   **Instance Info API**: `http://your-alb-dns/instance/info`

## Expected Behavior

1. **Local Development**: Shows "Local Development Server" info
2. **AWS Production**: Shows real AWS instance metadata
3. **Load Balancer**: Different instance info on each request
4. **Auto Scaling**: New instances appear after scaling events

## No Additional Configuration Needed

Aplikasi sudah siap deploy tanpa perlu ubah code lagi! 🚀
