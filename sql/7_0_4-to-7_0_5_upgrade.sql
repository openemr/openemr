ALTER TABLE `oauth_clients`
ADD COLUMN `identity_provider` VARCHAR(255) NOT NULL DEFAULT 'local',
ADD COLUMN `google_client_id` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `google_client_secret` VARCHAR(255) DEFAULT NULL;
