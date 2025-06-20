CREATE TABLE IF NOT EXISTS `form_brief_aan_verwijzer` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `introductie` longtext,
  `reden_van_aanmelding` longtext,
  `anamnese` longtext,
  `psychiatrisch_onderzoek` longtext,
  `beschrijvend_conclusie` longtext,
  `advies_beleid` longtext,
  `autosave_flag` tinyint(4) default 1,
  `autosave_datetime` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 ;
