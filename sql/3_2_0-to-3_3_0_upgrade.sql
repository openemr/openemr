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
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','apptstat','Appointment Statuses',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','-','- None',1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','*','* Reminder done' , 1);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','+','+ Chart pulled'  , 2);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','x','x Canceled'      , 3);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','?','? No show'       , 4);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','@','@ Arrived'       , 5);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','~','~ Arrived late'  , 6);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','!','! Left w/o visit', 7);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','#','# Ins/fin issue' , 8);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','<','< In exam room'  , 9);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','>','> Checked out'   ,10);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','$','$ Coding done'   ,11);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('apptstat','%','% Canceled < 24h',12);
ALTER TABLE openemr_postcalendar_events CHANGE pc_apptstatus pc_apptstatus varchar(15) NOT NULL DEFAULT '-';
#EndIf

