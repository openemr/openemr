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
  `pid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `provider_since` date NULL,
  `status` varchar(20) DEFAULT 'active',
  `team_name` varchar(255) DEFAULT NULL,
  `note` text,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB;
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
-- ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Observation Form Changes

-- Fix the issue that we don't have a primary key on the form_observation table
#IfMissingColumn form_observation form_id
ALTER TABLE `form_observation` RENAME COLUMN `id` TO `form_id`;
ALTER TABLE `form_observation` ADD COLUMN `id` BIGINT(20) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`);
#EndIf

#IfMissingColumn form_observation parent_observation_id
ALTER TABLE `form_observation` ADD `parent_observation_id` bigint(20) DEFAULT NULL COMMENT 'FK to parent observation for sub-observations';
#EndIf

#IfMissingColumn form_observation category
ALTER TABLE `form_observation` ADD `category` varchar(64) DEFAULT NULL COMMENT 'FK to list_options.option_id for observation category (SDOH, Functional, Cognitive, Physical, etc)';
#EndIf

#IfMissingColumn form_observation questionnaire_response_id
ALTER TABLE `form_observation` ADD `questionnaire_response_id` bigint(21) DEFAULT NULL COMMENT 'FK to questionnaire_response table';
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
ALTER TABLE `form_observation` ADD INDEX  `idx_pid_encounter` (`pid`, `encounter`);
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
#IfNotRow3D list_options list_id observation_value_types option_id physical_exam_performed activity 0
UPDATE `list_options` SET `activity`=0 WHERE `list_id`='Observation_Types' AND `option_id`='physical_exam_performed';
#EndIf

#IfNotRow3D list_options list_id observation_value_types option_id procedure_diagnostic activity 0
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
