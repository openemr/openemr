output "subnet_ids" {
  description = "Subnet IDs used for ECS/OpenEMR tasks."
  value       = local.task_subnet_ids_effective
}

output "assign_public_ip" {
  description = "Whether ECS tasks are configured with public IP assignment."
  value       = local.assign_public_ip_effective
}

output "deployment" {
  description = "Structured deployment output for automation and operations."
  value = {
    network = {
      vpc_id                  = local.vpc_id_effective
      public_subnet_ids       = local.public_subnet_ids_effective
      private_subnet_ids      = local.private_subnet_ids_effective
      db_subnet_ids           = local.db_subnet_ids_effective
      task_subnet_ids         = local.task_subnet_ids_effective
      assign_public_ip        = local.assign_public_ip_effective
      nat_enabled             = local.enable_nat_gateway_effective
      nat_gateway_id          = local.nat_gateway_id_effective
      internet_gateway_id     = local.internet_gateway_id_effective
      public_route_table_ids  = local.public_route_table_ids_effective
      private_route_table_ids = local.private_route_table_ids_effective
      db_route_table_ids      = local.db_route_table_ids_effective
      vpc_endpoints = {
        logs           = try(aws_vpc_endpoint.logs[0].id, null)
        secretsmanager = try(aws_vpc_endpoint.secretsmanager[0].id, null)
        s3             = try(aws_vpc_endpoint.s3[0].id, null)
      }
    }
    load_balancer = {
      alb_arn        = local.alb_arn_effective
      alb_dns_name   = local.alb_dns_name_effective
      alb_zone_id    = local.alb_zone_id_effective
      https_listener = local.https_listener_arn_effective
      http_listener  = local.http_listener_arn_effective
      openemr_tg_arn = local.openemr_target_group_arn_effective
    }
    dns_tls = {
      hosted_zone_id  = local.hosted_zone_id_effective
      hostnames       = local.openemr_hostnames_effective
      certificate_arn = local.create_acm_certificate_effective ? aws_acm_certificate_validation.openemr[0].certificate_arn : var.existing_certificate_arn
    }
    compute = {
      cluster_arn      = aws_ecs_cluster.app.arn
      openemr_service  = aws_ecs_service.openemr.name
      openemr_task_def = aws_ecs_task_definition.openemr.arn
    }
    database = {
      created             = local.create_db_effective
      endpoint            = local.db_endpoint_effective
      port                = local.db_port_effective
      name                = local.db_name_effective
      username            = local.db_username_effective
      secret_arn          = local.rds_secret_arn_effective
      publicly_accessible = local.db_publicly_accessible_effective
      allowed_cidrs       = local.db_allowed_cidrs_effective
    }
    storage = {
      efs_file_system_id  = aws_efs_file_system.openemr_sites.id
      efs_access_point_id = aws_efs_access_point.openemr_sites.id
    }
    monitoring = {
      enabled        = local.create_monitoring_effective
      dashboard_name = try(aws_cloudwatch_dashboard.openemr[0].dashboard_name, null)
      dashboard_url  = try("https://${var.aws_region}.console.aws.amazon.com/cloudwatch/home?region=${var.aws_region}#dashboards:name=${aws_cloudwatch_dashboard.openemr[0].dashboard_name}", null)
      alarms = {
        alb_5xx             = try(aws_cloudwatch_metric_alarm.alb_5xx[0].arn, null)
        alb_unhealthy_hosts = try(aws_cloudwatch_metric_alarm.alb_unhealthy_hosts[0].arn, null)
        alb_latency_high    = try(aws_cloudwatch_metric_alarm.alb_latency_high[0].arn, null)
        ecs_cpu_high        = try(aws_cloudwatch_metric_alarm.ecs_cpu_high[0].arn, null)
        ecs_memory_high     = try(aws_cloudwatch_metric_alarm.ecs_memory_high[0].arn, null)
        rds_cpu_high        = try(aws_cloudwatch_metric_alarm.rds_cpu_high[0].arn, null)
        rds_free_storage    = try(aws_cloudwatch_metric_alarm.rds_free_storage_low[0].arn, null)
      }
    }
  }
}

output "openemr_url" {
  description = "Public HTTPS URL for OpenEMR (IP-allowlisted)."
  value       = "https://${local.openemr_primary_hostname}"
}

output "openemr_service_name" {
  description = "OpenEMR ECS service name."
  value       = aws_ecs_service.openemr.name
}

output "openemr_task_definition_arn" {
  description = "Current OpenEMR task definition ARN."
  value       = aws_ecs_task_definition.openemr.arn
}

output "openemr_target_group_arn" {
  description = "ALB target group ARN for OpenEMR."
  value       = local.openemr_target_group_arn_effective
}

output "openemr_configuration_secret_arn" {
  description = "Secrets Manager ARN for OpenEMR config secret."
  value       = aws_secretsmanager_secret.openemr_configuration.arn
}

output "rds_secret_arn" {
  description = "Secrets Manager ARN for RDS credentials secret."
  value       = local.rds_secret_arn_effective
}

output "rds_endpoint" {
  description = "RDS endpoint hostname."
  value       = local.db_endpoint_effective
}

output "openemr_efs_file_system_id" {
  description = "EFS filesystem id used for OpenEMR sites persistence."
  value       = aws_efs_file_system.openemr_sites.id
}

output "openemr_efs_access_point_id" {
  description = "EFS access point id used by OpenEMR sites volume mount."
  value       = aws_efs_access_point.openemr_sites.id
}
