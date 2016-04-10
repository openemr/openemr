
#add lists to lists
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value )
  VALUES ( 'lists','income_sources','income_sources', '298','1', '0');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value )
  VALUES ( 'lists','ss_branches','ss_branches', '301','1', '0')  ;

#add values to the lists
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype ) VALUES ( 'income_sources', 'social security', 'Social Security', '', '', '0', '', '', '', '', '', '1', '');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype ) VALUES ( 'income_sources', 'walfare', 'Walfare', '', '', '0', '', '', '', '', '', '1', '');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype ) VALUES ( 'income_sources', 'ministry defence', 'Ministry of defence', '', '', '0', '', '', '', '', '', '1', '');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype ) VALUES ( 'income_sources', 'germany', 'Reparations from Germany', '', '', '0', '', '', '', '', '', '1', '');


#Fields of the form
Delete from layout_options where group_name="4Employer";
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','occupation','4Employer','Occupation',1,26,1,0,0,'Occupation',1,1,'','','Occupation',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_name','4Employer','Employer Name',2,2,1,20,63,'',1,1,'','C','Employer Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_street','4Employer','Employer Address',3,2,1,25,63,'',1,1,'','C','Street and Number',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_city','4Employer','City',4,2,1,15,63,'',1,1,'','C','City Name',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_state','4Employer','State',5,26,1,0,0,'state',1,1,'','','State/Locality',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_postal_code','4Employer','Postal Code',6,2,1,6,63,'',1,1,'','','Postal Code',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','em_country','4Employer','Country',7,26,1,0,0,'country',1,1,'','','Country',0,'','F','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`,`list_backup_id`,`source`,`conditions`) VALUES ('DEM','industry','4Employer','Industry',8,26,1,0,0,'Industry',1,1,'','','Industry',0,'','F','');



