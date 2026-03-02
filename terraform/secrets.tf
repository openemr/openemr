variable "app_config_secret_name" {
  description = "Secrets Manager secret name for app configuration."
  type        = string
  default     = "openemr/prod/openemr_config"
}

variable "openrouter_api_key_secret_name" {
  description = "Secrets Manager secret name for OpenRouter API key."
  type        = string
  default     = "openemr/prod/openrouter_api_key"
}

resource "random_password" "app_session_secret" {
  length           = 48
  special          = true
  override_special = "!@#$%^&*()-_=+"
}

resource "aws_secretsmanager_secret" "app_config" {
  name                    = var.app_config_secret_name
  recovery_window_in_days = 7
  tags                    = merge(local.tags, { Name = "${local.app_name}-app-config" })
}

resource "aws_secretsmanager_secret_version" "app_config" {
  secret_id = aws_secretsmanager_secret.app_config.id
  secret_string = jsonencode({
    (local.app_secret_key_session_secret) = random_password.app_session_secret.result
    (local.app_secret_key_client_id)      = ""
  })

  lifecycle {
    ignore_changes = [secret_string]
  }
}

resource "aws_secretsmanager_secret" "openrouter_api_key" {
  name                    = var.openrouter_api_key_secret_name
  recovery_window_in_days = 7
  tags                    = merge(local.tags, { Name = "${local.app_name}-openrouter-api-key" })
}

resource "aws_secretsmanager_secret_version" "openrouter_api_key" {
  secret_id     = aws_secretsmanager_secret.openrouter_api_key.id
  secret_string = jsonencode({ api_key = "" })

  lifecycle {
    ignore_changes = [secret_string]
  }
}
