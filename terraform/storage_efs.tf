resource "aws_security_group" "efs" {
  name        = "${local.app_name}-efs-sg"
  description = "Security group for OpenEMR EFS"
  vpc_id      = local.vpc_id_effective

  ingress {
    description     = "Allow OpenEMR tasks to mount EFS"
    from_port       = 2049
    to_port         = 2049
    protocol        = "tcp"
    security_groups = [aws_security_group.openemr.id]
  }

  tags = merge(local.tags, { Name = "${local.app_name}-efs-sg" })
}

resource "aws_efs_file_system" "openemr_sites" {
  encrypted        = var.efs_encrypted
  performance_mode = var.efs_performance_mode
  throughput_mode  = var.efs_throughput_mode

  lifecycle_policy {
    transition_to_ia = local.backup_efs_transition_ia
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-sites-efs" })
}

resource "aws_efs_access_point" "openemr_sites" {
  file_system_id = aws_efs_file_system.openemr_sites.id

  root_directory {
    path = "/openemr-sites"

    creation_info {
      owner_gid   = 0
      owner_uid   = 0
      permissions = "0777"
    }
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-sites-ap" })
}

resource "aws_efs_mount_target" "openemr_sites" {
  for_each = toset(local.task_subnet_ids_effective)

  file_system_id  = aws_efs_file_system.openemr_sites.id
  subnet_id       = each.value
  security_groups = [aws_security_group.efs.id]
}
