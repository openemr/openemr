CREATE TABLE IF NOT EXISTS `form_CAMOS` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
category TEXT,
subcategory TEXT,
item TEXT,
content TEXT,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_category` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

category TEXT,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_subcategory` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

subcategory TEXT,
category_id bigint(20) NOT NULL,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_item` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

item TEXT,
content TEXT,
subcategory_id bigint(20) NOT NULL,

PRIMARY KEY (id)
) TYPE=MyISAM;
