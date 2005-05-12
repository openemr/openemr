ALTER TABLE `users` ADD `upin` varchar(255) default NULL;

CREATE TABLE issue_encounter (
  pid       int(11)    NOT NULL, -- pid from patient_data table
  list_id   int(11)    NOT NULL, -- id from lists table
  encounter int(11)    NOT NULL, -- encounter from form_encounters table
  resolved  tinyint(1) NOT NULL, -- if problem seems resolved with this encounter
  PRIMARY KEY (pid, list_id, encounter)
);
