# OpenEMR on AWS ECS (Terraform Module)

This Terraform module deploys OpenEMR on ECS/Fargate with optional self-managed networking, load balancer, DNS/TLS, database, and monitoring.

You can run it in three ways:

- `turnkey`: create almost everything automatically.
- `hybrid`: reuse existing infrastructure and create missing pieces.
- `byo`: bring your own infrastructure references.

Default preset is `turnkey` for lowest-lift deployment.

Creation flags always win over references:

- `create_vpc = true` ignores `network_reference`.
- `create_load_balancer = true` ignores `load_balancer_reference`.

If references are partial/missing, Terraform creates missing dependencies (for example listeners, target groups, route tables, NAT/IGW where required).

## What gets deployed

- ECS cluster and OpenEMR ECS service
- EFS for persistent OpenEMR `sites/`
- Optional RDS MySQL
- Optional ALB listeners/target group with HTTP -> HTTPS redirect
- Optional Route53 records and ACM cert
- Optional CloudWatch alarms and operations dashboard
- Optional VPC endpoints for private AWS service access

## Prerequisites

1. Terraform `>= 1.6`
2. AWS credentials with rights for VPC, ALB, ECS, IAM, RDS, EFS, Route53, ACM, CloudWatch, Secrets Manager
3. If using DNS/cert creation:
   - a Route53 public hosted zone
   - a hostname for OpenEMR
4. Container image URI to deploy (`openemr_image`)

## Module usage (Turnkey)

In your root Terraform configuration:

```hcl
terraform {
  required_version = ">= 1.6.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = "us-east-2"
}

module "openemr" {
  source = "./terraform"

  deployment_preset      = "turnkey"
  create_vpc             = true
  create_load_balancer   = true
  create_db              = true
  create_acm_certificate = true
  use_hosted_zone_lookup = true
  hosted_zone_name       = "example.com"
  openemr_hostname       = "openemr.example.com"
  openemr_allowed_cidrs  = ["203.0.113.10/32"]
  openemr_image          = "openemr/openemr:8.0.1"
}
```

Example input files are included:

- `terraform/terraform.turnkey.tfvars.example`
- `terraform/terraform.hybrid.tfvars.example`
- `terraform/terraform.byo.tfvars.example`

## Validation

Before opening a PR, run local Terraform checks from the `terraform/` directory:

```sh
terraform fmt -check
terraform validate
tflint
```

These checks keep formatting, configuration validity, and linting aligned with repository quality expectations.

## Hybrid Example (reuse network and ALB)

```hcl
deployment_preset = "hybrid"

network_reference = {
  vpc_id             = "vpc-0123456789abcdef0"
  public_subnet_ids  = ["subnet-a", "subnet-b"]
  private_subnet_ids = ["subnet-c", "subnet-d"]
  db_subnet_ids      = ["subnet-e", "subnet-f"]
}

load_balancer_reference = {
  alb_arn               = "arn:aws:elasticloadbalancing:us-east-2:123456789012:loadbalancer/app/example/abc123"
  alb_security_group_id = "sg-0123456789abcdef0"
  http_listener_arn     = "arn:aws:elasticloadbalancing:us-east-2:123456789012:listener/app/example/abc123/def456"
  https_listener_arn    = "arn:aws:elasticloadbalancing:us-east-2:123456789012:listener/app/example/abc123/ghi789"
}

hosted_zone_name      = "example.com"
openemr_hostname      = "openemr.example.com"
openemr_allowed_cidrs = ["203.0.113.10/32"]
```

## BYO Example (external DB + existing ALB)

```hcl
deployment_preset      = "byo"
create_db              = false
create_acm_certificate = false

network_reference = {
  vpc_id             = "vpc-0123456789abcdef0"
  public_subnet_ids  = ["subnet-a", "subnet-b"]
  private_subnet_ids = ["subnet-c", "subnet-d"]
  db_subnet_ids      = ["subnet-e", "subnet-f"]
}

load_balancer_reference = {
  alb_arn                  = "arn:aws:elasticloadbalancing:us-east-2:123456789012:loadbalancer/app/example/abc123"
  alb_security_group_id    = "sg-0123456789abcdef0"
  https_listener_arn       = "arn:aws:elasticloadbalancing:us-east-2:123456789012:listener/app/example/abc123/ghi789"
  http_listener_arn        = "arn:aws:elasticloadbalancing:us-east-2:123456789012:listener/app/example/abc123/def456"
  openemr_target_group_arn = "arn:aws:elasticloadbalancing:us-east-2:123456789012:targetgroup/openemr/xyz123"
}

database_reference = {
  endpoint            = "mydb.abc.us-east-2.rds.amazonaws.com"
  port                = 3306
  db_name             = "openemr_primary"
  username            = "openemr_admin"
  password_secret_arn = "arn:aws:secretsmanager:us-east-2:123456789012:secret:external-db"
}

existing_certificate_arn = "arn:aws:acm:us-east-2:123456789012:certificate/uuid"
openemr_hostname         = "openemr.example.com"
openemr_allowed_cidrs    = ["203.0.113.10/32"]
```

## NAT and subnet behavior

Subnet tiers:

- `public`: ALB and optional NAT gateway
- `private`: ECS tasks when NAT is enabled
- `db`: dedicated RDS subnets (always separate from app tiers)

- `enable_nat_gateway = true`
  - ECS tasks run in private subnets
  - `assign_public_ip = false`
- `enable_nat_gateway = false`
  - ECS tasks run in public subnets
  - `assign_public_ip = true`
  - Tasks remain ALB-only reachable via security groups (no direct ingress)

Route table behavior:

- By default `manage_route_table_associations = true`.
- Terraform explicitly associates every subnet in each tier (`public`, `private`, `db`) to a route table.
- If route table IDs are provided and there are more subnets than route tables, associations are mapped deterministically.

## VPC endpoints (optional)

Enable private service access with:

```hcl
create_vpc_endpoints = true
vpc_endpoints = {
  logs           = true
  secretsmanager = true
  s3             = true
}
```

Useful when running private tasks and reducing NAT dependency.

## Monitoring and dashboard

Enable with:

```hcl
create_monitoring   = true
alarm_sns_topic_arn = "arn:aws:sns:us-east-2:123456789012:openemr-alerts"
```

Creates:

- ALB 5xx / unhealthy hosts / latency alarms
- ECS CPU and memory alarms
- RDS CPU and free storage alarms (if RDS is created)
- A CloudWatch dashboard with ALB, ECS, EFS, RDS, and recent OpenEMR error logs

## Backup configuration

Use `backup_config` to tune RDS/EFS retention behavior:

```hcl
backup_config = {
  rds_backup_retention_days    = 14
  rds_skip_final_snapshot      = false
  rds_delete_automated_backups = false
  efs_transition_to_ia         = "AFTER_30_DAYS"
}
```

## DB connectivity options

By default, RDS is private in dedicated DB subnets.

To allow direct client access (for example admin client tools):

```hcl
db_connectivity = {
  publicly_accessible = true
  allowed_cidrs       = ["203.0.113.10/32"]
}
```

Keep this list narrow in production.

## Deploy flow after apply

1. Publish your image to a registry and set `openemr_image`.
2. Run `terraform apply`.
3. Force a new OpenEMR deployment when task definition or image changes.

## Verify deployment

1. Open `https://<openemr_hostname>` from an allowed CIDR.
2. Confirm ALB target group healthy hosts > 0.
3. Confirm ECS OpenEMR service desired/running task count is stable.
4. Confirm logs in `/ecs/<openemr_task_family>` and dashboard widgets are green.

## Troubleshooting

- `OpenEMR not configured` on first startup:
  - verify EFS mount and seed container logs
  - verify DB endpoint/credentials secret wiring
- TLS cert not validating:
  - confirm hosted zone is correct and ACM DNS records exist
- Service fails to pull image in private mode:
  - enable NAT or create required VPC endpoints

## Key output

Use output `deployment` for automation. It contains effective network, load balancer, DNS/TLS, compute, database, storage, and monitoring references.
