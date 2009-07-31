--
-- Table structure for table `articles`
--

CREATE TABLE `autosave_articles` (
  `id` int(5) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `content` varchar(255) NOT NULL default '',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
