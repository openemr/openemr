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

