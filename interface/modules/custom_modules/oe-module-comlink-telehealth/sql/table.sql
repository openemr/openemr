#IfNotTable comlink_telehealth_auth
CREATE TABLE IF NOT EXISTS `comlink_telehealth_auth`
(
    `id`              	      BIGINT(20)         NOT NULL AUTO_INCREMENT,
    `username`                VARCHAR(64) NOT NULL COMMENT 'Username used for external access',
    `user_id`                 BIGINT(20)         NULL COMMENT 'Foreign key reference to users.id',
    `patient_id`              BIGINT(20)         NULL COMMENT 'Foreign key reference to patient_data.id',
    `auth_token` 	          TEXT COMMENT 'external authorization token to use telehealth api',
    `date_registered`         DATETIME NULL COMMENT 'The date the user or patient registered with the api',
    `date_created`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the record was created',
    `date_updated`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the record was created',
    `active`                  TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'If the record is currently activated or not',
    `app_registration_code`   TEXT COMMENT 'mobile app registration code',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
#EndIf

#IfNotTable comlink_telehealth_person_settings
CREATE TABLE IF NOT EXISTS `comlink_telehealth_person_settings`
(
    `id`              	      BIGINT(20)         NOT NULL AUTO_INCREMENT,
    `user_id`                 BIGINT(20)         NULL COMMENT 'Foreign key reference to users.id',
    `patient_id`              BIGINT(20)         NULL COMMENT 'Foreign key reference to patient_data.id',
    `date_created`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the record was created',
    `date_updated`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the record was created',
    `enabled`                  TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'If telehealth is currently activated or not for the given person',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
#EndIf

#IfNotRow openemr_postcalendar_categories pc_constant_id comlink_telehealth_new_patient
INSERT INTO `openemr_postcalendar_categories` (
    `pc_constant_id`, `pc_catname`, `pc_catcolor`, `pc_catdesc`,
   `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`,
   `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`,
   `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`, `aco_spec`
)
VALUES (
    'comlink_telehealth_new_patient', 'Telehealth New Patient', '#a2d9e2'
    , 'New Patient Telehealth appointments', '0', NULL
    , 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}'
    , '0', '1800', '0', NULL, '0', '0', '0', 0, '1', '10', 'encounters|notes'
);
#EndIf

#IfNotRow openemr_postcalendar_categories pc_constant_id comlink_telehealth_established_patient
INSERT INTO `openemr_postcalendar_categories` (
    `pc_constant_id`, `pc_catname`, `pc_catcolor`, `pc_catdesc`,
    `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`,
    `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`,
    `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`, `aco_spec`
)
VALUES (
   'comlink_telehealth_established_patient', 'TeleHealth Established Patient', '#93d3a2'
    , 'TeleHealth Established Patient appointment', '0', NULL
    , 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}'
    , '0', '900', '0', NULL, '0', '0', '0', 0, '1', '9', 'encounters|notes'
);
#EndIf

#IfNotTable comlink_telehealth_appointment_session
CREATE TABLE IF NOT EXISTS `comlink_telehealth_appointment_session`
(
    `id`              	            BIGINT(20)         NOT NULL AUTO_INCREMENT,
    `user_id`                       BIGINT(20)         NOT NULL COMMENT 'Foreign key reference to users.id',
    `pc_eid`                        INT(11) UNSIGNED   NOT NULL COMMENT 'Foreign key reference to openemr_postcalendar_events.pc_eid',
    `encounter`                     BIGINT(20)         NOT NULL COMMENT 'Foreign key reference to forms.encounter',
    `pid`                           BIGINT(20)         NOT NULL COMMENT 'Foreign key reference to patient_data.pid',
    `provider_start_time`           DATETIME           DEFAULT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT 'Provider start time',
    `provider_last_update`          DATETIME           DEFAULT NULL COMMENT 'Provider last communication timestamp',
    `patient_start_time`            DATETIME           DEFAULT NULL COMMENT 'Patient join time',
    `patient_last_update`           DATETIME           DEFAULT NULL COMMENT 'Last communication timestamp with patient',
    `pid_related`                   BIGINT(20)         DEFAULT NULL COMMENT 'Foreign key reference to patient_data.pid for related patient',
    `patient_related_start_time`    DATETIME           DEFAULT NULL COMMENT 'Related Patient join time',
    `patient_related_last_update`   DATETIME           DEFAULT NULL COMMENT 'Last communication timestamp with related patient',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
#EndIf

#IfNotRow2D list_options list_id apptstat option_id TRNSFR
INSERT INTO list_options(list_id, option_id, title, seq, notes, timestamp, toggle_setting_1, toggle_setting_2)
    VALUES('apptstat', 'TRNSFR', "Transferred Provider", 100, "#FFE100|0", NOW(), 0, 1);
#EndIf

#IfNotRow2D globals gl_name set_pos_code_encounter gl_value 1
UPDATE globals SET gl_value=1 WHERE gl_name="set_pos_code_encounter";
#EndIf

#IfMissingColumn comlink_telehealth_auth app_registration_code
ALTER TABLE `comlink_telehealth_auth` ADD COLUMN `app_registration_code` TEXT COMMENT 'mobile app registration code';
#EndIf

#IfMissingColumn comlink_telehealth_appointment_session pid_related
ALTER TABLE `comlink_telehealth_appointment_session` ADD COLUMN `pid_related` BIGINT(20) DEFAULT NULL COMMENT 'Foreign key reference to patient_data.pid for related patient';
ALTER TABLE `comlink_telehealth_appointment_session` ADD COLUMN `patient_related_start_time` DATETIME DEFAULT NULL COMMENT 'Related Patient join time';
ALTER TABLE `comlink_telehealth_appointment_session` ADD COLUMN `patient_related_last_update` DATETIME DEFAULT NULL COMMENT 'Last communication timestamp with related patient';
#EndIf