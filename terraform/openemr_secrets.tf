resource "random_password" "db_master" {
  count            = local.create_db_effective ? 1 : 0
  length           = 30
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "random_password" "openemr_admin" {
  length           = 30
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "random_password" "openemr_dr_chen" {
  length           = 24
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "random_password" "openemr_rx_patel" {
  length           = 24
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "random_password" "openemr_rn_johnson" {
  length           = 24
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "aws_secretsmanager_secret" "openemr_configuration" {
  name                    = var.openemr_configuration_secret_name
  recovery_window_in_days = 7
  tags                    = merge(local.tags, { Name = "${local.app_name}-openemr-config" })
}

resource "aws_secretsmanager_secret_version" "openemr_configuration" {
  secret_id = aws_secretsmanager_secret.openemr_configuration.id
  secret_string = jsonencode({
    (local.openemr_secret_key_admin_pass)   = random_password.openemr_admin.result
    (local.openemr_secret_key_dr_chen_pass) = random_password.openemr_dr_chen.result
    (local.openemr_secret_key_rx_pass)      = random_password.openemr_rx_patel.result
    (local.openemr_secret_key_rn_pass)      = random_password.openemr_rn_johnson.result
  })

  lifecycle {
    ignore_changes = [secret_string]
  }
}

resource "aws_secretsmanager_secret" "rds" {
  count                   = local.create_db_effective ? 1 : 0
  name                    = var.rds_secret_name
  recovery_window_in_days = 7
  tags                    = merge(local.tags, { Name = "${local.app_name}-rds" })
}

resource "aws_secretsmanager_secret_version" "rds" {
  count     = local.create_db_effective ? 1 : 0
  secret_id = aws_secretsmanager_secret.rds[0].id
  secret_string = jsonencode({
    username                              = var.db_master_username
    (local.db_secret_key_master_password) = random_password.db_master[0].result
  })

  lifecycle {
    ignore_changes = [secret_string]
  }
}

resource "aws_cloudwatch_log_group" "openemr" {
  name              = "/ecs/${var.openemr_task_family}"
  retention_in_days = 30
  tags              = merge(local.tags, { Name = "${local.app_name}-openemr-logs" })
}
