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
--    arguments: mode(add or remove) layout_form_id the_edit_option comma_separated_list_of_field_ids

--  #IfVitalsDatesNeeded
--    desc: Change date from zeroes to date of vitals form creation.
--    arguments: none

--  #IfMBOEncounterNeeded
--    desc: Add encounter to the form_misc_billing_options table
--    arguments: none

#IfMissingColumn onetime_auth scope
ALTER TABLE `onetime_auth` ADD `scope` tinytext COMMENT 'context scope for this token';
#EndIf

#IfMissingColumn onetime_auth profile
ALTER TABLE `onetime_auth` ADD `profile` tinytext COMMENT 'profile of scope for this token';
#EndIf

#IfMissingColumn onetime_auth onetime_actions
ALTER TABLE `onetime_auth` ADD `onetime_actions` text COMMENT 'JSON array of actions that can be performed with this token';
#EndIf

UPDATE `list_options`
SET `notes` = '{"form_title":{"presence": {"message": "Title Required"}}}'
WHERE `list_id` = 'page_validation' AND `option_id` = 'add_edit_event#theform_prov' AND `title` = '/interface/main/calendar/add_edit_event.php?prov=true' AND `notes` = '{}';

#IfNotTable track_events
CREATE TABLE `track_events` (
    `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type`     TEXT,
    `event_label`    VARCHAR(255) DEFAULT NULL,
    `event_url`       TEXT,
    `event_target`  TEXT,
    `first_event`     DATETIME NULL,
    `last_event`     DATETIME NULL,
    `label_count`    INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_event_label_target` (`event_label`, `event_url`(255), `event_target`(255))
) ENGINE = InnoDB COMMENT = 'Telemetry Event Data';
#EndIf

#IfMissingColumn product_registration auth_by_id
ALTER TABLE `product_registration` ADD `auth_by_id` INT(11) NULL;
#EndIf

#IfMissingColumn product_registration telemetry_disabled
ALTER TABLE `product_registration` ADD `telemetry_disabled` TINYINT(1) NULL COMMENT '1 disabled. NULL ask. 0 use option scopes';
#EndIf

#IfMissingColumn product_registration last_ask_date
ALTER TABLE `product_registration` ADD `last_ask_date` DATETIME NULL;
#EndIf

#IfMissingColumn product_registration last_ask_version
ALTER TABLE `product_registration` ADD `last_ask_version` TINYTEXT;
#EndIf

#IfMissingColumn product_registration options
ALTER TABLE `product_registration` ADD `options` TEXT COMMENT 'JSON array of scope options';
#EndIf

#IfIndex track_events unique_event_label
ALTER TABLE `track_events` DROP INDEX `unique_event_label`;
#EndIf

#IfIndex track_events unique_event_label_url
ALTER TABLE `track_events` DROP INDEX `unique_event_label_url`;
#EndIf

#IfNotIndex track_events unique_event_label_target
ALTER TABLE `track_events` ADD UNIQUE `unique_event_label_target` (`event_label`, `event_url`(255), `event_target`(255));
#EndIf

#IfNotTable care_teams
CREATE TABLE `care_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `pid` int(11) NOT NULL COMMENT 'fk to patient_data.pid',
  `status` varchar(100) DEFAULT 'active' COMMENT 'fk to list_options.option_id where list_id=Care_Team_Status',
  `team_name` varchar(255) DEFAULT NULL,
  `note` text,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` BIGINT(20) COMMENT 'fk to users.id for user who created this record',
  `updated_by` BIGINT(20) COMMENT 'fk to users.id for user who last updated this record',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id lists option_id care_team_roles
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists','care_team_roles','Care Team Roles');
#EndIf
#IfNotRow list_options list_id care_team_roles
INSERT INTO list_options (list_id, option_id, title, seq, codes, notes) VALUES
   ('care_team_roles', 'primary_care_provider', 'Primary Care Provider', 10, 'SNOMED-CT:62247001', ''),
   ('care_team_roles', 'case_manager', 'Case Manager', 20, 'SNOMED-CT:133932002', ''),
   ('care_team_roles', 'caregiver', 'Caregiver', 30, 'SNOMED-CT:224931005', ''),
   ('care_team_roles', 'nurse', 'Nurse', 40, 'SNOMED-CT:224565007', ''),
   ('care_team_roles', 'social_worker', 'Social Worker', 50, 'SNOMED-CT:159033005', ''),
   ('care_team_roles', 'pharmacist', 'Pharmacist', 60, 'SNOMED-CT:46255001', ''),
   ('care_team_roles', 'specialist', 'Specialist', 70, 'SNOMED-CT:419772000', ''),
   ('care_team_roles', 'other', 'Other', 80, 'SNOMED-CT:106292003', '');
#EndIf

-- ---------------------------------------------------------------------------------------------------------------------------------
--
-- 2023 Performance Period Measures

#IfNotRow list_options list_id ecqm_2023_reporting
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('lists','ecqm_2023_reporting','eCQM 2023 Performance Period',0,1,0, '');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS1028v1','Severe Obstetric Complications',10,0,'Patients with severe obstetric complications which occur during the inpatient delivery hospitalization.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS104v11','Discharged on Antithrombotic Therapy',20,0,'Ischemic stroke patients prescribed or continuing to take antithrombotic therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS105v11','Discharged on Statin Medication',30,0,'Ischemic stroke patients who are prescribed or continuing to take statin medication at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS108v11','Venous Thromboembolism Prophylaxis',40,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given between the day of arrival to the day after hospital admission or surgery end date for surgeries that start the day of or the day after hospital admission');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS111v11','Median Admit Decision Time to ED Departure Time for Admitted Patients',50,0,'Median time (in minutes) from admit decision time to time of departure from the emergency department (ED) for emergency department patients admitted to inpatient status');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS117v11','Childhood Immunization Status',60,0,'Percentage of children 2 years of age who had four diphtheria, tetanus and acellular pertussis (DTaP); three polio (IPV), one measles, mumps and rubella (MMR); three or four H influenza type B (Hib); three hepatitis B (Hep B); one chicken pox (VZV); four pneumococcal conjugate (PCV); one hepatitis A (Hep A); two or three rotavirus (RV); and two influenza (flu) vaccines by their second birthday');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS122v11','Diabetes: Hemoglobin A1c (HbA1c) Poor Control (> 9%)',70,1,'Percentage of patients 18-75 years of age with diabetes who had hemoglobin A1c &gt; 9.0% during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS124v11','Cervical Cancer Screening',80,1,'Percentage of women 21-64 years of age who were screened for cervical cancer using either of the following criteria:<br>*  Women age 21-64 who had cervical cytology performed within the last 3 years<br>*  Women age 30-64 who had cervical human papillomavirus (HPV) testing performed within the last 5 years');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS125v11','Breast Cancer Screening',90,1,'Percentage of women 50-74 years of age who had a mammogram to screen for breast cancer in the 27 months prior to the end of the Measurement Period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS127v11','Pneumococcal Vaccination Status for Older Adults',100,1,'Percentage of patients 66 years of age and older who have received a pneumococcal vaccine');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS128v11','Anti-depressant Medication Management',110,0,'Percentage of patients 18 years of age and older who were treated with antidepressant medication, had a diagnosis of major depression, and who remained on an antidepressant medication treatment. Two rates are reported. <br>a. Percentage of patients who remained on an antidepressant medication for at least 84 days (12 weeks). <br>b. Percentage of patients who remained on an antidepressant medication for at least 180 days (6 months).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS129v12','Prostate Cancer: Avoidance of Overuse of Bone Scan for Staging Low Risk Prostate Cancer Patients',120,0,'Percentage of patients, regardless of age, with a diagnosis of prostate cancer at low (or very low) risk of recurrence receiving interstitial prostate brachytherapy, OR external beam radiotherapy to the prostate, OR radical prostatectomy who did not have a bone scan performed at any time since diagnosis of prostate cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS130v11','Colorectal Cancer Screening',130,1,'Percentage of adults 45-75 years of age who had appropriate screening for colorectal cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS131v11','Diabetes: Eye Exam',140,0,'Percentage of patients 18-75 years of age with diabetes and an active diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or diabetics with no diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or in the 12 months prior to the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS133v11','Cataracts: 20/40 or Better Visual Acuity within 90 Days Following Cataract Surgery',150,0,'Percentage of cataract surgeries for patients aged 18 and older with a diagnosis of uncomplicated cataract and no significant ocular conditions impacting the visual outcome of surgery and had best-corrected visual acuity of 20/40 or better (distance or near) achieved in the operative eye within 90 days following the cataract surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS134v11','Diabetes: Medical Attention for Nephropathy',160,0,'The percentage of patients 18-75 years of age with diabetes who had a nephropathy screening test or evidence of nephropathy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS135v11','Heart Failure (HF): Angiotensin-Converting Enzyme (ACE) Inhibitor or Angiotensin Receptor Blocker (ARB) or Angiotensin Receptor-Neprilysin Inhibitor (ARNI) Therapy for Left Ventricular Systolic Dysfunction (LVSD)',170,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed or already taking ACE inhibitor or ARB or ARNI therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS136v12','Follow-Up Care for Children Prescribed ADHD Medication (ADD)',180,0,'Percentage of children 6-12 years of age and newly prescribed a medication for attention-deficit/hyperactivity disorder (ADHD) who had appropriate follow-up care. Two rates are reported.  <br>a. Percentage of children who had one follow-up visit with a practitioner with prescribing authority during the 30-Day Initiation Phase.<br>b. Percentage of children who remained on ADHD medication for at least 210 days and who, in addition to the visit in the Initiation Phase, had at least two additional follow-up visits with a practitioner within 270 days (9 months) after the Initiation Phase ended.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS137v11','Initiation and Engagement of Substance Use Disorder Treatment',190,0,'Percentage of patients 13 years of age and older with a new substance use disorder (SUD) episode who received the following (Two rates are reported):<br><br>a. Percentage of patients who initiated treatment, including either an intervention or medication for the treatment of SUD, within 14 days of the new SUD episode. <br>b. Percentage of patients who engaged in ongoing treatment, including two additional interventions or short-term medications, or one long-term medication for the treatment of SUD, within 34 days of the initiation.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS138v11','Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',200,1,'Percentage of patients aged 18 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user.<br><br>Three rates are reported: <br>a. Percentage of patients aged 18 years and older who were screened for tobacco use one or more times during the measurement period<br>b. Percentage of patients aged 18 years and older who were identified as a tobacco user during the measurement period who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period <br>c. Percentage of patients aged 18 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS139v11','Falls: Screening for Future Fall Risk',210,0,'Percentage of patients 65 years of age and older who were screened for future fall risk during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS142v11','Diabetic Retinopathy: Communication with the Physician Managing Ongoing Diabetes Care',220,0,'Percentage of patients aged 18 years and older with a diagnosis of diabetic retinopathy who had a dilated macular or fundus exam performed with documented communication to the physician who manages the ongoing care of the patient with diabetes mellitus regarding the findings of the macular or fundus exam at least once within 12 months');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS143v11','Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation',230,0,'Percentage of patients aged 18 years and older with a diagnosis of primary open-angle glaucoma (POAG) who have an optic nerve head evaluation during one or more visits within 12 months');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS144v11','Heart Failure (HF): Beta-Blocker Therapy for Left Ventricular Systolic Dysfunction (LVSD)',240,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;= 40% who were prescribed or already taking beta-blocker therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS145v11','Coronary Artery Disease (CAD): Beta-Blocker Therapy-Prior Myocardial Infarction (MI) or Left Ventricular Systolic Dysfunction (LVEF <=40%)',250,0,'Percentage of patients aged 18 years and older with a diagnosis of coronary artery disease seen within a 12-month period who also have a prior MI or a current or prior LVEF &lt;=40% who were prescribed beta-blocker therapy');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS146v11','Appropriate Testing for Pharyngitis',260,0,'The percentage of episodes for patients 3 years and older with a diagnosis of pharyngitis that resulted in an antibiotic order and a group A streptococcus (strep) test in the seven-day period from three days prior to the episode date through three days after the episode date');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS147v12','Preventive Care and Screening: Influenza Immunization',270,1,'Percentage of patients aged 6 months and older seen for a visit between October 1 and March 31 who received an influenza immunization OR who reported previous receipt of an influenza immunization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS149v11','Dementia: Cognitive Assessment',280,0,'Percentage of patients, regardless of age, with a diagnosis of dementia for whom an assessment of cognition is performed and the results reviewed at least once within a 12-month period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS153v11','Chlamydia Screening in Women',290,0,'Percentage of women 16-24 years of age who were identified as sexually active and who had at least one test for chlamydia during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS154v11','Appropriate Treatment for Upper Respiratory Infection (URI)',300,0,'Percentage of episodes for patients 3 months of age and older with a diagnosis of upper respiratory infection (URI) that did not result in an antibiotic order');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS155v11','Weight Assessment and Counseling for Nutrition and Physical Activity for Children/Adolescents',310,0,'Percentage of patients 3-17 years of age who had an outpatient visit with a primary care physician (PCP) or obstetrician/gynecologist (OB/GYN) and who had evidence of the following during the measurement period.<br><br> - Percentage of patients with height, weight, and body mass index (BMI) percentile documentation<br> - Percentage of patients with counseling for nutrition<br> - Percentage of patients with counseling for physical activity');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS156v11','Use of High-Risk Medications in Older Adults',320,0,'Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class. Three rates are reported. <br>1. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class.<br>2. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class, except for appropriate diagnoses.<br>3. Total rate (the sum of the two numerators divided by the denominator, deduplicating for patients in both numerators).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS157v11','Oncology: Medical and Radiation - Pain Intensity Quantified',330,0,'Percentage of patient visits, regardless of patient age, with a diagnosis of cancer currently receiving chemotherapy or radiation therapy in which pain intensity is quantified');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS159v11','Depression Remission at Twelve Months',340,0,'The percentage of adolescent patients 12 to 17 years of age and adult patients 18 years of age or older with major depression or dysthymia who reached remission 12 months (+/- 60 days) after an index event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS161v11','Adult Major Depressive Disorder (MDD): Suicide Risk Assessment',350,0,'Percentage of all patient visits for those patients that turn 18 or older during the measurement period in which a new or recurrent diagnosis of major depressive disorder (MDD) was identified and a suicide risk assessment was completed during the visit');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS165v11','Controlling High Blood Pressure',360,1,'Percentage of patients 18-85 years of age who had a diagnosis of essential hypertension starting before and continuing into, or starting during the first six months of the measurement period, and whose most recent blood pressure was adequately controlled (&lt;140/90mmHg) during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS177v11','Child and Adolescent Major Depressive Disorder (MDD): Suicide Risk Assessment',370,0,'Percentage of patient visits for those patients aged 6 through 17 years with a diagnosis of major depressive disorder (MDD) with an assessment for suicide risk');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS190v11','Intensive Care Unit Venous Thromboembolism Prophylaxis',380,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given the day of or the day after the initial admission (or transfer) to the Intensive Care Unit (ICU) or surgery end date for surgeries that start the day of or the day after ICU admission (or transfer)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS22v11','Preventive Care and Screening: Screening for High Blood Pressure and Follow-Up Documented',390,1,'Percentage of patient visits for patients aged 18 years and older seen during the measurement period who were screened for high blood pressure AND a recommended follow-up plan is documented, as indicated, if blood pressure is elevated or hypertensive');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS249v5','Appropriate Use of DXA Scans in Women Under 65 Years Who Do Not Meet the Risk Factor Profile for Osteoporotic Fracture',400,0,'Percentage of female patients 50 to 64 years of age without select risk factors for osteoporotic fracture who received an order for a dual-energy x-ray absorptiometry (DXA) scan during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS2v12','Preventive Care and Screening: Screening for Depression and Follow-Up Plan',410,0,'Percentage of patients aged 12 years and older screened for depression on the date of the encounter or up to 14 days prior to the date of the encounter using an age-appropriate standardized depression screening tool AND if positive, a follow-up plan is documented on the date of or up to two days after the date of the qualifying encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS334v4','Cesarean Birth',420,0,'Nulliparous women with a term, singleton baby in a vertex position delivered by cesarean birth');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS347v6','Statin Therapy for the Prevention and Treatment of Cardiovascular Disease',430,0,'Percentage of the following patients - all considered at high risk of cardiovascular events - who were prescribed or were on statin therapy during the measurement period: <br>*All patients with an active diagnosis of clinical atherosclerotic cardiovascular disease (ASCVD) or ever had an ASCVD procedure; OR <br>*Patients aged &gt;= 20 years who have ever had a low-density lipoprotein cholesterol (LDL-C) level &gt;= 190 mg/dL or were previously diagnosed with or currently have an active diagnosis of familial hypercholesterolemia; OR <br>*Patients aged 40-75 years with a diagnosis of diabetes');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS349v5','HIV Screening',440,0,'Percentage of patients aged 15-65 at the start of the measurement period who were between 15-65 years old when tested for Human immunodeficiency virus (HIV)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS506v5','Safe Use of Opioids - Concurrent Prescribing',450,0,'Proportion of inpatient hospitalizations for patients 18 years of age and older prescribed, or continued on, two or more opioids or an opioid and benzodiazepine concurrently at discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS50v11','Closing the Referral Loop: Receipt of Specialist Report',460,0,'Percentage of patients with referrals, regardless of age, for which the referring clinician receives a report from the clinician to whom the patient was referred');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS529v3','Core Clinical Data Elements for the Hybrid Hospital-Wide Readmission (HWR) Measure with Claims and Electronic Health Record Data',470,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWR outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from encounters for adult Medicare Fee-For-Service patients admitted to acute care short stay hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS56v11','Functional Status Assessment for Total Hip Replacement',480,0,'Percentage of patients 19 years of age and older who received an elective primary total hip arthroplasty (THA) and completed a functional status assessment within 90 days prior to the surgery and in the 270 - 365 days after the surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS645v6','Bone density evaluation for patients with prostate cancer and receiving androgen deprivation therapy',490,0,'Patients determined as having prostate cancer who are currently starting or undergoing androgen deprivation therapy (ADT), for an anticipated period of 12 months or greater and who receive an initial bone density evaluation. The bone density evaluation must be prior to the start of ADT or within 3 months of the start of ADT.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS646v3','Intravesical Bacillus-Calmette-Guerin for non-muscle invasive bladder cancer',500,0,'Percentage of patients initially diagnosed with non-muscle invasive bladder cancer and who received intravesical Bacillus-Calmette-Guerin (BCG) within 6 months of bladder cancer staging');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS66v11','Functional Status Assessment for Total Knee Replacement',510,0,'Percentage of patients 19 years of age and older who received an elective primary total knee arthroplasty (TKA) and completed a functional status assessment within 90 days prior to the surgery and in the 270-365 days after the surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS68v12','Documentation of Current Medications in the Medical Record',520,0,'Percentage of visits for patients aged 18 years and older for which the eligible clinician attests to documenting a list of current medications using all immediate resources available on the date of the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS69v11','Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan',530,1,'Percentage of patients aged 18 years and older with a BMI documented during the current encounter or during the measurement period AND who had a follow-up plan documented if BMI was outside of normal parameters');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS71v12','Anticoagulation Therapy for Atrial Fibrillation/Flutter',540,0,'Ischemic stroke patients with atrial fibrillation/flutter who are prescribed or continuing to take anticoagulation therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS72v11','Antithrombotic Therapy By End of Hospital Day 2',550,0,'Ischemic stroke patients administered antithrombotic therapy by the end of hospital day 2');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS74v12','Primary Caries Prevention Intervention as Offered by Dentists',560,0,'Percentage of children, 6 months - 20 years of age, who received a fluoride varnish application during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS75v11','Children Who Have Dental Decay or Cavities',570,0,'Percentage of children, 6 months - 20 years of age at the start of the measurement period, who have had tooth decay or cavities during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS771v4','Urinary Symptom Score Change 6-12 Months After Diagnosis of Benign Prostatic Hyperplasia',580,0,'Percentage of patients with an office visit within the measurement period and with a new diagnosis of clinically significant Benign Prostatic Hyperplasia who have International Prostate Symptoms Score (IPSS) or American Urological Association (AUA) Symptom Index (SI) documented at time of diagnosis and again 6-12 months later with an improvement of 3 points');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS816v2','Hospital Harm - Severe Hypoglycemia',590,0,'Inpatient hospitalizations for patients 18 years of age or older at admission, who were administered at least one hypoglycemic medication during the encounter, who suffer the harm of a severe hypoglycemic event during the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS819v1','Hospital Harm - Opioid-Related Adverse Events',600,0,'This measure assesses the proportion of inpatient hospital encounters where patients ages 18 years of age or older have been administered an opioid medication and are subsequently administered an opioid antagonist (naloxone) within 12 hours, an indication of an opioid-related adverse event. This measure excludes opioid antagonist (naloxone) administration occurring in the operating room setting.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS844v3','Core Clinical Data Elements for the Hybrid Hospital-Wide (All-Condition, All-Procedure) Risk-Standardized Mortality Measure (HWM)',610,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWM outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from encounters for adult Medicare Fee-For-Service patients admitted to acute care short stay hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS871v2','Hospital Harm - Severe Hyperglycemia',620,0,'This measure assesses the number of inpatient hospital days with a hyperglycemic event (harm) per the total qualifying inpatient hospital days for that encounter for patients 18 years of age or older at admission');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS90v12','Functional Status Assessments for Heart Failure',630,0,'Percentage of patients 18 years of age and older with heart failure who completed initial and follow-up patient-reported functional status assessments');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS951v1','Kidney Health Evaluation',640,0,'Percentage of patients aged 18-75 years with a diagnosis of diabetes who received a kidney health evaluation defined by an Estimated Glomerular Filtration Rate (eGFR) AND Urine Albumin-Creatinine Ratio (uACR) within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS986v1','Global Malnutrition Composite Score',650,0,'This measure assesses the percentage of hospitalizations for adults aged 65 years and older prior to the start of the measurement period with a length of stay equal to or greater than 24 hours who received optimal malnutrition care during the current inpatient hospitalizations where care performed was appropriate to the patient&#39;s level of malnutrition risk and severity. Malnutrition care best practices recommend that for each hospitalization, adult inpatients are screened for malnutrition risk, assessed to confirm findings of malnutrition risk, and if identified with a &quot;moderate&quot; or &quot;severe&quot;  malnutrition status in the current performed malnutrition assessment, receive a current &quot;moderate&quot; or &quot;severe&quot; malnutrition diagnosis and have a current nutrition care plan performed.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS996v3','Appropriate Treatment for ST-Segment Elevation Myocardial Infarction (STEMI) Patients in the Emergency Department (ED)',660,0,'Percentage of emergency department (ED) encounters for patients 18 years and older with a diagnosis of ST-segment elevation myocardial infarction (STEMI) that received appropriate treatment, defined as fibrinolytic therapy within 30 minutes of ED arrival, percutaneous coronary intervention (PCI) within 90 minutes of ED arrival, or transfer within 45 minutes of ED arrival');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2023_reporting','CMS9v11','Exclusive Breast Milk Feeding',670,0,'PC-05 Exclusive breast milk feeding during the newborn&#39;s entire hospitalization.<br><br>The measure is reported as an overall rate which includes all newborns that were exclusively fed breast milk during the entire hospitalization.');
#EndIf

-- ---------------------------------------------------------------------------------------------------------------------------------
--
-- 2024 Performance Period Measures

#IfNotRow list_options list_id ecqm_2024_reporting
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('lists','ecqm_2024_reporting','eCQM 2024 Performance Period',0,1,0, '');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS1028v2','Severe Obstetric Complications',10,0,'Patients with severe obstetric complications which occur during the inpatient delivery hospitalization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS104v12','Discharged on Antithrombotic Therapy',20,0,'Ischemic stroke patients prescribed or continuing to take antithrombotic therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS1056v1','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Clinician Level)',30,0,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. It is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in inpatient, outpatient and ambulatory care settings are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS1074v1','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility IQR)',40,0,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. It is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital inpatient care settings are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS108v12','Venous Thromboembolism Prophylaxis',50,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given between the day of arrival to the day after hospital admission or surgery end date for surgeries that start the day of or the day after hospital admission');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS117v12','Childhood Immunization Status',60,0,'Percentage of children 2 years of age who had four diphtheria, tetanus and acellular pertussis (DTaP); three polio (IPV), one measles, mumps and rubella (MMR); three or four H influenza type B (Hib); three hepatitis B (Hep B); one chicken pox (VZV); four pneumococcal conjugate (PCV); one hepatitis A (Hep A); two or three rotavirus (RV); and two influenza (flu) vaccines by their second birthday');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS1188v1','Sexually Transmitted Infection (STI) Testing for People with HIV',70,0,'Percentage of patients 13 years of age and older with a diagnosis of HIV who had tests for syphilis, gonorrhea, and chlamydia performed within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS1206v1','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility OQR)',80,0,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. It is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital outpatient care settings (including emergency settings) are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS122v12','Diabetes: Hemoglobin A1c (HbA1c) Poor Control (> 9%)',90,1,'Percentage of patients 18-75 years of age with diabetes who had hemoglobin A1c &gt; 9.0% during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS124v12','Cervical Cancer Screening',100,1,'Percentage of women 21-64 years of age who were screened for cervical cancer using either of the following criteria:<br>- Women age 21-64 who had cervical cytology performed within the last 3 years<br>- Women age 30-64 who had cervical human papillomavirus (HPV) testing performed within the last 5 years');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS125v12','Breast Cancer Screening',110,1,'Percentage of women 50-74 years of age who had a mammogram to screen for breast cancer in the 27 months prior to the end of the Measurement Period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS127v12','Pneumococcal Vaccination Status for Older Adults',120,1,'Percentage of patients 65 years of age and older who have received a pneumococcal vaccine');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS128v12','Anti-depressant Medication Management',130,0,'Percentage of patients 18 years of age and older who were treated with antidepressant medication, had a diagnosis of major depression, and who remained on an antidepressant medication treatment. Two rates are reported. <br>a. Percentage of patients who remained on an antidepressant medication for at least 84 days (12 weeks). <br>b. Percentage of patients who remained on an antidepressant medication for at least 180 days (6 months).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS129v13','Prostate Cancer: Avoidance of Overuse of Bone Scan for Staging Low Risk Prostate Cancer Patients',140,0,'Percentage of patients, regardless of age, with a diagnosis of prostate cancer at low (or very low) risk of recurrence receiving interstitial prostate brachytherapy, OR external beam radiotherapy to the prostate, OR radical prostatectomy who did not have a bone scan performed at any time since diagnosis of prostate cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS130v12','Colorectal Cancer Screening',150,1,'Percentage of adults 45-75 years of age who had appropriate screening for colorectal cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS131v12','Diabetes: Eye Exam',160,0,'Percentage of patients 18-75 years of age with diabetes and an active diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or diabetics with no diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or in the 12 months prior to the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS133v12','Cataracts: 20/40 or Better Visual Acuity within 90 Days Following Cataract Surgery',170,0,'Percentage of cataract surgeries for patients aged 18 and older with a diagnosis of uncomplicated cataract and no significant ocular conditions impacting the visual outcome of surgery and had best-corrected visual acuity of 20/40 or better (distance or near) achieved in the operative eye within 90 days following the cataract surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS135v12','Heart Failure (HF): Angiotensin-Converting Enzyme (ACE) Inhibitor or Angiotensin Receptor Blocker (ARB) or Angiotensin Receptor-Neprilysin Inhibitor (ARNI) Therapy for Left Ventricular Systolic Dysfunction (LVSD)',180,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed or already taking ACE inhibitor or ARB or ARNI therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS136v13','Follow-Up Care for Children Prescribed ADHD Medication (ADD)',190,0,'Percentage of children 6-12 years of age and newly prescribed a medication for attention-deficit/hyperactivity disorder (ADHD) who had appropriate follow-up care. Two rates are reported.  <br>a. Percentage of children who had one follow-up visit with a practitioner with prescribing authority during the 30-Day Initiation Phase.<br>b. Percentage of children who remained on ADHD medication for at least 210 treatment days and who, in addition to the visit in the Initiation Phase, had at least two additional follow-up visits with a practitioner within 270 days (9 months) after the Initiation Phase ended.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS137v12','Initiation and Engagement of Substance Use Disorder Treatment',200,0,'Percentage of patients 13 years of age and older with a new substance use disorder (SUD) episode who received the following (Two rates are reported):<br><br>a. Percentage of patients who initiated treatment, including either an intervention or medication for the treatment of SUD, within 14 days of the new SUD episode. <br>b. Percentage of patients who engaged in ongoing treatment, including two additional interventions or short-term medications, or one long-term medication for the treatment of SUD, within 34 days of the initiation.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS138v12','Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',210,1,'Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user.<br><br>Three rates are reported: <br>a. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period<br>b. Percentage of patients aged 12 years and older who were identified as a tobacco user during the measurement period who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period <br>c. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS139v12','Falls: Screening for Future Fall Risk',220,0,'Percentage of patients 65 years of age and older who were screened for future fall risk during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS142v12','Diabetic Retinopathy: Communication with the Physician Managing Ongoing Diabetes Care',230,0,'Percentage of patients aged 18 years and older with a diagnosis of diabetic retinopathy who had a dilated macular or fundus exam performed with documented communication to the physician who manages the ongoing care of the patient with diabetes mellitus regarding the findings of the macular or fundus exam at least once during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS143v12','Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation',240,0,'Percentage of patients aged 18 years and older with a diagnosis of primary open-angle glaucoma (POAG) who have an optic nerve head evaluation during one or more visits within 12 months');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS144v12','Heart Failure (HF): Beta-Blocker Therapy for Left Ventricular Systolic Dysfunction (LVSD)',250,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;= 40% who were prescribed or already taking beta-blocker therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS145v12','Coronary Artery Disease (CAD): Beta-Blocker Therapy-Prior Myocardial Infarction (MI) or Left Ventricular Systolic Dysfunction (LVEF <=40%)',260,0,'Percentage of patients aged 18 years and older with a diagnosis of coronary artery disease seen within a 12-month period who also have a prior MI or a current or prior LVEF &lt;=40% who were prescribed beta-blocker therapy');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS146v12','Appropriate Testing for Pharyngitis',270,0,'The percentage of episodes for patients 3 years and older with a diagnosis of pharyngitis that resulted in an antibiotic order on or three days after the episode date and a group A streptococcus (strep) test in the seven-day period from three days prior to the episode date through three days after the episode date');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS147v13','Preventive Care and Screening: Influenza Immunization',280,1,'Percentage of patients aged 6 months and older seen for a visit between October 1 and March 31 who received an influenza immunization OR who reported previous receipt of an influenza immunization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS149v12','Dementia: Cognitive Assessment',290,0,'Percentage of patients, regardless of age, with a diagnosis of dementia for whom an assessment of cognition is performed and the results reviewed at least once within a 12-month period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS153v12','Chlamydia Screening in Women',300,0,'Percentage of women 16-24 years of age who were identified as sexually active at any time during the measurement period and who had at least one test for chlamydia during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS154v12','Appropriate Treatment for Upper Respiratory Infection (URI)',310,0,'Percentage of episodes for patients 3 months of age and older with a diagnosis of upper respiratory infection (URI) that did not result in an antibiotic order');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS155v12','Weight Assessment and Counseling for Nutrition and Physical Activity for Children/Adolescents',320,0,'Percentage of patients 3-17 years of age who had an outpatient visit with a primary care physician (PCP) or obstetrician/gynecologist (OB/GYN) and who had evidence of the following during the measurement period.<br><br> - Percentage of patients with height, weight, and body mass index (BMI) percentile documentation<br> - Percentage of patients with counseling for nutrition<br> - Percentage of patients with counseling for physical activity');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS156v12','Use of High-Risk Medications in Older Adults',330,0,'Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class. Three rates are reported. <br>1. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class.<br>2. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class, except for appropriate diagnoses.<br>3. Total rate (the sum of the two numerators divided by the denominator, deduplicating for patients in both numerators).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS157v12','Oncology: Medical and Radiation - Pain Intensity Quantified',340,0,'Percentage of patient visits, regardless of patient age, with a diagnosis of cancer currently receiving chemotherapy or radiation therapy in which pain intensity is quantified');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS159v12','Depression Remission at Twelve Months',350,0,'The percentage of adolescent patients 12 to 17 years of age and adult patients 18 years of age or older with major depression or dysthymia who reached remission 12 months (+/- 60 days) after an index event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS161v12','Adult Major Depressive Disorder (MDD): Suicide Risk Assessment',360,0,'Percentage of all patient visits for those patients that are 17 years of age or older at the start of the measurement period in which a new or recurrent diagnosis of major depressive disorder (MDD) was identified and a suicide risk assessment was completed during the visit');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS165v12','Controlling High Blood Pressure',370,1,'Percentage of patients 18-85 years of age who had a diagnosis of essential hypertension starting before and continuing into, or starting during the first six months of the measurement period, and whose most recent blood pressure was adequately controlled (&lt;140/90 mmHg) during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS177v12','Child and Adolescent Major Depressive Disorder (MDD): Suicide Risk Assessment',380,0,'Percentage of patient visits for those patients aged 6 through 16 at the start of the measurement period with a diagnosis of major depressive disorder (MDD) with an assessment for suicide risk');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS190v12','Intensive Care Unit Venous Thromboembolism Prophylaxis',390,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given the day of or the day after the initial admission (or transfer) to the Intensive Care Unit (ICU) or surgery end date for surgeries that start the day of or the day after ICU admission (or transfer)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS22v12','Preventive Care and Screening: Screening for High Blood Pressure and Follow-Up Documented',400,1,'Percentage of patient visits for patients aged 18 years and older seen during the measurement period who were screened for high blood pressure AND a recommended follow-up plan is documented, as indicated, if blood pressure is elevated or hypertensive');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS249v6','Appropriate Use of DXA Scans in Women Under 65 Years Who Do Not Meet the Risk Factor Profile for Osteoporotic Fracture',410,0,'Percentage of female patients 50 to 64 years of age without select risk factors for osteoporotic fracture who received an order for a dual-energy x-ray absorptiometry (DXA) scan during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS2v13','Preventive Care and Screening: Screening for Depression and Follow-Up Plan',420,0,'Percentage of patients aged 12 years and older screened for depression on the date of the encounter or up to 14 days prior to the date of the encounter using an age-appropriate standardized depression screening tool AND if positive, a follow-up plan is documented on the date of or up to two days after the date of the qualifying encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS314v1','HIV Viral Suppression',430,0,'Percentage of patients, regardless of age, diagnosed with HIV prior to or during the first 90 days of the measurement period, with an eligible encounter in the first 240 days of the measurement period, whose last HIV viral load test result was less than 200 copies/mL during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS334v5','Cesarean Birth',440,0,'Nulliparous women with a term, singleton baby in a vertex position delivered by cesarean birth');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS347v7','Statin Therapy for the Prevention and Treatment of Cardiovascular Disease',450,0,'Percentage of the following patients - all considered at high risk of cardiovascular events - who were prescribed or were on statin therapy during the measurement period: <br>- All patients who were previously diagnosed with or currently have a diagnosis of clinical atherosclerotic cardiovascular disease (ASCVD), including an ASCVD procedure; OR <br>- Patients aged 20 to 75 years who have ever had a low-density lipoprotein cholesterol (LDL-C) level &gt;= 190 mg/dL or were previously diagnosed with or currently have an active diagnosis of familial hypercholesterolemia; OR <br>- Patients aged 40-75 years with a diagnosis of diabetes; OR<br>- Patients aged 40 to 75 with a 10-year ASCVD risk score of &gt;= 20 percent');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS349v6','HIV Screening',460,0,'Percentage of patients aged 15-65 at the start of the measurement period who were between 15-65 years old when tested for Human immunodeficiency virus (HIV)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS506v6','Safe Use of Opioids - Concurrent Prescribing',470,0,'Proportion of inpatient hospitalizations for patients 18 years of age and older prescribed, or continued on, two or more opioids or an opioid and benzodiazepine concurrently at discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS50v12','Closing the Referral Loop: Receipt of Specialist Report',480,0,'Percentage of patients with referrals, regardless of age, for which the referring clinician receives a report from the clinician to whom the patient was referred');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS529v4','Core Clinical Data Elements for the Hybrid Hospital-Wide Readmission (HWR) Measure with Claims and Electronic Health Record Data',490,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWR outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from encounters for adult Medicare Fee-For-Service and Medicare Advantage patients admitted to acute care short stay hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS56v12','Functional Status Assessment for Total Hip Replacement',500,0,'Percentage of patients 19 years of age and older who received an elective primary total hip arthroplasty (THA) and completed a functional status assessment within 90 days prior to the surgery and in the 300 - 425 days after the surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS645v7','Bone density evaluation for patients with prostate cancer and receiving androgen deprivation therapy',510,0,'Percentage of patients determined as having prostate cancer who are currently starting or undergoing androgen deprivation therapy (ADT), for an anticipated period of 12 months or greater and who receive an initial bone density evaluation. The bone density evaluation must be prior to the start of ADT or within 3 months of the start of ADT.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS646v4','Intravesical Bacillus-Calmette-Guerin for non-muscle invasive bladder cancer',520,0,'Percentage of patients initially diagnosed with non-muscle invasive bladder cancer and who received intravesical Bacillus-Calmette-Guerin (BCG) within 6 months of bladder cancer staging');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS68v13','Documentation of Current Medications in the Medical Record',530,0,'Percentage of visits for patients aged 18 years and older for which the eligible clinician attests to documenting a list of current medications using all immediate resources available on the date of the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS69v12','Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan',540,1,'Percentage of patients aged 18 years and older with a BMI documented during the current encounter or during the measurement period AND who had a follow-up plan documented if BMI was outside of normal parameters');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS71v13','Anticoagulation Therapy for Atrial Fibrillation/Flutter',550,0,'Ischemic stroke patients with atrial fibrillation/flutter who are prescribed or continuing to take anticoagulation therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS72v12','Antithrombotic Therapy By End of Hospital Day 2',560,0,'Ischemic stroke patients administered antithrombotic therapy by the end of hospital day 2');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS74v13','Primary Caries Prevention Intervention as Offered by Dentists',570,0,'Percentage of children, 1 - 20 years of age, who received two fluoride varnish applications during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS75v12','Children Who Have Dental Decay or Cavities',580,0,'Percentage of children, 1 - 20 years of age at the start of the measurement period, who have had tooth decay or cavities during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS771v5','Urinary Symptom Score Change 6-12 Months After Diagnosis of Benign Prostatic Hyperplasia',590,0,'Percentage of patients with an office visit within the measurement period and with a new diagnosis of clinically significant Benign Prostatic Hyperplasia who have International Prostate Symptoms Score (IPSS) or American Urological Association (AUA) Symptom Index (SI) documented at time of diagnosis and again 6-12 months later with an improvement of 3 points');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS816v3','Hospital Harm - Severe Hypoglycemia',600,0,'The measure assesses the number of inpatient hospitalizations for patients age 18 and older who were administered at least one hypoglycemic medication during the encounter, who suffer the harm of a severe hypoglycemic event during the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS819v2','Hospital Harm - Opioid-Related Adverse Events',610,0,'This measure assesses the number of inpatient hospitalizations for patients age 18 and older who have been administered an opioid medication and are subsequently administered an opioid antagonist within 12 hours, an indication of an opioid-related adverse event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS826v1','Hospital Harm - Pressure Injury',620,0,'The proportion of inpatient hospitalizations for patients aged 18 and older who suffer the harm of developing a new stage 2, stage 3, stage 4, deep tissue, or unstageable pressure injury');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS832v1','Hospital Harm -  Acute Kidney Injury',630,0,'The proportion of inpatient hospitalizations for patients age 18 and older who have an acute kidney injury (stage 2 or greater) that occurred during the encounter. Acute kidney injury (AKI) stage 2 or greater is defined as a substantial increase in serum creatinine value, or by the initiation of kidney dialysis (continuous renal replacement therapy (CRRT), hemodialysis or peritoneal dialysis).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS844v4','Core Clinical Data Elements for the Hybrid Hospital-Wide (All-Condition, All-Procedure) Risk-Standardized Mortality Measure (HWM)',640,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWM outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from encounters for adult Medicare Fee-For-Service and Medicare Advantage patients admitted to acute care short stay hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS871v3','Hospital Harm - Severe Hyperglycemia',650,0,'This measure assesses the number of inpatient hospital days for patients age 18 and older with a hyperglycemic event (harm) per the total qualifying inpatient hospital days for that encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS90v13','Functional Status Assessments for Heart Failure',660,0,'Percentage of patients 18 years of age and older with heart failure who completed initial and follow-up patient-reported functional status assessments');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS951v2','Kidney Health Evaluation',670,0,'Percentage of patients aged 18-75 years with a diagnosis of diabetes who received a kidney health evaluation defined by an Estimated Glomerular Filtration Rate (eGFR) AND Urine Albumin-Creatinine Ratio (uACR) within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2024_reporting','CMS996v4','Appropriate Treatment for ST-Segment Elevation Myocardial Infarction (STEMI) Patients in the Emergency Department (ED)',680,0,'Percentage of emergency department (ED) encounters for patients 18 years and older with a diagnosis of ST-segment elevation myocardial infarction (STEMI) that received appropriate treatment, defined as fibrinolytic therapy within 30 minutes of ED arrival, percutaneous coronary intervention (PCI) within 90 minutes of ED arrival, or transfer within 45 minutes of ED arrival');
#EndIf
-- ---------------------------------------------------------------------------------------------------------------------------------
--
-- 2025 Performance Period Measures

#IfNotRow list_options list_id ecqm_2025_reporting
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('lists','ecqm_2025_reporting','eCQM 2025 Performance Period',0,1,0, '');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1017v1','Hospital Harm – Falls with Injury',10,0,'This ratio measure assesses the number of inpatient hospitalizations where at least one fall with a major or moderate injury occurs among the total qualifying inpatient hospital days for patients age 18 years and older');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1028v3','Severe Obstetric Complications',20,0,'Patients with severe obstetric complications that occur during the inpatient delivery hospitalization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS104v13','Discharged on Antithrombotic Therapy',30,0,'Ischemic stroke patients prescribed or continuing to take antithrombotic therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1056v2','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Clinician Level)',40,1,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. It is expressed as a percentage of patients with CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in inpatient, outpatient and ambulatory care settings are eligible. This measure is not telehealth eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1074v2','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility IQR)',50,0,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. This measure is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital inpatient care settings are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS108v13','Venous Thromboembolism Prophylaxis',60,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given between the day of arrival to the day after hospital admission or surgery end date for surgeries that start the day of or the day after hospital admission');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1157v1','HIV Annual Retention in Care',70,0,'Percentage of patients, regardless of age, with a diagnosis of Human Immunodeficiency Virus (HIV) during the first 240 days of the measurement period or before the measurement period who had at least two eligible encounters or at least one eligible encounter and one HIV viral load test that were at least 90 days apart within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS117v13','Childhood Immunization Status',80,0,'Percentage of children 2 years of age who had four diphtheria, tetanus and acellular pertussis (DTaP); three polio (IPV), one measles, mumps and rubella (MMR); three or four H influenza type B (HiB); three hepatitis B (HepB); one chicken pox (VZV); four pneumococcal conjugate (PCV); one hepatitis A (HepA); two or three rotavirus (RV); and two influenza (flu) vaccines by their second birthday');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1188v2','Sexually Transmitted Infection (STI) Testing for People with HIV',90,0,'Percentage of patients 13 years of age and older with a diagnosis of HIV who had tests for syphilis, gonorrhea, and chlamydia performed within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1206v2','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility OQR)',100,0,'This measure is an episode of care measure that provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. This measure is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital non-inpatient care settings (including emergency settings) are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS1218v1','Hospital Harm - Postoperative Respiratory Failure',110,0,'This measure assesses the number of elective inpatient hospitalizations for patients aged 18 years and older without an obstetrical condition who have a procedure resulting in postoperative respiratory failure (PRF)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS122v13','Diabetes: Glycemic Status Assessment Greater Than 9%',120,1,'Percentage of patients 18-75 years of age with diabetes who had a glycemic status assessment (hemoglobin A1c [HbA1c] or glucose management indicator [GMI]) &gt; 9.0% during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS124v13','Cervical Cancer Screening',130,1,'Percentage of women 21-64 years of age who were screened for cervical cancer using either of the following criteria:<br>- Women age 21-64 who had cervical cytology performed within the last 3 years<br>- Women age 30-64 who had cervical human papillomavirus (HPV) testing performed within the last 5 years');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS125v13','Breast Cancer Screening',140,1,'Percentage of women 50-74 years of age who had a mammogram to screen for breast cancer in the 27 months prior to the end of the Measurement Period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS128v13','Antidepressant Medication Management',150,0,'Percentage of patients 18 years of age and older who were treated with antidepressant medication, had a diagnosis of major depression, and who remained on an antidepressant medication treatment. Two rates are reported. <br>a. Percentage of patients who remained on an antidepressant medication for at least 84 days (12 weeks). <br>b. Percentage of patients who remained on an antidepressant medication for at least 180 days (6 months).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS129v14','Prostate Cancer: Avoidance of Overuse of Bone Scan for Staging Low Risk Prostate Cancer Patients',160,0,'Percentage of patients, regardless of age, with a diagnosis of prostate cancer at low (or very low) risk of recurrence receiving interstitial prostate brachytherapy, OR external beam radiotherapy to the prostate, OR radical prostatectomy who did not have a bone scan performed at any time since diagnosis of prostate cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS130v13','Colorectal Cancer Screening',170,1,'Percentage of adults 45-75 years of age who had appropriate screening for colorectal cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS131v13','Diabetes: Eye Exam',180,0,'Percentage of patients 18-75 years of age with diabetes and an active diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or diabetics with no diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or in the 12 months prior to the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS133v13','Cataracts: 20/40 or Better Visual Acuity within 90 Days Following Cataract Surgery',190,0,'Percentage of cataract surgeries for patients aged 18 and older with a diagnosis of uncomplicated cataract and no significant ocular conditions impacting the visual outcome of surgery and had best-corrected visual acuity of 20/40 or better (distance or near) achieved in the operative eye within 90 days following the cataract surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS135v13','Heart Failure (HF): Angiotensin-Converting Enzyme (ACE) Inhibitor or Angiotensin Receptor Blocker (ARB) or Angiotensin Receptor-Neprilysin Inhibitor (ARNI) Therapy for Left Ventricular Systolic Dysfunction (LVSD)',200,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed or already taking ACE inhibitor or ARB or ARNI therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS136v14','Follow-Up Care for Children Prescribed ADHD Medication (ADD)',210,0,'Percentage of children 6-12 years of age and newly prescribed a medication for attention-deficit/hyperactivity disorder (ADHD) who had appropriate follow-up care. Two rates are reported.  <br>a. Percentage of children who had one follow-up visit with a practitioner with prescribing authority during the 30-Day Initiation Phase.<br>b. Percentage of children who remained on ADHD medication for at least 210 treatment days and who, in addition to the visit in the Initiation Phase, had at least two additional follow-up visits with a practitioner within 270 days (9 months) after the Initiation Phase ended.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS137v13','Initiation and Engagement of Substance Use Disorder Treatment',220,0,'Percentage of patients 13 years of age and older with a new substance use disorder (SUD) episode who received the following (Two rates are reported):<br><br>a. Percentage of patients who initiated treatment, including either an intervention or medication for the treatment of SUD, within 14 days of the new SUD episode. <br>b. Percentage of patients who engaged in ongoing treatment, including two additional interventions or medication treatment events for SUD, or one long-acting medication event for the treatment of SUD, within 34 days of the initiation.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS138v13','Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',230,1,'Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user.<br><br>Three rates are reported: <br>a. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period<br>b. Percentage of patients aged 12 years and older who were identified as a tobacco user during the measurement period who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period <br>c. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS139v13','Falls: Screening for Future Fall Risk',240,0,'Percentage of patients 65 years of age and older who were screened for future fall risk during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS142v13','Diabetic Retinopathy: Communication with the Physician Managing Ongoing Diabetes Care',250,0,'Percentage of patients aged 18 years and older with a diagnosis of diabetic retinopathy who had a dilated macular or fundus exam performed with documented communication to the physician who manages the ongoing care of the patient with diabetes mellitus regarding the findings of the macular or fundus exam at least once during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS143v13','Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation',260,0,'Percentage of patients aged 18 years and older with a diagnosis of primary open-angle glaucoma (POAG) who have an optic nerve head evaluation during one or more visits within 12 months');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS144v13','Heart Failure (HF): Beta-Blocker Therapy for Left Ventricular Systolic Dysfunction (LVSD)',270,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;= 40% who were prescribed or already taking beta-blocker therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS145v13','Coronary Artery Disease (CAD): Beta-Blocker Therapy-Prior Myocardial Infarction (MI) or Left Ventricular Systolic Dysfunction (LVEF less than or equal to 40%)',280,0,'Percentage of patients aged 18 years and older with a diagnosis of coronary artery disease seen within a 12-month period who also have a prior MI or a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed beta-blocker therapy');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS146v13','Appropriate Testing for Pharyngitis',290,0,'The percentage of episodes for patients 3 years and older with a diagnosis of pharyngitis that resulted in an antibiotic order on or three days after the episode date and a group A streptococcus (strep) test in the seven-day period from three days prior to the episode date through three days after the episode date');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS149v13','Dementia: Cognitive Assessment',300,0,'Percentage of patients, regardless of age, with a diagnosis of dementia for whom an assessment of cognition is performed and the results reviewed at least once within a 12-month period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS153v13','Chlamydia Screening in Women',310,0,'Percentage of women 16-24 years of age who were identified as sexually active at any time during the measurement period and who had at least one test for chlamydia during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS154v13','Appropriate Treatment for Upper Respiratory Infection (URI)',320,0,'Percentage of episodes for patients 3 months of age and older with a diagnosis of upper respiratory infection (URI) that did not result in an antibiotic order');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS155v13','Weight Assessment and Counseling for Nutrition and Physical Activity for Children/Adolescents',330,0,'Percentage of patients 3-17 years of age who had an outpatient visit with a primary care physician (PCP) or obstetrician/gynecologist (OB/GYN) and who had evidence of the following during the measurement period.<br><br> - Percentage of patients with height, weight, and body mass index (BMI) percentile documentation<br> - Percentage of patients with counseling for nutrition<br> - Percentage of patients with counseling for physical activity');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS156v13','Use of High-Risk Medications in Older Adults',340,0,'Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class. Three rates are reported. <br>1. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class.<br>2. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class, except for appropriate diagnoses.<br>3. Total rate (the sum of the two numerators divided by the denominator, deduplicating for patients in both numerators).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS157v13','Oncology: Medical and Radiation - Pain Intensity Quantified',350,0,'Percentage of patient visits, regardless of patient age, with a diagnosis of cancer currently receiving chemotherapy or radiation therapy in which pain intensity is quantified');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS159v13','Depression Remission at Twelve Months',360,0,'The percentage of adolescent patients 12 to 17 years of age and adult patients 18 years of age or older with major depression or dysthymia who reached remission 12 months (+/- 60 days) after an index event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS165v13','Controlling High Blood Pressure',370,1,'Percentage of patients 18-85 years of age who had a diagnosis of essential hypertension starting before and continuing into, or starting during the first six months of the measurement period, and whose most recent blood pressure was adequately controlled (&lt;140/90 mmHg) during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS177v13','Child and Adolescent Major Depressive Disorder (MDD): Suicide Risk Assessment',380,0,'Percentage of patient visits for those patients aged 6 through 16 at the start of the measurement period with a diagnosis of major depressive disorder (MDD) with an assessment for suicide risk');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS190v13','Intensive Care Unit Venous Thromboembolism Prophylaxis',390,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given the day of or the day after the initial admission (or transfer) to the Intensive Care Unit (ICU) or surgery end date for surgeries that start the day of or the day after ICU admission (or transfer)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS22v13','Preventive Care and Screening: Screening for High Blood Pressure and Follow-Up Documented',400,1,'Percentage of patient visits for patients aged 18 years and older seen during the measurement period who were screened for high blood pressure AND a recommended follow-up plan is documented, as indicated, if blood pressure is elevated or hypertensive');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS249v7','Appropriate Use of DXA Scans in Women Under 65 Years Who Do Not Meet the Risk Factor Profile for Osteoporotic Fracture',410,0,'Percentage of female patients 50 to 64 years of age without select risk factors for osteoporotic fracture who received an order for a dual-energy x-ray absorptiometry (DXA) scan during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS2v14','Preventive Care and Screening: Screening for Depression and Follow-Up Plan',420,0,'Percentage of patients aged 12 years and older screened for depression on the date of the encounter or up to 14 days prior to the date of the encounter using an age-appropriate standardized depression screening tool AND if positive a follow-up plan is documented on the date of or up to two days after the date of the qualifying encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS314v2','HIV Viral Suppression',430,0,'Percentage of patients, regardless of age, diagnosed with HIV prior to or during the first 90 days of the measurement period, with an eligible encounter in the first 240 days of the measurement period, whose last HIV viral load test result was less than 200 copies/mL during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS334v6','Cesarean Birth',440,0,'Nulliparous women with a term, singleton baby in a vertex position delivered by cesarean birth');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS347v8','Statin Therapy for the Prevention and Treatment of Cardiovascular Disease',450,0,'Percentage of the following patients - all considered at high risk of cardiovascular events - who were prescribed or were on statin therapy during the measurement period: <br>- All patients who were previously diagnosed with or currently have a diagnosis of clinical atherosclerotic cardiovascular disease (ASCVD), including an ASCVD procedure; OR <br>- Patients aged 20 to 75 years who have ever had a low-density lipoprotein cholesterol (LDL-C) level &gt;= 190 mg/dL or were previously diagnosed with or currently have an active diagnosis of familial hypercholesterolemia; OR <br>- Patients aged 40-75 years with a diagnosis of diabetes; OR<br>- Patients aged 40 to 75 with a 10-year ASCVD risk score of &gt;= 20 percent.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS349v7','HIV Screening',460,0,'Percentage of patients aged 15-65 at the start of the measurement period who were between 15-65 years old when tested for human immunodeficiency virus (HIV).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS506v7','Safe Use of Opioids - Concurrent Prescribing',470,0,'Proportion of inpatient hospitalizations for patients 18 years of age and older prescribed, or continued on, two or more opioids or an opioid and benzodiazepine concurrently at discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS50v13','Closing the Referral Loop: Receipt of Specialist Report',480,0,'Percentage of patients with referrals, regardless of age, for which the referring clinician receives a report from the clinician to whom the patient was referred');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS529v5','Core Clinical Data Elements for the Hybrid Hospital-Wide Readmission Measure with Claims and Electronic Health Record Data - HWR',490,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWR outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from hospitalizations for adult Medicare Fee-For-Service (FFS) and Medicare Advantage (MA) patients admitted to acute care hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS56v13','Functional Status Assessment for Total Hip Replacement',500,0,'Percentage of patients 19 years of age and older who received an elective primary total hip arthroplasty (THA) and completed a functional status assessment within 90 days prior to the surgery and in the 300 - 425 days after the surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS645v8','Bone Density Evaluation for Patients with Prostate Cancer and Receiving Androgen Deprivation Therapy',510,0,'Percentage of patients determined as having prostate cancer who are currently starting or undergoing androgen deprivation therapy (ADT), for an anticipated period of 12 months or greater and who receive an initial bone density evaluation. The bone density evaluation must be prior to the start of ADT or within 3 months of the start of ADT.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS646v5','Intravesical Bacillus-Calmette-Guerin for Non-Muscle Invasive Bladder Cancer',520,0,'Percentage of patients initially diagnosed with non-muscle invasive bladder cancer and who received intravesical Bacillus-Calmette-Guerin (BCG) within 6 months of bladder cancer staging');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS68v14','Documentation of Current Medications in the Medical Record',530,1,'Percentage of visits for which the eligible clinician attests to documenting a list of current medications using all immediate resources available on the date of the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS69v13','Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan',540,1,'Percentage of patients aged 18 years and older with a BMI documented during the current encounter or during the measurement period AND who had a follow-up plan documented if BMI was outside of normal parameters');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS71v14','Anticoagulation Therapy for Atrial Fibrillation/Flutter',550,0,'Ischemic stroke patients with atrial fibrillation/flutter who are prescribed or continuing to take anticoagulation therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS72v13','Antithrombotic Therapy by End of Hospital Day 2',560,0,'Ischemic stroke patients administered antithrombotic therapy by the end of hospital day 2');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS74v14','Primary Caries Prevention Intervention as Offered by Dentists',570,0,'Percentage of children, 1-20 years of age, who received two fluoride varnish applications during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS75v13','Children Who Have Dental Decay or Cavities',580,0,'Percentage of children, 1-20 years of age at the start of the measurement period, who have had dental decay or cavities during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS771v6','Urinary Symptom Score Change 6-12 Months After Diagnosis of Benign Prostatic Hyperplasia',590,0,'Percentage of patients with an office visit within the measurement period and with a new diagnosis of clinically significant Benign Prostatic Hyperplasia who have International Prostate Symptoms Score (IPSS) or American Urological Association (AUA) Symptom Index (SI) documented at time of diagnosis and again 6-12 months later with an improvement of 3 points');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS816v4','Hospital Harm - Severe Hypoglycemia',600,0,'The measure assesses the number of inpatient hospitalizations for patients age 18 and older who were administered at least one hypoglycemic medication during the encounter and who suffer the harm of a severe hypoglycemic event during the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS819v3','Hospital Harm - Opioid-Related Adverse Events',610,0,'This measure assesses the number of inpatient hospitalizations for patients age 18 and older who have been administered an opioid medication outside of the operating room and are subsequently administered a non-enteral opioid antagonist outside of the operating room within 12 hours, an indication of an opioid-related adverse event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS826v2','Hospital Harm - Pressure Injury',620,0,'The measure assesses the number of inpatient hospitalizations for patients aged 18 and older who suffer the harm of developing a new stage 2, stage 3, stage 4, deep tissue, or unstageable pressure injury');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS832v2','Hospital Harm -  Acute Kidney Injury',630,0,'The measure assesses the number of inpatient hospitalizations for patients age 18 and older who have an acute kidney injury (stage 2 or greater) that occurred during the encounter. Acute kidney injury (AKI) stage 2 or greater is defined as a substantial increase in serum creatinine value, or by the initiation of kidney dialysis (continuous renal replacement therapy (CRRT), hemodialysis or peritoneal dialysis).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS844v5','Core Clinical Data Elements for the Hybrid Hospital-Wide All-Condition All-Procedure Risk-Standardized Mortality Measure - HWM',640,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWM outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from hospitalizations for adult Medicare Fee-For-Service (FFS) and Medicare Advantage (MA) patients admitted to acute care hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS871v4','Hospital Harm - Severe Hyperglycemia',650,0,'This measure assesses the number of inpatient hospital days for patients age 18 and older with a hyperglycemic event (harm) per the total qualifying inpatient hospital days for that encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS90v14','Functional Status Assessments for Heart Failure',660,0,'Percentage of patients 18 years of age and older with heart failure who completed initial and follow-up patient-reported functional status assessments');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS951v3','Kidney Health Evaluation',670,0,'Percentage of patients aged 18-85 years with a diagnosis of diabetes who received a kidney health evaluation defined by an Estimated Glomerular Filtration Rate (eGFR) AND Urine Albumin-Creatinine Ratio (uACR) within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS986v4','Global Malnutrition Composite Score',680,0,'This measure assesses the percentage of hospitalizations of adults aged 65 years and older at the start of the inpatient encounter during the measurement period, with a length of stay equal to or greater than 24 hours, who received optimal malnutrition care where care performed was appropriate to the patient&#39;s level of malnutrition risk and severity. Malnutrition care best practices recommend that for each hospitalization, adult inpatients are (1) screened for malnutrition risk or for a hospital dietitian referral order to be placed, (2) assessed by a registered dietitian (RD) or registered dietitian nutritionist (RDN) to confirm findings of malnutrition risk, and if identified with a &quot;moderate&quot; or &quot;severe&quot; malnutrition status in the current performed malnutrition assessment, (3) receive a &quot;moderate&quot; or &quot;severe&quot; malnutrition diagnosis by a physician or eligible provider as defined by the Centers for Medicare &amp; Medicaid Services (CMS), and (4) have a current nutrition care plan performed by an RD/RDN.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2025_reporting','CMS996v5','Appropriate Treatment for ST-Segment Elevation Myocardial Infarction (STEMI) Patients in the Emergency Department (ED)',690,0,'Percentage of emergency department (ED) encounters for patients 18 years and older with a diagnosis of ST-segment elevation myocardial infarction (STEMI) that received appropriate treatment, defined as fibrinolytic therapy within 30 minutes of ED arrival, percutaneous coronary intervention (PCI) within 90 minutes of ED arrival, or transfer within 45 minutes of ED arrival');
#EndIf
-- ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
--
-- Periods List

#IfNotRow list_options list_id ecqm_reporting_period
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists','ecqm_reporting_period','eCQM Reporting Periods',0,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `is_default`) VALUES ('ecqm_reporting_period','2022','2022 Reporting Period',10,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `is_default`) VALUES ('ecqm_reporting_period','2023','2023 Reporting Period',20,1,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `is_default`) VALUES ('ecqm_reporting_period','2024','2024 Reporting Period',30,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `is_default`) VALUES ('ecqm_reporting_period','2025','2025 Reporting Period',40,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `is_default`) VALUES ('ecqm_reporting_period','2026','2026 Reporting Period',50,0,0);
#EndIf
-- ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Social History SDOH

#IfNotTable form_history_sdoh
CREATE TABLE `form_history_sdoh`
(
    `id`                              bigint(21) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                            binary(16)                   DEFAULT NULL,
    `pid`                             int(10) UNSIGNED    NOT NULL,
    `encounter`                       int(10) UNSIGNED             DEFAULT NULL,
    `created_at`                      datetime            NOT NULL DEFAULT current_timestamp(),
    `updated_at`                      datetime            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_by`                      int(10) UNSIGNED             DEFAULT NULL,
    `updated_by`                      int(10) UNSIGNED             DEFAULT NULL,
    `assessment_date`                 date                         DEFAULT NULL,
    `screening_tool`                  varchar(255)                 DEFAULT NULL,
    `assessor`                        varchar(255)                 DEFAULT NULL,
    `food_insecurity`                 varchar(50)                  DEFAULT NULL,
    `food_insecurity_notes`           text,
    `housing_instability`             varchar(50)                  DEFAULT NULL,
    `housing_instability_notes`       text,
    `transportation_insecurity`       varchar(50)                  DEFAULT NULL,
    `transportation_insecurity_notes` text,
    `utilities_insecurity`            varchar(50)                  DEFAULT NULL,
    `utilities_insecurity_notes`      text,
    `interpersonal_safety`            varchar(50)                  DEFAULT NULL,
    `interpersonal_safety_notes`      text,
    `financial_strain`                varchar(50)                  DEFAULT NULL,
    `financial_strain_notes`          text,
    `social_isolation`                varchar(50)                  DEFAULT NULL,
    `social_isolation_notes`          text,
    `childcare_needs`                 varchar(50)                  DEFAULT NULL,
    `childcare_needs_notes`           text,
    `digital_access`                  varchar(50)                  DEFAULT NULL,
    `digital_access_notes`            text,
    `employment_status`               varchar(50)                  DEFAULT NULL,
    `education_level`                 varchar(50)                  DEFAULT NULL,
    `caregiver_status`                varchar(20)                  DEFAULT NULL,
    `veteran_status`                  varchar(20)                  DEFAULT NULL,
    `pregnancy_status`                varchar(20)                  DEFAULT NULL,
    `pregnancy_edd`                   date                         DEFAULT NULL,
    `pregnancy_gravida`               smallint(6)                  DEFAULT NULL,
    `pregnancy_para`                  smallint(6)                  DEFAULT NULL,
    `postpartum_status`               varchar(20)                  DEFAULT NULL,
    `postpartum_end`                  date                         DEFAULT NULL,
    `goals`                           text,
    `interventions`                   text,
    PRIMARY KEY (`id`),
    KEY `uuid_idx` (`uuid`),
    KEY `pid_idx` (`pid`),
    KEY `assessment_idx` (`assessment_date`),
    KEY `encounter_idx` (`encounter`)
) ENGINE = InnoDB;
#EndIf
-- -------------------------------------------------------------------------------------------------------------------------------------------------------
-- Social History SDOHValuesets

#IfNotRow list_options option_id sdoh_food_insecurity_risk
INSERT INTO list_options (list_id, option_id, title, seq)
VALUES ('lists', 'sdoh_food_insecurity_risk', 'SDOH – Food Insecurity (Risk)', 0),
       ('lists', 'sdoh_housing_worry', 'SDOH – Housing Worry (Y/N)', 0),
       ('lists', 'sdoh_housing_worry_freq', 'SDOH – Housing Worry (Freq)', 0),
       ('lists', 'sdoh_transportation_barrier', 'SDOH – Transportation Barrier', 0),
       ('lists', 'sdoh_utilities_shutoff', 'SDOH – Utilities Shutoff Risk', 0),
       ('lists', 'sdoh_ipv_yesno', 'SDOH – Interpersonal Safety (Y/N)', 0),
       ('lists', 'sdoh_financial_strain', 'SDOH – Financial Strain', 0),
       ('lists', 'sdoh_social_isolation_freq', 'SDOH – Social Connection (Freq)', 0),
       ('lists', 'sdoh_childcare_needs', 'SDOH – Childcare Needs (Y/N)', 0),
       ('lists', 'sdoh_digital_access', 'SDOH – Digital Access (Y/N)', 0),
       ('lists', 'sdoh_employment_status', 'SDOH – Employment Status', 0),
       ('lists', 'sdoh_education_level', 'SDOH – Education Level', 0),
       ('lists', 'pregnancy_status', 'Pregnancy Status', 0),
       ('lists', 'postpartum_status', 'Postpartum Status', 0),
       ('lists', 'sdoh_instruments', 'SDOH – Screening Instruments', 0);

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_food_insecurity_risk', 'at_risk', 'At risk', 10, 'LOINC:LA19952-3', 'Question LOINC 88124-3'),
       ('sdoh_food_insecurity_risk', 'no_risk', 'No risk', 20, 'LOINC:LA19983-8', 'Question LOINC 88124-3'),
       ('sdoh_food_insecurity_risk', 'declined', 'Declined', 90, 'LOINC:LA30122-8', 'Question LOINC 88124-3');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_housing_worry', 'yes', 'Yes', 10, 'LOINC:LA33-6', 'Question LOINC 93033-9'),
       ('sdoh_housing_worry', 'no', 'No', 20, 'LOINC:LA32-8', 'Question LOINC 93033-9'),
       ('sdoh_housing_worry', 'declined', 'Declined', 90, 'LOINC:LA30122-8', 'Question LOINC 93033-9');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_housing_worry_freq', 'never', 'Never', 10, 'LOINC:LA26683-5', 'Question LOINC 104561-6'),
       ('sdoh_housing_worry_freq', 'rarely', 'Rarely', 20, 'LOINC:LA30109-6', 'Question LOINC 104561-6'),
       ('sdoh_housing_worry_freq', 'sometimes', 'Sometimes', 30, 'LOINC:LA30110-4', 'Question LOINC 104561-6'),
       ('sdoh_housing_worry_freq', 'often', 'Often', 40, 'LOINC:LA30111-2', 'Question LOINC 104561-6'),
       ('sdoh_housing_worry_freq', 'always', 'Always', 50, 'LOINC:LA30112-0', 'Question LOINC 104561-6'),
       ('sdoh_housing_worry_freq', 'declined', 'Declined', 90, 'LOINC:LA30122-8', 'Question LOINC 104561-6');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_transportation_barrier', 'yes_med', 'Yes – medical', 10, 'LOINC:LA30133-5', 'Question LOINC 93030-5'),
       ('sdoh_transportation_barrier', 'yes_nonmed', 'Yes – non-medical', 20, 'LOINC:LA30134-3', 'Question LOINC 93030-5'),
       ('sdoh_transportation_barrier', 'no', 'No', 30, 'LOINC:LA32-8', 'Question LOINC 93030-5'),
       ('sdoh_transportation_barrier', 'declined', 'Declined', 90, 'LOINC:LA30122-8', 'Question LOINC 93030-5'),
       ('sdoh_transportation_barrier', 'unable', 'Unable to respond', 95, 'LOINC:LA33608-3', 'Question LOINC 93030-5');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_utilities_shutoff', 'yes', 'Yes', 10, 'LOINC:LA33-6', 'Question LOINC 96779-4'),
       ('sdoh_utilities_shutoff', 'no', 'No', 20, 'LOINC:LA32-8', 'Question LOINC 96779-4'),
       ('sdoh_utilities_shutoff', 'already_off', 'Already shut off', 30, 'LOINC:LA32002-0', 'Question LOINC 96779-4'),
       ('sdoh_utilities_shutoff', 'declined', 'Declined', 90, 'LOINC:LA30122-8', 'Question LOINC 96779-4');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_ipv_yesno', 'yes', 'Yes', 10, 'LOINC:LA33-6', 'Use with HARK items'),
       ('sdoh_ipv_yesno', 'no', 'No', 20, 'LOINC:LA32-8', 'Use with HARK items'),
       ('sdoh_ipv_yesno', 'declined', 'Declined', 90, 'LOINC:LA30122-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_financial_strain', 'very_hard', 'Very hard', 10, 'LOINC:LA15832-1', 'Question LOINC 76513-1'),
       ('sdoh_financial_strain', 'hard', 'Hard', 20, 'LOINC:LA14745-6', 'Question LOINC 76513-1'),
       ('sdoh_financial_strain', 'somewhat_hard', 'Somewhat hard', 30, 'LOINC:LA22683-9', 'Question LOINC 76513-1'),
       ('sdoh_financial_strain', 'not_very_hard', 'Not very hard', 40, 'LOINC:LA22682-1', 'Question LOINC 76513-1');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_social_isolation_freq', 'never', 'Never', 10, 'LOINC:LA26683-5', 'Pair with LOINC 93159-2'),
       ('sdoh_social_isolation_freq', 'rarely', 'Rarely', 20, 'LOINC:LA30109-6', ''),
       ('sdoh_social_isolation_freq', 'sometimes', 'Sometimes', 30, 'LOINC:LA30110-4', ''),
       ('sdoh_social_isolation_freq', 'often', 'Often', 40, 'LOINC:LA30111-2', ''),
       ('sdoh_social_isolation_freq', 'always', 'Always', 50, 'LOINC:LA30112-0', ''),
       ('sdoh_social_isolation_freq', 'declined', 'Declined', 90, 'LOINC:LA30122-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_childcare_needs', 'yes', 'Yes', 10, 'LOINC:LA33-6', ''),
       ('sdoh_childcare_needs', 'no', 'No', 20, 'LOINC:LA32-8', ''),
       ('sdoh_childcare_needs', 'declined', 'Declined', 90, 'LOINC:LA30122-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_digital_access', 'yes', 'Yes', 10, 'LOINC:LA33-6', 'e.g., access available'),
       ('sdoh_digital_access', 'no', 'No', 20, 'LOINC:LA32-8', 'e.g., access not available'),
       ('sdoh_digital_access', 'declined', 'Declined', 90, 'LOINC:LA30122-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_employment_status', 'unemployed', 'Unemployed', 10, 'LOINC:LA17956-6', 'PRAPARE/LOINC 67875-5 family'),
       ('sdoh_employment_status', 'part_time', 'Part-time / temporary', 20, 'LOINC:LA30138-4', ''),
       ('sdoh_employment_status', 'full_time', 'Full-time', 30, 'LOINC:LA30136-8', ''),
       ('sdoh_employment_status', 'otherwise_unemployed', 'Otherwise unemployed (student/retired/disabled/caregiver)', 40, 'LOINC:LA30137-6', ''),
       ('sdoh_employment_status', 'declined', 'Declined', 90, 'LOINC:LA30122-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_education_level', 'less_than_hs', '< High school', 5, 'LOINC:LA15606-9', 'Question LOINC 63504-5'),
       ('sdoh_education_level', 'hs_grad', 'High school graduate', 10, 'LOINC:LA15564-0', ''),
       ('sdoh_education_level', 'ged', 'GED or equivalent', 20, 'LOINC:LA15619-2', ''),
       ('sdoh_education_level', 'some_college', 'Some college, no degree', 30, 'LOINC:LA15620-0', ''),
       ('sdoh_education_level', 'assoc', 'Associate degree', 40, 'LOINC:LA15621-8', ''),
       ('sdoh_education_level', 'bachelor', 'Bachelor’s degree', 50, 'LOINC:LA12460-4', ''),
       ('sdoh_education_level', 'master', 'Master’s degree', 60, 'LOINC:LA12461-2', ''),
       ('sdoh_education_level', 'professional', 'Professional school degree', 70, 'LOINC:LA15625-9', ''),
       ('sdoh_education_level', 'doctorate', 'Doctoral degree', 80, 'LOINC:LA15626-7', ''),
       ('sdoh_education_level', 'declined', 'Declined', 90, 'LOINC:LA4389-8', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('pregnancy_status', 'pregnant', 'Pregnant', 10, 'SNOMED-CT:77386006', ''),
       ('pregnancy_status', 'not_pregnant', 'Not pregnant', 20, 'SNOMED-CT:60001007', ''),
       ('pregnancy_status', 'possible', 'Possible pregnancy', 30, 'SNOMED-CT:146799005', ''),
       ('pregnancy_status', 'unconfirmed', 'Pregnancy not yet confirmed', 40, 'SNOMED-CT:152231000119106', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('postpartum_status', 'postpartum', 'Postpartum (≤6 weeks)', 10, 'SNOMED-CT:10152009', '');

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('sdoh_instruments', 'hunger_vital_sign', 'Hunger Vital Sign (2-item)', 10, 'LOINC:88121-9', 'Includes items 88122-7, 88123-5; risk 88124-3'),
       ('sdoh_instruments', 'ahc_hrsn_core', 'AHC HRSN – Core', 20, 'LOINC:96777-8', ''),
       ('sdoh_instruments', 'ahc_hrsn_supp', 'AHC HRSN – Supplemental', 30, 'LOINC:97023-6', 'Financial strain 76513-1; loneliness 93159-2'),
       ('sdoh_instruments', 'prapare', 'PRAPARE', 40, 'LOINC:93025-5', ''),
       ('sdoh_instruments', 'ipv_hark', 'Intimate Partner Violence – HARK', 50, 'LOINC:76499-3', '');
#EndIf

-- Observation Form Changes

-- Fix the issue that we don't have a primary key on the form_observation table
#IfMissingColumn form_observation form_id
ALTER TABLE `form_observation` CHANGE COLUMN `id` `form_id` BIGINT(20) NOT NULL;
#EndIf

#IfMissingColumn form_observation id
ALTER TABLE `form_observation` ADD COLUMN `id` BIGINT(20) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`);
#EndIf

#IfMissingColumn form_observation parent_observation_id
ALTER TABLE `form_observation` ADD `parent_observation_id` BIGINT(20) DEFAULT NULL  COMMENT 'FK to parent observation for sub-observations';
#EndIf

#IfMissingColumn form_observation category
ALTER TABLE `form_observation`
    ADD `category` VARCHAR(64) DEFAULT NULL COMMENT 'FK to list_options.option_id for observation category (SDOH, Functional, Cognitive, Physical, etc)';
#EndIf

#IfMissingColumn form_observation questionnaire_response_id
ALTER TABLE `form_observation` ADD `questionnaire_response_id` BIGINT(21) DEFAULT NULL COMMENT 'FK to questionnaire_response table';
#EndIf

#IfNotIndex form_observation idx_parent_observation
ALTER TABLE `form_observation` ADD INDEX `idx_parent_observation` (`parent_observation_id`);
#EndIf

#IfNotIndex form_observation idx_category
ALTER TABLE `form_observation` ADD INDEX `idx_category` (`category`);
#EndIf

#IfNotIndex form_observation idx_questionnaire_response
ALTER TABLE `form_observation` ADD INDEX `idx_questionnaire_response` (`questionnaire_response_id`);
#EndIf

#IfNotIndex form_observation idx_form_id
ALTER TABLE `form_observation` ADD INDEX `idx_form_id` (`form_id`);
#EndIf

#IfNotIndex form_observation idx_pid_encounter
ALTER TABLE `form_observation` ADD INDEX `idx_pid_encounter` (`pid`, `encounter`);
#EndIf

#IfNotIndex form_observation idx_date
ALTER TABLE `form_observation` ADD INDEX `idx_date` (`date`);
#EndIf

#IfNotRow2D list_options list_id Observation_Types option_id sdoh
-- assessment, procedure_diagnostic, physical_exam_performed exist, so only adding missing ones
-- procedure_diagnostic, physical_exam_performed exist but are deprecated
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','activity','Activity',5,1,'Observations that measure or record any bodily activity that enhances or maintains physical fitness and overall health and wellness. Not under direct supervision of practitioner such as a physical therapist. (e.g., laps swum, steps, sleep data)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','care-experience-preference','Care Experience Preference',20,1,'Personal thoughts about something a person feels is relevant to their care experience and may be pertinent when planning their care.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','cognitive-status','Cognitive Status',30,1,'Cognitive Status category');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','disability-status','Disability Status',40,1,'Disability Status category');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','exam','Exam',50,1,'Observations generated by physical exam findings including direct observations made by a clinician and use of simple instruments and the result of simple maneuvers performed directly on the patient\'s body.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','functional-status','Functional Status',60,1,'Functional Status category');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','imaging','Imaging',70,1,'Observations generated by imaging. The scope includes observations regarding plain x-ray, ultrasound, CT, MRI, angiography, echocardiography, and nuclear medicine.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','laboratory','Laboratory',80,1,'The results of observations generated by laboratories. Laboratory results are typically generated by laboratories providing analytic services in areas such as chemistry, hematology, serology, histology, cytology, anatomic pathology (including digital pathology), microbiology, and/or virology. These observations are based on analysis of specimens obtained from the patient and submitted to the laboratory.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','observation-adi-documentation','Observation ADI Documentation',90,1,'Statement of presence and properties of patient or provider authored documents that record a patient\'s goals, preferences and priorities should a patient be unable to communicate them to a provider.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','procedure','Procedure',100,1,'Observations generated by other procedures. This category includes observations resulting from interventional and non-interventional procedures excluding laboratory and imaging (e.g., cardiology catheterization, endoscopy, electrodiagnostics, etc.). Procedure results are typically generated by a clinician to provide more granular information about component observations made during a procedure. An example would be when a gastroenterologist reports the size of a polyp observed during a colonoscopy.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','sdoh','Social Determinants of Health (SDOH)',110,1,'Social, economic, and environmental factors affecting health');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','social-history','Social History',120,1,'Social History Observations define the patient''s occupational, personal (e.g., lifestyle), social, familial, and environmental history and health risk factors that may impact the patient''s health.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','survey','Survey',130,1,'Assessment tool/survey instrument observations (e.g., Apgar Scores, Montreal Cognitive Assessment (MoCA)).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','therapy','Therapy',140,1,'Observations generated by non-interventional treatment protocols (e.g. occupational, physical, radiation, nutritional and medication therapy)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','treatment-intervention-preference','Treatment Intervention Preference',150,1,'A personal preference for a type of medical intervention (treatment) request under certain conditions.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('Observation_Types','vital-signs','Vital Signs',160,1,'Clinical observations measure the body''s basic functions such as blood pressure, heart rate, respiratory rate, height, weight, body mass index, head circumference, pulse oximetry, temperature, and body surface area.');
#EndIf

-- now we hide the old values
#IfNotRow3D list_options list_id Observation_Types option_id physical_exam_performed activity 0
UPDATE `list_options` SET `activity`=0 WHERE `list_id`='Observation_Types' AND `option_id`='physical_exam_performed';
#EndIf

#IfNotRow3D list_options list_id Observation_Types option_id procedure_diagnostic activity 0
UPDATE `list_options` SET `activity`=0 WHERE `list_id`='Observation_Types' AND `option_id`='procedure_diagnostic';
#EndIf

-- Add list options of observation-status codes
#IfNotRow list_options list_id observation-status
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('lists','observation-status','Observation Status Codes',0,1,0, 'Codes representing the status of an observation from http://hl7.org/fhir/ValueSet/observation-status');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','registered','Registered',10,1,'The existence of the observation is registered, but there is no result yet available');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','preliminary','Preliminary',20,1,'This is an initial or interim observation: data may be incomplete or unverified');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','final','Final',30,1,'The observation is complete and there are no further actions needed. Additional information such "released", "signed", etc would be represented using [Provenance](provenance.html) which provides not only the act but also the actors and dates and other related data. These act states would be associated with an observation status of `preliminary` until they are all completed and then a status of `final` would be applied.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','amended','Amended',40,1,'Subsequent to being Final, the observation has been modified subsequent. This includes updates/new information and corrections.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','corrected','Corrected',50,1,'Subsequent to being Final, the observation has been modified to correct an error in the test result.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','cancelled','Cancelled',60,1,'The observation is unavailable because the measurement was not started or not completed (also sometimes called "aborted").');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','entered-in-error','Entered in Error',70,1,'The observation has been withdrawn following previous final release. This electronic record should never have existed, though it is possible that real-world decisions were based on it. (If real-world activity has occurred, the status should be "cancelled" rather than "entered-in-error".).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('observation-status','unknown','Unknown',80,1,'The authoring/source system does not know which of the status values currently applies for this observation. Note: This concept is not to be used for "other" - one of the listed statuses is presumed to apply, but the authoring/source system does not know which.');
#EndIf

-- Add uuid for form_observation if missing
#IfMissingColumn form_observation uuid
ALTER TABLE `form_observation` ADD `uuid` binary(16) DEFAULT NULL COMMENT 'UUID for the observation, used as unique logical identifier';
#EndIf

#IfEyeFormLaserCategoriesNeeded
#EndIf

-- =========================
-- DEM Layout: Tribal Affiliations
-- Resequence group by order of 10
-- =========================
#IfNotRow2D layout_options form_id DEM field_id tribal_affiliations
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='religion' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='religion' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES ('DEM','tribal_affiliations',@group_id,'Tribal Affiliations',@seq_add_to+10,1,1,0,0,'tribal_affiliations',1,1,'','','Tribal Affiliations entries',0,'','F','','','');
ALTER TABLE `patient_data` ADD `tribal_affiliations` TEXT;
#Endif

-- =========================
-- form_history_sdoh: grouped adds
-- =========================
#IfMissingColumn form_history_sdoh instrument_score
ALTER TABLE form_history_sdoh
    ADD COLUMN instrument_score INT NULL,
    ADD COLUMN positive_domain_count INT NULL,
    ADD COLUMN declined_flag TINYINT(1) NULL,
    ADD COLUMN disability_status VARCHAR(50) NULL,
    ADD COLUMN disability_status_notes TEXT,
    ADD COLUMN disability_scale TEXT,
    ADD COLUMN hunger_q1 VARCHAR(50) DEFAULT NULL COMMENT 'LOINC 88122-7 response',
    ADD COLUMN hunger_q2 VARCHAR(50) DEFAULT NULL COMMENT 'LOINC 88123-5 response',
    ADD COLUMN hunger_score INT DEFAULT NULL COMMENT 'Calculated HVS score';
#EndIf
-- =========================
-- Lists & Options (grouped; parent first, then options)
-- =========================

#IfNotRow2D list_options list_id lists option_id vital_signs_answers
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','vital_signs_answers','Vital Signs Answers',0,0,0,'',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('vital_signs_answers','LA28397-0','Often true',10,'LOINC:LA28397-0',1),
       ('vital_signs_answers','LA28398-8','Sometimes true',20,'LOINC:LA28398-8',1),
       ('vital_signs_answers','LA28399-6','Never true',30,'LOINC:LA28399-6',1);
#EndIf

#IfNotRow2D list_options list_id lists option_id disability_status
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','disability_status','Disability Status',0,1,0,'',1)
ON DUPLICATE KEY UPDATE title=VALUES(title), notes=VALUES(notes);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('disability_status','im_safe','I''m Safe.',10,'LOINC:LA29242-7',1),
       ('disability_status','im_vulnerable','I''m Vulnerable.',20,'LOINC:LA29243-5',1),
       ('disability_status','im_at_risk','I''m at risk.',30,'LOINC:LA29244-3',1),
       ('disability_status','im_in_crisis','I''m in crisis.',40,'LOINC:LA29245-0',1);
#EndIf

#IfNotRow2D list_options list_id lists option_id sdoh_problems
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','sdoh_problems','SDOH Problems/Health Concerns',0,0,0,'USCDI v3 SDOH - Gravity Project',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('sdoh_problems','160903007','Lives alone',10,'SNOMED:160903007',1),
       ('sdoh_problems','224130005','Difficulty accessing healthcare',20,'SNOMED:224130005',1),
       ('sdoh_problems','182964004','Medication not available',30,'SNOMED:182964004',1),
       ('sdoh_problems','73438004','Educational problem',40,'SNOMED:73438004',1),
       ('sdoh_problems','266948004','Unemployed',50,'SNOMED:266948004',1),
       ('sdoh_problems','Z59.1','Inadequate housing',60,'ICD10CM:Z59.1',1),
       ('sdoh_problems','Z59.4','Lack of adequate food',70,'ICD10CM:Z59.4',1),
       ('sdoh_problems','Z59.6','Low income',80,'ICD10CM:Z59.6',1),
       ('sdoh_problems','Z62.9','Problem related to upbringing',90,'ICD10CM:Z62.9',1),
       ('sdoh_problems','266944006','Lives in poverty',100,'SNOMED:266944006',1);
#EndIf

#IfNotRow2D list_options list_id lists option_id sdoh_interventions
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','sdoh_interventions','SDOH Interventions',0,0,0,'USCDI v3 SDOH Interventions - Gravity Project',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('sdoh_interventions','467681000124101','Referral to food assistance program',10,'SNOMED:467681000124101',1),
       ('sdoh_interventions','assist_food_program','Assistance with application for food program',90,'SNOMED:467681000124101',1),
       ('sdoh_interventions','467711000124100','Referral to housing assistance program',20,'SNOMED:467711000124100',1),
       ('sdoh_interventions','467721000124107','Referral to transportation assistance program',30,'SNOMED:467721000124107',1),
       ('sdoh_interventions','467731000124109','Referral to utility assistance program',40,'SNOMED:467731000124109',1),
       ('sdoh_interventions','428191000124101','Education about community resources',50,'SNOMED:428191000124101',1),
       ('sdoh_interventions','464031000124108','Referral to social worker',60,'SNOMED:464031000124108',1),
       ('sdoh_interventions','385763009','Lifestyle education',70,'SNOMED:385763009',1),
       ('sdoh_interventions','467741000124103','Referral to financial assistance program',80,'SNOMED:467741000124103',1),
       ('sdoh_interventions','467701000124103','Assistance with application for housing program',100,'SNOMED:467701000124103',1),
       ('sdoh_interventions','assist_transport','Assistance with transportation',110,'SNOMED:467721000124107',1);
#EndIf

#IfNotRow2D list_options list_id lists option_id tribal_affiliations
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','tribal_affiliations','Tribal Affiliation',0,0,0,'USCDI v3 Required - HL7 TribalEntityUS',1);

INSERT INTO list_options (list_id, option_id, title, seq, notes, activity)
VALUES ('tribal_affiliations','coquille','Coquille Indian Tribe',10,'65',1),
       ('tribal_affiliations','cherokee_nation','Cherokee Nation (OK)',20,'40',1),
       ('tribal_affiliations','chickasaw_nation','Chickasaw Nation (OK)',30,'43',1),
       ('tribal_affiliations','choctaw_nation','Choctaw Nation of Oklahoma',40,'47',1),
       ('tribal_affiliations','gila_river','Gila River Indian Community (AZ)',50,'93',1),
       ('tribal_affiliations','hopi','Hopi Tribe (AZ)',60,'104',1),
       ('tribal_affiliations','navajo_nation','Navajo Nation (AZ/NM/UT)',70,'170',1),
       ('tribal_affiliations','standing_rock','Standing Rock Sioux Tribe (ND/SD)',80,'289',1),
       ('tribal_affiliations','tohono_oodham','Tohono O''odham Nation (AZ)',90,'302',1),
       ('tribal_affiliations','white_mountain_apache','White Mountain Apache Tribe (AZ)',100,'325',1),
       ('tribal_affiliations','zuni','Zuni Tribe (NM)',110,'337',1),
       ('tribal_affiliations','other_specify','Other (specify)',120,'000',1);
#EndIf

#IfNotRow2D list_options list_id IndustryODH option_id 541110
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','IndustryODH','ODH Industry',0,0,0,'NAICS-based industry codes from ODH',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('IndustryODH','541110','Offices of Lawyers',10,'541110.008099',1),
       ('IndustryODH','541330','Engineering Services',20,'541330.008117',1),
       ('IndustryODH','236220','Commercial and Institutional Building Construction',30,'236220.004781',1),
       ('IndustryODH','622110','General Medical and Surgical Hospitals',40,'622110.009243',1),
       ('IndustryODH','611110','Elementary and Secondary Schools',50,'611110.008684',1),
       ('IndustryODH','561720','Janitorial Services',60,'561720.002294',1),
       ('IndustryODH','722511','Full-Service Restaurants',70,'722511.010339',1),
       ('IndustryODH','445110','Supermarkets and Other Grocery Stores',80,'445110.006564',1),
       ('IndustryODH','238210','Electrical Contractors',90,'238210.004871',1),
       ('IndustryODH','621111','Offices of Physicians (except Mental Health)',100,'621111.009165',1),
       ('IndustryODH','531110','Lessors of Residential Buildings',110,'531110.007615',1),
       ('IndustryODH','484121','General Freight Trucking, Long-Distance',120,'484121.007193',1),
       ('IndustryODH','812111','Barber Shops',130,'812111.011099',1),
       ('IndustryODH','522110','Commercial Banking',140,'522110.007773',1),
       ('IndustryODH','999999','Unemployed',150,'999999',1),
       ('IndustryODH','UNKNOWN','Unknown',160,'UNKNOWN',1);
#EndIf

#IfNotRow2D list_options list_id OccupationODH option_id 23-1011.00
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','OccupationODH','ODH Occupation',0,0,0,'O*NET-SOC based occupation codes from ODH',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, activity)
VALUES ('OccupationODH','23-1011.00','Lawyers',10,'23-1011.00.031000',1),
       ('OccupationODH','17-2051.00','Civil Engineers',20,'17-2051.00.019051',1),
       ('OccupationODH','47-2061.00','Construction Laborers',30,'47-2061.00.051621',1),
       ('OccupationODH','29-1141.00','Registered Nurses',40,'29-1141.00.038232',1),
       ('OccupationODH','25-2021.00','Elementary School Teachers',50,'25-2021.00.032102',1),
       ('OccupationODH','37-2011.00','Janitors and Cleaners',60,'37-2011.00.028742',1),
       ('OccupationODH','35-3031.00','Waiters and Waitresses',70,'35-3031.00.045251',1),
       ('OccupationODH','41-2011.00','Cashiers',80,'41-2011.00.047211',1),
       ('OccupationODH','11-1021.00','General and Operations Managers',90,'11-1021.00.003891',1),
       ('OccupationODH','43-9061.00','Office Clerks, General',100,'43-9061.00.049705',1),
       ('OccupationODH','53-3032.00','Heavy and Tractor-Trailer Truck Drivers',110,'53-3032.00.057651',1),
       ('OccupationODH','29-1211.00','Physician Assistants',120,'29-1211.00.038302',1),
       ('OccupationODH','39-5012.00','Hairdressers, Hairstylists, and Cosmetologists',130,'39-5012.00.046262',1),
       ('OccupationODH','13-2011.00','Accountants and Auditors',140,'13-2011.00.010350',1),
       ('OccupationODH','15-1252.00','Software Developers',150,'15-1252.00.016221',1),
       ('OccupationODH','33-9032.00','Security Guards',160,'33-9032.00.042562',1),
       ('OccupationODH','49-9071.00','Maintenance and Repair Workers, General',170,'49-9071.00.053722',1),
       ('OccupationODH','31-1120.00','Home Health Aides',180,'31-1120.00.039792',1),
       ('OccupationODH','25-9045.00','Teaching Assistants',190,'25-9045.00.032175',1),
       ('OccupationODH','21-1093.00','Social Workers',200,'21-1093.00.027030',1),
       ('OccupationODH','999999','Unemployed',210,'999999',1),
       ('OccupationODH','UNKNOWN','Unknown',220,'UNKNOWN',1);
#EndIf
-- =========================
-- Care Plan (form_care_plan table, status list, etc)
-- =========================
#IfMissingColumn form_care_plan plan_status
ALTER TABLE `form_care_plan` ADD COLUMN `plan_status` VARCHAR(32) DEFAULT NULL COMMENT 'Care Plan status (e.g., draft, active, completed, etc)';
#EndIf
-- Add proposed_date to care plan
#IfMissingColumn form_care_plan proposed_date
ALTER TABLE `form_care_plan` ADD COLUMN `proposed_date` DATETIME NULL COMMENT 'Target or Achieve-by date for the goal';
#EndIf

#IfNotIndex form_care_plan idx_status_date
ALTER TABLE `form_care_plan` ADD INDEX `idx_status_date` (`plan_status`, `date`, `date_end`);
#EndIf

-- Care plan status list aligned to FHIR R4 CarePlan.status (titles are user-facing)
#IfNotRow2D list_options list_id lists option_id care_plan_status
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'care_plan_status', 'Care Plan Status', 0);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id draft
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','draft','Draft',10);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id active
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','active','Active',20);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id on-hold
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','on-hold','On hold',30);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id revoked
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','revoked','Revoked',40);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id completed
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','completed','Completed',50);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id entered-in-error
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','entered-in-error','Entered in error',60);
#EndIf
#IfNotRow2D list_options list_id care_plan_status option_id unknown
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`) VALUES ('care_plan_status','unknown','Unknown',70);
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2025-10-01 load_filename icd10orderfiles.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2025-10-01', 'icd10orderfiles.zip', '781ce6e72697181f1ef0d4230921e902');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2025-10-01', 'zip-file-3-2026-icd-10-pcs-codes-file.zip', '86a5fb7a3269bea68b74565152e4b849');
#EndIf

#IfMissingColumn questionnaire_repository category
ALTER TABLE `questionnaire_repository` ADD COLUMN `category` VARCHAR(64) DEFAULT NULL;
#EndIf

-- observation values can be codes as well so we need to populate a description field
#IfMissingColumn form_observation ob_value_code_description
ALTER TABLE `form_observation` ADD COLUMN `ob_value_code_description` VARCHAR(255) DEFAULT NULL;
#EndIf

#IfNotRow list_options list_id pregnancy_intent
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','pregnancy_intent','Pregnancy Intent Over Next Year',0,0,0,'Codeset from valueset http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1166.22',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('pregnancy_intent', 'not_sure', 'Not sure of desire to become pregnant (finding)', 10, 'SNOMED-CT:454381000124105', ''),
       ('pregnancy_intent', 'ambivalent', 'Ambivalent about becoming pregnant (finding)', 20, 'SNOMED-CT:454391000124108', ''),
       ('pregnancy_intent', 'no_desire', 'No desire to become pregnant (finding)', 30, 'SNOMED-CT:454391000124108', ''),
       ('pregnancy_intent', 'wants_pregnancy', 'Wants to become pregnant (finding)', 40, 'SNOMED-CT:454411000124108', '');
#EndIf

#IfMissingColumn form_history_sdoh pregnancy_intent
ALTER TABLE `form_history_sdoh` ADD COLUMN `pregnancy_intent` VARCHAR(32) DEFAULT NULL COMMENT 'Pregnancy Intent Over Next Year (codes from PregnancyIntent list)';
#EndIf

#IfNotRow2D list_options list_id personal_relationship option_id FTH
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','FTH','Father','FTH','76');
#EndIf

UPDATE `layout_options` SET `list_id` = 'OccupationODH', `list_backup_id` = 'Occupation' WHERE `form_id` = 'DEM' AND `field_id` = 'occupation' AND `group_id` = '4';
UPDATE `layout_options` SET `list_id` = 'IndustryODH', `list_backup_id` = 'Industry' WHERE `form_id` = 'DEM' AND `field_id` = 'industry' AND `group_id` = '4';
-- ------------------------------------------- 8-1-25 ------------ sjp ------------------------------------------------------------------

#IfNotRow2D list_options list_id lists option_id specimen_type
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
    ('lists', 'specimen_type', 'Specimen Type', 1, 0, 0, '', 'FHIR Specimen.type - SNOMED CT preferred');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
-- Blood specimens
('specimen_type', '119297000', 'Blood specimen', 10, 1, 0, 'SNOMED-CT:119297000', 'Whole blood'),
('specimen_type', '122555007', 'Venous blood specimen', 15, 0, 0, 'SNOMED-CT:122555007', 'Venous blood'),
('specimen_type', '122554006', 'Capillary blood specimen', 20, 0, 0, 'SNOMED-CT:122554006', 'Capillary blood'),
('specimen_type', '119364003', 'Serum specimen', 30, 0, 0, 'SNOMED-CT:119364003', 'Blood serum'),
('specimen_type', '119361006', 'Plasma specimen', 40, 0, 0, 'SNOMED-CT:119361006', 'Blood plasma'),
('specimen_type', '122556008', 'Cord blood specimen', 50, 0, 0, 'SNOMED-CT:122556008', 'Umbilical cord blood'),
('specimen_type', '122560006', 'Arterial blood specimen', 60, 0, 0, 'SNOMED-CT:122560006', 'Arterial blood'),
('specimen_type', '258580003', 'Whole blood specimen', 65, 0, 0, 'SNOMED-CT:258580003', 'Whole blood for testing'),
-- Urine specimens
('specimen_type', '122575003', 'Urine specimen', 70, 0, 0, 'SNOMED-CT:122575003', 'Urine'),
('specimen_type', '278020009', 'Spot urine specimen', 75, 0, 0, 'SNOMED-CT:278020009', 'Random/spot urine'),
('specimen_type', '258574006', 'Midstream urine specimen', 80, 0, 0, 'SNOMED-CT:258574006', 'Midstream catch'),
('specimen_type', '258566001', '24-hour urine specimen', 85, 0, 0, 'SNOMED-CT:258566001', '24-hour collection'),
-- Tissue and biopsy
('specimen_type', '119376003', 'Tissue specimen', 90, 0, 0, 'SNOMED-CT:119376003', 'Tissue'),
('specimen_type', '119327009', 'Tissue specimen from skin', 95, 0, 0, 'SNOMED-CT:119327009', 'Skin biopsy'),
('specimen_type', '430268003', 'Bone specimen', 100, 0, 0, 'SNOMED-CT:430268003', 'Bone tissue'),
('specimen_type', '258415003', 'Biopsy specimen', 105, 0, 0, 'SNOMED-CT:258415003', 'Biopsy'),
-- Body fluids
('specimen_type', '309051001', 'Body fluid specimen', 110, 0, 0, 'SNOMED-CT:309051001', 'Body fluid'),
('specimen_type', '258450006', 'Cerebrospinal fluid specimen', 120, 0, 0, 'SNOMED-CT:258450006', 'CSF'),
('specimen_type', '119378004', 'Amniotic fluid specimen', 125, 0, 0, 'SNOMED-CT:119378004', 'Amniotic fluid'),
('specimen_type', '258459008', 'Gastric fluid specimen', 130, 0, 0, 'SNOMED-CT:258459008', 'Gastric aspirate'),
('specimen_type', '258442002', 'Bile specimen', 135, 0, 0, 'SNOMED-CT:258442002', 'Bile fluid'),
('specimen_type', '258498002', 'Synovial fluid specimen', 140, 0, 0, 'SNOMED-CT:258498002', 'Joint fluid'),
('specimen_type', '119323008', 'Pus specimen', 145, 0, 0, 'SNOMED-CT:119323008', 'Pus/purulent drainage'),
-- Respiratory specimens
('specimen_type', '119334006', 'Sputum specimen', 150, 0, 0, 'SNOMED-CT:119334006', 'Sputum'),
('specimen_type', '258603007', 'Respiratory tract specimen', 155, 0, 0, 'SNOMED-CT:258603007', 'Respiratory specimen'),
('specimen_type', '258500001', 'Nasopharyngeal swab', 160, 0, 0, 'SNOMED-CT:258500001', 'NP swab'),
('specimen_type', '472901003', 'Swab from nasal sinus', 165, 0, 0, 'SNOMED-CT:472901003', 'Nasal swab'),
('specimen_type', '258529004', 'Throat swab', 170, 0, 0, 'SNOMED-CT:258529004', 'Throat culture swab'),
('specimen_type', '258607008', 'Bronchoalveolar lavage fluid specimen', 175, 0, 0, 'SNOMED-CT:258607008', 'BAL fluid'),
-- Gastrointestinal
('specimen_type', '119339001', 'Stool specimen', 180, 0, 0, 'SNOMED-CT:119339001', 'Fecal specimen'),
('specimen_type', '119342007', 'Saliva specimen', 190, 0, 0, 'SNOMED-CT:119342007', 'Saliva'),
('specimen_type', '258455001', 'Gastric aspirate specimen', 195, 0, 0, 'SNOMED-CT:258455001', 'Gastric contents'),
-- Swabs and aspirates
('specimen_type', '119295008', 'Aspirate', 200, 0, 0, 'SNOMED-CT:119295008', 'Specimen obtained by aspiration'),
-- Genital/reproductive
('specimen_type', '119396002', 'Specimen from vagina', 210, 0, 0, 'SNOMED-CT:119396002', 'Vaginal specimen'),
('specimen_type', '119397006', 'Specimen from cervix', 215, 0, 0, 'SNOMED-CT:119397006', 'Cervical specimen'),
('specimen_type', '119393003', 'Specimen from urethra', 220, 0, 0, 'SNOMED-CT:119393003', 'Urethral specimen'),
('specimen_type', '119395003', 'Semen specimen', 225, 0, 0, 'SNOMED-CT:119395003', 'Seminal fluid'),
-- Other
('specimen_type', '119303007', 'Microbial isolate specimen', 230, 0, 0, 'SNOMED-CT:119303007', 'Microbial culture');
#EndIf

#IfNotRow2D list_options list_id lists option_id specimen_location
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
    ('lists', 'specimen_location', 'Specimen Collection Site', 1, 0, 0, '', 'FHIR Specimen.collection.bodySite - SNOMED CT required');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
-- Upper extremity venipuncture sites
('specimen_location', '368208006', 'Left upper arm structure', 10, 0, 0, 'SNOMED-CT:368208006', 'Left arm'),
('specimen_location', '368209003', 'Right upper arm structure', 20, 1, 0, 'SNOMED-CT:368209003', 'Right arm'),
('specimen_location', '16671000205103', 'Structure of left antecubital fossa', 30, 0, 0, 'SNOMED-CT:16671000205103', 'Left AC fossa'),
('specimen_location', '16681000205101', 'Structure of right antecubital fossa', 40, 0, 0, 'SNOMED-CT:16681000205101', 'Right AC fossa'),
('specimen_location', '368456002', 'Left forearm structure', 50, 0, 0, 'SNOMED-CT:368456002', 'Left forearm'),
('specimen_location', '368454004', 'Right forearm structure', 60, 0, 0, 'SNOMED-CT:368454004', 'Right forearm'),
('specimen_location', '85151006', 'Structure of left hand', 70, 0, 0, 'SNOMED-CT:85151006', 'Left hand'),
('specimen_location', '78791008', 'Structure of right hand', 80, 0, 0, 'SNOMED-CT:78791008', 'Right hand'),
('specimen_location', '762101005', 'Structure of left index finger', 85, 0, 0, 'SNOMED-CT:762101005', 'Left index finger'),
('specimen_location', '762106000', 'Structure of right index finger', 87, 0, 0, 'SNOMED-CT:762106000', 'Right index finger'),
('specimen_location', '7569003', 'Finger structure', 90, 0, 0, 'SNOMED-CT:7569003', 'Finger (unspecified)'),
-- Capillary collection sites
('specimen_location', '76853006', 'Structure of heel', 100, 0, 0, 'SNOMED-CT:76853006', 'Heel stick'),
('specimen_location', '29707007', 'Structure of toe', 105, 0, 0, 'SNOMED-CT:29707007', 'Toe'),
('specimen_location', '117590005', 'Structure of ear', 110, 0, 0, 'SNOMED-CT:117590005', 'Ear lobe'),
-- Respiratory collection sites
('specimen_location', '45206002', 'Nasal structure', 120, 0, 0, 'SNOMED-CT:45206002', 'Nose/nasal cavity'),
('specimen_location', '71836000', 'Nasopharyngeal structure', 130, 0, 0, 'SNOMED-CT:71836000', 'Nasopharynx'),
('specimen_location', '31389004', 'Oropharyngeal structure', 140, 0, 0, 'SNOMED-CT:31389004', 'Oropharynx'),
('specimen_location', '54066008', 'Pharyngeal structure', 150, 0, 0, 'SNOMED-CT:54066008', 'Throat/pharynx'),
('specimen_location', '44567001', 'Tracheal structure', 155, 0, 0, 'SNOMED-CT:44567001', 'Trachea'),
('specimen_location', '39607008', 'Lung structure', 160, 0, 0, 'SNOMED-CT:39607008', 'Lung'),
-- Genital/urinary sites
('specimen_location', '13648007', 'Urethral structure', 170, 0, 0, 'SNOMED-CT:13648007', 'Urethra'),
('specimen_location', '71252005', 'Cervix uteri structure', 180, 0, 0, 'SNOMED-CT:71252005', 'Cervix'),
('specimen_location', '76784001', 'Vaginal structure', 190, 0, 0, 'SNOMED-CT:76784001', 'Vagina'),
('specimen_location', '34402009', 'Rectum structure', 200, 0, 0, 'SNOMED-CT:34402009', 'Rectum'),
('specimen_location', '13024002', 'Male genital structure', 205, 0, 0, 'SNOMED-CT:13024002', 'Male genitalia'),
-- Wound/lesion sites
('specimen_location', '416462003', 'Wound', 210, 0, 0, 'SNOMED-CT:416462003', 'Wound site'),
('specimen_location', '125643001', 'Open wound', 215, 0, 0, 'SNOMED-CT:125643001', 'Open wound'),
('specimen_location', '39937001', 'Skin structure', 220, 0, 0, 'SNOMED-CT:39937001', 'Skin'),
('specimen_location', '128477000', 'Abscess', 225, 0, 0, 'SNOMED-CT:128477000', 'Abscess'),
-- Other anatomical sites
('specimen_location', '83419000', 'Spinal canal structure', 230, 0, 0, 'SNOMED-CT:83419000', 'CSF collection site'),
('specimen_location', '39352004', 'Joint structure', 240, 0, 0, 'SNOMED-CT:39352004', 'Joint (arthrocentesis)'),
('specimen_location', '38266002', 'Entire body', 250, 0, 0, 'SNOMED-CT:38266002', 'Body as a whole'),
('specimen_location', '113345001', 'Abdominal structure', 255, 0, 0, 'SNOMED-CT:113345001', 'Abdomen'),
('specimen_location', '69105007', 'Carotid artery structure', 260, 0, 0, 'SNOMED-CT:69105007', 'Carotid artery'),
('specimen_location', '51185008', 'Radial artery structure', 265, 0, 0, 'SNOMED-CT:51185008', 'Radial artery');
#EndIf

#IfNotRow2D list_options list_id lists option_id specimen_condition
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
    ('lists', 'specimen_condition', 'Specimen Condition', 1, 0, 0, '', 'FHIR uses HL7 v2 Table 0493 - specimen condition/state');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
    ('specimen_condition', 'AUT', 'Autolyzed', 10, 0, 0, 'HL7V20493:AUT', 'Autolyzed specimen'),
    ('specimen_condition', 'CLOT', 'Clotted', 20, 0, 0, 'HL7V20493:CLOT', 'Specimen clotted'),
    ('specimen_condition', 'CON', 'Contaminated', 30, 0, 0, 'HL7V20493:CON', 'Specimen contaminated'),
    ('specimen_condition', 'COOL', 'Cool', 40, 0, 0, 'HL7V20493:COOL', 'Cooled specimen'),
    ('specimen_condition', 'FROZ', 'Frozen', 50, 0, 0, 'HL7V20493:FROZ', 'Frozen specimen'),
    ('specimen_condition', 'HEM', 'Hemolyzed', 60, 0, 0, 'HL7V20493:HEM', 'Hemolyzed specimen'),
    ('specimen_condition', 'LIVE', 'Live', 70, 0, 0, 'HL7V20493:LIVE', 'Live organism'),
    ('specimen_condition', 'ROOM', 'Room temperature', 80, 0, 0, 'HL7V20493:ROOM', 'Room temperature'),
    ('specimen_condition', 'SNR', 'Sample not received', 90, 0, 0, 'HL7V20493:SNR', 'Not received by lab'),
    ('specimen_condition', 'THAW', 'Thawed', 100, 0, 0, 'HL7V20493:THAW', 'Thawed specimen'),
    ('specimen_condition', 'UNFZ', 'Unfrozen', 110, 0, 0, 'HL7V20493:UNFZ', 'Unfrozen specimen'),
    ('specimen_condition', 'WARM', 'Warm', 120, 0, 0, 'HL7V20493:WARM', 'Warmed specimen'),
    ('specimen_condition', 'WET', 'Wet', 130, 0, 0, 'HL7V20493:WET', 'Wet specimen'),
    ('specimen_condition', 'DRY', 'Dry', 140, 0, 0, 'HL7V20493:DRY', 'Dry specimen'),
    ('specimen_condition', 'OTHER', 'Other', 150, 0, 0, 'HL7V20493:OTHER', 'Other condition'),
    ('specimen_condition', 'acceptable', 'Acceptable', 160, 1, 0, '','Specimen is acceptable for testing'),
    ('specimen_condition', 'QNS', 'Quantity not sufficient', 170, 0, 0, 'LOCAL:QNS', 'Insufficient volume'),
    ('specimen_condition', 'HEMOLYZED', 'Hemolyzed', 180, 0, 0, 'LOCAL:HEM', 'Hemolysis detected'),
    ('specimen_condition', 'LIPEMIC', 'Lipemic', 190, 0, 0, 'LOCAL:LIP', 'Lipemia present'),
    ('specimen_condition', 'ICTERIC', 'Icteric', 200, 0, 0, 'LOCAL:ICT', 'Icterus/jaundice'),
    ('specimen_condition', 'EXPIRED', 'Specimen expired', 210, 0, 0, 'LOCAL:EXP', 'Past stability time'),
    ('specimen_condition', 'MISLABELED', 'Mislabeled', 220, 0, 0, 'LOCAL:MISLAB', 'Labeling error'),
    ('specimen_condition', 'UNLABELED', 'Unlabeled', 230, 0, 0, 'LOCAL:NOLAB', 'No label present'),
    ('specimen_condition', 'DAMAGED', 'Container damaged', 240, 0, 0, 'LOCAL:DAM', 'Container leak/break'),
    ('specimen_condition', 'WRONGTEMP', 'Improper storage temperature', 250, 0, 0, 'LOCAL:TEMP', 'Temperature abuse'),
    ('specimen_condition', 'WRONGTUBE', 'Wrong collection container', 260, 0, 0, 'LOCAL:TUBE', 'Incorrect tube type');
#EndIf

#IfNotRow2D list_options list_id lists option_id specimen_collection_method
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
    ('lists', 'specimen_collection_method', 'Specimen Collection Method', 1, 0, 0, '', 'SNOMED-CT binding');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`, `notes`) VALUES
-- Core/Common Methods
('specimen_collection_method', '129316008', 'Aspiration', 10, 0, 0, 'SNOMED-CT:129316008', 'Aspiration procedure'),
('specimen_collection_method', '129300006', 'Puncture', 20, 0, 0, 'SNOMED-CT:129300006', 'Puncture procedure'),
('specimen_collection_method', '129314006', 'Biopsy', 30, 0, 0, 'SNOMED-CT:129314006', 'Biopsy procedure'),
('specimen_collection_method', '129304002', 'Excision', 40, 0, 0, 'SNOMED-CT:129304002', 'Excision procedure'),
('specimen_collection_method', '129323009', 'Scraping', 50, 0, 0, 'SNOMED-CT:129323009', 'Scraping procedure'),
('specimen_collection_method', '70777001', 'Swab', 60, 0, 0, 'SNOMED-CT:70777001', 'Swab procedure'),
('specimen_collection_method', '225113003', 'Timed collection', 70, 0, 0, 'SNOMED-CT:225113003', 'Timed specimen collection'),
('specimen_collection_method', '386089008', 'Collection of coughed sputum', 80, 0, 0, 'SNOMED-CT:386089008', 'Sputum collection'),
('specimen_collection_method', '278450005', 'Finger-prick sampling', 90, 0, 0, 'SNOMED-CT:278450005', 'Capillary blood collection'),
-- Blood Collection Specific
('specimen_collection_method', '28520004', 'Venipuncture', 100, 1, 0, 'SNOMED-CT:28520004', 'Venous blood draw'),
('specimen_collection_method', '76499008', 'Arterial puncture', 110, 0, 0, 'SNOMED-CT:76499008', 'Arterial blood draw'),
-- Urine Collection Specific
('specimen_collection_method', '258574006', 'Midstream urine', 120, 0, 0, 'SNOMED-CT:258574006', 'Midstream urine collection'),
('specimen_collection_method', '73416001', 'Urine specimen collection, clean catch', 130, 0, 0, 'SNOMED-CT:73416001', 'Clean catch method'),
('specimen_collection_method', '386090004', 'Catheter specimen of urine', 140, 0, 0, 'SNOMED-CT:386090004', 'Catheterized urine'),
('specimen_collection_method', '386091000', 'Suprapubic aspiration of urine', 150, 0, 0, 'SNOMED-CT:386091000', 'Suprapubic tap'),
-- Respiratory
('specimen_collection_method', '397394008', 'Bronchoalveolar lavage', 160, 0, 0, 'SNOMED-CT:397394008', 'BAL procedure'),
('specimen_collection_method', '168138009', 'Nasopharyngeal swab', 170, 0, 0, 'SNOMED-CT:168138009', 'NP swab collection'),
-- Other
('specimen_collection_method', '225116006', 'Drainage of fluid', 180, 0, 0, 'SNOMED-CT:225116006', 'Fluid drainage');
#EndIf

#IfNotTable procedure_specimen
CREATE TABLE `procedure_specimen` (
  `procedure_specimen_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'record id',
  `uuid` binary(16) DEFAULT NULL COMMENT 'FHIR Specimen id',
  `procedure_order_id` BIGINT(20) NOT NULL COMMENT 'links to procedure_order.procedure_order_id',
  `procedure_order_seq` INT(11) NOT NULL COMMENT 'links to procedure_order_code.procedure_order_seq (per test line)',
  `specimen_identifier` VARCHAR(128) DEFAULT NULL COMMENT 'tube/barcode/internal id',
  `accession_identifier` VARCHAR(128) DEFAULT NULL COMMENT 'lab accession number',
  `specimen_type_code` VARCHAR(64) DEFAULT NULL COMMENT 'prefer SNOMED CT code',
  `specimen_type` VARCHAR(255) DEFAULT NULL COMMENT 'display/text',
  `collection_method_code` VARCHAR(64) DEFAULT NULL,
  `collection_method` VARCHAR(255) DEFAULT NULL,
  `specimen_location_code` VARCHAR(64) DEFAULT NULL,
  `specimen_location` VARCHAR(255) DEFAULT NULL,
  `collected_date` DATETIME DEFAULT NULL COMMENT 'single instant',
  `collection_date_low` DATETIME DEFAULT NULL COMMENT 'period start',
  `collection_date_high` DATETIME DEFAULT NULL COMMENT 'period end',
  `volume_value` DECIMAL(10,3) DEFAULT NULL,
  `volume_unit` VARCHAR(32) DEFAULT 'mL',
  `condition_code` VARCHAR(32) DEFAULT NULL COMMENT 'HL7 v2 0493 (e.g., ACT, HEM)',
  `specimen_condition` VARCHAR(64) DEFAULT NULL,
  `comments` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT(20) DEFAULT NULL,
  `updated_by` BIGINT(20) DEFAULT NULL,
  `deleted` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`procedure_specimen_id`),
  UNIQUE KEY `uuid_unique` (`uuid`),
  KEY `idx_order_line` (`procedure_order_id`,`procedure_order_seq`),
  KEY `idx_identifier` (`specimen_identifier`),
  KEY `idx_accession` (`accession_identifier`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow3D layout_options form_id DEM field_id occupation data_type 26
UPDATE `layout_options` SET `data_type` = 26 WHERE `form_id` = 'DEM' AND `field_id` = 'occupation';
-- Migrate existing occupation values to new list (if not already present)
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity) SELECT 'OccupationODH', occupations.occupation, occupations.occupation, 0, 0, 0, 'O*NET-SOC based occupation codes from ODH - migrated from patient_data.occupation', 1 FROM ( select distinct occupation from patient_data where occupation is not null and occupation != '' AND occupation NOT IN ( select option_id FROM list_options WHERE list_id='OccupationODH' ) ) AS occupations;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id em_start_date
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='em_country' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='em_country' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES
    ('DEM','em_start_date',@group_id,'Employment Start Date',@seq_add_to+10,4,1,20,0, '',1,1,'','','Employment Start Date',63,'','F',NULL,NULL,'');
ALTER TABLE `employer_data` ADD `start_date` datetime DEFAULT NULL COMMENT 'Employment start date for patient';
#Endif

#IfNotRow2D layout_options form_id DEM field_id em_end_date
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='em_start_date' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='em_start_date' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES
('DEM','em_end_date',@group_id,'Employment End Date',@seq_add_to+10,4,1,20,0, '',1,1,'','','Employment End Date',63,'','F',NULL,NULL,'');
ALTER TABLE `employer_data` ADD `end_date` datetime DEFAULT NULL COMMENT 'Employment end date for patient';
#Endif

#IfMissingColumn employer_data occupation
-- to avoid data truncation issues, use longtext, however, the list_options are limited to 64 chars so in the future we may need to truncate or re-map some values.
-- this may create some performance issues if searching on this field, so consider re-mapping to a shorter code in the future
ALTER TABLE `employer_data` ADD COLUMN `occupation` longtext COMMENT 'Employment Occupation fk to list_options.option_id where list_id=OccupationODH';
#EndIf

#IfMissingColumn employer_data industry
-- to avoid data truncation issues, use longtext, however, the list_options are limited to 64 chars so in the future we may need to truncate or re-map some values.
-- this may create some performance issues if searching on this field, so consider re-mapping to a shorter code in the future
ALTER TABLE `employer_data` ADD COLUMN `industry` text COMMENT 'Employment Industry fk to list_options.option_id where list_id=IndustryODH';
#EndIf

#IfMissingColumn employer_data created_by
ALTER TABLE `employer_data` ADD COLUMN `created_by` INT DEFAULT NULL COMMENT 'fk to users.id for the user that entered in the employer data';
#EndIf

#IfMissingColumn employer_data uuid
ALTER TABLE `employer_data` ADD COLUMN `uuid` binary(16) DEFAULT NULL COMMENT 'UUID for this employer record, for data exchange purposes';
#EndIf
-- ------------------------------------------------------------------- 10-10-2025 sjp-----------------------------------------------------------------------------
-- =====================================================================
-- US Core 8.0 ServiceRequest Database Migration
-- For ONC 2025 USCDI v5 Compliance
-- =====================================================================
--
#IfMissingColumn procedure_order scheduled_date
ALTER TABLE `procedure_order`
    ADD COLUMN `scheduled_date` DATETIME DEFAULT NULL
        COMMENT 'Scheduled date for service (FHIR occurrence[x])',
    ADD COLUMN `scheduled_start` DATETIME DEFAULT NULL
        COMMENT 'Scheduled start time (FHIR occurrencePeriod.start)',
    ADD COLUMN `scheduled_end` DATETIME DEFAULT NULL
        COMMENT 'Scheduled end time (FHIR occurrencePeriod.end)',
    ADD COLUMN `performer_type` VARCHAR(50) DEFAULT NULL
        COMMENT 'Type of performer: laboratory, radiology, pathology (SNOMED CT)',
    ADD COLUMN `order_intent` VARCHAR(31) NOT NULL DEFAULT 'order'
        COMMENT 'FHIR intent: order, plan, directive, proposal',
    ADD COLUMN `location_id` INT DEFAULT NULL
        COMMENT 'References facility.id for service location (FHIR locationReference)';
ALTER TABLE `procedure_order`
    ADD INDEX IF NOT EXISTS `idx_scheduled_date` (`scheduled_date`),
    ADD INDEX IF NOT EXISTS `idx_order_intent` (`order_intent`),
    ADD INDEX IF NOT EXISTS `idx_location_id` (`location_id`);
#EndIf

#IfNotRow2D list_options list_id ord_priority option_id routine
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `activity`)
VALUES
    ('ord_priority', 'routine', 'Routine', 45, 1, 0, 'Normal priority order', 1),
    ('ord_priority', 'urgent', 'Urgent', 55, 0, 0, 'Urgent priority order', 1),
    ('ord_priority', 'asap', 'ASAP', 65, 0, 0, 'As soon as possible', 1),
    ('ord_priority', 'stat', 'STAT', 75, 0, 0, 'Immediate/emergency', 1);
#EndIf
#IfNotRow2D list_options list_id lists option_id order_intent
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `activity`)
    VALUES ('lists', 'order_intent', 'Order Intent', 1, 0, 0, 'FHIR ServiceRequest intent values', 1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `activity`)
VALUES
    ('order_intent', 'order', 'Order', 10, 1, 0, 'Request for action to occur as specified', 1),
    ('order_intent', 'plan', 'Plan', 20, 0, 0, 'Intention to perform an action', 1),
    ('order_intent', 'directive', 'Directive', 30, 0, 0, 'Request with legal standing', 1),
    ('order_intent', 'proposal', 'Proposal', 40, 0, 0, 'Suggestion for action', 1),
    ('order_intent', 'option', 'Option', 50, 0, 0, 'Option for consideration', 1);
#EndIf
#IfNotRow2D list_options list_id lists option_id performer_type
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `activity`)
    VALUES ('lists', 'performer_type', 'Performer Type', 1, 0, 0, 'FHIR ServiceRequest performer type', 1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `codes`)
VALUES
    ('performer_type', 'laboratory', 'Laboratory', 10, 1, 0, 'Laboratory technician', 'SNOMED:159001'),
    ('performer_type', 'radiology', 'Radiology', 20, 0, 0, 'Radiologist', 'SNOMED:66862007'),
    ('performer_type', 'pathology', 'Pathology', 30, 0, 0, 'Pathologist', 'SNOMED:61207006'),
    ('performer_type', 'cardiology', 'Cardiology', 40, 0, 0, 'Cardiologist', ''),
    ('performer_type', 'pharmacy', 'Pharmacy', 50, 0, 0, 'Pharmacist', '');
#EndIf

#IfNotTable procedure_order_relationships
CREATE TABLE `procedure_order_relationships` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
     `procedure_order_id` BIGINT(20) NOT NULL COMMENT 'Links to procedure_order.procedure_order_id',
     `resource_type` VARCHAR(50) NOT NULL COMMENT 'FHIR resource type (Observation, Condition, etc.)',
     `resource_uuid` BINARY(16) NOT NULL COMMENT 'UUID of the related resource',
     `relationship` VARCHAR(50) DEFAULT NULL COMMENT 'Type of relationship',
     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     `created_by` BIGINT(20) DEFAULT NULL COMMENT 'User who created this link',
     INDEX `idx_order_id` (`procedure_order_id`),
     INDEX `idx_resource` (`resource_type`, `resource_uuid`),
     INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Links ServiceRequests to supporting clinical information';

-- --------------------------- Migrate existing data ---------------------------------------------------------------
-- Set order_intent default for existing orders
UPDATE `procedure_order` SET `order_intent` = 'order' WHERE `order_intent` IS NULL OR `order_intent` = '';
-- Set default priority if empty (and not already set)
UPDATE `procedure_order` SET `order_priority` = 'routine' WHERE (`order_priority` IS NULL OR `order_priority` = '') AND `date_ordered` IS NOT NULL;
-- Infer performer_type from procedure_order_type for existing orders
UPDATE `procedure_order`
SET `performer_type` = CASE
   WHEN `procedure_order_type` = 'laboratory_test' THEN 'laboratory'
   WHEN `procedure_order_type` = 'imaging' THEN 'radiology'
   WHEN `procedure_order_type` = 'clinical_test' THEN 'laboratory'
   WHEN `procedure_order_type` = 'procedure' THEN NULL END WHERE `performer_type` IS NULL AND `procedure_order_type` IS NOT NULL;
-- Copy date_collected to scheduled_date where appropriate
UPDATE `procedure_order` SET `scheduled_date` = `date_collected` WHERE `scheduled_date` IS NULL AND `date_collected` IS NOT NULL AND `date_collected` > NOW();
#EndIf

#IfMissingColumn issue_encounter uuid
ALTER TABLE `issue_encounter` ADD COLUMN `uuid` binary(16) DEFAULT NULL COMMENT 'UUID for this issue encounter record, for data exchange purposes';
#EndIf

#IfMissingColumn issue_encounter id
ALTER TABLE `issue_encounter` ADD UNIQUE INDEX `uniq_issue_key`(`pid`,`list_id`,`encounter`);
ALTER TABLE `issue_encounter` DROP PRIMARY KEY;
ALTER TABLE `issue_encounter` ADD COLUMN `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfNotIndex issue_encounter uuid_unique
ALTER TABLE `issue_encounter` ADD UNIQUE KEY `uuid_unique` (`uuid`);
#EndIf

#IfNotIndex employer_data uuid_unique
ALTER TABLE `employer_data` ADD UNIQUE KEY `uuid_unique` (`uuid`);
#EndIf

#IfMissingColumn issue_encounter created_by
ALTER TABLE `issue_encounter` ADD COLUMN `created_by` bigint(20) DEFAULT NULL COMMENT 'fk to users.id for the user that entered in the issue encounter data';
#EndIf
#IfMissingColumn issue_encounter updated_by
ALTER TABLE `issue_encounter` ADD COLUMN `updated_by` bigint(20) DEFAULT NULL COMMENT 'fk to users.id for the user that last updated the issue encounter data';
#EndIf
#IfMissingColumn issue_encounter created_at
ALTER TABLE `issue_encounter` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when this issue encounter record was created';
#EndIf
#IfMissingColumn issue_encounter updated_at
ALTER TABLE `issue_encounter` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'timestamp when this issue encounter record was last updated';
#EndIf

#IfNotRow list_options list_id administrative_sex
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','administrative_sex','Administrative Sex',0,0,0,'Codeset from valueset http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1021.121 (expansive)',1);

-- note USCDI V3 has a ton more options here, but USCDI V4 reverts to M/F/nonbinary/asked-decline with expansion allowed so adding in unknown to map values from patient_data.sex column
INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('administrative_sex', 'Male', 'Male', 10, 'SNOMED-CT:248152002', ''),
       ('administrative_sex', 'Female', 'Female', 20, 'SNOMED-CT:248153007', ''),
       ('administrative_sex', 'nonbinary', 'Identifies as nonbinary gender (finding)', 20, 'SNOMED-CT:33791000087105', ''),
       ('administrative_sex', 'asked-declined', 'Asked But Declined', 30, 'DataAbsentReason:asked-declined', ''),
       ('administrative_sex', 'UNK', 'unknown', 40, 'DataAbsentReason:unknown', '');
#EndIf

#IfMissingColumn patient_data sex_identified
ALTER TABLE `patient_data` ADD COLUMN `sex_identified` TEXT COMMENT 'Patient reported current sex';
-- migrate existing values over as its a new column, people can change it later if needed
UPDATE `patient_data` SET `sex_identified` = `sex` WHERE `sex` IS NOT NULL AND `sex` != '';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id sex_identified
-- we rename Sex to Birth Sex and use the sex_identified to represent the 'Sex' label with the Administrative Sex list option
UPDATE `layout_options` SET title='Birth Sex',description='Birth Sex' WHERE `form_id` = 'DEM' AND `field_id` = 'sex';
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='gender_identity' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='gender_identity' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES
    ('DEM','sex_identified',@group_id,'Sex',@seq_add_to+10,1,1,20,0,'administrative_sex',1,1,'UNK','sex_identified',"Sex",1,'','[\"N\"]','Sex',0,'');
#Endif

#IfNotRow list_options list_id yes_no_unknown
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','yes_no_unknown','Yes/No/Unknown',0,0,0,'Codeset from valueset https://vsac.nlm.nih.gov/valueset/2.16.840.1.113762.1.4.1267.16/expansion',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('yes_no_unknown', 'yes', 'Yes', 10, 'SNOMED-CT:373066001', ''),
       ('yes_no_unknown', 'no', 'No', 20, 'SNOMED-CT:373067005', ''),
       ('yes_no_unknown', 'asked-unknown', 'Asked But Unknown', 30, 'DataAbsentReason:asked-unknown', ''),
       ('yes_no_unknown', 'unknown', 'Unknown', 40, 'DataAbsentReason:unknown', '');
#EndIf

#IfMissingColumn patient_data interpreter_needed
-- we add this column in order to map to the USCDI V4 element for interpreter needed since the patient_data.interpreter column is a free text field
ALTER TABLE `patient_data` ADD COLUMN `interpreter_needed` TEXT COMMENT 'fk to list_options.option_id where list_id=yes_no_unknown used to determine if patient needs an interpreter';
-- migrate existing values over as its a new column, people can change it later if needed
UPDATE `patient_data` SET `interpreter_needed` = 'YES' WHERE `interpretter` IS NOT NULL AND `interpretter` != '' AND LOWER(TRIM(`interpretter`)) ='yes';
UPDATE `patient_data` SET `interpreter_needed` = 'NO' WHERE `interpretter` IS NOT NULL AND `interpretter` != '' AND LOWER(TRIM(`interpretter`)) ='no';
-- there are so many possibilities that for a structured data set we set the value to unknown
UPDATE `patient_data` SET `interpreter_needed` = 'unknown' WHERE `interpreter_needed` IS NULL and `interpretter` IS NOT NULL AND `interpretter` != '';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id interpreter_needed
-- we rename 'Interpreter' to 'Intepreter Comments' and add Interpreter Needed as structured data so we can programatically key off of it
UPDATE `layout_options` SET title='Interpreter Comments',description='Additional notes about interpretation needs' WHERE `form_id` = 'DEM' AND `field_id` = 'interpretter';
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='homeless' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='homeless' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES
    ('DEM','interpreter_needed',@group_id,'Interpreter',@seq_add_to+5,1,1,20,0,'yes_no_unknown',1,1,'UNK','interpreter_needed',"Interpreter needed?",1,'','','',0,'');
#Endif

#IfMissingColumn form_misc_billing_options encounter
ALTER TABLE `form_misc_billing_options` ADD `encounter` BIGINT(20) DEFAULT NULL;
#EndIf

#IfNotIndex form_misc_billing_options encounter
ALTER TABLE `form_misc_billing_options` ADD UNIQUE `encounter` (`encounter`);
#EndIf

#IfMBOEncounterNeeded
#EndIf
-- ------------------------------------------------------------------- 10-17-2025 sjp -----------------------------------------------------------------------------
-- Care Team Roles: parent list entry --Bug fix
-- Care Team Roles: add if missing

UPDATE `list_options` SET option_id = 'family_medicine_specialist', title = 'Family Medicine Specialist'
WHERE list_id = 'care_team_roles' AND option_id = 'primary_care_provider' AND codes = 'SNOMED-CT:62247001';

#IfNotRow2D list_options list_id lists option_id care_team_roles
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists','care_team_roles','Care Team Roles');
#EndIf
#IfNotRow2D list_options list_id care_team_roles option_id healthcare_professional
INSERT IGNORE INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`,`codes`,`activity`) VALUES
     ('care_team_roles','physician','Physician',90,0,'SNOMED-CT:158965000',1),
     ('care_team_roles','nurse_practitioner','Nurse Practitioner',100,0,'SNOMED-CT:224571005',1),
     ('care_team_roles','physician_assistant','Physician Assistant',110,0,'SNOMED-CT:449161006',1),
     ('care_team_roles','therapist','Clinical Therapist',120,0,'SNOMED-CT:224538006',1),
     ('care_team_roles','primary_care_provider','Primary Care Provider',130,0,'SNOMED-CT:446050000',1),
     ('care_team_roles','dietitian','Dietitian',140,0,'SNOMED-CT:159033005',1),
     ('care_team_roles','mental_health','Mental Health Professional',150,0,'SNOMED-CT:224597008',1),
     ('care_team_roles','healthcare_professional','Healthcare Professional',160,0,'SNOMED-CT:223366009',1);
#EndIf

#IfNotTable form_vitals_calculation
CREATE TABLE `form_vitals_calculation` (
   `id` int NOT NULL AUTO_INCREMENT,
   `uuid` binary(16) DEFAULT NULL,
   `encounter` bigint(20) DEFAULT NULL COMMENT 'fk to form_encounter.id',
   `pid` bigint(20) NOT NULL COMMENT 'fk to patient_data.pid',
   `date_start` datetime DEFAULT NULL,
   `date_end` datetime DEFAULT NULL,
   `created_at` datetime DEFAULT NULL,
   `updated_at` datetime DEFAULT NULL,
   `created_by` bigint(20) DEFAULT NULL,
   `updated_by` bigint(20) DEFAULT NULL,
   `calculation_id` varchar(64) DEFAULT NULL COMMENT 'application identifier representing calculation e.g., bp-MeanLast5, bp-Mean3Day, bp-MeanEncounter',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unq_uuid` (`uuid`),
   KEY `idx_pid` (`pid`),
   KEY `idx_encounter` (`encounter`),
   KEY `idx_calculation_id` (`calculation_id`)
) ENGINE=InnoDB COMMENT = 'Main calculation records - one per logical calculation (e.g., average BP)';
#EndIf

#IfNotTable form_vitals_calculation_components
CREATE TABLE `form_vitals_calculation_components` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fvc_uuid` binary(16) NOT NULL COMMENT 'fk to form_vitals_calculation.uuid',
  `vitals_column` varchar(64) NOT NULL COMMENT 'Component type: bps, bpd, pulse, etc.',
  `value` DECIMAL(12,6) DEFAULT NULL COMMENT 'Calculated numeric component value',
  `value_string` varchar(255) DEFAULT NULL COMMENT 'Calculated non-numeric component value',
  `value_unit` varchar(16) DEFAULT NULL COMMENT 'Unit for this component value',
  `component_order` int NOT NULL DEFAULT 0 COMMENT 'Display order for components',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_fvc_component` (`fvc_uuid`, `vitals_column`),
  KEY `idx_vitals_column` (`vitals_column`),
  KEY `idx_component_order` (`fvc_uuid`, `component_order`)
) ENGINE=InnoDB COMMENT = 'Component values for calculations (e.g., systolic=120, diastolic=80)';
#EndIf

#IfNotTable form_vitals_calculation_form_vitals
CREATE TABLE `form_vitals_calculation_form_vitals` (
    `fvc_uuid` binary(16) NOT NULL COMMENT 'fk to form_vitals_calculation.uuid',
    `vitals_id` bigint(20) NOT NULL COMMENT 'fk to form_vitals.id',
    PRIMARY KEY (`fvc_uuid`, `vitals_id`)
) ENGINE=InnoDB COMMENT = 'Join table between form_vitals_calculation and form_vitals table representing the derivative observation relationship between the calculation and the source records';
#EndIf

#IfMissingColumn drug_sales uuid
ALTER TABLE `drug_sales` ADD COLUMN `uuid` binary(16) DEFAULT NULL COMMENT 'UUID for this drug sales record, for data exchange purposes';
ALTER TABLE `drug_sales` ADD UNIQUE KEY `uuid_unique` (`uuid`);
#EndIf

#IfMissingColumn drug_sales pharmacy_supply_type
ALTER TABLE `drug_sales` ADD COLUMN `pharmacy_supply_type` VARCHAR(50) DEFAULT NULL COMMENT 'fk to list_options.option_id where list_id=pharmacy_supply_type to indicate type of dispensing first order, refil, emergency, partial order, etc';
#EndIf

#IfNotRow list_options list_id act_pharmacy_supply_type
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','act_pharmacy_supply_type','Act Pharmacy Supply Type',0,0,0,'Codeset from valueset http://terminology.hl7.org/ValueSet/v3-ActPharmacySupplyType (HL7 v3 ActCode)',1);

-- Act Pharmacy Supply Type codes from HL7 v3 ActCode system
INSERT INTO list_options (list_id, option_id, title, seq, codes, notes)
VALUES ('act_pharmacy_supply_type', 'DF', 'Daily Fill', 10, 'DF', 'A fill providing sufficient supply for one day'),
       ('act_pharmacy_supply_type', 'EM', 'Emergency Supply', 20, 'EM', 'A supply action where there is no valid order for the supplied medication'),
       ('act_pharmacy_supply_type', 'SO', 'Script Owing', 30, 'SO', 'An emergency supply where the expectation is that a formal order authorizing the supply will be provided at a later date'),
       ('act_pharmacy_supply_type', 'FF', 'First Fill', 40, 'FF', 'The initial fill against an order'),
       ('act_pharmacy_supply_type', 'FFS', 'Fee for Service', 50, 'FFS', 'A billing arrangement where a Provider charges a separate fee for each intervention/procedure/event or product'),
       ('act_pharmacy_supply_type', 'FPFF', 'First Fill - Part Fill', 60, 'FPFF', 'A first fill where the quantity supplied is less than one full repetition of the ordered amount'),
       ('act_pharmacy_supply_type', 'FFCS', 'First Fill Complete, Sub', 70, 'FFCS', 'A first fill where the quantity supplied is equal to one full repetition and strength supplied is less than ordered'),
       ('act_pharmacy_supply_type', 'TFS', 'Trial Fill Partial', 80, 'TFS', 'A fill where a small portion is provided to allow for determination of therapy effectiveness and patient tolerance'),
       ('act_pharmacy_supply_type', 'FFC', 'First Fill Complete', 90, 'FFC', 'A first fill where the quantity supplied is equal to one full repetition of the ordered amount'),
       ('act_pharmacy_supply_type', 'FFP', 'First Fill, Part Fill', 100, 'FFP', 'A first fill where the quantity supplied is less than one full repetition of the ordered amount'),
       ('act_pharmacy_supply_type', 'FFSS', 'First Fill, Partial Strength', 110, 'FFSS', 'A first fill where the strength supplied is less than the ordered strength'),
       ('act_pharmacy_supply_type', 'TF', 'Trial Fill', 120, 'TF', 'A fill where a small portion is provided to allow for determination of therapy effectiveness and patient tolerance'),
       ('act_pharmacy_supply_type', 'FS', 'Floor stock', 130, 'FS', 'A supply action to restock a smaller more local dispensary'),
       ('act_pharmacy_supply_type', 'MS', 'Manufacturer Sample', 140, 'MS', 'A supply of a manufacturer sample'),
       ('act_pharmacy_supply_type', 'RF', 'Refill', 150, 'RF', 'A fill against an order that has already been filled at least once'),
       ('act_pharmacy_supply_type', 'UD', 'Unit Dose', 160, 'UD', 'A supply action that provides sufficient material for a single dose'),
       ('act_pharmacy_supply_type', 'RFC', 'Refill - Complete', 170, 'RFC', 'A refill where the quantity supplied is equal to one full repetition of the ordered amount'),
       ('act_pharmacy_supply_type', 'RFCS', 'Refill Complete, Partial Strength', 180, 'RFCS', 'A refill complete fill where the strength supplied is less than the ordered strength'),
       ('act_pharmacy_supply_type', 'RFF', 'Refill First Fill this Facility', 190, 'RFF', 'The first fill against an order that has already been filled at least once at another facility'),
       ('act_pharmacy_supply_type', 'RFFS', 'Refill First Fill, Partial Strength', 200, 'RFFS', 'The first fill at another facility where the strength supplied is less than ordered'),
       ('act_pharmacy_supply_type', 'RFP', 'Refill with Partial Fill', 210, 'RFP', 'A refill where the quantity supplied is less than one full repetition of the ordered amount'),
       ('act_pharmacy_supply_type', 'RFPS', 'Refill Partial Fill, Partial Strength', 220, 'RFPS', 'A refill partial fill where the strength supplied is less than the ordered strength'),
       ('act_pharmacy_supply_type', 'RFS', 'Refill partial strength', 230, 'RFS', 'A refill where the strength supplied is less than the ordered strength'),
       ('act_pharmacy_supply_type', 'TB', 'Trial Balance', 240, 'TB', 'A fill where the remainder of a complete fill is provided after a trial fill'),
       ('act_pharmacy_supply_type', 'TBS', 'Trial Balance Partial Strength', 250, 'TBS', 'A fill where the remainder is provided after a trial fill and strength is less than ordered'),
       ('act_pharmacy_supply_type', 'UDE', 'Unit Dose Equivalent', 260, 'UDE', 'A supply action that provides sufficient material for a single dose via multiple products');
#EndIf

-- update NCI codes for drug_route list options
#IfNotRow3D list_options list_id drug_route option_id intradermal codes NCI-CONCEPT-ID:C38238
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C38288' WHERE list_id='drug_route' AND option_id=1 AND title="Per Oris";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38295" WHERE list_id='drug_route' AND option_id=2 AND title="Per Rectum";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38675" WHERE list_id='drug_route' AND option_id=3 AND title="To Skin";
-- 4 codes are empty as there is no mapping for 'To Affected Area' as it depends on the region (skin, internal, tumor, etc)
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38300" WHERE list_id='drug_route' AND option_id=5 AND title="Sublingual";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38300" WHERE list_id='drug_route' AND option_id=6 AND title="Left Eye";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38300" WHERE list_id='drug_route' AND option_id=7 AND title="Right Eye";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38287" WHERE list_id='drug_route' AND option_id=8 AND title="Each Eye";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38299" WHERE list_id='drug_route' AND option_id=9 AND title="Subcutaneous";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C28161" WHERE list_id='drug_route' AND option_id=10 AND title="IM";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38276" WHERE list_id='drug_route' AND option_id=11 AND title="IV";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38284" WHERE list_id='drug_route' AND option_id=12 AND title="NS";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38192" WHERE list_id='drug_route' AND option_id=13 AND title="Both Ears";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38192" WHERE list_id='drug_route' AND option_id=14 AND title="Left Ear";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38192" WHERE list_id='drug_route' AND option_id=15 AND title="Right Ear";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38238" WHERE list_id='drug_route' AND option_id="intradermal";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38290" WHERE list_id='drug_route' AND option_id="other";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38305" WHERE list_id='drug_route' AND option_id="transdermal";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C28161" WHERE list_id='drug_route' AND option_id="intramuscular";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38216" WHERE list_id='drug_route' AND option_id="inhale";
UPDATE `list_options` SET codes="NCI-CONCEPT-ID:C38288" WHERE list_id='drug_route' AND option_id="bymouth";
#EndIf

#IfMissingColumn drug_sales last_updated
ALTER TABLE `drug_sales` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn drug_sales date_created
ALTER TABLE `drug_sales` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn drug_sales updated_by
ALTER TABLE `drug_sales` ADD `updated_by` BIGINT(20) DEFAULT NULL;
#EndIf

#IfMissingColumn drug_sales created_by
ALTER TABLE `drug_sales` ADD `created_by` BIGINT(20) DEFAULT NULL;
#EndIf

#IfNotRow4D list_options list_id drug_interval option_id 15 codes Q1H title h
-- Empty/default option
UPDATE `list_options` SET codes='', notes='No specific dosing interval specified' WHERE list_id='drug_interval' AND option_id='0';

-- Standard frequency intervals
UPDATE `list_options` SET codes='BID', notes='Twice daily (bis in die) - Two times a day at institution specified time' WHERE list_id='drug_interval' AND option_id='1' AND title='b.i.d.';
UPDATE `list_options` SET codes='TID', notes='Three times daily (ter in die) - Three times a day at institution specified time' WHERE list_id='drug_interval' AND option_id='2' AND title='t.i.d.';
UPDATE `list_options` SET codes='QID', notes='Four times daily (quater in die) - Four times a day at institution specified time' WHERE list_id='drug_interval' AND option_id='3' AND title='q.i.d.';

-- Hour-based intervals
UPDATE `list_options` SET codes='Q3H', notes='Every 3 hours - Administer medication every three hours' WHERE list_id='drug_interval' AND option_id='4' AND title='q.3h';
UPDATE `list_options` SET codes='Q4H', notes='Every 4 hours - Administer medication every four hours' WHERE list_id='drug_interval' AND option_id='5' AND title='q.4h';
UPDATE `list_options` SET codes='', notes='Every 5 hours - No standard FHIR code available' WHERE list_id='drug_interval' AND option_id='6' AND title='q.5h';
UPDATE `list_options` SET codes='Q6H', notes='Every 6 hours - Administer medication every six hours' WHERE list_id='drug_interval' AND option_id='7' AND title='q.6h';
UPDATE `list_options` SET codes='Q8H', notes='Every 8 hours - Administer medication every eight hours' WHERE list_id='drug_interval' AND option_id='8' AND title='q.8h';

-- Daily dosing
UPDATE `list_options` SET codes='QD', notes='Once daily (quaque die) - Daily at institution specified time' WHERE list_id='drug_interval' AND option_id='9' AND title='Daily';

-- Meal-related timing (no FHIR codes available for these)
UPDATE `list_options` SET codes='', notes='Before meals (ante cibum) - Take medication before eating' WHERE list_id='drug_interval' AND option_id='10' AND title='a.c.';
UPDATE `list_options` SET codes='', notes='After meals (post cibum) - Take medication after eating' WHERE list_id='drug_interval' AND option_id='11' AND title='p.c.';

-- Time of day
UPDATE `list_options` SET codes='AM', notes='Morning (ante meridiem) - Administer in the morning hours' WHERE list_id='drug_interval' AND option_id='12' AND title='a.m.';
UPDATE `list_options` SET codes='PM', notes='Evening (post meridiem) - Administer in the evening hours' WHERE list_id='drug_interval' AND option_id='13' AND title='p.m.';

-- "Ante" means "before"
UPDATE `list_options` SET codes='', notes='Before - General instruction meaning "before" (ante)' WHERE list_id='drug_interval' AND option_id='14' AND title='ante';

-- Hour unit
UPDATE `list_options` SET codes='Q1H', notes='Every 1 hour - Administer medication every hour' WHERE list_id='drug_interval' AND option_id='15' AND title='h';

-- Bedtime
UPDATE `list_options` SET codes='HS', notes='At bedtime (hora somni) - Administer at bedtime or hour of sleep' WHERE list_id='drug_interval' AND option_id='16' AND title='h.s.';

-- As needed (should be PRN, but nothing in FHIR)
UPDATE `list_options` SET codes='', notes='As needed (pro re nata) - Take medication when necessary or as required' WHERE list_id='drug_interval' AND option_id='17' AND title='p.r.n.';

-- Immediately (should be STAT but nothing in FHIR)
UPDATE `list_options` SET codes='', notes='Immediately (statim) - Administer medication immediately' WHERE list_id='drug_interval' AND option_id='18' AND title='stat';

-- Extended intervals
UPDATE `list_options` SET codes='WK', notes='Weekly - Once per week' WHERE list_id='drug_interval' AND option_id='19' AND title='Weekly';
UPDATE `list_options` SET codes='MO', notes='Monthly - Once per month' WHERE list_id='drug_interval' AND option_id='20' AND title='Monthly';
#EndIf

#IfNotRow2D list_options list_id medication_adherence_information_source option_id professional_nurse
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','medication_adherence_information_source','Information Source for Medication Adherence',0,0,0,'Codeset from valueset http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1267.11 (InformationSourceForMedicationAdherence)',1);

-- this is an example value set which means the value set can be nearly anything we want here so we can expand in the future if needed
INSERT INTO list_options (list_id, option_id, title, seq, codes)
VALUES ('medication_adherence_information_source', 'professional_nurse', 'Professional Nurse (occupation)', 10, 'SNOMED-CT:106292003'),
        ('medication_adherence_information_source', 'patient', 'Patient (person)', 20, 'SNOMED-CT:116154003'),
       ('medication_adherence_information_source', 'pharmacy', 'Pharmacy', 30, 'HSOC:1179-1'),
       ('medication_adherence_information_source', 'home_care', 'Home Care', 40, 'HSOC:1192-4'),
       ('medication_adherence_information_source', 'location_outside_facility', 'Location Outside Facility', 50, 'HSOC:1204-7'),
       ('medication_adherence_information_source', 'adm_physician', 'admitting physician', 60, 'ParticipationFunction:ADMPHYS'),
       ('medication_adherence_information_source', 'parent', 'Parent', 70, 'ParticipationFunction:PRN');
#EndIf

#IfNotRow2D list_options list_id medication_adherence option_id compliance
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity)
VALUES ('lists','medication_adherence','Medication Adherence',0,0,0,'Codeset from valueset http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1240.8 (rMedicationAdherence)',1);

INSERT INTO list_options (list_id, option_id, title, seq, codes)
VALUES ('medication_adherence', 'compliance', 'Complies with drug therapy (finding)', 10, 'SNOMED-CT:1156699004'),
       ('medication_adherence', 'non_compliance', 'Does not take medication (finding)', 20, 'SNOMED-CT:715036001'),
       ('medication_adherence', 'asked_declined', 'Asked But Declined', 30, 'DataAbsentReason:asked-declined'),
       ('medication_adherence', 'asked_unknown', 'Asked But Unknown', 40, 'DataAbsentReason:asked-unknown'),
       ('medication_adherence', 'not_asked', 'Not Asked', 50, 'DataAbsentReason:not-asked'),
       ('medication_adherence', 'unknown', 'Unknown', 60, 'DataAbsentReason:unknown');
#EndIf

#IfMissingColumn lists_medication medication_adherence_information_source
ALTER TABLE lists_medication ADD COLUMN `medication_adherence_information_source` VARCHAR(50) DEFAULT NULL COMMENT 'fk to list_options.option_id where list_id=medication_adherence_information_source to indicate who provided the medication adherence information';
ALTER TABLE lists_medication ADD COLUMN `medication_adherence` VARCHAR(50) DEFAULT NULL COMMENT 'fk to list_options.option_id where list_id=medication_adherence to indicate if patient is complying with medication regimen';
ALTER TABLE lists_medication ADD COLUMN `medication_adherence_date_asserted` DATETIME DEFAULT NULL COMMENT 'Date when the medication adherence information was asserted';
#EndIf

#IfMissingColumn prescriptions diagnosis
-- prescriptions has an indication column which would normally be this diagnosis but Weno and other tables seem to use
-- this as some kind of identifier so we can't use that column
ALTER TABLE prescriptions ADD COLUMN diagnosis TEXT COMMENT 'Diagnosis or reason for the prescription';
#EndIf

#IfMissingColumn lists_medication prescription_id
-- instead of linking medications by their title, we can link them by prescription_id to the prescriptions table
ALTER TABLE lists_medication ADD COLUMN `prescription_id` BIGINT(20) DEFAULT NULL COMMENT 'fk to prescriptions.prescription_id to link medication to prescription record';
#EndIf


--
-- Table structure for linking clinical notes to documents
--
#IfNotTable clinical_notes_documents
CREATE TABLE IF NOT EXISTS `clinical_notes_documents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `clinical_note_id` bigint(20) NOT NULL COMMENT 'Foreign key to form_clinical_notes.id',
  `document_id` bigint(20) NOT NULL COMMENT 'Foreign key to documents.id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
  `created_by` varchar(255) DEFAULT NULL COMMENT 'Username who created the link',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_document` (`clinical_note_id`, `document_id`),
  KEY `idx_clinical_note_id` (`clinical_note_id`),
  KEY `idx_document_id` (`document_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Links clinical notes to patient documents';
#EndIf

--
-- Table structure for linking clinical notes to procedure results
--
#IfNotTable clinical_notes_procedure_results
CREATE TABLE IF NOT EXISTS `clinical_notes_procedure_results` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `clinical_note_id` bigint(20) NOT NULL COMMENT 'Foreign key to form_clinical_notes.id',
  `procedure_result_id` bigint(20) NOT NULL COMMENT 'Foreign key to procedure_result.procedure_result_id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
  `created_by` varchar(255) DEFAULT NULL COMMENT 'Username who created the link',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_result` (`clinical_note_id`, `procedure_result_id`),
  KEY `idx_clinical_note_id` (`clinical_note_id`),
  KEY `idx_procedure_result_id` (`procedure_result_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Links clinical notes to procedure results/lab values';
#EndIf

#IfNotRow issue_types type health_concern
INSERT INTO issue_types(active, category, type, plural, singular, abbreviation,style, force_show, ordering, aco_spec) VALUES (1, 'default', 'health_concern', 'Health Concerns', 'Health Concern', 'HC', 0, 1, 15, 'patients|med');
#EndIf


#IfNotTable form_history_sdoh_health_concerns
CREATE TABLE IF NOT EXISTS `form_history_sdoh_health_concerns` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `sdoh_history_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to form_history_sdoh.id',
    `health_concern_id` bigint(20) NOT NULL COMMENT 'FK to lists.id where type=health_concern or medical_problem',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` bigint(20) DEFAULT NULL COMMENT 'FK to users.id',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_sdoh_concern` (`sdoh_history_id`, `health_concern_id`),
    KEY `idx_sdoh_history` (`sdoh_history_id`),
    KEY `idx_health_concern` (`health_concern_id`)
) ENGINE=InnoDB COMMENT='Links SDOH assessments to health concern conditions';
#EndIf
-- ------------------------------------------------------------------- 11-01-2025 sjp -----------------------------------------------------------------------------
-- Patient Preferences Database Schema
-- Uses OpenEMR's list_options table for LOINC codes
-- Table for storing patient treatment intervention preferences
#IfNotTable patient_treatment_intervention_preferences
CREATE TABLE `patient_treatment_intervention_preferences` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uuid` binary(16) DEFAULT NULL,
    `patient_id` int(11) NOT NULL,
    `observation_code` varchar(50) NOT NULL COMMENT 'LOINC code',
    `observation_code_text` varchar(255) DEFAULT NULL,
    `value_type` enum('coded','text','boolean') DEFAULT 'coded',
    `value_code` varchar(50) DEFAULT NULL,
    `value_code_system` varchar(255) DEFAULT NULL,
    `value_display` varchar(255) DEFAULT NULL,
    `value_text` text,
    `value_boolean` tinyint(1) DEFAULT NULL,
    `effective_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` varchar(20) DEFAULT 'final',
    `note` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_uuid` (`uuid`),
    KEY `patient_id` (`patient_id`),
    KEY `observation_code` (`observation_code`),
    KEY `status` (`status`)
) ENGINE=InnoDB;
#EndIf

-- Table for storing patient care experience preferences
#IfNotTable patient_care_experience_preferences
CREATE TABLE `patient_care_experience_preferences` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uuid` binary(16) DEFAULT NULL,
    `patient_id` int(11) NOT NULL,
    `observation_code` varchar(50) NOT NULL COMMENT 'LOINC code',
    `observation_code_text` varchar(255) DEFAULT NULL,
    `value_type` enum('coded','text','boolean') DEFAULT 'coded',
    `value_code` varchar(50) DEFAULT NULL,
    `value_code_system` varchar(255) DEFAULT NULL,
    `value_display` varchar(255) DEFAULT NULL,
    `value_text` text,
    `value_boolean` tinyint(1) DEFAULT NULL,
    `effective_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` varchar(20) DEFAULT 'final',
    `note` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_uuid` (`uuid`),
    KEY `patient_id` (`patient_id`),
    KEY `observation_code` (`observation_code`),
    KEY `status` (`status`)
) ENGINE=InnoDB;
#EndIf

-- ------------------------------------- Parent lists under `lists`--------------------------------------------------------------------
#IfNotRow2D list_options list_id lists option_id treatment_intervention_preferences
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`)
VALUES  ('lists','treatment_intervention_preferences','Treatment Intervention Preferences',1);
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`notes`,`codes`,`activity`) VALUES
    ('treatment_intervention_preferences','81329-5','Thoughts on resuscitation (CPR)',10,'tip_resuscitation_answers','LOINC:81329-5',1),
    ('treatment_intervention_preferences','81330-3','Thoughts on intubation',20,'tip_intubation_answers','LOINC:81330-3',1),
    ('treatment_intervention_preferences','81331-1','Thoughts on tube feeding',30,'tip_tubefeeding_answers','LOINC:81331-1',1),
    ('treatment_intervention_preferences','81332-9','Thoughts on IV fluid and support',40,'tip_ivfluids_answers','LOINC:81332-9',1),
    ('treatment_intervention_preferences','81333-7','Thoughts on antibiotics',50,'tip_antibiotics_answers','LOINC:81333-7',1);
#EndIf

#IfNotRow2D list_options list_id lists option_id care_experience_preferences
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`)
VALUES ('lists','care_experience_preferences','Care Experience Preferences',1);
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`notes`,`codes`,`activity`) VALUES
    ('care_experience_preferences','95541-9','Care experience preference',10,'cep_general_answers','LOINC:95541-9',1),
    ('care_experience_preferences','81364-2','Religious or cultural beliefs (reported)',20,'cep_religious_answers','LOINC:81364-2',1),
    ('care_experience_preferences','81365-9','Religious/cultural affiliation contact to notify (reported)',30,'cep_religious_contact_answers','LOINC:81365-9',1),
    ('care_experience_preferences','103980-9','Preferred pharmacy',40,'cep_pharmacy_answers','LOINC:103980-9',1),
    ('care_experience_preferences','81338-6','Patient goals, preferences & priorities for care experience',90,'cep_overall_narrative','LOINC:81338-6',1);
#EndIf
-- Value sets table for coded answers
#IfNotTable preference_value_sets
CREATE TABLE `preference_value_sets` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `loinc_code` varchar(50) NOT NULL,
     `answer_code` varchar(100) NOT NULL,
     `answer_system` varchar(255) NOT NULL,
     `answer_display` varchar(255) NOT NULL,
     `answer_definition` text,
     `sort_order` int(11) DEFAULT 0,
     `active` tinyint(1) DEFAULT 1,
     PRIMARY KEY (`id`),
     KEY `loinc_code` (`loinc_code`)
) ENGINE=InnoDB COMMENT='Answer lists for preference codes';

INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
    ('81329-5','LA33470-8','http://loinc.org','Yes CPR',1,1),
    ('81329-5','LA33471-6','http://loinc.org','No CPR (Do Not Attempt Resuscitation)',2,1),
    ('81329-5','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81329-5','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81330-3','373066001','http://snomed.info/sct','Yes',1,1),
    ('81330-3','373067005','http://snomed.info/sct','No',2,1),
    ('81330-3','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81330-3','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81331-1','373066001','http://snomed.info/sct','Yes',1,1),
    ('81331-1','373067005','http://snomed.info/sct','No',2,1),
    ('81331-1','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81331-1','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81332-9','373066001','http://snomed.info/sct','Yes',1,1),
    ('81332-9','373067005','http://snomed.info/sct','No',2,1),
    ('81332-9','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81332-9','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81333-7','373066001','http://snomed.info/sct','Yes',1,1),
    ('81333-7','373067005','http://snomed.info/sct','No',2,1),
    ('81333-7','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81333-7','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81364-2','160542002','http://snomed.info/sct','Muslim',1,1),
    ('81364-2','160540005','http://snomed.info/sct','Jewish',2,1),
    ('81364-2','160539006','http://snomed.info/sct','Christian',3,1),
    ('81364-2','160538003','http://snomed.info/sct','Hindu',4,1),
    ('81364-2','160543007','http://snomed.info/sct','Buddhist',5,1),
    ('81364-2','276119007','http://snomed.info/sct','No religion',6,1),
    ('81364-2','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other',99,1),
    ('81365-9','373066001','http://snomed.info/sct','Yes',1,1),
    ('81365-9','373067005','http://snomed.info/sct','No',2,1),
    ('81365-9','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1),
    ('81365-9','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('103980-9','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('95541-9','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1),
    ('81338-6','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other (see free text)',100,1);
#EndIf

-- ---------------------------------------------------------------- psoas s.n sjp---- related person implementation 11-06-2025----------------------------------------
-- relatedperson
-- https://build.fhir.org/relatedperson.html

#IfNotTable person
CREATE TABLE `person` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `uuid` BINARY(16) DEFAULT NULL,
    `title` VARCHAR(31) DEFAULT NULL COMMENT 'Mr., Mrs., Dr., etc.',
    `first_name` VARCHAR(63) DEFAULT NULL,
    `middle_name` VARCHAR(63) DEFAULT NULL,
    `last_name` VARCHAR(63) DEFAULT NULL,
    `preferred_name` VARCHAR(63) DEFAULT NULL COMMENT 'Name person prefers to be called',
    `gender` VARCHAR(31) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `death_date` DATE DEFAULT NULL,
    `marital_status` VARCHAR(31) DEFAULT NULL,
    `race` VARCHAR(63) DEFAULT NULL,
    `ethnicity` VARCHAR(63) DEFAULT NULL,
    `preferred_language` VARCHAR(63) DEFAULT NULL COMMENT 'ISO 639-1 code',
    `communication` VARCHAR(254) DEFAULT NULL COMMENT 'Communication preferences/needs',
    `ssn` VARCHAR(31) DEFAULT NULL COMMENT 'Should be encrypted in application',
    `active` TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    `inactive_reason` VARCHAR(255) DEFAULT NULL,
    `inactive_date` DATETIME DEFAULT NULL,
    `notes` TEXT,
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uuid` (`uuid`),
    KEY `idx_person_name` (`last_name`, `first_name`),
    KEY `idx_person_dob` (`birth_date`),
    KEY `idx_person_search` (`last_name`, `first_name`, `birth_date`),
    KEY `idx_person_active` (`active`)
) ENGINE=InnoDB COMMENT='Core person demographics - contact info in contact_telecom';
#EndIf


#IfNotTable contact_relation
CREATE TABLE `contact_relation` (
    `id`  BIGINT(20) NOT NULL auto_increment,
    `contact_id`  BIGINT(20) NOT NULL,
    `target_table`  VARCHAR(255) NOT NULL DEFAULT '',
    `target_id`  BIGINT(20) NOT NULL,
    `active` BOOLEAN DEFAULT TRUE,
    `role` VARCHAR(63)  DEFAULT NULL,
    `relationship` VARCHAR(63)  DEFAULT NULL,
    `contact_priority` INT DEFAULT 1 COMMENT '1=highest priority',
    `is_primary_contact` BOOLEAN DEFAULT FALSE,
    `is_emergency_contact` BOOLEAN DEFAULT FALSE,
    `can_make_medical_decisions` BOOLEAN DEFAULT FALSE,
    `can_receive_medical_info` BOOLEAN DEFAULT FALSE,
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `notes` TEXT,
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
   PRIMARY KEY (`id`),
   KEY (`contact_id`),
   INDEX idx_contact_target_table (target_table, target_id)
) ENGINE = InnoDB;
#EndIf


#IfMissingColumn contact_address created_date
ALTER TABLE `contact_address` ADD COLUMN `created_by` BIGINT(20) DEFAULT NULL COMMENT 'fk to users.id';
ALTER TABLE `contact_address` ADD COLUMN `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `contact_address` ADD COLUMN `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'fk to users.id';
#EndIf

#IfNotTable contact_telecom
CREATE TABLE `contact_telecom` (
    `id` BIGINT(20) NOT NULL auto_increment,
    `contact_id` BIGINT(20) NOT NULL,
    `rank` INT(11) NULL COMMENT 'Specify preferred order of use (1 = highest)',
    `system` VARCHAR(255) NULL
    	COMMENT 'FK to list_options.option_id for list_id telecom_systems [phone, fax, email, pager, url, sms, other]',
    `use` VARCHAR(255) NULL
    	COMMENT 'FK to list_options.option_id for list_id telecom_uses [home, work, temp, old, mobile]',
    `value` varchar(255) default NULL,
    `status` CHAR(1) NULL COMMENT 'A=active,I=inactive',
    `is_primary` CHAR(1) NULL COMMENT 'Y=yes,N=no',
    `notes` TINYTEXT,
    `period_start` DATETIME NULL COMMENT 'Date the telecom became active',
    `period_end` DATETIME NULL COMMENT 'Date the telecom became deactivated',
    `inactivated_reason` VARCHAR(45) DEFAULT NULL COMMENT '[Values: ???, etc]',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
   PRIMARY KEY (`id`),
    KEY (`contact_id`)
) ENGINE = InnoDB ;
#EndIf

#IfNotTable person_patient_link
CREATE TABLE `person_patient_link` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `person_id` BIGINT(20) NOT NULL COMMENT 'FK to person.id',
    `patient_id` BIGINT(20) NOT NULL COMMENT 'FK to patient_data.id',
    `linked_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
    `linked_by` BIGINT(20) DEFAULT NULL COMMENT 'FK to users.id - who created the link',
    `link_method` VARCHAR(50) DEFAULT 'manual' COMMENT 'How link was created: manual, auto_detected, migrated, import',
    `notes` TEXT COMMENT 'Optional notes about why/how they were linked',
    `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether link is active (allows soft delete)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_active_link` (`person_id`, `patient_id`, `active`),
    KEY `idx_ppl_person` (`person_id`),
    KEY `idx_ppl_patient` (`patient_id`),
    KEY `idx_ppl_active` (`active`),
    KEY `idx_ppl_linked_date` (`linked_date`),
    KEY `idx_ppl_method` (`link_method`)
) ENGINE=InnoDB COMMENT='Links person records to patient_data records when person becomes patient';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id additional_telecoms
    #IfRow2D layout_options form_id DEM field_id additional_addresses
    SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='additional_addresses' AND form_id='DEM');
    SET @max_seq = (SELECT max(seq) FROM layout_options WHERE group_id = @group_id AND form_id='DEM');
    UPDATE layout_options SET seq = @max_seq+19 WHERE form_id = 'DEM' AND field_id = 'additional_addresses';
    INSERT INTO `layout_options`
        (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`)
    VALUES
        ('DEM','additional_telecoms',@group_id,'',@max_seq+9,55,0,0,0,'',4,4,'','[\"J\",\"SP\"]','Additional Telecoms',0);
    #Endif
#Endif

#IfNotRow2D list_options list_id lists option_id telecom_systems
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
VALUES ('lists','telecom_systems','Telecom Systems',0, 1, 0);

INSERT INTO list_options
(list_id,option_id,title,seq,is_default,activity)
VALUES
    ('telecom_systems','PHONE','phone',10,0,1),
    ('telecom_systems','FAX','fax',20,0,1),
    ('telecom_systems','EMAIL','email',30,0,1),
    ('telecom_systems','PAGER','pager',40,0,1),
    ('telecom_systems','URL','url',50,0,1),
    ('telecom_systems','SMS','sms',60,0,1),
    ('telecom_systems','OTHER','other',70,0,1);
#EndIf

#IfNotRow2D list_options list_id lists option_id telecom_uses
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
VALUES ('lists','telecom_uses','Telecom Uses',0, 1, 0);

INSERT INTO list_options
(list_id,option_id,title,seq,is_default,activity)
VALUES
    ('telecom_uses','HOME','home',10,0,1),
    ('telecom_uses','WORK','work',20,0,1),
    ('telecom_uses','TEMP','temp',30,0,1),
    ('telecom_uses','OLD','old',40,0,1),
    ('telecom_uses','MOBILE','mobile',50,0,1);
#EndIf

#IfNotRow2D list_options list_id lists option_id person_patient_link_method
INSERT IGNORE INTO list_options
(list_id, option_id, title, seq, is_default)
VALUES
    ('lists', 'person_patient_link_method', 'Person-Patient Link Method', 1, 0);

INSERT INTO list_options
(list_id, option_id, title, seq, is_default, option_value, notes)
VALUES
    ('person_patient_link_method', 'manual', 'Manually Linked by User', 10, 1, 0, 'User explicitly linked person to patient'),
    ('person_patient_link_method', 'auto_detected', 'Auto-Detected at Registration', 20, 0, 0, 'System detected match during patient registration'),
    ('person_patient_link_method', 'migrated', 'Migrated from Legacy System', 30, 0, 0, 'Link created during data migration'),
    ('person_patient_link_method', 'import', 'Imported from External System', 40, 0, 0, 'Link created during data import'),
    ('person_patient_link_method', 'merge', 'Merged Duplicate Records', 50, 0, 0, 'Link created when merging duplicate records');
#EndIf

-- -------------------------------------------------------------------------------------------------------------------------------------------------------
-- relatedperson-relationshiptype Valuesets
-- https://terminology.hl7.org/6.5.0/ValueSet-v3-PersonalRelationshipRoleType.html

#IfNotRow2D list_options list_id lists option_id related_person_relationship
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','related_person_relationship','Related Person Relationships',0, 1, 0);

-- Spouse/Partner
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','SPS','spouse',10,0,1),
    ('related_person_relationship','HUSB','husband',20,0,1),
    ('related_person_relationship','WIFE','wife',30,0,1),
    ('related_person_relationship','DOMPART','domestic partner',40,0,1),
    ('related_person_relationship','SIGOTHR','significant other',50,0,1),
    ('related_person_relationship','FMRSPS','former spouse',60,0,1);

    -- Parents
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','PRN','parent',70,0,1),
    ('related_person_relationship','NPRN','natural parent',80,0,1),
    ('related_person_relationship','FTH','father',90,0,1),
    ('related_person_relationship','NFTH','natural father',100,0,1),
    ('related_person_relationship','MTH','mother',110,0,1),
    ('related_person_relationship','NMTH','natural mother',120,0,1),
    ('related_person_relationship','ADOPTF','adoptive father',130,0,1),
    ('related_person_relationship','ADOPTM','adoptive mother',140,0,1),
    ('related_person_relationship','ADOPTP','adoptive parent',150,0,1),
    ('related_person_relationship','FTHFOST','foster father',160,0,1),
    ('related_person_relationship','MTHFOST','foster mother',170,0,1),
    ('related_person_relationship','PRNFOST','foster parent',180,0,1),
    ('related_person_relationship','STPFTH','stepfather',190,0,1),
    ('related_person_relationship','STPMTH','stepmother',200,0,1),
    ('related_person_relationship','STPPRN','step parent',210,0,1),
    ('related_person_relationship','GESTM','gestational mother',220,0,1);

    -- Children
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','CHILD','Child',230,0,1),
    ('related_person_relationship','NCHILD','natural child',240,0,1),
    ('related_person_relationship','DAUC','daughter',250,0,1),
    ('related_person_relationship','DAU','natural daughter',260,0,1),
    ('related_person_relationship','SONC','son',270,0,1),
    ('related_person_relationship','SON','natural son',280,0,1),
    ('related_person_relationship','CHLDADOPT','Adopted Child',290,0,1),
    ('related_person_relationship','DAUADOPT','Adopted Daughter',300,0,1),
    ('related_person_relationship','SONADOPT','Adopted Son',310,0,1),
    ('related_person_relationship','CHLDFOST','Foster Child',320,0,1),
    ('related_person_relationship','DAUFOST','foster daughter',330,0,1),
    ('related_person_relationship','SONFOST','foster son',340,0,1),
    ('related_person_relationship','STPCHLD','step child',350,0,1),
    ('related_person_relationship','STPDAU','stepdaughter',360,0,1),
    ('related_person_relationship','STPSON','stepson',370,0,1);

    -- Siblings
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','SIB','sibling',380,0,1),
    ('related_person_relationship','NSIB','natural sibling',390,0,1),
    ('related_person_relationship','BRO','brother',400,0,1),
    ('related_person_relationship','NBRO','natural brother',410,0,1),
    ('related_person_relationship','SIS','sister',420,0,1),
    ('related_person_relationship','NSIS','natural sister',430,0,1),
    ('related_person_relationship','HBRO','half-brother',440,0,1),
    ('related_person_relationship','HSIS','half-sister',450,0,1),
    ('related_person_relationship','HSIB','half-sibling',460,0,1),
    ('related_person_relationship','STPBRO','stepbrother',470,0,1),
    ('related_person_relationship','STPSIS','stepsister',480,0,1),
    ('related_person_relationship','STPSIB','step sibling',490,0,1),
    ('related_person_relationship','TWIN','twin',500,0,1),
    ('related_person_relationship','TWINBRO','twin brother',510,0,1),
    ('related_person_relationship','TWINSIS','twin sister',520,0,1),
    ('related_person_relationship','FTWIN','fraternal twin',530,0,1),
    ('related_person_relationship','FTWINBRO','fraternal twin brother',540,0,1),
    ('related_person_relationship','FTWINSIS','fraternal twin sister',550,0,1),
    ('related_person_relationship','ITWIN','identical twin',560,0,1),
    ('related_person_relationship','ITWINBRO','identical twin brother',570,0,1),
    ('related_person_relationship','ITWINSIS','identical twin sister',580,0,1);

    -- Grandparents
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','GRPRN','grandparent',590,0,1),
    ('related_person_relationship','GRFTH','grandfather',600,0,1),
    ('related_person_relationship','GRMTH','grandmother',610,0,1),
    ('related_person_relationship','MGRPRN','maternal grandparent',620,0,1),
    ('related_person_relationship','MGRFTH','maternal grandfather',630,0,1),
    ('related_person_relationship','MGRMTH','maternal grandmother',640,0,1),
    ('related_person_relationship','PGRPRN','paternal grandparent',650,0,1),
    ('related_person_relationship','PGRFTH','paternal grandfather',660,0,1),
    ('related_person_relationship','PGRMTH','paternal grandmother',670,0,1);

    -- Great Grandparents
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','GGRPRN','great grandparent',680,0,1),
    ('related_person_relationship','GGRFTH','great grandfather',690,0,1),
    ('related_person_relationship','GGRMTH','great grandmother',700,0,1),
    ('related_person_relationship','MGGRPRN','maternal great-grandparent',710,0,1),
    ('related_person_relationship','MGGRFTH','maternal great-grandfather',720,0,1),
    ('related_person_relationship','MGGRMTH','maternal great-grandmother',730,0,1),
    ('related_person_relationship','PGGRPRN','paternal great-grandparent',740,0,1),
    ('related_person_relationship','PGGRFTH','paternal great-grandfather',750,0,1),
    ('related_person_relationship','PGGRMTH','paternal great-grandmother',760,0,1);

    -- Grandchildren
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','GRNDCHILD','grandchild',770,0,1),
    ('related_person_relationship','GRNDDAU','granddaughter',780,0,1),
    ('related_person_relationship','GRNDSON','grandson',790,0,1);

    -- Extended Family
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','FAMMEMB','Family Member',800,0,1),
    ('related_person_relationship','EXT','extended family member',810,0,1),
    ('related_person_relationship','AUNT','aunt',820,0,1),
    ('related_person_relationship','MAUNT','maternal aunt',830,0,1),
    ('related_person_relationship','PAUNT','paternal aunt',840,0,1),
    ('related_person_relationship','UNCLE','uncle',850,0,1),
    ('related_person_relationship','MUNCLE','maternal uncle',860,0,1),
    ('related_person_relationship','PUNCLE','paternal uncle',870,0,1),
    ('related_person_relationship','COUSN','maternal cousin',880,0,1),
    ('related_person_relationship','MCOUSN','maternal cousin',890,0,1),
    ('related_person_relationship','PCOUSN','paternal cousin',900,0,1),
    ('related_person_relationship','NEPHEW','nephew',910,0,1),
    ('related_person_relationship','NIECE','niece',920,0,1);

    -- In-Laws
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','INLAW','inlaw',930,0,1),
    ('related_person_relationship','PRNINLAW','parent in-law',940,0,1),
    ('related_person_relationship','FTHINLAW','father-in-law',950,0,1),
    ('related_person_relationship','MTHINLAW','mother-in-law',960,0,1),
    ('related_person_relationship','SIBINLAW','sibling in-law',970,0,1),
    ('related_person_relationship','BROINLAW','brother-in-law',980,0,1),
    ('related_person_relationship','SISINLAW','sister-in-law',990,0,1),
    ('related_person_relationship','DAUINLAW','daughter in-law',1000,0,1),
    ('related_person_relationship','SONINLAW','son in-law',1010,0,1);

    -- Legal/Guardian Relationships
    -- INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    -- ('related_person_relationship','GUADLTM','guardian ad lidem',1030,0,1),
    -- ('related_person_relationship','SPOWATT','special power of attorney',1050,0,1);

    -- Other Relationships
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','FRND','unrelated friend',1070,0,1),
    ('related_person_relationship','NBOR','neighbor',1080,0,1),
    ('related_person_relationship','ROOM','Roommate',1090,0,1);

    -- Self
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES
    ('related_person_relationship','ONESELF','self',1100,0,1);
#Endif


-- -------------------------------------------------------------------------------------------------------------------------------------------------------
-- relatedperson-relationshiptype Valuesets
-- https://build.fhir.org/valueset-relatedperson-relationshiptype.html

#IfNotRow2D list_options list_id lists option_id related_person_role
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','related_person_role','Related Person Role',0, 1, 0);

INSERT INTO list_options
    (list_id,option_id,title,seq,is_default,activity)
VALUES
    ('related_person_role','ECON','Emergency Contact',10,0,1),
    ('related_person_role','NOK','Next of Kin',20,0,1),
    ('related_person_role','GUARD','Guardian',30,0,1),
    ('related_person_role','DEPEN','Dependent',40,0,1),
    ('related_person_role','CON','contact',50,0,1),
    ('related_person_role','EMP','Employee',60,0,1),
    ('related_person_role','GUAR','Guarantor',70,0,1),
    ('related_person_role','CAREGIVER','Caregiver',80,0,1),
    ('related_person_role','POWATT','Power of Attorney',90,0,1),
    ('related_person_role','DPOWATT','Durable Power of Attorney',100,0,1),
    ('related_person_role','HPOWATT','Healthcare Power of Attorney',110,0,1),
    ('related_person_role','BILL','Billing Contact',120,0,1),
    ('related_person_role','E','Employer',130,0,1),
    ('related_person_role','POLHOLD','Policy Holder',140,0,1),
    ('related_person_role','PAYEE','Payee',150,0,1),
    ('related_person_role','NOT','Notary Public',160,0,1),
    ('related_person_role','PROV','Healthcare Provider',170,0,1),
    ('related_person_role','WIT','Witness',180,0,1),
    ('related_person_role','O','Other',190,0,1),
    ('related_person_role','U','Unknown',200,0,1);
#EndIf

#IfTable patient_related_persons
ALTER TABLE `person` ADD COLUMN `pid` BIGINT(20) DEFAULT NULL COMMENT 'Temporary column to hold patient_data.pid during migration';
ALTER TABLE `person` ADD COLUMN `is_new` TINYINT(1) DEFAULT 1 COMMENT 'Flag to indicate if record is newly created during migration';
-- we need to migrate the data over for the patient_related_persons table
CREATE TEMPORARY TABLE `person_temp` AS SELECT * FROM patient_related_persons WHERE related_firstname_1 IS NOT NULL AND related_firstname_1 != '';

CREATE TEMPORARY TABLE `person_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `person_seq` (`pid`) SELECT pe.pid FROM person_temp pe;
INSERT INTO `person` (`first_name`, `last_name`, `gender`,`pid`) SELECT related_firstname_1, related_lastname_1, `related_sex_1`,`pid` FROM person_temp;
UPDATE `person` JOIN person_seq ON person.pid = person_seq.pid CROSS JOIN sequences ids SET person.id = person_seq.id+ids.id WHERE person.is_new = 1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM person);
DROP TEMPORARY TABLE `person_seq`;

-- add patient_data contacts if needed
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'patient_data', new_pids.pid FROM (SELECT p.pid FROM person p LEFT JOIN contact c ON p.pid = c.foreign_id AND c.foreign_table_name='patient_data' WHERE c.id IS NULL AND p.is_new=1) new_pids;
INSERT INTO `contact_relation` (`contact_id`, `target_table`, `target_id`, `relationship`) SELECT c.id, 'person', pe.id, p.related_relationship_1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name = 'patient_data' AND c.foreign_id = pe.pid  AND pe.is_new=1;

-- add person contacts
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'person', pe.id FROM person pe WHERE pe.is_new=1;

-- we can only do this because persons are a brand new table
CREATE TEMPORARY TABLE `addresses_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `addresses_seq` (`pid`) SELECT pe.pid FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND p.pid = pe.pid WHERE pe.is_new=1;
UPDATE addresses_seq CROSS JOIN sequences s SET addresses_seq.id = addresses_seq.id + s.id;
INSERT INTO addresses (`id`, `line1`, `city`, `state`, `zip`, `country`, `foreign_id`) SELECT addr.id, p.related_address_1, p.related_city_1, p.related_state_1, p.related_postalcode_1,p.related_country_1, c.id FROM person_temp p JOIN addresses_seq addr ON p.pid=addr.pid JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name='person' AND c.foreign_id=pe.id CROSS JOIN sequences s WHERE p.related_city_1 IS NOT NULL AND p.related_city_1 != '' AND pe.is_new=1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM addresses_seq);
DROP TEMPORARY TABLE `addresses_seq`;

-- address_id = contact -> person
INSERT INTO `contact_address` (`contact_id`, `address_id`, `type`, `use`, `is_primary`,`status`) SELECT c.id, a.id, 'home', 'home', 'Y', 'A' FROM person pe JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id JOIN addresses a ON a.foreign_id = c.id WHERE pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'home', p.related_phone_1, 'Y', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_phone_1 IS NOT NULL AND p.related_phone_1 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'work', p.related_workphone_1, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_workphone_1 IS NOT NULL AND p.related_workphone_1 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'EMAIL', 'home', p.related_email_1, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_1 AND pe.last_name = p.related_lastname_1 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_email_1 IS NOT NULL AND p.related_email_1 != ''  AND pe.is_new=1;

UPDATE `person` SET `is_new` = 0 WHERE pid IS NOT NULL;
DROP TEMPORARY TABLE `person_temp`;

-- now handle related person 2
CREATE TEMPORARY TABLE `person_temp` AS SELECT * FROM patient_related_persons WHERE related_firstname_2 IS NOT NULL AND related_firstname_2 != '';

CREATE TEMPORARY TABLE `person_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `person_seq` (`pid`) SELECT pe.pid FROM person_temp pe;
INSERT INTO `person` (`first_name`, `last_name`, `gender`,`pid`) SELECT related_firstname_2, related_lastname_2, `related_sex_2`,`pid` FROM person_temp;
UPDATE `person` JOIN person_seq ON person.pid = person_seq.pid CROSS JOIN sequences ids SET person.id = person_seq.id+ids.id WHERE person.is_new = 1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM person);
DROP TEMPORARY TABLE `person_seq`;

-- add patient_data contacts if needed
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'patient_data', new_pids.pid FROM (SELECT p.pid FROM person p LEFT JOIN contact c ON p.pid = c.foreign_id AND c.foreign_table_name='patient_data' WHERE c.id IS NULL AND p.is_new=1) new_pids;
INSERT INTO `contact_relation` (`contact_id`, `target_table`, `target_id`, `relationship`) SELECT c.id, 'person', pe.id, p.related_relationship_2 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name = 'patient_data' AND c.foreign_id = pe.pid  AND pe.is_new=1;

-- add person contacts
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'person', pe.id FROM person pe WHERE pe.is_new=1;

-- we can only do this because persons are a brand new table
CREATE TEMPORARY TABLE `addresses_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `addresses_seq` (`pid`) SELECT pe.pid FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND p.pid = pe.pid WHERE pe.is_new=1;
UPDATE addresses_seq CROSS JOIN sequences s SET addresses_seq.id = addresses_seq.id + s.id;
INSERT INTO addresses (`id`, `line1`, `city`, `state`, `zip`, `country`, `foreign_id`) SELECT addr.id, p.related_address_2, p.related_city_2, p.related_state_2, p.related_postalcode_2,p.related_country_2, c.id FROM person_temp p JOIN addresses_seq addr ON p.pid=addr.pid JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name='person' AND c.foreign_id=pe.id CROSS JOIN sequences s WHERE p.related_city_2 IS NOT NULL AND p.related_city_2 != '' AND pe.is_new=1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM addresses_seq);
DROP TEMPORARY TABLE `addresses_seq`;

-- address_id = contact -> person
INSERT INTO `contact_address` (`contact_id`, `address_id`, `type`, `use`, `is_primary`,`status`) SELECT c.id, a.id, 'home', 'home', 'Y', 'A' FROM person pe JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id JOIN addresses a ON a.foreign_id = c.id WHERE pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'home', p.related_phone_2, 'Y', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_phone_2 IS NOT NULL AND p.related_phone_2 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'work', p.related_workphone_2, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_workphone_2 IS NOT NULL AND p.related_workphone_2 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'EMAIL', 'home', p.related_email_2, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_2 AND pe.last_name = p.related_lastname_2 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_email_2 IS NOT NULL AND p.related_email_2 != ''  AND pe.is_new=1;

UPDATE `person` SET `is_new` = 0 WHERE pid IS NOT NULL;
DROP TEMPORARY TABLE `person_temp`;

-- now handle related person 3
CREATE TEMPORARY TABLE `person_temp` AS SELECT * FROM patient_related_persons WHERE related_firstname_3 IS NOT NULL AND related_firstname_3 != '';

CREATE TEMPORARY TABLE `person_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `person_seq` (`pid`) SELECT pe.pid FROM person_temp pe;
INSERT INTO `person` (`first_name`, `last_name`, `gender`,`pid`) SELECT related_firstname_3, related_lastname_3, `related_sex_3`,`pid` FROM person_temp;
UPDATE `person` JOIN person_seq ON person.pid = person_seq.pid CROSS JOIN sequences ids SET person.id = person_seq.id+ids.id WHERE person.is_new = 1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM person);
DROP TEMPORARY TABLE `person_seq`;

-- add patient_data contacts if needed
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'patient_data', new_pids.pid FROM (SELECT p.pid FROM person p LEFT JOIN contact c ON p.pid = c.foreign_id AND c.foreign_table_name='patient_data' WHERE c.id IS NULL AND p.is_new=1) new_pids;
INSERT INTO `contact_relation` (`contact_id`, `target_table`, `target_id`, `relationship`) SELECT c.id, 'person', pe.id, p.related_relationship_3 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name = 'patient_data' AND c.foreign_id = pe.pid  AND pe.is_new=1;

-- add person contacts
INSERT INTO `contact` (`foreign_table_name`, `foreign_id`) SELECT 'person', pe.id FROM person pe WHERE pe.is_new=1;

-- we can only do this because persons are a brand new table
CREATE TEMPORARY TABLE `addresses_seq` (`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, `pid` BIGINT(20) NOT NULL) ENGINE=InnoDB;
INSERT INTO `addresses_seq` (`pid`) SELECT pe.pid FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND p.pid = pe.pid WHERE pe.is_new=1;
UPDATE addresses_seq CROSS JOIN sequences s SET addresses_seq.id = addresses_seq.id + s.id;
INSERT INTO addresses (`id`, `line1`, `city`, `state`, `zip`, `country`, `foreign_id`) SELECT addr.id, p.related_address_3, p.related_city_3, p.related_state_3, p.related_postalcode_3,p.related_country_3, c.id FROM person_temp p JOIN addresses_seq addr ON p.pid=addr.pid JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND p.pid = pe.pid JOIN contact c ON c.foreign_table_name='person' AND c.foreign_id=pe.id CROSS JOIN sequences s WHERE p.related_city_3 IS NOT NULL AND p.related_city_3 != '' AND pe.is_new=1;
UPDATE `sequences` SET `id` = (SELECT MAX(id)+1 FROM addresses_seq);
DROP TEMPORARY TABLE `addresses_seq`;

-- address_id = contact -> person
INSERT INTO `contact_address` (`contact_id`, `address_id`, `type`, `use`, `is_primary`,`status`) SELECT c.id, a.id, 'home', 'home', 'Y', 'A' FROM person pe JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id JOIN addresses a ON a.foreign_id = c.id WHERE pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'home', p.related_phone_3, 'Y', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_phone_3 IS NOT NULL AND p.related_phone_3 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'PHONE', 'work', p.related_workphone_3, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_workphone_3 IS NOT NULL AND p.related_workphone_3 != ''  AND pe.is_new=1;
INSERT INTO `contact_telecom` (`contact_id`, `system`, `use`, `value`, `is_primary`, `status`, `rank`) SELECT c.id, 'EMAIL', 'home', p.related_email_3, 'N', 'A', 1 FROM person_temp p JOIN person pe ON pe.first_name = p.related_firstname_3 AND pe.last_name = p.related_lastname_3 AND pe.pid = p.pid JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = pe.id WHERE p.related_email_3 IS NOT NULL AND p.related_email_3 != ''  AND pe.is_new=1;

UPDATE `person` SET `is_new` = 0 WHERE pid IS NOT NULL;
DROP TEMPORARY TABLE `person_temp`;

DROP TABLE IF EXISTS patient_related_persons;
ALTER TABLE `person` DROP COLUMN `pid`, DROP COLUMN `is_new`;
#EndIf

#IfRow2D layout_options form_id DEM field_id related_firstname_1
DELETE FROM layout_options WHERE form_id='DEM' AND field_id IN ('related_firstname_1','related_lastname_1','related_relationship_1','related_sex_1','related_address_1','related_city_1','related_state_1','related_postalcode_1','related_country_1','related_phone_1','related_workphone_1','related_email_1','related_static_1');
DELETE FROM layout_options WHERE form_id='DEM' AND field_id IN ('related_firstname_2','related_lastname_2','related_relationship_2','related_sex_2','related_address_2','related_city_2','related_state_2','related_postalcode_2','related_country_2','related_phone_2','related_workphone_2','related_email_2','related_static_2');
DELETE FROM layout_options WHERE form_id='DEM' AND field_id IN ('related_firstname_3','related_lastname_3','related_relationship_3','related_sex_3','related_address_3','related_city_3','related_state_3','related_postalcode_3','related_country_3','related_phone_3','related_workphone_3','related_email_3','related_static_3');
-- clear out the Related group if it exists so we can re-add it
DELETE FROM layout_group_properties WHERE  grp_form_id = 'DEM' AND grp_title = 'Related';
#EndIf


#IfRow3D layout_options form_id DEM field_id guardiansname title Name
UPDATE layout_options SET title = 'Guardian Name' WHERE form_id = 'DEM' AND field_id = 'guardiansname';
#Endif

#IfRow2D layout_group_properties grp_form_id DEM grp_title Guardian
SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='guardianemail' AND form_id='DEM');
UPDATE layout_group_properties SET grp_title = 'Related' WHERE grp_title = 'Guardian' AND grp_form_id = 'DEM' AND grp_group_id = @group_id;
#Endif

#IfNotRow2D layout_group_properties grp_form_id DEM grp_title Related
SET @group_id = (SELECT max(`grp_group_id`)+1 FROM layout_group_properties WHERE grp_form_id='DEM');
INSERT INTO layout_group_properties
(grp_form_id, grp_group_id, grp_title, grp_mapping)
VALUES
    ('DEM', @group_id, 'Related','');
#Endif


#IfNotRow2D layout_options form_id DEM field_id related_persons
-- Add Related Persons field to DEM form under Related group, if there are duplicates we add to the end
SET @group_id = (SELECT max(`grp_group_id`) FROM layout_group_properties WHERE grp_form_id='DEM' AND grp_title='Related');
SET @seq_add_to = (SELECT max(seq) FROM layout_options WHERE group_id = @group_id AND form_id='DEM');
INSERT INTO `layout_options`
(`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`)
VALUES
    ('DEM','related_persons',@group_id,'',@seq_add_to+1,56,1,0,0,'',4,4,'','["J","SP"]','Related Persons',0);
#Endif

#IfNotIndex patient_data idx_patient_name
CREATE INDEX idx_patient_name ON patient_data(lname, fname);
#EndIf

-- Catch up new table structure
#IfColumn care_teams user_id
ALTER TABLE `care_teams`
    DROP `user_id`,
    DROP `role`,
    DROP `facility_id`,
    DROP `provider_since`,
    CHANGE `status` `status` VARCHAR(100);
#EndIf

#IfMissingColumn care_teams created_by
ALTER TABLE `care_teams`
    ADD `created_by` BIGINT(20) DEFAULT NULL,
    ADD  `updated_by` BIGINT(20) DEFAULT NULL;
#EndIf

#IfNotIndex patient_data idx_patient_dob
CREATE INDEX idx_patient_dob ON patient_data(DOB);
#EndIf

#IfNotTable care_team_member
CREATE TABLE `care_team_member` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `care_team_id` int(11) NOT NULL,
    `user_id` BIGINT(20) COMMENT 'fk to users.id represents a provider or staff member',
    `contact_id` BIGINT(20) COMMENT 'fk to contact.id which represents a contact person not in users or facility table',
    `role` varchar(50) NOT NULL COMMENT 'fk to list_options.option_id WHERE list_id=care_team_roles',
    `facility_id` BIGINT(20) COMMENT 'fk to facility.id represents an organization or location',
    `provider_since` date NULL,
    `status` varchar(100) DEFAULT 'active' COMMENT 'fk to list_options.option_id where list_id=Care_Team_Status',
    `date_created` datetime DEFAULT current_timestamp(),
    `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_by` BIGINT(20) COMMENT 'fk to users.id and is the user that added this team member',
    `updated_by` BIGINT(20) COMMENT 'fk to users.id and is the user that last updated this team member',
    `note` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `care_team_member_unique` (`care_team_id`, `user_id`, `facility_id`, `contact_id`)
) ENGINE=InnoDB COMMENT='Stores members of a care team for a patient';
#EndIf

-- ----------------------------------------------------------------------- sjp 11/10/2025 --------------------------------------------------------------
-- Enhanced Patient Preferences Schema for USCDI v5 / US Core 8.0
-- Additions to existing schema with more comprehensive value sets

-- ========================================
-- Additional Treatment Intervention Preferences
-- ========================================
#IfNotRow2D list_options list_id treatment_intervention_preferences option_id 75773-2
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`notes`,`codes`,`activity`) VALUES
    ('treatment_intervention_preferences','75773-2','Goals, preferences, and priorities for medical treatment [Reported]',5,'tip_general_answers','LOINC:75773-2',1),
    ('treatment_intervention_preferences','81336-0','Patient''s thoughts on cardiopulmonary bypass',60,'tip_bypass_answers','LOINC:81336-0',1),
    ('treatment_intervention_preferences','81337-8','Patient''s thoughts on mechanical ventilation',70,'tip_ventilation_answers','LOINC:81337-8',1),
    ('treatment_intervention_preferences','81376-6','Upon death organ donation consent',80,'tip_organ_donation_answers','LOINC:81376-6',1),
    ('treatment_intervention_preferences','81378-2','Patient Healthcare goals',90,'tip_healthcare_goals_text','LOINC:81378-2',1);
#EndIf
-- ========================================
-- Additional Care Experience Preferences
-- ========================================
#IfNotRow2D list_options list_id care_experience_preferences option_id 81342-8
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`notes`,`codes`,`activity`) VALUES
    ('care_experience_preferences','81342-8','Care experience preference under certain health conditions',50,'cep_conditional_answers','LOINC:81342-8',1),
    ('care_experience_preferences','81343-6','Care experience preference at end of life',60,'cep_endoflife_answers','LOINC:81343-6',1),
    ('care_experience_preferences','81362-6','Preferred location for healthcare',70,'cep_location_answers','LOINC:81362-6',1),
    ('care_experience_preferences','81363-4','Preferred healthcare professional',80,'cep_professional_answers','LOINC:81363-4',1);
#EndIf
-- ========================================
-- Enhanced Value Sets
-- ========================================
-- General Goals/Preferences (75773-2 and general use)
#IfNotRow preference_value_sets answer_code 385643006
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('75773-2','385643006','http://snomed.info/sct','Prefers full resuscitation',1,1),
('75773-2','385644000','http://snomed.info/sct','Prefers limited resuscitation',2,1),
('75773-2','304253006','http://snomed.info/sct','Does not want resuscitation',3,1),
('75773-2','395092004','http://snomed.info/sct','Prefers aggressive treatment',4,1),
('75773-2','395093009','http://snomed.info/sct','Prefers comfort measures only',5,1),
('75773-2','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other',99,1);

-- Cardiopulmonary Bypass (81336-0)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81336-0','373066001','http://snomed.info/sct','Yes',1,1),
('81336-0','373067005','http://snomed.info/sct','No',2,1),
('81336-0','261665006','http://snomed.info/sct','Unknown',98,1),
('81336-0','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1);

-- Mechanical Ventilation (81337-8)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81337-8','LA33470-8','http://loinc.org','Yes ventilation',1,1),
('81337-8','LA33471-6','http://loinc.org','No ventilation',2,1),
('81337-8','LA32996-3','http://loinc.org','Trial period of ventilation',3,1),
('81337-8','UNK','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Unknown',99,1);

-- Organ Donation (81376-6)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81376-6','LA33-6','http://loinc.org','Yes',1,1),
('81376-6','LA32-8','http://loinc.org','No',2,1),
('81376-6','LA32948-4','http://loinc.org','Yes, but only certain organs/tissues',3,1),
('81376-6','LA4489-6','http://loinc.org','Unknown',99,1);

-- Care Under Certain Health Conditions (81342-8)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81342-8','LA33474-0','http://loinc.org','If mentally incapacitated',1,1),
('81342-8','LA33475-7','http://loinc.org','If terminally ill',2,1),
('81342-8','LA33476-5','http://loinc.org','If permanently unconscious',3,1),
('81342-8','LA33477-3','http://loinc.org','If severe chronic illness',4,1),
('81342-8','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other condition',99,1);

-- Care at End of Life (81343-6)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81343-6','395092004','http://snomed.info/sct','Prefers aggressive treatment',1,1),
('81343-6','395093009','http://snomed.info/sct','Prefers comfort measures only',2,1),
('81343-6','385644000','http://snomed.info/sct','Limited intervention',3,1),
('81343-6','225270000','http://snomed.info/sct','Hospice care',4,1),
('81343-6','385656005','http://snomed.info/sct','Home death preferred',5,1),
('81343-6','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other',99,1);

-- Preferred Location (81362-6)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81362-6','264362003','http://snomed.info/sct','Home',1,1),
('81362-6','22232009','http://snomed.info/sct','Hospital',2,1),
('81362-6','284546000','http://snomed.info/sct','Hospice',3,1),
('81362-6','42665001','http://snomed.info/sct','Nursing home',4,1),
('81362-6','413456002','http://snomed.info/sct','Adult day care center',5,1),
('81362-6','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other location',99,1);

-- Preferred Healthcare Professional (81363-4)
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81363-4','309343006','http://snomed.info/sct','Physician',1,1),
('81363-4','106292003','http://snomed.info/sct','Professional nurse',2,1),
('81363-4','224571005','http://snomed.info/sct','Nurse practitioner',3,1),
('81363-4','449161006','http://snomed.info/sct','Physician assistant',4,1),
('81363-4','768730001','http://snomed.info/sct','Home health aide',5,1),
('81363-4','OTH','http://terminology.hl7.org/CodeSystem/v3-NullFlavor','Other provider',99,1);

-- ========================================
-- Additional Common Answer Values
-- ========================================
-- Add more religious/cultural options
INSERT INTO `preference_value_sets`
(`loinc_code`,`answer_code`,`answer_system`,`answer_display`,`sort_order`,`active`) VALUES
('81364-2','309884000','http://snomed.info/sct','Atheist',7,1),
('81364-2','160234004','http://snomed.info/sct','Agnostic',8,1),
('81364-2','428821008','http://snomed.info/sct','Latter Day Saints',9,1),
('81364-2','80587008','http://snomed.info/sct','Jehovah''s Witness',10,1),
('81364-2','309687009','http://snomed.info/sct','Baptist',11,1),
('81364-2','160540005','http://snomed.info/sct','Sikh',12,1),
('81364-2','LA14063-6','http://loinc.org','Prefer not to answer',98,1);
#EndIf
