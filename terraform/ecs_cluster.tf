resource "aws_ecs_cluster" "app" {
  name = var.ecs_cluster_name
  tags = merge(local.tags, { Name = "${local.app_name}-cluster" })
}

output "ecs_cluster_arn" {
  description = "ECS cluster ARN."
  value       = aws_ecs_cluster.app.arn
}

output "ecs_cluster_name" {
  description = "ECS cluster name."
  value       = aws_ecs_cluster.app.name
}
