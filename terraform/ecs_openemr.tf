resource "aws_service_discovery_private_dns_namespace" "internal" {
  name        = local.service_discovery_namespace
  vpc         = local.vpc_id_effective
  description = "Internal namespace for ${local.app_name} services"
  tags        = merge(local.tags, { Name = "${local.app_name}-sd-namespace" })
}

resource "aws_service_discovery_service" "openemr" {
  name = "openemr"

  dns_config {
    namespace_id = aws_service_discovery_private_dns_namespace.internal.id
    dns_records {
      ttl  = 10
      type = "A"
    }
    routing_policy = "MULTIVALUE"
  }

  health_check_custom_config {
    failure_threshold = 1
  }

  tags = merge(local.tags, { Name = "${local.app_name}-sd-openemr" })
}

resource "aws_security_group" "openemr" {
  name        = "${local.app_name}-openemr-sg"
  description = "Security group for OpenEMR tasks"
  vpc_id      = local.vpc_id_effective

  ingress {
    description     = "Allow OpenEMR traffic from ALB only"
    from_port       = var.openemr_container_port
    to_port         = var.openemr_container_port
    protocol        = "tcp"
    security_groups = [local.alb_security_group_id_effective]
  }

  ingress {
    description     = "Allow app traffic to OpenEMR"
    from_port       = var.openemr_container_port
    to_port         = var.openemr_container_port
    protocol        = "tcp"
    security_groups = [aws_security_group.app.id]
  }

  egress {
    description = "Allow MySQL to DB"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    description = "Allow NFS to EFS"
    from_port   = 2049
    to_port     = 2049
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    description = "Allow HTTPS outbound"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-sg" })
}

resource "aws_iam_role" "task_openemr" {
  name = "${local.app_name}-task-role-openemr"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ecs-tasks.amazonaws.com"
        }
      }
    ]
  })

  tags = merge(local.tags, { Name = "${local.app_name}-task-role-openemr" })
}

resource "aws_ecs_task_definition" "openemr" {
  family                   = var.openemr_task_family
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = tostring(var.openemr_task_cpu)
  memory                   = tostring(var.openemr_task_memory)
  execution_role_arn       = aws_iam_role.task_execution.arn
  task_role_arn            = aws_iam_role.task_openemr.arn

  container_definitions = jsonencode([
    {
      name      = "seed-sites"
      image     = var.openemr_image
      essential = false
      command = [
        "/bin/sh",
        "-lc",
        "set -euo pipefail; if [ -f /seed-sites/default/sqlconf.php ]; then echo 'OpenEMR sites already configured; skipping seed'; exit 0; fi; if [ ! -f /seed-sites/.base_seed_complete ]; then echo 'Seeding OpenEMR sites into EFS'; rsync --owner --group --perms --recursive --links --ignore-existing /swarm-pieces/sites/ /seed-sites/; rm -f /seed-sites/docker-leader /seed-sites/docker-initiated /seed-sites/docker-completed; touch /seed-sites/.base_seed_complete; else echo 'OpenEMR base seed already complete; skipping'; fi"
      ]
      mountPoints = [
        {
          sourceVolume  = local.openemr_sites_volume_name
          containerPath = "/seed-sites"
          readOnly      = false
        }
      ]
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.openemr.name
          awslogs-region        = var.aws_region
          awslogs-stream-prefix = "seed"
        }
      }
    },
    {
      name      = "openemr"
      image     = var.openemr_image
      essential = true
      dependsOn = [
        {
          containerName = "seed-sites"
          condition     = "SUCCESS"
        }
      ]
      portMappings = [
        {
          containerPort = var.openemr_container_port
          hostPort      = var.openemr_container_port
          protocol      = "tcp"
        }
      ]
      environment = [
        { name = "MYSQL_HOST", value = local.db_endpoint_effective },
        { name = "MYSQL_ROOT_USER", value = local.db_username_effective },
        { name = "MYSQL_DATABASE", value = local.db_name_effective },
        { name = "MYSQL_USER", value = local.db_username_effective },
        { name = "OE_USER", value = "admin" },
        { name = "SWARM_MODE", value = "no" },
        { name = "OPENEMR_SETTING_site_addr_oath", value = local.openemr_oauth_site_addr },
        { name = "OPENEMR_SETTING_oauth_password_grant", value = "3" },
        { name = "OPENEMR_SETTING_rest_system_scopes_api", value = "1" },
        { name = "OPENEMR_SETTING_rest_api", value = "1" },
        { name = "OPENEMR_SETTING_rest_fhir_api", value = "1" },
        { name = "OPENEMR_SETTING_rest_portal_api", value = "1" },
      ]
      secrets = [
        { name = "MYSQL_ROOT_PASS", valueFrom = "${local.rds_secret_arn_effective}:${local.db_secret_key_master_password}::" },
        { name = "MYSQL_PASS", valueFrom = "${local.rds_secret_arn_effective}:${local.db_secret_key_master_password}::" },
        { name = "OE_PASS", valueFrom = "${aws_secretsmanager_secret.openemr_configuration.arn}:${local.openemr_secret_key_admin_pass}::" },
      ]
      mountPoints = [
        {
          sourceVolume  = local.openemr_sites_volume_name
          containerPath = "/var/www/localhost/htdocs/openemr/sites"
          readOnly      = false
        }
      ]
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.openemr.name
          awslogs-region        = var.aws_region
          awslogs-stream-prefix = "ecs"
        }
      }
    }
  ])

  volume {
    name = local.openemr_sites_volume_name

    efs_volume_configuration {
      file_system_id     = aws_efs_file_system.openemr_sites.id
      root_directory     = "/"
      transit_encryption = "ENABLED"

      authorization_config {
        access_point_id = aws_efs_access_point.openemr_sites.id
        iam             = "DISABLED"
      }
    }
  }

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-taskdef" })
}

resource "aws_ecs_service" "openemr" {
  name            = var.openemr_service_name
  cluster         = aws_ecs_cluster.app.id
  task_definition = aws_ecs_task_definition.openemr.arn
  launch_type     = "FARGATE"
  desired_count   = var.openemr_desired_count

  deployment_minimum_healthy_percent = var.openemr_deployment_minimum_healthy_percent
  deployment_maximum_percent         = var.openemr_deployment_maximum_percent
  health_check_grace_period_seconds  = var.openemr_health_check_grace_period_seconds

  deployment_circuit_breaker {
    enable   = true
    rollback = true
  }

  network_configuration {
    subnets          = local.task_subnet_ids_effective
    security_groups  = [aws_security_group.openemr.id]
    assign_public_ip = local.assign_public_ip_effective
  }

  load_balancer {
    target_group_arn = local.openemr_target_group_arn_effective
    container_name   = "openemr"
    container_port   = var.openemr_container_port
  }

  service_registries {
    registry_arn = aws_service_discovery_service.openemr.arn
  }

  depends_on = [
    time_sleep.openemr_prereq_settle,
    aws_efs_access_point.openemr_sites,
    aws_efs_mount_target.openemr_sites,
    aws_lb_listener_rule.openemr_host_ip_locked,
    aws_iam_role_policy_attachment.task_execution_managed,
    aws_iam_role_policy.task_execution_secrets,
  ]

  tags = merge(local.tags, { Name = "${local.app_name}-openemr-service" })
}
