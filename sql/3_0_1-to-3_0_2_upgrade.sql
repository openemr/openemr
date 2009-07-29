#IfMissingColumn pnotes deleted
ALTER TABLE `pnotes` 
    ADD `deleted` TINYINT DEFAULT '0' COMMENT 'flag indicates note is deleted';
#EndIf

#IfNotRow list_options list_id adjreason
INSERT INTO list_options VALUES ('lists'    ,'adjreason'      ,'Adjustment Reasons',1,0,0);
INSERT INTO list_options VALUES ('adjreason','Adm adjust'     ,'Adm adjust'     , 5,0,0);
INSERT INTO list_options VALUES ('adjreason','After hrs calls','After hrs calls',10,0,0);
INSERT INTO list_options VALUES ('adjreason','Bad check'      ,'Bad check'      ,15,0,0);
INSERT INTO list_options VALUES ('adjreason','Bad debt'       ,'Bad debt'       ,20,0,0);
INSERT INTO list_options VALUES ('adjreason','Coll w/o'       ,'Coll w/o'       ,25,0,0);
INSERT INTO list_options VALUES ('adjreason','Discount'       ,'Discount'       ,30,0,0);
INSERT INTO list_options VALUES ('adjreason','Hardship w/o'   ,'Hardship w/o'   ,35,0,0);
INSERT INTO list_options VALUES ('adjreason','Ins adjust'     ,'Ins adjust'     ,40,0,0);
INSERT INTO list_options VALUES ('adjreason','Ins bundling'   ,'Ins bundling'   ,45,0,0);
INSERT INTO list_options VALUES ('adjreason','Ins overpaid'   ,'Ins overpaid'   ,50,0,0);
INSERT INTO list_options VALUES ('adjreason','Ins refund'     ,'Ins refund'     ,55,0,0);
INSERT INTO list_options VALUES ('adjreason','Pt overpaid'    ,'Pt overpaid'    ,60,0,0);
INSERT INTO list_options VALUES ('adjreason','Pt refund'      ,'Pt refund'      ,65,0,0);
INSERT INTO list_options VALUES ('adjreason','Pt released'    ,'Pt released'    ,70,0,0);
INSERT INTO list_options VALUES ('adjreason','Sm debt w/o'    ,'Sm debt w/o'    ,75,0,0);
INSERT INTO list_options VALUES ('adjreason','To copay'       ,'To copay'       ,80,0,0);
INSERT INTO list_options VALUES ('adjreason','To ded\'ble'    ,'To ded\'ble'    ,85,0,0);
INSERT INTO list_options VALUES ('adjreason','Untimely filing','Untimely filing',90,0,0);
#EndIf

#IfNotRow list_options list_id sub_relation
INSERT INTO list_options VALUES ('lists'       ,'sub_relation','Subscriber Relationship',18,0,0);
INSERT INTO list_options VALUES ('sub_relation','self'        ,'Self'                   , 1,0,0);
INSERT INTO list_options VALUES ('sub_relation','spouse'      ,'Spouse'                 , 2,0,0);
INSERT INTO list_options VALUES ('sub_relation','child'       ,'Child'                  , 3,0,0);
INSERT INTO list_options VALUES ('sub_relation','other'       ,'Other'                  , 4,0,0);
#EndIf

#IfNotRow list_options list_id occurrence
INSERT INTO list_options VALUES ('lists'     ,'occurrence','Occurrence'                  ,10,0,0);
INSERT INTO list_options VALUES ('occurrence','0'         ,'Unknown or N/A'              , 5,0,0);
INSERT INTO list_options VALUES ('occurrence','1'         ,'First'                       ,10,0,0);
INSERT INTO list_options VALUES ('occurrence','6'         ,'Early Recurrence (<2 Mo)'    ,15,0,0);
INSERT INTO list_options VALUES ('occurrence','7'         ,'Late Recurrence (2-12 Mo)'   ,20,0,0);
INSERT INTO list_options VALUES ('occurrence','8'         ,'Delayed Recurrence (> 12 Mo)',25,0,0);
INSERT INTO list_options VALUES ('occurrence','4'         ,'Chronic/Recurrent'           ,30,0,0);
INSERT INTO list_options VALUES ('occurrence','5'         ,'Acute on Chronic'            ,35,0,0);
#EndIf

#IfNotRow list_options list_id outcome
INSERT INTO list_options VALUES ('lists'  ,'outcome','Outcome'         ,10,0,0);
INSERT INTO list_options VALUES ('outcome','0'      ,'Unassigned'      , 2,0,0);
INSERT INTO list_options VALUES ('outcome','1'      ,'Resolved'        , 5,0,0);
INSERT INTO list_options VALUES ('outcome','2'      ,'Improved'        ,10,0,0);
INSERT INTO list_options VALUES ('outcome','3'      ,'Status quo'      ,15,0,0);
INSERT INTO list_options VALUES ('outcome','4'      ,'Worse'           ,20,0,0);
INSERT INTO list_options VALUES ('outcome','5'      ,'Pending followup',25,0,0);
#EndIf

#IfNotRow list_options list_id note_type
INSERT INTO list_options VALUES ('lists'    ,'note_type'      ,'Patient Note Types',10,0,0);
INSERT INTO list_options VALUES ('note_type','Unassigned'     ,'Unassigned'        , 1,0,0);
INSERT INTO list_options VALUES ('note_type','Chart Note'     ,'Chart Note'        , 2,0,0);
INSERT INTO list_options VALUES ('note_type','Insurance'      ,'Insurance'         , 3,0,0);
INSERT INTO list_options VALUES ('note_type','New Document'   ,'New Document'      , 4,0,0);
INSERT INTO list_options VALUES ('note_type','Pharmacy'       ,'Pharmacy'          , 5,0,0);
INSERT INTO list_options VALUES ('note_type','Prior Auth'     ,'Prior Auth'        , 6,0,0);
INSERT INTO list_options VALUES ('note_type','Referral'       ,'Referral'          , 7,0,0);
INSERT INTO list_options VALUES ('note_type','Test Scheduling','Test Scheduling'   , 8,0,0);
INSERT INTO list_options VALUES ('note_type','Bill/Collect'   ,'Bill/Collect'      , 9,0,0);
INSERT INTO list_options VALUES ('note_type','Other'          ,'Other'             ,10,0,0);
#EndIf

#IfNotRow list_options list_id immunizations
INSERT INTO list_options VALUES ('lists','immunizations','Immunizations',8,0,0);
INSERT INTO list_options (list_id,option_id,title) SELECT 'immunizations',id,name FROM immunization ORDER BY name;
DROP TABLE immunization;
#EndIf

#IfNotRow2D list_options list_id drug_form option_id 0
INSERT INTO list_options VALUES ('drug_form'    ,'0' ,''         , 0,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_form option_id 10
INSERT INTO list_options VALUES ('drug_form'    ,'10','cream'    ,10,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_form option_id 11
INSERT INTO list_options VALUES ('drug_form'    ,'11','ointment' ,11,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_units option_id 0
INSERT INTO list_options VALUES ('drug_units'   ,'0' ,''         , 0,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_route option_id 0
INSERT INTO list_options VALUES ('drug_route'   ,'0' ,''         , 0,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_route option_id 13
INSERT INTO list_options VALUES ('drug_route'   ,'13','Both Ears',13,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_route option_id 14
INSERT INTO list_options VALUES ('drug_route'   ,'14','Left Ear' ,14,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_route option_id 15
INSERT INTO list_options VALUES ('drug_route'   ,'15','Right Ear',15,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 0
INSERT INTO list_options VALUES ('drug_interval','0' ,''         , 0,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 10
INSERT INTO list_options VALUES ('drug_interval','10','a.c.'     ,10,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 11
INSERT INTO list_options VALUES ('drug_interval','11','p.c.'     ,11,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 12
INSERT INTO list_options VALUES ('drug_interval','12','a.m.'     ,12,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 13
INSERT INTO list_options VALUES ('drug_interval','13','p.m.'     ,13,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 14
INSERT INTO list_options VALUES ('drug_interval','14','ante'     ,14,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 15
INSERT INTO list_options VALUES ('drug_interval','15','h'        ,15,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 16
INSERT INTO list_options VALUES ('drug_interval','16','h.s.'     ,16,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 17
INSERT INTO list_options VALUES ('drug_interval','17','p.r.n.'   ,17,0,0);
#EndIf
#IfNotRow2D list_options list_id drug_interval option_id 18
INSERT INTO list_options VALUES ('drug_interval','18','stat'     ,18,0,0);
#EndIf

UPDATE list_options 
    SET option_id = '6' WHERE option_id = ' 6' and list_id = 'drug_route';    
UPDATE list_options
    SET title = 'mcg' WHERE option_id = '7' and list_id = 'drug_units';
UPDATE list_options
    SET title = 'grams' WHERE option_id = '8' and list_id = 'drug_units';

UPDATE `layout_options` 
    SET data_type = 26 WHERE form_id = 'DEM' and field_id = 'state';
UPDATE `layout_options`
    SET data_type = 26 WHERE form_id = 'DEM' and field_id = 'country_code';
UPDATE `layout_options`
    SET data_type = 26, list_id = 'state', fld_length = 0, max_length = 0, edit_options = '' WHERE form_id = 'DEM' and field_id = 'em_state';
UPDATE `layout_options`
    SET data_type = 26, list_id = 'country', fld_length = 0, max_length = 0, edit_options = '' WHERE form_id = 'DEM' and field_id = 'em_country';

ALTER TABLE `prices` 
    CHANGE `pr_selector` `pr_selector` VARCHAR( 255 ) NOT NULL default '' COMMENT 'template selector for drugs, empty for codes';

#IfMissingColumn form_encounter provider_id
ALTER TABLE `form_encounter` 
  ADD `provider_id` INT(11) DEFAULT '0' COMMENT 'default and main provider for this visit';
UPDATE form_encounter AS fe, forms AS f, billing AS b, users AS u
  SET fe.provider_id = u.id WHERE
  fe.provider_id = 0 AND
  f.form_id = fe.id AND
  f.formdir = 'newpatient' AND
  f.deleted = 0 AND
  b.pid = fe.pid AND
  b.encounter = fe.encounter AND
  b.fee > 0 AND
  b.provider_id > 0 AND
  b.activity = 1 AND
  u.id = b.provider_id AND
  u.authorized = 1;
UPDATE form_encounter AS fe, forms AS f, users AS u
  SET fe.provider_id = u.id WHERE
  fe.provider_id = 0 AND
  f.form_id = fe.id AND
  f.formdir = 'newpatient' AND
  f.deleted = 0 AND
  u.username = f.user AND
  u.authorized = 1;
UPDATE form_encounter AS fe, forms AS f, billing AS b
  SET b.provider_id = 0 WHERE
  fe.provider_id > 0 AND
  f.form_id = fe.id AND
  f.formdir = 'newpatient' AND
  f.deleted = 0 AND
  b.pid = fe.pid AND
  b.encounter = fe.encounter AND
  b.activity = 1;
#EndIf

#IfMissingColumn codes active
ALTER TABLE `codes` 
  ADD `active` TINYINT(1) DEFAULT 1 COMMENT '0 = inactive, 1 = active';
#EndIf

#IfMissingColumn drugs active
ALTER TABLE `drugs` 
  ADD `active` TINYINT(1) DEFAULT 1 COMMENT '0 = inactive, 1 = active';
#EndIf

#IfNotTable users_facility
CREATE TABLE `users_facility` (
  `tablename` varchar(64) NOT NULL,
  `table_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  PRIMARY KEY (`tablename`,`table_id`,`facility_id`)
) ENGINE=InnoDB COMMENT='joins users or patient_data to facility table';
#EndIf

#IfTable form_reviewofs
ALTER TABLE form_reviewofs CHANGE fever fever varchar(5);
ALTER TABLE form_reviewofs CHANGE chills chills varchar(5);
ALTER TABLE form_reviewofs CHANGE night_sweats night_sweats varchar(5);
ALTER TABLE form_reviewofs CHANGE weight_loss weight_loss varchar(5);
ALTER TABLE form_reviewofs CHANGE poor_appetite poor_appetite varchar(5);
ALTER TABLE form_reviewofs CHANGE insomnia insomnia varchar(5);
ALTER TABLE form_reviewofs CHANGE fatigued fatigued varchar(5);
ALTER TABLE form_reviewofs CHANGE depressed depressed varchar(5);
ALTER TABLE form_reviewofs CHANGE hyperactive hyperactive varchar(5);
ALTER TABLE form_reviewofs CHANGE exposure_to_foreign_countries exposure_to_foreign_countries varchar(5);
ALTER TABLE form_reviewofs CHANGE cataracts cataracts varchar(5);
ALTER TABLE form_reviewofs CHANGE cataract_surgery cataract_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE glaucoma glaucoma varchar(5);
ALTER TABLE form_reviewofs CHANGE double_vision double_vision varchar(5);
ALTER TABLE form_reviewofs CHANGE blurred_vision blurred_vision varchar(5);
ALTER TABLE form_reviewofs CHANGE poor_hearing poor_hearing varchar(5);
ALTER TABLE form_reviewofs CHANGE headaches headaches varchar(5);
ALTER TABLE form_reviewofs CHANGE ringing_in_ears ringing_in_ears varchar(5);
ALTER TABLE form_reviewofs CHANGE bloody_nose bloody_nose varchar(5);
ALTER TABLE form_reviewofs CHANGE sinusitis sinusitis varchar(5);
ALTER TABLE form_reviewofs CHANGE sinus_surgery sinus_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE dry_mouth dry_mouth varchar(5);
ALTER TABLE form_reviewofs CHANGE strep_throat strep_throat varchar(5);
ALTER TABLE form_reviewofs CHANGE tonsillectomy tonsillectomy varchar(5);
ALTER TABLE form_reviewofs CHANGE swollen_lymph_nodes swollen_lymph_nodes varchar(5);
ALTER TABLE form_reviewofs CHANGE throat_cancer throat_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE throat_cancer_surgery throat_cancer_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE heart_attack heart_attack varchar(5);
ALTER TABLE form_reviewofs CHANGE irregular_heart_beat irregular_heart_beat varchar(5);
ALTER TABLE form_reviewofs CHANGE chest_pains chest_pains varchar(5);
ALTER TABLE form_reviewofs CHANGE shortness_of_breath shortness_of_breath varchar(5);
ALTER TABLE form_reviewofs CHANGE high_blood_pressure high_blood_pressure varchar(5);
ALTER TABLE form_reviewofs CHANGE heart_failure heart_failure varchar(5);
ALTER TABLE form_reviewofs CHANGE poor_circulation poor_circulation varchar(5);
ALTER TABLE form_reviewofs CHANGE vascular_surgery vascular_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE cardiac_catheterization cardiac_catheterization varchar(5);
ALTER TABLE form_reviewofs CHANGE coronary_artery_bypass coronary_artery_bypass varchar(5);
ALTER TABLE form_reviewofs CHANGE heart_transplant heart_transplant varchar(5);
ALTER TABLE form_reviewofs CHANGE stress_test stress_test varchar(5);
ALTER TABLE form_reviewofs CHANGE emphysema emphysema varchar(5);
ALTER TABLE form_reviewofs CHANGE chronic_bronchitis chronic_bronchitis varchar(5);
ALTER TABLE form_reviewofs CHANGE interstitial_lung_disease interstitial_lung_disease varchar(5);
ALTER TABLE form_reviewofs CHANGE shortness_of_breath_2 shortness_of_breath_2 varchar(5);
ALTER TABLE form_reviewofs CHANGE lung_cancer lung_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE lung_cancer_surgery lung_cancer_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE pheumothorax pheumothorax varchar(5);
ALTER TABLE form_reviewofs CHANGE stomach_pains stomach_pains varchar(5);
ALTER TABLE form_reviewofs CHANGE peptic_ulcer_disease peptic_ulcer_disease varchar(5);
ALTER TABLE form_reviewofs CHANGE gastritis gastritis varchar(5);
ALTER TABLE form_reviewofs CHANGE endoscopy endoscopy varchar(5);
ALTER TABLE form_reviewofs CHANGE polyps polyps varchar(5);
ALTER TABLE form_reviewofs CHANGE colonoscopy colonoscopy varchar(5);
ALTER TABLE form_reviewofs CHANGE colon_cancer colon_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE colon_cancer_surgery colon_cancer_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE ulcerative_colitis ulcerative_colitis varchar(5);
ALTER TABLE form_reviewofs CHANGE crohns_disease crohns_disease varchar(5);
ALTER TABLE form_reviewofs CHANGE appendectomy appendectomy varchar(5);
ALTER TABLE form_reviewofs CHANGE divirticulitis divirticulitis varchar(5);
ALTER TABLE form_reviewofs CHANGE divirticulitis_surgery divirticulitis_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE gall_stones gall_stones varchar(5);
ALTER TABLE form_reviewofs CHANGE cholecystectomy cholecystectomy varchar(5);
ALTER TABLE form_reviewofs CHANGE hepatitis hepatitis varchar(5);
ALTER TABLE form_reviewofs CHANGE cirrhosis_of_the_liver cirrhosis_of_the_liver varchar(5);
ALTER TABLE form_reviewofs CHANGE splenectomy splenectomy varchar(5);
ALTER TABLE form_reviewofs CHANGE kidney_failure kidney_failure varchar(5);
ALTER TABLE form_reviewofs CHANGE kidney_stones kidney_stones varchar(5);
ALTER TABLE form_reviewofs CHANGE kidney_cancer kidney_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE kidney_infections kidney_infections varchar(5);
ALTER TABLE form_reviewofs CHANGE bladder_infections bladder_infections varchar(5);
ALTER TABLE form_reviewofs CHANGE bladder_cancer bladder_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE prostate_problems prostate_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE prostate_cancer prostate_cancer varchar(5);
ALTER TABLE form_reviewofs CHANGE kidney_transplant kidney_transplant varchar(5);
ALTER TABLE form_reviewofs CHANGE sexually_transmitted_disease sexually_transmitted_disease varchar(5);
ALTER TABLE form_reviewofs CHANGE burning_with_urination burning_with_urination varchar(5);
ALTER TABLE form_reviewofs CHANGE discharge_from_urethra discharge_from_urethra varchar(5);
ALTER TABLE form_reviewofs CHANGE rashes rashes varchar(5);
ALTER TABLE form_reviewofs CHANGE infections infections varchar(5);
ALTER TABLE form_reviewofs CHANGE ulcerations ulcerations varchar(5);
ALTER TABLE form_reviewofs CHANGE pemphigus pemphigus varchar(5);
ALTER TABLE form_reviewofs CHANGE herpes herpes varchar(5);
ALTER TABLE form_reviewofs CHANGE osetoarthritis osetoarthritis varchar(5);
ALTER TABLE form_reviewofs CHANGE rheumotoid_arthritis rheumotoid_arthritis varchar(5);
ALTER TABLE form_reviewofs CHANGE lupus lupus varchar(5);
ALTER TABLE form_reviewofs CHANGE ankylosing_sondlilitis ankylosing_sondlilitis varchar(5);
ALTER TABLE form_reviewofs CHANGE swollen_joints swollen_joints varchar(5);
ALTER TABLE form_reviewofs CHANGE stiff_joints stiff_joints varchar(5);
ALTER TABLE form_reviewofs CHANGE broken_bones broken_bones varchar(5);
ALTER TABLE form_reviewofs CHANGE neck_problems neck_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE back_problems back_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE back_surgery back_surgery varchar(5);
ALTER TABLE form_reviewofs CHANGE scoliosis scoliosis varchar(5);
ALTER TABLE form_reviewofs CHANGE herniated_disc herniated_disc varchar(5);
ALTER TABLE form_reviewofs CHANGE shoulder_problems shoulder_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE elbow_problems elbow_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE wrist_problems wrist_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE hand_problems hand_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE hip_problems hip_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE knee_problems knee_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE ankle_problems ankle_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE foot_problems foot_problems varchar(5);
ALTER TABLE form_reviewofs CHANGE insulin_dependent_diabetes insulin_dependent_diabetes varchar(5);
ALTER TABLE form_reviewofs CHANGE noninsulin_dependent_diabetes noninsulin_dependent_diabetes varchar(5);
ALTER TABLE form_reviewofs CHANGE hypothyroidism hypothyroidism varchar(5);
ALTER TABLE form_reviewofs CHANGE hyperthyroidism hyperthyroidism varchar(5);
ALTER TABLE form_reviewofs CHANGE cushing_syndrom cushing_syndrom varchar(5);
ALTER TABLE form_reviewofs CHANGE addison_syndrom addison_syndrom varchar(5);
#EndIf
