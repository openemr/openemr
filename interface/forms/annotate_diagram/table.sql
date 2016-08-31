CREATE TABLE `form_annotate_diagram` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT '1',
  `data` text DEFAULT '' NULL,
  `imagedata` varchar(255) DEFAULT 'NEW',
  `dyntitle` varchar(255) DEFAULT 'Annotated Diagram',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB