resource "aws_vpc" "main" {
  count = local.create_vpc_effective ? 1 : 0

  cidr_block           = var.vpc_cidr
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = merge(local.tags, { Name = "${local.app_name}-vpc" })
}

resource "aws_internet_gateway" "main" {
  count = local.network_ref_internet_gateway_id == "" && local.vpc_id_effective != "" && (length(local.public_subnet_ids_effective) > 0 || local.db_publicly_accessible_effective) ? 1 : 0

  vpc_id = local.vpc_id_effective
  tags   = merge(local.tags, { Name = "${local.app_name}-igw" })
}

resource "aws_subnet" "public" {
  for_each = {
    for idx, cidr in var.public_subnet_cidrs : idx => cidr
    if local.vpc_id_effective != "" && length(local.public_subnet_ids_reference) == 0
  }

  vpc_id                  = local.vpc_id_effective
  cidr_block              = each.value
  map_public_ip_on_launch = true
  availability_zone       = local.resolved_azs[tonumber(each.key)]

  tags = merge(local.tags, { Name = "${local.app_name}-public-${each.key}" })
}

resource "aws_subnet" "private" {
  for_each = {
    for idx, cidr in var.private_subnet_cidrs : idx => cidr
    if local.enable_nat_gateway_effective && local.vpc_id_effective != "" && length(local.private_subnet_ids_reference) == 0
  }

  vpc_id            = local.vpc_id_effective
  cidr_block        = each.value
  availability_zone = local.resolved_azs[tonumber(each.key)]

  tags = merge(local.tags, { Name = "${local.app_name}-private-${each.key}" })
}

resource "aws_subnet" "db" {
  for_each = {
    for idx, cidr in var.db_subnet_cidrs : idx => cidr
    if local.vpc_id_effective != "" && length(local.db_subnet_ids_reference) == 0
  }

  vpc_id            = local.vpc_id_effective
  cidr_block        = each.value
  availability_zone = local.resolved_azs[tonumber(each.key)]

  tags = merge(local.tags, { Name = "${local.app_name}-db-${each.key}" })
}

resource "aws_eip" "nat" {
  count = local.enable_nat_gateway_effective && length(local.nat_gateway_ids_reference) == 0 && length(local.public_subnet_ids_effective) > 0 ? 1 : 0

  domain = "vpc"
  tags   = merge(local.tags, { Name = "${local.app_name}-nat-eip" })
}

resource "aws_nat_gateway" "main" {
  count = local.enable_nat_gateway_effective && length(local.nat_gateway_ids_reference) == 0 && length(local.public_subnet_ids_effective) > 0 ? 1 : 0

  allocation_id = aws_eip.nat[0].id
  subnet_id     = local.public_subnet_ids_effective[0]

  tags = merge(local.tags, { Name = "${local.app_name}-nat" })
}

resource "aws_route_table" "public" {
  for_each = {
    for idx, subnet_id in local.public_subnet_ids_effective : idx => subnet_id
    if local.internet_gateway_id_effective != "" && length(local.network_ref_public_route_table_ids) == 0
  }

  vpc_id = local.vpc_id_effective

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = local.internet_gateway_id_effective
  }

  tags = merge(local.tags, { Name = "${local.app_name}-public-rt-${each.key}" })
}

resource "aws_route_table" "private" {
  for_each = {
    for idx, subnet_id in local.private_subnet_ids_effective : idx => subnet_id
    if local.enable_nat_gateway_effective && local.nat_gateway_id_effective != "" && length(local.network_ref_private_route_table_ids) == 0
  }

  vpc_id = local.vpc_id_effective

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = local.nat_gateway_id_effective
  }

  tags = merge(local.tags, { Name = "${local.app_name}-private-rt-${each.key}" })
}

resource "aws_route_table" "db" {
  for_each = {
    for idx, subnet_id in local.db_subnet_ids_effective : idx => subnet_id
    if length(local.network_ref_db_route_table_ids) == 0
  }

  vpc_id = local.vpc_id_effective

  dynamic "route" {
    for_each = local.db_publicly_accessible_effective && local.internet_gateway_id_effective != "" ? [1] : []
    content {
      cidr_block = "0.0.0.0/0"
      gateway_id = local.internet_gateway_id_effective
    }
  }

  tags = merge(local.tags, { Name = "${local.app_name}-db-rt-${each.key}" })
}

resource "aws_route_table_association" "public" {
  for_each = {
    for idx, subnet_id in local.public_subnet_ids_effective : idx => subnet_id
    if var.manage_route_table_associations && length(local.public_route_table_ids_effective) > 0
  }

  subnet_id = each.value
  route_table_id = length(aws_route_table.public) > 0 ? aws_route_table.public[each.key].id : element(
    local.public_route_table_ids_effective,
    tonumber(each.key) % length(local.public_route_table_ids_effective)
  )
}

resource "aws_route_table_association" "private" {
  for_each = {
    for idx, subnet_id in local.private_subnet_ids_effective : idx => subnet_id
    if var.manage_route_table_associations && length(local.private_route_table_ids_effective) > 0
  }

  subnet_id = each.value
  route_table_id = length(aws_route_table.private) > 0 ? aws_route_table.private[each.key].id : element(
    local.private_route_table_ids_effective,
    tonumber(each.key) % length(local.private_route_table_ids_effective)
  )
}

resource "aws_route_table_association" "db" {
  for_each = {
    for idx, subnet_id in local.db_subnet_ids_effective : idx => subnet_id
    if var.manage_route_table_associations && length(local.db_route_table_ids_effective) > 0
  }

  subnet_id = each.value
  route_table_id = length(aws_route_table.db) > 0 ? aws_route_table.db[each.key].id : element(
    local.db_route_table_ids_effective,
    tonumber(each.key) % length(local.db_route_table_ids_effective)
  )
}

resource "aws_security_group" "vpc_endpoints" {
  count = var.create_vpc_endpoints ? 1 : 0

  name        = "${local.app_name}-vpce-sg"
  description = "Security group for VPC interface endpoints"
  vpc_id      = local.vpc_id_effective

  ingress {
    description = "Allow HTTPS from task security groups"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    security_groups = [
      aws_security_group.openemr.id,
      aws_security_group.app.id,
    ]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.tags, { Name = "${local.app_name}-vpce-sg" })
}

resource "aws_vpc_endpoint" "logs" {
  count = local.create_logs_endpoint ? 1 : 0

  vpc_id              = local.vpc_id_effective
  service_name        = "com.amazonaws.${var.aws_region}.logs"
  vpc_endpoint_type   = "Interface"
  subnet_ids          = local.endpoint_subnet_ids_effective
  private_dns_enabled = true
  security_group_ids  = [aws_security_group.vpc_endpoints[0].id]

  tags = merge(local.tags, { Name = "${local.app_name}-vpce-logs" })
}

resource "aws_vpc_endpoint" "secretsmanager" {
  count = local.create_secretsmanager_endpoint ? 1 : 0

  vpc_id              = local.vpc_id_effective
  service_name        = "com.amazonaws.${var.aws_region}.secretsmanager"
  vpc_endpoint_type   = "Interface"
  subnet_ids          = local.endpoint_subnet_ids_effective
  private_dns_enabled = true
  security_group_ids  = [aws_security_group.vpc_endpoints[0].id]

  tags = merge(local.tags, { Name = "${local.app_name}-vpce-secrets" })
}

resource "aws_vpc_endpoint" "s3" {
  count = local.create_s3_endpoint && length(local.endpoint_route_table_ids_effective) > 0 ? 1 : 0

  vpc_id            = local.vpc_id_effective
  service_name      = "com.amazonaws.${var.aws_region}.s3"
  vpc_endpoint_type = "Gateway"
  route_table_ids   = local.endpoint_route_table_ids_effective

  tags = merge(local.tags, { Name = "${local.app_name}-vpce-s3" })
}
