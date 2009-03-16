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


#### START SQL DUMP DATA ######


-- MySQL dump 10.9
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	4.1.10a-standard

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `form_CAMOS_category`
--

DROP TABLE IF EXISTS `form_CAMOS_category`;
CREATE TABLE `form_CAMOS_category` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `category` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Dumping data for table `form_CAMOS_category`
--


/*!40000 ALTER TABLE `form_CAMOS_category` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_category` WRITE;
INSERT INTO `form_CAMOS_category` VALUES (13,NULL,NULL,NULL,NULL,NULL,NULL,'prescriptions'),(14,NULL,NULL,NULL,NULL,NULL,NULL,'referrals'),(15,NULL,NULL,NULL,NULL,NULL,NULL,'radiology'),(16,NULL,NULL,NULL,NULL,NULL,NULL,'exam'),(17,NULL,NULL,NULL,NULL,NULL,NULL,'text fragments');
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_category` ENABLE KEYS */;

--
-- Table structure for table `form_CAMOS_subcategory`
--

DROP TABLE IF EXISTS `form_CAMOS_subcategory`;
CREATE TABLE `form_CAMOS_subcategory` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `subcategory` text,
  `category_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Dumping data for table `form_CAMOS_subcategory`
--


/*!40000 ALTER TABLE `form_CAMOS_subcategory` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_subcategory` WRITE;
INSERT INTO `form_CAMOS_subcategory` VALUES (90,NULL,NULL,NULL,NULL,NULL,NULL,'by dx',16),(91,NULL,NULL,NULL,NULL,NULL,NULL,'antibiotics',13),(92,NULL,NULL,NULL,NULL,NULL,NULL,'analgesics',13),(93,NULL,NULL,NULL,NULL,NULL,NULL,'orthopedics',14),(94,NULL,NULL,NULL,NULL,NULL,NULL,'cardiology',14),(95,NULL,NULL,NULL,NULL,NULL,NULL,'endocrinology',14),(96,NULL,NULL,NULL,NULL,NULL,NULL,'MRI',15),(97,NULL,NULL,NULL,NULL,NULL,NULL,'X-Ray',15),(98,NULL,NULL,NULL,NULL,NULL,NULL,'prescription',17),(99,NULL,NULL,NULL,NULL,NULL,NULL,'physical',17),(100,NULL,NULL,NULL,NULL,NULL,NULL,'htn',13);
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_subcategory` ENABLE KEYS */;

--
-- Table structure for table `form_CAMOS_item`
--

DROP TABLE IF EXISTS `form_CAMOS_item`;
CREATE TABLE `form_CAMOS_item` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `item` text,
  `content` text,
  `subcategory_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Dumping data for table `form_CAMOS_item`
--


/*!40000 ALTER TABLE `form_CAMOS_item` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_item` WRITE;
INSERT INTO `form_CAMOS_item` VALUES (259,NULL,NULL,NULL,NULL,NULL,NULL,'tonsillitis','',90),(260,NULL,NULL,NULL,NULL,NULL,NULL,'basic normal 01','/* This is just an example, not necessarily a good example! */\r\nGeneral: Alert and Oriented x 3, in no\r\n         apparent distress.\r\nHEENT:   Head is normocephalic, atraumatic,\r\n         Ears are unremarkable, Throat unremarkable\r\nNeck:    Supple, no carotid bruits ausc.\r\nHeart:   Regular rate and rhythm, no murmurs, rubs,\r\n         or gallops ausc.\r\nLungs:   Clear, no wheezes, rales, or rhonchi\r\nAbdomen: Soft, non-tender, bowel sounds present x 4q\r\nNeuro:   CN II->XII grossly intact\r\n',99),(261,NULL,NULL,NULL,NULL,NULL,NULL,'health checkup','Routine physical exam\r\n\r\n/*replace::basic normal 01*/\r\n\r\nAssessment:\r\n\r\nv70.0\r\n\r\nPlan:\r\n\r\nfollow up prn.',90),(262,NULL,NULL,NULL,NULL,NULL,NULL,'30 tablets qd','dispense: #30/thirty tablets.\r\n\r\nsig: Take one tablet by mouth once daily.',98),(263,NULL,NULL,NULL,NULL,NULL,NULL,'atenolol','Atenolol 25mg\r\n\r\n/*replace::30 tablets qd*/',100),(264,NULL,NULL,NULL,NULL,NULL,NULL,'90 tablets qd','dispense: #90/ninety tablets.\r\n\r\nsig: Take one tablet by mouth once daily.',98);
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_item` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

