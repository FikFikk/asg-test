# Deployment Guide - AWS Instance Monitoring

## Prerequisites

-   AWS Account dengan VPC dan subnets
-   Terraform atau CloudFormation untuk infrastructure
-   SSH key pair untuk EC2 access
-   Domain name (opsional)

## Step-by-Step Deployment

### 1. Prepare Infrastructure

```bash
# Clone repository
git clone https://github.com/your-username/asg-test.git
cd asg-test

# Update variables in terraform-asg.tf
vim deployment/terraform-asg.tf
```

### 2. Deploy Infrastructure

```bash
# Initialize Terraform
terraform init

# Plan deployment
terraform plan \
  -var="vpc_id=vpc-xxxxxxxx" \
  -var="public_subnets=[\"subnet-xxxxxxxx\",\"subnet-yyyyyyyy\"]" \
  -var="private_subnets=[\"subnet-zzzzzzzz\",\"subnet-aaaaaaaa\"]" \
  -var="environment=production"

# Apply configuration
terraform apply
```

### 3. Configure Application

Update `deployment/user-data.sh`:

```bash
# Set your Git repository
git clone https://github.com/your-username/asg-test.git .

# Set your database credentials
echo "DB_HOST=your-rds-endpoint.region.rds.amazonaws.com" >> .env
echo "DB_DATABASE=your_database_name" >> .env
echo "DB_USERNAME=your_username" >> .env
echo "DB_PASSWORD=your_password" >> .env
```

### 4. Launch Auto Scaling Group

The Terraform script will automatically:

-   Create Launch Template with user data
-   Launch initial instances (min: 2)
-   Setup Application Load Balancer
-   Configure auto scaling policies
-   Create CloudWatch alarms

### 5. Verify Deployment

1. Check ALB DNS name from Terraform output
2. Access: `http://your-alb-dns-name`
3. Test monitoring: `http://your-alb-dns-name/instance`
4. Run stress test to trigger scaling

### 6. Monitor Auto Scaling

-   AWS Console → EC2 → Auto Scaling Groups
-   CloudWatch → Alarms
-   ALB → Target Groups (check healthy instances)

## Testing Load Balancer Distribution

### Method 1: Multiple Browser Tabs

1. Open multiple tabs: `http://your-alb-dns-name/instance`
2. Refresh beberapa kali
3. Perhatikan Instance ID yang berbeda

### Method 2: Command Line

```bash
# Multiple requests untuk melihat distribution
for i in {1..10}; do
  curl -s http://your-alb-dns-name/instance | grep -o "Instance ID.*</p>" | head -1
  sleep 1
done
```

### Method 3: Stress Test

1. Akses `/instance` page
2. Set stress test duration: 60 seconds
3. Set intensity: 85%
4. Click "Start Stress Test"
5. Monitor CloudWatch untuk scaling activity

## Troubleshooting

### Instance tidak healthy di ALB

```bash
# Check security groups
aws ec2 describe-security-groups --group-ids sg-xxxxxxxx

# Check instance logs
aws ssm start-session --target i-xxxxxxxx
sudo tail -f /var/log/httpd/error_log
```

### Auto scaling tidak trigger

```bash
# Check CloudWatch alarms
aws cloudwatch describe-alarms --alarm-names "laravel-cpu-high"

# Manual scaling test
aws autoscaling set-desired-capacity \
  --auto-scaling-group-name laravel-asg \
  --desired-capacity 3
```

### Monitoring page error

```bash
# Check Laravel logs
aws ssm start-session --target i-xxxxxxxx
sudo tail -f /var/www/html/storage/logs/laravel.log

# Check Apache logs
sudo tail -f /var/log/httpd/laravel_error.log
```

## Environment Specific Configuration

### Development

```env
APP_ENV=local
APP_DEBUG=true
INSTANCE_NAME="Dev-Server-Local"
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=false
INSTANCE_NAME="Staging-Server-${AZ}-${INSTANCE_ID:0:8}"
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
INSTANCE_NAME="Prod-Server-${AZ}-${INSTANCE_ID:0:8}"
```

## Security Best Practices

### Network Security

-   EC2 instances di private subnets
-   ALB di public subnets
-   Security groups dengan minimal required ports
-   VPC Flow Logs enabled

### Instance Security

-   Regular security updates via user data
-   IAM roles dengan least privilege
-   SSH access via Session Manager (no direct SSH)
-   CloudWatch Logs untuk audit trail

### Application Security

-   HTTPS dengan SSL certificate
-   Database di private subnets
-   Secrets management dengan AWS Secrets Manager
-   Regular dependency updates

## Monitoring & Alerting

### CloudWatch Metrics

-   CPU Utilization
-   Memory Usage
-   Disk Usage
-   Network I/O
-   Application response time

### Custom Alerts

```bash
# Create custom metric untuk application errors
aws cloudwatch put-metric-data \
  --namespace "Laravel/Application" \
  --metric-data MetricName=ErrorCount,Value=1,Unit=Count
```

### Log Aggregation

-   Application logs → CloudWatch Logs
-   Access logs → S3
-   Error tracking dengan Sentry/Bugsnag

## Cost Optimization

### Instance Types

-   t3.nano: Development/testing
-   t3.micro: Low traffic production
-   t3.small: Medium traffic
-   t3.medium: High traffic

### Auto Scaling Configuration

```bash
# Scheduled scaling untuk predictable traffic
aws autoscaling put-scheduled-update-group-action \
  --auto-scaling-group-name laravel-asg \
  --scheduled-action-name "morning-scale-up" \
  --start-time "2023-01-01T08:00:00Z" \
  --recurrence "0 8 * * MON-FRI" \
  --desired-capacity 4
```

### Resource Cleanup

```bash
# Terminate infrastructure
terraform destroy

# Or manual cleanup
aws autoscaling delete-auto-scaling-group \
  --auto-scaling-group-name laravel-asg \
  --force-delete
```

## Backup & Recovery

### Application Backup

-   Code: Git repository
-   Database: RDS automated backups
-   Files: S3 bucket sync

### Disaster Recovery

-   Multi-AZ deployment
-   Database cross-region replication
-   Infrastructure as Code (Terraform)
-   Automated testing pipeline

## Performance Tuning

### PHP Optimization

```bash
# Install PHP OPcache
sudo yum install -y php-opcache

# Configure php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

### Laravel Optimization

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Database Optimization

-   Read replicas untuk read-heavy workloads
-   Connection pooling
-   Query optimization
-   Database indexing
