CREATE TABLE IF NOT EXISTS `audio2note_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `openemr_internal_random_uuid` VARCHAR(36) DEFAULT NULL,
    `effective_instance_identifier` VARCHAR(255) DEFAULT NULL,
    `encrypted_license_key` TEXT DEFAULT NULL,
    `encrypted_wc_consumer_key` TEXT DEFAULT NULL,
    `encrypted_wc_consumer_secret` TEXT DEFAULT NULL,
    `encrypted_dlm_activation_token` TEXT DEFAULT NULL,
    `encrypted_backend_audio_process_base_url` TEXT DEFAULT NULL,
    `encrypted_wc_api_base_url` TEXT DEFAULT NULL,
    `license_status` VARCHAR(50) DEFAULT NULL,
    `license_expires_at` DATETIME DEFAULT NULL,
    `last_validation_timestamp` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
