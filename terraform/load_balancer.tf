resource "aws_security_group" "alb" {
  count = local.create_load_balancer_effective ? 1 : 0

  name        = "${local.app_name}-alb-sg"
  description = "Security group for OpenEMR ALB"
  vpc_id      = local.vpc_id_effective

  ingress {
    description = "Allow HTTP"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = var.alb_allowed_cidrs
  }

  ingress {
    description = "Allow HTTPS"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = var.alb_allowed_cidrs
  }

  egress {
    description = "Allow outbound to target groups"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.tags, { Name = "${local.app_name}-alb-sg" })
}

resource "aws_lb" "shared" {
  count = local.create_load_balancer_effective ? 1 : 0

  name               = substr(local.alb_name_effective, 0, 32)
  internal           = var.alb_internal
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb[0].id]
  subnets            = local.public_subnet_ids_effective

  tags = merge(local.tags, { Name = local.alb_name_effective })
}

resource "aws_lb_listener" "http" {
  count = local.http_listener_arn_reference == "" ? 1 : 0

  load_balancer_arn = local.alb_arn_effective
  port              = 80
  protocol          = "HTTP"

  default_action {
    type = "redirect"

    redirect {
      port        = "443"
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}

resource "aws_lb_listener" "https" {
  count = local.https_listener_arn_reference == "" ? 1 : 0

  load_balancer_arn = local.alb_arn_effective
  port              = 443
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS13-1-2-2021-06"
  certificate_arn   = local.create_acm_certificate_effective ? aws_acm_certificate_validation.openemr[0].certificate_arn : var.existing_certificate_arn

  default_action {
    type = "fixed-response"

    fixed_response {
      content_type = "text/plain"
      message_body = "Not found"
      status_code  = "404"
    }
  }
}
