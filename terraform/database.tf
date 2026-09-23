resource "aws_security_group" "rds" {
  name        = "${local.app_name}-rds-sg"
  description = "Security group for RDS MySQL"
  vpc_id      = local.vpc_id_effective

  ingress {
    description     = "Allow OpenEMR to MySQL"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.openemr.id]
  }

  dynamic "ingress" {
    for_each = local.db_publicly_accessible_effective ? local.db_allowed_cidrs_effective : []
    content {
      description = "Optional client access"
      from_port   = 3306
      to_port     = 3306
      protocol    = "tcp"
      cidr_blocks = [ingress.value]
    }
  }

  tags = merge(local.tags, { Name = "${local.app_name}-rds-sg" })
}

resource "aws_db_subnet_group" "mysql" {
  count      = local.create_db_effective ? 1 : 0
  name       = "${local.app_name}-mysql-subnet-group"
  subnet_ids = local.db_subnet_ids_effective
  tags       = merge(local.tags, { Name = "${local.app_name}-mysql-subnet-group" })
}

resource "aws_db_instance" "mysql" {
  count                        = local.create_db_effective ? 1 : 0
  identifier                   = "${local.app_name}-mysql"
  engine                       = "mysql"
  engine_version               = "8.0"
  instance_class               = var.db_instance_class
  allocated_storage            = var.db_allocated_storage
  max_allocated_storage        = var.db_max_allocated_storage
  storage_type                 = "gp3"
  db_name                      = var.db_name
  username                     = var.db_master_username
  password                     = random_password.db_master[0].result
  backup_retention_period      = local.backup_rds_retention_days
  multi_az                     = var.db_multi_az
  deletion_protection          = var.deletion_protection
  publicly_accessible          = local.db_publicly_accessible_effective
  skip_final_snapshot          = local.backup_rds_skip_snapshot
  delete_automated_backups     = local.backup_rds_delete_auto
  apply_immediately            = true
  db_subnet_group_name         = aws_db_subnet_group.mysql[0].name
  vpc_security_group_ids       = [aws_security_group.rds.id]
  auto_minor_version_upgrade   = true
  performance_insights_enabled = false

  tags = merge(local.tags, { Name = "${local.app_name}-mysql" })
}

resource "time_sleep" "openemr_prereq_settle" {
  depends_on = [aws_db_instance.mysql, aws_efs_access_point.openemr_sites, aws_efs_mount_target.openemr_sites]

  create_duration = "${var.openemr_prereq_settle_seconds}s"
}
