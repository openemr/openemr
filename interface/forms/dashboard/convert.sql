ALTER TABLE `form_dashboard` DROP COLUMN `db_form_complete`;
ALTER TABLE `form_dashboard` DROP COLUMN `db_form_priority`;
ALTER TABLE `form_dashboard` DROP COLUMN `db_alcohol_long`;

ALTER TABLE `form_dashboard` CHANGE `db_ht` `db_height` VARCHAR(16);
ALTER TABLE `form_dashboard` CHANGE `db_wt` `db_weight` VARCHAR(16);
ALTER TABLE `form_dashboard` CHANGE `db_bmi` `db_BMI` FLOAT(4,1);
ALTER TABLE `form_dashboard` CHANGE `db_bmi_status` `db_BMI_status` VARCHAR(64);
ALTER TABLE `form_dashboard` CHANGE `db_hr` `db_pulse` VARCHAR(16);
ALTER TABLE `form_dashboard` CHANGE `db_hcg` `db_HCG` VARCHAR(16);
ALTER TABLE `form_dashboard` CHANGE `db_alcohol_much` `db_alcohol_note` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_birth_date` `db_DOB` DATE;
ALTER TABLE `form_dashboard` CHANGE `db_marital` `db_status` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_education` `db_wmt_education` VARCHAR(64) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_husband` `db_wmt_partner_name` VARCHAR(128) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_husband_ph` `db_wmt_partner_ph` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_father` `db_wmt_father_name` VARCHAR(128) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_father_ph` `db_wmt_father_ph` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_address` `db_street` VARCHAR(128) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_zip` `db_postal_code` VARCHAR(16) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_home_ph` `db_phone_home` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_other_ph` `db_phone_biz` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_cell_ph` `db_phone_cell` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_emergency_ph` `db_phone_contact` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_smoking_desc` `db_smoking_desc` VARCHAR(4) DEFAULT NULL;
ALTER TABLE `form_dashboard` CHANGE `db_smoking_status` `db_smoking_status` VARCHAR(32) DEFAULT NULL;

