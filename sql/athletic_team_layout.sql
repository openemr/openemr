INSERT INTO layout_options VALUES ('DEM','fitness'  ,'5Stats'  ,'Fitness to Play',10, 1,1, 0, 0,'fitness',1,1,'','','Fitness Level');
INSERT INTO layout_options VALUES ('DEM','userdate1','5Stats'  ,'Return to Play' ,11, 4,1,10,10,''       ,1,1,'','','Return to Play Date');

INSERT INTO layout_options VALUES ('HIS',''         ,'1General','Allergies'      , 3,24,1, 0, 0,''       ,1,1,'','' ,'List of allergies');

INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','1','Full Play'          , 1,1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','2','Full Training'      , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','3','Restricted Training', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','4','Injured Out'        , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','5','Rehabilitation'     , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','6','Illness'            , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fitness','7','International Duty' , 7,0,0);

INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','fitness','Sports Fitness Levels',51,0,0);

ALTER TABLE patient_data
  ADD `userdate1` date DEFAULT NULL;

CREATE TABLE IF NOT EXISTS lists_football_injury (
  id          bigint(20)   NOT NULL,
  fiinjmin    int(11)      NOT NULL DEFAULT 0,
  fiinjtime   int(11)      NOT NULL DEFAULT 0,
  fimatchtype int(11)      NOT NULL DEFAULT 0,
  fimech_tackling    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_tackled     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_collision   tinyint(1)   NOT NULL DEFAULT 0,
  fimech_kicked      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_elbow       tinyint(1)   NOT NULL DEFAULT 0,
  fimech_othercon    varchar(255) NOT NULL DEFAULT '',
  fimech_nofoul      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_oppfoul     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_ownfoul     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_yellow      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_red         tinyint(1)   NOT NULL DEFAULT 0,
  fimech_passing     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_shooting    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_running     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_dribbling   tinyint(1)   NOT NULL DEFAULT 0,
  fimech_heading     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_jumping     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_landing     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_fall        tinyint(1)   NOT NULL DEFAULT 0,
  fimech_stretching  tinyint(1)   NOT NULL DEFAULT 0,
  fimech_turning     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_throwing    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_diving      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_overuse     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_othernon    varchar(255) NOT NULL DEFAULT '',
  fisurface   int(11)      NOT NULL DEFAULT 0,
  fiposition  int(11)      NOT NULL DEFAULT 0,
  fifootwear  int(11)      NOT NULL DEFAULT 0,
  fiside      int(11)      NOT NULL DEFAULT 0,
  firemoved   int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) TYPE=MyISAM;

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'apptstat';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','apptstat','Event Statuses',1);
DELETE FROM list_options WHERE list_id = 'apptstat';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','FS','Fitness Session',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','WT','Weight Session' ,2);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','SS','Skills Session' ,3);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','G' ,'Game'           ,4);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','O' ,'Off'            ,5);
ALTER TABLE openemr_postcalendar_events CHANGE pc_apptstatus pc_apptstatus varchar(15) NOT NULL;

CREATE TABLE daily_fitness (
  `pid`      int(11)     NOT NULL COMMENT 'references patient_data.pid',
  `date`     date        NOT NULL,
  `fitness`  varchar(31) NOT NULL DEFAULT '' COMMENT 'list fitness',
  `issue_id` bigint(20)  NOT NULL DEFAULT 0  COMMENT 'references lists.id',
  PRIMARY KEY (`pid`,`date`)
) ENGINE=MyISAM;

CREATE TABLE player_event (
  `pid`             int(11) NOT NULL COMMENT 'references patient_data.pid',
  `date`            date        NOT NULL,
  `pc_eid`          int(11) NOT NULL COMMENT 'references openemr_postcalendar_events.pc_eid',
  `minutes`         int(11) NOT NULL COMMENT 'minutes of participation',
  `fitness_related` int(1)  NOT NULL DEFAULT 1 COMMENT 'if non-participation is due to fitness',
  PRIMARY KEY (`pid`,`date`,`pc_eid`)
) ENGINE=MyISAM;

-- Added 2010-04-05:

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'injury_part';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','injury_part','Injured Body Parts',1);
DELETE FROM list_options WHERE list_id = 'injury_part';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','ankle'    ,'Ankle',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','elbow'    ,'Elbow',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','foot'     ,'Foot / Toe',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','hand'     ,'Hand / Finger / Thumb',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','head'     ,'Head / Face',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','hip'      ,'Hip / Groin',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','knee'     ,'Knee',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','lowerback','Lower Back',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','lowerleg' ,'Lower Leg / Achilles',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','neck'     ,'Neck / Cervical Spine',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','pelvis'   ,'Pelvis',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','postthigh','Post Thigh / Hamstring',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','shoulder' ,'Shoulder / Clavicle',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','sternum'  ,'Sternum / Upper Back',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','thigh'    ,'Thigh / Quad',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','upperarm' ,'Upper Arm',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_part','wrist'    ,'Wrist',1);

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'injury_type';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','injury_type','Injury Type',1);
DELETE FROM list_options WHERE list_id = 'injury_type';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','abrasion'   ,'Abrasion',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','concussion' ,'Concussion',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','dental'     ,'Dental Injury',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','dislocation','Dislocation / Subluxation',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','fracture'   ,'Fracture',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','haematoma'  ,'Haematoma / Contusion / Bruise',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','laceration' ,'Laceration',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','meniscal'   ,'Meniscal / Cartilage',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','muscle'     ,'Muscle Rupture/Tear/Strain',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','nerve'      ,'Nerve Injury',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','other'      ,'Other',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','otherbone'  ,'Other Bone Injury',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','overuse'    ,'Overuse Symptoms Non Specific',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','sprain'     ,'Sprain / Ligament Injury',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','synovitis'  ,'Synovitis / Effusion',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_type','tendon'     ,'Tendon Rupture / Partial Tear / Tendinopathy',1);

UPDATE lists AS l, lists_football_injury AS i SET l.extrainfo = i.fiside WHERE i.id = l.id;

ALTER TABLE lists_football_injury
  ADD ficondition           int(11)    NOT NULL DEFAULT 0,
  ADD fimech_blocked        tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_hitbyball      tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_goalpost       tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_ground         tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_collother      tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_tackside       tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_tackback       tinyint(1) NOT NULL DEFAULT 0,
  ADD fimech_sliding        tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_sunny       tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_rainy       tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_windy       tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_dry         tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_sleet       tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_overcast    tinyint(1) NOT NULL DEFAULT 0,
  ADD fiweather_temperature varchar(7) NOT NULL DEFAULT '';

-- Added 2010-06-22:

INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','medical_system','Medical Systems',1);
DELETE FROM list_options WHERE list_id = 'medical_system';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','resp'  ,'Respiratory'      ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','cardio','Cardiovascular'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','gastro','Gastro Intestinal',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','genito','Genito Urinary'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','ent'   ,'ENT'              ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','endo'  ,'Endocrine'        ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','neuro' ,'Neurological'     ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','hemato','Haematological'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','psych' ,'Psychiatric / Psychological',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','skin'  ,'Skin'             ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_system','preg'  ,'Pregnancy'        ,1);

INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','medical_type','Medical Types',1);
DELETE FROM list_options WHERE list_id = 'medical_type';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','neopl'  ,'Neoplastic'  ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','metabol','Metabolic'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','infect' ,'Infective'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','trauma' ,'Traumatic'   ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','autoimm','Autoimmune'  ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','vasc'   ,'Vascular'    ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','inflamm','Inflammatory',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','degen'  ,'Degenerative',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','idiopat','Idiopathic'  ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('medical_type','na'     ,'N/A'         ,2);

-- Added 2010-08-18, revised 2011-01-02:

DELETE FROM code_types;
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('OSICS10', 9, 1, 4, '', 0, 0, 0, 1);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('OPCS'   , 6, 2, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('Phys'   , 7, 3, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('CPT4'   , 1, 4, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('Rad'    ,10, 5, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('INJ'    ,11, 6, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('LAB'    ,12, 7, 0, '', 0, 0, 0, 0);

-- Added 2011-01-02:

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'injury_grade';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','injury_grade','Injury Grade',1);
DELETE FROM list_options WHERE list_id = 'injury_grade';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_grade','1','Mild'     ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_grade','2','Moderate' ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('injury_grade','3','Severe'   ,1);

-- Added 2011-01-02:

DELETE FROM list_options WHERE list_id = 'lbfnames' AND option_id = 'LBFvbf';
INSERT INTO list_options ( list_id, option_id, title, seq, option_value ) VALUES ('lbfnames','LBFvbf','Vitals and Body Fat',1,5);

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'temperature_locations';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','temperature_locations','Temperature Locations',1);
DELETE FROM list_options WHERE list_id = 'temperature_locations';
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('temperature_locations','Oral'             ,'Oral'             ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('temperature_locations','Tympanic Membrane','Tympanic Membrane',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('temperature_locations','Rectal'           ,'Rectal'           ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('temperature_locations','Axillary'         ,'Axillary'         ,1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('temperature_locations','Temporal Artery'  ,'Temporal Artery'  ,1);

DELETE FROM layout_options WHERE form_id = 'LBFvbf';
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','bmi','1','BMI',12,2,1,5,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','bmi_status','1','BMI Status',13,2,1,8,255,'',1,3,'','','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','body_fat','1','Body Fat %',28,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','bp_diastolic','1','BP Diastolic',6,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','bp_systolic','1','BP Systolic',5,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','height_cm','1','Height (cm)',4,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','height_in','1','Height (in)',3,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','pulse','1','Pulse',7,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','respiration','1','Respiration',8,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_abdomen','1','Skin Fold - Abdomen',24,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_bicep','1','Skin Fold - Bicep',21,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_calf','1','Skin Fold - Calf',26,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_subscapular','1','Skin Fold - Subscapular',23,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_sum','1','Sum of Skin Folds',27,2,1,4,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_suprailiac','1','Skin Fold - Suprailiac',24,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_thigh','1','Skin Fold - Thigh',25,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','sf_tricep','1','Skin Fold - Tricep',22,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','temperature_c','1','Temperature (C)',10,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','temperature_f','1','Temperature (F)',9,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','temp_location','1','Temp Location',11,1,1,0,255,'temperature_locations',1,3,'','','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','weight_kg','1','Weight (kg)',2,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFvbf','weight_lbs','1','Weight (lbs)',1,2,1,3,255,'',1,3,'','G','');

DELETE FROM list_options WHERE list_id = 'lbfnames' AND option_id = 'LBFfms';
INSERT INTO list_options ( list_id, option_id, title, seq, option_value ) VALUES ('lbfnames','LBFfms','Functional Movement Screening',1,5);

DELETE FROM layout_options WHERE form_id = 'LBFfms';
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','squat_1'   ,'1','Deep Squat'     , 1,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','squat_2'   ,'1',''               , 2,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','hurdle_l_1','1','Hurdle ST L'    , 3,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','hurdle_l_2','1',''               , 4,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','hurdle_r_1','1','Hurdle ST R'    , 5,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','hurdle_r_2','1',''               , 6,0,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','lunge_l_1' ,'1','In Line Lunge L', 7,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','lunge_l_2' ,'1',''               , 8,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','lunge_r_1' ,'1','In Line Lunge R', 9,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','lunge_r_2' ,'1',''               ,10,0,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','sho_l_1'   ,'1','Sho Mob L'      ,11,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','sho_l_2'   ,'1',''               ,12,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','sho_r_1'   ,'1','Sho Mob R'      ,13,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','sho_r_2'   ,'1',''               ,14,0,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','actslr_l_1','1','Active SLR L'   ,15,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','actslr_l_2','1',''               ,16,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','actslr_r_1','1','Active SLR R'   ,17,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','actslr_r_2','1',''               ,18,0,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','tspu_1'    ,'1','Trunk Stab Push Up',19,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','tspu_2'    ,'1',''                  ,20,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spine_1'   ,'1','Spine Extension',21,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spine_2'   ,'1',''               ,22,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','rotary_l_1','1','Rotary Stab L'  ,23,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','rotary_l_2','1',''               ,24,2,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','rotary_r_1','1','Rotary Stab R'  ,25,2,1,1,1,'',1,1,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','rotary_r_2','1',''               ,26,0,1,1,1,'',0,2,'G');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spinef_1'  ,'1','Spine Flexion'  ,27,1,1,1,1,'boolean',1,1,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spinef_2'  ,'1',''               ,28,1,1,1,1,'boolean',0,2,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spinee_1'  ,'1','Spine Extension',29,1,1,1,1,'boolean',1,1,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','spinee_2'  ,'1',''               ,30,1,1,1,1,'boolean',0,2,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','imping_l_1','1','Act Impingement L',31,1,1,1,1,'boolean',1,1,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','imping_l_2','1',''                 ,32,1,1,1,1,'boolean',0,2,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','imping_r_1','1','Act Impingement R',33,1,1,1,1,'boolean',1,1,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','imping_r_2','1',''                 ,34,1,1,1,1,'boolean',0,2,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, edit_options) VALUES ('LBFfms','total'     ,'1','Sum of Scores'  ,35,2,1,3,3,'',2,2,'G');

-- Added 2011-05-25:

ALTER TABLE daily_fitness
  ADD `am` text NOT NULL DEFAULT '',
  ADD `pm` text NOT NULL DEFAULT '';

-- Added 2011-07-12:

DELETE FROM list_options WHERE list_id = 'lbfnames' AND option_id = 'LBFathv';
INSERT INTO list_options ( list_id, option_id, title, seq, option_value ) VALUES ('lbfnames','LBFathv','Athletic Vitals',1,5);
DELETE FROM layout_options WHERE form_id = 'LBFathv';
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','weight_lbs'    ,'1','Weight (lbs)'           , 1,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','weight_kg'     ,'1','Weight (kg)'            , 2,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','height_in'     ,'1','Height (in)'            , 3,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','height_cm'     ,'1','Height (cm)'            , 4,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','bp_systolic'   ,'1','BP Systolic'            , 5,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','bp_diastolic'  ,'1','BP Diastolic'           , 6,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','pulse'         ,'1','Pulse'                  , 7,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','respiration'   ,'1','Respiration'            , 8,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','temperature_f' ,'1','Temperature (F)'        , 9,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','temperature_c' ,'1','Temperature (C)'        ,10,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','temp_location' ,'1','Temp Location'          ,11,1,1,0,255,'',1,3,'','' ,'');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','bmi'           ,'1','BMI'                    ,12,2,1,5,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathv','bmi_status'    ,'1','BMI Status'             ,13,2,1,8,255,'',1,3,'','' ,'');

DELETE FROM list_options WHERE list_id = 'lbfnames' AND option_id = 'LBFathbf';
INSERT INTO list_options ( list_id, option_id, title, seq, option_value ) VALUES ('lbfnames','LBFathbf','Athletic Body Fat',1,5);
DELETE FROM layout_options WHERE form_id = 'LBFathbf';
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_bicep'      ,'1','Skin Fold - Bicep'      ,21,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_tricep'     ,'1','Skin Fold - Tricep'     ,22,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_subscapular','1','Skin Fold - Subscapular',23,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_abdomen'    ,'1','Skin Fold - Abdomen'    ,24,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_suprailiac' ,'1','Skin Fold - Suprailiac' ,25,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_thigh'      ,'1','Skin Fold - Thigh'      ,26,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_calf'       ,'1','Skin Fold - Calf'       ,27,2,1,3,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','sf_sum'        ,'1','Sum of Skin Folds'      ,28,2,1,4,255,'',1,3,'','G','');
INSERT INTO layout_options (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFathbf','body_fat'      ,'1','Body Fat %'             ,29,2,1,3,255,'',1,3,'','G','');

