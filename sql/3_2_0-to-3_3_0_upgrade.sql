#IfNotRow categories name Advance Directive
  INSERT INTO categories select (select MAX(id) from categories) + 1, 'Advance Directive', '', 1, rght, rght + 7 from categories where name = 'Categories';
  INSERT INTO categories select (select MAX(id) from categories) + 1, 'Do Not Resuscitate Order', '', (select id from categories where name = 'Advance Directive'), rght + 1, rght + 2 from categories where name = 'Categories';
  INSERT INTO categories select (select MAX(id) from categories) + 1, 'Durable Power of Attorney', '', (select id from categories where name = 'Advance Directive'), rght + 3, rght + 4 from categories where name = 'Categories';
  INSERT INTO categories select (select MAX(id) from categories) + 1, 'Living Will', '', (select id from categories where name = 'Advance Directive'), rght + 5, rght + 6 from categories where name = 'Categories';
  UPDATE categories SET rght = rght + 8 WHERE name = 'Categories';
  UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

#IfMissingColumn patient_data completed_ad
ALTER TABLE patient_data
  ADD completed_ad VARCHAR(3) NOT NULL DEFAULT 'NO',
  ADD ad_reviewed date DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id apptstat
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists'   ,'apptstat','Appointment Statuses', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','-'       ,'- None'              , 5,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','*'       ,'* Reminder done'     ,10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','+'       ,'+ Chart pulled'      ,15,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','x'       ,'x Canceled'          ,20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','?'       ,'? No show'           ,25,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','@'       ,'@ Arrived'           ,30,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','~'       ,'~ Arrived late'      ,35,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','!'       ,'! Left w/o visit'    ,40,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','#'       ,'# Ins/fin issue'     ,45,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','<'       ,'< In exam room'      ,50,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','>'       ,'> Checked out'       ,55,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','$'       ,'$ Coding done'       ,60,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('apptstat','%'       ,'% Canceled < 24h'    ,65,0);
ALTER TABLE openemr_postcalendar_events CHANGE pc_apptstatus pc_apptstatus varchar(15) NOT NULL DEFAULT '-';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id referral_source
INSERT INTO `layout_options` VALUES ('DEM', 'referral_source', '5Stats', 'Referral Source',10, 26, 1, 0, 0, 'refsource', 1, 1, '', '', 'How did they hear about us');
#EndIf

#IfMissingColumn list_options notes
ALTER TABLE list_options
  CHANGE mapping mapping varchar(31) NOT NULL DEFAULT '',
  ADD notes varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfNotRow2D list_options list_id lists option_id warehouse
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','warehouse','Warehouses',21,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('warehouse','onsite','On Site', 5,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id abook_type
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','abook_type'  ,'Address Book Types'  , 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('abook_type','ord_img','Imaging Service'     , 5,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('abook_type','ord_imm','Immunization Service',10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('abook_type','ord_lab','Lab Service'         ,15,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('abook_type','spe'    ,'Specialist'          ,20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('abook_type','oth'    ,'Other'               ,95,0);
#EndIf

#IfMissingColumn users abook_type
ALTER TABLE users
  ADD abook_type varchar(31) NOT NULL DEFAULT '';
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_type
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','proc_type','Procedure Types', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','img','Imaging'       ,10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','imm','Immunization'  ,20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','lab','Lab'           ,30,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','rec','Recommendation',80,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','oth','Other'         ,90,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_body_site
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','proc_body_site','Procedure Body Sites', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_body_site','arm'    ,'Arm'    ,10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_body_site','buttock','Buttock',20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_body_site','oth'    ,'Other'  ,90,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_specimen
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','proc_specimen','Procedure Specimen Types', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_specimen','blood' ,'Blood' ,10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_specimen','saliva','Saliva',20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_specimen','urine' ,'Urine' ,30,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_specimen','oth'   ,'Other' ,90,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_route
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','proc_route','Procedure Routes', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_route','inj' ,'Injection',10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_route','oral','Oral'     ,20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_route','oth' ,'Other'    ,90,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_lat
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','proc_lat','Procedure Lateralities', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_lat','left' ,'Left'     ,10,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_lat','right','Right'    ,20,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_lat','bilat','Bilateral',30,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id proc_unit
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','proc_unit','Procedure Units', 1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','cu_mm'      ,'CU.MM'      , 10);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','fl'         ,'FL'         , 20);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','g_dl'       ,'G/DL'       , 30);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','gm_dl'      ,'GM/DL'      , 40);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','hmol_l'     ,'HMOL/L'     , 50);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','iu_l'       ,'IU/L'       , 60);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','mg_dl'      ,'MG/DL'      , 70);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','mil_cu_mm'  ,'Mil/CU.MM'  , 80);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','percent'    ,'Percent'    , 90);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','percentile' ,'Percentile' ,100);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','pg'         ,'PG'         ,110);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','ratio'      ,'Ratio'      ,120);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','thous_cu_mm','Thous/CU.MM',130);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','units'      ,'Units'      ,140);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','units_l'    ,'Units/L'    ,150);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('proc_unit','oth'        ,'Other'      ,990);
#EndIf

#IfNotTable procedure_type
CREATE TABLE `procedure_type` (
  `procedure_type_id`   bigint(20)   NOT NULL AUTO_INCREMENT,
  `parent`              bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references procedure_type.procedure_type_id',
  `name`                varchar(63)  NOT NULL DEFAULT '' COMMENT 'name for this category, procedure or result type',
  `lab_id`              bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references users.id, 0 means default to parent',
  `procedure_code`      varchar(31)  NOT NULL DEFAULT '' COMMENT 'code identifying this procedure',
  `procedure_type`      varchar(31)  NOT NULL DEFAULT '' COMMENT 'lab, imaging, ...',
  `body_site`           varchar(31)  NOT NULL DEFAULT '' COMMENT 'where to do injection, e.g. arm, buttok',
  `specimen`            varchar(31)  NOT NULL DEFAULT '' COMMENT 'blood, urine, saliva, etc.',
  `route_admin`         varchar(31)  NOT NULL DEFAULT '' COMMENT 'oral, injection',
  `laterality`          varchar(31)  NOT NULL DEFAULT '' COMMENT 'left, right, ...',
  `description`         varchar(255) NOT NULL DEFAULT '' COMMENT 'descriptive text for procedure_code',
  `standard_code`       varchar(255) NOT NULL DEFAULT '' COMMENT 'industry standard code type and code (e.g. CPT4:12345)',
  `related_code`        varchar(255) NOT NULL DEFAULT '' COMMENT 'suggested code(s) for followup services if result is abnormal',
  `is_discrete`         tinyint(1)   NOT NULL DEFAULT 0  COMMENT 'can this be referenced by lab_test_result?',
  `is_orderable`        tinyint(1)   NOT NULL DEFAULT 0  COMMENT 'can this test type be ordered?',
  `units`               varchar(31)  NOT NULL DEFAULT '' COMMENT 'default for procedure_result.units',
  `range`               varchar(255) NOT NULL DEFAULT '' COMMENT 'default for procedure_result.range',
  PRIMARY KEY (`procedure_type_id`),
  KEY parent (parent)
) ENGINE=MyISAM;
#EndIf

