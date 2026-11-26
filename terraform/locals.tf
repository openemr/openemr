data "aws_availability_zones" "available" {
  state = "available"
}

locals {
  preset_defaults = {
    turnkey = {
      create_vpc           = true
      create_load_balancer = true
      create_db            = true
      create_acm           = true
      create_monitoring    = true
      nat_gateway          = true
    }
    hybrid = {
      create_vpc           = false
      create_load_balancer = false
      create_db            = true
      create_acm           = true
      create_monitoring    = true
      nat_gateway          = true
    }
    byo = {
      create_vpc           = false
      create_load_balancer = false
      create_db            = false
      create_acm           = false
      create_monitoring    = false
      nat_gateway          = false
    }
  }

  preset = local.preset_defaults[var.deployment_preset]

  create_vpc_requested           = var.create_vpc != null ? var.create_vpc : local.preset.create_vpc
  create_load_balancer_requested = var.create_load_balancer != null ? var.create_load_balancer : local.preset.create_load_balancer
  create_vpc_effective           = local.create_vpc_requested || coalesce(try(var.network_reference.vpc_id, null), "") == ""
  create_load_balancer_effective = local.create_load_balancer_requested || coalesce(try(var.load_balancer_reference.alb_arn, null), "") == ""

  network_ref_effective = local.create_vpc_effective ? {} : var.network_reference
  lb_ref_effective      = local.create_load_balancer_effective ? {} : var.load_balancer_reference

  create_acm_certificate_effective = var.create_acm_certificate != null ? var.create_acm_certificate : local.preset.create_acm
  create_monitoring_effective      = var.create_monitoring != null ? var.create_monitoring : local.preset.create_monitoring

  network_ref_vpc_id                  = coalesce(try(local.network_ref_effective.vpc_id, null), "")
  network_ref_public_subnet_ids       = coalesce(try(local.network_ref_effective.public_subnet_ids, null), [])
  network_ref_private_subnet_ids      = coalesce(try(local.network_ref_effective.private_subnet_ids, null), [])
  network_ref_db_subnet_ids           = coalesce(try(local.network_ref_effective.db_subnet_ids, null), [])
  network_ref_nat_gateway_ids         = coalesce(try(local.network_ref_effective.nat_gateway_ids, null), [])
  network_ref_internet_gateway_id     = coalesce(try(local.network_ref_effective.internet_gateway_id, null), "")
  network_ref_public_route_table_ids  = coalesce(try(local.network_ref_effective.public_route_table_ids, null), [])
  network_ref_private_route_table_ids = coalesce(try(local.network_ref_effective.private_route_table_ids, null), [])
  network_ref_db_route_table_ids      = coalesce(try(local.network_ref_effective.db_route_table_ids, null), [])

  lb_ref_alb_arn               = coalesce(try(local.lb_ref_effective.alb_arn, null), "")
  lb_ref_alb_dns_name          = coalesce(try(local.lb_ref_effective.alb_dns_name, null), "")
  lb_ref_alb_zone_id           = coalesce(try(local.lb_ref_effective.alb_zone_id, null), "")
  lb_ref_alb_security_group_id = coalesce(try(local.lb_ref_effective.alb_security_group_id, null), "")
  lb_ref_http_listener_arn     = coalesce(try(local.lb_ref_effective.http_listener_arn, null), "")
  lb_ref_https_listener_arn    = coalesce(try(local.lb_ref_effective.https_listener_arn, null), "")
  lb_ref_openemr_tg_arn        = coalesce(try(local.lb_ref_effective.openemr_target_group_arn, null), "")

  db_ref_endpoint   = trimspace(coalesce(try(var.database_reference.endpoint, null), ""))
  db_ref_port       = coalesce(try(var.database_reference.port, null), 3306)
  db_ref_name       = coalesce(try(var.database_reference.db_name, null), var.db_name)
  db_ref_username   = coalesce(try(var.database_reference.username, null), var.db_master_username)
  db_ref_secret_arn = coalesce(try(var.database_reference.password_secret_arn, null), "")

  backup_cfg_rds_backup_retention_days = coalesce(try(var.backup_config.rds_backup_retention_days, null), var.db_backup_retention_days)
  backup_cfg_rds_skip_final_snapshot   = coalesce(try(var.backup_config.rds_skip_final_snapshot, null), true)
  backup_cfg_rds_delete_auto_backups   = coalesce(try(var.backup_config.rds_delete_automated_backups, null), true)
  backup_cfg_efs_transition_to_ia      = coalesce(try(var.backup_config.efs_transition_to_ia, null), var.efs_transition_to_ia)

  vpce_logs_enabled           = coalesce(try(var.vpc_endpoints.logs, null), true)
  vpce_secretsmanager_enabled = coalesce(try(var.vpc_endpoints.secretsmanager, null), true)
  vpce_s3_enabled             = coalesce(try(var.vpc_endpoints.s3, null), true)

  db_publicly_accessible_effective = coalesce(try(var.db_connectivity.publicly_accessible, null), false)
  db_allowed_cidrs_effective       = coalesce(try(var.db_connectivity.allowed_cidrs, null), [])

  external_db_endpoint = trimspace(var.db_endpoint_override) != "" ? trimspace(var.db_endpoint_override) : local.db_ref_endpoint
  create_db_effective  = local.external_db_endpoint == "" ? (var.create_db != null ? var.create_db : local.preset.create_db) : false

  enable_nat_gateway_effective = var.enable_nat_gateway != null ? var.enable_nat_gateway : local.preset.nat_gateway

  resolved_azs = length(var.availability_zones) > 0 ? var.availability_zones : slice(data.aws_availability_zones.available.names, 0, max(max(length(var.public_subnet_cidrs), length(var.private_subnet_cidrs)), length(var.db_subnet_cidrs)))

  app_name  = var.name_prefix
  tags      = merge(var.tags, { Name = local.app_name })

  vpc_id_reference_fallback = local.network_ref_vpc_id
  vpc_id_effective          = local.create_vpc_effective ? aws_vpc.main[0].id : local.vpc_id_reference_fallback

  public_subnet_ids_reference  = local.network_ref_public_subnet_ids
  private_subnet_ids_reference = local.network_ref_private_subnet_ids
  db_subnet_ids_reference      = local.network_ref_db_subnet_ids
  nat_gateway_ids_reference    = local.network_ref_nat_gateway_ids

  public_subnet_ids_created  = [for s in aws_subnet.public : s.id]
  private_subnet_ids_created = [for s in aws_subnet.private : s.id]
  db_subnet_ids_created      = [for s in aws_subnet.db : s.id]

  public_subnet_ids_effective  = length(local.public_subnet_ids_created) > 0 ? local.public_subnet_ids_created : local.public_subnet_ids_reference
  private_subnet_ids_effective = length(local.private_subnet_ids_created) > 0 ? local.private_subnet_ids_created : local.private_subnet_ids_reference
  db_subnet_ids_effective      = length(local.db_subnet_ids_created) > 0 ? local.db_subnet_ids_created : local.db_subnet_ids_reference

  task_subnet_ids_effective  = local.enable_nat_gateway_effective ? local.private_subnet_ids_effective : local.public_subnet_ids_effective
  assign_public_ip_effective = var.assign_public_ip != null ? var.assign_public_ip : !local.enable_nat_gateway_effective

  alb_arn_reference = local.lb_ref_alb_arn
  alb_sg_reference  = local.lb_ref_alb_security_group_id

  alb_name_effective = var.alb_name != "" ? var.alb_name : "${local.app_name}-alb"

  alb_arn_effective               = local.create_load_balancer_effective ? aws_lb.shared[0].arn : local.alb_arn_reference
  alb_dns_name_effective          = local.create_load_balancer_effective ? aws_lb.shared[0].dns_name : (local.lb_ref_alb_dns_name != "" ? local.lb_ref_alb_dns_name : try(data.aws_lb.shared[0].dns_name, ""))
  alb_zone_id_effective           = local.create_load_balancer_effective ? aws_lb.shared[0].zone_id : (local.lb_ref_alb_zone_id != "" ? local.lb_ref_alb_zone_id : try(data.aws_lb.shared[0].zone_id, ""))
  alb_security_group_id_effective = local.create_load_balancer_effective ? aws_security_group.alb[0].id : (local.alb_sg_reference != "" ? local.alb_sg_reference : try(element(data.aws_lb.shared[0].security_groups, 0), ""))

  hosted_zone_id_effective = var.hosted_zone_id != "" ? var.hosted_zone_id : (var.use_hosted_zone_lookup ? try(data.aws_route53_zone.app[0].zone_id, "") : "")

  openemr_hostnames_effective = distinct(compact(concat([var.openemr_hostname], var.openemr_hostnames)))
  openemr_primary_hostname    = local.openemr_hostnames_effective[0]

  service_discovery_namespace = "${var.name_prefix}.internal"
  openemr_oauth_site_addr     = "https://${local.openemr_primary_hostname}"

  https_listener_arn_reference = local.lb_ref_https_listener_arn
  http_listener_arn_reference  = local.lb_ref_http_listener_arn

  https_listener_arn_effective = local.https_listener_arn_reference != "" ? local.https_listener_arn_reference : aws_lb_listener.https[0].arn
  http_listener_arn_effective  = local.http_listener_arn_reference != "" ? local.http_listener_arn_reference : aws_lb_listener.http[0].arn

  openemr_target_group_arn_reference = local.lb_ref_openemr_tg_arn

  openemr_target_group_arn_effective = local.openemr_target_group_arn_reference != "" ? local.openemr_target_group_arn_reference : aws_lb_target_group.openemr[0].arn

  openemr_sites_volume_name = "openemr-sites"

  db_secret_key_master_password = "master_password"
  app_secret_key_session_secret = "app_session_secret"
  app_secret_key_client_id      = "openemr_client_id"

  openemr_secret_key_admin_pass   = "openemr_admin_password"
  openemr_secret_key_dr_chen_pass = "dr_chen_password"
  openemr_secret_key_rx_pass      = "rx_patel_password"
  openemr_secret_key_rn_pass      = "rn_johnson_password"

  db_endpoint_effective = local.create_db_effective ? aws_db_instance.mysql[0].address : local.external_db_endpoint
  db_port_effective     = local.create_db_effective ? 3306 : local.db_ref_port
  db_name_effective     = local.create_db_effective ? var.db_name : local.db_ref_name
  db_username_effective = local.create_db_effective ? var.db_master_username : local.db_ref_username

  rds_secret_arn_effective = local.create_db_effective ? aws_secretsmanager_secret.rds[0].arn : local.db_ref_secret_arn

  backup_rds_retention_days = local.backup_cfg_rds_backup_retention_days
  backup_rds_skip_snapshot  = local.backup_cfg_rds_skip_final_snapshot
  backup_rds_delete_auto    = local.backup_cfg_rds_delete_auto_backups
  backup_efs_transition_ia  = local.backup_cfg_efs_transition_to_ia

  endpoint_subnet_ids_effective     = local.enable_nat_gateway_effective ? local.private_subnet_ids_effective : local.public_subnet_ids_effective
  public_route_table_ids_effective  = length([for rt in aws_route_table.public : rt.id]) > 0 ? [for rt in aws_route_table.public : rt.id] : local.network_ref_public_route_table_ids
  private_route_table_ids_effective = length([for rt in aws_route_table.private : rt.id]) > 0 ? [for rt in aws_route_table.private : rt.id] : local.network_ref_private_route_table_ids
  db_route_table_ids_effective      = length([for rt in aws_route_table.db : rt.id]) > 0 ? [for rt in aws_route_table.db : rt.id] : local.network_ref_db_route_table_ids

  nat_gateway_id_effective      = length(local.nat_gateway_ids_reference) > 0 ? local.nat_gateway_ids_reference[0] : try(aws_nat_gateway.main[0].id, "")
  internet_gateway_id_effective = local.network_ref_internet_gateway_id != "" ? local.network_ref_internet_gateway_id : try(aws_internet_gateway.main[0].id, "")

  endpoint_route_table_ids_effective = local.enable_nat_gateway_effective ? local.private_route_table_ids_effective : local.public_route_table_ids_effective

  create_logs_endpoint           = var.create_vpc_endpoints && local.vpce_logs_enabled
  create_secretsmanager_endpoint = var.create_vpc_endpoints && local.vpce_secretsmanager_enabled
  create_s3_endpoint             = var.create_vpc_endpoints && local.vpce_s3_enabled
}

data "aws_route53_zone" "app" {
  count        = var.hosted_zone_id == "" && var.use_hosted_zone_lookup && var.hosted_zone_name != "" ? 1 : 0
  name         = var.hosted_zone_name
  private_zone = false
}

data "aws_lb" "shared" {
  count = !local.create_load_balancer_effective && local.alb_arn_reference != "" ? 1 : 0
  arn   = local.alb_arn_reference
}

resource "terraform_data" "validations" {
  input = "validation"

  lifecycle {
    precondition {
      condition     = local.create_vpc_effective || local.vpc_id_reference_fallback != ""
      error_message = "Either create_vpc must be true or a vpc_id/network_reference.vpc_id must be provided."
    }

    precondition {
      condition     = length(local.public_subnet_ids_effective) > 0
      error_message = "At least one public subnet is required (created or referenced)."
    }

    precondition {
      condition     = !local.enable_nat_gateway_effective || length(local.private_subnet_ids_effective) > 0
      error_message = "NAT mode requires private subnet IDs (created or referenced)."
    }

    precondition {
      condition     = local.create_load_balancer_effective || local.alb_arn_reference != ""
      error_message = "Either create_load_balancer must be true or an ALB ARN must be provided."
    }

    precondition {
      condition     = local.create_db_effective || local.external_db_endpoint != ""
      error_message = "Either create_db must be true or db_endpoint_override/database_reference.endpoint must be provided."
    }

    precondition {
      condition     = !local.create_db_effective || length(local.db_subnet_ids_effective) > 0
      error_message = "DB creation requires dedicated db_subnet_ids (created or referenced)."
    }

    precondition {
      condition     = local.create_db_effective || local.rds_secret_arn_effective != ""
      error_message = "When using external DB, database_reference.password_secret_arn is required."
    }

    precondition {
      condition     = !local.db_publicly_accessible_effective || length(local.db_allowed_cidrs_effective) > 0
      error_message = "When db_connectivity.publicly_accessible is true, set db_connectivity.allowed_cidrs."
    }

    precondition {
      condition     = !local.db_publicly_accessible_effective || local.internet_gateway_id_effective != ""
      error_message = "Publicly accessible DB requires an internet gateway (referenced or created)."
    }

    precondition {
      condition     = !local.create_acm_certificate_effective || local.hosted_zone_id_effective != ""
      error_message = "ACM DNS validation requires hosted_zone_id or hosted_zone_name with lookup enabled."
    }

    precondition {
      condition     = local.enable_nat_gateway_effective || local.assign_public_ip_effective
      error_message = "When NAT is disabled, ECS tasks must use assign_public_ip=true."
    }

    precondition {
      condition     = !local.enable_nat_gateway_effective || !local.assign_public_ip_effective
      error_message = "When NAT is enabled, ECS tasks must use assign_public_ip=false and private subnets."
    }

    precondition {
      condition     = local.create_acm_certificate_effective || local.https_listener_arn_reference != "" || var.existing_certificate_arn != ""
      error_message = "When ACM creation is disabled, provide either an existing HTTPS listener ARN or existing_certificate_arn."
    }
  }
}
