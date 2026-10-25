#IfNotTable form_general_dermatology
CREATE TABLE IF NOT EXISTS form_general_dermatology (
                                          id int(11) NOT NULL,
                                          pid bigint(20) NOT NULL,
                                          groupname varchar(55) NULL,
                                          user varchar(55) NULL,
                                          authorized tinyint(4) NULL,
                                          activity int(11) NOT NULL,
                                          date datetime NOT NULL,
                                          form_json mediumblob NOT NULL
);
ALTER TABLE `form_general_dermatology` ADD PRIMARY KEY (`id`);
ALTER TABLE `form_general_dermatology` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `general_dermatology` (
    `id` bigint(20) NOT NULL,
    `pid` bigint(20) NULL DEFAULT NULL,
    `groupname` varchar(255) DEFAULT NULL,
    `user` varchar(255) DEFAULT NULL,
    `authorized` tinyint(4) NOT NULL DEFAULT 0,
    `activity` tinyint(4) NOT NULL DEFAULT 0,
    `date` DATE DEFAULT NULL,
    `encounter` int(10) DEFAULT NULL,
    `subjective_text` longtext DEFAULT NULL,
    `skinlesion` tinyint(4) DEFAULT 0,
    `skincheck2` tinyint(4) DEFAULT 0,
    `rash2` tinyint(4) DEFAULT 0,
    `cosmetic2` tinyint(4) DEFAULT 0,
    `surgical2` tinyint(4) DEFAULT 0,
    `acne2` tinyint(4) DEFAULT 0,
    `hairloss2` tinyint(4) DEFAULT 0,
    `naildisorder2` tinyint(4) DEFAULT 0,
    `other2` tinyint(4) DEFAULT 0,
    `onset_year` varchar(255) DEFAULT NULL,
    `onset_month` varchar(255) DEFAULT NULL,
    `onset_week` varchar(255) DEFAULT NULL,
    `onset_day` varchar(255) DEFAULT NULL,
    `location_initially` varchar(255) DEFAULT NULL,
    `location_later` varchar(255) DEFAULT NULL,
    `location_custom` varchar(255) DEFAULT NULL,
    `prior_tx1` varchar(255) DEFAULT NULL,
    `prior_1` varchar(255) DEFAULT NULL,
    `prior_tx2` varchar(255) DEFAULT NULL,
    `prior_2` varchar(255) DEFAULT NULL,
    `severity_text` varchar(255) DEFAULT NULL,
    `growth` varchar(255) DEFAULT NULL,
    `pain` varchar(255) DEFAULT NULL,
    `itching` varchar(255) DEFAULT NULL,
    `bleeding` varchar(255) DEFAULT NULL,
    `changeincolor` varchar(255) DEFAULT NULL,
    `changeinshape` varchar(255) DEFAULT NULL,
    `spreading` varchar(255) DEFAULT NULL,
    `asymptomatic` varchar(255) DEFAULT NULL,
    `sx_pain` tinyint(4) DEFAULT 0,
    `sx_itch` tinyint(4) DEFAULT 0,
    `affecting_adls` tinyint(4) DEFAULT 0,
    `affecting_sleep` tinyint(4) DEFAULT 0,
    `unrelenting` tinyint(4) DEFAULT 0,
    `well_developed` tinyint(4) DEFAULT 0,
    `well_nourished` tinyint(4) DEFAULT 0,
    `obese` tinyint(4) DEFAULT 0,
    `anorexic` tinyint(4) DEFAULT 0,
    `time` tinyint(4) DEFAULT 0,
    `place` tinyint(4) DEFAULT 0,
    `person` tinyint(4) DEFAULT 0,
    `alert` tinyint(4) DEFAULT 0,
    `uncomfortable` tinyint(4) DEFAULT 0,
    `anxious` tinyint(4) DEFAULT 0,
    `flat` tinyint(4) DEFAULT 0,
    `vital_bp` tinyint(4) DEFAULT 0,
    `vital_hr` tinyint(4) DEFAULT 0,
    `vital_rr` tinyint(4) DEFAULT 0,
    `scalp` tinyint(4) DEFAULT 0,
    `hair` tinyint(4) DEFAULT 0,
    `face` tinyint(4) DEFAULT 0,
    `externalears` tinyint(4) DEFAULT 0,
    `eyelids` tinyint(4) DEFAULT 0,
    `lips` tinyint(4) DEFAULT 0,
    `nose` tinyint(4) DEFAULT 0,
    `neck` tinyint(4) DEFAULT 0,
    `chest` tinyint(4) DEFAULT 0,
    `shoulders` tinyint(4) DEFAULT 0,
    `back` tinyint(4) DEFAULT 0,
    `arms` tinyint(4) DEFAULT 0,
    `forearms` tinyint(4) DEFAULT 0,
    `hands` tinyint(4) DEFAULT 0,
    `fingernails` tinyint(4) DEFAULT 0,
    `legs` tinyint(4) DEFAULT 0,
    `feet` tinyint(4) DEFAULT 0,
    `buttocks` tinyint(4) DEFAULT 0,
    `axillae` tinyint(4) DEFAULT 0,
    `toenails` tinyint(4) DEFAULT 0,
    `groin` tinyint(4) DEFAULT 0,
    `externalgenitalia` tinyint(4) DEFAULT 0,
    `genitalmucosa` tinyint(4) DEFAULT 0,
    `conjunctivalmucosa` tinyint(4) DEFAULT 0,
    `oralmucosa` tinyint(4) DEFAULT 0,
    `ptdeclinedfullbodyskinexam` tinyint(4) DEFAULT 0,
    `ptdeclinedtoremoveunderwear` tinyint(4) DEFAULT 0,
    `ptdeclinedtoremovebra` tinyint(4) DEFAULT 0,
    `ptwearingfingernailpolish` tinyint(4) DEFAULT 0,
    `ptwearingtoenailpolish` tinyint(4) DEFAULT 0,
    `sites_examined_text` varchar(255) DEFAULT NULL,
    `cervical` tinyint(4) DEFAULT 0,
    `postauricular` tinyint(4) DEFAULT 0,
    `occipital` tinyint(4) DEFAULT 0,
    `submandibular` tinyint(4) DEFAULT 0,
    `supraclavicular` tinyint(4) DEFAULT 0,
    `axillary` tinyint(4) DEFAULT 0,
    `inguinal` tinyint(4) DEFAULT 0,
    `popliteal` tinyint(4) DEFAULT 0,
    `total_spent` varchar(255) DEFAULT NULL,
    `total_severity` text DEFAULT NULL,
    `areas_cantharidin` varchar(255) DEFAULT NULL,
    `areas_cryotherapy` varchar(255) DEFAULT NULL,
    `areas_debridement` varchar(255) DEFAULT NULL,
    `areas_dermabrasion` varchar(255) DEFAULT NULL,
    `areas_electro` varchar(255) DEFAULT NULL,
    `areas_extraction` varchar(255) DEFAULT NULL,
    `areas_hyfrecation` varchar(255) DEFAULT NULL,
    `areas_incision` varchar(255) DEFAULT NULL

    );
ALTER TABLE `general_dermatology` ADD treatment LONGTEXT DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD PRIMARY KEY (`id`);
ALTER TABLE `general_dermatology` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `general_dermatology` ADD general_dermatology_form_template_name VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD Select_All VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD review_external_notes tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD review_test_results tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD order_tests tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD obtain_hx tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD independent_test_another_hcp tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD discuss_test_outside_hcp tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD document_time_hcp tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD consult_family tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD perform_diag_procedure tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD patient_type_new tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD patient_type_established tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` ADD general_derm_patient_follow_up longtext DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD general_derm_note longtext DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD abdomen tinyint DEFAULT 0;
ALTER TABLE `general_dermatology` MODIFY vital_bp VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` MODIFY vital_hr VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` MODIFY vital_rr VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD total_time_spent INT DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD template_status VARCHAR(20) DEFAULT 'active';
ALTER TABLE `general_dermatology` ADD template_updated_at DATETIME DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD template_created_by VARCHAR(255) DEFAULT NULL;
ALTER TABLE `general_dermatology` ADD template_description VARCHAR(500) DEFAULT NULL;

-- Phase 2 indexes (safe on fresh install after table exists)
ALTER TABLE `general_dermatology` ADD INDEX `idx_gd_pid_encounter` (`pid`, `encounter`);
ALTER TABLE `general_dermatology` ADD INDEX `idx_gd_template_name` (`general_dermatology_form_template_name`);
ALTER TABLE `general_dermatology` ADD INDEX `idx_gd_template_status` (`template_status`);
-- NOTE: form_general_dermatology is legacy/deprecated and is not used by application code.
