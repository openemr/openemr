--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfColumn
--    arguments: table_name colname
--    behavior:  if the table and column exist,  the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotColumnTypeDefault
--    arguments: table_name colname value value2
--    behavior:  If the table table_name does not have a column colname with a data type equal to value and a default equal to value2, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does have a row where colname = value, the block will be executed.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--        arguments: table_name colname value colname2 value2 colname3 value3
--        behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfRowIsNull
--    arguments: table_name colname
--    behavior:  If the table table_name does have a row where colname is null, the block will be executed.

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with a #EndIf statement.

--  #IfNotListReaction
--    Custom function for creating Reaction List

--  #IfNotListOccupation
--    Custom function for creating Occupation List

--  #IfTextNullFixNeeded
--    desc: convert all text fields without default null to have default null.
--    arguments: none

--  #IfTableEngine
--    desc:      Execute SQL if the table has been created with given engine specified.
--    arguments: table_name engine
--    behavior:  Use when engine conversion requires more than one ALTER TABLE

--  #IfInnoDBMigrationNeeded
--    desc: find all MyISAM tables and convert them to InnoDB.
--    arguments: none
--    behavior: can take a long time.

--  #IfDocumentNamingNeeded
--    desc: populate name field with document names.
--    arguments: none

--  #IfUpdateEditOptionsNeeded
--    desc: Change Layout edit options.
--    arguments: mode(add or remove) layout_form_id the_edit_option comma_seperated_list_of_field_ids

#IfNotRow2D layout_options form_id DEM field_id prevent_portal_apps
SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='allow_patient_portal' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='allow_patient_portal' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM','prevent_portal_apps',@group_id,'Prevent API Access',@seq_add_to+5,21,1,0,0,'',1,1,'','','Check to not allow third party API access.',0);
ALTER TABLE `patient_data` ADD `prevent_portal_apps` TEXT;
#Endif

#IfMissingColumn clinical_rules bibliographic_citation
ALTER TABLE `clinical_rules` ADD COLUMN `bibliographic_citation` VARCHAR(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn clinical_rules linked_referential_cds
ALTER TABLE `clinical_rules` ADD COLUMN `linked_referential_cds` VARCHAR(50) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn clinical_rules amc_2015_flag
ALTER TABLE `clinical_rules` ADD `amc_2015_flag` TINYINT(1) NULL DEFAULT NULL
    COMMENT '2015 Automated Measure Calculation flag for (unable to customize per patient)';
#EndIf

#IfMissingColumn clinical_rules amc_code_2015
ALTER TABLE `clinical_rules` ADD `amc_code_2015` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Automated Measure Calculation 2014 identifier (MU rule)';
#EndIf

#IfMissingColumn patient_access_onsite date_created
-- We add the date time so we know exactly when the credentials were generated without having to lookup in the audit log
ALTER TABLE patient_access_onsite ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfNotRow clinical_rules id patient_access_amc
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_2011_flag`,
                              `cqm_2014_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_2011_flag`,
                              `amc_2014_flag`, `amc_code`, `amc_code_2014`, `amc_code_2015`, `amc_2014_stage1_flag`,
                              `amc_2014_stage2_flag`, `amc_2015_flag`, `patient_reminder_flag`, `developer`,
                              `funding_source`, `release_version`, `web_reference`, `access_control`,
                              `bibliographic_citation`, `linked_referential_cds`)
    VALUES ('patient_access_amc', '0', '0', '0', '0', '0', '0', '', '', '1', '0', '0', '', ''
    , '170.315(g)(1)/(2)–2c', '0', '0', '1', '0', '', '', '', '', 'patients:med', '', '');
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id patient_access_amc
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`
                            , `codes`, `toggle_setting_1`, `toggle_setting_2`)
    VALUES ('clinical_rules', 'patient_access_amc', 'Provide Patients Electronic Access to Their Health Information - API Access (ACM)'
    , 240, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id ecqm_2021_reporting
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists','ecqm_2021_reporting','eCQM 2021 Performance Period',0,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS117v9','Childhood Immunization Status',10,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS122v9','Diabetes: Hemoglobin A1c (HbA1c) Poor Control (>9%)',20,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS124v9','Cervical Cancer Screening',30,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS125v9','Breast Cancer Screening',40,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS127v9','Pneumococcal Vaccination Status for Older Adults',50,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS128v9','Anti-Depressant Medication Management',60,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS129v10','Prostate Cancer: Avoidance of Overuse of Bone Scan for Staging Low Risk Prostate Cancer Patients',70,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS130v9','Colorectal Cancer Screening',80,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS131v9','Diabetes: Eye Exam',90,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS133v9','Cataracts: 20/40 or Better Visual Acuity within 90 Days Following Cataract Surgery',95,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS134v9','Diabetes: Medical Attention for Nephropathy',100,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS135v9','Heart Failure (HF): Angiotensin-Converting Enzyme (ACE) Inhibitor or Angiotensin Receptor Blocker (ARB) or Angiotensin Receptor-Neprilysin Inhibitor (ARNI) Therapy for Left Ventricular Systolic Dysfunction (LVSD)',110,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS136v10','Follow-Up Care for Children Prescribed ADHD Medication (ADD)',120,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS137v9','Initiation and Engagement of Alcohol and Other Drug Dependence Treatment',130,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS138v9','Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',140,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS139v9','Falls: Screening for Future Fall Risk',150,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS142v9','Diabetic Retinopathy: Communication with the Physician Managing Ongoing Diabetes Care',160,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS143v9','Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation',170,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS144v9','Heart Failure (HF): Beta-Blocker Therapy for Left Ventricular Systolic Dysfunction (LVSD)',180,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS145v9','Coronary Artery Disease (CAD): Beta-Blocker Therapy – Prior Myocardial Infarction (MI) or Left Ventricular Systolic Dysfunction (LVEF < 40%)',190,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS146v9','Appropriate Testing for Pharyngitis',200,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS147v10','Preventive Care and Screening: Influenza Immunization',210,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS149v9','Dementia: Cognitive Assessment',220,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS153v9','Chlamydia Screening for Women',230,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS154v9','Appropriate Treatment for Upper Respiratory Infection (URI)',240,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS155v9','Weight Assessment and Counseling for Nutrition and Physical `activity` for Children and Adolescents',250,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS156v9','Use of High-Risk Medications in Older Adults',260,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS157v9','Oncology: Medical and Radiation – Pain Intensity Quantified',280,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS159v9','Depression Remission at Twelve Months',290,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS161v9','Adult Major Depressive Disorder (MDD): Suicide Risk Assessment',300,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS165v9','Controlling High Blood Pressure',310,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS177v9','Child and Adolescent Major Depressive Disorder (MDD): Suicide Risk Assessment',320,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS22v9','Preventive Care and Screening: Screening for High Blood Pressure and Follow-Up Documented',330,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS249v3','Appropriate Use of DXA Scans in Women Under 65 Years Who Do Not Meet the Risk Factor Profile for Osteoporotic Fracture',340,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS2v10','Preventive Care and Screening: Screening for Depression and Follow-Up Plan',350,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS347v4','Statin Therapy for the Prevention and Treatment of Cardiovascular Disease',360,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS349v3','HIV Screening',370,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS50v9','Closing the Referral Loop: Receipt of Specialist Report',380,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS56v9','Functional Status Assessment for Total Hip Replacement',390,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS645v4','Bone Density Evaluation for Patients with Prostate Cancer and Receiving Androgen Deprivation Therapy',400,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS66v9','Functional Status Assessment for Total Knee Replacement',410,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS68v10','Documentation of Current Medications in the Medical Record',420,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS69v9','Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan',430,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS74v10','Primary Caries Prevention Intervention as Offered by Primary Care Providers, including Dentists',440,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS75v9','Children Who Have Dental Decay or Cavities',450,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS771v2','Urinary Symptom Score Change 6-12 Months After Diagnosis of Benign Prostatic Hyperplasia',460,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`) VALUES ('ecqm_2021_reporting','CMS90v10','Functional Status Assessments for Congestive Heart Failure',470,0);
#EndIf
