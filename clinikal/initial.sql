
##Demographics Who
#Set inactive the Ms. from title list
Delete from list_options;
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Mr.', 'Mr.', 1, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Mrs.', 'Mrs.', 2, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default,activity ) VALUES ('titles', 'Ms.', 'Ms.', 3, 0, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('titles', 'Dr.', 'Dr.', 4, 0);

#Hide mname
  UPDATE layout_options SET uor = '0' WHERE form_id = 'DEM' AND field_id = 'mname';
#Required fname
  UPDATE layout_options SET uor = '2' WHERE form_id = 'DEM' AND field_id = 'fname';
#Required lname
  UPDATE layout_options SET uor = '2' WHERE form_id = 'DEM' AND field_id = 'lname';
