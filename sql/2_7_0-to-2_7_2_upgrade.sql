ALTER TABLE `users` ADD `upin` varchar(255) default NULL;

CREATE TABLE issue_encounter (
  pid       int(11)    NOT NULL, -- pid from patient_data table
  list_id   int(11)    NOT NULL, -- id from lists table
  encounter int(11)    NOT NULL, -- encounter from form_encounters table
  resolved  tinyint(1) NOT NULL, -- if problem seems resolved with this encounter
  PRIMARY KEY (pid, list_id, encounter)
);


CREATE TABLE `immunization` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `immunization_name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=36 ;

INSERT INTO `immunization` VALUES (1, 'DTaP 1');
INSERT INTO `immunization` VALUES (2, 'DTaP 2');
INSERT INTO `immunization` VALUES (3, 'DTaP 3');
INSERT INTO `immunization` VALUES (4, 'DTaP 4');
INSERT INTO `immunization` VALUES (5, 'DTaP 5');
INSERT INTO `immunization` VALUES (6, 'DT 1');
INSERT INTO `immunization` VALUES (7, 'DT 2');
INSERT INTO `immunization` VALUES (8, 'DT 3');
INSERT INTO `immunization` VALUES (9, 'DT 4');
INSERT INTO `immunization` VALUES (10, 'DT 5');
INSERT INTO `immunization` VALUES (11, 'IPV 1');
INSERT INTO `immunization` VALUES (12, 'IPV 2');
INSERT INTO `immunization` VALUES (13, 'IPV 3');
INSERT INTO `immunization` VALUES (14, 'IPV 4');
INSERT INTO `immunization` VALUES (15, 'Hib 1');
INSERT INTO `immunization` VALUES (16, 'Hib 2');
INSERT INTO `immunization` VALUES (17, 'Hib 3');
INSERT INTO `immunization` VALUES (18, 'Hib 4');
INSERT INTO `immunization` VALUES (19, 'Pneumococcal Conjugate 1');
INSERT INTO `immunization` VALUES (20, 'Pneumococcal Conjugate 2');
INSERT INTO `immunization` VALUES (21, 'Pneumococcal Conjugate 3');
INSERT INTO `immunization` VALUES (22, 'Pneumococcal Conjugate 4');
INSERT INTO `immunization` VALUES (23, 'MMR 1');
INSERT INTO `immunization` VALUES (24, 'MMR 2');
INSERT INTO `immunization` VALUES (25, 'Varicella 1');
INSERT INTO `immunization` VALUES (26, 'Varicella 2');
INSERT INTO `immunization` VALUES (27, 'Hepatitis B 1');
INSERT INTO `immunization` VALUES (28, 'Hepatitis B 2');
INSERT INTO `immunization` VALUES (29, 'Hepatitis B 3');
INSERT INTO `immunization` VALUES (30, 'Influenza 1');
INSERT INTO `immunization` VALUES (31, 'Influenza 2');
INSERT INTO `immunization` VALUES (32, 'Td');
INSERT INTO `immunization` VALUES (33, 'Hepatitis A 1');
INSERT INTO `immunization` VALUES (34, 'Hepatitis A 2');
INSERT INTO `immunization` VALUES (35, 'Other');


CREATE TABLE `immunizations` (
  `id` bigint(20) NOT NULL auto_increment,
  `patient_id` int(11) default NULL,
  `administered_date` date default NULL,
  `immunization_id` int(11) default NULL,
  `manufacturer` varchar(100) default NULL,
  `lot_number` varchar(50) default NULL,
  `administered_by_id` bigint(20) default NULL,
  `education_date` date default NULL,
  `note` text,
  `create_date` datetime default NULL,
  `update_date` timestamp(14) NOT NULL,
  `created_by` bigint(20) default NULL,
  `updated_by` bigint(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


replace into immunizations (patient_id,create_date,note,created_by,updated_by,administered_by_id)
  select l.pid,l.date,concat(l.title,': ',l.comments),u.id, u.id, u.id
    from lists l
           left join users u on l.user = u.username
   where l.type = 'immunization';
