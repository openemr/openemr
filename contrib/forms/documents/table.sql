CREATE TABLE IF NOT EXISTS `form_documents` (
`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default NULL,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default NULL,
`activity` tinyint(4) default NULL,
`document_image` varchar (255),
`document_path`  tinytext,
`document_description` tinytext,
`document_source` varchar (255),
PRIMARY KEY (id)
) TYPE=MyISAM;
