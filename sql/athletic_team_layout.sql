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

-- Added 2010-08-18:

DELETE FROM code_types;
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('OSICS10', 9, 1, 4, '', 0, 0, 0, 1);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('OPCS'   , 6, 2, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('PTCJ'   , 7, 3, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('CPT4'   , 1, 4, 0, '', 0, 0, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('SMPC'   ,10, 5, 0, '', 0, 0, 0, 0);

