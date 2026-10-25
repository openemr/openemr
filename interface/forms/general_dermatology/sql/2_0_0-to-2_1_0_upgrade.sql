-- Phase 2 schema alignment for general_dermatology
-- 1) Allow NULL pid for SOAP templates (product already writes pid = NULL)
-- 2) Convert legacy template rows that used pid = 0
-- 3) Add lookup indexes for patient notes and templates
-- 4) Document deprecation of form_general_dermatology (no longer read by app code)

#IfMissingColumn general_dermatology template_status
ALTER TABLE `general_dermatology` ADD `template_status` VARCHAR(20) DEFAULT 'active';
#EndIf

#IfMissingColumn general_dermatology template_updated_at
ALTER TABLE `general_dermatology` ADD `template_updated_at` DATETIME DEFAULT NULL;
#EndIf

#IfMissingColumn general_dermatology template_created_by
ALTER TABLE `general_dermatology` ADD `template_created_by` VARCHAR(255) DEFAULT NULL;
#EndIf

#IfMissingColumn general_dermatology template_description
ALTER TABLE `general_dermatology` ADD `template_description` VARCHAR(500) DEFAULT NULL;
#EndIf

-- Make pid nullable so templates are not forced into patient 0
ALTER TABLE `general_dermatology` MODIFY `pid` bigint(20) NULL DEFAULT NULL;

-- Template rows historically may have pid = 0; normalize to NULL
UPDATE `general_dermatology`
SET `pid` = NULL
WHERE IFNULL(`general_dermatology_form_template_name`, '') <> ''
  AND (`pid` = 0 OR `pid` IS NULL);

#IfNotIndex general_dermatology idx_gd_pid_encounter
CREATE INDEX `idx_gd_pid_encounter` ON `general_dermatology` (`pid`, `encounter`);
#EndIf

#IfNotIndex general_dermatology idx_gd_template_name
CREATE INDEX `idx_gd_template_name` ON `general_dermatology` (`general_dermatology_form_template_name`);
#EndIf

#IfNotIndex general_dermatology idx_gd_template_status
CREATE INDEX `idx_gd_template_status` ON `general_dermatology` (`template_status`);
#EndIf
