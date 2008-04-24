CREATE TABLE IF NOT EXISTS `form_brief_aan_verwijzer` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set latin1 default NULL,
  `groupname` varchar(255) character set latin1 default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `introductie` longtext character set latin1,
  `reden_van_aanmelding` longtext character set latin1,
  `anamnese` longtext character set latin1,
  `psychiatrisch_onderzoek` longtext character set latin1,
  `beschrijvend_conclusie` longtext character set latin1,
  `advies_beleid` longtext character set latin1,
  `autosave_flag` tinyint(4) default 1,
  `autosave_datetime` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;
