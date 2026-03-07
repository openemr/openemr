resource "aws_lb_target_group" "openemr" {
  count       = local.openemr_target_group_arn_reference == "" ? 1 : 0
  name        = substr("${local.app_name}-openemr-tg", 0, 32)
  port        = var.openemr_container_port
  protocol    = "HTTP"
  vpc_id      = local.vpc_id_effective
  target_type = "ip"

  health_check {
    enabled             = true
    path                = var.openemr_health_check_path
    protocol            = "HTTP"
    matcher             = "200-399"
    healthy_threshold   = 2
    unhealthy_threshold = 3
    timeout             = 5
    interval            = 30
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-tg" })
}

resource "aws_acm_certificate" "openemr" {
  count                     = local.create_acm_certificate_effective ? 1 : 0
  domain_name               = local.openemr_primary_hostname
  subject_alternative_names = length(local.openemr_hostnames_effective) > 1 ? slice(local.openemr_hostnames_effective, 1, length(local.openemr_hostnames_effective)) : []
  validation_method         = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-cert" })
}

resource "aws_route53_record" "openemr_cert_validation" {
  for_each = {
    for dvo in(local.create_acm_certificate_effective ? aws_acm_certificate.openemr[0].domain_validation_options : []) : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  zone_id         = local.hosted_zone_id_effective
  name            = each.value.name
  type            = each.value.type
  ttl             = 60
  records         = [each.value.record]
}

resource "aws_acm_certificate_validation" "openemr" {
  count                   = local.create_acm_certificate_effective ? 1 : 0
  certificate_arn         = aws_acm_certificate.openemr[0].arn
  validation_record_fqdns = [for record in aws_route53_record.openemr_cert_validation : record.fqdn]
}

resource "aws_lb_listener_certificate" "openemr" {
  count           = local.create_acm_certificate_effective ? 1 : 0
  listener_arn    = local.https_listener_arn_effective
  certificate_arn = aws_acm_certificate_validation.openemr[0].certificate_arn
}

resource "aws_lb_listener_rule" "openemr_host_ip_locked" {
  listener_arn = local.https_listener_arn_effective
  priority     = var.openemr_host_rule_priority

  condition {
    host_header {
      values = local.openemr_hostnames_effective
    }
  }

  condition {
    source_ip {
      values = var.openemr_allowed_cidrs
    }
  }

  action {
    type             = "forward"
    target_group_arn = local.openemr_target_group_arn_effective
  }
}

resource "aws_lb_listener_rule" "openemr_http_redirect" {
  listener_arn = local.http_listener_arn_effective
  priority     = var.openemr_http_redirect_priority

  condition {
    host_header {
      values = local.openemr_hostnames_effective
    }
  }

  action {
    type = "redirect"
    redirect {
      protocol    = "HTTPS"
      port        = "443"
      host        = "#{host}"
      path        = "/#{path}"
      query       = "#{query}"
      status_code = "HTTP_301"
    }
  }
}

resource "aws_route53_record" "openemr_alias" {
  for_each = local.hosted_zone_id_effective != "" ? toset(local.openemr_hostnames_effective) : toset([])

  zone_id = local.hosted_zone_id_effective
  name    = each.value
  type    = "A"

  alias {
    name                   = local.alb_dns_name_effective
    zone_id                = local.alb_zone_id_effective
    evaluate_target_health = true
  }
}
