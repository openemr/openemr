variable "openemr_hostname" {
  description = "Public hostname for OpenEMR."
  type        = string
  default     = "openemr.example.com"
}

variable "openemr_allowed_cidrs" {
  description = "CIDRs allowed to access the OpenEMR public host through ALB."
  type        = list(string)
  default     = ["203.0.113.10/32"]
}

variable "openemr_host_rule_priority" {
  description = "Priority for OpenEMR host-based rule."
  type        = number
  default     = 211
}

variable "openemr_http_redirect_priority" {
  description = "Priority for OpenEMR HTTP -> HTTPS redirect rule."
  type        = number
  default     = 213
}

variable "openemr_container_port" {
  description = "Container port exposed by OpenEMR inside ECS."
  type        = number
  default     = 80
}

variable "openemr_health_check_path" {
  description = "ALB target group health check path for OpenEMR."
  type        = string
  default     = "/"
}

variable "openemr_service_name" {
  description = "ECS service name for OpenEMR."
  type        = string
  default     = "openemr-service"
}

variable "openemr_task_family" {
  description = "ECS task definition family for OpenEMR."
  type        = string
  default     = "openemr-task"
}

variable "openemr_image" {
  description = "Container image URI for OpenEMR task containers."
  type        = string
  default     = "openemr/openemr:8.0.1"
}

variable "openemr_task_cpu" {
  description = "CPU units for OpenEMR task."
  type        = number
  default     = 1024
}

variable "openemr_task_memory" {
  description = "Memory (MiB) for OpenEMR task."
  type        = number
  default     = 2048
}

variable "openemr_desired_count" {
  description = "Desired OpenEMR task count."
  type        = number
  default     = 1
}

variable "openemr_deployment_minimum_healthy_percent" {
  description = "Lower bound healthy OpenEMR tasks during deploys. Use 0 for stop-first/start-next."
  type        = number
  default     = 0
}

variable "openemr_deployment_maximum_percent" {
  description = "Upper bound running OpenEMR tasks during deploys. Keep 100 to avoid overlap."
  type        = number
  default     = 100
}

variable "openemr_health_check_grace_period_seconds" {
  description = "Grace period before ECS uses ALB health checks for OpenEMR service."
  type        = number
  default     = 1200
}

variable "openemr_prereq_settle_seconds" {
  description = "Seconds to wait after RDS/EFS provisioning before starting OpenEMR service."
  type        = number
  default     = 300
}

variable "openemr_configuration_secret_name" {
  description = "Secrets Manager secret name for OpenEMR runtime config."
  type        = string
  default     = "openemr/prod/openemr_configuration"
}

variable "rds_secret_name" {
  description = "Secrets Manager secret name for RDS credentials."
  type        = string
  default     = "openemr/prod/rds"
}

variable "db_name" {
  description = "OpenEMR database name."
  type        = string
  default     = "openemr_primary"
}

variable "db_master_username" {
  description = "Master username for RDS MySQL."
  type        = string
  default     = "openemr_admin"
}

variable "db_instance_class" {
  description = "RDS instance class (non-Aurora)."
  type        = string
  default     = "db.t4g.micro"
}

variable "db_allocated_storage" {
  description = "Allocated RDS storage (GiB)."
  type        = number
  default     = 20
}

variable "db_max_allocated_storage" {
  description = "Max autoscaled RDS storage (GiB)."
  type        = number
  default     = 50
}

variable "db_backup_retention_days" {
  description = "RDS backup retention period in days."
  type        = number
  default     = 7
}

variable "db_multi_az" {
  description = "Enable multi-AZ for RDS."
  type        = bool
  default     = false
}

variable "deletion_protection" {
  description = "Enable deletion protection on stateful resources."
  type        = bool
  default     = true
}

variable "efs_encrypted" {
  description = "Enable encryption at rest for OpenEMR EFS."
  type        = bool
  default     = true
}

variable "efs_performance_mode" {
  description = "EFS performance mode."
  type        = string
  default     = "generalPurpose"
}

variable "efs_throughput_mode" {
  description = "EFS throughput mode."
  type        = string
  default     = "bursting"
}

variable "efs_transition_to_ia" {
  description = "Lifecycle policy transition for EFS files."
  type        = string
  default     = "AFTER_30_DAYS"
}
