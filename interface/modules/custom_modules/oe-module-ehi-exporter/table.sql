#used for cleaning up the tables if needed
#DROP TABLE IF EXISTS ehi_export_job_task_patients;
#DROP table IF EXISTS ehi_export_job_tasks;
#DROP TABLE IF EXISTS ehi_export_job_patients;
#DROP TABLE IF EXISTS ehi_export_job;

#IfNotTable ehi_export_job
CREATE TABLE `ehi_export_job`(
   `ehi_export_job_id` int(11) NOT NULL AUTO_INCREMENT,
   `uuid` BINARY(16) DEFAULT NULL,
   `user_id` BIGINT(20) NOT NULL COMMENT 'FK to users.id - represents the user that started the export process',
   `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `completion_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `status` VARCHAR(20) NOT NULL DEFAULT 'processing' COMMENT 'processing=export in progress, failed=error occurred in one or more tasks, completed=export completed without errors',
   `include_patient_documents` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Include patient documents into zip export, 0=no patient documents included in the export',
   `document_limit_size` INT(11) UNSIGNED NOT NULL DEFAULT 524288000 COMMENT 'The batch zip file export size, default set to 500MB max value size is 4GB',
   PRIMARY KEY(`ehi_export_job_id`),
   CONSTRAINT `FK_ehi_export_job_users` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = INNODB COMMENT = 'User initiated export of patient electronic health info.';
#EndIf

#IfNotTable ehi_export_job_patients
CREATE TABLE `ehi_export_job_patients`(
     `ehi_export_job_id` int(11) NOT NULL,
     `pid`  BIGINT(20) NOT NULL,
     -- join tables should always cascade
     PRIMARY KEY(`ehi_export_job_id`, `pid`),
     CONSTRAINT `FK_ehi_export_job_patients_job_id` FOREIGN KEY (`ehi_export_job_id`) REFERENCES `ehi_export_job`(`ehi_export_job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
     CONSTRAINT `FK_ehi_export_job_patients_patient_id` FOREIGN KEY (`pid`) REFERENCES `patient_data`(`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB COMMENT = 'Patients to be exported in the ehi_export_job';
#EndIf

#IfNotTable ehi_export_job_tasks
CREATE TABLE `ehi_export_job_tasks`(
     `ehi_task_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
     `ehi_export_job_id` int(11) NOT NULL,
     `export_document_id` int(11) COMMENT 'FK to documents.id - represents the document result file that was created as part of this task',
#      `export_document_size_limit` int(11) DEFAULT 525312500 COMMENT 'The patient documents export size, default set to 500MB',
     `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `completion_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending=task export has not started, processing=task export in progress, failed=error occurred in the task, completed=export task completed without errors',
     `error_message` TEXT COMMENT 'The error that occurred in the export process, only populated if status=failed',
     `exported_result` TEXT COMMENT 'The JSON encoded result of the export process, populated during processing for status updates',
     PRIMARY KEY(`ehi_task_id`),
     CONSTRAINT `FK_task_ehi_export_job_id` FOREIGN KEY (`ehi_export_job_id`) REFERENCES `ehi_export_job`(`ehi_export_job_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
     CONSTRAINT `FK_task_ehi_export_document_id` FOREIGN KEY (`export_document_id`) REFERENCES `documents`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = INNODB COMMENT = 'Export task for a subset of patients in the export job';
#EndIf

#IfNotTable ehi_export_job_task_patients
CREATE TABLE `ehi_export_job_task_patients`(
  `ehi_task_id` BIGINT(20) NOT NULL,
  `pid`  BIGINT(20) NOT NULL,
-- join tables should always cascade
  PRIMARY KEY(`ehi_task_id`, `pid`),
  CONSTRAINT `FK_ehi_export_job_task_patients_job_id` FOREIGN KEY (`ehi_task_id`) REFERENCES `ehi_export_job_tasks`(`ehi_task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ehi_export_job_task_patients_patient_id` FOREIGN KEY (`pid`) REFERENCES `patient_data`(`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB COMMENT = 'Patients to be exported in the ehi_export_job_tasks';
#EndIf

#IfNotRow categories name EHI Export Zip File
INSERT INTO categories(`id`,`name`, `value`, `parent`, `lft`, `rght`, `aco_spec`) select (select MAX(id) from categories) + 1, 'EHI Export Zip File', '', 1, rght, rght + 1, 'admin|super' from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

#IfMissingColumn ehi_export_job_tasks exported_result
ALTER TABLE ehi_export_job_tasks ADD COLUMN `exported_result` TEXT COMMENT 'The JSON encoded result of the export process, populated during processing for status updates';
#Endif
