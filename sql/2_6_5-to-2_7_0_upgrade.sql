ALTER TABLE `form_encounter` ADD `onset_date` DATETIME;

ALTER TABLE `patient_data` ADD `hipaa_mail` VARCHAR( 3 ) DEFAULT 'NO' NOT NULL ,
ADD `hipaa_voice` VARCHAR( 3 ) DEFAULT 'NO' NOT NULL ;
