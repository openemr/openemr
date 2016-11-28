CREATE TABLE IF NOT EXISTS `form_therapy_groups_attendance` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL ,
  `group_id` int(11),
  `group_name` int(11) NOT NULL ,
  `authorized` tinyint NOT NULL ,
  `encounter_id` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;