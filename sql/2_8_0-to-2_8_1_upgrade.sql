## ########################################################

## 
## Table structure for table `lang_constants`
## 

CREATE TABLE IF NOT EXISTS `lang_constants` (
  `cons_id` int(11) NOT NULL auto_increment,
  `constant_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `cons_name` (`constant_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

## 
## Dumping data for table `lang_constants`
## 

## ########################################################

## 
## Table structure for table `lang_definitions`
## 

CREATE TABLE IF NOT EXISTS `lang_definitions` (
  `def_id` int(11) NOT NULL auto_increment,
  `cons_id` int(11) NOT NULL default '0',
  `lang_id` int(11) NOT NULL default '0',
  `definition` mediumtext NOT NULL,
  UNIQUE KEY `def_id` (`def_id`),
  KEY `definition` (`definition`(100))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

## 
## Dumping data for table `lang_definitions`
## 


## ########################################################

## 
## Table structure for table `lang_languages`
## 

CREATE TABLE IF NOT EXISTS `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) NOT NULL default '',
  `lang_description` varchar(100) NOT NULL default '',
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

## 
## Dumping data for table `lang_languages`
## 

INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (1, 'en', 'English');
INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (2, 'se', 'Swedish');
INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (3, 'es', 'Spanish');
