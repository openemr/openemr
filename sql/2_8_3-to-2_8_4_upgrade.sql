ALTER TABLE form_encounter
  ADD billing_note text NOT NULL DEFAULT '';

ALTER TABLE users
  ADD organization varchar(255) NOT NULL DEFAULT '',
  ADD valedictory  varchar(255) NOT NULL DEFAULT '';

ALTER TABLE openemr_postcalendar_events
  ADD pc_facility smallint(6) NOT NULL default '0' COMMENT 'facility id for this event';

ALTER TABLE payments
  ADD encounter bigint(20) NOT NULL DEFAULT 0,
  ADD KEY pid (pid);

ALTER TABLE patient_data
  ADD `usertext1`  varchar(255) NOT NULL DEFAULT '',
  ADD `usertext2`  varchar(255) NOT NULL DEFAULT '',
  ADD `userlist1`  varchar(255) NOT NULL DEFAULT '',
  ADD `userlist2`  varchar(255) NOT NULL DEFAULT '',
  ADD `pricelevel` varchar(255) NOT NULL DEFAULT '';

CREATE TABLE list_options (
  list_id        varchar(31)   NOT NULL,
  option_id      varchar(31)   NOT NULL,
  title          varchar(255)  NOT NULL DEFAULT '',
  seq            int(11)       NOT NULL DEFAULT 0,
  is_default     tinyint(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (list_id, option_id)
) TYPE=MyISAM;

INSERT INTO list_options VALUES ('yesno'  ,'NO' ,'NO' ,1,0);
INSERT INTO list_options VALUES ('yesno'  ,'YES','YES',2,0);

INSERT INTO list_options VALUES ('titles' ,'Mr.'   ,'Mr.'    ,1,0);
INSERT INTO list_options VALUES ('titles' ,'Mrs.'  ,'Mrs.'   ,2,0);
INSERT INTO list_options VALUES ('titles' ,'Ms.'   ,'Ms.'    ,3,0);
INSERT INTO list_options VALUES ('titles' ,'Dr.'   ,'Dr.'    ,4,0);

INSERT INTO list_options VALUES ('sex'    ,'Female','Female' ,1,0);
INSERT INTO list_options VALUES ('sex'    ,'Male'  ,'Male'   ,2,0);

INSERT INTO list_options VALUES ('marital','married'         ,'Married'         ,1,0);
INSERT INTO list_options VALUES ('marital','single'          ,'Single'          ,2,0);
INSERT INTO list_options VALUES ('marital','divorced'        ,'Divorced'        ,3,0);
INSERT INTO list_options VALUES ('marital','widowed'         ,'Widowed'         ,4,0);
INSERT INTO list_options VALUES ('marital','separated'       ,'Separated'       ,5,0);
INSERT INTO list_options VALUES ('marital','domestic partner','Domestic Partner',6,0);

INSERT INTO list_options VALUES ('language','English','English',1,1);
INSERT INTO list_options VALUES ('language','Spanish','Spanish',2,0);

INSERT INTO list_options VALUES ('ethrace','Caucasian','Caucasian',1,0);
INSERT INTO list_options VALUES ('ethrace','Asian'    ,'Asian'    ,2,0);
INSERT INTO list_options VALUES ('ethrace','Black'    ,'Black'    ,3,0);
INSERT INTO list_options VALUES ('ethrace','Hispanic' ,'Hispanic' ,4,0);

INSERT INTO list_options VALUES ('userlist1','sample','Sample',1,0);

INSERT INTO list_options VALUES ('userlist2','sample','Sample',1,0);

INSERT INTO list_options VALUES ('pricelevel','standard','Standard',1,1);
INSERT INTO list_options VALUES ('pricelevel','level1'  ,'Level 1' ,2,0);
INSERT INTO list_options VALUES ('pricelevel','level2'  ,'Level 2' ,3,0);

CREATE TABLE layout_options (
  form_id        varchar(31)   NOT NULL,
  field_id       varchar(31)   NOT NULL,
  group_name     varchar(15)   NOT NULL DEFAULT '',
  title          varchar(63)   NOT NULL DEFAULT '',
  seq            int(11)       NOT NULL DEFAULT 0,
  data_type      tinyint(3)    NOT NULL DEFAULT 0,
  uor            tinyint(1)    NOT NULL DEFAULT 1,
  fld_length     int(11)       NOT NULL DEFAULT 15,
  max_length     int(11)       NOT NULL DEFAULT 0,
  list_id        varchar(31)   NOT NULL DEFAULT '',
  titlecols      tinyint(3)    NOT NULL DEFAULT 1,
  datacols       tinyint(3)    NOT NULL DEFAULT 1,
  default_value  varchar(255)  NOT NULL DEFAULT '',
  edit_options   varchar(36)   NOT NULL DEFAULT '',
  description    varchar(255)  NOT NULL DEFAULT '',
  PRIMARY KEY (form_id, field_id, seq)
) TYPE=MyISAM;

INSERT INTO layout_options VALUES ('DEM','title'          ,'1Who','Name'          , 1, 1,1, 0, 0,'titles' ,1,1,'','' ,'Title');
INSERT INTO layout_options VALUES ('DEM','fname'          ,'1Who',''              , 2, 2,2,10,63,''       ,0,0,'','C','First Name');
INSERT INTO layout_options VALUES ('DEM','mname'          ,'1Who',''              , 3, 2,1, 2,63,''       ,0,0,'','C','Middle Name');
INSERT INTO layout_options VALUES ('DEM','lname'          ,'1Who',''              , 4, 2,2,10,63,''       ,0,0,'','C','Last Name');
INSERT INTO layout_options VALUES ('DEM','pubpid'         ,'1Who','External ID'   , 5, 2,1,10,15,''       ,1,1,'','' ,'External identifier');
INSERT INTO layout_options VALUES ('DEM','DOB'            ,'1Who','DOB'           , 6, 2,2,10,10,''       ,1,1,'','D','Date of Birth');
INSERT INTO layout_options VALUES ('DEM','sex'            ,'1Who','Sex'           , 7, 1,2, 0, 0,'sex'    ,1,1,'','' ,'Sex');
INSERT INTO layout_options VALUES ('DEM','ss'             ,'1Who','S.S.'          , 8, 2,1,11,11,''       ,1,1,'','' ,'Social Security Number');
INSERT INTO layout_options VALUES ('DEM','drivers_license','1Who','License/ID'    , 9, 2,1,15,63,''       ,1,1,'','' ,'Drivers License or State ID');
INSERT INTO layout_options VALUES ('DEM','status'         ,'1Who','Marital Status',10, 1,1, 0, 0,'marital',1,3,'','' ,'Marital Status');
INSERT INTO layout_options VALUES ('DEM','genericname1'   ,'1Who','User Defined'  ,11, 2,1,15,63,''       ,1,3,'','' ,'User Defined Field');
INSERT INTO layout_options VALUES ('DEM','genericval1'    ,'1Who',''              ,12, 2,1,15,63,''       ,0,0,'','' ,'User Defined Field');
INSERT INTO layout_options VALUES ('DEM','genericname2'   ,'1Who',''              ,13, 2,1,15,63,''       ,0,0,'','' ,'User Defined Field');
INSERT INTO layout_options VALUES ('DEM','genericval2'    ,'1Who',''              ,14, 2,1,15,63,''       ,0,0,'','' ,'User Defined Field');
INSERT INTO layout_options VALUES ('DEM','squad'          ,'1Who','Squad'         ,15,13,0, 0, 0,''       ,1,3,'','' ,'Squad Membership');
INSERT INTO layout_options VALUES ('DEM','pricelevel'     ,'1Who','Price Level'   ,16, 1,0, 0, 0,'pricelevel',1,1,'','' ,'Discount Level');

INSERT INTO layout_options VALUES ('DEM','street'              ,'2Contact','Address'          , 1, 2,1,25,63,''     ,1,1,'','C','Street and Number');
INSERT INTO layout_options VALUES ('DEM','city'                ,'2Contact','City'             , 2, 2,1,15,63,''     ,1,1,'','C','City Name');
INSERT INTO layout_options VALUES ('DEM','state'               ,'2Contact','State'            , 3, 2,1,15,63,''     ,1,1,'','C','State/Locality');
INSERT INTO layout_options VALUES ('DEM','postal_code'         ,'2Contact','Postal Code'      , 4, 2,1, 6,63,''     ,1,1,'','' ,'Postal Code');
INSERT INTO layout_options VALUES ('DEM','country_code'        ,'2Contact','Country'          , 5, 2,1,10,63,''     ,1,1,'','C','Country');
INSERT INTO layout_options VALUES ('DEM','contact_relationship','2Contact','Emergency Contact', 6, 2,1,10,63,''     ,1,1,'','C','Emergency Contact Person');
INSERT INTO layout_options VALUES ('DEM','phone_contact'       ,'2Contact','Emergency Phone'  , 7, 2,1,20,63,''     ,1,1,'','P','Emergency Contact Phone Number');
INSERT INTO layout_options VALUES ('DEM','phone_home'          ,'2Contact','Home Phone'       , 8, 2,1,20,63,''     ,1,1,'','P','Home Phone Number');
INSERT INTO layout_options VALUES ('DEM','phone_biz'           ,'2Contact','Work Phone'       , 9, 2,1,20,63,''     ,1,1,'','P','Work Phone Number');
INSERT INTO layout_options VALUES ('DEM','phone_cell'          ,'2Contact','Mobile Phone'     ,10, 2,1,20,63,''     ,1,1,'','P','Cell Phone Number');
INSERT INTO layout_options VALUES ('DEM','email'               ,'2Contact','Contact Email'    ,11, 2,1,30,95,''     ,1,1,'','' ,'Contact Email Address');

INSERT INTO layout_options VALUES ('DEM','providerID'   ,'3Choices','Provider'             , 1,11,2, 0, 0,''       ,1,3,'','' ,'Referring Provider');
INSERT INTO layout_options VALUES ('DEM','pharmacy_id'  ,'3Choices','Pharmacy'             , 2,12,1, 0, 0,''       ,1,3,'','' ,'Preferred Pharmacy');
INSERT INTO layout_options VALUES ('DEM','hipaa_notice' ,'3Choices','HIPAA Notice Received', 3, 1,1, 0, 0,'yesno'  ,1,1,'','' ,'Did you receive a copy of the HIPAA Notice?');
INSERT INTO layout_options VALUES ('DEM','hipaa_voice'  ,'3Choices','Allow Voice Message'  , 4, 1,1, 0, 0,'yesno'  ,1,1,'','' ,'Allow telephone messages?');
INSERT INTO layout_options VALUES ('DEM','hipaa_mail'   ,'3Choices','Allow Mail Message'   , 5, 1,1, 0, 0,'yesno'  ,1,1,'','' ,'Allow email messages?');
INSERT INTO layout_options VALUES ('DEM','hipaa_message','3Choices','Leave Message With'   , 6, 2,1,20,63,''       ,1,1,'','' ,'With whom may we leave a message?');

INSERT INTO layout_options VALUES ('DEM','occupation'    ,'4Employer','Occupation'      , 1, 2,1,20,63,''     ,1,1,'','C','Occupation');
INSERT INTO layout_options VALUES ('DEM','em_name'       ,'4Employer','Employer Name'   , 2, 2,1,20,63,''     ,1,1,'','C','Employer Name');
INSERT INTO layout_options VALUES ('DEM','em_street'     ,'4Employer','Employer Address', 3, 2,1,25,63,''     ,1,1,'','C','Street and Number');
INSERT INTO layout_options VALUES ('DEM','em_city'       ,'4Employer','City'            , 4, 2,1,15,63,''     ,1,1,'','C','City Name');
INSERT INTO layout_options VALUES ('DEM','em_state'      ,'4Employer','State'           , 5, 2,1,15,63,''     ,1,1,'','C','State/Locality');
INSERT INTO layout_options VALUES ('DEM','em_postal_code','4Employer','Postal Code'     , 6, 2,1, 6,63,''     ,1,1,'','' ,'Postal Code');
INSERT INTO layout_options VALUES ('DEM','em_country'    ,'4Employer','Country'         , 7, 2,1,10,63,''     ,1,1,'','C','Country');

INSERT INTO layout_options VALUES ('DEM','language'        ,'5Stats','Language'             , 1, 1,1, 0, 0,'language',1,1,'','' ,'Preferred Language');
INSERT INTO layout_options VALUES ('DEM','ethnoracial'     ,'5Stats','Race/Ethnicity'       , 2, 1,1, 0, 0,'ethrace' ,1,1,'','' ,'Ethnicity or Race');
INSERT INTO layout_options VALUES ('DEM','financial_review','5Stats','Financial Review Date', 3, 2,1,10,10,''        ,1,1,'','D','Financial Review Date');
INSERT INTO layout_options VALUES ('DEM','family_size'     ,'5Stats','Family Size'          , 4, 2,1,20,63,''        ,1,1,'','' ,'Family Size');
INSERT INTO layout_options VALUES ('DEM','monthly_income'  ,'5Stats','Monthly Income'       , 5, 2,1,20,63,''        ,1,1,'','' ,'Monthly Income');
INSERT INTO layout_options VALUES ('DEM','homeless'        ,'5Stats','Homeless, etc.'       , 6, 2,1,20,63,''        ,1,1,'','' ,'Homeless or similar?');
INSERT INTO layout_options VALUES ('DEM','interpretter'    ,'5Stats','Interpreter'          , 7, 2,1,20,63,''        ,1,1,'','' ,'Interpreter needed?');
INSERT INTO layout_options VALUES ('DEM','migrantseasonal' ,'5Stats','Migrant/Seasonal'     , 8, 2,1,20,63,''        ,1,1,'','' ,'Migrant or seasonal worker?');

INSERT INTO layout_options VALUES ('DEM','usertext1'       ,'6Misc','User Defined Text 1'   , 1, 2,0,10,63,''         ,1,1,'','' ,'User Defined');
INSERT INTO layout_options VALUES ('DEM','usertext2'       ,'6Misc','User Defined Text 2'   , 2, 2,0,10,63,''         ,1,1,'','' ,'User Defined');
INSERT INTO layout_options VALUES ('DEM','userlist1'       ,'6Misc','User Defined List 1'   , 3, 1,0, 0, 0,'userlist1',1,1,'','' ,'User Defined');
INSERT INTO layout_options VALUES ('DEM','userlist2'       ,'6Misc','User Defined List 2'   , 4, 1,0, 0, 0,'userlist2',1,1,'','' ,'User Defined');

ALTER TABLE transactions
  ADD `refer_date`              date         DEFAULT NULL,
  ADD `refer_from`              int(11)      NOT NULL DEFAULT 0,
  ADD `refer_to`                int(11)      NOT NULL DEFAULT 0,
  ADD `refer_diag`              varchar(255) NOT NULL DEFAULT '',
  ADD `refer_risk_level`        varchar(255) NOT NULL DEFAULT '',
  ADD `refer_vitals`            tinyint(1)   NOT NULL DEFAULT 0,
  ADD `reply_date`              date         DEFAULT NULL,
  ADD `reply_from`              varchar(255) NOT NULL DEFAULT '',
  ADD `reply_init_diag`         varchar(255) NOT NULL DEFAULT '',
  ADD `reply_final_diag`        varchar(255) NOT NULL DEFAULT '',
  ADD `reply_documents`         varchar(255) NOT NULL DEFAULT '',
  ADD `reply_findings`          text         NOT NULL DEFAULT '',
  ADD `reply_services`          text         NOT NULL DEFAULT '',
  ADD `reply_recommend`         text         NOT NULL DEFAULT '',
  ADD `reply_rx_refer`          text         NOT NULL DEFAULT '';

INSERT INTO layout_options VALUES ('REF','refer_date'      ,'','Referral Date'          , 1, 4,2, 0,  0,''         ,1,1,'C','D','Date of referral');
INSERT INTO layout_options VALUES ('REF','refer_from'      ,'','Refer From'             , 2,14,2, 0,  0,''         ,1,1,'' ,'' ,'Referral By');
INSERT INTO layout_options VALUES ('REF','refer_to'        ,'','Refer To'               , 3,14,2, 0,  0,''         ,1,1,'' ,'' ,'Referral To');
INSERT INTO layout_options VALUES ('REF','body'            ,'','Reason'                 , 4, 3,2,30,  3,''         ,1,1,'' ,'' ,'Reason for referral');
INSERT INTO layout_options VALUES ('REF','refer_diag'      ,'','Referrer Diagnosis'     , 5, 2,1,30,255,''         ,1,1,'' ,'X','Referrer diagnosis');
INSERT INTO layout_options VALUES ('REF','refer_risk_level','','Risk Level'             , 6, 1,1, 0,  0,'risklevel',1,1,'' ,'' ,'Level of urgency');
INSERT INTO layout_options VALUES ('REF','refer_vitals'    ,'','Include Vitals'         , 7, 1,1, 0,  0,'boolean'  ,1,1,'' ,'' ,'Include vitals data?');
INSERT INTO layout_options VALUES ('REF','reply_date'      ,'','Reply Date'             , 8, 4,1, 0,  0,''         ,1,1,'' ,'D','Date of reply');
INSERT INTO layout_options VALUES ('REF','reply_from'      ,'','Reply From'             , 9, 2,1,30,255,''         ,1,1,'' ,'' ,'Who replied?');
INSERT INTO layout_options VALUES ('REF','reply_init_diag' ,'','Presumed Diagnosis'     ,10, 2,1,30,255,''         ,1,1,'' ,'' ,'Presumed diagnosis by specialist');
INSERT INTO layout_options VALUES ('REF','reply_final_diag','','Final Diagnosis'        ,11, 2,1,30,255,''         ,1,1,'' ,'' ,'Final diagnosis by specialist');
INSERT INTO layout_options VALUES ('REF','reply_documents' ,'','Documents'              ,12, 2,1,30,255,''         ,1,1,'' ,'' ,'Where may related scanned or paper documents be found?');
INSERT INTO layout_options VALUES ('REF','reply_findings'  ,'','Findings'               ,13, 3,1,30,  3,''         ,1,1,'' ,'' ,'Findings by specialist');
INSERT INTO layout_options VALUES ('REF','reply_services'  ,'','Services Provided'      ,14, 3,1,30,  3,''         ,1,1,'' ,'' ,'Service provided by specialist');
INSERT INTO layout_options VALUES ('REF','reply_recommend' ,'','Recommendations'        ,15, 3,1,30,  3,''         ,1,1,'' ,'' ,'Recommendations by specialist');
INSERT INTO layout_options VALUES ('REF','reply_rx_refer'  ,'','Prescriptions/Referrals',16, 3,1,30,  3,''         ,1,1,'' ,'' ,'Prescriptions and/or referrals by specialist');

INSERT INTO list_options VALUES ('risklevel','low'   ,'Low'   ,1,0);
INSERT INTO list_options VALUES ('risklevel','medium','Medium',2,1);
INSERT INTO list_options VALUES ('risklevel','high'  ,'High'  ,3,0);

INSERT INTO list_options VALUES ('boolean','0','No' ,1,0);
INSERT INTO list_options VALUES ('boolean','1','Yes',2,0);

INSERT INTO `categories` VALUES (6,'Patient Photograph','',1,9,10);

INSERT INTO `registry` VALUES ('CAMOS',1,'CAMOS',16,1,1,'2008-01-01 00:00:00');
