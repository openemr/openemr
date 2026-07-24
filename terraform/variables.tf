variable "aws_region" {
  description = "AWS region for all resources."
  type        = string
  default     = "us-east-2"
}

variable "name_prefix" {
  description = "Prefix for named resources."
  type        = string
  default     = "openemr"
}

variable "deployment_preset" {
  description = "Optional deployment preset: turnkey, hybrid, or byo."
  type        = string
  default     = "turnkey"

  validation {
    condition     = contains(["turnkey", "hybrid", "byo"], var.deployment_preset)
    error_message = "deployment_preset must be one of: turnkey, hybrid, byo."
  }
}

variable "create_vpc" {
  description = "Create a VPC and networking resources. If true, network references are ignored."
  type        = bool
  default     = null
  nullable    = true
}

variable "enable_nat_gateway" {
  description = "Create and use NAT gateway for private subnet egress."
  type        = bool
  default     = null
  nullable    = true
}

variable "assign_public_ip" {
  description = "Override ECS task public IP assignment. Leave null to auto-compute from NAT setting."
  type        = bool
  default     = null
  nullable    = true
}

variable "network_reference" {
  description = "Optional existing network references; missing parts may be created when create_vpc is false."
  type = object({
    vpc_id                  = optional(string)
    public_subnet_ids       = optional(list(string))
    private_subnet_ids      = optional(list(string))
    db_subnet_ids           = optional(list(string))
    nat_gateway_ids         = optional(list(string))
    internet_gateway_id     = optional(string)
    public_route_table_ids  = optional(list(string))
    private_route_table_ids = optional(list(string))
    db_route_table_ids      = optional(list(string))
  })
  default = {}
}

variable "vpc_cidr" {
  description = "CIDR block for created VPC."
  type        = string
  default     = "10.42.0.0/16"
}

variable "public_subnet_cidrs" {
  description = "CIDR blocks for public subnets when created."
  type        = list(string)
  default     = ["10.42.0.0/20", "10.42.16.0/20"]
}

variable "private_subnet_cidrs" {
  description = "CIDR blocks for private subnets when created."
  type        = list(string)
  default     = ["10.42.128.0/20", "10.42.144.0/20"]
}

variable "db_subnet_cidrs" {
  description = "CIDR blocks for DB subnets when created."
  type        = list(string)
  default     = ["10.42.224.0/24", "10.42.225.0/24"]
}

variable "availability_zones" {
  description = "Availability zones to use for subnet creation. Leave empty to auto-select."
  type        = list(string)
  default     = []
}

variable "create_load_balancer" {
  description = "Create ALB/listeners if references are not supplied. If true, load balancer references are ignored."
  type        = bool
  default     = null
  nullable    = true
}

variable "load_balancer_reference" {
  description = "Optional existing ALB references; missing listeners/target groups may be created."
  type = object({
    alb_arn                  = optional(string)
    alb_dns_name             = optional(string)
    alb_zone_id              = optional(string)
    alb_security_group_id    = optional(string)
    http_listener_arn        = optional(string)
    https_listener_arn       = optional(string)
    openemr_target_group_arn = optional(string)
  })
  default = {}
}

variable "alb_name" {
  description = "Name for created ALB."
  type        = string
  default     = ""
}

variable "alb_internal" {
  description = "Whether created ALB is internal."
  type        = bool
  default     = false
}

variable "alb_allowed_cidrs" {
  description = "CIDRs allowed to reach created ALB on HTTP/HTTPS."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "hosted_zone_name" {
  description = "Public Route53 hosted zone name for DNS lookup."
  type        = string
  default     = ""
}

variable "hosted_zone_id" {
  description = "Explicit Route53 hosted zone ID; used when lookup is disabled or preferred."
  type        = string
  default     = ""
}

variable "use_hosted_zone_lookup" {
  description = "Lookup Route53 zone by name when hosted_zone_id is not provided."
  type        = bool
  default     = true
}

variable "create_acm_certificate" {
  description = "Create ACM certificate and DNS validation records for OpenEMR hostnames."
  type        = bool
  default     = null
  nullable    = true
}

variable "existing_certificate_arn" {
  description = "Existing ACM certificate ARN used when create_acm_certificate is false."
  type        = string
  default     = ""
}

variable "openemr_hostnames" {
  description = "Additional OpenEMR hostnames (SANs) for cert and listener rules."
  type        = list(string)
  default     = []
}

variable "create_db" {
  description = "Create RDS database unless overridden by explicit endpoint/reference."
  type        = bool
  default     = null
  nullable    = true
}

variable "db_endpoint_override" {
  description = "External DB endpoint that overrides RDS creation when non-empty."
  type        = string
  default     = ""
}

variable "database_reference" {
  description = "Optional existing database reference object."
  type = object({
    endpoint            = optional(string)
    port                = optional(number)
    db_name             = optional(string)
    username            = optional(string)
    password_secret_arn = optional(string)
  })
  default = {}
}

variable "create_vpc_endpoints" {
  description = "Create VPC endpoints for ECS private communications."
  type        = bool
  default     = true
}

variable "manage_route_table_associations" {
  description = "Whether Terraform should explicitly manage subnet to route table associations."
  type        = bool
  default     = true
}

variable "vpc_endpoints" {
  description = "Optional endpoint toggles."
  type = object({
    logs           = optional(bool, true)
    secretsmanager = optional(bool, true)
    s3             = optional(bool, true)
  })
  default = {}
}

variable "create_monitoring" {
  description = "Create CloudWatch alarms and dashboard."
  type        = bool
  default     = null
  nullable    = true
}

variable "alarm_sns_topic_arn" {
  description = "Optional SNS topic ARN for alarm actions."
  type        = string
  default     = ""
}

variable "backup_config" {
  description = "Optional backup and retention settings."
  type = object({
    rds_backup_retention_days    = optional(number)
    rds_skip_final_snapshot      = optional(bool)
    rds_delete_automated_backups = optional(bool)
    efs_transition_to_ia         = optional(string)
  })
  default = {}
}

variable "db_connectivity" {
  description = "DB network exposure controls."
  type = object({
    publicly_accessible = optional(bool)
    allowed_cidrs       = optional(list(string))
  })
  default = {}
}

variable "tags" {
  description = "Additional tags to merge into all resources."
  type        = map(string)
  default = {
    Project     = "openemr"
    Environment = "prod"
    ManagedBy   = "terraform"
  }
}

variable "ecs_cluster_name" {
  description = "ECS cluster name."
  type        = string
  default     = "openemr-cluster"
}
