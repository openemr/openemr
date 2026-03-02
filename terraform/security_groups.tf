resource "aws_security_group" "app" {
  name        = "${local.app_name}-app-sg"
  description = "Security group for app tasks that call OpenEMR"
  vpc_id      = local.vpc_id_effective

  egress {
    description = "Allow HTTPS egress"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    description = "Allow OpenEMR calls"
    from_port   = var.openemr_container_port
    to_port     = var.openemr_container_port
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.tags, { Name = "${local.app_name}-app-sg" })
}
