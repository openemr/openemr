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

ALTER TABLE `prices` 
    CHANGE `pr_selector` `pr_selector` VARCHAR( 255 ) NOT NULL default '' COMMENT 'template selector for drugs, empty for codes';

