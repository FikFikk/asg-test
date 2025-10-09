# Auto Scaling Group Configuration
# Template untuk AWS CloudFormation atau Terraform

# Launch Template Configuration
resource "aws_launch_template" "laravel_template" {
  name_prefix   = "laravel-asg-"
  image_id      = "ami-0abcdef1234567890"  # Amazon Linux 2 AMI
  instance_type = "t3.micro"
  
  vpc_security_group_ids = [aws_security_group.laravel_sg.id]
  
  user_data = base64encode(file("${path.module}/user-data.sh"))
  
  iam_instance_profile {
    name = aws_iam_instance_profile.laravel_profile.name
  }
  
  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "Laravel-Instance"
      Environment = var.environment
      Application = "Laravel"
    }
  }
}

# Auto Scaling Group
resource "aws_autoscaling_group" "laravel_asg" {
  name                = "laravel-asg"
  vpc_zone_identifier = var.private_subnets
  target_group_arns   = [aws_lb_target_group.laravel_tg.arn]
  health_check_type   = "ELB"
  health_check_grace_period = 300
  
  min_size         = 2
  max_size         = 6
  desired_capacity = 2
  
  launch_template {
    id      = aws_launch_template.laravel_template.id
    version = "$Latest"
  }
  
  # Auto Scaling Policies
  enabled_metrics = [
    "GroupMinSize",
    "GroupMaxSize",
    "GroupDesiredCapacity",
    "GroupInServiceInstances",
    "GroupTotalInstances"
  ]
  
  tag {
    key                 = "Name"
    value               = "Laravel-ASG-Instance"
    propagate_at_launch = true
  }
  
  tag {
    key                 = "Environment"
    value               = var.environment
    propagate_at_launch = true
  }
}

# Security Group untuk Laravel instances
resource "aws_security_group" "laravel_sg" {
  name_prefix = "laravel-sg-"
  vpc_id      = var.vpc_id
  
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    security_groups = [aws_security_group.alb_sg.id]
  }
  
  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["10.0.0.0/8"]  # Adjust as needed
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "Laravel-Security-Group"
  }
}

# Application Load Balancer
resource "aws_lb" "laravel_alb" {
  name               = "laravel-alb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb_sg.id]
  subnets            = var.public_subnets
  
  enable_deletion_protection = false
  
  tags = {
    Name = "Laravel-ALB"
    Environment = var.environment
  }
}

# Security Group untuk ALB
resource "aws_security_group" "alb_sg" {
  name_prefix = "laravel-alb-sg-"
  vpc_id      = var.vpc_id
  
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "Laravel-ALB-Security-Group"
  }
}

# Target Group
resource "aws_lb_target_group" "laravel_tg" {
  name     = "laravel-tg"
  port     = 80
  protocol = "HTTP"
  vpc_id   = var.vpc_id
  
  health_check {
    enabled             = true
    healthy_threshold   = 2
    interval            = 30
    matcher             = "200"
    path                = "/"
    port                = "traffic-port"
    protocol            = "HTTP"
    timeout             = 5
    unhealthy_threshold = 2
  }
  
  tags = {
    Name = "Laravel-Target-Group"
  }
}

# ALB Listener
resource "aws_lb_listener" "laravel_listener" {
  load_balancer_arn = aws_lb.laravel_alb.arn
  port              = "80"
  protocol          = "HTTP"
  
  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.laravel_tg.arn
  }
}

# Auto Scaling Policies
resource "aws_autoscaling_policy" "scale_up" {
  name                   = "laravel-scale-up"
  scaling_adjustment     = 1
  adjustment_type        = "ChangeInCapacity"
  cooldown               = 300
  autoscaling_group_name = aws_autoscaling_group.laravel_asg.name
}

resource "aws_autoscaling_policy" "scale_down" {
  name                   = "laravel-scale-down"
  scaling_adjustment     = -1
  adjustment_type        = "ChangeInCapacity"
  cooldown               = 300
  autoscaling_group_name = aws_autoscaling_group.laravel_asg.name
}

# CloudWatch Alarms
resource "aws_cloudwatch_metric_alarm" "cpu_high" {
  alarm_name          = "laravel-cpu-high"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/EC2"
  period              = "120"
  statistic           = "Average"
  threshold           = "80"
  alarm_description   = "This metric monitors ec2 cpu utilization"
  alarm_actions       = [aws_autoscaling_policy.scale_up.arn]
  
  dimensions = {
    AutoScalingGroupName = aws_autoscaling_group.laravel_asg.name
  }
}

resource "aws_cloudwatch_metric_alarm" "cpu_low" {
  alarm_name          = "laravel-cpu-low"
  comparison_operator = "LessThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/EC2"
  period              = "120"
  statistic           = "Average"
  threshold           = "20"
  alarm_description   = "This metric monitors ec2 cpu utilization"
  alarm_actions       = [aws_autoscaling_policy.scale_down.arn]
  
  dimensions = {
    AutoScalingGroupName = aws_autoscaling_group.laravel_asg.name
  }
}

# IAM Role untuk EC2 instances
resource "aws_iam_role" "laravel_role" {
  name = "laravel-ec2-role"
  
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ec2.amazonaws.com"
        }
      }
    ]
  })
}

# IAM Policy untuk CloudWatch dan SSM
resource "aws_iam_role_policy" "laravel_policy" {
  name = "laravel-ec2-policy"
  role = aws_iam_role.laravel_role.id
  
  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "cloudwatch:PutMetricData",
          "cloudwatch:GetMetricStatistics",
          "cloudwatch:ListMetrics",
          "logs:PutLogEvents",
          "logs:CreateLogGroup",
          "logs:CreateLogStream"
        ]
        Resource = "*"
      },
      {
        Effect = "Allow"
        Action = [
          "ssm:GetParameter",
          "ssm:GetParameters",
          "ssm:GetParametersByPath"
        ]
        Resource = "*"
      }
    ]
  })
}

# Instance Profile
resource "aws_iam_instance_profile" "laravel_profile" {
  name = "laravel-instance-profile"
  role = aws_iam_role.laravel_role.name
}

# Variables
variable "vpc_id" {
  description = "VPC ID"
  type        = string
}

variable "public_subnets" {
  description = "Public subnet IDs"
  type        = list(string)
}

variable "private_subnets" {
  description = "Private subnet IDs"
  type        = list(string)
}

variable "environment" {
  description = "Environment name"
  type        = string
  default     = "production"
}

# Outputs
output "load_balancer_dns_name" {
  description = "DNS name of the load balancer"
  value       = aws_lb.laravel_alb.dns_name
}

output "load_balancer_zone_id" {
  description = "The zone ID of the load balancer"
  value       = aws_lb.laravel_alb.zone_id
}