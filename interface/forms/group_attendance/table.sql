CREATE TABLE IF NOT EXISTS `form_group_attendance` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` date NOT NULL ,
  `group_id` int(11),
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint NOT NULL ,
  `encounter_id` int(11),
  `activity` tinyint(4) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
# enable form to therapy group and not for patient
UPDATE `registry` SET `category` = 'Clinical' ,`patient_encounter`= 0 , `therapy_group_encounter`= 1 WHERE `directory` = 'group_attendance';