
##Demographics Who
#Set inactive the Ms. from title list
Delete from list_options where list_id="titles";
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Mr.', 'Mr.', 1, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Mrs.', 'Mrs.', 2, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default,activity ) VALUES ('titles', 'Ms.', 'Ms.', 3, 0, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Dr.', 'Dr.', 4, 0);


Delete from layout_options where group_name="1Who";
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','title','1Who','Name',1,1,1,0,0,'titles',1,1,'','N','Title',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','fname','1Who','',2,2,2,10,63,'',0,0,'','CD','First Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','mname','1Who','',3,2,0,2,63,'',0,0,'','C','Middle Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','lname','1Who','',4,2,2,10,63,'',0,0,'','CD','Last Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','DOB','1Who','DOB',5,4,2,0,10,'',1,1,'','D','Date of Birth',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','type_id','1Who','Type Id.',6,1,2,0,0,'id_type',1,1,'','','Type of Id document',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','pubpid','1Who','Id. Number',7,2,2,10,15,'',1,1,'','ND','External identifier',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','ILpopulation','1Who','',8,2,1,0,0,'',0,0,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','passportCountry','1Who','Country of Issue',9,1,1,0,0,'country',1,1,'','','Country of Issue (passpor) when passport is selected',0,'','F','a:1:{i:0;a:5:{s:2:\"id\";s:7:\"type_id\";s:6:\"itemid\";N;s:8:\"operator\";s:2:\"ne\";s:5:\"value\";s:8:\"Passport\";s:5:\"andor\";s:0:\"\";}}');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','passportYear','1Who','Year Issued',10,2,1,4,4,'',1,1,'','','Passport year Issued (YYYY)',0,'','F','a:1:{i:0;a:5:{s:2:\"id\";s:7:\"type_id\";s:6:\"itemid\";N;s:8:\"operator\";s:2:\"ne\";s:5:\"value\";s:8:\"Passport\";s:5:\"andor\";s:0:\"\";}}');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','sex','1Who','Sex',11,1,2,0,0,'sex',1,1,'','N','Sex',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','status','1Who','Marital Status',12,1,1,0,0,'marital',1,1,'','','Marital Status',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','drivers_license','1Who','License/ID',13,1,1,0,0,'YNDN',1,1,'','','Drivers License',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','nickName','1Who','Nickname',20,2,1,15,0,'',1,1,'','','Nickname',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','momsname','1Who','Mother\'s Name',21,2,1,15,0,'',1,1,'','','Mother\'s Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','fathersName','1Who','Father\'s Name',22,2,1,15,0,'',1,1,'','','Dad\'s Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','previousLastName','1Who','Previous Last Name',23,2,1,15,0,'',1,1,'','','Previous Last Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','placeOfBirth','1Who','Place of birth',24,1,1,0,0,'country',1,1,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','citizenshipYear','1Who','Citizenship Year',25,2,1,4,4,'',1,1,'','','aliya year (YYYY)',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','genericname1','1Who','User Defined',30,2,0,15,63,'',1,1,'','','User Defined Field',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','genericval1','1Who','',31,2,0,15,63,'',0,0,'','','User Defined Field',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','genericname2','1Who','',32,2,0,15,63,'',0,0,'','','User Defined Field',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','genericval2','1Who','',33,2,0,15,63,'',0,0,'','','User Defined Field',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','squad','1Who','Squad',34,13,0,0,0,'',0,0,'','','Squad Membership',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','pricelevel','1Who','Price Level',35,1,0,0,0,'pricelevel',0,0,'','','Discount Level',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','ss','1Who','S.S.',36,2,0,11,11,'',0,0,'','','Social Security Number',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','billing_note','1Who','Billing Note',37,2,0,60,0,'',0,0,'','','Patient Level Billing Note (Collections)',0,'','F','');






ALTER TABLE `patient_data` ADD `type_id` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `passportCountry` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `passportYear` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `fathersName` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `momsName` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `nickName` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `previousLastName` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `placeOfBirth` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `citizenshipYear` TEXT NOT NULL;
ALTER TABLE `patient_data` ADD `ILpopulation` TEXT NOT NULL;


#add lists to lists

INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value )
  VALUES ( 'lists','id_type','id_type', '300','1', '0');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value )
  VALUES ( 'lists','YNDN','YNDN', '301','1', '0');




#Add list for idintification types
Delete from list_options where list_id="id_type";
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`,`option_value`,`mapping`,`notes`,`codes`,`toggle_setting_1`,`toggle_setting_2`,`activity`,`subtype`) VALUES ('id_type','idnumber','Id. Number',1,1,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`,`option_value`,`mapping`,`notes`,`codes`,`toggle_setting_1`,`toggle_setting_2`,`activity`,`subtype`) VALUES ('id_type','passport','Passport',2,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`,`option_value`,`mapping`,`notes`,`codes`,`toggle_setting_1`,`toggle_setting_2`,`activity`,`subtype`) VALUES ('id_type','visa','Visa',0,0,0,'','','',0,0,1,'');

#Add list marital status
Delete from list_options where list_id="marital";
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'married', 'Married', '1', '', '0', '', 'M', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'single', 'Single', '2', '', '0', '', 'S', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'divorced', 'Divorced', '3', '', '0', '', 'D', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'widowed', 'Widowed', '4', '', '0', '', 'W', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'separated', 'Separated', '5', '', '0', '', 'L', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'domestic partner', 'Domestic Partner (No contract)', '6', '', '0', '', 'T', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'remarried', 'Remarried', '7', '', '0', '', 'R', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'public known', 'Publicly known', '8', '', '0', '', 'P', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'marital', 'partner w contract', 'Domestic Partner (With contract)', '9', '', '0', '', 'C', '', '', '', '1', '' );


#Add list yes no dontknow
Delete from list_options where list_id="YNDN";
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'YNDN', 'Yes', 'Yes', '1', '', '0', '', '', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'YNDN', 'No', 'No', '2', '', '0', '', '', '', '', '', '1', '' );
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype )
  VALUES ( 'YNDN', 'Dont Know', 'Don\'t Know', '3', '', '0', '', '', '', '', '', '1', '' );




