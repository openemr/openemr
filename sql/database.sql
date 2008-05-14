-- phpMyAdmin SQL Dump
-- version 2.6.4-pl4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 21, 2008 at 06:19 PM
-- Server version: 5.0.17
-- PHP Version: 5.1.1
-- 
-- Database: `openemr`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `addresses`
-- 

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL default '0',
  `line1` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `line2` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `state` varchar(35) character set utf8 collate utf8_unicode_ci default NULL,
  `zip` varchar(10) character set utf8 collate utf8_unicode_ci default NULL,
  `plus_four` varchar(4) character set utf8 collate utf8_unicode_ci default NULL,
  `country` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `foreign_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `foreign_id` (`foreign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `addresses`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `array`
-- 

DROP TABLE IF EXISTS `array`;
CREATE TABLE `array` (
  `array_key` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `array_value` longtext character set utf8 collate utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `array`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `batchcom`
-- 

DROP TABLE IF EXISTS `batchcom`;
CREATE TABLE `batchcom` (
  `id` bigint(20) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL default '0',
  `sent_by` bigint(20) NOT NULL default '0',
  `msg_type` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `msg_subject` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `msg_text` mediumtext character set utf8 collate utf8_unicode_ci,
  `msg_date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `batchcom`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `billing`
-- 

DROP TABLE IF EXISTS `billing`;
CREATE TABLE `billing` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime default NULL,
  `code_type` varchar(7) character set utf8 collate utf8_unicode_ci default NULL,
  `code` varchar(9) character set utf8 collate utf8_unicode_ci default NULL,
  `pid` int(11) default NULL,
  `provider_id` int(11) default NULL,
  `user` int(11) default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(1) default NULL,
  `encounter` int(11) default NULL,
  `code_text` longtext character set utf8 collate utf8_unicode_ci,
  `billed` tinyint(1) default NULL,
  `activity` tinyint(1) default NULL,
  `payer_id` int(11) default NULL,
  `bill_process` tinyint(2) NOT NULL default '0',
  `bill_date` datetime default NULL,
  `process_date` datetime default NULL,
  `process_file` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `modifier` varchar(5) character set utf8 collate utf8_unicode_ci default NULL,
  `units` tinyint(3) default NULL,
  `fee` decimal(7,2) default NULL,
  `justify` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `target` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_partner_id` int(11) default NULL,
  `ndc_info` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `billing`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `categories`
-- 

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `value` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rght` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `lft` (`lft`,`rght`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `categories`
-- 

INSERT INTO `categories` VALUES (1, 'Categories', '', 0, 0, 9);
INSERT INTO `categories` VALUES (2, 'Lab Report', '', 1, 1, 2);
INSERT INTO `categories` VALUES (3, 'Medical Record', '', 1, 3, 4);
INSERT INTO `categories` VALUES (4, 'Patient Information', '', 1, 5, 8);
INSERT INTO `categories` VALUES (5, 'Patient ID card', '', 4, 6, 7);

-- --------------------------------------------------------

-- 
-- Table structure for table `categories_seq`
-- 

DROP TABLE IF EXISTS `categories_seq`;
CREATE TABLE `categories_seq` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `categories_seq`
-- 

INSERT INTO `categories_seq` VALUES (5);

-- --------------------------------------------------------

-- 
-- Table structure for table `categories_to_documents`
-- 

DROP TABLE IF EXISTS `categories_to_documents`;
CREATE TABLE `categories_to_documents` (
  `category_id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `categories_to_documents`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `claims`
-- 

DROP TABLE IF EXISTS `claims`;
CREATE TABLE `claims` (
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `version` int(10) unsigned NOT NULL auto_increment,
  `payer_id` int(11) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `payer_type` tinyint(4) NOT NULL default '0',
  `bill_process` tinyint(2) NOT NULL default '0',
  `bill_time` datetime default NULL,
  `process_time` datetime default NULL,
  `process_file` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `target` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_partner_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`patient_id`,`encounter_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `claims`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `codes`
-- 

DROP TABLE IF EXISTS `codes`;
CREATE TABLE `codes` (
  `id` int(11) NOT NULL auto_increment,
  `code_text` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `code_text_short` varchar(24) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `code` varchar(10) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `code_type` tinyint(2) default NULL,
  `modifier` varchar(5) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `units` tinyint(3) default NULL,
  `fee` decimal(7,2) default NULL,
  `superbill` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `related_code` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `taxrates` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `codes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `config`
-- 

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `value` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rght` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `lft` (`lft`,`rght`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `config`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `config_seq`
-- 

DROP TABLE IF EXISTS `config_seq`;
CREATE TABLE `config_seq` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `config_seq`
-- 

INSERT INTO `config_seq` VALUES (0);

-- --------------------------------------------------------

-- 
-- Table structure for table `documents`
-- 

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL default '0',
  `type` enum('file_url','blob','web_url') character set latin1 default NULL,
  `size` int(11) default NULL,
  `date` datetime default NULL,
  `url` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `mimetype` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pages` int(11) default NULL,
  `owner` int(11) default NULL,
  `revision` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `foreign_id` int(11) default NULL,
  `docdate` date default NULL,
  `list_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `revision` (`revision`),
  KEY `foreign_id` (`foreign_id`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `documents`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `drug_inventory`
-- 

DROP TABLE IF EXISTS `drug_inventory`;
CREATE TABLE `drug_inventory` (
  `inventory_id` int(11) NOT NULL auto_increment,
  `drug_id` int(11) NOT NULL,
  `lot_number` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `expiration` date default NULL,
  `manufacturer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `on_hand` int(11) NOT NULL default '0',
  `last_notify` date NOT NULL default '0000-00-00',
  `destroy_date` date default NULL,
  `destroy_method` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `destroy_witness` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `destroy_notes` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`inventory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `drug_inventory`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `drug_sales`
-- 

DROP TABLE IF EXISTS `drug_sales`;
CREATE TABLE `drug_sales` (
  `sale_id` int(11) NOT NULL auto_increment,
  `drug_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL default '0',
  `pid` int(11) NOT NULL default '0',
  `encounter` int(11) NOT NULL default '0',
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `sale_date` date NOT NULL,
  `quantity` int(11) NOT NULL default '0',
  `fee` decimal(7,2) NOT NULL default '0.00',
  `billed` tinyint(1) NOT NULL default '0' COMMENT 'indicates if the sale is posted to accounting',
  PRIMARY KEY  (`sale_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `drug_sales`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `drug_templates`
-- 

DROP TABLE IF EXISTS `drug_templates`;
CREATE TABLE `drug_templates` (
  `drug_id` int(11) NOT NULL,
  `selector` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `dosage` varchar(10) character set utf8 collate utf8_unicode_ci default NULL,
  `period` int(11) NOT NULL default '0',
  `quantity` int(11) NOT NULL default '0',
  `refills` int(11) NOT NULL default '0',
  `taxrates` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`drug_id`,`selector`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `drug_templates`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `drugs`
-- 

DROP TABLE IF EXISTS `drugs`;
CREATE TABLE `drugs` (
  `drug_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `ndc_number` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `on_order` int(11) NOT NULL default '0',
  `reorder_point` int(11) NOT NULL default '0',
  `last_notify` date NOT NULL default '0000-00-00',
  `reactions` text character set utf8 collate utf8_unicode_ci,
  `form` int(3) NOT NULL default '0',
  `size` float unsigned NOT NULL default '0',
  `unit` int(11) NOT NULL default '0',
  `route` int(11) NOT NULL default '0',
  `substitute` int(11) NOT NULL default '0',
  `related_code` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'may reference a related codes.code',
  PRIMARY KEY  (`drug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `drugs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `employer_data`
-- 

DROP TABLE IF EXISTS `employer_data`;
CREATE TABLE `employer_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `street` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `postal_code` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `state` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `country` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `date` datetime default NULL,
  `pid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `employer_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `facility`
-- 

DROP TABLE IF EXISTS `facility`;
CREATE TABLE `facility` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `phone` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `fax` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `street` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `state` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `postal_code` varchar(11) character set utf8 collate utf8_unicode_ci default NULL,
  `country_code` varchar(10) character set utf8 collate utf8_unicode_ci default NULL,
  `federal_ein` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  `service_location` tinyint(1) NOT NULL default '1',
  `billing_location` tinyint(1) NOT NULL default '0',
  `accepts_assignment` tinyint(1) NOT NULL default '0',
  `pos_code` tinyint(4) default NULL,
  `x12_sender_id` varchar(25) character set utf8 collate utf8_unicode_ci default NULL,
  `attn` varchar(65) character set utf8 collate utf8_unicode_ci default NULL,
  `domain_identifier` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `facility_npi` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `facility`
-- 

INSERT INTO `facility` VALUES (3, 'Your Clinic Name Here', '000-000-0000', '000-000-0000', '', '', '', '', '', '', 1, 0, 0, NULL, '', '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `fee_sheet_options`
-- 

DROP TABLE IF EXISTS `fee_sheet_options`;
CREATE TABLE `fee_sheet_options` (
  `fs_category` varchar(63) character set utf8 collate utf8_unicode_ci default NULL,
  `fs_option` varchar(63) character set utf8 collate utf8_unicode_ci default NULL,
  `fs_codes` varchar(255) character set utf8 collate utf8_unicode_ci default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `fee_sheet_options`
-- 

INSERT INTO `fee_sheet_options` VALUES ('1New Patient', '1Brief', 'CPT4|99201|');
INSERT INTO `fee_sheet_options` VALUES ('1New Patient', '2Limited', 'CPT4|99202|');
INSERT INTO `fee_sheet_options` VALUES ('1New Patient', '3Detailed', 'CPT4|99203|');
INSERT INTO `fee_sheet_options` VALUES ('1New Patient', '4Extended', 'CPT4|99204|');
INSERT INTO `fee_sheet_options` VALUES ('1New Patient', '5Comprehensive', 'CPT4|99205|');
INSERT INTO `fee_sheet_options` VALUES ('2Established Patient', '1Brief', 'CPT4|99211|');
INSERT INTO `fee_sheet_options` VALUES ('2Established Patient', '2Limited', 'CPT4|99212|');
INSERT INTO `fee_sheet_options` VALUES ('2Established Patient', '3Detailed', 'CPT4|99213|');
INSERT INTO `fee_sheet_options` VALUES ('2Established Patient', '4Extended', 'CPT4|99214|');
INSERT INTO `fee_sheet_options` VALUES ('2Established Patient', '5Comprehensive', 'CPT4|99215|');

-- --------------------------------------------------------

-- 
-- Table structure for table `form_dictation`
-- 

DROP TABLE IF EXISTS `form_dictation`;
CREATE TABLE `form_dictation` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `dictation` longtext character set utf8 collate utf8_unicode_ci,
  `additional_notes` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_dictation`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_encounter`
-- 

DROP TABLE IF EXISTS `form_encounter`;
CREATE TABLE `form_encounter` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `reason` longtext character set utf8 collate utf8_unicode_ci,
  `facility` longtext character set utf8 collate utf8_unicode_ci,
  `facility_id` int(11) NOT NULL default '0',
  `pid` bigint(20) default NULL,
  `encounter` bigint(20) default NULL,
  `onset_date` datetime default NULL,
  `sensitivity` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `billing_note` text character set utf8 collate utf8_unicode_ci,
  `pc_catid` int(11) NOT NULL default '5' COMMENT 'event category from openemr_postcalendar_categories',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_encounter`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_misc_billing_options`
-- 

DROP TABLE IF EXISTS `form_misc_billing_options`;
CREATE TABLE `form_misc_billing_options` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `employment_related` tinyint(1) default NULL,
  `auto_accident` tinyint(1) default NULL,
  `accident_state` varchar(2) character set utf8 collate utf8_unicode_ci default NULL,
  `other_accident` tinyint(1) default NULL,
  `outside_lab` tinyint(1) default NULL,
  `lab_amount` decimal(5,2) default NULL,
  `is_unable_to_work` tinyint(1) default NULL,
  `off_work_from` date default NULL,
  `off_work_to` date default NULL,
  `is_hospitalized` tinyint(1) default NULL,
  `hospitalization_date_from` date default NULL,
  `hospitalization_date_to` date default NULL,
  `medicaid_resubmission_code` varchar(10) character set utf8 collate utf8_unicode_ci default NULL,
  `medicaid_original_reference` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  `prior_auth_number` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `comments` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_misc_billing_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_reviewofs`
-- 

DROP TABLE IF EXISTS `form_reviewofs`;
CREATE TABLE `form_reviewofs` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `fever` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `chills` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `night_sweats` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `weight_loss` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `poor_appetite` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `insomnia` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `fatigued` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `depressed` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `hyperactive` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `exposure_to_foreign_countries` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cataracts` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cataract_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `glaucoma` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `double_vision` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `blurred_vision` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `poor_hearing` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `headaches` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `ringing_in_ears` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `bloody_nose` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `sinusitis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `sinus_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `dry_mouth` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `strep_throat` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `tonsillectomy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `swollen_lymph_nodes` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `throat_cancer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `throat_cancer_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `heart_attack` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `irregular_heart_beat` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `chest_pains` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `shortness_of_breath` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `high_blood_pressure` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `heart_failure` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `poor_circulation` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `vascular_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cardiac_catheterization` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `coronary_artery_bypass` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `heart_transplant` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `stress_test` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `emphysema` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `chronic_bronchitis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `interstitial_lung_disease` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `shortness_of_breath_2` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `lung_cancer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `lung_cancer_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pheumothorax` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `stomach_pains` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `peptic_ulcer_disease` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `gastritis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `endoscopy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `polyps` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `colonoscopy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `colon_cancer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `colon_cancer_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `ulcerative_colitis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `crohns_disease` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `appendectomy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `divirticulitis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `divirticulitis_surgery` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `gall_stones` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cholecystectomy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `hepatitis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cirrhosis_of_the_liver` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `splenectomy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `kidney_failure` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `kidney_stones` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `kidney_cancer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `kidney_infections` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `bladder_infections` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `bladder_cancer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `prostate_problems` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `prostate_cancer` varchar(255) character set latin1 default NULL,
  `kidney_transplant` varchar(255) character set latin1 default NULL,
  `sexually_transmitted_disease` varchar(255) character set latin1 default NULL,
  `burning_with_urination` varchar(255) character set latin1 default NULL,
  `discharge_from_urethra` varchar(255) character set latin1 default NULL,
  `rashes` varchar(255) character set latin1 default NULL,
  `infections` varchar(255) character set latin1 default NULL,
  `ulcerations` varchar(255) character set latin1 default NULL,
  `pemphigus` varchar(255) character set latin1 default NULL,
  `herpes` varchar(255) character set latin1 default NULL,
  `osetoarthritis` varchar(255) character set latin1 default NULL,
  `rheumotoid_arthritis` varchar(255) character set latin1 default NULL,
  `lupus` varchar(255) character set latin1 default NULL,
  `ankylosing_sondlilitis` varchar(255) character set latin1 default NULL,
  `swollen_joints` varchar(255) character set latin1 default NULL,
  `stiff_joints` varchar(255) character set latin1 default NULL,
  `broken_bones` varchar(255) character set latin1 default NULL,
  `neck_problems` varchar(255) character set latin1 default NULL,
  `back_problems` varchar(255) character set latin1 default NULL,
  `back_surgery` varchar(255) character set latin1 default NULL,
  `scoliosis` varchar(255) character set latin1 default NULL,
  `herniated_disc` varchar(255) character set latin1 default NULL,
  `shoulder_problems` varchar(255) character set latin1 default NULL,
  `elbow_problems` varchar(255) character set latin1 default NULL,
  `wrist_problems` varchar(255) character set latin1 default NULL,
  `hand_problems` varchar(255) character set latin1 default NULL,
  `hip_problems` varchar(255) character set latin1 default NULL,
  `knee_problems` varchar(255) character set latin1 default NULL,
  `ankle_problems` varchar(255) character set latin1 default NULL,
  `foot_problems` varchar(255) character set latin1 default NULL,
  `insulin_dependent_diabetes` varchar(255) character set latin1 default NULL,
  `noninsulin_dependent_diabetes` varchar(255) character set latin1 default NULL,
  `hypothyroidism` varchar(255) character set latin1 default NULL,
  `hyperthyroidism` varchar(255) character set latin1 default NULL,
  `cushing_syndrom` varchar(255) character set latin1 default NULL,
  `addison_syndrom` varchar(255) character set latin1 default NULL,
  `additional_notes` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_reviewofs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_ros`
-- 

DROP TABLE IF EXISTS `form_ros`;
CREATE TABLE `form_ros` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL,
  `activity` int(11) NOT NULL default '1',
  `date` datetime default NULL,
  `weight_change` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `weakness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `fatigue` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `anorexia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `fever` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `chills` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `night_sweats` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `insomnia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `irritability` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `heat_or_cold` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `intolerance` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `change_in_vision` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `glaucoma_history` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `eye_pain` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `irritation` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `redness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `excessive_tearing` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `double_vision` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `blind_spots` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `photophobia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hearing_loss` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `discharge` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `pain` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `vertigo` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `tinnitus` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `frequent_colds` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `sore_throat` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `sinus_problems` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `post_nasal_drip` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `nosebleed` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `snoring` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `apnea` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `breast_mass` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `breast_discharge` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `biopsy` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `abnormal_mammogram` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `cough` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `sputum` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `shortness_of_breath` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `wheezing` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hemoptsyis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `asthma` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `copd` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `chest_pain` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `palpitation` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `syncope` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `pnd` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `doe` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `orthopnea` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `peripheal` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `edema` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `legpain_cramping` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `history_murmur` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `arrythmia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `heart_problem` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `dysphagia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `heartburn` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `bloating` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `belching` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `flatulence` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `nausea` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `vomiting` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hematemesis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `gastro_pain` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `food_intolerance` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hepatitis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `jaundice` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hematochezia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `changed_bowel` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `diarrhea` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `constipation` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `polyuria` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `polydypsia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `dysuria` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hematuria` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `frequency` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `urgency` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `incontinence` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `renal_stones` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `utis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hesitancy` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `dribbling` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `stream` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `nocturia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `erections` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `ejaculations` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `g` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `p` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `ap` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `lc` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `mearche` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `menopause` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `lmp` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `f_frequency` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `f_flow` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `f_symptoms` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `abnormal_hair_growth` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `f_hirsutism` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `joint_pain` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `swelling` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `m_redness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `m_warm` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `m_stiffness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `muscle` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `m_aches` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `fms` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `arthritis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `loc` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `seizures` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `stroke` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `tia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `n_numbness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `n_weakness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `paralysis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `intellectual_decline` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `memory_problems` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `dementia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `n_headache` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `s_cancer` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `psoriasis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `s_acne` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `s_other` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `s_disease` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `p_diagnosis` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `p_medication` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `depression` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `anxiety` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `social_difficulties` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `thyroid_problems` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `diabetes` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `abnormal_blood` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `anemia` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `fh_blood_problems` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `bleeding_problems` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `allergies` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `frequent_illness` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hiv` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  `hai_status` varchar(3) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_ros`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_soap`
-- 

DROP TABLE IF EXISTS `form_soap`;
CREATE TABLE `form_soap` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default '0',
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default '0',
  `activity` tinyint(4) default '0',
  `subjective` text character set utf8 collate utf8_unicode_ci,
  `objective` text character set utf8 collate utf8_unicode_ci,
  `assessment` text character set utf8 collate utf8_unicode_ci,
  `plan` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_soap`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form_vitals`
-- 

DROP TABLE IF EXISTS `form_vitals`;
CREATE TABLE `form_vitals` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default '0',
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default '0',
  `activity` tinyint(4) default '0',
  `bps` varchar(40) character set utf8 collate utf8_unicode_ci default NULL,
  `bpd` varchar(40) character set utf8 collate utf8_unicode_ci default NULL,
  `weight` float(5,2) default '0.00',
  `height` float(5,2) default '0.00',
  `temperature` float(5,2) default '0.00',
  `temp_method` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pulse` float(5,2) default '0.00',
  `respiration` float(5,2) default '0.00',
  `note` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `BMI` float(4,1) default '0.0',
  `BMI_status` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `waist_circ` float(5,2) default '0.00',
  `head_circ` float(4,2) default '0.00',
  `oxygen_saturation` float(5,2) default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form_vitals`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forms`
-- 

DROP TABLE IF EXISTS `forms`;
CREATE TABLE `forms` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `encounter` bigint(20) default NULL,
  `form_name` longtext character set utf8 collate utf8_unicode_ci,
  `form_id` bigint(20) default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `authorized` tinyint(4) default NULL,
  `formdir` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `forms`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `geo_country_reference`
-- 

DROP TABLE IF EXISTS `geo_country_reference`;
CREATE TABLE `geo_country_reference` (
  `countries_id` int(5) NOT NULL auto_increment,
  `countries_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `countries_iso_code_2` char(2) character set latin1 NOT NULL default '',
  `countries_iso_code_3` char(3) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`countries_id`),
  KEY `IDX_COUNTRIES_NAME` (`countries_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=240 ;

-- 
-- Dumping data for table `geo_country_reference`
-- 

INSERT INTO `geo_country_reference` VALUES (1, 'Afghanistan', 'AF', 'AFG');
INSERT INTO `geo_country_reference` VALUES (2, 'Albania', 'AL', 'ALB');
INSERT INTO `geo_country_reference` VALUES (3, 'Algeria', 'DZ', 'DZA');
INSERT INTO `geo_country_reference` VALUES (4, 'American Samoa', 'AS', 'ASM');
INSERT INTO `geo_country_reference` VALUES (5, 'Andorra', 'AD', 'AND');
INSERT INTO `geo_country_reference` VALUES (6, 'Angola', 'AO', 'AGO');
INSERT INTO `geo_country_reference` VALUES (7, 'Anguilla', 'AI', 'AIA');
INSERT INTO `geo_country_reference` VALUES (8, 'Antarctica', 'AQ', 'ATA');
INSERT INTO `geo_country_reference` VALUES (9, 'Antigua and Barbuda', 'AG', 'ATG');
INSERT INTO `geo_country_reference` VALUES (10, 'Argentina', 'AR', 'ARG');
INSERT INTO `geo_country_reference` VALUES (11, 'Armenia', 'AM', 'ARM');
INSERT INTO `geo_country_reference` VALUES (12, 'Aruba', 'AW', 'ABW');
INSERT INTO `geo_country_reference` VALUES (13, 'Australia', 'AU', 'AUS');
INSERT INTO `geo_country_reference` VALUES (14, 'Austria', 'AT', 'AUT');
INSERT INTO `geo_country_reference` VALUES (15, 'Azerbaijan', 'AZ', 'AZE');
INSERT INTO `geo_country_reference` VALUES (16, 'Bahamas', 'BS', 'BHS');
INSERT INTO `geo_country_reference` VALUES (17, 'Bahrain', 'BH', 'BHR');
INSERT INTO `geo_country_reference` VALUES (18, 'Bangladesh', 'BD', 'BGD');
INSERT INTO `geo_country_reference` VALUES (19, 'Barbados', 'BB', 'BRB');
INSERT INTO `geo_country_reference` VALUES (20, 'Belarus', 'BY', 'BLR');
INSERT INTO `geo_country_reference` VALUES (21, 'Belgium', 'BE', 'BEL');
INSERT INTO `geo_country_reference` VALUES (22, 'Belize', 'BZ', 'BLZ');
INSERT INTO `geo_country_reference` VALUES (23, 'Benin', 'BJ', 'BEN');
INSERT INTO `geo_country_reference` VALUES (24, 'Bermuda', 'BM', 'BMU');
INSERT INTO `geo_country_reference` VALUES (25, 'Bhutan', 'BT', 'BTN');
INSERT INTO `geo_country_reference` VALUES (26, 'Bolivia', 'BO', 'BOL');
INSERT INTO `geo_country_reference` VALUES (27, 'Bosnia and Herzegowina', 'BA', 'BIH');
INSERT INTO `geo_country_reference` VALUES (28, 'Botswana', 'BW', 'BWA');
INSERT INTO `geo_country_reference` VALUES (29, 'Bouvet Island', 'BV', 'BVT');
INSERT INTO `geo_country_reference` VALUES (30, 'Brazil', 'BR', 'BRA');
INSERT INTO `geo_country_reference` VALUES (31, 'British Indian Ocean Territory', 'IO', 'IOT');
INSERT INTO `geo_country_reference` VALUES (32, 'Brunei Darussalam', 'BN', 'BRN');
INSERT INTO `geo_country_reference` VALUES (33, 'Bulgaria', 'BG', 'BGR');
INSERT INTO `geo_country_reference` VALUES (34, 'Burkina Faso', 'BF', 'BFA');
INSERT INTO `geo_country_reference` VALUES (35, 'Burundi', 'BI', 'BDI');
INSERT INTO `geo_country_reference` VALUES (36, 'Cambodia', 'KH', 'KHM');
INSERT INTO `geo_country_reference` VALUES (37, 'Cameroon', 'CM', 'CMR');
INSERT INTO `geo_country_reference` VALUES (38, 'Canada', 'CA', 'CAN');
INSERT INTO `geo_country_reference` VALUES (39, 'Cape Verde', 'CV', 'CPV');
INSERT INTO `geo_country_reference` VALUES (40, 'Cayman Islands', 'KY', 'CYM');
INSERT INTO `geo_country_reference` VALUES (41, 'Central African Republic', 'CF', 'CAF');
INSERT INTO `geo_country_reference` VALUES (42, 'Chad', 'TD', 'TCD');
INSERT INTO `geo_country_reference` VALUES (43, 'Chile', 'CL', 'CHL');
INSERT INTO `geo_country_reference` VALUES (44, 'China', 'CN', 'CHN');
INSERT INTO `geo_country_reference` VALUES (45, 'Christmas Island', 'CX', 'CXR');
INSERT INTO `geo_country_reference` VALUES (46, 'Cocos (Keeling) Islands', 'CC', 'CCK');
INSERT INTO `geo_country_reference` VALUES (47, 'Colombia', 'CO', 'COL');
INSERT INTO `geo_country_reference` VALUES (48, 'Comoros', 'KM', 'COM');
INSERT INTO `geo_country_reference` VALUES (49, 'Congo', 'CG', 'COG');
INSERT INTO `geo_country_reference` VALUES (50, 'Cook Islands', 'CK', 'COK');
INSERT INTO `geo_country_reference` VALUES (51, 'Costa Rica', 'CR', 'CRI');
INSERT INTO `geo_country_reference` VALUES (52, 'Cote D Ivoire', 'CI', 'CIV');
INSERT INTO `geo_country_reference` VALUES (53, 'Croatia', 'HR', 'HRV');
INSERT INTO `geo_country_reference` VALUES (54, 'Cuba', 'CU', 'CUB');
INSERT INTO `geo_country_reference` VALUES (55, 'Cyprus', 'CY', 'CYP');
INSERT INTO `geo_country_reference` VALUES (56, 'Czech Republic', 'CZ', 'CZE');
INSERT INTO `geo_country_reference` VALUES (57, 'Denmark', 'DK', 'DNK');
INSERT INTO `geo_country_reference` VALUES (58, 'Djibouti', 'DJ', 'DJI');
INSERT INTO `geo_country_reference` VALUES (59, 'Dominica', 'DM', 'DMA');
INSERT INTO `geo_country_reference` VALUES (60, 'Dominican Republic', 'DO', 'DOM');
INSERT INTO `geo_country_reference` VALUES (61, 'East Timor', 'TP', 'TMP');
INSERT INTO `geo_country_reference` VALUES (62, 'Ecuador', 'EC', 'ECU');
INSERT INTO `geo_country_reference` VALUES (63, 'Egypt', 'EG', 'EGY');
INSERT INTO `geo_country_reference` VALUES (64, 'El Salvador', 'SV', 'SLV');
INSERT INTO `geo_country_reference` VALUES (65, 'Equatorial Guinea', 'GQ', 'GNQ');
INSERT INTO `geo_country_reference` VALUES (66, 'Eritrea', 'ER', 'ERI');
INSERT INTO `geo_country_reference` VALUES (67, 'Estonia', 'EE', 'EST');
INSERT INTO `geo_country_reference` VALUES (68, 'Ethiopia', 'ET', 'ETH');
INSERT INTO `geo_country_reference` VALUES (69, 'Falkland Islands (Malvinas)', 'FK', 'FLK');
INSERT INTO `geo_country_reference` VALUES (70, 'Faroe Islands', 'FO', 'FRO');
INSERT INTO `geo_country_reference` VALUES (71, 'Fiji', 'FJ', 'FJI');
INSERT INTO `geo_country_reference` VALUES (72, 'Finland', 'FI', 'FIN');
INSERT INTO `geo_country_reference` VALUES (73, 'France', 'FR', 'FRA');
INSERT INTO `geo_country_reference` VALUES (74, 'France, MEtropolitan', 'FX', 'FXX');
INSERT INTO `geo_country_reference` VALUES (75, 'French Guiana', 'GF', 'GUF');
INSERT INTO `geo_country_reference` VALUES (76, 'French Polynesia', 'PF', 'PYF');
INSERT INTO `geo_country_reference` VALUES (77, 'French Southern Territories', 'TF', 'ATF');
INSERT INTO `geo_country_reference` VALUES (78, 'Gabon', 'GA', 'GAB');
INSERT INTO `geo_country_reference` VALUES (79, 'Gambia', 'GM', 'GMB');
INSERT INTO `geo_country_reference` VALUES (80, 'Georgia', 'GE', 'GEO');
INSERT INTO `geo_country_reference` VALUES (81, 'Germany', 'DE', 'DEU');
INSERT INTO `geo_country_reference` VALUES (82, 'Ghana', 'GH', 'GHA');
INSERT INTO `geo_country_reference` VALUES (83, 'Gibraltar', 'GI', 'GIB');
INSERT INTO `geo_country_reference` VALUES (84, 'Greece', 'GR', 'GRC');
INSERT INTO `geo_country_reference` VALUES (85, 'Greenland', 'GL', 'GRL');
INSERT INTO `geo_country_reference` VALUES (86, 'Grenada', 'GD', 'GRD');
INSERT INTO `geo_country_reference` VALUES (87, 'Guadeloupe', 'GP', 'GLP');
INSERT INTO `geo_country_reference` VALUES (88, 'Guam', 'GU', 'GUM');
INSERT INTO `geo_country_reference` VALUES (89, 'Guatemala', 'GT', 'GTM');
INSERT INTO `geo_country_reference` VALUES (90, 'Guinea', 'GN', 'GIN');
INSERT INTO `geo_country_reference` VALUES (91, 'Guinea-bissau', 'GW', 'GNB');
INSERT INTO `geo_country_reference` VALUES (92, 'Guyana', 'GY', 'GUY');
INSERT INTO `geo_country_reference` VALUES (93, 'Haiti', 'HT', 'HTI');
INSERT INTO `geo_country_reference` VALUES (94, 'Heard and Mc Donald Islands', 'HM', 'HMD');
INSERT INTO `geo_country_reference` VALUES (95, 'Honduras', 'HN', 'HND');
INSERT INTO `geo_country_reference` VALUES (96, 'Hong Kong', 'HK', 'HKG');
INSERT INTO `geo_country_reference` VALUES (97, 'Hungary', 'HU', 'HUN');
INSERT INTO `geo_country_reference` VALUES (98, 'Iceland', 'IS', 'ISL');
INSERT INTO `geo_country_reference` VALUES (99, 'India', 'IN', 'IND');
INSERT INTO `geo_country_reference` VALUES (100, 'Indonesia', 'ID', 'IDN');
INSERT INTO `geo_country_reference` VALUES (101, 'Iran (Islamic Republic of)', 'IR', 'IRN');
INSERT INTO `geo_country_reference` VALUES (102, 'Iraq', 'IQ', 'IRQ');
INSERT INTO `geo_country_reference` VALUES (103, 'Ireland', 'IE', 'IRL');
INSERT INTO `geo_country_reference` VALUES (104, 'Israel', 'IL', 'ISR');
INSERT INTO `geo_country_reference` VALUES (105, 'Italy', 'IT', 'ITA');
INSERT INTO `geo_country_reference` VALUES (106, 'Jamaica', 'JM', 'JAM');
INSERT INTO `geo_country_reference` VALUES (107, 'Japan', 'JP', 'JPN');
INSERT INTO `geo_country_reference` VALUES (108, 'Jordan', 'JO', 'JOR');
INSERT INTO `geo_country_reference` VALUES (109, 'Kazakhstan', 'KZ', 'KAZ');
INSERT INTO `geo_country_reference` VALUES (110, 'Kenya', 'KE', 'KEN');
INSERT INTO `geo_country_reference` VALUES (111, 'Kiribati', 'KI', 'KIR');
INSERT INTO `geo_country_reference` VALUES (112, 'Korea, Democratic Peoples Republic of', 'KP', 'PRK');
INSERT INTO `geo_country_reference` VALUES (113, 'Korea, Republic of', 'KR', 'KOR');
INSERT INTO `geo_country_reference` VALUES (114, 'Kuwait', 'KW', 'KWT');
INSERT INTO `geo_country_reference` VALUES (115, 'Kyrgyzstan', 'KG', 'KGZ');
INSERT INTO `geo_country_reference` VALUES (116, 'Lao Peoples Democratic Republic', 'LA', 'LAO');
INSERT INTO `geo_country_reference` VALUES (117, 'Latvia', 'LV', 'LVA');
INSERT INTO `geo_country_reference` VALUES (118, 'Lebanon', 'LB', 'LBN');
INSERT INTO `geo_country_reference` VALUES (119, 'Lesotho', 'LS', 'LSO');
INSERT INTO `geo_country_reference` VALUES (120, 'Liberia', 'LR', 'LBR');
INSERT INTO `geo_country_reference` VALUES (121, 'Libyan Arab Jamahiriya', 'LY', 'LBY');
INSERT INTO `geo_country_reference` VALUES (122, 'Liechtenstein', 'LI', 'LIE');
INSERT INTO `geo_country_reference` VALUES (123, 'Lithuania', 'LT', 'LTU');
INSERT INTO `geo_country_reference` VALUES (124, 'Luxembourg', 'LU', 'LUX');
INSERT INTO `geo_country_reference` VALUES (125, 'Macau', 'MO', 'MAC');
INSERT INTO `geo_country_reference` VALUES (126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD');
INSERT INTO `geo_country_reference` VALUES (127, 'Madagascar', 'MG', 'MDG');
INSERT INTO `geo_country_reference` VALUES (128, 'Malawi', 'MW', 'MWI');
INSERT INTO `geo_country_reference` VALUES (129, 'Malaysia', 'MY', 'MYS');
INSERT INTO `geo_country_reference` VALUES (130, 'Maldives', 'MV', 'MDV');
INSERT INTO `geo_country_reference` VALUES (131, 'Mali', 'ML', 'MLI');
INSERT INTO `geo_country_reference` VALUES (132, 'Malta', 'MT', 'MLT');
INSERT INTO `geo_country_reference` VALUES (133, 'Marshall Islands', 'MH', 'MHL');
INSERT INTO `geo_country_reference` VALUES (134, 'Martinique', 'MQ', 'MTQ');
INSERT INTO `geo_country_reference` VALUES (135, 'Mauritania', 'MR', 'MRT');
INSERT INTO `geo_country_reference` VALUES (136, 'Mauritius', 'MU', 'MUS');
INSERT INTO `geo_country_reference` VALUES (137, 'Mayotte', 'YT', 'MYT');
INSERT INTO `geo_country_reference` VALUES (138, 'Mexico', 'MX', 'MEX');
INSERT INTO `geo_country_reference` VALUES (139, 'Micronesia, Federated States of', 'FM', 'FSM');
INSERT INTO `geo_country_reference` VALUES (140, 'Moldova, Republic of', 'MD', 'MDA');
INSERT INTO `geo_country_reference` VALUES (141, 'Monaco', 'MC', 'MCO');
INSERT INTO `geo_country_reference` VALUES (142, 'Mongolia', 'MN', 'MNG');
INSERT INTO `geo_country_reference` VALUES (143, 'Montserrat', 'MS', 'MSR');
INSERT INTO `geo_country_reference` VALUES (144, 'Morocco', 'MA', 'MAR');
INSERT INTO `geo_country_reference` VALUES (145, 'Mozambique', 'MZ', 'MOZ');
INSERT INTO `geo_country_reference` VALUES (146, 'Myanmar', 'MM', 'MMR');
INSERT INTO `geo_country_reference` VALUES (147, 'Namibia', 'NA', 'NAM');
INSERT INTO `geo_country_reference` VALUES (148, 'Nauru', 'NR', 'NRU');
INSERT INTO `geo_country_reference` VALUES (149, 'Nepal', 'NP', 'NPL');
INSERT INTO `geo_country_reference` VALUES (150, 'Netherlands', 'NL', 'NLD');
INSERT INTO `geo_country_reference` VALUES (151, 'Netherlands Antilles', 'AN', 'ANT');
INSERT INTO `geo_country_reference` VALUES (152, 'New Caledonia', 'NC', 'NCL');
INSERT INTO `geo_country_reference` VALUES (153, 'New Zealand', 'NZ', 'NZL');
INSERT INTO `geo_country_reference` VALUES (154, 'Nicaragua', 'NI', 'NIC');
INSERT INTO `geo_country_reference` VALUES (155, 'Niger', 'NE', 'NER');
INSERT INTO `geo_country_reference` VALUES (156, 'Nigeria', 'NG', 'NGA');
INSERT INTO `geo_country_reference` VALUES (157, 'Niue', 'NU', 'NIU');
INSERT INTO `geo_country_reference` VALUES (158, 'Norfolk Island', 'NF', 'NFK');
INSERT INTO `geo_country_reference` VALUES (159, 'Northern Mariana Islands', 'MP', 'MNP');
INSERT INTO `geo_country_reference` VALUES (160, 'Norway', 'NO', 'NOR');
INSERT INTO `geo_country_reference` VALUES (161, 'Oman', 'OM', 'OMN');
INSERT INTO `geo_country_reference` VALUES (162, 'Pakistan', 'PK', 'PAK');
INSERT INTO `geo_country_reference` VALUES (163, 'Palau', 'PW', 'PLW');
INSERT INTO `geo_country_reference` VALUES (164, 'Panama', 'PA', 'PAN');
INSERT INTO `geo_country_reference` VALUES (165, 'Papua New Guinea', 'PG', 'PNG');
INSERT INTO `geo_country_reference` VALUES (166, 'Paraguay', 'PY', 'PRY');
INSERT INTO `geo_country_reference` VALUES (167, 'Peru', 'PE', 'PER');
INSERT INTO `geo_country_reference` VALUES (168, 'Philippines', 'PH', 'PHL');
INSERT INTO `geo_country_reference` VALUES (169, 'Pitcairn', 'PN', 'PCN');
INSERT INTO `geo_country_reference` VALUES (170, 'Poland', 'PL', 'POL');
INSERT INTO `geo_country_reference` VALUES (171, 'Portugal', 'PT', 'PRT');
INSERT INTO `geo_country_reference` VALUES (172, 'Puerto Rico', 'PR', 'PRI');
INSERT INTO `geo_country_reference` VALUES (173, 'Qatar', 'QA', 'QAT');
INSERT INTO `geo_country_reference` VALUES (174, 'Reunion', 'RE', 'REU');
INSERT INTO `geo_country_reference` VALUES (175, 'Romania', 'RO', 'ROM');
INSERT INTO `geo_country_reference` VALUES (176, 'Russian Federation', 'RU', 'RUS');
INSERT INTO `geo_country_reference` VALUES (177, 'Rwanda', 'RW', 'RWA');
INSERT INTO `geo_country_reference` VALUES (178, 'Saint Kitts and Nevis', 'KN', 'KNA');
INSERT INTO `geo_country_reference` VALUES (179, 'Saint Lucia', 'LC', 'LCA');
INSERT INTO `geo_country_reference` VALUES (180, 'Saint Vincent and the Grenadines', 'VC', 'VCT');
INSERT INTO `geo_country_reference` VALUES (181, 'Samoa', 'WS', 'WSM');
INSERT INTO `geo_country_reference` VALUES (182, 'San Marino', 'SM', 'SMR');
INSERT INTO `geo_country_reference` VALUES (183, 'Sao Tome and Principe', 'ST', 'STP');
INSERT INTO `geo_country_reference` VALUES (184, 'Saudi Arabia', 'SA', 'SAU');
INSERT INTO `geo_country_reference` VALUES (185, 'Senegal', 'SN', 'SEN');
INSERT INTO `geo_country_reference` VALUES (186, 'Seychelles', 'SC', 'SYC');
INSERT INTO `geo_country_reference` VALUES (187, 'Sierra Leone', 'SL', 'SLE');
INSERT INTO `geo_country_reference` VALUES (188, 'Singapore', 'SG', 'SGP');
INSERT INTO `geo_country_reference` VALUES (189, 'Slovakia (Slovak Republic)', 'SK', 'SVK');
INSERT INTO `geo_country_reference` VALUES (190, 'Slovenia', 'SI', 'SVN');
INSERT INTO `geo_country_reference` VALUES (191, 'Solomon Islands', 'SB', 'SLB');
INSERT INTO `geo_country_reference` VALUES (192, 'Somalia', 'SO', 'SOM');
INSERT INTO `geo_country_reference` VALUES (193, 'south Africa', 'ZA', 'ZAF');
INSERT INTO `geo_country_reference` VALUES (194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS');
INSERT INTO `geo_country_reference` VALUES (195, 'Spain', 'ES', 'ESP');
INSERT INTO `geo_country_reference` VALUES (196, 'Sri Lanka', 'LK', 'LKA');
INSERT INTO `geo_country_reference` VALUES (197, 'St. Helena', 'SH', 'SHN');
INSERT INTO `geo_country_reference` VALUES (198, 'St. Pierre and Miquelon', 'PM', 'SPM');
INSERT INTO `geo_country_reference` VALUES (199, 'Sudan', 'SD', 'SDN');
INSERT INTO `geo_country_reference` VALUES (200, 'Suriname', 'SR', 'SUR');
INSERT INTO `geo_country_reference` VALUES (201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM');
INSERT INTO `geo_country_reference` VALUES (202, 'Swaziland', 'SZ', 'SWZ');
INSERT INTO `geo_country_reference` VALUES (203, 'Sweden', 'SE', 'SWE');
INSERT INTO `geo_country_reference` VALUES (204, 'Switzerland', 'CH', 'CHE');
INSERT INTO `geo_country_reference` VALUES (205, 'Syrian Arab Republic', 'SY', 'SYR');
INSERT INTO `geo_country_reference` VALUES (206, 'Taiwan, Province of China', 'TW', 'TWN');
INSERT INTO `geo_country_reference` VALUES (207, 'Tajikistan', 'TJ', 'TJK');
INSERT INTO `geo_country_reference` VALUES (208, 'Tanzania, United Republic of', 'TZ', 'TZA');
INSERT INTO `geo_country_reference` VALUES (209, 'Thailand', 'TH', 'THA');
INSERT INTO `geo_country_reference` VALUES (210, 'Togo', 'TG', 'TGO');
INSERT INTO `geo_country_reference` VALUES (211, 'Tokelau', 'TK', 'TKL');
INSERT INTO `geo_country_reference` VALUES (212, 'Tonga', 'TO', 'TON');
INSERT INTO `geo_country_reference` VALUES (213, 'Trinidad and Tobago', 'TT', 'TTO');
INSERT INTO `geo_country_reference` VALUES (214, 'Tunisia', 'TN', 'TUN');
INSERT INTO `geo_country_reference` VALUES (215, 'Turkey', 'TR', 'TUR');
INSERT INTO `geo_country_reference` VALUES (216, 'Turkmenistan', 'TM', 'TKM');
INSERT INTO `geo_country_reference` VALUES (217, 'Turks and Caicos Islands', 'TC', 'TCA');
INSERT INTO `geo_country_reference` VALUES (218, 'Tuvalu', 'TV', 'TUV');
INSERT INTO `geo_country_reference` VALUES (219, 'Uganda', 'UG', 'UGA');
INSERT INTO `geo_country_reference` VALUES (220, 'Ukraine', 'UA', 'UKR');
INSERT INTO `geo_country_reference` VALUES (221, 'United Arab Emirates', 'AE', 'ARE');
INSERT INTO `geo_country_reference` VALUES (222, 'United Kingdom', 'GB', 'GBR');
INSERT INTO `geo_country_reference` VALUES (223, 'United States', 'US', 'USA');
INSERT INTO `geo_country_reference` VALUES (224, 'United States Minor Outlying Islands', 'UM', 'UMI');
INSERT INTO `geo_country_reference` VALUES (225, 'Uruguay', 'UY', 'URY');
INSERT INTO `geo_country_reference` VALUES (226, 'Uzbekistan', 'UZ', 'UZB');
INSERT INTO `geo_country_reference` VALUES (227, 'Vanuatu', 'VU', 'VUT');
INSERT INTO `geo_country_reference` VALUES (228, 'Vatican City State (Holy See)', 'VA', 'VAT');
INSERT INTO `geo_country_reference` VALUES (229, 'Venezuela', 'VE', 'VEN');
INSERT INTO `geo_country_reference` VALUES (230, 'Viet Nam', 'VN', 'VNM');
INSERT INTO `geo_country_reference` VALUES (231, 'Virgin Islands (British)', 'VG', 'VGB');
INSERT INTO `geo_country_reference` VALUES (232, 'Virgin Islands (U.S.)', 'VI', 'VIR');
INSERT INTO `geo_country_reference` VALUES (233, 'Wallis and Futuna Islands', 'WF', 'WLF');
INSERT INTO `geo_country_reference` VALUES (234, 'Western Sahara', 'EH', 'ESH');
INSERT INTO `geo_country_reference` VALUES (235, 'Yemen', 'YE', 'YEM');
INSERT INTO `geo_country_reference` VALUES (236, 'Yugoslavia', 'YU', 'YUG');
INSERT INTO `geo_country_reference` VALUES (237, 'Zaire', 'ZR', 'ZAR');
INSERT INTO `geo_country_reference` VALUES (238, 'Zambia', 'ZM', 'ZMB');
INSERT INTO `geo_country_reference` VALUES (239, 'Zimbabwe', 'ZW', 'ZWE');

-- --------------------------------------------------------

-- 
-- Table structure for table `geo_zone_reference`
-- 

DROP TABLE IF EXISTS `geo_zone_reference`;
CREATE TABLE `geo_zone_reference` (
  `zone_id` int(5) NOT NULL auto_increment,
  `zone_country_id` int(5) NOT NULL default '0',
  `zone_code` varchar(5) character set utf8 collate utf8_unicode_ci default NULL,
  `zone_name` varchar(32) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=83 ;

-- 
-- Dumping data for table `geo_zone_reference`
-- 

INSERT INTO `geo_zone_reference` VALUES (1, 223, 'AL', 'Alabama');
INSERT INTO `geo_zone_reference` VALUES (2, 223, 'AK', 'Alaska');
INSERT INTO `geo_zone_reference` VALUES (3, 223, 'AS', 'American Samoa');
INSERT INTO `geo_zone_reference` VALUES (4, 223, 'AZ', 'Arizona');
INSERT INTO `geo_zone_reference` VALUES (5, 223, 'AR', 'Arkansas');
INSERT INTO `geo_zone_reference` VALUES (6, 223, 'AF', 'Armed Forces Africa');
INSERT INTO `geo_zone_reference` VALUES (7, 223, 'AA', 'Armed Forces Americas');
INSERT INTO `geo_zone_reference` VALUES (8, 223, 'AC', 'Armed Forces Canada');
INSERT INTO `geo_zone_reference` VALUES (9, 223, 'AE', 'Armed Forces Europe');
INSERT INTO `geo_zone_reference` VALUES (10, 223, 'AM', 'Armed Forces Middle East');
INSERT INTO `geo_zone_reference` VALUES (11, 223, 'AP', 'Armed Forces Pacific');
INSERT INTO `geo_zone_reference` VALUES (12, 223, 'CA', 'California');
INSERT INTO `geo_zone_reference` VALUES (13, 223, 'CO', 'Colorado');
INSERT INTO `geo_zone_reference` VALUES (14, 223, 'CT', 'Connecticut');
INSERT INTO `geo_zone_reference` VALUES (15, 223, 'DE', 'Delaware');
INSERT INTO `geo_zone_reference` VALUES (16, 223, 'DC', 'District of Columbia');
INSERT INTO `geo_zone_reference` VALUES (17, 223, 'FM', 'Federated States Of Micronesia');
INSERT INTO `geo_zone_reference` VALUES (18, 223, 'FL', 'Florida');
INSERT INTO `geo_zone_reference` VALUES (19, 223, 'GA', 'Georgia');
INSERT INTO `geo_zone_reference` VALUES (20, 223, 'GU', 'Guam');
INSERT INTO `geo_zone_reference` VALUES (21, 223, 'HI', 'Hawaii');
INSERT INTO `geo_zone_reference` VALUES (22, 223, 'ID', 'Idaho');
INSERT INTO `geo_zone_reference` VALUES (23, 223, 'IL', 'Illinois');
INSERT INTO `geo_zone_reference` VALUES (24, 223, 'IN', 'Indiana');
INSERT INTO `geo_zone_reference` VALUES (25, 223, 'IA', 'Iowa');
INSERT INTO `geo_zone_reference` VALUES (26, 223, 'KS', 'Kansas');
INSERT INTO `geo_zone_reference` VALUES (27, 223, 'KY', 'Kentucky');
INSERT INTO `geo_zone_reference` VALUES (28, 223, 'LA', 'Louisiana');
INSERT INTO `geo_zone_reference` VALUES (29, 223, 'ME', 'Maine');
INSERT INTO `geo_zone_reference` VALUES (30, 223, 'MH', 'Marshall Islands');
INSERT INTO `geo_zone_reference` VALUES (31, 223, 'MD', 'Maryland');
INSERT INTO `geo_zone_reference` VALUES (32, 223, 'MA', 'Massachusetts');
INSERT INTO `geo_zone_reference` VALUES (33, 223, 'MI', 'Michigan');
INSERT INTO `geo_zone_reference` VALUES (34, 223, 'MN', 'Minnesota');
INSERT INTO `geo_zone_reference` VALUES (35, 223, 'MS', 'Mississippi');
INSERT INTO `geo_zone_reference` VALUES (36, 223, 'MO', 'Missouri');
INSERT INTO `geo_zone_reference` VALUES (37, 223, 'MT', 'Montana');
INSERT INTO `geo_zone_reference` VALUES (38, 223, 'NE', 'Nebraska');
INSERT INTO `geo_zone_reference` VALUES (39, 223, 'NV', 'Nevada');
INSERT INTO `geo_zone_reference` VALUES (40, 223, 'NH', 'New Hampshire');
INSERT INTO `geo_zone_reference` VALUES (41, 223, 'NJ', 'New Jersey');
INSERT INTO `geo_zone_reference` VALUES (42, 223, 'NM', 'New Mexico');
INSERT INTO `geo_zone_reference` VALUES (43, 223, 'NY', 'New York');
INSERT INTO `geo_zone_reference` VALUES (44, 223, 'NC', 'North Carolina');
INSERT INTO `geo_zone_reference` VALUES (45, 223, 'ND', 'North Dakota');
INSERT INTO `geo_zone_reference` VALUES (46, 223, 'MP', 'Northern Mariana Islands');
INSERT INTO `geo_zone_reference` VALUES (47, 223, 'OH', 'Ohio');
INSERT INTO `geo_zone_reference` VALUES (48, 223, 'OK', 'Oklahoma');
INSERT INTO `geo_zone_reference` VALUES (49, 223, 'OR', 'Oregon');
INSERT INTO `geo_zone_reference` VALUES (50, 223, 'PW', 'Palau');
INSERT INTO `geo_zone_reference` VALUES (51, 223, 'PA', 'Pennsylvania');
INSERT INTO `geo_zone_reference` VALUES (52, 223, 'PR', 'Puerto Rico');
INSERT INTO `geo_zone_reference` VALUES (53, 223, 'RI', 'Rhode Island');
INSERT INTO `geo_zone_reference` VALUES (54, 223, 'SC', 'South Carolina');
INSERT INTO `geo_zone_reference` VALUES (55, 223, 'SD', 'South Dakota');
INSERT INTO `geo_zone_reference` VALUES (56, 223, 'TN', 'Tenessee');
INSERT INTO `geo_zone_reference` VALUES (57, 223, 'TX', 'Texas');
INSERT INTO `geo_zone_reference` VALUES (58, 223, 'UT', 'Utah');
INSERT INTO `geo_zone_reference` VALUES (59, 223, 'VT', 'Vermont');
INSERT INTO `geo_zone_reference` VALUES (60, 223, 'VI', 'Virgin Islands');
INSERT INTO `geo_zone_reference` VALUES (61, 223, 'VA', 'Virginia');
INSERT INTO `geo_zone_reference` VALUES (62, 223, 'WA', 'Washington');
INSERT INTO `geo_zone_reference` VALUES (63, 223, 'WV', 'West Virginia');
INSERT INTO `geo_zone_reference` VALUES (64, 223, 'WI', 'Wisconsin');
INSERT INTO `geo_zone_reference` VALUES (65, 223, 'WY', 'Wyoming');
INSERT INTO `geo_zone_reference` VALUES (66, 38, 'AB', 'Alberta');
INSERT INTO `geo_zone_reference` VALUES (67, 38, 'BC', 'British Columbia');
INSERT INTO `geo_zone_reference` VALUES (68, 38, 'MB', 'Manitoba');
INSERT INTO `geo_zone_reference` VALUES (69, 38, 'NF', 'Newfoundland');
INSERT INTO `geo_zone_reference` VALUES (70, 38, 'NB', 'New Brunswick');
INSERT INTO `geo_zone_reference` VALUES (71, 38, 'NS', 'Nova Scotia');
INSERT INTO `geo_zone_reference` VALUES (72, 38, 'NT', 'Northwest Territories');
INSERT INTO `geo_zone_reference` VALUES (73, 38, 'NU', 'Nunavut');
INSERT INTO `geo_zone_reference` VALUES (74, 38, 'ON', 'Ontario');
INSERT INTO `geo_zone_reference` VALUES (75, 38, 'PE', 'Prince Edward Island');
INSERT INTO `geo_zone_reference` VALUES (76, 38, 'QC', 'Quebec');
INSERT INTO `geo_zone_reference` VALUES (77, 38, 'SK', 'Saskatchewan');
INSERT INTO `geo_zone_reference` VALUES (78, 38, 'YT', 'Yukon Territory');
INSERT INTO `geo_zone_reference` VALUES (79, 61, 'QLD', 'Queensland');
INSERT INTO `geo_zone_reference` VALUES (80, 61, 'SA', 'South Australia');
INSERT INTO `geo_zone_reference` VALUES (81, 61, 'ACT', 'Australian Capital Territory');
INSERT INTO `geo_zone_reference` VALUES (82, 61, 'VIC', 'Victoria');

-- --------------------------------------------------------

-- 
-- Table structure for table `groups`
-- 

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` longtext character set utf8 collate utf8_unicode_ci,
  `user` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `history_data`
-- 

DROP TABLE IF EXISTS `history_data`;
CREATE TABLE `history_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `coffee` longtext character set utf8 collate utf8_unicode_ci,
  `tobacco` longtext character set utf8 collate utf8_unicode_ci,
  `alcohol` longtext character set utf8 collate utf8_unicode_ci,
  `sleep_patterns` longtext character set utf8 collate utf8_unicode_ci,
  `exercise_patterns` longtext character set utf8 collate utf8_unicode_ci,
  `seatbelt_use` longtext character set utf8 collate utf8_unicode_ci,
  `counseling` longtext character set utf8 collate utf8_unicode_ci,
  `hazardous_activities` longtext character set utf8 collate utf8_unicode_ci,
  `last_breast_exam` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_mammogram` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_gynocological_exam` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_rectal_exam` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_prostate_exam` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_physical_exam` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_sigmoidoscopy_colonoscopy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_ecg` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_cardiac_echo` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_retinal` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_fluvax` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_pneuvax` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_ldl` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_hemoglobin` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_psa` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `last_exam_results` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `history_mother` longtext character set utf8 collate utf8_unicode_ci,
  `history_father` longtext character set utf8 collate utf8_unicode_ci,
  `history_siblings` longtext character set utf8 collate utf8_unicode_ci,
  `history_offspring` longtext character set utf8 collate utf8_unicode_ci,
  `history_spouse` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_cancer` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_tuberculosis` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_diabetes` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_high_blood_pressure` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_heart_problems` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_stroke` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_epilepsy` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_mental_illness` longtext character set utf8 collate utf8_unicode_ci,
  `relatives_suicide` longtext character set utf8 collate utf8_unicode_ci,
  `cataract_surgery` datetime default NULL,
  `tonsillectomy` datetime default NULL,
  `cholecystestomy` datetime default NULL,
  `heart_surgery` datetime default NULL,
  `hysterectomy` datetime default NULL,
  `hernia_repair` datetime default NULL,
  `hip_replacement` datetime default NULL,
  `knee_replacement` datetime default NULL,
  `appendectomy` datetime default NULL,
  `date` datetime default NULL,
  `pid` bigint(20) NOT NULL default '0',
  `name_1` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `value_1` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `name_2` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `value_2` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `additional_history` text character set utf8 collate utf8_unicode_ci,
  `exams`      text         character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext11` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext12` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext13` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext14` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext15` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext16` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext17` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext18` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext19` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext20` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext21` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext22` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext23` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext24` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext25` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext26` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext27` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext28` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext29` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext30` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userdate11` date DEFAULT NULL,
  `userdate12` date DEFAULT NULL,
  `userdate13` date DEFAULT NULL,
  `userdate14` date DEFAULT NULL,
  `userdate15` date DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `history_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `immunization`
-- 

DROP TABLE IF EXISTS `immunization`;
CREATE TABLE `immunization` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `immunization_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=36 ;

-- 
-- Dumping data for table `immunization`
-- 

INSERT INTO `immunization` VALUES (1, 'DTaP 1');
INSERT INTO `immunization` VALUES (2, 'DTaP 2');
INSERT INTO `immunization` VALUES (3, 'DTaP 3');
INSERT INTO `immunization` VALUES (4, 'DTaP 4');
INSERT INTO `immunization` VALUES (5, 'DTaP 5');
INSERT INTO `immunization` VALUES (6, 'DT 1');
INSERT INTO `immunization` VALUES (7, 'DT 2');
INSERT INTO `immunization` VALUES (8, 'DT 3');
INSERT INTO `immunization` VALUES (9, 'DT 4');
INSERT INTO `immunization` VALUES (10, 'DT 5');
INSERT INTO `immunization` VALUES (11, 'IPV 1');
INSERT INTO `immunization` VALUES (12, 'IPV 2');
INSERT INTO `immunization` VALUES (13, 'IPV 3');
INSERT INTO `immunization` VALUES (14, 'IPV 4');
INSERT INTO `immunization` VALUES (15, 'Hib 1');
INSERT INTO `immunization` VALUES (16, 'Hib 2');
INSERT INTO `immunization` VALUES (17, 'Hib 3');
INSERT INTO `immunization` VALUES (18, 'Hib 4');
INSERT INTO `immunization` VALUES (19, 'Pneumococcal Conjugate 1');
INSERT INTO `immunization` VALUES (20, 'Pneumococcal Conjugate 2');
INSERT INTO `immunization` VALUES (21, 'Pneumococcal Conjugate 3');
INSERT INTO `immunization` VALUES (22, 'Pneumococcal Conjugate 4');
INSERT INTO `immunization` VALUES (23, 'MMR 1');
INSERT INTO `immunization` VALUES (24, 'MMR 2');
INSERT INTO `immunization` VALUES (25, 'Varicella 1');
INSERT INTO `immunization` VALUES (26, 'Varicella 2');
INSERT INTO `immunization` VALUES (27, 'Hepatitis B 1');
INSERT INTO `immunization` VALUES (28, 'Hepatitis B 2');
INSERT INTO `immunization` VALUES (29, 'Hepatitis B 3');
INSERT INTO `immunization` VALUES (30, 'Influenza 1');
INSERT INTO `immunization` VALUES (31, 'Influenza 2');
INSERT INTO `immunization` VALUES (32, 'Td');
INSERT INTO `immunization` VALUES (33, 'Hepatitis A 1');
INSERT INTO `immunization` VALUES (34, 'Hepatitis A 2');
INSERT INTO `immunization` VALUES (35, 'Other');

-- --------------------------------------------------------

-- 
-- Table structure for table `immunizations`
-- 

DROP TABLE IF EXISTS `immunizations`;
CREATE TABLE `immunizations` (
  `id` bigint(20) NOT NULL auto_increment,
  `patient_id` int(11) default NULL,
  `administered_date` date default NULL,
  `immunization_id` int(11) default NULL,
  `manufacturer` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  `lot_number` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `administered_by_id` bigint(20) default NULL,
  `education_date` date default NULL,
  `note` text character set utf8 collate utf8_unicode_ci,
  `create_date` datetime default NULL,
  `update_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created_by` bigint(20) default NULL,
  `updated_by` bigint(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `immunizations`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `insurance_companies`
-- 

DROP TABLE IF EXISTS `insurance_companies`;
CREATE TABLE `insurance_companies` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `attn` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `cms_id` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  `freeb_type` tinyint(2) default NULL,
  `x12_receiver_id` varchar(25) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_default_partner_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `insurance_companies`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `insurance_data`
-- 

DROP TABLE IF EXISTS `insurance_data`;
CREATE TABLE `insurance_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `type` enum('primary','secondary','tertiary') character set latin1 default NULL,
  `provider` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `plan_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `policy_number` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `group_number` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_lname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_mname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_fname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_relationship` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_ss` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_DOB` date default NULL,
  `subscriber_street` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_postal_code` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_state` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_country` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_phone` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer_street` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer_postal_code` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer_state` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer_country` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `subscriber_employer_city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `copay` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `date` date NOT NULL default '0000-00-00',
  `pid` bigint(20) NOT NULL default '0',
  `subscriber_sex` varchar(25) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pid_type_date` (`pid`,`type`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `insurance_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `insurance_numbers`
-- 

DROP TABLE IF EXISTS `insurance_numbers`;
CREATE TABLE `insurance_numbers` (
  `id` int(11) NOT NULL default '0',
  `provider_id` int(11) NOT NULL default '0',
  `insurance_company_id` int(11) default NULL,
  `provider_number` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `rendering_provider_number` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `group_number` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `provider_number_type` varchar(4) character set utf8 collate utf8_unicode_ci default NULL,
  `rendering_provider_number_type` varchar(4) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `insurance_numbers`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `integration_mapping`
-- 

DROP TABLE IF EXISTS `integration_mapping`;
CREATE TABLE `integration_mapping` (
  `id` int(11) NOT NULL default '0',
  `foreign_id` int(11) NOT NULL default '0',
  `foreign_table` varchar(125) character set utf8 collate utf8_unicode_ci default NULL,
  `local_id` int(11) NOT NULL default '0',
  `local_table` varchar(125) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `foreign_id` (`foreign_id`,`foreign_table`,`local_id`,`local_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `integration_mapping`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `issue_encounter`
-- 

DROP TABLE IF EXISTS `issue_encounter`;
CREATE TABLE `issue_encounter` (
  `pid` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `encounter` int(11) NOT NULL,
  `resolved` tinyint(1) NOT NULL,
  PRIMARY KEY  (`pid`,`list_id`,`encounter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `issue_encounter`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `lang_constants`
-- 

DROP TABLE IF EXISTS `lang_constants`;
CREATE TABLE `lang_constants` (
  `cons_id` int(11) NOT NULL auto_increment,
  `constant_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `cons_name` (`constant_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2640 ;

-- 
-- Dumping data for table `lang_constants`
-- 

INSERT INTO `lang_constants` VALUES (1, 'Upload this file:');
INSERT INTO `lang_constants` VALUES (2, 'Start Date');
INSERT INTO `lang_constants` VALUES (3, 'Template:');
INSERT INTO `lang_constants` VALUES (4, 'Edit Structure');
INSERT INTO `lang_constants` VALUES (5, 'Completed');
INSERT INTO `lang_constants` VALUES (6, 'Full Play');
INSERT INTO `lang_constants` VALUES (7, 'Full Training');
INSERT INTO `lang_constants` VALUES (8, 'Restricted Training');
INSERT INTO `lang_constants` VALUES (9, 'Injured Out');
INSERT INTO `lang_constants` VALUES (10, 'Rehabilitation');
INSERT INTO `lang_constants` VALUES (11, 'Illness');
INSERT INTO `lang_constants` VALUES (12, 'International Duty');
INSERT INTO `lang_constants` VALUES (13, 'Unknown or N/A');
INSERT INTO `lang_constants` VALUES (14, 'First');
INSERT INTO `lang_constants` VALUES (15, 'Late Recurrence (2-12 Mo)');
INSERT INTO `lang_constants` VALUES (16, 'Delayed Recurrence (> 12 Mo)');
INSERT INTO `lang_constants` VALUES (17, 'Second');
INSERT INTO `lang_constants` VALUES (18, 'Third');
INSERT INTO `lang_constants` VALUES (19, 'Chronic/Recurrent');
INSERT INTO `lang_constants` VALUES (20, 'Acute on Chronic');
INSERT INTO `lang_constants` VALUES (21, 'Trauma');
INSERT INTO `lang_constants` VALUES (22, 'Overuse');
INSERT INTO `lang_constants` VALUES (23, 'There is no match for invoice id');
INSERT INTO `lang_constants` VALUES (24, 'but was');
INSERT INTO `lang_constants` VALUES (25, 'Imported from Accounting');
INSERT INTO `lang_constants` VALUES (26, 'Group:');
INSERT INTO `lang_constants` VALUES (27, 'Username:');
INSERT INTO `lang_constants` VALUES (28, 'Password:');
INSERT INTO `lang_constants` VALUES (29, 'Copyright Notice');
INSERT INTO `lang_constants` VALUES (30, 'Blocked');
INSERT INTO `lang_constants` VALUES (31, 'Sent successfully');
INSERT INTO `lang_constants` VALUES (32, 'Failed');
INSERT INTO `lang_constants` VALUES (33, 'Pending');
INSERT INTO `lang_constants` VALUES (34, 'Send in progress');
INSERT INTO `lang_constants` VALUES (35, 'Sleeping');
INSERT INTO `lang_constants` VALUES (36, 'Suspended');
INSERT INTO `lang_constants` VALUES (37, 'Waiting');
INSERT INTO `lang_constants` VALUES (38, 'Received Faxes');
INSERT INTO `lang_constants` VALUES (39, 'Faxes In');
INSERT INTO `lang_constants` VALUES (40, 'Faxes Out');
INSERT INTO `lang_constants` VALUES (41, 'Scanner In');
INSERT INTO `lang_constants` VALUES (42, 'Document');
INSERT INTO `lang_constants` VALUES (43, 'Received');
INSERT INTO `lang_constants` VALUES (44, 'From');
INSERT INTO `lang_constants` VALUES (45, 'Pages');
INSERT INTO `lang_constants` VALUES (46, 'Dispatch');
INSERT INTO `lang_constants` VALUES (47, 'Job ID');
INSERT INTO `lang_constants` VALUES (48, 'To');
INSERT INTO `lang_constants` VALUES (49, 'Dials');
INSERT INTO `lang_constants` VALUES (50, 'TTS');
INSERT INTO `lang_constants` VALUES (51, 'Status');
INSERT INTO `lang_constants` VALUES (52, 'Filename');
INSERT INTO `lang_constants` VALUES (53, 'Scanned');
INSERT INTO `lang_constants` VALUES (54, 'Length');
INSERT INTO `lang_constants` VALUES (55, 'Internal error - patient ID was not provided!');
INSERT INTO `lang_constants` VALUES (56, 'You did not choose any actions.');
INSERT INTO `lang_constants` VALUES (57, 'Dispatch Received Document');
INSERT INTO `lang_constants` VALUES (58, 'Copy Pages to Patient Chart');
INSERT INTO `lang_constants` VALUES (59, 'Patient');
INSERT INTO `lang_constants` VALUES (60, 'Click to select');
INSERT INTO `lang_constants` VALUES (61, 'Category');
INSERT INTO `lang_constants` VALUES (62, 'Create Patient Note');
INSERT INTO `lang_constants` VALUES (63, 'Type');
INSERT INTO `lang_constants` VALUES (64, 'Close');
INSERT INTO `lang_constants` VALUES (65, 'Message');
INSERT INTO `lang_constants` VALUES (66, 'Forward Pages via Fax');
INSERT INTO `lang_constants` VALUES (67, 'Fax');
INSERT INTO `lang_constants` VALUES (68, 'Quality');
INSERT INTO `lang_constants` VALUES (69, 'Delete Document from Queue');
INSERT INTO `lang_constants` VALUES (70, 'Please select the desired pages to copy or forward:');
INSERT INTO `lang_constants` VALUES (71, 'Logged in');
INSERT INTO `lang_constants` VALUES (72, 'New Patient');
INSERT INTO `lang_constants` VALUES (73, 'Title');
INSERT INTO `lang_constants` VALUES (74, 'Mrs.');
INSERT INTO `lang_constants` VALUES (75, 'Ms.');
INSERT INTO `lang_constants` VALUES (76, 'Mr.');
INSERT INTO `lang_constants` VALUES (77, 'Dr.');
INSERT INTO `lang_constants` VALUES (78, 'First Name');
INSERT INTO `lang_constants` VALUES (79, 'Middle Name');
INSERT INTO `lang_constants` VALUES (80, 'Last Name');
INSERT INTO `lang_constants` VALUES (81, 'Referral Source');
INSERT INTO `lang_constants` VALUES (82, 'Patient Number');
INSERT INTO `lang_constants` VALUES (83, 'omit to autoassign');
INSERT INTO `lang_constants` VALUES (84, 'Navigation');
INSERT INTO `lang_constants` VALUES (85, 'ID');
INSERT INTO `lang_constants` VALUES (86, 'SSN');
INSERT INTO `lang_constants` VALUES (87, 'DOB');
INSERT INTO `lang_constants` VALUES (88, 'Find Patient');
INSERT INTO `lang_constants` VALUES (89, 'Back');
INSERT INTO `lang_constants` VALUES (90, 'Name');
INSERT INTO `lang_constants` VALUES (91, 'Find');
INSERT INTO `lang_constants` VALUES (92, 'New');
INSERT INTO `lang_constants` VALUES (93, 'Password');
INSERT INTO `lang_constants` VALUES (94, 'Administration');
INSERT INTO `lang_constants` VALUES (95, 'Reports');
INSERT INTO `lang_constants` VALUES (96, 'Notes');
INSERT INTO `lang_constants` VALUES (97, 'AB');
INSERT INTO `lang_constants` VALUES (98, 'Docs');
INSERT INTO `lang_constants` VALUES (99, 'Billing');
INSERT INTO `lang_constants` VALUES (100, 'Roster');
INSERT INTO `lang_constants` VALUES (101, 'Calendar');
INSERT INTO `lang_constants` VALUES (102, 'Home');
INSERT INTO `lang_constants` VALUES (103, 'Logout');
INSERT INTO `lang_constants` VALUES (104, 'Just Mine');
INSERT INTO `lang_constants` VALUES (105, 'See All');
INSERT INTO `lang_constants` VALUES (106, 'Patient Notes');
INSERT INTO `lang_constants` VALUES (107, 'and');
INSERT INTO `lang_constants` VALUES (108, 'Authorizations');
INSERT INTO `lang_constants` VALUES (109, 'Note Type');
INSERT INTO `lang_constants` VALUES (110, 'Timestamp and Text');
INSERT INTO `lang_constants` VALUES (111, 'Some authorizations were not displayed. Click here to view all');
INSERT INTO `lang_constants` VALUES (112, 'Authorize');
INSERT INTO `lang_constants` VALUES (113, 'Provider');
INSERT INTO `lang_constants` VALUES (114, 'Transactions');
INSERT INTO `lang_constants` VALUES (115, 'Encounter Forms');
INSERT INTO `lang_constants` VALUES (116, 'Patient Finder');
INSERT INTO `lang_constants` VALUES (117, 'Search by:');
INSERT INTO `lang_constants` VALUES (118, 'for:');
INSERT INTO `lang_constants` VALUES (119, 'If name, any part of lastname or lastname,firstname');
INSERT INTO `lang_constants` VALUES (120, 'SS');
INSERT INTO `lang_constants` VALUES (121, 'Find Available Appointments');
INSERT INTO `lang_constants` VALUES (122, 'The destination form was closed; I cannot act on your selection.');
INSERT INTO `lang_constants` VALUES (123, 'Start date:');
INSERT INTO `lang_constants` VALUES (124, 'for');
INSERT INTO `lang_constants` VALUES (125, 'days');
INSERT INTO `lang_constants` VALUES (126, 'Day');
INSERT INTO `lang_constants` VALUES (127, 'Date');
INSERT INTO `lang_constants` VALUES (128, 'Available Times');
INSERT INTO `lang_constants` VALUES (129, 'No openings were found for this period.');
INSERT INTO `lang_constants` VALUES (130, '* Reminder done');
INSERT INTO `lang_constants` VALUES (131, '+ Chart pulled');
INSERT INTO `lang_constants` VALUES (132, '? No show');
INSERT INTO `lang_constants` VALUES (133, '@ Arrived');
INSERT INTO `lang_constants` VALUES (134, '~ Arrived late');
INSERT INTO `lang_constants` VALUES (135, '! Left w/o visit');
INSERT INTO `lang_constants` VALUES (136, 'Ins/fin issue');
INSERT INTO `lang_constants` VALUES (137, '< In exam room');
INSERT INTO `lang_constants` VALUES (138, '> Checked out');
INSERT INTO `lang_constants` VALUES (139, '$ Coding done');
INSERT INTO `lang_constants` VALUES (140, 'Event');
INSERT INTO `lang_constants` VALUES (141, 'All day event');
INSERT INTO `lang_constants` VALUES (142, 'yyyy-mm-dd event date or starting date');
INSERT INTO `lang_constants` VALUES (143, 'Click here to choose a date');
INSERT INTO `lang_constants` VALUES (144, 'Time');
INSERT INTO `lang_constants` VALUES (145, 'Event start time');
INSERT INTO `lang_constants` VALUES (146, 'AM');
INSERT INTO `lang_constants` VALUES (147, 'PM');
INSERT INTO `lang_constants` VALUES (148, 'Event title');
INSERT INTO `lang_constants` VALUES (149, 'duration');
INSERT INTO `lang_constants` VALUES (150, 'Event duration in minutes');
INSERT INTO `lang_constants` VALUES (151, 'Click to select patient');
INSERT INTO `lang_constants` VALUES (152, 'Repeats');
INSERT INTO `lang_constants` VALUES (153, 'Pref Cat');
INSERT INTO `lang_constants` VALUES (154, 'Appointment status');
INSERT INTO `lang_constants` VALUES (155, 'Preferred Event Category');
INSERT INTO `lang_constants` VALUES (156, 'until');
INSERT INTO `lang_constants` VALUES (157, 'yyyy-mm-dd last date of this event');
INSERT INTO `lang_constants` VALUES (158, 'Comments');
INSERT INTO `lang_constants` VALUES (159, 'Optional information about this event');
INSERT INTO `lang_constants` VALUES (160, 'DOB is missing, please enter if possible');
INSERT INTO `lang_constants` VALUES (161, 'yyyy-mm-dd date of birth');
INSERT INTO `lang_constants` VALUES (162, 'Patient Appointment');
INSERT INTO `lang_constants` VALUES (163, '(Notes and Authorizations)');
INSERT INTO `lang_constants` VALUES (164, '(Patient Notes)');
INSERT INTO `lang_constants` VALUES (165, 'Search');
INSERT INTO `lang_constants` VALUES (166, 'Records Found');
INSERT INTO `lang_constants` VALUES (167, '(New Patient)');
INSERT INTO `lang_constants` VALUES (168, 'Please enter some information');
INSERT INTO `lang_constants` VALUES (169, 'Select Patient');
INSERT INTO `lang_constants` VALUES (170, 'by');
INSERT INTO `lang_constants` VALUES (171, 'Office Notes');
INSERT INTO `lang_constants` VALUES (172, 'Add New Note');
INSERT INTO `lang_constants` VALUES (173, 'View:');
INSERT INTO `lang_constants` VALUES (174, 'All');
INSERT INTO `lang_constants` VALUES (175, 'Only Active');
INSERT INTO `lang_constants` VALUES (176, 'Only Inactive');
INSERT INTO `lang_constants` VALUES (177, 'Change Activity');
INSERT INTO `lang_constants` VALUES (178, 'Previous');
INSERT INTO `lang_constants` VALUES (179, 'Next');
INSERT INTO `lang_constants` VALUES (180, 'Popups');
INSERT INTO `lang_constants` VALUES (181, 'Issues');
INSERT INTO `lang_constants` VALUES (182, 'Export');
INSERT INTO `lang_constants` VALUES (183, 'Import');
INSERT INTO `lang_constants` VALUES (184, 'Appts');
INSERT INTO `lang_constants` VALUES (185, 'Refer');
INSERT INTO `lang_constants` VALUES (186, 'Payment');
INSERT INTO `lang_constants` VALUES (187, 'Debug Information');
INSERT INTO `lang_constants` VALUES (188, 'Pennington Firm OpenEMR v');
INSERT INTO `lang_constants` VALUES (189, 'Due Ins');
INSERT INTO `lang_constants` VALUES (190, 'Due Pt');
INSERT INTO `lang_constants` VALUES (191, 'Collections Report');
INSERT INTO `lang_constants` VALUES (192, 'Svc Date:');
INSERT INTO `lang_constants` VALUES (193, 'To:');
INSERT INTO `lang_constants` VALUES (194, 'Open');
INSERT INTO `lang_constants` VALUES (195, 'Credits');
INSERT INTO `lang_constants` VALUES (196, 'Insurance');
INSERT INTO `lang_constants` VALUES (197, 'Phone');
INSERT INTO `lang_constants` VALUES (198, 'City');
INSERT INTO `lang_constants` VALUES (199, 'Invoice');
INSERT INTO `lang_constants` VALUES (200, 'Svc Date');
INSERT INTO `lang_constants` VALUES (201, 'Charge');
INSERT INTO `lang_constants` VALUES (202, 'Adjust');
INSERT INTO `lang_constants` VALUES (203, 'Paid');
INSERT INTO `lang_constants` VALUES (204, 'Balance');
INSERT INTO `lang_constants` VALUES (205, 'Prv');
INSERT INTO `lang_constants` VALUES (206, 'Sel');
INSERT INTO `lang_constants` VALUES (207, 'End Date');
INSERT INTO `lang_constants` VALUES (208, 'Patient Data');
INSERT INTO `lang_constants` VALUES (209, 'Primary Insurance Data');
INSERT INTO `lang_constants` VALUES (210, 'Secondary Insurance Data');
INSERT INTO `lang_constants` VALUES (211, 'Tertiary Insurance Data');
INSERT INTO `lang_constants` VALUES (212, 'Billing Information');
INSERT INTO `lang_constants` VALUES (213, 'Code');
INSERT INTO `lang_constants` VALUES (214, 'Fee');
INSERT INTO `lang_constants` VALUES (215, 'Sub-Total');
INSERT INTO `lang_constants` VALUES (216, 'Total');
INSERT INTO `lang_constants` VALUES (217, 'Physician Signature');
INSERT INTO `lang_constants` VALUES (218, 'Patient Insurance Distribution');
INSERT INTO `lang_constants` VALUES (219, 'Primary Insurance');
INSERT INTO `lang_constants` VALUES (220, 'Patients');
INSERT INTO `lang_constants` VALUES (221, 'Percent');
INSERT INTO `lang_constants` VALUES (222, 'Front Office Receipts');
INSERT INTO `lang_constants` VALUES (223, 'Unique Seen Patients');
INSERT INTO `lang_constants` VALUES (224, 'Visits From');
INSERT INTO `lang_constants` VALUES (225, 'Last Visit');
INSERT INTO `lang_constants` VALUES (226, 'Visits');
INSERT INTO `lang_constants` VALUES (227, 'Age');
INSERT INTO `lang_constants` VALUES (228, 'Sex');
INSERT INTO `lang_constants` VALUES (229, 'Race');
INSERT INTO `lang_constants` VALUES (230, 'Secondary Insurance');
INSERT INTO `lang_constants` VALUES (231, 'Total Number of Patients');
INSERT INTO `lang_constants` VALUES (232, 'Injury Overview Report');
INSERT INTO `lang_constants` VALUES (233, 'Hold down Ctrl to select multiple squads');
INSERT INTO `lang_constants` VALUES (234, 'Click to generate the report');
INSERT INTO `lang_constants` VALUES (235, 'Receipts by Payment Method');
INSERT INTO `lang_constants` VALUES (236, 'Payment Date');
INSERT INTO `lang_constants` VALUES (237, 'Invoice Date');
INSERT INTO `lang_constants` VALUES (238, 'From:');
INSERT INTO `lang_constants` VALUES (239, 'Method');
INSERT INTO `lang_constants` VALUES (240, 'Procedure');
INSERT INTO `lang_constants` VALUES (241, 'Amount');
INSERT INTO `lang_constants` VALUES (242, 'Total for');
INSERT INTO `lang_constants` VALUES (243, 'Grand Total');
INSERT INTO `lang_constants` VALUES (244, 'Absences by Diagnosis');
INSERT INTO `lang_constants` VALUES (245, 'Days and Games Missed');
INSERT INTO `lang_constants` VALUES (246, 'By:');
INSERT INTO `lang_constants` VALUES (247, 'Diagnosis');
INSERT INTO `lang_constants` VALUES (248, 'Player');
INSERT INTO `lang_constants` VALUES (249, 'Description');
INSERT INTO `lang_constants` VALUES (250, 'Games');
INSERT INTO `lang_constants` VALUES (251, 'Sales by Item');
INSERT INTO `lang_constants` VALUES (252, 'Item');
INSERT INTO `lang_constants` VALUES (253, 'Qty');
INSERT INTO `lang_constants` VALUES (254, 'Activity Type');
INSERT INTO `lang_constants` VALUES (255, 'Body Region');
INSERT INTO `lang_constants` VALUES (256, 'Footwear Type');
INSERT INTO `lang_constants` VALUES (257, 'Game Period');
INSERT INTO `lang_constants` VALUES (258, 'Injury Mechanism');
INSERT INTO `lang_constants` VALUES (259, 'Injury Type');
INSERT INTO `lang_constants` VALUES (260, 'Playing Position');
INSERT INTO `lang_constants` VALUES (261, 'Sanction Type');
INSERT INTO `lang_constants` VALUES (262, 'Surface Type');
INSERT INTO `lang_constants` VALUES (263, 'Training Type');
INSERT INTO `lang_constants` VALUES (264, 'Ankle + heel');
INSERT INTO `lang_constants` VALUES (265, 'Buttock + S.I.');
INSERT INTO `lang_constants` VALUES (266, 'Chest');
INSERT INTO `lang_constants` VALUES (267, 'Thoracic spine');
INSERT INTO `lang_constants` VALUES (268, 'Elbow');
INSERT INTO `lang_constants` VALUES (269, 'Foot');
INSERT INTO `lang_constants` VALUES (270, 'Hip + groin');
INSERT INTO `lang_constants` VALUES (271, 'Head');
INSERT INTO `lang_constants` VALUES (272, 'Knee');
INSERT INTO `lang_constants` VALUES (273, 'Lumbar spine');
INSERT INTO `lang_constants` VALUES (274, 'Medical problem');
INSERT INTO `lang_constants` VALUES (275, 'Neck');
INSERT INTO `lang_constants` VALUES (276, 'Abdominal');
INSERT INTO `lang_constants` VALUES (277, 'Hand + fingers');
INSERT INTO `lang_constants` VALUES (278, 'Lower leg');
INSERT INTO `lang_constants` VALUES (279, 'Forearm');
INSERT INTO `lang_constants` VALUES (280, 'Shoulder + clavicle');
INSERT INTO `lang_constants` VALUES (281, 'Thigh + hamstring');
INSERT INTO `lang_constants` VALUES (282, 'Upper arm');
INSERT INTO `lang_constants` VALUES (283, 'Wrist');
INSERT INTO `lang_constants` VALUES (284, 'Multiple areas');
INSERT INTO `lang_constants` VALUES (285, 'Area not specified');
INSERT INTO `lang_constants` VALUES (286, 'Environmental');
INSERT INTO `lang_constants` VALUES (287, 'Fluid and electrolyte problem');
INSERT INTO `lang_constants` VALUES (288, 'Arthritis / degen joint disease');
INSERT INTO `lang_constants` VALUES (289, 'Developmental abnormality');
INSERT INTO `lang_constants` VALUES (290, 'Cartilage / chondral / disc damage');
INSERT INTO `lang_constants` VALUES (291, 'Dislocation');
INSERT INTO `lang_constants` VALUES (292, 'Tumour');
INSERT INTO `lang_constants` VALUES (293, 'Fracture');
INSERT INTO `lang_constants` VALUES (294, 'Avulsion / avulsion fracture');
INSERT INTO `lang_constants` VALUES (295, 'Haematoma / bruising');
INSERT INTO `lang_constants` VALUES (296, 'Infection / Abscess');
INSERT INTO `lang_constants` VALUES (297, 'Minor joint strain +/- synovitis');
INSERT INTO `lang_constants` VALUES (298, 'Laceration / skin condition');
INSERT INTO `lang_constants` VALUES (299, 'Ligament tear or sprain');
INSERT INTO `lang_constants` VALUES (300, 'Strain of muscle');
INSERT INTO `lang_constants` VALUES (301, 'Neural condition / nerve damage');
INSERT INTO `lang_constants` VALUES (302, 'Visceral damage/trauma/surgery');
INSERT INTO `lang_constants` VALUES (303, 'Chronic synovitis / effusion / joint pain / gout');
INSERT INTO `lang_constants` VALUES (304, 'Old fracture non / malunion');
INSERT INTO `lang_constants` VALUES (305, 'Rupture');
INSERT INTO `lang_constants` VALUES (306, 'Stress fracture');
INSERT INTO `lang_constants` VALUES (307, 'Tendonitis / osis / bursitis');
INSERT INTO `lang_constants` VALUES (308, 'Instability / subluxation');
INSERT INTO `lang_constants` VALUES (309, 'Vascular condition');
INSERT INTO `lang_constants` VALUES (310, 'Trigger point / compartment syndrome / DOMS / cramp');
INSERT INTO `lang_constants` VALUES (311, 'Undiagnosed');
INSERT INTO `lang_constants` VALUES (312, 'Arthritis / degen joint diseas');
INSERT INTO `lang_constants` VALUES (313, 'Football Injury Report');
INSERT INTO `lang_constants` VALUES (314, 'Hold down Ctrl to select multiple items');
INSERT INTO `lang_constants` VALUES (315, 'Team Roster');
INSERT INTO `lang_constants` VALUES (316, 'Squad');
INSERT INTO `lang_constants` VALUES (317, 'Fitness');
INSERT INTO `lang_constants` VALUES (318, 'Last Encounter');
INSERT INTO `lang_constants` VALUES (319, 'Source');
INSERT INTO `lang_constants` VALUES (320, 'Today');
INSERT INTO `lang_constants` VALUES (321, 'Totals');
INSERT INTO `lang_constants` VALUES (322, 'Patient List');
INSERT INTO `lang_constants` VALUES (323, 'Street');
INSERT INTO `lang_constants` VALUES (324, 'State');
INSERT INTO `lang_constants` VALUES (325, 'Zip');
INSERT INTO `lang_constants` VALUES (326, 'Home Phone');
INSERT INTO `lang_constants` VALUES (327, 'Work Phone');
INSERT INTO `lang_constants` VALUES (328, 'Appointments Report');
INSERT INTO `lang_constants` VALUES (329, 'Appointments and Encounters');
INSERT INTO `lang_constants` VALUES (330, 'Booking Date');
INSERT INTO `lang_constants` VALUES (331, 'Practitioner');
INSERT INTO `lang_constants` VALUES (332, 'Chart');
INSERT INTO `lang_constants` VALUES (333, 'Encounter');
INSERT INTO `lang_constants` VALUES (334, 'Billed');
INSERT INTO `lang_constants` VALUES (335, 'Error');
INSERT INTO `lang_constants` VALUES (336, 'Destroyed Drugs');
INSERT INTO `lang_constants` VALUES (337, 'Drug Name');
INSERT INTO `lang_constants` VALUES (338, 'NDC');
INSERT INTO `lang_constants` VALUES (339, 'Lot');
INSERT INTO `lang_constants` VALUES (340, 'Date Destroyed');
INSERT INTO `lang_constants` VALUES (341, 'Witness');
INSERT INTO `lang_constants` VALUES (342, 'Prescriptions and Dispensations');
INSERT INTO `lang_constants` VALUES (343, 'Patient ID');
INSERT INTO `lang_constants` VALUES (344, 'Drug');
INSERT INTO `lang_constants` VALUES (345, 'RX');
INSERT INTO `lang_constants` VALUES (346, 'Units');
INSERT INTO `lang_constants` VALUES (347, 'Refills');
INSERT INTO `lang_constants` VALUES (348, 'Instructed');
INSERT INTO `lang_constants` VALUES (349, 'Reactions');
INSERT INTO `lang_constants` VALUES (350, 'Dispensed');
INSERT INTO `lang_constants` VALUES (351, 'Manufacturer');
INSERT INTO `lang_constants` VALUES (352, 'NDC Number');
INSERT INTO `lang_constants` VALUES (353, 'On Order');
INSERT INTO `lang_constants` VALUES (354, 'Reorder At');
INSERT INTO `lang_constants` VALUES (355, 'Form');
INSERT INTO `lang_constants` VALUES (356, 'Pill Size');
INSERT INTO `lang_constants` VALUES (357, 'Route');
INSERT INTO `lang_constants` VALUES (358, 'Templates');
INSERT INTO `lang_constants` VALUES (359, 'Schedule');
INSERT INTO `lang_constants` VALUES (360, 'Interval');
INSERT INTO `lang_constants` VALUES (361, 'suspension');
INSERT INTO `lang_constants` VALUES (362, 'tablet');
INSERT INTO `lang_constants` VALUES (363, 'capsule');
INSERT INTO `lang_constants` VALUES (364, 'solution');
INSERT INTO `lang_constants` VALUES (365, 'tsp');
INSERT INTO `lang_constants` VALUES (366, 'ml');
INSERT INTO `lang_constants` VALUES (367, 'inhalations');
INSERT INTO `lang_constants` VALUES (368, 'gtts(drops)');
INSERT INTO `lang_constants` VALUES (369, 'Per Oris');
INSERT INTO `lang_constants` VALUES (370, 'Per Rectum');
INSERT INTO `lang_constants` VALUES (371, 'To Skin');
INSERT INTO `lang_constants` VALUES (372, 'To Affected Area');
INSERT INTO `lang_constants` VALUES (373, 'Sublingual');
INSERT INTO `lang_constants` VALUES (374, 'OS');
INSERT INTO `lang_constants` VALUES (375, 'OD');
INSERT INTO `lang_constants` VALUES (376, 'OU');
INSERT INTO `lang_constants` VALUES (377, 'SQ');
INSERT INTO `lang_constants` VALUES (378, 'IM');
INSERT INTO `lang_constants` VALUES (379, 'IV');
INSERT INTO `lang_constants` VALUES (380, 'Per Nostril');
INSERT INTO `lang_constants` VALUES (381, 'twice daily');
INSERT INTO `lang_constants` VALUES (382, '3 times daily');
INSERT INTO `lang_constants` VALUES (383, '4 times daily');
INSERT INTO `lang_constants` VALUES (384, 'every 3 hours');
INSERT INTO `lang_constants` VALUES (385, 'every 4 hours');
INSERT INTO `lang_constants` VALUES (386, 'every 5 hours');
INSERT INTO `lang_constants` VALUES (387, 'every 6 hours');
INSERT INTO `lang_constants` VALUES (388, 'every 8 hours');
INSERT INTO `lang_constants` VALUES (389, 'daily');
INSERT INTO `lang_constants` VALUES (390, 'by mouth');
INSERT INTO `lang_constants` VALUES (391, 'rectally');
INSERT INTO `lang_constants` VALUES (392, 'under tongue');
INSERT INTO `lang_constants` VALUES (393, 'in left eye');
INSERT INTO `lang_constants` VALUES (394, 'in right eye');
INSERT INTO `lang_constants` VALUES (395, 'in each eye');
INSERT INTO `lang_constants` VALUES (396, 'subcutaneously');
INSERT INTO `lang_constants` VALUES (397, 'intramuscularly');
INSERT INTO `lang_constants` VALUES (398, 'intravenously');
INSERT INTO `lang_constants` VALUES (399, 'in nostril');
INSERT INTO `lang_constants` VALUES (400, 'Allowed');
INSERT INTO `lang_constants` VALUES (401, 'Drug Inventory');
INSERT INTO `lang_constants` VALUES (402, 'Size');
INSERT INTO `lang_constants` VALUES (403, 'Unit');
INSERT INTO `lang_constants` VALUES (404, 'Add');
INSERT INTO `lang_constants` VALUES (405, 'QOH');
INSERT INTO `lang_constants` VALUES (406, 'Expires');
INSERT INTO `lang_constants` VALUES (407, 'Destroy Lot');
INSERT INTO `lang_constants` VALUES (408, 'Lot Number');
INSERT INTO `lang_constants` VALUES (409, 'Quantity On Hand');
INSERT INTO `lang_constants` VALUES (410, 'Expiration Date');
INSERT INTO `lang_constants` VALUES (411, 'Method of Destruction');
INSERT INTO `lang_constants` VALUES (412, 'Prescription Label');
INSERT INTO `lang_constants` VALUES (413, 'Expiration');
INSERT INTO `lang_constants` VALUES (414, 'On Hand');
INSERT INTO `lang_constants` VALUES (415, 'Work/School Note');
INSERT INTO `lang_constants` VALUES (416, 'Save');
INSERT INTO `lang_constants` VALUES (417, 'Don''t Save');
INSERT INTO `lang_constants` VALUES (418, 'WORK NOTE');
INSERT INTO `lang_constants` VALUES (419, 'SCHOOL NOTE');
INSERT INTO `lang_constants` VALUES (420, 'MESSAGE:');
INSERT INTO `lang_constants` VALUES (421, 'Signature:');
INSERT INTO `lang_constants` VALUES (422, 'Doctor:');
INSERT INTO `lang_constants` VALUES (423, 'Don''t Save Changes');
INSERT INTO `lang_constants` VALUES (424, 'Date:');
INSERT INTO `lang_constants` VALUES (425, 'Review of Systems Checks');
INSERT INTO `lang_constants` VALUES (426, 'General');
INSERT INTO `lang_constants` VALUES (427, 'Skin');
INSERT INTO `lang_constants` VALUES (428, 'HEENT');
INSERT INTO `lang_constants` VALUES (429, 'Cardiovascular');
INSERT INTO `lang_constants` VALUES (430, 'Endocrine');
INSERT INTO `lang_constants` VALUES (431, 'Pulmonary');
INSERT INTO `lang_constants` VALUES (432, 'Genitourinary');
INSERT INTO `lang_constants` VALUES (433, 'Gastrointestinal');
INSERT INTO `lang_constants` VALUES (434, 'Musculoskeletal');
INSERT INTO `lang_constants` VALUES (435, 'Additional Notes:');
INSERT INTO `lang_constants` VALUES (436, 'Previous Encounter CAMOS entries');
INSERT INTO `lang_constants` VALUES (437, 'Subcategory');
INSERT INTO `lang_constants` VALUES (438, 'Content');
INSERT INTO `lang_constants` VALUES (439, 'do not save');
INSERT INTO `lang_constants` VALUES (440, 'help');
INSERT INTO `lang_constants` VALUES (441, 'CAMOS');
INSERT INTO `lang_constants` VALUES (442, 'do nothing');
INSERT INTO `lang_constants` VALUES (443, 'Computer Aided Medical Ordering System');
INSERT INTO `lang_constants` VALUES (444, 'Ankle Evaluation Form');
INSERT INTO `lang_constants` VALUES (445, 'Date of Injury');
INSERT INTO `lang_constants` VALUES (446, 'Work related?');
INSERT INTO `lang_constants` VALUES (447, 'Foot:');
INSERT INTO `lang_constants` VALUES (448, 'Left');
INSERT INTO `lang_constants` VALUES (449, 'Right');
INSERT INTO `lang_constants` VALUES (450, 'Severity of Pain');
INSERT INTO `lang_constants` VALUES (451, 'Significant Swelling:');
INSERT INTO `lang_constants` VALUES (452, 'Onset of Swelling:');
INSERT INTO `lang_constants` VALUES (453, 'within minutes');
INSERT INTO `lang_constants` VALUES (454, 'within hours');
INSERT INTO `lang_constants` VALUES (455, 'How did Injury Occur?');
INSERT INTO `lang_constants` VALUES (456, 'Ottawa Ankle Rules');
INSERT INTO `lang_constants` VALUES (457, 'Bone Tenderness: Medial Malleolus');
INSERT INTO `lang_constants` VALUES (458, 'Lateral Malleolus');
INSERT INTO `lang_constants` VALUES (459, 'Base of fifth (5th) Metarsal');
INSERT INTO `lang_constants` VALUES (460, 'At the Navicular');
INSERT INTO `lang_constants` VALUES (461, 'Able to Bear Weight four (4) steps:');
INSERT INTO `lang_constants` VALUES (462, 'Yes');
INSERT INTO `lang_constants` VALUES (463, 'No');
INSERT INTO `lang_constants` VALUES (464, 'X-RAY Interpretation:');
INSERT INTO `lang_constants` VALUES (465, 'Normal');
INSERT INTO `lang_constants` VALUES (466, 'Avulsion medial malleolus');
INSERT INTO `lang_constants` VALUES (467, 'Avulsion lateral malleolus');
INSERT INTO `lang_constants` VALUES (468, 'Fracture, Base of fifth (5th) Metatarsal');
INSERT INTO `lang_constants` VALUES (469, 'Trimalleolar');
INSERT INTO `lang_constants` VALUES (470, 'Fracture at the Navicula');
INSERT INTO `lang_constants` VALUES (471, 'Fracture medial malleolus');
INSERT INTO `lang_constants` VALUES (472, 'Fracture lateral malleolus');
INSERT INTO `lang_constants` VALUES (473, 'Other');
INSERT INTO `lang_constants` VALUES (474, 'Diagnosis:');
INSERT INTO `lang_constants` VALUES (475, 'None');
INSERT INTO `lang_constants` VALUES (476, '845.00 ankle sprain NOS');
INSERT INTO `lang_constants` VALUES (477, '845.01 Sprain Medial (Deltoid) Lig.');
INSERT INTO `lang_constants` VALUES (478, '845.02 Sprain, Calcaneal fibular');
INSERT INTO `lang_constants` VALUES (479, '825.35 Fracture, Base of fifth (5th) Metatarsal');
INSERT INTO `lang_constants` VALUES (480, '825.32 Fracture, of Navicular (ankle)');
INSERT INTO `lang_constants` VALUES (481, '824.2 Fracture, lateral malleolus, closed');
INSERT INTO `lang_constants` VALUES (482, '824.0 Fracture, medial malleolus, closed');
INSERT INTO `lang_constants` VALUES (483, '824.6 Fracture, Trimalleolar, closed');
INSERT INTO `lang_constants` VALUES (484, 'Add ICD Code');
INSERT INTO `lang_constants` VALUES (485, 'CPT Codes');
INSERT INTO `lang_constants` VALUES (486, 'Plan:');
INSERT INTO `lang_constants` VALUES (487, 'Left:');
INSERT INTO `lang_constants` VALUES (488, 'Right:');
INSERT INTO `lang_constants` VALUES (489, 'Severity of Pain:');
INSERT INTO `lang_constants` VALUES (490, 'within minutes:');
INSERT INTO `lang_constants` VALUES (491, 'within hours:');
INSERT INTO `lang_constants` VALUES (492, 'How did Injury Occur?:');
INSERT INTO `lang_constants` VALUES (493, 'Bone Tenderness:');
INSERT INTO `lang_constants` VALUES (494, 'Medial malleolus:');
INSERT INTO `lang_constants` VALUES (495, 'Lateral malleolus:');
INSERT INTO `lang_constants` VALUES (496, 'Base of fifth (5th) Metarsal:');
INSERT INTO `lang_constants` VALUES (497, 'At the Navicular:');
INSERT INTO `lang_constants` VALUES (498, 'Yes:');
INSERT INTO `lang_constants` VALUES (499, 'No:');
INSERT INTO `lang_constants` VALUES (500, 'New encounters not authorized');
INSERT INTO `lang_constants` VALUES (501, 'New Encounter');
INSERT INTO `lang_constants` VALUES (502, 'New Encounter Form');
INSERT INTO `lang_constants` VALUES (503, 'Chief Complaint:');
INSERT INTO `lang_constants` VALUES (504, 'Issues (Problems, Medications, Surgeries, Allergies):');
INSERT INTO `lang_constants` VALUES (505, 'Hold down [Ctrl] for multiple selections or to unselect');
INSERT INTO `lang_constants` VALUES (506, 'Facility:');
INSERT INTO `lang_constants` VALUES (507, 'Date of Service:');
INSERT INTO `lang_constants` VALUES (508, 'yyyy-mm-dd Date of service');
INSERT INTO `lang_constants` VALUES (509, 'Onset/hospitalization date:');
INSERT INTO `lang_constants` VALUES (510, 'yyyy-mm-dd Date of onset or hospitalization');
INSERT INTO `lang_constants` VALUES (511, 'Cancel');
INSERT INTO `lang_constants` VALUES (512, 'Add Issue');
INSERT INTO `lang_constants` VALUES (513, 'Patient Encounter');
INSERT INTO `lang_constants` VALUES (514, 'Patient Encounter Form');
INSERT INTO `lang_constants` VALUES (515, 'Sensitivity:');
INSERT INTO `lang_constants` VALUES (516, 'Sheet');
INSERT INTO `lang_constants` VALUES (517, 'Mod');
INSERT INTO `lang_constants` VALUES (518, 'Auth');
INSERT INTO `lang_constants` VALUES (519, 'Delete');
INSERT INTO `lang_constants` VALUES (520, 'PROVIDER:');
INSERT INTO `lang_constants` VALUES (521, 'UCSMC codes provided by the University of Calgary Sports Medicine Centre');
INSERT INTO `lang_constants` VALUES (522, 'Bronchitis Form');
INSERT INTO `lang_constants` VALUES (523, 'Onset of Illness:');
INSERT INTO `lang_constants` VALUES (524, 'HPI:');
INSERT INTO `lang_constants` VALUES (525, 'Other Pertinent Symptoms:');
INSERT INTO `lang_constants` VALUES (526, 'Fever:');
INSERT INTO `lang_constants` VALUES (527, 'Cough:');
INSERT INTO `lang_constants` VALUES (528, 'Dizziness:');
INSERT INTO `lang_constants` VALUES (529, 'Chest Pain:');
INSERT INTO `lang_constants` VALUES (530, 'Dyspnea:');
INSERT INTO `lang_constants` VALUES (531, 'Sweating:');
INSERT INTO `lang_constants` VALUES (532, 'Wheezing:');
INSERT INTO `lang_constants` VALUES (533, 'Malaise:');
INSERT INTO `lang_constants` VALUES (534, 'Sputum:');
INSERT INTO `lang_constants` VALUES (535, 'Appearance:');
INSERT INTO `lang_constants` VALUES (536, 'All Reviewed and Negative:');
INSERT INTO `lang_constants` VALUES (537, 'Review of PMH:');
INSERT INTO `lang_constants` VALUES (538, 'Medications:');
INSERT INTO `lang_constants` VALUES (539, 'Allergies:');
INSERT INTO `lang_constants` VALUES (540, 'Social History:');
INSERT INTO `lang_constants` VALUES (541, 'Family History:');
INSERT INTO `lang_constants` VALUES (542, 'TM''S:');
INSERT INTO `lang_constants` VALUES (543, 'Normal Right:');
INSERT INTO `lang_constants` VALUES (544, 'NARES:');
INSERT INTO `lang_constants` VALUES (545, 'Normal Right');
INSERT INTO `lang_constants` VALUES (546, 'Thickened Right:');
INSERT INTO `lang_constants` VALUES (547, 'Swelling Right');
INSERT INTO `lang_constants` VALUES (548, 'A/F Level Right:');
INSERT INTO `lang_constants` VALUES (549, 'Discharge Right:');
INSERT INTO `lang_constants` VALUES (550, 'Retracted Right:');
INSERT INTO `lang_constants` VALUES (551, 'Bulging Right:');
INSERT INTO `lang_constants` VALUES (552, 'Perforated Right:');
INSERT INTO `lang_constants` VALUES (553, 'Not Examined:');
INSERT INTO `lang_constants` VALUES (554, 'SINUS TENDERNESS:');
INSERT INTO `lang_constants` VALUES (555, 'No Sinus Tenderness:');
INSERT INTO `lang_constants` VALUES (556, 'OROPHARYNX:');
INSERT INTO `lang_constants` VALUES (557, 'Normal Oropharynx:');
INSERT INTO `lang_constants` VALUES (558, 'Frontal Right:');
INSERT INTO `lang_constants` VALUES (559, 'Erythema:');
INSERT INTO `lang_constants` VALUES (560, 'Exudate:');
INSERT INTO `lang_constants` VALUES (561, 'Abcess:');
INSERT INTO `lang_constants` VALUES (562, 'Ulcers:');
INSERT INTO `lang_constants` VALUES (563, 'Maxillary Right:');
INSERT INTO `lang_constants` VALUES (564, 'HEART:');
INSERT INTO `lang_constants` VALUES (565, 'laterally displaced PMI:');
INSERT INTO `lang_constants` VALUES (566, 'S3:');
INSERT INTO `lang_constants` VALUES (567, 'S4:');
INSERT INTO `lang_constants` VALUES (568, 'Click:');
INSERT INTO `lang_constants` VALUES (569, 'Rub:');
INSERT INTO `lang_constants` VALUES (570, 'Murmur:');
INSERT INTO `lang_constants` VALUES (571, 'Grade:');
INSERT INTO `lang_constants` VALUES (572, 'Location:');
INSERT INTO `lang_constants` VALUES (573, 'Normal Cardiac Exam:');
INSERT INTO `lang_constants` VALUES (574, 'LUNGS:');
INSERT INTO `lang_constants` VALUES (575, 'Breath Sounds:');
INSERT INTO `lang_constants` VALUES (576, 'normal:');
INSERT INTO `lang_constants` VALUES (577, 'reduced:');
INSERT INTO `lang_constants` VALUES (578, 'increased:');
INSERT INTO `lang_constants` VALUES (579, 'Crackles:');
INSERT INTO `lang_constants` VALUES (580, 'LLL:');
INSERT INTO `lang_constants` VALUES (581, 'RLL:');
INSERT INTO `lang_constants` VALUES (582, 'Bilateral:');
INSERT INTO `lang_constants` VALUES (583, 'Rubs:');
INSERT INTO `lang_constants` VALUES (584, 'Wheezes:');
INSERT INTO `lang_constants` VALUES (585, 'Diffuse:');
INSERT INTO `lang_constants` VALUES (586, 'Normal Lung Exam:');
INSERT INTO `lang_constants` VALUES (587, 'Diagnostic Tests:');
INSERT INTO `lang_constants` VALUES (588, '465.9, URI');
INSERT INTO `lang_constants` VALUES (589, '466.0, Bronchitis, Acute NOS');
INSERT INTO `lang_constants` VALUES (590, '493.92, Asthma, Acute Exac.');
INSERT INTO `lang_constants` VALUES (591, '491.8, Bronchitis, Chronic');
INSERT INTO `lang_constants` VALUES (592, '496.0, COPD');
INSERT INTO `lang_constants` VALUES (593, '491.21, COPD Exacerbation');
INSERT INTO `lang_constants` VALUES (594, '486.0, Pneumonia, Acute');
INSERT INTO `lang_constants` VALUES (595, '519.7, Bronchospasm');
INSERT INTO `lang_constants` VALUES (596, 'Additional Diagnosis:');
INSERT INTO `lang_constants` VALUES (597, 'Treatment:');
INSERT INTO `lang_constants` VALUES (598, 'Onset of Ilness:');
INSERT INTO `lang_constants` VALUES (599, 'Other Pertinent Symptoms');
INSERT INTO `lang_constants` VALUES (600, 'NARES: Normal Right');
INSERT INTO `lang_constants` VALUES (601, 'Discharge Right');
INSERT INTO `lang_constants` VALUES (602, 'Not Examined');
INSERT INTO `lang_constants` VALUES (603, 'Edit Diagnoses for');
INSERT INTO `lang_constants` VALUES (604, 'Order');
INSERT INTO `lang_constants` VALUES (605, 'WNL');
INSERT INTO `lang_constants` VALUES (606, 'ABN1');
INSERT INTO `lang_constants` VALUES (607, 'System');
INSERT INTO `lang_constants` VALUES (608, 'Specific');
INSERT INTO `lang_constants` VALUES (609, 'Appearance');
INSERT INTO `lang_constants` VALUES (610, 'Conjuntiva, pupils');
INSERT INTO `lang_constants` VALUES (611, 'TMs/EAMs/EE, ext nose');
INSERT INTO `lang_constants` VALUES (612, 'Nasal mucosa pink, septum midline');
INSERT INTO `lang_constants` VALUES (613, 'Oral mucosa pink, throat clear');
INSERT INTO `lang_constants` VALUES (614, 'Neck supple');
INSERT INTO `lang_constants` VALUES (615, 'Thyroid normal');
INSERT INTO `lang_constants` VALUES (616, 'RRR without MOR');
INSERT INTO `lang_constants` VALUES (617, 'No thrills or heaves');
INSERT INTO `lang_constants` VALUES (618, 'Cartoid pulsations nl, pedal pulses nl');
INSERT INTO `lang_constants` VALUES (619, 'No peripheral edema');
INSERT INTO `lang_constants` VALUES (620, 'No skin dimpling or breast nodules');
INSERT INTO `lang_constants` VALUES (621, 'Chest CTAB');
INSERT INTO `lang_constants` VALUES (622, 'Respirator effort unlabored');
INSERT INTO `lang_constants` VALUES (623, 'No masses, tenderness');
INSERT INTO `lang_constants` VALUES (624, 'No ogrganomegoly');
INSERT INTO `lang_constants` VALUES (625, 'No hernia');
INSERT INTO `lang_constants` VALUES (626, 'Anus nl, no rectal tenderness/mass');
INSERT INTO `lang_constants` VALUES (627, 'No testicular tenderness, masses');
INSERT INTO `lang_constants` VALUES (628, 'Prostate w/o enlrgmt, nodules, tender');
INSERT INTO `lang_constants` VALUES (629, 'Nl ext genitalia, vag mucosa, cervix');
INSERT INTO `lang_constants` VALUES (630, 'No adnexal tenderness/masses');
INSERT INTO `lang_constants` VALUES (631, 'No adenopathy (2 areas required)');
INSERT INTO `lang_constants` VALUES (632, 'Strength');
INSERT INTO `lang_constants` VALUES (633, 'ROM');
INSERT INTO `lang_constants` VALUES (634, 'Stability');
INSERT INTO `lang_constants` VALUES (635, 'Inspection');
INSERT INTO `lang_constants` VALUES (636, 'CN2-12 intact');
INSERT INTO `lang_constants` VALUES (637, 'Reflexes normal');
INSERT INTO `lang_constants` VALUES (638, 'Sensory exam normal');
INSERT INTO `lang_constants` VALUES (639, 'Orientated x 3');
INSERT INTO `lang_constants` VALUES (640, 'Affect normal');
INSERT INTO `lang_constants` VALUES (641, 'No rash or abnormal lesions');
INSERT INTO `lang_constants` VALUES (642, 'Labs');
INSERT INTO `lang_constants` VALUES (643, 'X-ray');
INSERT INTO `lang_constants` VALUES (644, 'Return Visit');
INSERT INTO `lang_constants` VALUES (645, 'Speech Dictation');
INSERT INTO `lang_constants` VALUES (646, 'Dictation:');
INSERT INTO `lang_constants` VALUES (647, 'Checked box = yes , empty = no');
INSERT INTO `lang_constants` VALUES (648, 'BOX 10 A. Employment related');
INSERT INTO `lang_constants` VALUES (649, 'BOX 10 B. Auto Accident');
INSERT INTO `lang_constants` VALUES (650, 'BOX 10 C. Other Accident');
INSERT INTO `lang_constants` VALUES (651, 'BOX 16. Date unable to work from (yyyy-mm-dd):');
INSERT INTO `lang_constants` VALUES (652, 'BOX 16. Date unable to work to (yyyy-mm-dd):');
INSERT INTO `lang_constants` VALUES (653, 'BOX 18. Hospitalization date from (yyyy-mm-dd):');
INSERT INTO `lang_constants` VALUES (654, 'BOX 18. Hospitalization date to (yyyy-mm-dd):');
INSERT INTO `lang_constants` VALUES (655, 'BOX 20. Is Outside Lab used?');
INSERT INTO `lang_constants` VALUES (656, 'BOX 22. Medicaid Resubmission Code (ICD-9)');
INSERT INTO `lang_constants` VALUES (657, 'Medicaid Original Reference No.');
INSERT INTO `lang_constants` VALUES (658, 'BOX 23. Prior Authorization No.');
INSERT INTO `lang_constants` VALUES (659, 'Follow manually');
INSERT INTO `lang_constants` VALUES (660, 'OpenEMR requires Javascript to perform user authentication.');
INSERT INTO `lang_constants` VALUES (661, 'Logs Viewer');
INSERT INTO `lang_constants` VALUES (662, 'Refresh');
INSERT INTO `lang_constants` VALUES (663, 'List Insurance Companies');
INSERT INTO `lang_constants` VALUES (664, 'Attn');
INSERT INTO `lang_constants` VALUES (665, 'Address');
INSERT INTO `lang_constants` VALUES (666, 'Other HCFA');
INSERT INTO `lang_constants` VALUES (667, 'Medicare Part B');
INSERT INTO `lang_constants` VALUES (668, 'Medicaid');
INSERT INTO `lang_constants` VALUES (669, 'ChampUSVA');
INSERT INTO `lang_constants` VALUES (670, 'ChampUS');
INSERT INTO `lang_constants` VALUES (671, 'Blue Cross Blue Shield');
INSERT INTO `lang_constants` VALUES (672, 'FECA');
INSERT INTO `lang_constants` VALUES (673, 'Self Pay');
INSERT INTO `lang_constants` VALUES (674, 'Central Certification');
INSERT INTO `lang_constants` VALUES (675, 'Other Non-Federal Programs');
INSERT INTO `lang_constants` VALUES (676, 'Preferred Provider Organization (PPO)');
INSERT INTO `lang_constants` VALUES (677, 'Point of Service (POS)');
INSERT INTO `lang_constants` VALUES (678, 'Exclusive Provider Organization (EPO)');
INSERT INTO `lang_constants` VALUES (679, 'Indemnity Insurance');
INSERT INTO `lang_constants` VALUES (680, 'Health Maintenance Organization (HMO) Medicare Risk');
INSERT INTO `lang_constants` VALUES (681, 'Automobile Medical');
INSERT INTO `lang_constants` VALUES (682, 'Commercial Insurance Co.');
INSERT INTO `lang_constants` VALUES (683, 'Disability');
INSERT INTO `lang_constants` VALUES (684, 'Health Maintenance Organization');
INSERT INTO `lang_constants` VALUES (685, 'Liability');
INSERT INTO `lang_constants` VALUES (686, 'Liability Medical');
INSERT INTO `lang_constants` VALUES (687, 'Other Federal Program');
INSERT INTO `lang_constants` VALUES (688, 'Title V');
INSERT INTO `lang_constants` VALUES (689, 'Veterans Administration Plan');
INSERT INTO `lang_constants` VALUES (690, 'Workers Compensation Health Plan');
INSERT INTO `lang_constants` VALUES (691, 'Mutually Defined');
INSERT INTO `lang_constants` VALUES (692, 'Insurance Company Search/Add');
INSERT INTO `lang_constants` VALUES (693, 'Name of insurance company');
INSERT INTO `lang_constants` VALUES (694, 'Attention');
INSERT INTO `lang_constants` VALUES (695, 'Contact name');
INSERT INTO `lang_constants` VALUES (696, 'Address1');
INSERT INTO `lang_constants` VALUES (697, 'Address2');
INSERT INTO `lang_constants` VALUES (698, 'City/State');
INSERT INTO `lang_constants` VALUES (699, 'Zip/Country:');
INSERT INTO `lang_constants` VALUES (700, 'CMS ID');
INSERT INTO `lang_constants` VALUES (701, 'Payer Type');
INSERT INTO `lang_constants` VALUES (702, 'X12 Partner');
INSERT INTO `lang_constants` VALUES (703, 'None');
INSERT INTO `lang_constants` VALUES (704, 'Forms Administration');
INSERT INTO `lang_constants` VALUES (705, 'Registered');
INSERT INTO `lang_constants` VALUES (706, 'click here to update priority, category and nickname settings');
INSERT INTO `lang_constants` VALUES (707, 'Priority');
INSERT INTO `lang_constants` VALUES (708, 'Nickname');
INSERT INTO `lang_constants` VALUES (709, 'disabled');
INSERT INTO `lang_constants` VALUES (710, 'enabled');
INSERT INTO `lang_constants` VALUES (711, 'PHP extracted');
INSERT INTO `lang_constants` VALUES (712, 'PHP compressed');
INSERT INTO `lang_constants` VALUES (713, 'DB installed');
INSERT INTO `lang_constants` VALUES (714, 'install DB');
INSERT INTO `lang_constants` VALUES (715, 'Unregistered');
INSERT INTO `lang_constants` VALUES (716, 'msec');
INSERT INTO `lang_constants` VALUES (717, 'Logged in as:');
INSERT INTO `lang_constants` VALUES (718, 'Superbill');
INSERT INTO `lang_constants` VALUES (719, 'Codes');
INSERT INTO `lang_constants` VALUES (720, 'Some codes were not displayed.');
INSERT INTO `lang_constants` VALUES (721, 'Clear Justification');
INSERT INTO `lang_constants` VALUES (722, 'Copay');
INSERT INTO `lang_constants` VALUES (723, 'Generated on');
INSERT INTO `lang_constants` VALUES (724, 'Date Of Service');
INSERT INTO `lang_constants` VALUES (725, 'History Data');
INSERT INTO `lang_constants` VALUES (726, 'Employer Data');
INSERT INTO `lang_constants` VALUES (727, 'Patient Immunization');
INSERT INTO `lang_constants` VALUES (728, 'Patient Transactions');
INSERT INTO `lang_constants` VALUES (729, 'Note');
INSERT INTO `lang_constants` VALUES (730, 'cannot be displayed inline becuase its type is not supported by the browser');
INSERT INTO `lang_constants` VALUES (731, 'Thank You');
INSERT INTO `lang_constants` VALUES (732, 'code type');
INSERT INTO `lang_constants` VALUES (733, 'Please pay this amount');
INSERT INTO `lang_constants` VALUES (734, 'This Encounter');
INSERT INTO `lang_constants` VALUES (735, 'Walt Pennington');
INSERT INTO `lang_constants` VALUES (736, 'Info test 1');
INSERT INTO `lang_constants` VALUES (737, 'Info test 2');
INSERT INTO `lang_constants` VALUES (738, 'Info test 3');
INSERT INTO `lang_constants` VALUES (739, 'Info test 4');
INSERT INTO `lang_constants` VALUES (740, 'Info test 5');
INSERT INTO `lang_constants` VALUES (741, 'Info test 6');
INSERT INTO `lang_constants` VALUES (742, 'Superbill Codes');
INSERT INTO `lang_constants` VALUES (743, 'Not all fields are required for all codes or code types.');
INSERT INTO `lang_constants` VALUES (744, 'Code Text');
INSERT INTO `lang_constants` VALUES (745, 'Modifier');
INSERT INTO `lang_constants` VALUES (746, 'Include in Superbill');
INSERT INTO `lang_constants` VALUES (747, 'Add Code');
INSERT INTO `lang_constants` VALUES (748, 'Prev 100');
INSERT INTO `lang_constants` VALUES (749, 'Next 100');
INSERT INTO `lang_constants` VALUES (750, 'Coding not authorized');
INSERT INTO `lang_constants` VALUES (751, 'Receipt');
INSERT INTO `lang_constants` VALUES (752, 'Patient Encounters');
INSERT INTO `lang_constants` VALUES (753, 'New Patient Encounter');
INSERT INTO `lang_constants` VALUES (754, 'Coding');
INSERT INTO `lang_constants` VALUES (755, 'Prescriptions');
INSERT INTO `lang_constants` VALUES (756, 'List Prescriptions');
INSERT INTO `lang_constants` VALUES (757, 'Add Prescription');
INSERT INTO `lang_constants` VALUES (758, 'You are not authorized for this.');
INSERT INTO `lang_constants` VALUES (759, 'Issues and Encounters');
INSERT INTO `lang_constants` VALUES (760, 'Issues and Encounters for');
INSERT INTO `lang_constants` VALUES (761, 'Issues Section');
INSERT INTO `lang_constants` VALUES (762, 'Encounters Section');
INSERT INTO `lang_constants` VALUES (763, 'Presenting Complaint');
INSERT INTO `lang_constants` VALUES (764, 'Instructions:');
INSERT INTO `lang_constants` VALUES (765, 'Cash');
INSERT INTO `lang_constants` VALUES (766, 'Check');
INSERT INTO `lang_constants` VALUES (767, 'MC');
INSERT INTO `lang_constants` VALUES (768, 'VISA');
INSERT INTO `lang_constants` VALUES (769, 'AMEX');
INSERT INTO `lang_constants` VALUES (770, 'DISC');
INSERT INTO `lang_constants` VALUES (771, 'Receipt for Payment');
INSERT INTO `lang_constants` VALUES (772, 'Paid Via');
INSERT INTO `lang_constants` VALUES (773, 'Check/Ref Number');
INSERT INTO `lang_constants` VALUES (774, 'Amount for This Visit');
INSERT INTO `lang_constants` VALUES (775, 'Amount for Past Balance');
INSERT INTO `lang_constants` VALUES (776, 'Received By');
INSERT INTO `lang_constants` VALUES (777, 'Print');
INSERT INTO `lang_constants` VALUES (778, 'Record Payment');
INSERT INTO `lang_constants` VALUES (779, 'Accept Payment for');
INSERT INTO `lang_constants` VALUES (780, 'Payment Method');
INSERT INTO `lang_constants` VALUES (781, 'Check/Reference Number');
INSERT INTO `lang_constants` VALUES (782, 'Amount for Todays Visit');
INSERT INTO `lang_constants` VALUES (783, 'Amount for Prior Balance');
INSERT INTO `lang_constants` VALUES (784, 'Logged in as');
INSERT INTO `lang_constants` VALUES (785, 'New Appointment');
INSERT INTO `lang_constants` VALUES (786, 'Patient Issues');
INSERT INTO `lang_constants` VALUES (787, 'Begin');
INSERT INTO `lang_constants` VALUES (788, 'End');
INSERT INTO `lang_constants` VALUES (789, 'Diag');
INSERT INTO `lang_constants` VALUES (790, 'Occurrence');
INSERT INTO `lang_constants` VALUES (791, 'Missed');
INSERT INTO `lang_constants` VALUES (792, 'RefBy');
INSERT INTO `lang_constants` VALUES (793, 'Enc');
INSERT INTO `lang_constants` VALUES (794, 'Patient Summary');
INSERT INTO `lang_constants` VALUES (795, 'Issue');
INSERT INTO `lang_constants` VALUES (796, 'Begin Date');
INSERT INTO `lang_constants` VALUES (797, 'yyyy-mm-dd date of onset, surgery or start of medication');
INSERT INTO `lang_constants` VALUES (798, 'yyyy-mm-dd date of recovery or end of medication');
INSERT INTO `lang_constants` VALUES (799, 'leave blank if still active');
INSERT INTO `lang_constants` VALUES (800, 'Active');
INSERT INTO `lang_constants` VALUES (801, 'Indicates if this issue is currently active');
INSERT INTO `lang_constants` VALUES (802, 'Returned to Play');
INSERT INTO `lang_constants` VALUES (803, 'yyyy-mm-dd date returned to play');
INSERT INTO `lang_constants` VALUES (804, 'Diagnosis must be coded into a linked encounter');
INSERT INTO `lang_constants` VALUES (805, 'Classification');
INSERT INTO `lang_constants` VALUES (806, 'Number of games or events missed, if any');
INSERT INTO `lang_constants` VALUES (807, 'games/events');
INSERT INTO `lang_constants` VALUES (808, 'Referred by');
INSERT INTO `lang_constants` VALUES (809, 'Referring physician and practice');
INSERT INTO `lang_constants` VALUES (810, 'Outcome');
INSERT INTO `lang_constants` VALUES (811, 'Destination');
INSERT INTO `lang_constants` VALUES (812, 'Demographics');
INSERT INTO `lang_constants` VALUES (813, 'Mrs');
INSERT INTO `lang_constants` VALUES (814, 'Ms');
INSERT INTO `lang_constants` VALUES (815, 'Mr');
INSERT INTO `lang_constants` VALUES (816, 'Dr');
INSERT INTO `lang_constants` VALUES (817, 'Number');
INSERT INTO `lang_constants` VALUES (818, 'Emergency Contact');
INSERT INTO `lang_constants` VALUES (819, 'Female');
INSERT INTO `lang_constants` VALUES (820, 'Male');
INSERT INTO `lang_constants` VALUES (821, 'Emergency Phone');
INSERT INTO `lang_constants` VALUES (822, 'S.S.');
INSERT INTO `lang_constants` VALUES (823, 'Mobile Phone');
INSERT INTO `lang_constants` VALUES (824, 'License/ID');
INSERT INTO `lang_constants` VALUES (825, 'Contact Email');
INSERT INTO `lang_constants` VALUES (826, 'Country');
INSERT INTO `lang_constants` VALUES (827, 'List Immediate Family Members');
INSERT INTO `lang_constants` VALUES (828, 'User Defined Fields');
INSERT INTO `lang_constants` VALUES (829, 'Marital Status');
INSERT INTO `lang_constants` VALUES (830, 'Unassigned');
INSERT INTO `lang_constants` VALUES (831, 'Pharmacy');
INSERT INTO `lang_constants` VALUES (832, 'Save Patient Demographics');
INSERT INTO `lang_constants` VALUES (833, 'HIPAA Choices');
INSERT INTO `lang_constants` VALUES (834, 'Did you receive a copy of the HIPAA Notice?');
INSERT INTO `lang_constants` VALUES (835, 'Allow Voice Msg');
INSERT INTO `lang_constants` VALUES (836, 'Allow Mail');
INSERT INTO `lang_constants` VALUES (837, 'Who may we leave a message with?');
INSERT INTO `lang_constants` VALUES (838, 'Occupation');
INSERT INTO `lang_constants` VALUES (839, 'Employer');
INSERT INTO `lang_constants` VALUES (840, 'if unemployed enter Student, PT Student, or leave blank');
INSERT INTO `lang_constants` VALUES (841, 'Employer Address');
INSERT INTO `lang_constants` VALUES (842, 'Language');
INSERT INTO `lang_constants` VALUES (843, 'Race/Ethnicity');
INSERT INTO `lang_constants` VALUES (844, 'Financial Review Date');
INSERT INTO `lang_constants` VALUES (845, 'Family Size');
INSERT INTO `lang_constants` VALUES (846, 'Monthly Income');
INSERT INTO `lang_constants` VALUES (847, 'Homeless, etc.');
INSERT INTO `lang_constants` VALUES (848, 'Interpreter');
INSERT INTO `lang_constants` VALUES (849, 'Migrant/Seasonal');
INSERT INTO `lang_constants` VALUES (850, 'Search/Add Insurer');
INSERT INTO `lang_constants` VALUES (851, 'Plan Name');
INSERT INTO `lang_constants` VALUES (852, 'Policy Number');
INSERT INTO `lang_constants` VALUES (853, 'Group Number');
INSERT INTO `lang_constants` VALUES (854, 'Subscriber Employer (SE)');
INSERT INTO `lang_constants` VALUES (855, 'if unemployed enter Student');
INSERT INTO `lang_constants` VALUES (856, 'SE Address');
INSERT INTO `lang_constants` VALUES (857, 'SE City');
INSERT INTO `lang_constants` VALUES (858, 'SE');
INSERT INTO `lang_constants` VALUES (859, 'SE Country');
INSERT INTO `lang_constants` VALUES (860, 'Subscriber');
INSERT INTO `lang_constants` VALUES (861, 'Relationship');
INSERT INTO `lang_constants` VALUES (862, 'Browse');
INSERT INTO `lang_constants` VALUES (863, 'D.O.B.');
INSERT INTO `lang_constants` VALUES (864, 'Subscriber Address');
INSERT INTO `lang_constants` VALUES (865, 'Subscriber Phone');
INSERT INTO `lang_constants` VALUES (866, 'Browse for Record');
INSERT INTO `lang_constants` VALUES (867, 'Copy Values');
INSERT INTO `lang_constants` VALUES (868, 'Insurance Provider');
INSERT INTO `lang_constants` VALUES (869, 'Primary');
INSERT INTO `lang_constants` VALUES (870, 'Secondary');
INSERT INTO `lang_constants` VALUES (871, 'Tertiary');
INSERT INTO `lang_constants` VALUES (872, 'Amend Existing Note');
INSERT INTO `lang_constants` VALUES (873, 'Append to This Note');
INSERT INTO `lang_constants` VALUES (874, 'View');
INSERT INTO `lang_constants` VALUES (875, 'Immunizations');
INSERT INTO `lang_constants` VALUES (876, 'Immunization');
INSERT INTO `lang_constants` VALUES (877, 'Date Administered');
INSERT INTO `lang_constants` VALUES (878, 'Immunization Manufacturer');
INSERT INTO `lang_constants` VALUES (879, 'Immunization Lot Number');
INSERT INTO `lang_constants` VALUES (880, 'Name and Title of Immunization Administrator');
INSERT INTO `lang_constants` VALUES (881, 'Date Immunization Information Statements Given');
INSERT INTO `lang_constants` VALUES (882, 'Print Shot Record');
INSERT INTO `lang_constants` VALUES (883, 'Save Immunization');
INSERT INTO `lang_constants` VALUES (884, 'Clear');
INSERT INTO `lang_constants` VALUES (885, 'Edit');
INSERT INTO `lang_constants` VALUES (886, 'Issues not authorized');
INSERT INTO `lang_constants` VALUES (887, 'Start');
INSERT INTO `lang_constants` VALUES (888, 'Return');
INSERT INTO `lang_constants` VALUES (889, 'Demographics not authorized');
INSERT INTO `lang_constants` VALUES (890, 'Email');
INSERT INTO `lang_constants` VALUES (891, 'Billing Note');
INSERT INTO `lang_constants` VALUES (892, 'Fitness to Play');
INSERT INTO `lang_constants` VALUES (893, 'Primary Insurance Provider');
INSERT INTO `lang_constants` VALUES (894, 'Secondary Insurance Provider');
INSERT INTO `lang_constants` VALUES (895, 'Tertiary Insurance Provider');
INSERT INTO `lang_constants` VALUES (896, 'Some notes were not displayed. Click here to view all');
INSERT INTO `lang_constants` VALUES (897, 'Transaction Type');
INSERT INTO `lang_constants` VALUES (898, 'Referral');
INSERT INTO `lang_constants` VALUES (899, 'Patient Request');
INSERT INTO `lang_constants` VALUES (900, 'Physician Request');
INSERT INTO `lang_constants` VALUES (901, 'Legal');
INSERT INTO `lang_constants` VALUES (902, 'Details');
INSERT INTO `lang_constants` VALUES (903, 'Add New Transaction');
INSERT INTO `lang_constants` VALUES (904, 'Patient Record Report');
INSERT INTO `lang_constants` VALUES (905, 'Normal View');
INSERT INTO `lang_constants` VALUES (906, 'Allergies');
INSERT INTO `lang_constants` VALUES (907, 'Medications');
INSERT INTO `lang_constants` VALUES (908, 'Medical Problems');
INSERT INTO `lang_constants` VALUES (909, 'Patient Comunication Sent');
INSERT INTO `lang_constants` VALUES (910, 'Forms');
INSERT INTO `lang_constants` VALUES (911, 'Patient Allergies');
INSERT INTO `lang_constants` VALUES (912, 'Patient Medications');
INSERT INTO `lang_constants` VALUES (913, 'Patient Medical Problems');
INSERT INTO `lang_constants` VALUES (914, 'cannot be displayed inline becuase its type is not supported by the browser.');
INSERT INTO `lang_constants` VALUES (915, 'cannot be converted to JPEG. Perhaps ImageMagick is not installed?');
INSERT INTO `lang_constants` VALUES (916, 'Signature');
INSERT INTO `lang_constants` VALUES (917, 'Patient Report');
INSERT INTO `lang_constants` VALUES (918, 'Printable Version');
INSERT INTO `lang_constants` VALUES (919, 'Patient Communication sent');
INSERT INTO `lang_constants` VALUES (920, 'cannot be displayed inline because its type is not supported by the browser.');
INSERT INTO `lang_constants` VALUES (921, 'View Comprehensive Patient Report');
INSERT INTO `lang_constants` VALUES (922, 'Generate Report');
INSERT INTO `lang_constants` VALUES (923, 'Issues to Include in this Report');
INSERT INTO `lang_constants` VALUES (924, 'Encounter Forms to Include in this Report');
INSERT INTO `lang_constants` VALUES (925, 'Documents');
INSERT INTO `lang_constants` VALUES (926, 'Summary');
INSERT INTO `lang_constants` VALUES (927, 'History');
INSERT INTO `lang_constants` VALUES (928, 'Transaction');
INSERT INTO `lang_constants` VALUES (929, 'Report');
INSERT INTO `lang_constants` VALUES (930, 'Patient History / Lifestyle');
INSERT INTO `lang_constants` VALUES (931, 'Family History');
INSERT INTO `lang_constants` VALUES (932, 'Relatives');
INSERT INTO `lang_constants` VALUES (933, 'Lifestyle');
INSERT INTO `lang_constants` VALUES (934, 'Date of Last');
INSERT INTO `lang_constants` VALUES (935, 'Additional History');
INSERT INTO `lang_constants` VALUES (936, 'Encounters not authorized');
INSERT INTO `lang_constants` VALUES (937, 'Past Encounters');
INSERT INTO `lang_constants` VALUES (938, 'Reason/Form');
INSERT INTO `lang_constants` VALUES (939, 'weight_loss_clinic');
INSERT INTO `lang_constants` VALUES (940, 'Some encounters were not displayed. Click here to view all.');
INSERT INTO `lang_constants` VALUES (941, 'No access');
INSERT INTO `lang_constants` VALUES (942, 'Reason');
INSERT INTO `lang_constants` VALUES (943, 'Father');
INSERT INTO `lang_constants` VALUES (944, 'Mother');
INSERT INTO `lang_constants` VALUES (945, 'Siblings');
INSERT INTO `lang_constants` VALUES (946, 'Spouse');
INSERT INTO `lang_constants` VALUES (947, 'Offspring');
INSERT INTO `lang_constants` VALUES (948, 'Cancer');
INSERT INTO `lang_constants` VALUES (949, 'Tuberculosis');
INSERT INTO `lang_constants` VALUES (950, 'Diabetes');
INSERT INTO `lang_constants` VALUES (951, 'High Blood Pressure');
INSERT INTO `lang_constants` VALUES (952, 'Heart Problems');
INSERT INTO `lang_constants` VALUES (953, 'Stroke');
INSERT INTO `lang_constants` VALUES (954, 'Epilepsy');
INSERT INTO `lang_constants` VALUES (955, 'Mental Illness');
INSERT INTO `lang_constants` VALUES (956, 'Suicide');
INSERT INTO `lang_constants` VALUES (957, 'Coffee');
INSERT INTO `lang_constants` VALUES (958, 'Tobacco');
INSERT INTO `lang_constants` VALUES (959, 'Alcohol');
INSERT INTO `lang_constants` VALUES (960, 'Sleep Patterns');
INSERT INTO `lang_constants` VALUES (961, 'Exercise Patterns');
INSERT INTO `lang_constants` VALUES (962, 'Seatbelt Use');
INSERT INTO `lang_constants` VALUES (963, 'Counseling');
INSERT INTO `lang_constants` VALUES (964, 'Hazardous Activities');
INSERT INTO `lang_constants` VALUES (965, 'Date/Notes of Last');
INSERT INTO `lang_constants` VALUES (966, 'Nor');
INSERT INTO `lang_constants` VALUES (967, 'Abn');
INSERT INTO `lang_constants` VALUES (968, '** Please move surgeries to Issues!');
INSERT INTO `lang_constants` VALUES (969, 'Patient History');
INSERT INTO `lang_constants` VALUES (970, '08 Cardiac Echo');
INSERT INTO `lang_constants` VALUES (971, '07 ECG');
INSERT INTO `lang_constants` VALUES (972, '05 Physical Exam');
INSERT INTO `lang_constants` VALUES (973, '00 Breast Exam');
INSERT INTO `lang_constants` VALUES (974, '01 Mammogram');
INSERT INTO `lang_constants` VALUES (975, '02 Gynecological Exam');
INSERT INTO `lang_constants` VALUES (976, '04 Prostate Exam');
INSERT INTO `lang_constants` VALUES (977, '03 Rectal Exam');
INSERT INTO `lang_constants` VALUES (978, '06 Sigmoid/Colonoscopy');
INSERT INTO `lang_constants` VALUES (979, 'Cataract Surgery');
INSERT INTO `lang_constants` VALUES (980, 'Tonsillectomy');
INSERT INTO `lang_constants` VALUES (981, 'Appendectomy');
INSERT INTO `lang_constants` VALUES (982, 'Cholecystestomy');
INSERT INTO `lang_constants` VALUES (983, 'Heart Surgery');
INSERT INTO `lang_constants` VALUES (984, 'Hysterectomy');
INSERT INTO `lang_constants` VALUES (985, 'Hernia Repair');
INSERT INTO `lang_constants` VALUES (986, 'Hip Replacement');
INSERT INTO `lang_constants` VALUES (987, 'Knee Replacement');
INSERT INTO `lang_constants` VALUES (988, 'Delete Patient, Encounter, Form, Issue or Document');
INSERT INTO `lang_constants` VALUES (989, 'and all subordinate data? This action will be logged');
INSERT INTO `lang_constants` VALUES (990, 'Balance Due');
INSERT INTO `lang_constants` VALUES (991, 'Patient Checkout');
INSERT INTO `lang_constants` VALUES (992, 'Patient Checkout for');
INSERT INTO `lang_constants` VALUES (993, 'Amount Paid');
INSERT INTO `lang_constants` VALUES (994, 'Posting Date');
INSERT INTO `lang_constants` VALUES (995, 'Lab');
INSERT INTO `lang_constants` VALUES (996, 'Therapeutic Injections');
INSERT INTO `lang_constants` VALUES (997, 'Receipts for Medical Services');
INSERT INTO `lang_constants` VALUES (998, 'Cash Receipts');
INSERT INTO `lang_constants` VALUES (999, 'InvAmt');
INSERT INTO `lang_constants` VALUES (1000, 'Prof.');
INSERT INTO `lang_constants` VALUES (1001, 'Clinic');
INSERT INTO `lang_constants` VALUES (1002, 'Totals for');
INSERT INTO `lang_constants` VALUES (1003, 'Grand Totals');
INSERT INTO `lang_constants` VALUES (1004, 'EOB Posting - Patient Note');
INSERT INTO `lang_constants` VALUES (1005, 'Billing Note for');
INSERT INTO `lang_constants` VALUES (1006, 'Billing queue results:');
INSERT INTO `lang_constants` VALUES (1007, 'EOB Posting - Instructions');
INSERT INTO `lang_constants` VALUES (1008, 'EOB Data Entry');
INSERT INTO `lang_constants` VALUES (1009, 'This module promotes efficient entry of EOB data.');
INSERT INTO `lang_constants` VALUES (1010, 'After the information is correctly entered, click the Save button.');
INSERT INTO `lang_constants` VALUES (1011, 'Request ignored - claims processing is already running!');
INSERT INTO `lang_constants` VALUES (1012, 'Batch processing initiated; this may take a while.');
INSERT INTO `lang_constants` VALUES (1013, 'Billing Report');
INSERT INTO `lang_constants` VALUES (1014, '[Change View]');
INSERT INTO `lang_constants` VALUES (1015, '[Export OFX]');
INSERT INTO `lang_constants` VALUES (1016, '[View Printable Report]');
INSERT INTO `lang_constants` VALUES (1017, '[Reports]');
INSERT INTO `lang_constants` VALUES (1018, '[EOBs]');
INSERT INTO `lang_constants` VALUES (1019, '[Start Batch Processing]');
INSERT INTO `lang_constants` VALUES (1020, '[view log]');
INSERT INTO `lang_constants` VALUES (1021, '[Select All]');
INSERT INTO `lang_constants` VALUES (1022, 'Run Test');
INSERT INTO `lang_constants` VALUES (1023, 'process_date');
INSERT INTO `lang_constants` VALUES (1024, 'key');
INSERT INTO `lang_constants` VALUES (1025, 'Printing results:');
INSERT INTO `lang_constants` VALUES (1026, 'X-ray not taken within the past 12 months or near enough to the start of treatment.');
INSERT INTO `lang_constants` VALUES (1027, 'Not paid separately when the patient is an inpatient.');
INSERT INTO `lang_constants` VALUES (1028, 'Equipment is the same or similar to equipment already being used.');
INSERT INTO `lang_constants` VALUES (1029, 'This is the last monthly installment payment for this durable medical equipment.');
INSERT INTO `lang_constants` VALUES (1030, 'Monthly rental payments can continue until the earlier of the 15th month from the first rental month, or the month when the equipment is no longer needed.');
INSERT INTO `lang_constants` VALUES (1031, 'You must furnish and service this item for as long as the patient continues to need it. We can pay for maintenance and/or servicing for every 6 month period after the end of the 15th paid rental month or the end of the warranty period.');
INSERT INTO `lang_constants` VALUES (1032, 'No rental payments after the item is purchased, or after the total of issued rental payments equals the purchase price.');
INSERT INTO `lang_constants` VALUES (1033, 'We do not accept blood gas tests results when the test was conducted by a medical supplier or taken while the patient is on oxygen.');
INSERT INTO `lang_constants` VALUES (1034, 'This is the tenth rental month. You must offer the patient the choice of changing the rental to a purchase agreement.');
INSERT INTO `lang_constants` VALUES (1035, 'Equipment purchases are limited to the first or the tenth month of medical necessity.');
INSERT INTO `lang_constants` VALUES (1036, 'DME, orthotics and prosthetics must be billed to the DME carrier who services the patient''s zip code.');
INSERT INTO `lang_constants` VALUES (1037, 'Diagnostic tests performed by a physician must indicate whether purchased services are included on the claim.');
INSERT INTO `lang_constants` VALUES (1038, 'Only one initial visit is covered per specialty per medical group.');
INSERT INTO `lang_constants` VALUES (1039, 'No separate payment for an injection administered during an office visit, and no payment for a full office visit if the patient only received an injection.');
INSERT INTO `lang_constants` VALUES (1040, 'Separately billed services/tests have been bundled as they are considered components of the same procedure. Separate payment is not allowed.');
INSERT INTO `lang_constants` VALUES (1041, 'Please see our web site, mailings, or bulletins for more details concerning this policy/procedure/decision.');
INSERT INTO `lang_constants` VALUES (1042, 'Payment approved as you did not know, and could not reasonably have been expected to know, that this would not normally have been covered for this patient. In the future, you will be liable for charges for the same service(s) under the same or similar con');
INSERT INTO `lang_constants` VALUES (1043, 'Certain services may be approved for home use. Neither a hospital nor a Skilled Nursing Facility (SNF) is considered to be a patient''s home.');
INSERT INTO `lang_constants` VALUES (1044, 'Missing oxygen certification/re-certification.');
INSERT INTO `lang_constants` VALUES (1045, 'Missing/incomplete/invalid HCPCS.');
INSERT INTO `lang_constants` VALUES (1046, 'Missing/incomplete/invalid place of residence for this service/item provided in a home.');
INSERT INTO `lang_constants` VALUES (1047, 'Missing/incomplete/invalid number of miles traveled.');
INSERT INTO `lang_constants` VALUES (1048, 'Missing invoice.');
INSERT INTO `lang_constants` VALUES (1049, 'Missing/incomplete/invalid number of doses per vial.');
INSERT INTO `lang_constants` VALUES (1050, 'Payment has been adjusted because the information furnished does not substantiate the need for this level of service. If you believe the service should have been fully covered as billed, or if you did not know and could not reasonably have been expected t');
INSERT INTO `lang_constants` VALUES (1051, 'Payment has been adjusted because the information furnished does not substantiate the need for this level of service. If you have collected any amount from the patient for this level of service /any amount that exceeds the limiting charge for the less ext');
INSERT INTO `lang_constants` VALUES (1052, 'The patient has been relieved of liability of payment of these items and services under the limitation of liability provision of the law. You, the provider, are ultimately liable for the patient''s waived charges, including any charges for coinsurance, sin');
INSERT INTO `lang_constants` VALUES (1053, 'This does not qualify for payment under Part B when Part A coverage is exhausted or not otherwise available.');
INSERT INTO `lang_constants` VALUES (1054, 'Missing operative report.');
INSERT INTO `lang_constants` VALUES (1055, 'Missing pathology report.');
INSERT INTO `lang_constants` VALUES (1056, 'Missing radiology report.');
INSERT INTO `lang_constants` VALUES (1057, 'This is a conditional payment made pending a decision on this service by the patient''s primary payer. This payment may be subject to refund upon your receipt of any additional payment for this service from another payer. You must contact this office immed');
INSERT INTO `lang_constants` VALUES (1058, 'This is the 11th rental month. We cannot pay for this until you indicate that the patient has been given the option of changing the rental to a purchase.');
INSERT INTO `lang_constants` VALUES (1059, 'Service not covered when the patient is under age 35.');
INSERT INTO `lang_constants` VALUES (1060, 'The patient is liable for the charges for this service as you informed the patient in writing before the service was furnished that we would not pay for it, and the patient agreed to pay.');
INSERT INTO `lang_constants` VALUES (1061, 'The patient is not liable for payment for this service as the advance notice of non-coverage you provided the patient did not comply with program requirements.');
INSERT INTO `lang_constants` VALUES (1062, 'Claim must be assigned and must be filed by the practitioner''s employer.');
INSERT INTO `lang_constants` VALUES (1063, 'We do not pay for this as the patient has no legal obligation to pay for this.');
INSERT INTO `lang_constants` VALUES (1064, 'The medical necessity form must be personally signed by the attending physician.');
INSERT INTO `lang_constants` VALUES (1065, 'Missing/incomplete/invalid condition code.');
INSERT INTO `lang_constants` VALUES (1066, 'Missing/incomplete/invalid occurrence code(s).');
INSERT INTO `lang_constants` VALUES (1067, 'Missing/incomplete/invalid occurrence span code(s).');
INSERT INTO `lang_constants` VALUES (1068, 'Missing/incomplete/invalid internal or document control number.');
INSERT INTO `lang_constants` VALUES (1069, 'Missing/incomplete/invalid value code(s) or amount(s).');
INSERT INTO `lang_constants` VALUES (1070, 'Missing/incomplete/invalid revenue code(s).');
INSERT INTO `lang_constants` VALUES (1071, 'Missing/incomplete/invalid procedure code(s).');
INSERT INTO `lang_constants` VALUES (1072, 'Missing/incomplete/invalid from date(s) of service.');
INSERT INTO `lang_constants` VALUES (1073, 'Missing/incomplete/invalid days or units of service.');
INSERT INTO `lang_constants` VALUES (1074, 'Missing/incomplete/invalid total charges.');
INSERT INTO `lang_constants` VALUES (1075, 'We do not pay for self-administered anti-emetic drugs that are not administered with a covered oral anti-cancer drug.');
INSERT INTO `lang_constants` VALUES (1076, 'Missing/incomplete/invalid payer identifier.');
INSERT INTO `lang_constants` VALUES (1077, 'Missing/incomplete/invalid to date(s) of service.');
INSERT INTO `lang_constants` VALUES (1078, 'Missing Certificate of Medical Necessity.');
INSERT INTO `lang_constants` VALUES (1079, 'We cannot pay for this as the approval period for the FDA clinical trial has expired.');
INSERT INTO `lang_constants` VALUES (1080, 'Missing/incomplete/invalid treatment authorization code.');
INSERT INTO `lang_constants` VALUES (1081, 'Missing/incomplete/invalid other diagnosis.');
INSERT INTO `lang_constants` VALUES (1082, 'One interpreting physician charge can be submitted per claim when a purchased diagnostic test is indicated. Please submit a separate claim for each interpreting physician.');
INSERT INTO `lang_constants` VALUES (1083, 'Our records indicate that you billed diagnostic tests subject to price limitations and the procedure code submitted includes a professional component. Only the technical component is subject to price limitations. Please submit the technical and profession');
INSERT INTO `lang_constants` VALUES (1084, 'Missing/incomplete/invalid other procedure code(s).');
INSERT INTO `lang_constants` VALUES (1085, 'Paid at the regular rate as you did not submit documentation to justify the modified procedure code.');
INSERT INTO `lang_constants` VALUES (1086, 'NDC code submitted for this service was translated to a HCPCS code for processing, but please continue to submit the NDC on future claims for this item.');
INSERT INTO `lang_constants` VALUES (1087, 'Total payment reduced due to overlap of tests billed.');
INSERT INTO `lang_constants` VALUES (1088, 'The HPSA/Physician Scarcity bonus can only be paid on the professional component of this service. Rebill as separate professional and technical components.');
INSERT INTO `lang_constants` VALUES (1089, 'This service does not qualify for a HPSA/Physician Scarcity bonus payment.');
INSERT INTO `lang_constants` VALUES (1090, 'Allowed amount adjusted. Multiple automated multichannel tests performed on the same day combined for payment.');
INSERT INTO `lang_constants` VALUES (1091, 'Missing/incomplete/invalid diagnosis or condition.');
INSERT INTO `lang_constants` VALUES (1092, 'Missing/incomplete/invalid place of service.');
INSERT INTO `lang_constants` VALUES (1093, 'Missing/incomplete/invalid charge.');
INSERT INTO `lang_constants` VALUES (1094, 'Not covered when performed during the same session/date as a previously processed service for the patient.');
INSERT INTO `lang_constants` VALUES (1095, 'You are required to code to the highest level of specificity.');
INSERT INTO `lang_constants` VALUES (1096, 'Service is not covered when patient is under age 50.');
INSERT INTO `lang_constants` VALUES (1097, 'Service is not covered unless the patient is classified as at high risk.');
INSERT INTO `lang_constants` VALUES (1098, 'Medical code sets used must be the codes in effect at the time of service');
INSERT INTO `lang_constants` VALUES (1099, 'Subjected to review of physician evaluation and management services.');
INSERT INTO `lang_constants` VALUES (1100, 'Service denied because payment already made for same/similar procedure within set time frame.');
INSERT INTO `lang_constants` VALUES (1101, 'Claim/service(s) subjected to CFO-CAP prepayment review.');
INSERT INTO `lang_constants` VALUES (1102, 'Not covered more than once under age 40.');
INSERT INTO `lang_constants` VALUES (1103, 'Not covered more than once in a 12 month period.');
INSERT INTO `lang_constants` VALUES (1104, 'Lab procedures with different CLIA certification numbers must be billed on separate claims.');
INSERT INTO `lang_constants` VALUES (1105, 'Information supplied supports a break in therapy. A new capped rental period began with delivery of this equipment.');
INSERT INTO `lang_constants` VALUES (1106, 'Information supplied does not support a break in therapy. A new capped rental period will not begin.');
INSERT INTO `lang_constants` VALUES (1107, 'Services subjected to Home Health Initiative medical review/cost report audit.');
INSERT INTO `lang_constants` VALUES (1108, 'The technical component of a service furnished to an inpatient may only be billed by that inpatient facility. You must contact the inpatient facility for technical component reimbursement. If not already billed, you should bill us for the professional com');
INSERT INTO `lang_constants` VALUES (1109, 'Not paid to practitioner when provided to patient in this place of service. Payment included in the reimbursement issued the facility.');
INSERT INTO `lang_constants` VALUES (1110, 'Missing/incomplete/invalid Universal Product Number/Serial Number.');
INSERT INTO `lang_constants` VALUES (1111, 'We do not pay for an oral anti-emetic drug that is not administered for use immediately before, at, or within 48 hours of administration of a covered chemotherapy drug.');
INSERT INTO `lang_constants` VALUES (1112, 'Service not performed on equipment approved by the FDA for this purpose.');
INSERT INTO `lang_constants` VALUES (1113, 'Information supplied supports a break in therapy. However, the medical information we have for this patient does not support the need for this item as billed. We have approved payment for this item at a reduced level, and a new capped rental period will b');
INSERT INTO `lang_constants` VALUES (1114, 'Information supplied supports a break in therapy. A new capped rental period will begin with delivery of the equipment. This is the maximum approved under the fee schedule for this item or service.');
INSERT INTO `lang_constants` VALUES (1115, 'Information supplied does not support a break in therapy. The medical information we have for this patient does not support the need for this item as billed. We have approved payment for this item at a reduced level, and a new capped rental period will no');
INSERT INTO `lang_constants` VALUES (1116, 'Payment reduced as 90-day rolling average hematocrit for ESRD patient exceeded 36.5%.');
INSERT INTO `lang_constants` VALUES (1117, 'We have provided you with a bundled payment for a teleconsultation. You must send 25 percent of the teleconsultation payment to the referring practitioner.');
INSERT INTO `lang_constants` VALUES (1118, 'We do not pay for chiropractic manipulative treatment when the patient refuses to have an x-ray taken.');
INSERT INTO `lang_constants` VALUES (1119, 'The approved amount is based on the maximum allowance for this item under the DMEPOS Competitive Bidding Demonstration.');
INSERT INTO `lang_constants` VALUES (1120, 'Our records indicate that this patient began using this service(s) prior to the current round of the DMEPOS Competitive Bidding Demonstration. Therefore, the approved amount is based on the allowance in effect prior to this round of bidding for this item.');
INSERT INTO `lang_constants` VALUES (1121, 'This service was processed in accordance with rules and guidelines under the Competitive Bidding Demonstration Project. If you would like more information regarding this project, you may phone 1-888-289-0710.');
INSERT INTO `lang_constants` VALUES (1122, 'This item is denied when provided to this patient by a non-demonstration supplier.');
INSERT INTO `lang_constants` VALUES (1123, 'Paid under the Competitive Bidding Demonstration project. Project is ending, and future services may not be paid under this project.');
INSERT INTO `lang_constants` VALUES (1124, 'Not covered unless submitted via electronic claim.');
INSERT INTO `lang_constants` VALUES (1125, 'Letter to follow containing further information.');
INSERT INTO `lang_constants` VALUES (1126, 'Missing/incomplete/invalid/ deactivated/withdrawn National Drug Code (NDC).');
INSERT INTO `lang_constants` VALUES (1127, 'We pay for this service only when performed with a covered cryosurgical ablation.');
INSERT INTO `lang_constants` VALUES (1128, 'Missing/incomplete/invalid level of subluxation.');
INSERT INTO `lang_constants` VALUES (1129, 'Missing/incomplete/invalid name, strength, or dosage of the drug furnished.');
INSERT INTO `lang_constants` VALUES (1130, 'Missing indication of whether the patient owns the equipment that requires the part or supply.');
INSERT INTO `lang_constants` VALUES (1131, 'Missing/incomplete/invalid information on the period of time for which the service/supply/equipment will be needed.');
INSERT INTO `lang_constants` VALUES (1132, 'Missing/incomplete/invalid individual lab codes included in the test.');
INSERT INTO `lang_constants` VALUES (1133, 'Missing patient medical record for this service.');
INSERT INTO `lang_constants` VALUES (1134, 'Missing/incomplete/invalid indicator of x-ray availability for review.');
INSERT INTO `lang_constants` VALUES (1135, 'Missing invoice or statement certifying the actual cost of the lens, less discounts, and/or the type of intraocular lens used.');
INSERT INTO `lang_constants` VALUES (1136, 'Missing physician financial relationship form.');
INSERT INTO `lang_constants` VALUES (1137, 'Missing pacemaker registration form.');
INSERT INTO `lang_constants` VALUES (1138, 'Claim did not identify who performed the purchased diagnostic test or the amount you were charged for the test.');
INSERT INTO `lang_constants` VALUES (1139, 'Performed by a facility/supplier in which the provider has a financial interest.');
INSERT INTO `lang_constants` VALUES (1140, 'Missing/incomplete/invalid plan of treatment.');
INSERT INTO `lang_constants` VALUES (1141, 'Missing/incomplete/invalid indication that the service was supervised or evaluated by a physician.');
INSERT INTO `lang_constants` VALUES (1142, 'Part B coinsurance under a demonstration project.');
INSERT INTO `lang_constants` VALUES (1143, 'Patient identified as a demonstration participant but the patient was not enrolled in the demonstration at the time services were rendered. Coverage is limited to demonstration participants.');
INSERT INTO `lang_constants` VALUES (1144, 'Denied services exceed the coverage limit for the demonstration.');
INSERT INTO `lang_constants` VALUES (1145, 'Missing physician certified plan of care.');
INSERT INTO `lang_constants` VALUES (1146, 'Missing American Diabetes Association Certificate of Recognition.');
INSERT INTO `lang_constants` VALUES (1147, 'We have no record that you are licensed to dispensed drugs in the State where located.');
INSERT INTO `lang_constants` VALUES (1148, 'Pre-/post-operative care payment is included in the allowance for the surgery/procedure.');
INSERT INTO `lang_constants` VALUES (1149, 'If you do not agree with what we approved for these services, you may appeal our decision. To make sure that we are fair to you, we require another individual that did not process your initial claim to conduct the appeal. However, in order to be eligible');
INSERT INTO `lang_constants` VALUES (1150, 'If you do not agree with this determination, you have the right to appeal. You must file a written request for an appeal within 180 days of the date you receive this notice. Decisions made by a Quality Improvement Organization (QIO) must be appealed to th');
INSERT INTO `lang_constants` VALUES (1151, 'If you do not agree with the approved amounts and $100 or more is in dispute (less deductible and coinsurance), you may ask for a hearing within six months of the date of this notice. To meet the $100, you may combine amounts on other claims that have bee');
INSERT INTO `lang_constants` VALUES (1152, 'Secondary payment cannot be considered without the identity of or payment information from the primary payer. The information was either not reported or was illegible.');
INSERT INTO `lang_constants` VALUES (1153, 'The claim information has also been forwarded to Medicaid for review.');
INSERT INTO `lang_constants` VALUES (1154, 'You should also submit this claim to the patient''s other insurer for potential payment of supplemental benefits. We did not forward the claim information as the supplemental coverage is not with a Medigap plan, or you do not participate in Medicare.');
INSERT INTO `lang_constants` VALUES (1155, 'Claim submitted as unassigned but processed as assigned. You agreed to accept assignment for all claims.');
INSERT INTO `lang_constants` VALUES (1156, 'The patient''s payment was in excess of the amount owed. You must refund the overpayment to the patient.');
INSERT INTO `lang_constants` VALUES (1157, 'You have not established that you have the right under the law to bill for services furnished by the person(s) that furnished this (these) service(s).');
INSERT INTO `lang_constants` VALUES (1158, 'You may be subject to penalties if you bill the patient for amounts not reported with the PR (patient responsibility) group code.');
INSERT INTO `lang_constants` VALUES (1159, 'Patient is a member of an employer-sponsored prepaid health plan. Services from outside that health plan are not covered. However, as you were not previously notified of this, we are paying this time. In the future, we will not pay you for non-plan servic');
INSERT INTO `lang_constants` VALUES (1160, 'Your claim has been separated to expedite handling. You will receive a separate notice for the other services reported.');
INSERT INTO `lang_constants` VALUES (1161, 'The patient is covered by the Black Lung Program. Send this claim to the Department of Labor, Federal Black Lung Program, P.O. Box 828, Lanham-Seabrook MD 20703.');
INSERT INTO `lang_constants` VALUES (1162, 'We are the primary payer and have paid at the primary rate. You must contact the patient''s other insurer to refund any excess it may have paid due to its erroneous primary payment.');
INSERT INTO `lang_constants` VALUES (1163, 'The claim information is also being forwarded to the patient''s supplemental insurer. Send any questions regarding supplemental benefits to them.');
INSERT INTO `lang_constants` VALUES (1164, 'Information was not sent to the Medigap insurer due to incorrect/invalid information you submitted concerning that insurer. Please verify your information and submit your secondary claim directly to that insurer.');
INSERT INTO `lang_constants` VALUES (1165, 'Skilled Nursing Facility (SNF) stay not covered when care is primarily related to the use of an urethral catheter for convenience or the control of incontinence.');
INSERT INTO `lang_constants` VALUES (1166, 'SSA records indicate mismatch with name and sex.');
INSERT INTO `lang_constants` VALUES (1167, 'Payment of less than $1.00 suppressed.');
INSERT INTO `lang_constants` VALUES (1168, 'Demand bill approved as result of medical review.');
INSERT INTO `lang_constants` VALUES (1169, 'Christian Science Sanitarium/ Skilled Nursing Facility (SNF) bill in the same benefit period.');
INSERT INTO `lang_constants` VALUES (1170, 'A patient may not elect to change a hospice provider more than once in a benefit period.');
INSERT INTO `lang_constants` VALUES (1171, 'Our records indicate that you were previously informed of this rule.');
INSERT INTO `lang_constants` VALUES (1172, 'Missing/incomplete/invalid entitlement number or name shown on the claim.');
INSERT INTO `lang_constants` VALUES (1173, 'Receipt of this notice by a physician or supplier who did not accept assignment is for information only and does not make the physician or supplier a party to the determination. No additional rights to appeal this decision, above those rights already prov');
INSERT INTO `lang_constants` VALUES (1174, 'Missing/incomplete/invalid type of bill.');
INSERT INTO `lang_constants` VALUES (1175, 'Missing/incomplete/invalid beginning and ending dates of the period billed.');
INSERT INTO `lang_constants` VALUES (1176, 'Missing/incomplete/invalid number of covered days during the billing period.');
INSERT INTO `lang_constants` VALUES (1177, 'Missing/incomplete/invalid noncovered days during the billing period.');
INSERT INTO `lang_constants` VALUES (1178, 'Missing/incomplete/invalid number of coinsurance days during the billing period.');
INSERT INTO `lang_constants` VALUES (1179, 'Missing/incomplete/invalid number of lifetime reserve days.');
INSERT INTO `lang_constants` VALUES (1180, 'Missing/incomplete/invalid patient name.');
INSERT INTO `lang_constants` VALUES (1181, 'Missing/incomplete/invalid patient''s address.');
INSERT INTO `lang_constants` VALUES (1182, 'Missing/incomplete/invalid gender.');
INSERT INTO `lang_constants` VALUES (1183, 'Missing/incomplete/invalid admission date.');
INSERT INTO `lang_constants` VALUES (1184, 'Missing/incomplete/invalid admission type.');
INSERT INTO `lang_constants` VALUES (1185, 'Missing/incomplete/invalid admission source.');
INSERT INTO `lang_constants` VALUES (1186, 'Missing/incomplete/invalid patient status.');
INSERT INTO `lang_constants` VALUES (1187, 'No appeal rights. Adjudicative decision based on law.');
INSERT INTO `lang_constants` VALUES (1188, 'As previously advised, a portion or all of your payment is being held in a special account.');
INSERT INTO `lang_constants` VALUES (1189, 'The new information was considered, however, additional payment cannot be issued. Please review the information listed for the explanation.');
INSERT INTO `lang_constants` VALUES (1190, 'Our records show you have opted out of Medicare, agreeing with the patient not to bill Medicare for services/tests/supplies furnished. As result, we cannot pay this claim. The patient is responsible for payment.');
INSERT INTO `lang_constants` VALUES (1191, 'Missing/incomplete/invalid name or address of responsible party or primary payer.');
INSERT INTO `lang_constants` VALUES (1192, 'Missing/incomplete/invalid Investigational Device Exemption number for FDA-approved clinical trial services.');
INSERT INTO `lang_constants` VALUES (1193, 'Missing/incomplete/invalid Competitive Bidding Demonstration Project identification.');
INSERT INTO `lang_constants` VALUES (1194, 'Physician certification or election consent for hospice care not received timely.');
INSERT INTO `lang_constants` VALUES (1195, 'Not covered as patient received medical health care services, automatically revoking his/her election to receive religious non-medical health care services.');
INSERT INTO `lang_constants` VALUES (1196, 'Our records show you have opted out of Medicare, agreeing with the patient not to bill Medicare for services/tests/supplies furnished. As result, we cannot pay this claim. The patient is responsible for payment, but under Federal law, you cannot charge th');
INSERT INTO `lang_constants` VALUES (1197, 'Patient submitted written request to revoke his/her election for religious non-medical health care services.');
INSERT INTO `lang_constants` VALUES (1198, 'Missing/incomplete/invalid release of information indicator.');
INSERT INTO `lang_constants` VALUES (1199, 'The patient overpaid you for these services. You must issue the patient a refund within 30 days for the difference between his/her payment and the total amount shown as patient responsibility on this notice.');
INSERT INTO `lang_constants` VALUES (1200, 'Missing/incomplete/invalid patient relationship to insured.');
INSERT INTO `lang_constants` VALUES (1201, 'Missing/incomplete/invalid social security number or health insurance claim number.');
INSERT INTO `lang_constants` VALUES (1202, 'Telephone review decision.');
INSERT INTO `lang_constants` VALUES (1203, 'Missing/incomplete/invalid principal diagnosis.');
INSERT INTO `lang_constants` VALUES (1204, 'Our records indicate that we should be the third payer for this claim. We cannot process this claim until we have received payment information from the primary and secondary payers.');
INSERT INTO `lang_constants` VALUES (1205, 'Missing/incomplete/invalid admitting diagnosis.');
INSERT INTO `lang_constants` VALUES (1206, 'Missing/incomplete/invalid principal procedure code.');
INSERT INTO `lang_constants` VALUES (1207, 'Correction to a prior claim.');
INSERT INTO `lang_constants` VALUES (1208, 'We did not crossover this claim because the secondary insurance information on the claim was incomplete. Please supply complete information or use the PLANID of the insurer to assure correct and timely routing of the claim.');
INSERT INTO `lang_constants` VALUES (1209, 'Missing/incomplete/invalid remarks.');
INSERT INTO `lang_constants` VALUES (1210, 'Missing/incomplete/invalid provider representative signature.');
INSERT INTO `lang_constants` VALUES (1211, 'Missing/incomplete/invalid provider representative signature date.');
INSERT INTO `lang_constants` VALUES (1212, 'The patient overpaid you for these assigned services. You must issue the patient a refund within 30 days for the difference between his/her payment to you and the total of the amount shown as patient responsibility and as paid to the patient on this notic');
INSERT INTO `lang_constants` VALUES (1213, 'Informational remittance associated with a Medicare demonstration. No payment issued under fee-for-service Medicare as patient has elected managed care.');
INSERT INTO `lang_constants` VALUES (1214, 'This payment replaces an earlier payment for this claim that was either lost, damaged or returned.');
INSERT INTO `lang_constants` VALUES (1215, 'Missing/incomplete/invalid patient or authorized representative signature.');
INSERT INTO `lang_constants` VALUES (1216, 'Missing/incomplete/invalid provider identifier for home health agency or hospice when physician is performing care plan oversight services.');
INSERT INTO `lang_constants` VALUES (1217, 'The patient overpaid you. You must issue the patient a refund within 30 days for the difference between the patient''s payment less the total of our and other payer payments and the amount shown as patient responsibility on this notice.');
INSERT INTO `lang_constants` VALUES (1218, 'Billed in excess of interim rate.');
INSERT INTO `lang_constants` VALUES (1219, 'Informational notice. No payment issued for this claim with this notice. Payment issued to the hospital by its intermediary for all services for this encounter under a demonstration project.');
INSERT INTO `lang_constants` VALUES (1220, 'Missing/incomplete/invalid provider/supplier signature.');
INSERT INTO `lang_constants` VALUES (1221, 'Did not indicate whether we are the primary or secondary payer.');
INSERT INTO `lang_constants` VALUES (1222, 'Patient identified as participating in the National Emphysema Treatment Trial but our records indicate that this patient is either not a participant, or has not yet been approved for this phase of the study. Contact Johns Hopkins University, the study coo');
INSERT INTO `lang_constants` VALUES (1223, 'Missing/incomplete/invalid insured''s address and/or telephone number for the primary payer.');
INSERT INTO `lang_constants` VALUES (1224, 'Missing/incomplete/invalid patient''s relationship to the insured for the primary payer.');
INSERT INTO `lang_constants` VALUES (1225, 'Missing/incomplete/invalid employment status code for the primary insured.');
INSERT INTO `lang_constants` VALUES (1226, 'This determination is the result of the appeal you filed.');
INSERT INTO `lang_constants` VALUES (1227, 'Missing plan information for other insurance.');
INSERT INTO `lang_constants` VALUES (1228, 'Non-PIP (Periodic Interim Payment) claim.');
INSERT INTO `lang_constants` VALUES (1229, 'Did not enter the statement ?Attending physician not hospice employee on the claim form to certify that the rendering physician is not an employee of the hospice.');
INSERT INTO `lang_constants` VALUES (1230, 'De-activate and refer to M51.');
INSERT INTO `lang_constants` VALUES (1231, 'Claim rejected. Coded as a Medicare Managed Care Demonstration but patient is not enrolled in a Medicare managed care plan.');
INSERT INTO `lang_constants` VALUES (1232, 'Missing/incomplete/invalid Medicare Managed Care Demonstration contract number.');
INSERT INTO `lang_constants` VALUES (1233, 'Missing/incomplete/invalid Medigap information.');
INSERT INTO `lang_constants` VALUES (1234, 'Missing/incomplete/invalid date of current illness or symptoms');
INSERT INTO `lang_constants` VALUES (1235, 'A Skilled Nursing Facility (SNF) is responsible for payment of outside providers who furnish these services/supplies to residents.');
INSERT INTO `lang_constants` VALUES (1236, 'Hemophilia Add On.');
INSERT INTO `lang_constants` VALUES (1237, 'PIP (Periodic Interim Payment) claim.');
INSERT INTO `lang_constants` VALUES (1238, 'Paper claim contains more than three separate data items in field 19.');
INSERT INTO `lang_constants` VALUES (1239, 'Paper claim contains more than one data item in field 23.');
INSERT INTO `lang_constants` VALUES (1240, 'Claim processed in accordance with ambulatory surgical guidelines.');
INSERT INTO `lang_constants` VALUES (1241, 'Missing/incomplete/invalid information on whether the diagnostic test(s) were performed by an outside entity or if no purchased tests are included on the claim.');
INSERT INTO `lang_constants` VALUES (1242, 'Missing/incomplete/invalid purchase price of the test(s) and/or the performing laboratory''s name and address.');
INSERT INTO `lang_constants` VALUES (1243, 'Missing/incomplete/invalid group practice information.');
INSERT INTO `lang_constants` VALUES (1244, 'Incomplete/invalid taxpayer identification number (TIN) submitted by you per the Internal Revenue Service. Your claims cannot be processed without your correct TIN, and you may not bill the patient pending correction of your TIN. There are no appeal right');
INSERT INTO `lang_constants` VALUES (1245, 'Missing/incomplete/invalid information on where the services were furnished.');
INSERT INTO `lang_constants` VALUES (1246, 'Missing/incomplete/invalid physical location (name and address, or PIN) where the service(s) were rendered in a Health Professional Shortage Area (HPSA).');
INSERT INTO `lang_constants` VALUES (1247, 'Did not complete the statement "Homebound" on the claim to validate whether laboratory services were performed at home or in an institution.');
INSERT INTO `lang_constants` VALUES (1248, 'This claim has been assessed a $1.00 user fee.');
INSERT INTO `lang_constants` VALUES (1249, 'Coinsurance and/or deductible amounts apply to a claim for services or supplies furnished to a Medicare-eligible veteran through a facility of the Department of Veterans Affairs. No Medicare payment issued.');
INSERT INTO `lang_constants` VALUES (1250, 'Provider level adjustment for late claim filing applies to this claim.');
INSERT INTO `lang_constants` VALUES (1251, 'Missing/incomplete/invalid CLIA certification number.');
INSERT INTO `lang_constants` VALUES (1252, 'Missing/incomplete/invalid x-ray date.');
INSERT INTO `lang_constants` VALUES (1253, 'Missing/incomplete/invalid initial treatment date.');
INSERT INTO `lang_constants` VALUES (1254, 'Your center was not selected to participate in this study, therefore, we cannot pay for these services.');
INSERT INTO `lang_constants` VALUES (1255, 'Per legislation governing this program, payment constitutes payment in full.');
INSERT INTO `lang_constants` VALUES (1256, 'Pancreas transplant not covered unless kidney transplant performed.');
INSERT INTO `lang_constants` VALUES (1257, 'Missing/incomplete/invalid FDA approval number.');
INSERT INTO `lang_constants` VALUES (1258, 'Your claim contains incomplete and/or invalid information, and no appeal rights are afforded because the claim is unprocessable. Please submit a new claim with the complete/correct information.');
INSERT INTO `lang_constants` VALUES (1259, 'Physician already paid for services in conjunction with this demonstration claim. You must have the physician withdraw that claim and refund the payment before we can process your claim.');
INSERT INTO `lang_constants` VALUES (1260, 'Adjustment to the pre-demonstration rate.');
INSERT INTO `lang_constants` VALUES (1261, 'Claim overlaps inpatient stay. Rebill only those services rendered outside the inpatient stay.');
INSERT INTO `lang_constants` VALUES (1262, 'Missing/incomplete/invalid provider number of the facility where the patient resides.');
INSERT INTO `lang_constants` VALUES (1263, 'You may appeal this decision in writing within the required time limits following receipt of this notice by following the instructions included in your contract or plan benefit documents.');
INSERT INTO `lang_constants` VALUES (1264, 'This allowance has been made in accordance with the most appropriate course of treatment provision of the plan.');
INSERT INTO `lang_constants` VALUES (1265, 'Missing consent form.');
INSERT INTO `lang_constants` VALUES (1266, 'Missing/incomplete/invalid prior insurance carrier EOB.');
INSERT INTO `lang_constants` VALUES (1267, 'EOB received from previous payer. Claim not on file.');
INSERT INTO `lang_constants` VALUES (1268, 'Under FEHB law (U.S.C. 8904(b)), we cannot pay more for covered care than the amount Medicare would have allowed if the patient were enrolled in Medicare Part A and/or Medicare Part B.');
INSERT INTO `lang_constants` VALUES (1269, 'Processing of this claim/service has included consideration under Major Medical provisions.');
INSERT INTO `lang_constants` VALUES (1270, 'Crossover claim denied by previous payer and complete claim data not forwarded. Resubmit this claim to this payer to provide adequate data for adjudication.');
INSERT INTO `lang_constants` VALUES (1271, 'Adjustment represents the estimated amount a previous payer may pay.');
INSERT INTO `lang_constants` VALUES (1272, 'Claim/service adjusted based on the findings of a review organization/professional consult/manual adjudication/medical or dental advisor.');
INSERT INTO `lang_constants` VALUES (1273, 'Denial reversed because of medical review.');
INSERT INTO `lang_constants` VALUES (1274, 'Policy provides coverage supplemental to Medicare. As member does not appear to be enrolled in Medicare Part B, the member is responsible for payment of the portion of the charge that would have been covered by Medicare.');
INSERT INTO `lang_constants` VALUES (1275, 'Payment based on professional/technical component modifier(s).');
INSERT INTO `lang_constants` VALUES (1276, 'Payment based on a contractual amount or agreement, fee schedule, or maximum allowable amount.');
INSERT INTO `lang_constants` VALUES (1277, 'Services for a newborn must be billed separately.');
INSERT INTO `lang_constants` VALUES (1278, 'Family/member Out-of-Pocket maximum has been met. Payment based on a higher percentage.');
INSERT INTO `lang_constants` VALUES (1279, 'Procedure code incidental to primary procedure.');
INSERT INTO `lang_constants` VALUES (1280, 'Service not payable with other service rendered on the same date.');
INSERT INTO `lang_constants` VALUES (1281, 'Your line item has been separated into multiple lines to expedite handling.');
INSERT INTO `lang_constants` VALUES (1282, 'This procedure code was added/changed because it more accurately describes the services rendered.');
INSERT INTO `lang_constants` VALUES (1283, 'Patient liability may be affected due to coordination of benefits with other carriers and/or maximum benefit provisions.');
INSERT INTO `lang_constants` VALUES (1284, 'Missing/incomplete/invalid Electronic Funds Transfer (EFT) banking information.');
INSERT INTO `lang_constants` VALUES (1285, 'This company has been contracted by your benefit plan to provide administrative claims payment services only. This company does not assume financial risk or obligation with respect to claims processed on behalf of your benefit plan.');
INSERT INTO `lang_constants` VALUES (1286, 'Missing itemized bill.');
INSERT INTO `lang_constants` VALUES (1287, 'Missing/incomplete/invalid treatment number.');
INSERT INTO `lang_constants` VALUES (1288, 'Consent form requirements not fulfilled.');
INSERT INTO `lang_constants` VALUES (1289, 'Missing documentation/orders/notes/summary/report/chart.');
INSERT INTO `lang_constants` VALUES (1290, 'Patient ineligible for this service.');
INSERT INTO `lang_constants` VALUES (1291, 'Missing/incomplete/invalid prescribing provider identifier.');
INSERT INTO `lang_constants` VALUES (1292, 'Claim must be submitted by the provider who rendered the service.');
INSERT INTO `lang_constants` VALUES (1293, 'No record of health check prior to initiation of treatment.');
INSERT INTO `lang_constants` VALUES (1294, 'Incorrect claim form/format for this service.');
INSERT INTO `lang_constants` VALUES (1295, 'Program integrity/utilization review decision.');
INSERT INTO `lang_constants` VALUES (1296, 'Claim must meet primary payers processing requirements before we can consider payment.');
INSERT INTO `lang_constants` VALUES (1297, 'Missing/incomplete/invalid tooth number/letter.');
INSERT INTO `lang_constants` VALUES (1298, 'Procedure code is not compatible with tooth number/letter.');
INSERT INTO `lang_constants` VALUES (1299, 'Missing x-ray.');
INSERT INTO `lang_constants` VALUES (1300, 'No record of mental health assessment.');
INSERT INTO `lang_constants` VALUES (1301, 'Bed hold or leave days exceeded.');
INSERT INTO `lang_constants` VALUES (1302, 'Payment based on authorized amount.');
INSERT INTO `lang_constants` VALUES (1303, 'Missing/incomplete/invalid admission hour.');
INSERT INTO `lang_constants` VALUES (1304, 'Claim conflicts with another inpatient stay.');
INSERT INTO `lang_constants` VALUES (1305, 'Claim information does not agree with information received from other insurance carrier.');
INSERT INTO `lang_constants` VALUES (1306, 'Court ordered coverage information needs validation.');
INSERT INTO `lang_constants` VALUES (1307, 'Missing/incomplete/invalid discharge information.');
INSERT INTO `lang_constants` VALUES (1308, 'Electronic interchange agreement not on file for provider/submitter.');
INSERT INTO `lang_constants` VALUES (1309, 'Patient not enrolled in the billing provider''s managed care plan on the date of service.');
INSERT INTO `lang_constants` VALUES (1310, 'Missing/incomplete/invalid point of pick-up address.');
INSERT INTO `lang_constants` VALUES (1311, 'Claim information is inconsistent with pre-certified/authorized services.');
INSERT INTO `lang_constants` VALUES (1312, 'Procedures for billing with group/referring/performing providers were not followed.');
INSERT INTO `lang_constants` VALUES (1313, 'Procedure code billed is not correct/valid for the services billed or the date of service billed.');
INSERT INTO `lang_constants` VALUES (1314, 'Missing/incomplete/invalid prescribing date.');
INSERT INTO `lang_constants` VALUES (1315, 'Missing/incomplete/invalid patient liability amount.');
INSERT INTO `lang_constants` VALUES (1316, 'Please refer to your provider manual for additional program and provider information.');
INSERT INTO `lang_constants` VALUES (1317, 'Rebill services on separate claims.');
INSERT INTO `lang_constants` VALUES (1318, 'Inpatient admission spans multiple rate periods. Resubmit separate claims.');
INSERT INTO `lang_constants` VALUES (1319, 'Rebill services on separate claim lines.');
INSERT INTO `lang_constants` VALUES (1320, 'The from and to dates must be different.');
INSERT INTO `lang_constants` VALUES (1321, 'Procedure code or procedure rate count cannot be determined, or was not on file, for the date of service/provider.');
INSERT INTO `lang_constants` VALUES (1322, 'Professional provider services not paid separately. Included in facility payment under a demonstration project. Apply to that facility for payment, or resubmit your claim if: the facility notifies you the patient was excluded from this demonstration; or i');
INSERT INTO `lang_constants` VALUES (1323, 'Prior payment being cancelled as we were subsequently notified this patient was covered by a demonstration project in this site of service. Professional services were included in the payment made to the facility. You must contact the facility for your pay');
INSERT INTO `lang_constants` VALUES (1324, 'PPS (Prospective Payment System) code changed by claims processing system. Insufficient visits or therapies.');
INSERT INTO `lang_constants` VALUES (1325, 'Home health consolidated billing and payment applies.');
INSERT INTO `lang_constants` VALUES (1326, 'Your unassigned claim for a drug or biological, clinical diagnostic laboratory services or ambulance service was processed as an assigned claim. You are required by law to accept assignment for these types of claims.');
INSERT INTO `lang_constants` VALUES (1327, 'PPS (Prospective Payment System) code changed by medical reviewers. Not supported by clinical records.');
INSERT INTO `lang_constants` VALUES (1328, 'Resubmit with multiple claims, each claim covering services provided in only one calendar month.');
INSERT INTO `lang_constants` VALUES (1329, 'Missing/incomplete/invalid tooth surface information.');
INSERT INTO `lang_constants` VALUES (1330, 'Missing/incomplete/invalid number of riders.');
INSERT INTO `lang_constants` VALUES (1331, 'Missing/incomplete/invalid designated provider number.');
INSERT INTO `lang_constants` VALUES (1332, 'The necessary components of the child and teen checkup (EPSDT) were not completed.');
INSERT INTO `lang_constants` VALUES (1333, 'Service billed is not compatible with patient location information.');
INSERT INTO `lang_constants` VALUES (1334, 'Missing/incomplete/invalid prenatal screening information.');
INSERT INTO `lang_constants` VALUES (1335, 'Procedure billed is not compatible with tooth surface code.');
INSERT INTO `lang_constants` VALUES (1336, 'Provider must accept insurance payment as payment in full when a third party payer contract specifies full reimbursement.');
INSERT INTO `lang_constants` VALUES (1337, 'No appeal rights. Adjudicative decision based on the provisions of a demonstration project.');
INSERT INTO `lang_constants` VALUES (1338, 'Further installment payments forthcoming.');
INSERT INTO `lang_constants` VALUES (1339, 'Final installment payment.');
INSERT INTO `lang_constants` VALUES (1340, 'A failed trial of pelvic muscle exercise training is required in order for biofeedback training for the treatment of urinary incontinence to be covered.');
INSERT INTO `lang_constants` VALUES (1341, 'Home use of biofeedback therapy is not covered.');
INSERT INTO `lang_constants` VALUES (1342, 'This payment is being made conditionally. An HHA episode of care notice has been filed for this patient. When a patient is treated under a HHA episode of care, consolidated billing requires that certain therapy services and supplies, such as this, be incl');
INSERT INTO `lang_constants` VALUES (1343, 'Payment information for this claim has been forwarded to more than one other payer, but format limitations permit only one of the secondary payers to be identified in this remittance advice.');
INSERT INTO `lang_constants` VALUES (1344, 'Covered only when performed by the attending physician.');
INSERT INTO `lang_constants` VALUES (1345, 'Services not included in the appeal review.');
INSERT INTO `lang_constants` VALUES (1346, 'This facility is not certified for digital mammography.');
INSERT INTO `lang_constants` VALUES (1347, 'A separate claim must be submitted for each place of service. Services furnished at multiple sites may not be billed in the same claim.');
INSERT INTO `lang_constants` VALUES (1348, 'Claim/Service denied because a more specific taxonomy code is required for adjudication.');
INSERT INTO `lang_constants` VALUES (1349, 'This provider type/provider specialty may not bill this service.');
INSERT INTO `lang_constants` VALUES (1350, 'Patient must be refractory to conventional therapy (documented behavioral, pharmacologic and/or surgical corrective therapy) and be an appropriate surgical candidate such that implantation with anesthesia can occur.');
INSERT INTO `lang_constants` VALUES (1351, 'Patients with stress incontinence, urinary obstruction, and specific neurologic diseases (e.g., diabetes with peripheral nerve involvement) which are associated with secondary manifestations of the above three indications are excluded.');
INSERT INTO `lang_constants` VALUES (1352, 'Patient must have had a successful test stimulation in order to support subsequent implantation. Before a patient is eligible for permanent implantation, he/she must demonstrate a 50 percent or greater improvement through test stimulation. Improvement is');
INSERT INTO `lang_constants` VALUES (1353, 'Patient must be able to demonstrate adequate ability to record voiding diary data such that clinical results of the implant procedure can be properly evaluated.');
INSERT INTO `lang_constants` VALUES (1354, 'PPS (Prospect Payment System) code corrected during adjudication.');
INSERT INTO `lang_constants` VALUES (1355, 'This claim has been denied without reviewing the medical record because the requested records were not received or were not received timely.');
INSERT INTO `lang_constants` VALUES (1356, 'Social Security records indicate that this patient was a prisoner when the service was rendered. This payer does not cover items and services furnished to an individual while they are in State or local custody under a penal authority, unless under State o');
INSERT INTO `lang_constants` VALUES (1357, 'This claim/service is not payable under our claims jurisdiction area. You can identify the correct Medicare contractor to process this claim/service through the CMS website at www.cms.hhs.gov.');
INSERT INTO `lang_constants` VALUES (1358, 'This is a misdirected claim/service for an RRB beneficiary. Submit paper claims to the RRB carrier: Palmetto GBA, P.O. Box 10066, Augusta, GA 30999. Call 866-749-4301 for RRB EDI information for electronic claims processing.');
INSERT INTO `lang_constants` VALUES (1359, 'Payment for services furnished to Skilled Nursing Facility (SNF) inpatients (except for excluded services) can only be made to the SNF. You must request payment from the SNF rather than the patient for this service.');
INSERT INTO `lang_constants` VALUES (1360, 'Services furnished to Skilled Nursing Facility (SNF) inpatients must be billed on the inpatient claim. They cannot be billed separately as outpatient services.');
INSERT INTO `lang_constants` VALUES (1361, 'Missing/incomplete/invalid upgrade information.');
INSERT INTO `lang_constants` VALUES (1362, 'This claim was chosen for complex review and was denied after reviewing the medical records.');
INSERT INTO `lang_constants` VALUES (1363, 'This facility is not certified for film mammography.');
INSERT INTO `lang_constants` VALUES (1364, 'No appeal right except duplicate claim/service issue. This service was included in a claim that has been previously billed and adjudicated.');
INSERT INTO `lang_constants` VALUES (1365, 'This claim is excluded from your electronic remittance advice.');
INSERT INTO `lang_constants` VALUES (1366, 'Only one initial visit is covered per physician, group practice or provider.');
INSERT INTO `lang_constants` VALUES (1367, 'During the transition to the Ambulance Fee Schedule, payment is based on the lesser of a blended amount calculated using a percentage of the reasonable charge/cost and fee schedule amounts, or the submitted charge for the service. You will be notified yea');
INSERT INTO `lang_constants` VALUES (1368, 'This decision was based on a local medical review policy (LMRP) or Local Coverage Determination (LCD).An LMRP/LCD provides a guide to assist in determining whether a particular item or service is covered. A copy of this policy is available at http://www.c');
INSERT INTO `lang_constants` VALUES (1369, 'This payment is being made conditionally because the service was provided in the home, and it is possible that the patient is under a home health episode of care. When a patient is treated under a home health episode of care, consolidated billing requires');
INSERT INTO `lang_constants` VALUES (1370, 'This service is paid only once in a patients lifetime.');
INSERT INTO `lang_constants` VALUES (1371, 'This service is not paid if billed more than once every 28 days.');
INSERT INTO `lang_constants` VALUES (1372, 'This service is not paid if billed once every 28 days, and the patient has spent 5 or more consecutive days in any inpatient or Skilled /nursing Facility (SNF) within those 28 days.');
INSERT INTO `lang_constants` VALUES (1373, 'Payment is subject to home health prospective payment system partial episode payment adjustment. Patient was transferred/discharged/readmitted during payment episode.');
INSERT INTO `lang_constants` VALUES (1374, 'Medicare Part B does not pay for items or services provided by this type of practitioner for beneficiaries in a Medicare Part A covered Skilled Nursing Facility (SNF) stay.');
INSERT INTO `lang_constants` VALUES (1375, 'Add-on code cannot be billed by itself.');
INSERT INTO `lang_constants` VALUES (1376, 'This is a split service and represents a portion of the units from the originally submitted service.');
INSERT INTO `lang_constants` VALUES (1377, 'Payment has been denied for the/made only for a less extensive service/item because the information furnished does not substantiate the need for the (more extensive) service/item. The patient is liable for the charges for this service/item as you informed');
INSERT INTO `lang_constants` VALUES (1378, 'Payment has been (denied for the/made only for a less extensive) service/item because the information furnished does not substantiate the need for the (more extensive) service/item. If you have collected any amount from the patient, you must refund that a');
INSERT INTO `lang_constants` VALUES (1379, 'Social Security Records indicate that this individual has been deported. This payer does not cover items and services furnished to individuals who have been deported.');
INSERT INTO `lang_constants` VALUES (1380, 'This is a misdirected claim/service for a United Mine Workers of America (UMWA) beneficiary. Please submit claims to them.');
INSERT INTO `lang_constants` VALUES (1381, 'This amount represents the prior to coverage portion of the allowance.');
INSERT INTO `lang_constants` VALUES (1382, 'This amount represents the dollar amount not eligible due to the patient''s age.');
INSERT INTO `lang_constants` VALUES (1383, 'Consult plan benefit documents for information about restrictions for this service.');
INSERT INTO `lang_constants` VALUES (1384, 'Total payments under multiple contracts cannot exceed the allowance for this service.');
INSERT INTO `lang_constants` VALUES (1385, 'Payments will cease for services rendered by this US Government debarred or excluded provider after the 30 day grace period as previously notified.');
INSERT INTO `lang_constants` VALUES (1386, 'Services for predetermination and services requesting payment are being processed separately.');
INSERT INTO `lang_constants` VALUES (1387, 'This represents your scheduled payment for this service. If treatment has been discontinued, please contact Customer Service.');
INSERT INTO `lang_constants` VALUES (1388, 'Record fees are the patient''s responsibility and limited to the specified co-payment.');
INSERT INTO `lang_constants` VALUES (1389, 'To obtain information on the process to file an appeal in Arizona, call the Department''s Consumer Assistance Office at (602) 912-8444 or (800) 325-2548.');
INSERT INTO `lang_constants` VALUES (1390, 'The provider acting on the Member''s behalf, may file an appeal with the Payer. The provider, acting on the Member''s behalf, may file a complaint with the State Insurance Regulatory Authority without first filing an appeal, if the coverage decision involve');
INSERT INTO `lang_constants` VALUES (1391, 'In the event you disagree with the Dental Advisor''s opinion and have additional information relative to the case, you may submit radiographs to the Dental Advisor Unit at the subscriber''s dental insurance carrier for a second Independent Dental Advisor Re');
INSERT INTO `lang_constants` VALUES (1392, 'Under the Code of Federal Regulations, Chapter 32, Section 199.13 a non-participating provider is not an appropriate appealing party. Therefore, if you disagree with the Dental Advisor''s opinion, you may appeal the determination if appointed in writing, b');
INSERT INTO `lang_constants` VALUES (1393, 'You have not been designated as an authorized OCONUS provider therefore are not considered an appropriate appealing party. If the beneficiary has appointed you, in writing, to act as his/her representative and you disagree with the Dental Advisor''s opinio');
INSERT INTO `lang_constants` VALUES (1394, 'The patient was not residing in a long-term care facility during all or part of the service dates billed.');
INSERT INTO `lang_constants` VALUES (1395, 'The original claim was denied. Resubmit a new claim, not a replacement claim.');
INSERT INTO `lang_constants` VALUES (1396, 'The patient was not in a hospice program during all or part of the service dates billed.');
INSERT INTO `lang_constants` VALUES (1397, 'The rate changed during the dates of service billed.');
INSERT INTO `lang_constants` VALUES (1398, 'Missing screening document.');
INSERT INTO `lang_constants` VALUES (1399, 'Long term care case mix or per diem rate cannot be determined because the patient ID number is missing, incomplete, or invalid on the assignment request.');
INSERT INTO `lang_constants` VALUES (1400, 'Missing/incomplete/invalid date of last menstrual period.');
INSERT INTO `lang_constants` VALUES (1401, 'Rebill all applicable services on a single claim.');
INSERT INTO `lang_constants` VALUES (1402, 'Missing/incomplete/invalid model number.');
INSERT INTO `lang_constants` VALUES (1403, 'Telephone contact services will not be paid until the face-to-face contact requirement has been met.');
INSERT INTO `lang_constants` VALUES (1404, 'Missing/incomplete/invalid replacement claim information.');
INSERT INTO `lang_constants` VALUES (1405, 'Missing/incomplete/invalid room and board rate.');
INSERT INTO `lang_constants` VALUES (1406, 'This payment was delayed for correction of provider''s mailing address.');
INSERT INTO `lang_constants` VALUES (1407, 'Our records do not indicate that other insurance is on file. Please submit other insurance information for our records.');
INSERT INTO `lang_constants` VALUES (1408, 'The patient is responsible for the difference between the approved treatment and the elective treatment.');
INSERT INTO `lang_constants` VALUES (1409, 'Transportation to/from this destination is not covered.');
INSERT INTO `lang_constants` VALUES (1410, 'Transportation in a vehicle other than an ambulance is not covered.');
INSERT INTO `lang_constants` VALUES (1411, 'Payment denied/reduced because mileage is not covered when the patient is not in the ambulance.');
INSERT INTO `lang_constants` VALUES (1412, 'The patient must choose an option before a payment can be made for this procedure/ equipment/ supply/ service.');
INSERT INTO `lang_constants` VALUES (1413, 'This drug/service/supply is covered only when the associated service is covered.');
INSERT INTO `lang_constants` VALUES (1414, 'This is an alert. Although your claim was paid, you have billed for a test/specialty not included in your Laboratory Certification. Your failure to correct the laboratory certification information will result in a denial of payment in the near future.');
INSERT INTO `lang_constants` VALUES (1415, 'Medical record does not support code billed per the code definition.');
INSERT INTO `lang_constants` VALUES (1416, 'Charges exceed the post-transplant coverage limit.');
INSERT INTO `lang_constants` VALUES (1417, 'A new/revised/renewed certificate of medical necessity is needed.');
INSERT INTO `lang_constants` VALUES (1418, 'Payment for repair or replacement is not covered or has exceeded the purchase price.');
INSERT INTO `lang_constants` VALUES (1419, 'The patient is not liable for the denied/adjusted charge(s) for receiving any updated service/item.');
INSERT INTO `lang_constants` VALUES (1420, 'No qualifying hospital stay dates were provided for this episode of care.');
INSERT INTO `lang_constants` VALUES (1421, 'This is not a covered service/procedure/ equipment/bed, however patient liability is limited to amounts shown in the adjustments under group "PR".');
INSERT INTO `lang_constants` VALUES (1422, 'Missing Review Organization Approval.');
INSERT INTO `lang_constants` VALUES (1423, 'Services provided aboard a ship are covered only when the ship is of United States registry and is in United States waters. In addition, a doctor licensed to practice in the United States must provide the service.');
INSERT INTO `lang_constants` VALUES (1424, 'We did not send this claim to patients other insurer. They have indicated no additional payment can be made.');
INSERT INTO `lang_constants` VALUES (1425, 'Missing pre-operative photos or visual field results.');
INSERT INTO `lang_constants` VALUES (1426, 'Additional information has been requested from the member. The charges will be reconsidered upon receipt of that information.');
INSERT INTO `lang_constants` VALUES (1427, 'This item or service does not meet the criteria for the category under which it was billed.');
INSERT INTO `lang_constants` VALUES (1428, 'Additional information has been requested from another provider involved in the care of this member. The charges will be reconsidered upon receipt of that information.');
INSERT INTO `lang_constants` VALUES (1429, 'This claim/service must be billed according to the schedule for this plan.');
INSERT INTO `lang_constants` VALUES (1430, 'This is a predetermination advisory message, when this service is submitted for payment additional documentation as specified in plan documents will be required to process benefits.');
INSERT INTO `lang_constants` VALUES (1431, 'Rebill technical and professional components separately.');
INSERT INTO `lang_constants` VALUES (1432, 'Do not resubmit this claim/service.');
INSERT INTO `lang_constants` VALUES (1433, 'Non-Availability Statement (NAS) required for this service. Contact the nearest Military Treatment Facility (MTF) for assistance.');
INSERT INTO `lang_constants` VALUES (1434, 'You may request a review in writing within the required time limits following receipt of this notice by following the instructions included in your contract or plan benefit documents.');
INSERT INTO `lang_constants` VALUES (1435, 'The approved level of care does not match the procedure code submitted.');
INSERT INTO `lang_constants` VALUES (1436, 'This service has been paid as a one-time exception to the plan''s benefit restrictions.');
INSERT INTO `lang_constants` VALUES (1437, 'Missing contract indicator.');
INSERT INTO `lang_constants` VALUES (1438, 'The provider must update insurance information directly with payer.');
INSERT INTO `lang_constants` VALUES (1439, 'Patient is a Medicaid/Qualified Medicare Beneficiary.');
INSERT INTO `lang_constants` VALUES (1440, 'Specific federal/state/local program may cover this service through another payer.');
INSERT INTO `lang_constants` VALUES (1441, 'Technical component not paid if provider does not own the equipment used.');
INSERT INTO `lang_constants` VALUES (1442, 'The technical component must be billed separately.');
INSERT INTO `lang_constants` VALUES (1443, 'Patient eligible to apply for other coverage which may be primary.');
INSERT INTO `lang_constants` VALUES (1444, 'The subscriber must update insurance information directly with payer.');
INSERT INTO `lang_constants` VALUES (1445, 'Rendering provider must be affiliated with the pay-to provider.');
INSERT INTO `lang_constants` VALUES (1446, 'Additional payment approved based on payer-initiated review/audit.');
INSERT INTO `lang_constants` VALUES (1447, 'The professional component must be billed separately.');
INSERT INTO `lang_constants` VALUES (1448, 'A mental health facility is responsible for payment of outside providers who furnish these services/supplies to residents.');
INSERT INTO `lang_constants` VALUES (1449, 'Additional information/explanation will be sent separately');
INSERT INTO `lang_constants` VALUES (1450, 'Missing/incomplete/invalid anesthesia time/units');
INSERT INTO `lang_constants` VALUES (1451, 'Services under review for possible pre-existing condition. Send medical records for prior 12 months');
INSERT INTO `lang_constants` VALUES (1452, 'Information provided was illegible');
INSERT INTO `lang_constants` VALUES (1453, 'The supporting documentation does not match the claim');
INSERT INTO `lang_constants` VALUES (1454, 'Missing/incomplete/invalid weight.');
INSERT INTO `lang_constants` VALUES (1455, 'Missing/incomplete/invalid DRG code');
INSERT INTO `lang_constants` VALUES (1456, 'Missing/invalid/incomplete taxpayer identification number (TIN)');
INSERT INTO `lang_constants` VALUES (1457, 'You may appeal this decision');
INSERT INTO `lang_constants` VALUES (1458, 'You may not appeal this decision');
INSERT INTO `lang_constants` VALUES (1459, 'Charges processed under a Point of Service benefit');
INSERT INTO `lang_constants` VALUES (1460, 'Missing/incomplete/invalid facility/discrete unit DRG/DRG exempt status information');
INSERT INTO `lang_constants` VALUES (1461, 'Missing/incomplete/invalid history of the related initial surgical procedure(s)');
INSERT INTO `lang_constants` VALUES (1462, 'A payer providing supplemental or secondary coverage shall not require a claims determination for this service from a primary payer as a condition of making its own claims determination.');
INSERT INTO `lang_constants` VALUES (1463, 'Patient is not enrolled in this portion of our benefit package');
INSERT INTO `lang_constants` VALUES (1464, 'We pay only one site of service per provider per claim');
INSERT INTO `lang_constants` VALUES (1465, 'You must furnish and service this item for as long as the patient continues to need it. We can pay for maintenance and/or servicing for the time period specified in the contract or coverage manual.');
INSERT INTO `lang_constants` VALUES (1466, 'Payment based on previous payer''s allowed amount.');
INSERT INTO `lang_constants` VALUES (1467, 'See the payer''s web site or contact the payer''s Customer Service department to obtain forms and instructions for filing a provider dispute.');
INSERT INTO `lang_constants` VALUES (1468, 'Missing Admitting History and Physical report.');
INSERT INTO `lang_constants` VALUES (1469, 'Incomplete/invalid Admitting History and Physical report.');
INSERT INTO `lang_constants` VALUES (1470, 'Missing documentation of benefit to the patient during initial treatment period.');
INSERT INTO `lang_constants` VALUES (1471, 'Incomplete/invalid documentation of benefit to the patient during initial treatment period.');
INSERT INTO `lang_constants` VALUES (1472, 'Incomplete/invalid documentation/orders/notes/summary/report/chart.');
INSERT INTO `lang_constants` VALUES (1473, 'Incomplete/invalid American Diabetes Association Certificate of Recognition.');
INSERT INTO `lang_constants` VALUES (1474, 'Incomplete/invalid Certificate of Medical Necessity.');
INSERT INTO `lang_constants` VALUES (1475, 'Incomplete/invalid consent form.');
INSERT INTO `lang_constants` VALUES (1476, 'Incomplete/invalid contract indicator.');
INSERT INTO `lang_constants` VALUES (1477, 'Incomplete/invalid indication of whether the patient owns the equipment that requires the part or supply.');
INSERT INTO `lang_constants` VALUES (1478, 'Incomplete/invalid invoice or statement certifying the actual cost of the lens, less discounts, and/or the type of intraocular lens used.');
INSERT INTO `lang_constants` VALUES (1479, 'Incomplete/invalid itemized bill.');
INSERT INTO `lang_constants` VALUES (1480, 'Incomplete/invalid operative report.');
INSERT INTO `lang_constants` VALUES (1481, 'Incomplete/invalid oxygen certification/re-certification.');
INSERT INTO `lang_constants` VALUES (1482, 'Incomplete/invalid pacemaker registration form.');
INSERT INTO `lang_constants` VALUES (1483, 'Incomplete/invalid pathology report.');
INSERT INTO `lang_constants` VALUES (1484, 'Incomplete/invalid patient medical record for this service.');
INSERT INTO `lang_constants` VALUES (1485, 'Incomplete/invalid physician certified plan of care');
INSERT INTO `lang_constants` VALUES (1486, 'Incomplete/invalid physician financial relationship form.');
INSERT INTO `lang_constants` VALUES (1487, 'Incomplete/invalid radiology report.');
INSERT INTO `lang_constants` VALUES (1488, 'Incomplete/invalid Review Organization Approval.');
INSERT INTO `lang_constants` VALUES (1489, 'Incomplete/invalid x-ray.');
INSERT INTO `lang_constants` VALUES (1490, 'Incomplete/invalid/not approved screening document.');
INSERT INTO `lang_constants` VALUES (1491, 'Incomplete/invalid pre-operative photos/visual field results.');
INSERT INTO `lang_constants` VALUES (1492, 'Incomplete/invalid plan information for other insurance');
INSERT INTO `lang_constants` VALUES (1493, 'State regulated patient payment limitations apply to this service.');
INSERT INTO `lang_constants` VALUES (1494, 'Missing/incomplete/invalid assistant surgeon taxonomy.');
INSERT INTO `lang_constants` VALUES (1495, 'Missing/incomplete/invalid assistant surgeon name.');
INSERT INTO `lang_constants` VALUES (1496, 'Missing/incomplete/invalid assistant surgeon primary identifier.');
INSERT INTO `lang_constants` VALUES (1497, 'Missing/incomplete/invalid assistant surgeon secondary identifier.');
INSERT INTO `lang_constants` VALUES (1498, 'Missing/incomplete/invalid attending provider taxonomy.');
INSERT INTO `lang_constants` VALUES (1499, 'Missing/incomplete/invalid attending provider name.');
INSERT INTO `lang_constants` VALUES (1500, 'Missing/incomplete/invalid attending provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1501, 'Missing/incomplete/invalid attending provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1502, 'Missing/incomplete/invalid billing provider taxonomy.');
INSERT INTO `lang_constants` VALUES (1503, 'Missing/incomplete/invalid billing provider/supplier name.');
INSERT INTO `lang_constants` VALUES (1504, 'Missing/incomplete/invalid billing provider/supplier primary identifier.');
INSERT INTO `lang_constants` VALUES (1505, 'Missing/incomplete/invalid billing provider/supplier address.');
INSERT INTO `lang_constants` VALUES (1506, 'Missing/incomplete/invalid billing provider/supplier secondary identifier.');
INSERT INTO `lang_constants` VALUES (1507, 'Missing/incomplete/invalid billing provider/supplier contact information.');
INSERT INTO `lang_constants` VALUES (1508, 'Missing/incomplete/invalid operating provider name.');
INSERT INTO `lang_constants` VALUES (1509, 'Missing/incomplete/invalid operating provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1510, 'Missing/incomplete/invalid operating provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1511, 'Missing/incomplete/invalid ordering provider name.');
INSERT INTO `lang_constants` VALUES (1512, 'Missing/incomplete/invalid ordering provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1513, 'Missing/incomplete/invalid ordering provider address.');
INSERT INTO `lang_constants` VALUES (1514, 'Missing/incomplete/invalid ordering provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1515, 'Missing/incomplete/invalid ordering provider contact information.');
INSERT INTO `lang_constants` VALUES (1516, 'Missing/incomplete/invalid other provider name.');
INSERT INTO `lang_constants` VALUES (1517, 'Missing/incomplete/invalid other provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1518, 'Missing/incomplete/invalid other provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1519, 'Missing/incomplete/invalid other payer attending provider identifier.');
INSERT INTO `lang_constants` VALUES (1520, 'Missing/incomplete/invalid other payer operating provider identifier.');
INSERT INTO `lang_constants` VALUES (1521, 'Missing/incomplete/invalid other payer other provider identifier.');
INSERT INTO `lang_constants` VALUES (1522, 'Missing/incomplete/invalid other payer purchased service provider identifier.');
INSERT INTO `lang_constants` VALUES (1523, 'Missing/incomplete/invalid other payer referring provider identifier.');
INSERT INTO `lang_constants` VALUES (1524, 'Missing/incomplete/invalid other payer rendering provider identifier.');
INSERT INTO `lang_constants` VALUES (1525, 'Missing/incomplete/invalid other payer service facility provider identifier.');
INSERT INTO `lang_constants` VALUES (1526, 'Missing/incomplete/invalid pay-to provider name.');
INSERT INTO `lang_constants` VALUES (1527, 'Missing/incomplete/invalid pay-to provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1528, 'Missing/incomplete/invalid pay-to provider address.');
INSERT INTO `lang_constants` VALUES (1529, 'Missing/incomplete/invalid pay-to provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1530, 'Missing/incomplete/invalid purchased service provider identifier.');
INSERT INTO `lang_constants` VALUES (1531, 'Missing/incomplete/invalid referring provider taxonomy.');
INSERT INTO `lang_constants` VALUES (1532, 'Missing/incomplete/invalid referring provider name.');
INSERT INTO `lang_constants` VALUES (1533, 'Missing/incomplete/invalid referring provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1534, 'Missing/incomplete/invalid referring provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1535, 'Missing/incomplete/invalid rendering provider taxonomy.');
INSERT INTO `lang_constants` VALUES (1536, 'Missing/incomplete/invalid rendering provider name.');
INSERT INTO `lang_constants` VALUES (1537, 'Missing/incomplete/invalid rendering provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1538, 'Missing/incomplete/invalid rending provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1539, 'Missing/incomplete/invalid service facility name.');
INSERT INTO `lang_constants` VALUES (1540, 'Missing/incomplete/invalid service facility primary identifier.');
INSERT INTO `lang_constants` VALUES (1541, 'Missing/incomplete/invalid service facility primary address.');
INSERT INTO `lang_constants` VALUES (1542, 'Missing/incomplete/invalid service facility secondary identifier.');
INSERT INTO `lang_constants` VALUES (1543, 'Missing/incomplete/invalid supervising provider name.');
INSERT INTO `lang_constants` VALUES (1544, 'Missing/incomplete/invalid supervising provider primary identifier.');
INSERT INTO `lang_constants` VALUES (1545, 'Missing/incomplete/invalid supervising provider secondary identifier.');
INSERT INTO `lang_constants` VALUES (1546, 'Missing/incomplete/invalid occurrence date(s).');
INSERT INTO `lang_constants` VALUES (1547, 'Missing/incomplete/invalid occurrence span date(s).');
INSERT INTO `lang_constants` VALUES (1548, 'Missing/incomplete/invalid procedure date(s).');
INSERT INTO `lang_constants` VALUES (1549, 'Missing/incomplete/invalid other procedure date(s).');
INSERT INTO `lang_constants` VALUES (1550, 'Missing/incomplete/invalid principal procedure date.');
INSERT INTO `lang_constants` VALUES (1551, 'Missing/incomplete/invalid dispensed date.');
INSERT INTO `lang_constants` VALUES (1552, 'Missing/incomplete/invalid accident date.');
INSERT INTO `lang_constants` VALUES (1553, 'Missing/incomplete/invalid acute manifestation date.');
INSERT INTO `lang_constants` VALUES (1554, 'Missing/incomplete/invalid adjudication or payment date.');
INSERT INTO `lang_constants` VALUES (1555, 'Missing/incomplete/invalid appliance placement date.');
INSERT INTO `lang_constants` VALUES (1556, 'Missing/incomplete/invalid assessment date.');
INSERT INTO `lang_constants` VALUES (1557, 'Missing/incomplete/invalid assumed or relinquished care date.');
INSERT INTO `lang_constants` VALUES (1558, 'Missing/incomplete/invalid authorized to return to work date.');
INSERT INTO `lang_constants` VALUES (1559, 'Missing/incomplete/invalid begin therapy date.');
INSERT INTO `lang_constants` VALUES (1560, 'Missing/incomplete/invalid certification revision date.');
INSERT INTO `lang_constants` VALUES (1561, 'Missing/incomplete/invalid diagnosis date.');
INSERT INTO `lang_constants` VALUES (1562, 'Missing/incomplete/invalid disability from date.');
INSERT INTO `lang_constants` VALUES (1563, 'Missing/incomplete/invalid disability to date.');
INSERT INTO `lang_constants` VALUES (1564, 'Missing/incomplete/invalid discharge hour.');
INSERT INTO `lang_constants` VALUES (1565, 'Missing/incomplete/invalid discharge or end of care date.');
INSERT INTO `lang_constants` VALUES (1566, 'Missing/incomplete/invalid hearing or vision prescription date.');
INSERT INTO `lang_constants` VALUES (1567, 'Missing/incomplete/invalid Home Health Certification Period.');
INSERT INTO `lang_constants` VALUES (1568, 'Missing/incomplete/invalid last admission period.');
INSERT INTO `lang_constants` VALUES (1569, 'Missing/incomplete/invalid last certification date.');
INSERT INTO `lang_constants` VALUES (1570, 'Missing/incomplete/invalid last contact date.');
INSERT INTO `lang_constants` VALUES (1571, 'Missing/incomplete/invalid last seen/visit date.');
INSERT INTO `lang_constants` VALUES (1572, 'Missing/incomplete/invalid last worked date.');
INSERT INTO `lang_constants` VALUES (1573, 'Missing/incomplete/invalide last x-ray date.');
INSERT INTO `lang_constants` VALUES (1574, 'Missing/incomplete/invalid other insured birth date.');
INSERT INTO `lang_constants` VALUES (1575, 'Missing/incomplete/invalid Oxygen Saturation Test date.');
INSERT INTO `lang_constants` VALUES (1576, 'Missing/incomplete/invalid patient birth date.');
INSERT INTO `lang_constants` VALUES (1577, 'Missing/incomplete/invalid patient death date.');
INSERT INTO `lang_constants` VALUES (1578, 'Missing/incomplete/invalid physician order date.');
INSERT INTO `lang_constants` VALUES (1579, 'Missing/incomplete/invalid prior hospital discharge date.');
INSERT INTO `lang_constants` VALUES (1580, 'Missing/incomplete/invalid prior placement date.');
INSERT INTO `lang_constants` VALUES (1581, 'Missing/incomplete/invalid re-evaluation date');
INSERT INTO `lang_constants` VALUES (1582, 'Missing/incomplete/invalid referral date.');
INSERT INTO `lang_constants` VALUES (1583, 'Missing/incomplete/invalid replacement date.');
INSERT INTO `lang_constants` VALUES (1584, 'Missing/incomplete/invalid secondary diagnosis date.');
INSERT INTO `lang_constants` VALUES (1585, 'Missing/incomplete/invalid shipped date.');
INSERT INTO `lang_constants` VALUES (1586, 'Missing/incomplete/invalid similar illness or symptom date.');
INSERT INTO `lang_constants` VALUES (1587, 'Missing/incomplete/invalid subscriber birth date.');
INSERT INTO `lang_constants` VALUES (1588, 'Missing/incomplete/invalid surgery date.');
INSERT INTO `lang_constants` VALUES (1589, 'Missing/incomplete/invalid test performed date.');
INSERT INTO `lang_constants` VALUES (1590, 'Missing/incomplete/invalid Transcutaneous Electrical Nerve Stimulator (TENS) trial start date.');
INSERT INTO `lang_constants` VALUES (1591, 'Missing/incomplete/invalid Transcutaneous Electrical Nerve Stimulator (TENS) trial end date.');
INSERT INTO `lang_constants` VALUES (1592, 'Date range not valid with units submitted.');
INSERT INTO `lang_constants` VALUES (1593, 'Missing/incomplete/invalid oral cavity designation code.');
INSERT INTO `lang_constants` VALUES (1594, 'Your claim for a referred or purchased service cannot be paid because payment has already been made for this same service to another provider by a payment contractor representing the payer.');
INSERT INTO `lang_constants` VALUES (1595, 'You chose that this service/supply/drug would be rendered/supplied and billed by a different practitioner/supplier.');
INSERT INTO `lang_constants` VALUES (1596, 'The administration method and drug must be reported to adjudicate this service.');
INSERT INTO `lang_constants` VALUES (1597, 'Missing/incomplete/invalid description of service for a Not Otherwise Classified (NOC) code or an Unlisted procedure.');
INSERT INTO `lang_constants` VALUES (1598, 'Service date outside of the approved treatment plan service dates.');
INSERT INTO `lang_constants` VALUES (1599, 'There are no scheduled payments for this service. Submit a claim for each patient visit.');
INSERT INTO `lang_constants` VALUES (1600, 'Benefits have been estimated, when the actual services have been rendered, additional payment will be considered based on the submitted claim.');
INSERT INTO `lang_constants` VALUES (1601, 'Incomplete/invalid invoice');
INSERT INTO `lang_constants` VALUES (1602, 'The law permits exceptions to the refund requirement in two cases: - If you did not know, and could not have reasonably been expected to know, that we would not pay for this service; or - If you notified the patient in writing before providing the service');
INSERT INTO `lang_constants` VALUES (1603, 'This service is not covered when performed with, or subsequent to, a non-covered service.');
INSERT INTO `lang_constants` VALUES (1604, 'Time frame requirements between this service/procedure/supply and a related service/procedure/supply have not been met.');
INSERT INTO `lang_constants` VALUES (1605, 'This decision may be reviewed if additional documentation as described in the contract or plan benefit documents is submitted.');
INSERT INTO `lang_constants` VALUES (1606, 'Missing/incomplete/invalid height.');
INSERT INTO `lang_constants` VALUES (1607, 'Coordination of benefits has not been calculated when estimating benefits for this pre-determination. Submit payment information from the primary payer with the secondary claim.');
INSERT INTO `lang_constants` VALUES (1608, 'Charges are adjusted based on multiple diagnostic imaging procedure rules.');
INSERT INTO `lang_constants` VALUES (1609, 'The number of Days or Units of Service exceeds our acceptable maximum.');
INSERT INTO `lang_constants` VALUES (1610, 'Alert: in the near future we are implementing new policies/procedures that would affect this determination.');
INSERT INTO `lang_constants` VALUES (1611, 'According to our agreement, you must waive the deductible and/or coinsurance amounts.');
INSERT INTO `lang_constants` VALUES (1612, 'This procedure code is not payable. It is for reporting/information purposes only.');
INSERT INTO `lang_constants` VALUES (1613, 'Requested information not provided. The claim will be reopened if the information previously requested is submitted within one year after the date of this denial notice.');
INSERT INTO `lang_constants` VALUES (1614, 'The claim information has been forwarded to a Health Savings Account processor for review.');
INSERT INTO `lang_constants` VALUES (1615, 'You must appeal the determination of the previously ajudicated claim.');
INSERT INTO `lang_constants` VALUES (1616, 'Alert: Although this claim has been processed, it is deficient according to state legislation/regulation.');
INSERT INTO `lang_constants` VALUES (1617, 'Indigent Patients Report');
INSERT INTO `lang_constants` VALUES (1618, 'End Date:');
INSERT INTO `lang_constants` VALUES (1619, 'Due Date');
INSERT INTO `lang_constants` VALUES (1620, 'EOB Posting - Invoice');
INSERT INTO `lang_constants` VALUES (1621, 'Source is missing for code');
INSERT INTO `lang_constants` VALUES (1622, 'Invalid or missing payer in source for code');
INSERT INTO `lang_constants` VALUES (1623, 'Missing slash after payer in source for code');
INSERT INTO `lang_constants` VALUES (1624, 'Invalid source designation "'' + tmp +');
INSERT INTO `lang_constants` VALUES (1625, 'Date is missing for code');
INSERT INTO `lang_constants` VALUES (1626, 'Payment value for code '' + code + ''');
INSERT INTO `lang_constants` VALUES (1627, 'Adjustment value for code '' + code + ''');
INSERT INTO `lang_constants` VALUES (1628, 'Please select an adjustment reason for code');
INSERT INTO `lang_constants` VALUES (1629, 'Patient:');
INSERT INTO `lang_constants` VALUES (1630, 'Invoice:');
INSERT INTO `lang_constants` VALUES (1631, 'Done with:'',''e'',''');
INSERT INTO `lang_constants` VALUES (1632, 'Bill Date:');
INSERT INTO `lang_constants` VALUES (1633, 'Now posting for:'',''e'',''');
INSERT INTO `lang_constants` VALUES (1634, 'Due Date:');
INSERT INTO `lang_constants` VALUES (1635, 'Due date mm/dd/yyyy or yyyy-mm-dd');
INSERT INTO `lang_constants` VALUES (1636, 'Pay');
INSERT INTO `lang_constants` VALUES (1637, 'EOB Posting - Search');
INSERT INTO `lang_constants` VALUES (1638, 'Source:');
INSERT INTO `lang_constants` VALUES (1639, 'Pay Date:');
INSERT INTO `lang_constants` VALUES (1640, 'Amount:');
INSERT INTO `lang_constants` VALUES (1641, 'Name:');
INSERT INTO `lang_constants` VALUES (1642, 'last,first'', or ''X-Y');
INSERT INTO `lang_constants` VALUES (1643, 'Chart ID:');
INSERT INTO `lang_constants` VALUES (1644, 'Encounter:');
INSERT INTO `lang_constants` VALUES (1645, 'Open');
INSERT INTO `lang_constants` VALUES (1646, 'Or upload ERA file:');
INSERT INTO `lang_constants` VALUES (1647, 'Processed as Primary');
INSERT INTO `lang_constants` VALUES (1648, 'Processed as Secondary');
INSERT INTO `lang_constants` VALUES (1649, 'Processed as Tertiary');
INSERT INTO `lang_constants` VALUES (1650, 'Denied');
INSERT INTO `lang_constants` VALUES (1651, 'Pended');
INSERT INTO `lang_constants` VALUES (1652, 'Received, but not in process');
INSERT INTO `lang_constants` VALUES (1653, 'Suspended - investigation with field');
INSERT INTO `lang_constants` VALUES (1654, 'Suspended - return with material');
INSERT INTO `lang_constants` VALUES (1655, 'Suspended - review pending');
INSERT INTO `lang_constants` VALUES (1656, 'Processed as Primary, Forwarded to Additional Payer(s)');
INSERT INTO `lang_constants` VALUES (1657, 'Processed as Secondary, Forwarded to Additional Payer(s)');
INSERT INTO `lang_constants` VALUES (1658, 'Processed as Tertiary, Forwarded to Additional Payer(s)');
INSERT INTO `lang_constants` VALUES (1659, 'Reversal of Previous Payment');
INSERT INTO `lang_constants` VALUES (1660, 'Not Our Claim, Forwarded to Additional Payer(s)');
INSERT INTO `lang_constants` VALUES (1661, 'Predetermination Pricing Only - No Payment');
INSERT INTO `lang_constants` VALUES (1662, 'Reviewed');
INSERT INTO `lang_constants` VALUES (1663, 'Cannot provide further status electronically.');
INSERT INTO `lang_constants` VALUES (1664, 'For more detailed information, see remittance advice.');
INSERT INTO `lang_constants` VALUES (1665, 'More detailed information in letter.');
INSERT INTO `lang_constants` VALUES (1666, 'Claim has been adjudicated and is awaiting payment cycle.');
INSERT INTO `lang_constants` VALUES (1667, 'This is a subsequent request for information from the original request.');
INSERT INTO `lang_constants` VALUES (1668, 'This is a final request for information.');
INSERT INTO `lang_constants` VALUES (1669, 'Balance due from the subscriber.');
INSERT INTO `lang_constants` VALUES (1670, 'Claim may be reconsidered at a future date.');
INSERT INTO `lang_constants` VALUES (1671, 'No payment due to contract/plan provisions.');
INSERT INTO `lang_constants` VALUES (1672, 'No payment will be made for this claim.');
INSERT INTO `lang_constants` VALUES (1673, 'All originally submitted procedure codes have been combined.');
INSERT INTO `lang_constants` VALUES (1674, 'Some originally submitted procedure codes have been combined.');
INSERT INTO `lang_constants` VALUES (1675, 'One or more originally submitted procedure codes have been combined.');
INSERT INTO `lang_constants` VALUES (1676, 'All originally submitted procedure codes have been modified.');
INSERT INTO `lang_constants` VALUES (1677, 'Some all originally submitted procedure codes have been modified.');
INSERT INTO `lang_constants` VALUES (1678, 'One or more originally submitted procedure code have been modified.');
INSERT INTO `lang_constants` VALUES (1679, 'Claim/encounter has been forwarded to entity.');
INSERT INTO `lang_constants` VALUES (1680, 'Claim/encounter has been forwarded by third party entity to entity.');
INSERT INTO `lang_constants` VALUES (1681, 'Entity received claim/encounter, but returned invalid status.');
INSERT INTO `lang_constants` VALUES (1682, 'Entity acknowledges receipt of claim/encounter.');
INSERT INTO `lang_constants` VALUES (1683, 'Accepted for processing.');
INSERT INTO `lang_constants` VALUES (1684, 'Missing or invalid information.');
INSERT INTO `lang_constants` VALUES (1685, '... before entering the adjudication system.');
INSERT INTO `lang_constants` VALUES (1686, 'Returned to Entity.');
INSERT INTO `lang_constants` VALUES (1687, 'Entity not approved as an electronic submitter.');
INSERT INTO `lang_constants` VALUES (1688, 'Entity not approved.');
INSERT INTO `lang_constants` VALUES (1689, 'Entity not found.');
INSERT INTO `lang_constants` VALUES (1690, 'Policy canceled.');
INSERT INTO `lang_constants` VALUES (1691, 'Claim submitted to wrong payer.');
INSERT INTO `lang_constants` VALUES (1692, 'Subscriber and policy number/contract number mismatched.');
INSERT INTO `lang_constants` VALUES (1693, 'Subscriber and subscriber id mismatched.');
INSERT INTO `lang_constants` VALUES (1694, 'Subscriber and policyholder name mismatched.');
INSERT INTO `lang_constants` VALUES (1695, 'Subscriber and policy number/contract number not found.');
INSERT INTO `lang_constants` VALUES (1696, 'Subscriber and subscriber id not found.');
INSERT INTO `lang_constants` VALUES (1697, 'Subscriber and policyholder name not found.');
INSERT INTO `lang_constants` VALUES (1698, 'Claim/encounter not found.');
INSERT INTO `lang_constants` VALUES (1699, 'Predetermination is on file, awaiting completion of services.');
INSERT INTO `lang_constants` VALUES (1700, 'Awaiting next periodic adjudication cycle.');
INSERT INTO `lang_constants` VALUES (1701, 'Charges for pregnancy deferred until delivery.');
INSERT INTO `lang_constants` VALUES (1702, 'Waiting for final approval.');
INSERT INTO `lang_constants` VALUES (1703, 'Special handling required at payer site.');
INSERT INTO `lang_constants` VALUES (1704, 'Awaiting related charges.');
INSERT INTO `lang_constants` VALUES (1705, 'Charges pending provider audit.');
INSERT INTO `lang_constants` VALUES (1706, 'Awaiting benefit determination.');
INSERT INTO `lang_constants` VALUES (1707, 'Internal review/audit.');
INSERT INTO `lang_constants` VALUES (1708, 'Internal review/audit - partial payment made.');
INSERT INTO `lang_constants` VALUES (1709, 'Referral/authorization.');
INSERT INTO `lang_constants` VALUES (1710, 'Pending provider accreditation review.');
INSERT INTO `lang_constants` VALUES (1711, 'Claim waiting for internal provider verification.');
INSERT INTO `lang_constants` VALUES (1712, 'Investigating occupational illness/accident.');
INSERT INTO `lang_constants` VALUES (1713, 'Investigating existence of other insurance coverage.');
INSERT INTO `lang_constants` VALUES (1714, 'Claim being researched for Insured ID/Group Policy Number error.');
INSERT INTO `lang_constants` VALUES (1715, 'Duplicate of a previously processed claim/line.');
INSERT INTO `lang_constants` VALUES (1716, 'Claim assigned to an approver/analyst.');
INSERT INTO `lang_constants` VALUES (1717, 'Awaiting eligibility determination.');
INSERT INTO `lang_constants` VALUES (1718, 'Pending COBRA information requested.');
INSERT INTO `lang_constants` VALUES (1719, 'Non-electronic request for information.');
INSERT INTO `lang_constants` VALUES (1720, 'Electronic request for information.');
INSERT INTO `lang_constants` VALUES (1721, 'Eligibility for extended benefits.');
INSERT INTO `lang_constants` VALUES (1722, 'Re-pricing information.');
INSERT INTO `lang_constants` VALUES (1723, 'Claim/line has been paid.');
INSERT INTO `lang_constants` VALUES (1724, 'Payment reflects usual and customary charges.');
INSERT INTO `lang_constants` VALUES (1725, 'Payment made in full.');
INSERT INTO `lang_constants` VALUES (1726, 'Partial payment made for this claim.');
INSERT INTO `lang_constants` VALUES (1727, 'Payment reflects plan provisions.');
INSERT INTO `lang_constants` VALUES (1728, 'Payment reflects contract provisions.');
INSERT INTO `lang_constants` VALUES (1729, 'Periodic installment released.');
INSERT INTO `lang_constants` VALUES (1730, 'Claim contains split payment.');
INSERT INTO `lang_constants` VALUES (1731, 'Payment made to entity, assignment of benefits not on file.');
INSERT INTO `lang_constants` VALUES (1732, 'Duplicate of an existing claim/line, awaiting processing.');
INSERT INTO `lang_constants` VALUES (1733, 'Contract/plan does not cover pre-existing conditions.');
INSERT INTO `lang_constants` VALUES (1734, 'No coverage for newborns.');
INSERT INTO `lang_constants` VALUES (1735, 'Service not authorized.');
INSERT INTO `lang_constants` VALUES (1736, 'Entity not primary.');
INSERT INTO `lang_constants` VALUES (1737, 'Diagnosis and patient gender mismatch.');
INSERT INTO `lang_constants` VALUES (1738, 'Denied: Entity not found.');
INSERT INTO `lang_constants` VALUES (1739, 'Entity not eligible for benefits for submitted dates of service.');
INSERT INTO `lang_constants` VALUES (1740, 'Entity not eligible for dental benefits for submitted dates of service.');
INSERT INTO `lang_constants` VALUES (1741, 'Entity not eligible for medical benefits for submitted dates of service.');
INSERT INTO `lang_constants` VALUES (1742, 'Entity not eligible/not approved for dates of service.');
INSERT INTO `lang_constants` VALUES (1743, 'Entity does not meet dependent or student qualification.');
INSERT INTO `lang_constants` VALUES (1744, 'Entity is not selected primary care provider.');
INSERT INTO `lang_constants` VALUES (1745, 'Entity not referred by selected primary care provider.');
INSERT INTO `lang_constants` VALUES (1746, 'Requested additional information not received.');
INSERT INTO `lang_constants` VALUES (1747, 'No agreement with entity.');
INSERT INTO `lang_constants` VALUES (1748, 'Patient eligibility not found with entity.');
INSERT INTO `lang_constants` VALUES (1749, 'Charges applied to deductible.');
INSERT INTO `lang_constants` VALUES (1750, 'Pre-treatment review.');
INSERT INTO `lang_constants` VALUES (1751, 'Pre-certification penalty taken.');
INSERT INTO `lang_constants` VALUES (1752, 'Claim was processed as adjustment to previous claim.');
INSERT INTO `lang_constants` VALUES (1753, 'Newborn''s charges processed on mother''s claim.');
INSERT INTO `lang_constants` VALUES (1754, 'Claim combined with other claim(s).');
INSERT INTO `lang_constants` VALUES (1755, 'Processed according to plan provisions.');
INSERT INTO `lang_constants` VALUES (1756, 'Claim/line is capitated.');
INSERT INTO `lang_constants` VALUES (1757, 'This amount is not entity''s responsibility.');
INSERT INTO `lang_constants` VALUES (1758, 'Processed according to contract/plan provisions.');
INSERT INTO `lang_constants` VALUES (1759, 'Coverage has been canceled for this entity.');
INSERT INTO `lang_constants` VALUES (1760, 'Entity not eligible.');
INSERT INTO `lang_constants` VALUES (1761, 'Claim requires pricing information.');
INSERT INTO `lang_constants` VALUES (1762, 'At the policyholder''s request these claims cannot be submitted electronically.');
INSERT INTO `lang_constants` VALUES (1763, 'Policyholder processes their own claims.');
INSERT INTO `lang_constants` VALUES (1764, 'Cannot process individual insurance policy claims.');
INSERT INTO `lang_constants` VALUES (1765, 'Should be handled by entity.');
INSERT INTO `lang_constants` VALUES (1766, 'Cannot process HMO claims');
INSERT INTO `lang_constants` VALUES (1767, 'Claim submitted to incorrect payer.');
INSERT INTO `lang_constants` VALUES (1768, 'Claim requires signature-on-file indicator.');
INSERT INTO `lang_constants` VALUES (1769, 'TPO rejected claim/line because payer name is missing.');
INSERT INTO `lang_constants` VALUES (1770, 'TPO rejected claim/line because certification information is missing');
INSERT INTO `lang_constants` VALUES (1771, 'TPO rejected claim/line because claim does not contain enough information');
INSERT INTO `lang_constants` VALUES (1772, 'Service line number greater than maximum allowable for payer.');
INSERT INTO `lang_constants` VALUES (1773, 'Missing/invalid data prevents payer from processing claim.');
INSERT INTO `lang_constants` VALUES (1774, 'Additional information requested from entity.');
INSERT INTO `lang_constants` VALUES (1775, 'Entity''s name, address, phone and id number.');
INSERT INTO `lang_constants` VALUES (1776, 'Entity''s name.');
INSERT INTO `lang_constants` VALUES (1777, 'Entity''s address.');
INSERT INTO `lang_constants` VALUES (1778, 'Entity''s phone number.');
INSERT INTO `lang_constants` VALUES (1779, 'Entity''s tax id.');
INSERT INTO `lang_constants` VALUES (1780, 'Entity''s Blue Cross provider id');
INSERT INTO `lang_constants` VALUES (1781, 'Entity''s Blue Shield provider id');
INSERT INTO `lang_constants` VALUES (1782, 'Entity''s Medicare provider id.');
INSERT INTO `lang_constants` VALUES (1783, 'Entity''s Medicaid provider id.');
INSERT INTO `lang_constants` VALUES (1784, 'Entity''s UPIN');
INSERT INTO `lang_constants` VALUES (1785, 'Entity''s CHAMPUS provider id.');
INSERT INTO `lang_constants` VALUES (1786, 'Entity''s commercial provider id.');
INSERT INTO `lang_constants` VALUES (1787, 'Entity''s health industry id number.');
INSERT INTO `lang_constants` VALUES (1788, 'Entity''s plan network id.');
INSERT INTO `lang_constants` VALUES (1789, 'Entity''s site id .');
INSERT INTO `lang_constants` VALUES (1790, 'Entity''s health maintenance provider id (HMO).');
INSERT INTO `lang_constants` VALUES (1791, 'Entity''s preferred provider organization id (PPO).');
INSERT INTO `lang_constants` VALUES (1792, 'Entity''s administrative services organization id (ASO).');
INSERT INTO `lang_constants` VALUES (1793, 'Entity''s license/certification number.');
INSERT INTO `lang_constants` VALUES (1794, 'Entity''s state license number.');
INSERT INTO `lang_constants` VALUES (1795, 'Entity''s specialty license number.');
INSERT INTO `lang_constants` VALUES (1796, 'Entity''s specialty code.');
INSERT INTO `lang_constants` VALUES (1797, 'Entity''s anesthesia license number.');
INSERT INTO `lang_constants` VALUES (1798, 'Entity''s qualification degree/designation (e.g. RN,PhD,MD)');
INSERT INTO `lang_constants` VALUES (1799, 'Entity''s social security number.');
INSERT INTO `lang_constants` VALUES (1800, 'Entity''s employer id.');
INSERT INTO `lang_constants` VALUES (1801, 'Entity''s drug enforcement agency (DEA) number.');
INSERT INTO `lang_constants` VALUES (1802, 'Pharmacy processor number.');
INSERT INTO `lang_constants` VALUES (1803, 'Entity''s id number.');
INSERT INTO `lang_constants` VALUES (1804, 'Relationship of surgeon & assistant surgeon.');
INSERT INTO `lang_constants` VALUES (1805, 'Entity''s relationship to patient');
INSERT INTO `lang_constants` VALUES (1806, 'Patient relationship to subscriber');
INSERT INTO `lang_constants` VALUES (1807, 'Entity''s Gender');
INSERT INTO `lang_constants` VALUES (1808, 'Entity''s date of birth');
INSERT INTO `lang_constants` VALUES (1809, 'Entity''s date of death');
INSERT INTO `lang_constants` VALUES (1810, 'Entity''s marital status');
INSERT INTO `lang_constants` VALUES (1811, 'Entity''s employment status');
INSERT INTO `lang_constants` VALUES (1812, 'Entity''s health insurance claim number (HICN).');
INSERT INTO `lang_constants` VALUES (1813, 'Entity''s policy number.');
INSERT INTO `lang_constants` VALUES (1814, 'Entity''s contract/member number.');
INSERT INTO `lang_constants` VALUES (1815, 'Entity''s employer name, address and phone.');
INSERT INTO `lang_constants` VALUES (1816, 'Entity''s employer name.');
INSERT INTO `lang_constants` VALUES (1817, 'Entity''s employer address.');
INSERT INTO `lang_constants` VALUES (1818, 'Entity''s employer phone number.');
INSERT INTO `lang_constants` VALUES (1819, 'Entity''s employee id.');
INSERT INTO `lang_constants` VALUES (1820, 'Other insurance coverage information (health, liability, auto, etc.).');
INSERT INTO `lang_constants` VALUES (1821, 'Other employer name, address and telephone number.');
INSERT INTO `lang_constants` VALUES (1822, 'Entity''s name, address, phone, gender, DOB, marital status, employment status and relation to subscriber.');
INSERT INTO `lang_constants` VALUES (1823, 'Entity''s student status.');
INSERT INTO `lang_constants` VALUES (1824, 'Entity''s school name.');
INSERT INTO `lang_constants` VALUES (1825, 'Entity''s school address.');
INSERT INTO `lang_constants` VALUES (1826, 'Transplant recipient''s name, date of birth, gender, relationship to insured.');
INSERT INTO `lang_constants` VALUES (1827, 'Submitted charges.');
INSERT INTO `lang_constants` VALUES (1828, 'Outside lab charges.');
INSERT INTO `lang_constants` VALUES (1829, 'Hospital s semi-private room rate.');
INSERT INTO `lang_constants` VALUES (1830, 'Hospital s room rate.');
INSERT INTO `lang_constants` VALUES (1831, 'Allowable/paid from primary coverage.');
INSERT INTO `lang_constants` VALUES (1832, 'Amount entity has paid.');
INSERT INTO `lang_constants` VALUES (1833, 'Purchase price for the rented durable medical equipment.');
INSERT INTO `lang_constants` VALUES (1834, 'Rental price for durable medical equipment.');
INSERT INTO `lang_constants` VALUES (1835, 'Purchase and rental price of durable medical equipment.');
INSERT INTO `lang_constants` VALUES (1836, 'Date(s) of service.');
INSERT INTO `lang_constants` VALUES (1837, 'Statement from-through dates.');
INSERT INTO `lang_constants` VALUES (1838, 'Hospital admission date.');
INSERT INTO `lang_constants` VALUES (1839, 'Hospital discharge date.');
INSERT INTO `lang_constants` VALUES (1840, 'Date of Last Menstrual Period (LMP)');
INSERT INTO `lang_constants` VALUES (1841, 'Date of first service for current series/symptom/illness.');
INSERT INTO `lang_constants` VALUES (1842, 'First consultation/evaluation date.');
INSERT INTO `lang_constants` VALUES (1843, 'Confinement dates.');
INSERT INTO `lang_constants` VALUES (1844, 'Unable to work dates.');
INSERT INTO `lang_constants` VALUES (1845, 'Return to work dates.');
INSERT INTO `lang_constants` VALUES (1846, 'Effective coverage date(s).');
INSERT INTO `lang_constants` VALUES (1847, 'Medicare effective date.');
INSERT INTO `lang_constants` VALUES (1848, 'Date of conception and expected date of delivery.');
INSERT INTO `lang_constants` VALUES (1849, 'Date of equipment return.');
INSERT INTO `lang_constants` VALUES (1850, 'Date of dental appliance prior placement.');
INSERT INTO `lang_constants` VALUES (1851, 'Date of dental prior replacement/reason for replacement.');
INSERT INTO `lang_constants` VALUES (1852, 'Date of dental appliance placed.');
INSERT INTO `lang_constants` VALUES (1853, 'Date dental canal(s) opened and date service completed.');
INSERT INTO `lang_constants` VALUES (1854, 'Date(s) dental root canal therapy previously performed.');
INSERT INTO `lang_constants` VALUES (1855, 'Most recent date of curettage, root planing, or periodontal surgery.');
INSERT INTO `lang_constants` VALUES (1856, 'Dental impression and seating date.');
INSERT INTO `lang_constants` VALUES (1857, 'Most recent date pacemaker was implanted.');
INSERT INTO `lang_constants` VALUES (1858, 'Most recent pacemaker battery change date.');
INSERT INTO `lang_constants` VALUES (1859, 'Date of the last x-ray.');
INSERT INTO `lang_constants` VALUES (1860, 'Date(s) of dialysis training provided to patient.');
INSERT INTO `lang_constants` VALUES (1861, 'Date of last routine dialysis.');
INSERT INTO `lang_constants` VALUES (1862, 'Date of first routine dialysis.');
INSERT INTO `lang_constants` VALUES (1863, 'Original date of prescription/orders/referral.');
INSERT INTO `lang_constants` VALUES (1864, 'Date of tooth extraction/evolution.');
INSERT INTO `lang_constants` VALUES (1865, 'Drug information.');
INSERT INTO `lang_constants` VALUES (1866, 'Drug name, strength and dosage form.');
INSERT INTO `lang_constants` VALUES (1867, 'NDC number.');
INSERT INTO `lang_constants` VALUES (1868, 'Prescription number.');
INSERT INTO `lang_constants` VALUES (1869, 'Drug product id number.');
INSERT INTO `lang_constants` VALUES (1870, 'Drug days supply and dosage.');
INSERT INTO `lang_constants` VALUES (1871, 'Drug dispensing units and average wholesale price (AWP).');
INSERT INTO `lang_constants` VALUES (1872, 'Route of drug/myelogram administration.');
INSERT INTO `lang_constants` VALUES (1873, 'Anatomical location for joint injection.');
INSERT INTO `lang_constants` VALUES (1874, 'Anatomical location.');
INSERT INTO `lang_constants` VALUES (1875, 'Joint injection site.');
INSERT INTO `lang_constants` VALUES (1876, 'Hospital information.');
INSERT INTO `lang_constants` VALUES (1877, 'Type of bill for UB-92 claim.');
INSERT INTO `lang_constants` VALUES (1878, 'Hospital admission source.');
INSERT INTO `lang_constants` VALUES (1879, 'Hospital admission hour.');
INSERT INTO `lang_constants` VALUES (1880, 'Hospital admission type.');
INSERT INTO `lang_constants` VALUES (1881, 'Admitting diagnosis.');
INSERT INTO `lang_constants` VALUES (1882, 'Hospital discharge hour.');
INSERT INTO `lang_constants` VALUES (1883, 'Patient discharge status.');
INSERT INTO `lang_constants` VALUES (1884, 'Units of blood furnished.');
INSERT INTO `lang_constants` VALUES (1885, 'Units of blood replaced.');
INSERT INTO `lang_constants` VALUES (1886, 'Units of deductible blood.');
INSERT INTO `lang_constants` VALUES (1887, 'Separate claim for mother/baby charges.');
INSERT INTO `lang_constants` VALUES (1888, 'Dental information.');
INSERT INTO `lang_constants` VALUES (1889, 'Tooth surface(s) involved.');
INSERT INTO `lang_constants` VALUES (1890, 'List of all missing teeth (upper and lower).');
INSERT INTO `lang_constants` VALUES (1891, 'Tooth numbers, surfaces, and/or quadrants involved.');
INSERT INTO `lang_constants` VALUES (1892, 'Months of dental treatment remaining.');
INSERT INTO `lang_constants` VALUES (1893, 'Tooth number or letter.');
INSERT INTO `lang_constants` VALUES (1894, 'Dental quadrant/arch.');
INSERT INTO `lang_constants` VALUES (1895, 'Total orthodontic service fee, initial appliance fee, monthly fee, length of service.');
INSERT INTO `lang_constants` VALUES (1896, 'Line information.');
INSERT INTO `lang_constants` VALUES (1897, 'Accident date, state, description and cause.');
INSERT INTO `lang_constants` VALUES (1898, 'Place of service.');
INSERT INTO `lang_constants` VALUES (1899, 'Type of service.');
INSERT INTO `lang_constants` VALUES (1900, 'Total anesthesia minutes.');
INSERT INTO `lang_constants` VALUES (1901, 'Authorization/certification number.');
INSERT INTO `lang_constants` VALUES (1902, 'Procedure/revenue code for service(s) rendered. Please use codes 454 or 455.');
INSERT INTO `lang_constants` VALUES (1903, 'Primary diagnosis code.');
INSERT INTO `lang_constants` VALUES (1904, 'Diagnosis code.');
INSERT INTO `lang_constants` VALUES (1905, 'DRG code(s).');
INSERT INTO `lang_constants` VALUES (1906, 'ADSM-III-R code for services rendered.');
INSERT INTO `lang_constants` VALUES (1907, 'Days/units for procedure/revenue code.');
INSERT INTO `lang_constants` VALUES (1908, 'Frequency of service.');
INSERT INTO `lang_constants` VALUES (1909, 'Length of medical necessity, including begin date.');
INSERT INTO `lang_constants` VALUES (1910, 'Obesity measurements.');
INSERT INTO `lang_constants` VALUES (1911, 'Type of surgery/service for which anesthesia was administered.');
INSERT INTO `lang_constants` VALUES (1912, 'Length of time for services rendered.');
INSERT INTO `lang_constants` VALUES (1913, 'Number of liters/minute & total hours/day for respiratory support.');
INSERT INTO `lang_constants` VALUES (1914, 'Number of lesions excised.');
INSERT INTO `lang_constants` VALUES (1915, 'Facility point of origin and destination - ambulance.');
INSERT INTO `lang_constants` VALUES (1916, 'Number of miles patient was transported.');
INSERT INTO `lang_constants` VALUES (1917, 'Location of durable medical equipment use.');
INSERT INTO `lang_constants` VALUES (1918, 'Length/size of laceration/tumor.');
INSERT INTO `lang_constants` VALUES (1919, 'Subluxation location.');
INSERT INTO `lang_constants` VALUES (1920, 'Number of spine segments.');
INSERT INTO `lang_constants` VALUES (1921, 'Oxygen contents for oxygen system rental.');
INSERT INTO `lang_constants` VALUES (1922, 'Weight.');
INSERT INTO `lang_constants` VALUES (1923, 'Height.');
INSERT INTO `lang_constants` VALUES (1924, 'Claim.');
INSERT INTO `lang_constants` VALUES (1925, 'UB-92/HCFA-1450/HCFA-1500 claim form.');
INSERT INTO `lang_constants` VALUES (1926, 'Paper claim.');
INSERT INTO `lang_constants` VALUES (1927, 'Signed claim form.');
INSERT INTO `lang_constants` VALUES (1928, 'Itemized claim.');
INSERT INTO `lang_constants` VALUES (1929, 'Itemized claim by provider.');
INSERT INTO `lang_constants` VALUES (1930, 'Related confinement claim.');
INSERT INTO `lang_constants` VALUES (1931, 'Copy of prescription.');
INSERT INTO `lang_constants` VALUES (1932, 'Medicare worksheet.');
INSERT INTO `lang_constants` VALUES (1933, 'Copy of Medicare ID card.');
INSERT INTO `lang_constants` VALUES (1934, 'Vouchers/explanation of benefits (EOB).');
INSERT INTO `lang_constants` VALUES (1935, 'Other payer''s Explanation of Benefits/payment information.');
INSERT INTO `lang_constants` VALUES (1936, 'Medical necessity for service.');
INSERT INTO `lang_constants` VALUES (1937, 'Reason for late hospital charges.');
INSERT INTO `lang_constants` VALUES (1938, 'Reason for late discharge.');
INSERT INTO `lang_constants` VALUES (1939, 'Pre-existing information.');
INSERT INTO `lang_constants` VALUES (1940, 'Reason for termination of pregnancy.');
INSERT INTO `lang_constants` VALUES (1941, 'Purpose of family conference/therapy.');
INSERT INTO `lang_constants` VALUES (1942, 'Reason for physical therapy.');
INSERT INTO `lang_constants` VALUES (1943, 'Supporting documentation.');
INSERT INTO `lang_constants` VALUES (1944, 'Attending physician report.');
INSERT INTO `lang_constants` VALUES (1945, 'Nurse''s notes.');
INSERT INTO `lang_constants` VALUES (1946, 'Medical notes/report.');
INSERT INTO `lang_constants` VALUES (1947, 'Operative report.');
INSERT INTO `lang_constants` VALUES (1948, 'Emergency room notes/report.');
INSERT INTO `lang_constants` VALUES (1949, 'Lab/test report/notes/results.');
INSERT INTO `lang_constants` VALUES (1950, 'MRI report.');
INSERT INTO `lang_constants` VALUES (1951, 'Refer to codes 300 for lab notes and 311 for pathology notes');
INSERT INTO `lang_constants` VALUES (1952, 'Physical therapy notes. Please use code 297:6O (6 ''OH'' - not zero)');
INSERT INTO `lang_constants` VALUES (1953, 'Reports for service.');
INSERT INTO `lang_constants` VALUES (1954, 'X-ray reports/interpretation.');
INSERT INTO `lang_constants` VALUES (1955, 'Detailed description of service.');
INSERT INTO `lang_constants` VALUES (1956, 'Narrative with pocket depth chart.');
INSERT INTO `lang_constants` VALUES (1957, 'Discharge summary.');
INSERT INTO `lang_constants` VALUES (1958, 'Code was duplicate of code 299');
INSERT INTO `lang_constants` VALUES (1959, 'Progress notes for the six months prior to statement date.');
INSERT INTO `lang_constants` VALUES (1960, 'Pathology notes/report.');
INSERT INTO `lang_constants` VALUES (1961, 'Dental charting.');
INSERT INTO `lang_constants` VALUES (1962, 'Bridgework information.');
INSERT INTO `lang_constants` VALUES (1963, 'Dental records for this service.');
INSERT INTO `lang_constants` VALUES (1964, 'Past perio treatment history.');
INSERT INTO `lang_constants` VALUES (1965, 'Complete medical history.');
INSERT INTO `lang_constants` VALUES (1966, 'Patient''s medical records.');
INSERT INTO `lang_constants` VALUES (1967, 'X-rays.');
INSERT INTO `lang_constants` VALUES (1968, 'Pre/post-operative x-rays/photographs.');
INSERT INTO `lang_constants` VALUES (1969, 'Study models.');
INSERT INTO `lang_constants` VALUES (1970, 'Radiographs or models.');
INSERT INTO `lang_constants` VALUES (1971, 'Recent fm x-rays.');
INSERT INTO `lang_constants` VALUES (1972, 'Study models, x-rays, and/or narrative.');
INSERT INTO `lang_constants` VALUES (1973, 'Recent x-ray of treatment area and/or narrative.');
INSERT INTO `lang_constants` VALUES (1974, 'Recent fm x-rays and/or narrative.');
INSERT INTO `lang_constants` VALUES (1975, 'Copy of transplant acquisition invoice.');
INSERT INTO `lang_constants` VALUES (1976, 'Periodontal case type diagnosis and recent pocket depth chart with narrative.');
INSERT INTO `lang_constants` VALUES (1977, 'Speech therapy notes. Please use code 297:6R');
INSERT INTO `lang_constants` VALUES (1978, 'Exercise notes.');
INSERT INTO `lang_constants` VALUES (1979, 'Occupational notes.');
INSERT INTO `lang_constants` VALUES (1980, 'History and physical.');
INSERT INTO `lang_constants` VALUES (1981, 'Authorization/certification (include period covered).');
INSERT INTO `lang_constants` VALUES (1982, 'Patient release of information authorization.');
INSERT INTO `lang_constants` VALUES (1983, 'Oxygen certification.');
INSERT INTO `lang_constants` VALUES (1984, 'Durable medical equipment certification.');
INSERT INTO `lang_constants` VALUES (1985, 'Chiropractic certification.');
INSERT INTO `lang_constants` VALUES (1986, 'Ambulance certification/documentation.');
INSERT INTO `lang_constants` VALUES (1987, 'Home health certification. Please use code 332:4Y');
INSERT INTO `lang_constants` VALUES (1988, 'Enteral/parenteral certification.');
INSERT INTO `lang_constants` VALUES (1989, 'Pacemaker certification.');
INSERT INTO `lang_constants` VALUES (1990, 'Private duty nursing certification.');
INSERT INTO `lang_constants` VALUES (1991, 'Podiatric certification.');
INSERT INTO `lang_constants` VALUES (1992, 'Documentation that facility is state licensed and Medicare approved as a surgical facility.');
INSERT INTO `lang_constants` VALUES (1993, 'Documentation that provider of physical therapy is Medicare Part B approved.');
INSERT INTO `lang_constants` VALUES (1994, 'Treatment plan for service/diagnosis');
INSERT INTO `lang_constants` VALUES (1995, 'Proposed treatment plan for next 6 months.');
INSERT INTO `lang_constants` VALUES (1996, 'Refer to code 345 for treatment plan and code 282 for prescription');
INSERT INTO `lang_constants` VALUES (1997, 'Chiropractic treatment plan.');
INSERT INTO `lang_constants` VALUES (1998, 'Psychiatric treatment plan. Please use codes 345:5I, 5J, 5K, 5L, 5M, 5N, 5O (5 ''OH'' - not zero), 5P');
INSERT INTO `lang_constants` VALUES (1999, 'Speech pathology treatment plan. Please use code 345:6R');
INSERT INTO `lang_constants` VALUES (2000, 'Physical/occupational therapy treatment plan. Please use codes 345:6O (6 ''OH'' - not zero), 6N');
INSERT INTO `lang_constants` VALUES (2001, 'Duration of treatment plan.');
INSERT INTO `lang_constants` VALUES (2002, 'Orthodontics treatment plan.');
INSERT INTO `lang_constants` VALUES (2003, 'Treatment plan for replacement of remaining missing teeth.');
INSERT INTO `lang_constants` VALUES (2004, 'Has claim been paid?');
INSERT INTO `lang_constants` VALUES (2005, 'Was blood furnished?');
INSERT INTO `lang_constants` VALUES (2006, 'Has or will blood be replaced?');
INSERT INTO `lang_constants` VALUES (2007, 'Does provider accept assignment of benefits?');
INSERT INTO `lang_constants` VALUES (2008, 'Is there a release of information signature on file?');
INSERT INTO `lang_constants` VALUES (2009, 'Is there an assignment of benefits signature on file?');
INSERT INTO `lang_constants` VALUES (2010, 'Is there other insurance?');
INSERT INTO `lang_constants` VALUES (2011, 'Is the dental patient covered by medical insurance?');
INSERT INTO `lang_constants` VALUES (2012, 'Will worker''s compensation cover submitted charges?');
INSERT INTO `lang_constants` VALUES (2013, 'Is accident/illness/condition employment related?');
INSERT INTO `lang_constants` VALUES (2014, 'Is service the result of an accident?');
INSERT INTO `lang_constants` VALUES (2015, 'Is injury due to auto accident?');
INSERT INTO `lang_constants` VALUES (2016, 'Is service performed for a recurring condition or new condition?');
INSERT INTO `lang_constants` VALUES (2017, 'Is medical doctor (MD) or doctor of osteopath (DO) on staff of this facility?');
INSERT INTO `lang_constants` VALUES (2018, 'Does patient condition preclude use of ordinary bed?');
INSERT INTO `lang_constants` VALUES (2019, 'Can patient operate controls of bed?');
INSERT INTO `lang_constants` VALUES (2020, 'Is patient confined to room?');
INSERT INTO `lang_constants` VALUES (2021, 'Is patient confined to bed?');
INSERT INTO `lang_constants` VALUES (2022, 'Is patient an insulin diabetic?');
INSERT INTO `lang_constants` VALUES (2023, 'Is prescribed lenses a result of cataract surgery?');
INSERT INTO `lang_constants` VALUES (2024, 'Was refraction performed?');
INSERT INTO `lang_constants` VALUES (2025, 'Was charge for ambulance for a round-trip?');
INSERT INTO `lang_constants` VALUES (2026, 'Was durable medical equipment purchased new or used?');
INSERT INTO `lang_constants` VALUES (2027, 'Is pacemaker temporary or permanent?');
INSERT INTO `lang_constants` VALUES (2028, 'Were services performed supervised by a physician?');
INSERT INTO `lang_constants` VALUES (2029, 'Were services performed by a CRNA under appropriate medical direction?');
INSERT INTO `lang_constants` VALUES (2030, 'Is drug generic?');
INSERT INTO `lang_constants` VALUES (2031, 'Did provider authorize generic or brand name dispensing?');
INSERT INTO `lang_constants` VALUES (2032, 'Was nerve block used for surgical procedure or pain management?');
INSERT INTO `lang_constants` VALUES (2033, 'Is prosthesis/crown/inlay placement an initial placement or a replacement?');
INSERT INTO `lang_constants` VALUES (2034, 'Is appliance upper or lower arch & is appliance fixed or removable?');
INSERT INTO `lang_constants` VALUES (2035, 'Is service for orthodontic purposes?');
INSERT INTO `lang_constants` VALUES (2036, 'Date patient last examined by entity');
INSERT INTO `lang_constants` VALUES (2037, 'Date post-operative care assumed');
INSERT INTO `lang_constants` VALUES (2038, 'Date post-operative care relinquished');
INSERT INTO `lang_constants` VALUES (2039, 'Date of most recent medical event necessitating service(s)');
INSERT INTO `lang_constants` VALUES (2040, 'Date(s) dialysis conducted');
INSERT INTO `lang_constants` VALUES (2041, 'Date(s) of blood transfusion(s)');
INSERT INTO `lang_constants` VALUES (2042, 'Date of previous pacemaker check');
INSERT INTO `lang_constants` VALUES (2043, 'Date(s) of most recent hospitalization related to service');
INSERT INTO `lang_constants` VALUES (2044, 'Date entity signed certification/recertification');
INSERT INTO `lang_constants` VALUES (2045, 'Date home dialysis began');
INSERT INTO `lang_constants` VALUES (2046, 'Date of onset/exacerbation of illness/condition');
INSERT INTO `lang_constants` VALUES (2047, 'Visual field test results');
INSERT INTO `lang_constants` VALUES (2048, 'Report of prior testing related to this service, including dates');
INSERT INTO `lang_constants` VALUES (2049, 'Claim is out of balance');
INSERT INTO `lang_constants` VALUES (2050, 'Source of payment is not valid');
INSERT INTO `lang_constants` VALUES (2051, 'Amount must be greater than zero');
INSERT INTO `lang_constants` VALUES (2052, 'Entity referral notes/orders/prescription');
INSERT INTO `lang_constants` VALUES (2053, 'Specific findings, complaints, or symptoms necessitating service');
INSERT INTO `lang_constants` VALUES (2054, 'Summary of services');
INSERT INTO `lang_constants` VALUES (2055, 'Brief medical history as related to service(s)');
INSERT INTO `lang_constants` VALUES (2056, 'Complications/mitigating circumstances');
INSERT INTO `lang_constants` VALUES (2057, 'Initial certification');
INSERT INTO `lang_constants` VALUES (2058, 'Medication logs/records (including medication therapy)');
INSERT INTO `lang_constants` VALUES (2059, 'Explain differences between treatment plan and patient''s condition');
INSERT INTO `lang_constants` VALUES (2060, 'Medical necessity for non-routine service(s)');
INSERT INTO `lang_constants` VALUES (2061, 'Medical records to substantiate decision of non-coverage');
INSERT INTO `lang_constants` VALUES (2062, 'Explain/justify differences between treatment plan and services rendered.');
INSERT INTO `lang_constants` VALUES (2063, 'Need for more than one physician to treat patient');
INSERT INTO `lang_constants` VALUES (2064, 'Justify services outside composite rate');
INSERT INTO `lang_constants` VALUES (2065, 'Verification of patient''s ability to retain and use information');
INSERT INTO `lang_constants` VALUES (2066, 'Prior testing, including result(s) and date(s) as related to service(s)');
INSERT INTO `lang_constants` VALUES (2067, 'Indicating why medications cannot be taken orally');
INSERT INTO `lang_constants` VALUES (2068, 'Individual test(s) comprising the panel and the charges for each test');
INSERT INTO `lang_constants` VALUES (2069, 'Name, dosage and medical justification of contrast material used for radiology procedure');
INSERT INTO `lang_constants` VALUES (2070, 'Medical review attachment/information for service(s)');
INSERT INTO `lang_constants` VALUES (2071, 'Homebound status');
INSERT INTO `lang_constants` VALUES (2072, 'Prognosis');
INSERT INTO `lang_constants` VALUES (2073, 'Statement of non-coverage including itemized bill');
INSERT INTO `lang_constants` VALUES (2074, 'Itemize non-covered services');
INSERT INTO `lang_constants` VALUES (2075, 'All current diagnoses');
INSERT INTO `lang_constants` VALUES (2076, 'Emergency care provided during transport');
INSERT INTO `lang_constants` VALUES (2077, 'Reason for transport by ambulance');
INSERT INTO `lang_constants` VALUES (2078, 'Loaded miles and charges for transport to nearest facility with appropriate services');
INSERT INTO `lang_constants` VALUES (2079, 'Nearest appropriate facility');
INSERT INTO `lang_constants` VALUES (2080, 'Provide condition/functional status at time of service');
INSERT INTO `lang_constants` VALUES (2081, 'Date benefits exhausted');
INSERT INTO `lang_constants` VALUES (2082, 'Copy of patient revocation of hospice benefits');
INSERT INTO `lang_constants` VALUES (2083, 'Reasons for more than one transfer per entitlement period');
INSERT INTO `lang_constants` VALUES (2084, 'Notice of Admission');
INSERT INTO `lang_constants` VALUES (2085, 'Short term goals');
INSERT INTO `lang_constants` VALUES (2086, 'Long term goals');
INSERT INTO `lang_constants` VALUES (2087, 'Number of patients attending session');
INSERT INTO `lang_constants` VALUES (2088, 'Size, depth, amount, and type of drainage wounds');
INSERT INTO `lang_constants` VALUES (2089, 'why non-skilled caregiver has not been taught procedure');
INSERT INTO `lang_constants` VALUES (2090, 'Entity professional qualification for service(s)');
INSERT INTO `lang_constants` VALUES (2091, 'Modalities of service');
INSERT INTO `lang_constants` VALUES (2092, 'Initial evaluation report');
INSERT INTO `lang_constants` VALUES (2093, 'Method used to obtain test sample');
INSERT INTO `lang_constants` VALUES (2094, 'Explain why hearing loss not correctable by hearing aid');
INSERT INTO `lang_constants` VALUES (2095, 'Documentation from prior claim(s) related to service(s)');
INSERT INTO `lang_constants` VALUES (2096, 'Plan of teaching');
INSERT INTO `lang_constants` VALUES (2097, 'Invalid billing combination. See STC12 for details. This code should only be used to indicate an inconsistency between two or more data elements on the claim. A detailed explanation is required in STC12 when this code is used.');
INSERT INTO `lang_constants` VALUES (2098, 'Projected date to discontinue service(s)');
INSERT INTO `lang_constants` VALUES (2099, 'Awaiting spend down determination');
INSERT INTO `lang_constants` VALUES (2100, 'Preoperative and post-operative diagnosis');
INSERT INTO `lang_constants` VALUES (2101, 'Total visits in total number of hours/day and total number of hours/week');
INSERT INTO `lang_constants` VALUES (2102, 'Procedure Code Modifier(s) for Service(s) Rendered');
INSERT INTO `lang_constants` VALUES (2103, 'Procedure code for services rendered.');
INSERT INTO `lang_constants` VALUES (2104, 'Revenue code for services rendered.');
INSERT INTO `lang_constants` VALUES (2105, 'Covered Day(s)');
INSERT INTO `lang_constants` VALUES (2106, 'Non-Covered Day(s)');
INSERT INTO `lang_constants` VALUES (2107, 'Coinsurance Day(s)');
INSERT INTO `lang_constants` VALUES (2108, 'Lifetime Reserve Day(s)');
INSERT INTO `lang_constants` VALUES (2109, 'NUBC Condition Code(s)');
INSERT INTO `lang_constants` VALUES (2110, 'NUBC Occurrence Code(s) and Date(s)');
INSERT INTO `lang_constants` VALUES (2111, 'NUBC Occurrence Span Code(s) and Date(s)');
INSERT INTO `lang_constants` VALUES (2112, 'NUBC Value Code(s) and/or Amount(s)');
INSERT INTO `lang_constants` VALUES (2113, 'Payer Assigned Claim Control Number');
INSERT INTO `lang_constants` VALUES (2114, 'Principal Procedure Code for Service(s) Rendered');
INSERT INTO `lang_constants` VALUES (2115, 'Entities Original Signature');
INSERT INTO `lang_constants` VALUES (2116, 'Entity Signature Date');
INSERT INTO `lang_constants` VALUES (2117, 'Patient Signature Source');
INSERT INTO `lang_constants` VALUES (2118, 'Purchase Service Charge');
INSERT INTO `lang_constants` VALUES (2119, 'Was service purchased from another entity?');
INSERT INTO `lang_constants` VALUES (2120, 'Were services related to an emergency?');
INSERT INTO `lang_constants` VALUES (2121, 'Ambulance Run Sheet');
INSERT INTO `lang_constants` VALUES (2122, 'Missing or invalid lab indicator');
INSERT INTO `lang_constants` VALUES (2123, 'Procedure code and patient gender mismatch');
INSERT INTO `lang_constants` VALUES (2124, 'Procedure code not valid for patient age');
INSERT INTO `lang_constants` VALUES (2125, 'Missing or invalid units of service');
INSERT INTO `lang_constants` VALUES (2126, 'Diagnosis code pointer is missing or invalid');
INSERT INTO `lang_constants` VALUES (2127, 'Claim submitter''s identifier (patient account number) is missing');
INSERT INTO `lang_constants` VALUES (2128, 'Other Carrier payer ID is missing or invalid');
INSERT INTO `lang_constants` VALUES (2129, 'Other Carrier Claim filing indicator is missing or invalid');
INSERT INTO `lang_constants` VALUES (2130, 'Claim/submission format is invalid.');
INSERT INTO `lang_constants` VALUES (2131, 'Date Error, Century Missing');
INSERT INTO `lang_constants` VALUES (2132, 'Maximum coverage amount met or exceeded for benefit period.');
INSERT INTO `lang_constants` VALUES (2133, 'Business Application Currently Not Available');
INSERT INTO `lang_constants` VALUES (2134, 'More information available than can be returned in real time mode. Narrow your current search criteria.');
INSERT INTO `lang_constants` VALUES (2135, 'Principle Procedure Date');
INSERT INTO `lang_constants` VALUES (2136, 'Claim not found, claim should have been submitted to/through ''entity''');
INSERT INTO `lang_constants` VALUES (2137, 'Diagnosis code(s) for the services rendered.');
INSERT INTO `lang_constants` VALUES (2138, 'Attachment Control Number');
INSERT INTO `lang_constants` VALUES (2139, 'Other Procedure Code for Service(s) Rendered');
INSERT INTO `lang_constants` VALUES (2140, 'Entity not eligible for encounter submission');
INSERT INTO `lang_constants` VALUES (2141, 'Other Procedure Date');
INSERT INTO `lang_constants` VALUES (2142, 'Version/Release/Industry ID code not currently supported by information holder');
INSERT INTO `lang_constants` VALUES (2143, 'Real-Time requests not supported by the information holder, resubmit as batch request');
INSERT INTO `lang_constants` VALUES (2144, 'Requests for re-adjudication must reference the newly assigned payer claim control number for this previously adjusted claim. Correct the payer claim control number and re-submit.');
INSERT INTO `lang_constants` VALUES (2145, 'Submitter not approved for electronic claim submissions on behalf of this entity');
INSERT INTO `lang_constants` VALUES (2146, 'Sales tax not paid');
INSERT INTO `lang_constants` VALUES (2147, 'Maximum leave days exhausted');
INSERT INTO `lang_constants` VALUES (2148, 'No rate on file with the payer for this service for this entity');
INSERT INTO `lang_constants` VALUES (2149, 'Entity''s Postal/Zip Code');
INSERT INTO `lang_constants` VALUES (2150, 'Entity''s State/Province');
INSERT INTO `lang_constants` VALUES (2151, 'Entity''s City');
INSERT INTO `lang_constants` VALUES (2152, 'Entity''s Street Address');
INSERT INTO `lang_constants` VALUES (2153, 'Entity''s Last Name');
INSERT INTO `lang_constants` VALUES (2154, 'Entity''s First Name');
INSERT INTO `lang_constants` VALUES (2155, 'Entity is changing processor/clearinghouse. This claim must be submitted to the new processor/clearinghouse');
INSERT INTO `lang_constants` VALUES (2156, 'HCPCS');
INSERT INTO `lang_constants` VALUES (2157, 'ICD9');
INSERT INTO `lang_constants` VALUES (2158, 'E-Code');
INSERT INTO `lang_constants` VALUES (2159, 'Future date');
INSERT INTO `lang_constants` VALUES (2160, 'Invalid character');
INSERT INTO `lang_constants` VALUES (2161, 'Length invalid for receiver''s application system');
INSERT INTO `lang_constants` VALUES (2162, 'HIPPS Rate Code for services Rendered');
INSERT INTO `lang_constants` VALUES (2163, 'Entities Middle Name');
INSERT INTO `lang_constants` VALUES (2164, 'Managed Care review');
INSERT INTO `lang_constants` VALUES (2165, 'Adjudication or Payment Date');
INSERT INTO `lang_constants` VALUES (2166, 'Adjusted Repriced Claim Reference Number');
INSERT INTO `lang_constants` VALUES (2167, 'Adjusted Repriced Line item Reference Number');
INSERT INTO `lang_constants` VALUES (2168, 'Adjustment Amount');
INSERT INTO `lang_constants` VALUES (2169, 'Adjustment Quantity');
INSERT INTO `lang_constants` VALUES (2170, 'Adjustment Reason Code');
INSERT INTO `lang_constants` VALUES (2171, 'Anesthesia Modifying Units');
INSERT INTO `lang_constants` VALUES (2172, 'Anesthesia Unit Count');
INSERT INTO `lang_constants` VALUES (2173, 'Arterial Blood Gas Quantity');
INSERT INTO `lang_constants` VALUES (2174, 'Begin Therapy Date');
INSERT INTO `lang_constants` VALUES (2175, 'Bundled or Unbundled Line Number');
INSERT INTO `lang_constants` VALUES (2176, 'Certification Condition Indicator');
INSERT INTO `lang_constants` VALUES (2177, 'Certification Period Projected Visit Count');
INSERT INTO `lang_constants` VALUES (2178, 'Certification Revision Date');
INSERT INTO `lang_constants` VALUES (2179, 'Claim Adjustment Indicator');
INSERT INTO `lang_constants` VALUES (2180, 'Claim Disproportinate Share Amount');
INSERT INTO `lang_constants` VALUES (2181, 'Claim DRG Amount');
INSERT INTO `lang_constants` VALUES (2182, 'Claim DRG Outlier Amount');
INSERT INTO `lang_constants` VALUES (2183, 'Claim ESRD Payment Amount');
INSERT INTO `lang_constants` VALUES (2184, 'Claim Frequency Code');
INSERT INTO `lang_constants` VALUES (2185, 'Claim Indirect Teaching Amount');
INSERT INTO `lang_constants` VALUES (2186, 'Claim MSP Pass-through Amount');
INSERT INTO `lang_constants` VALUES (2187, 'Claim or Encounter Identifier');
INSERT INTO `lang_constants` VALUES (2188, 'Claim PPS Capital Amount');
INSERT INTO `lang_constants` VALUES (2189, 'Claim PPS Capital Outlier Amount');
INSERT INTO `lang_constants` VALUES (2190, 'Claim Submission Reason Code');
INSERT INTO `lang_constants` VALUES (2191, 'Claim Total Denied Charge Amount');
INSERT INTO `lang_constants` VALUES (2192, 'Clearinghouse or Value Added Network Trace');
INSERT INTO `lang_constants` VALUES (2193, 'Clinical Laboratory Improvement Amendment');
INSERT INTO `lang_constants` VALUES (2194, 'Contract Amount');
INSERT INTO `lang_constants` VALUES (2195, 'Contract Code');
INSERT INTO `lang_constants` VALUES (2196, 'Contract Percentage');
INSERT INTO `lang_constants` VALUES (2197, 'Contract Type Code');
INSERT INTO `lang_constants` VALUES (2198, 'Contract Version Identifier');
INSERT INTO `lang_constants` VALUES (2199, 'Coordination of Benefits Code');
INSERT INTO `lang_constants` VALUES (2200, 'Coordination of Benefits Total Submitted Charge');
INSERT INTO `lang_constants` VALUES (2201, 'Cost Report Day Count');
INSERT INTO `lang_constants` VALUES (2202, 'Covered Amount');
INSERT INTO `lang_constants` VALUES (2203, 'Date Claim Paid');
INSERT INTO `lang_constants` VALUES (2204, 'Delay Reason Code');
INSERT INTO `lang_constants` VALUES (2205, 'Demonstration Project Identifier');
INSERT INTO `lang_constants` VALUES (2206, 'Diagnosis Date');
INSERT INTO `lang_constants` VALUES (2207, 'Discount Amount');
INSERT INTO `lang_constants` VALUES (2208, 'Document Control Identifier');
INSERT INTO `lang_constants` VALUES (2209, 'Entity''s Additional/Secondary Identifier');
INSERT INTO `lang_constants` VALUES (2210, 'Entity''s Contact Name');
INSERT INTO `lang_constants` VALUES (2211, 'Entity''s National Provider Identifier (NPI)');
INSERT INTO `lang_constants` VALUES (2212, 'Entity''s Tax Amount');
INSERT INTO `lang_constants` VALUES (2213, 'EPSDT Indicator');
INSERT INTO `lang_constants` VALUES (2214, 'Estimated Claim Due Amount');
INSERT INTO `lang_constants` VALUES (2215, 'Exception Code');
INSERT INTO `lang_constants` VALUES (2216, 'Facility Code Qualifier');
INSERT INTO `lang_constants` VALUES (2217, 'Family Planning Indicator');
INSERT INTO `lang_constants` VALUES (2218, 'Fixed Format Information');
INSERT INTO `lang_constants` VALUES (2219, 'Free Form Message Text');
INSERT INTO `lang_constants` VALUES (2220, 'Frequency Count');
INSERT INTO `lang_constants` VALUES (2221, 'Frequency Period');
INSERT INTO `lang_constants` VALUES (2222, 'Functional Limitation Code');
INSERT INTO `lang_constants` VALUES (2223, 'HCPCS Payable Amount Home Health');
INSERT INTO `lang_constants` VALUES (2224, 'Homebound Indicator');
INSERT INTO `lang_constants` VALUES (2225, 'Immunization Batch Number');
INSERT INTO `lang_constants` VALUES (2226, 'Industry Code');
INSERT INTO `lang_constants` VALUES (2227, 'Insurance Type Code');
INSERT INTO `lang_constants` VALUES (2228, 'Investigational Device Exemption Identifier');
INSERT INTO `lang_constants` VALUES (2229, 'Last Certification Date');
INSERT INTO `lang_constants` VALUES (2230, 'Last Worked Date');
INSERT INTO `lang_constants` VALUES (2231, 'Lifetime Psychiatric Days Count');
INSERT INTO `lang_constants` VALUES (2232, 'Line Item Charge Amount');
INSERT INTO `lang_constants` VALUES (2233, 'Line Item Control Number');
INSERT INTO `lang_constants` VALUES (2234, 'Line Item Denied Charge or Non-covered Charge');
INSERT INTO `lang_constants` VALUES (2235, 'Line Note Text');
INSERT INTO `lang_constants` VALUES (2236, 'Measurement Reference Identification Code');
INSERT INTO `lang_constants` VALUES (2237, 'Medical Record Number');
INSERT INTO `lang_constants` VALUES (2238, 'Medicare Assignment Code');
INSERT INTO `lang_constants` VALUES (2239, 'Medicare Coverage Indicator');
INSERT INTO `lang_constants` VALUES (2240, 'Medicare Paid at 100% Amount');
INSERT INTO `lang_constants` VALUES (2241, 'Medicare Paid at 80% Amount');
INSERT INTO `lang_constants` VALUES (2242, 'Medicare Section 4081 Indicator');
INSERT INTO `lang_constants` VALUES (2243, 'Mental Status Code');
INSERT INTO `lang_constants` VALUES (2244, 'Monthly Treatment Count');
INSERT INTO `lang_constants` VALUES (2245, 'Non-covered Charge Amount');
INSERT INTO `lang_constants` VALUES (2246, 'Non-payable Professional Component Amount');
INSERT INTO `lang_constants` VALUES (2247, 'Non-payable Professional Component Billed Amount');
INSERT INTO `lang_constants` VALUES (2248, 'Note Reference Code');
INSERT INTO `lang_constants` VALUES (2249, 'Oxygen Saturation Qty');
INSERT INTO `lang_constants` VALUES (2250, 'Oxygen Test Condition Code');
INSERT INTO `lang_constants` VALUES (2251, 'Oxygen Test Date');
INSERT INTO `lang_constants` VALUES (2252, 'Old Capital Amount');
INSERT INTO `lang_constants` VALUES (2253, 'Originator Application Transaction Identifier');
INSERT INTO `lang_constants` VALUES (2254, 'Orthodontic Treatment Months Count');
INSERT INTO `lang_constants` VALUES (2255, 'Paid From Part A Medicare Trust Fund Amount');
INSERT INTO `lang_constants` VALUES (2256, 'Paid From Part B Medicare Trust Fund Amount');
INSERT INTO `lang_constants` VALUES (2257, 'Paid Service Unit Count');
INSERT INTO `lang_constants` VALUES (2258, 'Participation Agreement');
INSERT INTO `lang_constants` VALUES (2259, 'Patient Discharge Facility Type Code');
INSERT INTO `lang_constants` VALUES (2260, 'Peer Review Authorization Number');
INSERT INTO `lang_constants` VALUES (2261, 'Per Day Limit Amount');
INSERT INTO `lang_constants` VALUES (2262, 'Physician Contact Date');
INSERT INTO `lang_constants` VALUES (2263, 'Physician Order Date');
INSERT INTO `lang_constants` VALUES (2264, 'Policy Compliance Code');
INSERT INTO `lang_constants` VALUES (2265, 'Policy Name');
INSERT INTO `lang_constants` VALUES (2266, 'Postage Claimed Amount');
INSERT INTO `lang_constants` VALUES (2267, 'PPS-Capital DSH DRG Amount');
INSERT INTO `lang_constants` VALUES (2268, 'PPS-Capital Exception Amount');
INSERT INTO `lang_constants` VALUES (2269, 'PPS-Capital FSP DRG Amount');
INSERT INTO `lang_constants` VALUES (2270, 'PPS-Capital HSP DRG Amount');
INSERT INTO `lang_constants` VALUES (2271, 'PPS-Capital IME Amount');
INSERT INTO `lang_constants` VALUES (2272, 'PPS-Operating Federal Specific DRG Amount');
INSERT INTO `lang_constants` VALUES (2273, 'PPS-Operating Hospital Specific DRG Amount');
INSERT INTO `lang_constants` VALUES (2274, 'Predetermination of Benefits Identifier');
INSERT INTO `lang_constants` VALUES (2275, 'Pregnancy Indicator');
INSERT INTO `lang_constants` VALUES (2276, 'Pre-Tax Claim Amount');
INSERT INTO `lang_constants` VALUES (2277, 'Pricing Methodology');
INSERT INTO `lang_constants` VALUES (2278, 'Property Casualty Claim Number');
INSERT INTO `lang_constants` VALUES (2279, 'Referring CLIA Number');
INSERT INTO `lang_constants` VALUES (2280, 'Reimbursement Rate');
INSERT INTO `lang_constants` VALUES (2281, 'Reject Reason Code');
INSERT INTO `lang_constants` VALUES (2282, 'Related Causes Code');
INSERT INTO `lang_constants` VALUES (2283, 'Remark Code');
INSERT INTO `lang_constants` VALUES (2284, 'Repriced Approved Ambulatory Patient Group');
INSERT INTO `lang_constants` VALUES (2285, 'Repriced Line Item Reference Number');
INSERT INTO `lang_constants` VALUES (2286, 'Repriced Saving Amount');
INSERT INTO `lang_constants` VALUES (2287, 'Repricing Per Diem or Flat Rate Amount');
INSERT INTO `lang_constants` VALUES (2288, 'Responsibility Amount');
INSERT INTO `lang_constants` VALUES (2289, 'Sales Tax Amount');
INSERT INTO `lang_constants` VALUES (2290, 'Service Adjudication or Payment Date');
INSERT INTO `lang_constants` VALUES (2291, 'Service Authorization Exception Code');
INSERT INTO `lang_constants` VALUES (2292, 'Service Line Paid Amount');
INSERT INTO `lang_constants` VALUES (2293, 'Service Line Rate');
INSERT INTO `lang_constants` VALUES (2294, 'Service Tax Amount');
INSERT INTO `lang_constants` VALUES (2295, 'Ship, Delivery or Calendar Pattern Code');
INSERT INTO `lang_constants` VALUES (2296, 'Shipped Date');
INSERT INTO `lang_constants` VALUES (2297, 'Similar Illness or Symptom Date');
INSERT INTO `lang_constants` VALUES (2298, 'Skilled Nursing Facility Indicator');
INSERT INTO `lang_constants` VALUES (2299, 'Special Program Indicator');
INSERT INTO `lang_constants` VALUES (2300, 'State Industrial Accident Provider Number');
INSERT INTO `lang_constants` VALUES (2301, 'Terms Discount Percentage');
INSERT INTO `lang_constants` VALUES (2302, 'Test Performed Date');
INSERT INTO `lang_constants` VALUES (2303, 'Total Denied Charge Amount');
INSERT INTO `lang_constants` VALUES (2304, 'Total Medicare Paid Amount');
INSERT INTO `lang_constants` VALUES (2305, 'Total Visits Projected This Certification Count');
INSERT INTO `lang_constants` VALUES (2306, 'Total Visits Rendered Count');
INSERT INTO `lang_constants` VALUES (2307, 'Treatment Code');
INSERT INTO `lang_constants` VALUES (2308, 'Unit or Basis for Measurement Code');
INSERT INTO `lang_constants` VALUES (2309, 'Universal Product Number');
INSERT INTO `lang_constants` VALUES (2310, 'Visits Prior to Recertification Date Count CR702');
INSERT INTO `lang_constants` VALUES (2311, 'X-ray Availability Indicator');
INSERT INTO `lang_constants` VALUES (2312, 'Entity''s Group Name');
INSERT INTO `lang_constants` VALUES (2313, 'Orthodontic Banding Date');
INSERT INTO `lang_constants` VALUES (2314, 'Surgery Date');
INSERT INTO `lang_constants` VALUES (2315, 'Surgical Procedure Code');
INSERT INTO `lang_constants` VALUES (2316, 'Real-Time requests not supported by the information holder, do not resubmit');
INSERT INTO `lang_constants` VALUES (2317, 'Missing Endodontics treatment history and prognosis');
INSERT INTO `lang_constants` VALUES (2318, 'Dental service narrative needed.');
INSERT INTO `lang_constants` VALUES (2319, 'No billing system is currently active');
INSERT INTO `lang_constants` VALUES (2320, 'Deductible Amount');
INSERT INTO `lang_constants` VALUES (2321, 'Coinsurance Amount');
INSERT INTO `lang_constants` VALUES (2322, 'Co-payment Amount');
INSERT INTO `lang_constants` VALUES (2323, 'The procedure code is inconsistent with the modifier used or a required modifier is missing');
INSERT INTO `lang_constants` VALUES (2324, 'The procedure code/bill type is inconsistent with the place of service');
INSERT INTO `lang_constants` VALUES (2325, 'The procedure/revenue code is inconsistent with the patients age');
INSERT INTO `lang_constants` VALUES (2326, 'The procedure/revenue code is inconsistent with the patients gender');
INSERT INTO `lang_constants` VALUES (2327, 'The procedure code is inconsistent with the provider type/specialty (taxonomy)');
INSERT INTO `lang_constants` VALUES (2328, 'The diagnosis is inconsistent with the patients age');
INSERT INTO `lang_constants` VALUES (2329, 'The diagnosis is inconsistent with the patients gender');
INSERT INTO `lang_constants` VALUES (2330, 'The diagnosis is inconsistent with the procedure');
INSERT INTO `lang_constants` VALUES (2331, 'The diagnosis is inconsistent with the provider type');
INSERT INTO `lang_constants` VALUES (2332, 'The date of death precedes the date of service');
INSERT INTO `lang_constants` VALUES (2333, 'The date of birth follows the date of service');
INSERT INTO `lang_constants` VALUES (2334, 'Payment adjusted because the submitted authorization number is missing, invalid, or does not apply to the billed services or provider');
INSERT INTO `lang_constants` VALUES (2335, 'Claim/service lacks information which is needed for adjudication. Additional information is supplied using remittance advice remarks codes whenever appropriate');
INSERT INTO `lang_constants` VALUES (2336, 'Payment adjusted because requested information was not provided or was insufficient/incomplete. Additional information is supplied using the remittance advice remarks codes whenever appropriate');
INSERT INTO `lang_constants` VALUES (2337, 'Duplicate claim/service');
INSERT INTO `lang_constants` VALUES (2338, 'Claim denied because this is a work-related injury/illness and thus the liability of the Workers Compensation Carrier');
INSERT INTO `lang_constants` VALUES (2339, 'Claim denied because this injury/illness is covered by the liability carrier');
INSERT INTO `lang_constants` VALUES (2340, 'Claim denied because this injury/illness is the liability of the no-fault carrier');
INSERT INTO `lang_constants` VALUES (2341, 'Payment adjusted because this care may be covered by another payer per coordination of benefits');
INSERT INTO `lang_constants` VALUES (2342, 'Payment adjusted due to the impact of prior payer(s) adjudication including payments and/or adjustments');
INSERT INTO `lang_constants` VALUES (2343, 'Payment for charges adjusted. Charges are covered under a capitation agreement/managed care plan');
INSERT INTO `lang_constants` VALUES (2344, 'Payment denied. Your Stop loss deductible has not been met');
INSERT INTO `lang_constants` VALUES (2345, 'Expenses incurred prior to coverage');
INSERT INTO `lang_constants` VALUES (2346, 'Expenses incurred after coverage terminated');
INSERT INTO `lang_constants` VALUES (2347, 'The time limit for filing has expired');
INSERT INTO `lang_constants` VALUES (2348, 'Claim denied as patient cannot be identified as our insured');
INSERT INTO `lang_constants` VALUES (2349, 'Our records indicate that this dependent is not an eligible dependent as defined');
INSERT INTO `lang_constants` VALUES (2350, 'Claim denied. Insured has no dependent coverage');
INSERT INTO `lang_constants` VALUES (2351, 'Claim denied. Insured has no coverage for newborns');
INSERT INTO `lang_constants` VALUES (2352, 'Lifetime benefit maximum has been reached');
INSERT INTO `lang_constants` VALUES (2353, 'Services not provided or authorized by designated (network/primary care) providers');
INSERT INTO `lang_constants` VALUES (2354, 'Services denied at the time authorization/pre-certification was requested');
INSERT INTO `lang_constants` VALUES (2355, 'Charges do not meet qualifications for emergent/urgent care');
INSERT INTO `lang_constants` VALUES (2356, 'Charges exceed our fee schedule or maximum allowable amount');
INSERT INTO `lang_constants` VALUES (2357, 'Gramm-Rudman reduction');
INSERT INTO `lang_constants` VALUES (2358, 'Prompt-pay discount');
INSERT INTO `lang_constants` VALUES (2359, 'Charges exceed your contracted/ legislated fee arrangement');
INSERT INTO `lang_constants` VALUES (2360, 'These are non-covered services because this is a routine exam or screening procedure done in conjunction with a routine exam');
INSERT INTO `lang_constants` VALUES (2361, 'These are non-covered services because this is not deemed a "medical necessity" by the payer');
INSERT INTO `lang_constants` VALUES (2362, 'These are non-covered services because this is a pre-existing condition');
INSERT INTO `lang_constants` VALUES (2363, 'Services by an immediate relative or a member of the same household are not covered');
INSERT INTO `lang_constants` VALUES (2364, 'Multiple physicians/assistants are not covered in this case');
INSERT INTO `lang_constants` VALUES (2365, 'Claim/service denied because procedure/treatment is deemed experimental/investigational by the payer');
INSERT INTO `lang_constants` VALUES (2366, 'Claim/service denied because procedure/treatment has not been deemed "proven to be effective" by the payer');
INSERT INTO `lang_constants` VALUES (2367, 'Payment denied/reduced because the payer deems the information submitted does not support this level of service, this many services, this length of service, this dosage, or this days supply');
INSERT INTO `lang_constants` VALUES (2368, 'Payment adjusted because treatment was deemed by the payer to have been rendered in an inappropriate or invalid place of service');
INSERT INTO `lang_constants` VALUES (2369, 'Charges are adjusted based on multiple surgery rules or concurrent anesthesia rules');
INSERT INTO `lang_constants` VALUES (2370, 'Charges for outpatient services with this proximity to inpatient services are not covered');
INSERT INTO `lang_constants` VALUES (2371, 'Charges adjusted as penalty for failure to obtain second surgical opinion');
INSERT INTO `lang_constants` VALUES (2372, 'Payment denied/reduced for absence of, or exceeded, pre-certification/authorization');
INSERT INTO `lang_constants` VALUES (2373, 'Blood Deductible');
INSERT INTO `lang_constants` VALUES (2374, 'Day outlier amount');
INSERT INTO `lang_constants` VALUES (2375, 'Cost outlier - Adjustment to compensate for additional costs');
INSERT INTO `lang_constants` VALUES (2376, 'Indirect Medical Education Adjustment');
INSERT INTO `lang_constants` VALUES (2377, 'Direct Medical Education Adjustment');
INSERT INTO `lang_constants` VALUES (2378, 'Disproportionate Share Adjustment');
INSERT INTO `lang_constants` VALUES (2379, 'Non-Covered days/Room charge adjustment');
INSERT INTO `lang_constants` VALUES (2380, 'Interest amount');
INSERT INTO `lang_constants` VALUES (2381, 'Transfer amount');
INSERT INTO `lang_constants` VALUES (2382, 'Adjustment amount represents collection against receivable created in prior overpayment');
INSERT INTO `lang_constants` VALUES (2383, 'Professional fees removed from charges');
INSERT INTO `lang_constants` VALUES (2384, 'Ingredient cost adjustment');
INSERT INTO `lang_constants` VALUES (2385, 'Dispensing fee adjustment');
INSERT INTO `lang_constants` VALUES (2386, 'Processed in Excess of charges');
INSERT INTO `lang_constants` VALUES (2387, 'Benefits adjusted. Plan procedures not followed');
INSERT INTO `lang_constants` VALUES (2388, 'Non-covered charge(s)');
INSERT INTO `lang_constants` VALUES (2389, 'Payment is included in the allowance for another service/procedure');
INSERT INTO `lang_constants` VALUES (2390, 'Payment made to patient/insured/responsible party');
INSERT INTO `lang_constants` VALUES (2391, 'Predetermination: anticipated payment upon completion of services or claim adjudication');
INSERT INTO `lang_constants` VALUES (2392, 'Major Medical Adjustment');
INSERT INTO `lang_constants` VALUES (2393, 'Provider promotional discount (e.g., Senior citizen discount)');
INSERT INTO `lang_constants` VALUES (2394, 'Managed care withholding');
INSERT INTO `lang_constants` VALUES (2395, 'Tax withholding');
INSERT INTO `lang_constants` VALUES (2396, 'Patient payment option/election not in effect');
INSERT INTO `lang_constants` VALUES (2397, 'Claim/service denied because the related or qualifying claim/service was not previously paid or identified on this claim');
INSERT INTO `lang_constants` VALUES (2398, 'Payment adjusted because rent/purchase guidelines were not met');
INSERT INTO `lang_constants` VALUES (2399, 'Claim not covered by this payer/contractor. You must send the claim to the correct payer/contractor');
INSERT INTO `lang_constants` VALUES (2400, 'Billing date predates service date');
INSERT INTO `lang_constants` VALUES (2401, 'Not covered unless the provider accepts assignment');
INSERT INTO `lang_constants` VALUES (2402, 'Payment adjusted as not furnished directly to the patient and/or not documented');
INSERT INTO `lang_constants` VALUES (2403, 'Payment denied because service/procedure was provided outside the United States or as a result of war');
INSERT INTO `lang_constants` VALUES (2404, 'Procedure/product not approved by the Food and Drug Administration');
INSERT INTO `lang_constants` VALUES (2405, 'Payment adjusted as procedure postponed or canceled');
INSERT INTO `lang_constants` VALUES (2406, 'Payment denied. The advance indemnification notice signed by the patient did not comply with requirements');
INSERT INTO `lang_constants` VALUES (2407, 'Payment adjusted because transportation is only covered to the closest facility that can provide the necessary care');
INSERT INTO `lang_constants` VALUES (2408, 'Charges reduced for ESRD network support');
INSERT INTO `lang_constants` VALUES (2409, 'Benefit maximum for this time period or occurrence has been reached');
INSERT INTO `lang_constants` VALUES (2410, 'Patient is covered by a managed care plan');
INSERT INTO `lang_constants` VALUES (2411, 'Indemnification adjustment');
INSERT INTO `lang_constants` VALUES (2412, 'Psychiatric reduction');
INSERT INTO `lang_constants` VALUES (2413, 'Payer refund due to overpayment');
INSERT INTO `lang_constants` VALUES (2414, 'Payer refund amount - not our patient');
INSERT INTO `lang_constants` VALUES (2415, 'Payment adjusted due to a submission/billing error(s). Additional information is supplied using the remittance advice remarks codes whenever appropriate');
INSERT INTO `lang_constants` VALUES (2416, 'Deductible -- Major Medical');
INSERT INTO `lang_constants` VALUES (2417, 'Coinsurance -- Major Medical');
INSERT INTO `lang_constants` VALUES (2418, 'Newborns services are covered in the mothers Allowance');
INSERT INTO `lang_constants` VALUES (2419, 'Payment denied - Prior processing information appears incorrect');
INSERT INTO `lang_constants` VALUES (2420, 'Claim submission fee');
INSERT INTO `lang_constants` VALUES (2421, 'Claim specific negotiated discount');
INSERT INTO `lang_constants` VALUES (2422, 'Prearranged demonstration project adjustment');
INSERT INTO `lang_constants` VALUES (2423, 'The disposition of this claim/service is pending further review');
INSERT INTO `lang_constants` VALUES (2424, 'Technical fees removed from charges');
INSERT INTO `lang_constants` VALUES (2425, 'Claim denied. Interim bills cannot be processed');
INSERT INTO `lang_constants` VALUES (2426, 'Claim Adjusted. Plan procedures of a prior payer were not followed');
INSERT INTO `lang_constants` VALUES (2427, 'Payment/Reduction for Regulatory Surcharges, Assessments, Allowances or Health Related Taxes');
INSERT INTO `lang_constants` VALUES (2428, 'Claim/service denied. Appeal procedures not followed or time limits not met');
INSERT INTO `lang_constants` VALUES (2429, 'Contracted funding agreement - Subscriber is employed by the provider of services');
INSERT INTO `lang_constants` VALUES (2430, 'Patient/Insured health identification number and name do not match');
INSERT INTO `lang_constants` VALUES (2431, 'Claim adjustment because the claim spans eligible and ineligible periods of coverage');
INSERT INTO `lang_constants` VALUES (2432, 'Claim adjusted by the monthly Medicaid patient liability amount');
INSERT INTO `lang_constants` VALUES (2433, 'Portion of payment deferred');
INSERT INTO `lang_constants` VALUES (2434, 'Incentive adjustment, e.g. preferred product/service');
INSERT INTO `lang_constants` VALUES (2435, 'Premium payment withholding');
INSERT INTO `lang_constants` VALUES (2436, 'Payment denied because the diagnosis was invalid for the date(s) of service reported');
INSERT INTO `lang_constants` VALUES (2437, 'Provider contracted/negotiated rate expired or not on file');
INSERT INTO `lang_constants` VALUES (2438, 'Claim/service rejected at this time because information from another provider was not provided or was insufficient/incomplete');
INSERT INTO `lang_constants` VALUES (2439, 'Lifetime benefit maximum has been reached for this service/benefit category');
INSERT INTO `lang_constants` VALUES (2440, 'Payment adjusted because the payer deems the information submitted does not support this level of service');
INSERT INTO `lang_constants` VALUES (2441, 'Payment adjusted because the payer deems the information submitted does not support this many services');
INSERT INTO `lang_constants` VALUES (2442, 'Payment adjusted because the payer deems the information submitted does not support this length of service');
INSERT INTO `lang_constants` VALUES (2443, 'Payment adjusted because the payer deems the information submitted does not support this dosage');
INSERT INTO `lang_constants` VALUES (2444, 'Payment adjusted because the payer deems the information submitted does not support this days supply');
INSERT INTO `lang_constants` VALUES (2445, 'This claim is denied because the patient refused the service/procedure');
INSERT INTO `lang_constants` VALUES (2446, 'Flexible spending account payments');
INSERT INTO `lang_constants` VALUES (2447, 'Payment denied/reduced because service/procedure was provided as a result of an act of war');
INSERT INTO `lang_constants` VALUES (2448, 'Payment denied/reduced because the service/procedure was provided outside of the United States');
INSERT INTO `lang_constants` VALUES (2449, 'Payment denied/reduced because the service/procedure was provided as a result of terrorism');
INSERT INTO `lang_constants` VALUES (2450, 'Payment denied/reduced because injury/illness was the result of an activity that is a benefit exclusion');
INSERT INTO `lang_constants` VALUES (2451, 'Provider performance bonus');
INSERT INTO `lang_constants` VALUES (2452, 'State-mandated Requirement for Property and Casualty, see Claim Payment Remarks Code for specific explanation');
INSERT INTO `lang_constants` VALUES (2453, 'Claim/Service adjusted because the attachment referenced on the claim was not received');
INSERT INTO `lang_constants` VALUES (2454, 'Claim/Service adjusted because the attachment referenced on the claim was not received in a timely fashion');
INSERT INTO `lang_constants` VALUES (2455, 'Payment denied /reduced for absence of, or exceeded referral');
INSERT INTO `lang_constants` VALUES (2456, 'These services were submitted after this payers responsibility for processing claims under this plan ended');
INSERT INTO `lang_constants` VALUES (2457, 'This (these) diagnosis(es) is (are) not covered');
INSERT INTO `lang_constants` VALUES (2458, 'Payment denied as Service(s) have been considered under the patients medical plan. Benefits are not available under this dental plan');
INSERT INTO `lang_constants` VALUES (2459, 'Payment adjusted because an alternate benefit has been provided');
INSERT INTO `lang_constants` VALUES (2460, 'Payment is denied when performed/billed by this type of provider');
INSERT INTO `lang_constants` VALUES (2461, 'Payment is denied when performed/billed by this type of provider in this type of facility');
INSERT INTO `lang_constants` VALUES (2462, 'Payment is adjusted when performed/billed by a provider of this specialty');
INSERT INTO `lang_constants` VALUES (2463, 'Payment adjusted because this service was not prescribed by a physician');
INSERT INTO `lang_constants` VALUES (2464, 'Payment denied because this service was not prescribed prior to delivery');
INSERT INTO `lang_constants` VALUES (2465, 'Payment denied because the prescription is incomplete');
INSERT INTO `lang_constants` VALUES (2466, 'Payment denied because the prescription is not current');
INSERT INTO `lang_constants` VALUES (2467, 'Payment denied because the patient has not met the required eligibility requirements');
INSERT INTO `lang_constants` VALUES (2468, 'Payment adjusted because the patient has not met the required spend down requirements');
INSERT INTO `lang_constants` VALUES (2469, 'Payment adjusted because the patient has not met the required waiting requirements');
INSERT INTO `lang_constants` VALUES (2470, 'Payment adjusted because the patient has not met the required residency requirements');
INSERT INTO `lang_constants` VALUES (2471, 'Payment adjusted because this procedure code was invalid on the date of service');
INSERT INTO `lang_constants` VALUES (2472, 'Payment adjusted because the procedure modifier was invalid on the date of service');
INSERT INTO `lang_constants` VALUES (2473, 'The referring provider is not eligible to refer the service billed');
INSERT INTO `lang_constants` VALUES (2474, 'The prescribing/ordering provider is not eligible to prescribe/order the service billed');
INSERT INTO `lang_constants` VALUES (2475, 'The rendering provider is not eligible to perform the service billed');
INSERT INTO `lang_constants` VALUES (2476, 'Payment adjusted since the level of care changed');
INSERT INTO `lang_constants` VALUES (2477, 'Health Savings account payments');
INSERT INTO `lang_constants` VALUES (2478, 'This product/procedure is only covered when used according to FDA recommendations');
INSERT INTO `lang_constants` VALUES (2479, '"Not otherwise classified" or "unlisted" procedure code (CPT/HCPCS) was billed when there is a specific procedure code for this procedure/service');
INSERT INTO `lang_constants` VALUES (2480, 'Payment is included in the allowance for a Skilled Nursing Facility (SNF) qualified stay');
INSERT INTO `lang_constants` VALUES (2481, 'Claim denied because this is not a work related injury/illness and thus not the liability of the workers compensation carrier');
INSERT INTO `lang_constants` VALUES (2482, 'Non standard adjustment code from paper remittance advice');
INSERT INTO `lang_constants` VALUES (2483, 'Original payment decision is being maintained. This claim was processed properly the first time');
INSERT INTO `lang_constants` VALUES (2484, 'Payment adjusted when anesthesia is performed by the operating physician, the assistant surgeon or the attending physician');
INSERT INTO `lang_constants` VALUES (2485, 'Payment denied/reduced due to a refund issued to an erroneous priority payer for this claim/service');
INSERT INTO `lang_constants` VALUES (2486, 'Patient refund amount');
INSERT INTO `lang_constants` VALUES (2487, 'Claim denied charges');
INSERT INTO `lang_constants` VALUES (2488, 'Contractual adjustment');
INSERT INTO `lang_constants` VALUES (2489, 'Medicare Claim PPS Capital Day Outlier Amount');
INSERT INTO `lang_constants` VALUES (2490, 'Medicare Claim PPS Capital Cost Outlier Amount');
INSERT INTO `lang_constants` VALUES (2491, 'Prior hospitalization or 30 day transfer requirement not met');
INSERT INTO `lang_constants` VALUES (2492, 'Presumptive Payment Adjustment');
INSERT INTO `lang_constants` VALUES (2493, 'Claim denied; ungroupable DRG');
INSERT INTO `lang_constants` VALUES (2494, 'Non-covered visits');
INSERT INTO `lang_constants` VALUES (2495, 'Late filing penalty');
INSERT INTO `lang_constants` VALUES (2496, 'Payment adjusted because coverage/program guidelines were not met or were exceeded');
INSERT INTO `lang_constants` VALUES (2497, 'This provider was not certified/eligible to be paid for this procedure/service on this date of service');
INSERT INTO `lang_constants` VALUES (2498, 'Claim/service not covered/reduced because alternative services were available, and should have been utilized');
INSERT INTO `lang_constants` VALUES (2499, 'Services not covered because the patient is enrolled in a Hospice');
INSERT INTO `lang_constants` VALUES (2500, 'Allowed amount has been reduced because a component of the basic procedure/test was paid. The beneficiary is not liable for more than the charge limit for the basic procedure/test');
INSERT INTO `lang_constants` VALUES (2501, 'The claim/service has been transferred to the proper payer/processor for processing. Claim/service not covered by this payer/processor');
INSERT INTO `lang_constants` VALUES (2502, 'Services not documented in patients medical records');
INSERT INTO `lang_constants` VALUES (2503, 'Previously paid. Payment for this claim/service may have been provided in a previous payment');
INSERT INTO `lang_constants` VALUES (2504, 'Payment denied because only one visit or consultation per physician per day is covered');
INSERT INTO `lang_constants` VALUES (2505, 'Payment adjusted because this procedure/service is not paid separately');
INSERT INTO `lang_constants` VALUES (2506, 'Payment adjusted because "New Patient" qualifications were not met');
INSERT INTO `lang_constants` VALUES (2507, 'Payment adjusted because this procedure code and modifier were invalid on the date of service');
INSERT INTO `lang_constants` VALUES (2508, 'Payment adjusted because procedure/service was partially or fully furnished by another provider');
INSERT INTO `lang_constants` VALUES (2509, 'This payment is adjusted based on the diagnosis');
INSERT INTO `lang_constants` VALUES (2510, 'Payment denied because this provider has failed an aspect of a proficiency testing program');
INSERT INTO `lang_constants` VALUES (2511, 'Claim lacks prior payer payment information');
INSERT INTO `lang_constants` VALUES (2512, 'Claim/Service has invalid non-covered days');
INSERT INTO `lang_constants` VALUES (2513, 'Claim/Service has missing diagnosis information');
INSERT INTO `lang_constants` VALUES (2514, 'Claim/Service lacks Physician/Operative or other supporting documentation');
INSERT INTO `lang_constants` VALUES (2515, 'Claim/Service missing service/product information');
INSERT INTO `lang_constants` VALUES (2516, 'This (these) diagnosis(es) is (are) missing or are invalid');
INSERT INTO `lang_constants` VALUES (2517, 'Workers Compensation State Fee Schedule Adjustment');
INSERT INTO `lang_constants` VALUES (2518, 'EOB Posting - Electronic Remittances');
INSERT INTO `lang_constants` VALUES (2519, 'translate this');
INSERT INTO `lang_constants` VALUES (2520, 'constant name');
INSERT INTO `lang_constants` VALUES (2521, 'Please Note: constants are case sensitive and any string is allowed.');
INSERT INTO `lang_constants` VALUES (2522, 'Multi Language Tool');
INSERT INTO `lang_constants` VALUES (2523, 'Add Language');
INSERT INTO `lang_constants` VALUES (2524, 'Add Constant');
INSERT INTO `lang_constants` VALUES (2525, 'Edit definitions');
INSERT INTO `lang_constants` VALUES (2526, 'Info');
INSERT INTO `lang_constants` VALUES (2527, 'Language definition added');
INSERT INTO `lang_constants` VALUES (2528, 'Language Code');
INSERT INTO `lang_constants` VALUES (2529, 'Language Name');
INSERT INTO `lang_constants` VALUES (2530, 'Edit Facility Information');
INSERT INTO `lang_constants` VALUES (2531, 'Facility Information');
INSERT INTO `lang_constants` VALUES (2532, 'Zip Code');
INSERT INTO `lang_constants` VALUES (2533, 'Federal EIN');
INSERT INTO `lang_constants` VALUES (2534, 'Facility NPI');
INSERT INTO `lang_constants` VALUES (2535, 'Billing Location');
INSERT INTO `lang_constants` VALUES (2536, 'Accepts Assignment');
INSERT INTO `lang_constants` VALUES (2537, 'POS Code');
INSERT INTO `lang_constants` VALUES (2538, 'Billing Attn');
INSERT INTO `lang_constants` VALUES (2539, 'CLIA Number');
INSERT INTO `lang_constants` VALUES (2540, 'Users & Groups');
INSERT INTO `lang_constants` VALUES (2541, 'Practice');
INSERT INTO `lang_constants` VALUES (2542, 'Database');
INSERT INTO `lang_constants` VALUES (2543, 'BatchCom');
INSERT INTO `lang_constants` VALUES (2544, 'Drugs');
INSERT INTO `lang_constants` VALUES (2545, 'Logs');
INSERT INTO `lang_constants` VALUES (2546, 'Exit from Administration');
INSERT INTO `lang_constants` VALUES (2547, 'Password Change');
INSERT INTO `lang_constants` VALUES (2548, 'Once you change your password, you will have to re-login.');
INSERT INTO `lang_constants` VALUES (2549, 'Real Name');
INSERT INTO `lang_constants` VALUES (2550, 'Username');
INSERT INTO `lang_constants` VALUES (2551, 'User & Group Administration');
INSERT INTO `lang_constants` VALUES (2552, 'New Facility Information');
INSERT INTO `lang_constants` VALUES (2553, 'Edit Facilities');
INSERT INTO `lang_constants` VALUES (2554, 'New User');
INSERT INTO `lang_constants` VALUES (2555, 'Groupname');
INSERT INTO `lang_constants` VALUES (2556, 'Authorized');
INSERT INTO `lang_constants` VALUES (2557, 'Default Facility');
INSERT INTO `lang_constants` VALUES (2558, 'Federal Tax ID');
INSERT INTO `lang_constants` VALUES (2559, 'Federal Drug ID');
INSERT INTO `lang_constants` VALUES (2560, 'UPIN');
INSERT INTO `lang_constants` VALUES (2561, 'See Authorizations');
INSERT INTO `lang_constants` VALUES (2562, 'Only Mine');
INSERT INTO `lang_constants` VALUES (2563, 'NPI');
INSERT INTO `lang_constants` VALUES (2564, 'Additional Info');
INSERT INTO `lang_constants` VALUES (2565, 'New Group');
INSERT INTO `lang_constants` VALUES (2566, 'Initial User');
INSERT INTO `lang_constants` VALUES (2567, 'Add User To Group');
INSERT INTO `lang_constants` VALUES (2568, 'User');
INSERT INTO `lang_constants` VALUES (2569, 'Specialty');
INSERT INTO `lang_constants` VALUES (2570, 'Assistant');
INSERT INTO `lang_constants` VALUES (2571, 'Website');
INSERT INTO `lang_constants` VALUES (2572, 'Main Address');
INSERT INTO `lang_constants` VALUES (2573, 'Alt Address');
INSERT INTO `lang_constants` VALUES (2574, 'Address Book');
INSERT INTO `lang_constants` VALUES (2575, 'First Name:');
INSERT INTO `lang_constants` VALUES (2576, 'Last Name:');
INSERT INTO `lang_constants` VALUES (2577, 'Specialty:');
INSERT INTO `lang_constants` VALUES (2578, 'Local');
INSERT INTO `lang_constants` VALUES (2579, 'Mobile');
INSERT INTO `lang_constants` VALUES (2580, 'Postal');
INSERT INTO `lang_constants` VALUES (2581, 'User Administration');
INSERT INTO `lang_constants` VALUES (2582, 'CSV File');
INSERT INTO `lang_constants` VALUES (2583, 'Phone call list');
INSERT INTO `lang_constants` VALUES (2584, 'Any');
INSERT INTO `lang_constants` VALUES (2585, 'Appointment Date');
INSERT INTO `lang_constants` VALUES (2586, 'Date format for "appointment start" is not valid');
INSERT INTO `lang_constants` VALUES (2587, 'Date format for "appointment end" is not valid');
INSERT INTO `lang_constants` VALUES (2588, 'Date format for "seen since" is not valid');
INSERT INTO `lang_constants` VALUES (2589, 'Date format for "not seen since" is not valid');
INSERT INTO `lang_constants` VALUES (2590, 'Age format for "age from" is not valid');
INSERT INTO `lang_constants` VALUES (2591, 'Age format for "age up to" is not valid');
INSERT INTO `lang_constants` VALUES (2592, 'Error in "Gender" selection');
INSERT INTO `lang_constants` VALUES (2593, 'Error in "Process" selection');
INSERT INTO `lang_constants` VALUES (2594, 'Error in "HIPAA" selection');
INSERT INTO `lang_constants` VALUES (2595, 'Error in "Sort By" selection');
INSERT INTO `lang_constants` VALUES (2596, 'Error in YES or NO option');
INSERT INTO `lang_constants` VALUES (2597, 'No results, please tray again.');
INSERT INTO `lang_constants` VALUES (2598, 'Batch Communication Tool');
INSERT INTO `lang_constants` VALUES (2599, 'Process');
INSERT INTO `lang_constants` VALUES (2600, 'Overwrite HIPAA choice');
INSERT INTO `lang_constants` VALUES (2601, 'Age From');
INSERT INTO `lang_constants` VALUES (2602, 'Gender');
INSERT INTO `lang_constants` VALUES (2603, 'Sort by');
INSERT INTO `lang_constants` VALUES (2604, 'Fill here only if sending email notification to patients');
INSERT INTO `lang_constants` VALUES (2605, 'Email Sender');
INSERT INTO `lang_constants` VALUES (2606, 'Email Subject');
INSERT INTO `lang_constants` VALUES (2607, 'Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***');
INSERT INTO `lang_constants` VALUES (2608, 'Work');
INSERT INTO `lang_constants` VALUES (2609, 'Contact');
INSERT INTO `lang_constants` VALUES (2610, 'Email from Batchcom');
INSERT INTO `lang_constants` VALUES (2611, 'Could not send email due to a server problem');
INSERT INTO `lang_constants` VALUES (2612, 'emails not sent');
INSERT INTO `lang_constants` VALUES (2613, 'Logged out.');
INSERT INTO `lang_constants` VALUES (2614, 'This page will inline include the login page, so that we do not have to click relogin every time.');
INSERT INTO `lang_constants` VALUES (2615, 'Relogin');
INSERT INTO `lang_constants` VALUES (2616, 'Sunday');
INSERT INTO `lang_constants` VALUES (2617, 'Monday');
INSERT INTO `lang_constants` VALUES (2618, 'Tuesday');
INSERT INTO `lang_constants` VALUES (2619, 'Wednesday');
INSERT INTO `lang_constants` VALUES (2620, 'Thursday');
INSERT INTO `lang_constants` VALUES (2621, 'Friday');
INSERT INTO `lang_constants` VALUES (2622, 'Saturday');
INSERT INTO `lang_constants` VALUES (2623, 'January');
INSERT INTO `lang_constants` VALUES (2624, 'February');
INSERT INTO `lang_constants` VALUES (2625, 'March');
INSERT INTO `lang_constants` VALUES (2626, 'April');
INSERT INTO `lang_constants` VALUES (2627, 'May');
INSERT INTO `lang_constants` VALUES (2628, 'June');
INSERT INTO `lang_constants` VALUES (2629, 'July');
INSERT INTO `lang_constants` VALUES (2630, 'August');
INSERT INTO `lang_constants` VALUES (2631, 'September');
INSERT INTO `lang_constants` VALUES (2632, 'October');
INSERT INTO `lang_constants` VALUES (2633, 'November');
INSERT INTO `lang_constants` VALUES (2634, 'December');
INSERT INTO `lang_constants` VALUES (2635, 'Primary Provider:');
INSERT INTO `lang_constants` VALUES (2636, 'b.i.d.');
INSERT INTO `lang_constants` VALUES (2637, 'Find Available');
INSERT INTO `lang_constants` VALUES (2638, 'minutes');
INSERT INTO `lang_constants` VALUES (2639, 'q.d.');
-- larry :: extra -24/04/2008
INSERT INTO `lang_constants`  VALUES (2640, 'Go');
INSERT INTO `lang_constants`  VALUES (2641, 'q.8h');
INSERT INTO `lang_constants`  VALUES (2642, 'q.6h');
INSERT INTO `lang_constants`  VALUES (2643, 'q.5h');
INSERT INTO `lang_constants`  VALUES (2644, 'q.4h');
INSERT INTO `lang_constants`  VALUES (2645, 'q.3h');
INSERT INTO `lang_constants`  VALUES (2646, 't.i.d.');
INSERT INTO `lang_constants`  VALUES (2647, 'Quantity');
INSERT INTO `lang_constants`  VALUES (2648, 'Medicine Units');
INSERT INTO `lang_constants`  VALUES (2649, 'Substitution');
INSERT INTO `lang_constants`  VALUES (2650, 'substitution allowed');
INSERT INTO `lang_constants`  VALUES (2651, 'substitution not allowed');
INSERT INTO `lang_constants`  VALUES (2652, '# of tablets:');
INSERT INTO `lang_constants`  VALUES (2653, 'Add to Medication List');
INSERT INTO `lang_constants`  VALUES (2654, 'Prescribe');
INSERT INTO `lang_constants`  VALUES (2655, 'No Prescriptions Found');
INSERT INTO `lang_constants`  VALUES (2656, 'Dosage');
INSERT INTO `lang_constants`  VALUES (2657, 'Print Multiple');
INSERT INTO `lang_constants`  VALUES (2658, 'Starting Date');
INSERT INTO `lang_constants`  VALUES (2659, 'Drug Lookup');
INSERT INTO `lang_constants`  VALUES (2660, 'Click to download');
INSERT INTO `lang_constants`  VALUES (2661, 'Add Note');
INSERT INTO `lang_constants`  VALUES (2662, 'Move');
INSERT INTO `lang_constants`  VALUES (2663, 'Move Document to Category:');
INSERT INTO `lang_constants`  VALUES (2664, 'married');
INSERT INTO `lang_constants`  VALUES (2665, 'Upload');
INSERT INTO `lang_constants`  VALUES (2666, 'Upload Document to category');
INSERT INTO `lang_constants`  VALUES (2667, 'Day View');
INSERT INTO `lang_constants`  VALUES (2668, 'Week View');
INSERT INTO `lang_constants`  VALUES (2669, 'Month View');
INSERT INTO `lang_constants`  VALUES (2670, 'Year View');
INSERT INTO `lang_constants`  VALUES (2671, 'Chart Note');
INSERT INTO `lang_constants`  VALUES (2672, 'Single');
INSERT INTO `lang_constants`  VALUES (2673, 'Divorced');
INSERT INTO `lang_constants`  VALUES (2674, 'Widowed');
INSERT INTO `lang_constants`  VALUES (2675, 'Separated');
INSERT INTO `lang_constants`  VALUES (2676, 'Domestic partner');
INSERT INTO `lang_constants`  VALUES (2677, 'Take');
INSERT INTO `lang_constants`  VALUES (2678, 'Primary care provider');
INSERT INTO `lang_constants`  VALUES (2679, 'Currently Active');
INSERT INTO `lang_constants`  VALUES (2680, 'Created');
INSERT INTO `lang_constants`  VALUES (2681, 'Changed');
INSERT INTO `lang_constants`  VALUES (2682, 'Print Multiple');
INSERT INTO `lang_constants`  VALUES (2683, 'Select');

-- --------------------------------------------------------

-- 
-- Table structure for table `lang_definitions`
-- 

DROP TABLE IF EXISTS `lang_definitions`;
CREATE TABLE `lang_definitions` (
  `def_id` int(11) NOT NULL auto_increment,
  `cons_id` int(11) NOT NULL default '0',
  `lang_id` int(11) NOT NULL default '0',
  `definition` mediumtext character set utf8 collate utf8_unicode_ci,
  UNIQUE KEY `def_id` (`def_id`),
  KEY `definition` (`definition`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=174 ;

-- 
-- Dumping data for table `lang_definitions`
-- 

INSERT INTO `lang_definitions` VALUES (1, 6, 3, 'Grupo');
INSERT INTO `lang_definitions` VALUES (2, 8, 3, 'Usuario:');
INSERT INTO `lang_definitions` VALUES (5, 10, 3, 'EOB Posting - Instrucciones');
INSERT INTO `lang_definitions` VALUES (6, 11, 3, 'Codigo Postal');
INSERT INTO `lang_definitions` VALUES (7, 12, 3, 'Apellido');
INSERT INTO `lang_definitions` VALUES (8, 18, 3, 'Hasta:');
INSERT INTO `lang_definitions` VALUES (9, 17, 3, 'Edades desde:');
INSERT INTO `lang_definitions` VALUES (10, 15, 3, 'Fecha de Cita');
INSERT INTO `lang_definitions` VALUES (11, 16, 3, 'Herramienta de Comunicaciones');
INSERT INTO `lang_definitions` VALUES (12, 14, 3, 'Femenino');
INSERT INTO `lang_definitions` VALUES (13, 13, 3, 'Masculino');
INSERT INTO `lang_definitions` VALUES (14, 19, 3, 'Seguro Social');
INSERT INTO `lang_definitions` VALUES (15, 20, 3, 'Administracion de Usuarios y Grupos');
INSERT INTO `lang_definitions` VALUES (16, 21, 3, 'Salir');
INSERT INTO `lang_definitions` VALUES (17, 22, 3, 'Contraseña:');
INSERT INTO `lang_definitions` VALUES (18, 23, 3, 'Entrar');
INSERT INTO `lang_definitions` VALUES (19, 24, 3, 'Administración');
INSERT INTO `lang_definitions` VALUES (20, 25, 3, 'Contraseña');
INSERT INTO `lang_definitions` VALUES (21, 28, 3, 'Facturación');
INSERT INTO `lang_definitions` VALUES (22, 29, 3, 'Inicio');
INSERT INTO `lang_definitions` VALUES (23, 27, 3, 'Notas');
INSERT INTO `lang_definitions` VALUES (24, 26, 3, 'Informes');
INSERT INTO `lang_definitions` VALUES (25, 32, 3, '(Más)');
INSERT INTO `lang_definitions` VALUES (26, 31, 3, 'Autorizaciónes');
INSERT INTO `lang_definitions` VALUES (27, 33, 3, 'Localizar Paciente');
INSERT INTO `lang_definitions` VALUES (28, 30, 3, 'Notas de Paciente');
INSERT INTO `lang_definitions` VALUES (29, 34, 3, 'Paciente Nuevo');
INSERT INTO `lang_definitions` VALUES (30, 37, 3, 'Fecha Nacimiento');
INSERT INTO `lang_definitions` VALUES (31, 36, 3, 'Num Expediente');
INSERT INTO `lang_definitions` VALUES (32, 35, 3, 'Nombre');
INSERT INTO `lang_definitions` VALUES (33, 38, 3, 'y');
INSERT INTO `lang_definitions` VALUES (34, 39, 3, 'Cita(s) del Paciente');
INSERT INTO `lang_definitions` VALUES (35, 40, 3, '(Notas y Autorizaciones)');
INSERT INTO `lang_definitions` VALUES (36, 41, 3, 'Búsqueda');
INSERT INTO `lang_definitions` VALUES (37, 42, 3, 'Categoría');
INSERT INTO `lang_definitions` VALUES (38, 43, 3, 'Fecha');
INSERT INTO `lang_definitions` VALUES (39, 44, 3, 'Título');
INSERT INTO `lang_definitions` VALUES (40, 45, 3, 'Paciente');
INSERT INTO `lang_definitions` VALUES (41, 46, 3, 'Médico');
INSERT INTO `lang_definitions` VALUES (42, 47, 3, 'Notas');
INSERT INTO `lang_definitions` VALUES (43, 48, 3, 'Duración');
INSERT INTO `lang_definitions` VALUES (44, 49, 3, 'Expedientes Localizado');
INSERT INTO `lang_definitions` VALUES (45, 50, 3, '(Paciente Nuevo)');
INSERT INTO `lang_definitions` VALUES (46, 51, 3, 'Día completo');
INSERT INTO `lang_definitions` VALUES (47, 52, 3, 'Hora');
INSERT INTO `lang_definitions` VALUES (48, 53, 3, 'Minutos');
INSERT INTO `lang_definitions` VALUES (49, 54, 3, 'Día');
INSERT INTO `lang_definitions` VALUES (50, 55, 3, 'Repite');
INSERT INTO `lang_definitions` VALUES (51, 56, 3, 'Hasta');
INSERT INTO `lang_definitions` VALUES (52, 57, 3, 'Ver Todos');
INSERT INTO `lang_definitions` VALUES (53, 58, 3, 'Mío');
INSERT INTO `lang_definitions` VALUES (54, 59, 3, 'Localizar Paciente');
INSERT INTO `lang_definitions` VALUES (55, 60, 3, 'Favor de ingresar datos');
INSERT INTO `lang_definitions` VALUES (56, 61, 3, 'Escoja Paciente');
INSERT INTO `lang_definitions` VALUES (57, 62, 3, 'por');
INSERT INTO `lang_definitions` VALUES (58, 63, 3, 'Apellido');
INSERT INTO `lang_definitions` VALUES (59, 64, 3, 'Volver');
INSERT INTO `lang_definitions` VALUES (60, 65, 3, 'Primer Nombre');
INSERT INTO `lang_definitions` VALUES (61, 66, 3, 'Segundo Nombre');
INSERT INTO `lang_definitions` VALUES (62, 67, 3, 'Número de Expediente');
INSERT INTO `lang_definitions` VALUES (63, 68, 3, 'Omitir para Autoasignación');
INSERT INTO `lang_definitions` VALUES (64, 69, 3, 'Usuario');
INSERT INTO `lang_definitions` VALUES (65, 70, 3, 'Información de Nuevas Facilidades');
INSERT INTO `lang_definitions` VALUES (66, 71, 3, 'País');
INSERT INTO `lang_definitions` VALUES (67, 72, 3, 'Dirección');
INSERT INTO `lang_definitions` VALUES (69, 74, 3, 'Estado');
INSERT INTO `lang_definitions` VALUES (70, 75, 3, 'Ciudad');
INSERT INTO `lang_definitions` VALUES (71, 76, 3, 'Seguro Social Patronal');
INSERT INTO `lang_definitions` VALUES (72, 77, 3, 'Acepta Facturación');
INSERT INTO `lang_definitions` VALUES (73, 78, 3, 'Centro de Facturación');
INSERT INTO `lang_definitions` VALUES (74, 79, 3, 'Aplicable si es Centro de Facturación');
INSERT INTO `lang_definitions` VALUES (75, 80, 3, 'Codígo de Lugar de Servicío');
INSERT INTO `lang_definitions` VALUES (76, 81, 3, 'Facturas a Nombre');
INSERT INTO `lang_definitions` VALUES (77, 82, 3, 'Código de Laboratorio Clínico');
INSERT INTO `lang_definitions` VALUES (78, 83, 3, 'Teléfono');
INSERT INTO `lang_definitions` VALUES (79, 84, 3, 'como');
INSERT INTO `lang_definitions` VALUES (80, 85, 3, 'Editar Información de Facilidades');
INSERT INTO `lang_definitions` VALUES (81, 86, 3, 'Información de Facilidades');
INSERT INTO `lang_definitions` VALUES (82, 87, 3, 'Actualizar');
INSERT INTO `lang_definitions` VALUES (83, 88, 3, 'Administracion de Usuarios');
INSERT INTO `lang_definitions` VALUES (84, 89, 3, 'Usuario');
INSERT INTO `lang_definitions` VALUES (85, 90, 3, 'Autorizado');
INSERT INTO `lang_definitions` VALUES (86, 91, 3, 'Número de Identifcación Federal');
INSERT INTO `lang_definitions` VALUES (87, 92, 3, 'Ver Autorizaciones');
INSERT INTO `lang_definitions` VALUES (88, 93, 3, 'Ninguno');
INSERT INTO `lang_definitions` VALUES (89, 94, 3, 'Mio Solamente');
INSERT INTO `lang_definitions` VALUES (90, 95, 3, 'Todos');
INSERT INTO `lang_definitions` VALUES (91, 96, 3, 'Información Addicional');
INSERT INTO `lang_definitions` VALUES (92, 97, 3, 'Guardar Cambios');
INSERT INTO `lang_definitions` VALUES (93, 98, 3, 'Dejar en Blanco para no cambiar contraseña');
INSERT INTO `lang_definitions` VALUES (94, 99, 3, 'Nombre de Clínica');
INSERT INTO `lang_definitions` VALUES (95, 100, 3, 'Nombre de Grupo');
INSERT INTO `lang_definitions` VALUES (96, 101, 3, 'Usuario Inicial');
INSERT INTO `lang_definitions` VALUES (97, 102, 3, 'Usuario');
INSERT INTO `lang_definitions` VALUES (98, 103, 3, 'Modificar');
INSERT INTO `lang_definitions` VALUES (99, 104, 3, 'Nombre Real');
INSERT INTO `lang_definitions` VALUES (100, 105, 3, 'Información');
INSERT INTO `lang_definitions` VALUES (101, 106, 3, 'si');
INSERT INTO `lang_definitions` VALUES (102, 107, 3, 'Salir de Administración');
INSERT INTO `lang_definitions` VALUES (103, 108, 3, 'Ver Registro');
INSERT INTO `lang_definitions` VALUES (104, 109, 3, 'Registro');
INSERT INTO `lang_definitions` VALUES (105, 110, 3, 'Idioma');
INSERT INTO `lang_definitions` VALUES (106, 111, 3, 'Base de Datos');
INSERT INTO `lang_definitions` VALUES (107, 116, 3, 'Communicación');
INSERT INTO `lang_definitions` VALUES (108, 112, 3, 'Calendario');
INSERT INTO `lang_definitions` VALUES (109, 114, 3, 'Formularios');
INSERT INTO `lang_definitions` VALUES (110, 113, 3, 'Consultorio');
INSERT INTO `lang_definitions` VALUES (111, 115, 3, 'Usuarios y Grupos');
INSERT INTO `lang_definitions` VALUES (112, 117, 3, 'Modificar Facilidades');
INSERT INTO `lang_definitions` VALUES (113, 118, 3, 'Usuario Nuevo');
INSERT INTO `lang_definitions` VALUES (114, 119, 3, 'Grupo Nuevo');
INSERT INTO `lang_definitions` VALUES (115, 120, 3, 'Agrega Usuario a Grupo');
INSERT INTO `lang_definitions` VALUES (116, 121, 3, 'Día Laboral');
INSERT INTO `lang_definitions` VALUES (117, 123, 3, 'mes');
INSERT INTO `lang_definitions` VALUES (118, 122, 3, 'semana');
INSERT INTO `lang_definitions` VALUES (119, 124, 3, 'año');
INSERT INTO `lang_definitions` VALUES (120, 126, 3, '2ndo');
INSERT INTO `lang_definitions` VALUES (121, 127, 3, '3er');
INSERT INTO `lang_definitions` VALUES (122, 128, 3, '4to');
INSERT INTO `lang_definitions` VALUES (123, 129, 3, '5to');
INSERT INTO `lang_definitions` VALUES (124, 130, 3, '6to');
INSERT INTO `lang_definitions` VALUES (125, 125, 3, 'cada');
INSERT INTO `lang_definitions` VALUES (126, 131, 3, 'Localizar');
INSERT INTO `lang_definitions` VALUES (127, 132, 3, 'Cancelar');
INSERT INTO `lang_definitions` VALUES (128, 133, 3, 'Guardar');
INSERT INTO `lang_definitions` VALUES (129, 134, 3, 'Eliminar');
INSERT INTO `lang_definitions` VALUES (130, 135, 3, 'Registro');
INSERT INTO `lang_definitions` VALUES (131, 136, 3, 'Evento');
INSERT INTO `lang_definitions` VALUES (132, 137, 3, 'Atención');
INSERT INTO `lang_definitions` VALUES (133, 138, 3, 'Registrado');
INSERT INTO `lang_definitions` VALUES (134, 139, 3, 'No Registrado');
INSERT INTO `lang_definitions` VALUES (135, 140, 3, 'Administración de Formularios');
INSERT INTO `lang_definitions` VALUES (136, 142, 3, 'desactivado');
INSERT INTO `lang_definitions` VALUES (137, 141, 3, 'activado');
INSERT INTO `lang_definitions` VALUES (138, 143, 3, 'Instalado');
INSERT INTO `lang_definitions` VALUES (139, 144, 3, 'PHP descomprimido');
INSERT INTO `lang_definitions` VALUES (140, 145, 3, 'Instalar');
INSERT INTO `lang_definitions` VALUES (141, 146, 3, 'Informe de Facturación');
INSERT INTO `lang_definitions` VALUES (142, 147, 3, 'Notas Oficiales');
INSERT INTO `lang_definitions` VALUES (143, 149, 3, 'Activos');
INSERT INTO `lang_definitions` VALUES (144, 150, 3, 'Inactivos');
INSERT INTO `lang_definitions` VALUES (145, 148, 3, 'Ver');
INSERT INTO `lang_definitions` VALUES (146, 151, 3, 'Modificar Actividad');
INSERT INTO `lang_definitions` VALUES (147, 152, 3, 'Agregar Nota Nueva');
INSERT INTO `lang_definitions` VALUES (148, 153, 3, 'Modificar Contraseña');
INSERT INTO `lang_definitions` VALUES (149, 154, 3, 'Al cambiar su contraseña, Re-valida.');
INSERT INTO `lang_definitions` VALUES (150, 155, 3, 'Nuevamente');
INSERT INTO `lang_definitions` VALUES (151, 156, 3, 'O');
INSERT INTO `lang_definitions` VALUES (152, 157, 3, 'Sexo');
INSERT INTO `lang_definitions` VALUES (153, 158, 3, 'Formato');
INSERT INTO `lang_definitions` VALUES (154, 159, 3, 'Citado entre');
INSERT INTO `lang_definitions` VALUES (155, 160, 3, 'Visto desde');
INSERT INTO `lang_definitions` VALUES (156, 161, 3, 'No visto desde');
INSERT INTO `lang_definitions` VALUES (157, 162, 3, 'Organizado por');
INSERT INTO `lang_definitions` VALUES (158, 163, 3, 'Correo Electronico por');
INSERT INTO `lang_definitions` VALUES (159, 164, 3, 'Tema de Correo Electronico');
INSERT INTO `lang_definitions` VALUES (160, 165, 3, 'Texto de Correo Electronico, Utiliza formato de Tag: ***NAME*** , eg. Saludos ***NAME***');
INSERT INTO `lang_definitions` VALUES (161, 166, 3, 'Processo Lento');
INSERT INTO `lang_definitions` VALUES (162, 167, 3, 'Solamente para uso de  Correo Electronico a pacientes');
INSERT INTO `lang_definitions` VALUES (163, 168, 3, 'Edad desde');
INSERT INTO `lang_definitions` VALUES (164, 169, 3, 'Hasta');
INSERT INTO `lang_definitions` VALUES (165, 171, 3, 'Contacto');
INSERT INTO `lang_definitions` VALUES (166, 172, 3, 'No enviado por error con servidor');
INSERT INTO `lang_definitions` VALUES (167, 173, 3, 'Correo Electronico no enviado');
INSERT INTO `lang_definitions` VALUES (168, 170, 3, 'Empleo');
INSERT INTO `lang_definitions` VALUES (169, 174, 3, 'Ignorar HIPAA');
INSERT INTO `lang_definitions` VALUES (170, 175, 3, 'Archivo CSV');
INSERT INTO `lang_definitions` VALUES (171, 177, 3, 'Correo Electrónico');
INSERT INTO `lang_definitions` VALUES (172, 176, 3, 'Lista de llamadas telefónicas');
INSERT INTO `lang_definitions` VALUES (173, 178, 3, 'Cualquier');

-- --------------------------------------------------------

-- 
-- Table structure for table `lang_languages`
-- 

DROP TABLE IF EXISTS `lang_languages`;
CREATE TABLE `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) character set latin1 NOT NULL default '',
  `lang_description` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `lang_languages`
-- 

INSERT INTO `lang_languages` VALUES (1, 'en', 'English');
INSERT INTO `lang_languages` VALUES (2, 'se', 'Swedish');
INSERT INTO `lang_languages` VALUES (3, 'es', 'Spanish');
INSERT INTO `lang_languages` VALUES (4, 'de', 'German');
INSERT INTO `lang_languages` VALUES (5, 'du', 'Dutch');
INSERT INTO `lang_languages` VALUES (6, 'he', 'Hebrew');

-- --------------------------------------------------------

-- 
-- Table structure for table `layout_options`
-- 

DROP TABLE IF EXISTS `layout_options`;
CREATE TABLE `layout_options` (
  `form_id` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `field_id` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `group_name` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(63) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `seq` int(11) NOT NULL default '0',
  `data_type` tinyint(3) NOT NULL default '0',
  `uor` tinyint(1) NOT NULL default '1',
  `fld_length` int(11) NOT NULL default '15',
  `max_length` int(11) NOT NULL default '0',
  `list_id` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `titlecols` tinyint(3) NOT NULL default '1',
  `datacols` tinyint(3) NOT NULL default '1',
  `default_value` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `edit_options` varchar(36) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `description` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`form_id`,`field_id`,`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `layout_options`
-- 

INSERT INTO `layout_options` VALUES ('DEM', 'title', '1Who', 'Name', 1, 1, 1, 0, 0, 'titles', 1, 1, '', '', 'Title');
INSERT INTO `layout_options` VALUES ('DEM', 'fname', '1Who', '', 2, 2, 2, 10, 63, '', 0, 0, '', 'C', 'First Name');
INSERT INTO `layout_options` VALUES ('DEM', 'mname', '1Who', '', 3, 2, 1, 2, 63, '', 0, 0, '', 'C', 'Middle Name');
INSERT INTO `layout_options` VALUES ('DEM', 'lname', '1Who', '', 4, 2, 2, 10, 63, '', 0, 0, '', 'C', 'Last Name');
INSERT INTO `layout_options` VALUES ('DEM', 'pubpid', '1Who', 'External ID', 5, 2, 1, 10, 15, '', 1, 1, '', '', 'External identifier');
INSERT INTO `layout_options` VALUES ('DEM', 'DOB', '1Who', 'DOB', 6, 4, 2, 10, 10, '', 1, 1, '', 'D', 'Date of Birth');
INSERT INTO `layout_options` VALUES ('DEM', 'sex', '1Who', 'Sex', 7, 1, 2, 0, 0, 'sex', 1, 1, '', '', 'Sex');
INSERT INTO `layout_options` VALUES ('DEM', 'ss', '1Who', 'S.S.', 8, 2, 1, 11, 11, '', 1, 1, '', '', 'Social Security Number');
INSERT INTO `layout_options` VALUES ('DEM', 'drivers_license', '1Who', 'License/ID', 9, 2, 1, 15, 63, '', 1, 1, '', '', 'Drivers License or State ID');
INSERT INTO `layout_options` VALUES ('DEM', 'status', '1Who', 'Marital Status', 10, 1, 1, 0, 0, 'marital', 1, 3, '', '', 'Marital Status');
INSERT INTO `layout_options` VALUES ('DEM', 'genericname1', '1Who', 'User Defined', 11, 2, 1, 15, 63, '', 1, 3, '', '', 'User Defined Field');
INSERT INTO `layout_options` VALUES ('DEM', 'genericval1', '1Who', '', 12, 2, 1, 15, 63, '', 0, 0, '', '', 'User Defined Field');
INSERT INTO `layout_options` VALUES ('DEM', 'genericname2', '1Who', '', 13, 2, 1, 15, 63, '', 0, 0, '', '', 'User Defined Field');
INSERT INTO `layout_options` VALUES ('DEM', 'genericval2', '1Who', '', 14, 2, 1, 15, 63, '', 0, 0, '', '', 'User Defined Field');
INSERT INTO `layout_options` VALUES ('DEM', 'squad', '1Who', 'Squad', 15, 13, 0, 0, 0, '', 1, 3, '', '', 'Squad Membership');
INSERT INTO `layout_options` VALUES ('DEM', 'pricelevel', '1Who', 'Price Level', 16, 1, 0, 0, 0, 'pricelevel', 1, 1, '', '', 'Discount Level');
INSERT INTO `layout_options` VALUES ('DEM', 'street', '2Contact', 'Address', 1, 2, 1, 25, 63, '', 1, 1, '', 'C', 'Street and Number');
INSERT INTO `layout_options` VALUES ('DEM', 'city', '2Contact', 'City', 2, 2, 1, 15, 63, '', 1, 1, '', 'C', 'City Name');
INSERT INTO `layout_options` VALUES ('DEM', 'state', '2Contact', 'State', 3, 2, 1, 15, 63, '', 1, 1, '', 'C', 'State/Locality');
INSERT INTO `layout_options` VALUES ('DEM', 'postal_code', '2Contact', 'Postal Code', 4, 2, 1, 6, 63, '', 1, 1, '', '', 'Postal Code');
INSERT INTO `layout_options` VALUES ('DEM', 'country_code', '2Contact', 'Country', 5, 1, 1, 0, 0, 'country', 1, 1, '', 'C', 'Country');
INSERT INTO `layout_options` VALUES ('DEM', 'contact_relationship', '2Contact', 'Emergency Contact', 6, 2, 1, 10, 63, '', 1, 1, '', 'C', 'Emergency Contact Person');
INSERT INTO `layout_options` VALUES ('DEM', 'phone_contact', '2Contact', 'Emergency Phone', 7, 2, 1, 20, 63, '', 1, 1, '', 'P', 'Emergency Contact Phone Number');
INSERT INTO `layout_options` VALUES ('DEM', 'phone_home', '2Contact', 'Home Phone', 8, 2, 1, 20, 63, '', 1, 1, '', 'P', 'Home Phone Number');
INSERT INTO `layout_options` VALUES ('DEM', 'phone_biz', '2Contact', 'Work Phone', 9, 2, 1, 20, 63, '', 1, 1, '', 'P', 'Work Phone Number');
INSERT INTO `layout_options` VALUES ('DEM', 'phone_cell', '2Contact', 'Mobile Phone', 10, 2, 1, 20, 63, '', 1, 1, '', 'P', 'Cell Phone Number');
INSERT INTO `layout_options` VALUES ('DEM', 'email', '2Contact', 'Contact Email', 11, 2, 1, 30, 95, '', 1, 1, '', '', 'Contact Email Address');
INSERT INTO `layout_options` VALUES ('DEM', 'providerID', '3Choices', 'Provider', 1, 11, 2, 0, 0, '', 1, 3, '', '', 'Referring Provider');
INSERT INTO `layout_options` VALUES ('DEM', 'pharmacy_id', '3Choices', 'Pharmacy', 2, 12, 1, 0, 0, '', 1, 3, '', '', 'Preferred Pharmacy');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_notice', '3Choices', 'HIPAA Notice Received', 3, 1, 1, 0, 0, 'yesno', 1, 1, '', '', 'Did you receive a copy of the HIPAA Notice?');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_voice', '3Choices', 'Allow Voice Message', 4, 1, 1, 0, 0, 'yesno', 1, 1, '', '', 'Allow telephone messages?');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_mail', '3Choices', 'Allow Mail Message', 5, 1, 1, 0, 0, 'yesno', 1, 1, '', '', 'Allow email messages?');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_message', '3Choices', 'Leave Message With', 6, 2, 1, 20, 63, '', 1, 1, '', '', 'With whom may we leave a message?');
INSERT INTO `layout_options` VALUES ('DEM', 'occupation', '4Employer', 'Occupation', 1, 2, 1, 20, 63, '', 1, 1, '', 'C', 'Occupation');
INSERT INTO `layout_options` VALUES ('DEM', 'em_name', '4Employer', 'Employer Name', 2, 2, 1, 20, 63, '', 1, 1, '', 'C', 'Employer Name');
INSERT INTO `layout_options` VALUES ('DEM', 'em_street', '4Employer', 'Employer Address', 3, 2, 1, 25, 63, '', 1, 1, '', 'C', 'Street and Number');
INSERT INTO `layout_options` VALUES ('DEM', 'em_city', '4Employer', 'City', 4, 2, 1, 15, 63, '', 1, 1, '', 'C', 'City Name');
INSERT INTO `layout_options` VALUES ('DEM', 'em_state', '4Employer', 'State', 5, 2, 1, 15, 63, '', 1, 1, '', 'C', 'State/Locality');
INSERT INTO `layout_options` VALUES ('DEM', 'em_postal_code', '4Employer', 'Postal Code', 6, 2, 1, 6, 63, '', 1, 1, '', '', 'Postal Code');
INSERT INTO `layout_options` VALUES ('DEM', 'em_country', '4Employer', 'Country', 7, 2, 1, 10, 63, '', 1, 1, '', 'C', 'Country');
INSERT INTO `layout_options` VALUES ('DEM', 'language', '5Stats', 'Language', 1, 1, 1, 0, 0, 'language', 1, 1, '', '', 'Preferred Language');
INSERT INTO `layout_options` VALUES ('DEM', 'ethnoracial', '5Stats', 'Race/Ethnicity', 2, 1, 1, 0, 0, 'ethrace', 1, 1, '', '', 'Ethnicity or Race');
INSERT INTO `layout_options` VALUES ('DEM', 'financial_review', '5Stats', 'Financial Review Date', 3, 2, 1, 10, 10, '', 1, 1, '', 'D', 'Financial Review Date');
INSERT INTO `layout_options` VALUES ('DEM', 'family_size', '5Stats', 'Family Size', 4, 2, 1, 20, 63, '', 1, 1, '', '', 'Family Size');
INSERT INTO `layout_options` VALUES ('DEM', 'monthly_income', '5Stats', 'Monthly Income', 5, 2, 1, 20, 63, '', 1, 1, '', '', 'Monthly Income');
INSERT INTO `layout_options` VALUES ('DEM', 'homeless', '5Stats', 'Homeless, etc.', 6, 2, 1, 20, 63, '', 1, 1, '', '', 'Homeless or similar?');
INSERT INTO `layout_options` VALUES ('DEM', 'interpretter', '5Stats', 'Interpreter', 7, 2, 1, 20, 63, '', 1, 1, '', '', 'Interpreter needed?');
INSERT INTO `layout_options` VALUES ('DEM', 'migrantseasonal', '5Stats', 'Migrant/Seasonal', 8, 2, 1, 20, 63, '', 1, 1, '', '', 'Migrant or seasonal worker?');
INSERT INTO `layout_options` VALUES ('DEM', 'contrastart', '5Stats', 'Contraceptives Start',9,4,0,10,10,'',1,1,'','','Date contraceptive services initially provided');
INSERT INTO `layout_options` VALUES ('DEM', 'usertext1', '6Misc', 'User Defined Text 1', 1, 2, 0, 10, 63, '', 1, 1, '', '', 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'usertext2', '6Misc', 'User Defined Text 2', 2, 2, 0, 10, 63, '', 1, 1, '', '', 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist1', '6Misc', 'User Defined List 1', 3, 1, 0, 0, 0, 'userlist1', 1, 1, '', '', 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist2', '6Misc', 'User Defined List 2', 4, 1, 0, 0, 0, 'userlist2', 1, 1, '', '', 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist3', '6Misc', 'User Defined List 3', 5, 1, 0, 0, 0, 'userlist3', 1, 1, '', '' , 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist4', '6Misc', 'User Defined List 4', 6, 1, 0, 0, 0, 'userlist4', 1, 1, '', '' , 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist5', '6Misc', 'User Defined List 5', 7, 1, 0, 0, 0, 'userlist5', 1, 1, '', '' , 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist6', '6Misc', 'User Defined List 6', 8, 1, 0, 0, 0, 'userlist6', 1, 1, '', '' , 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'userlist7', '6Misc', 'User Defined List 7', 9, 1, 0, 0, 0, 'userlist7', 1, 1, '', '' , 'User Defined');
INSERT INTO `layout_options` VALUES ('DEM', 'regdate'  , '6Misc', 'Registration Date'  ,10, 4, 0,10,10, ''         , 1, 1, '', 'D', 'Start Date at This Clinic');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_allowsms', '3Choices', 'Allow SMS', 5, 1, 1, 0, 0, 'yesno', 1, 1, '', '', 'Allow SMS (text messages)?');
INSERT INTO `layout_options` VALUES ('DEM', 'hipaa_allowemail', '3Choices', 'Allow Email', 5, 1, 1, 0, 0, 'yesno', 1, 1, '', '', 'Allow Email?');

INSERT INTO layout_options VALUES ('REF','refer_date'      ,'1Referral','Referral Date'                  , 1, 4,2, 0,  0,''         ,1,1,'C','D','Date of referral');
INSERT INTO layout_options VALUES ('REF','refer_from'      ,'1Referral','Refer By'                       , 2,10,2, 0,  0,''         ,1,1,'' ,'' ,'Referral By');
INSERT INTO layout_options VALUES ('REF','refer_to'        ,'1Referral','Refer To'                       , 3,14,2, 0,  0,''         ,1,1,'' ,'' ,'Referral To');
INSERT INTO layout_options VALUES ('REF','body'            ,'1Referral','Reason'                         , 4, 3,2,30,  3,''         ,1,1,'' ,'' ,'Reason for referral');
INSERT INTO layout_options VALUES ('REF','refer_external'  ,'1Referral','External Referral'              , 5, 1,1, 0,  0,'boolean'  ,1,1,'' ,'' ,'External referral?');
INSERT INTO layout_options VALUES ('REF','refer_diag'      ,'1Referral','Referrer Diagnosis'             , 6, 2,1,30,255,''         ,1,1,'' ,'X','Referrer diagnosis');
INSERT INTO layout_options VALUES ('REF','refer_risk_level','1Referral','Risk Level'                     , 7, 1,1, 0,  0,'risklevel',1,1,'' ,'' ,'Level of urgency');
INSERT INTO layout_options VALUES ('REF','refer_vitals'    ,'1Referral','Include Vitals'                 , 8, 1,1, 0,  0,'boolean'  ,1,1,'' ,'' ,'Include vitals data?');
INSERT INTO layout_options VALUES ('REF','reply_date'      ,'2Counter-Referral','Reply Date'             , 9, 4,1, 0,  0,''         ,1,1,'' ,'D','Date of reply');
INSERT INTO layout_options VALUES ('REF','reply_from'      ,'2Counter-Referral','Reply From'             ,10, 2,1,30,255,''         ,1,1,'' ,'' ,'Who replied?');
INSERT INTO layout_options VALUES ('REF','reply_init_diag' ,'2Counter-Referral','Presumed Diagnosis'     ,11, 2,1,30,255,''         ,1,1,'' ,'' ,'Presumed diagnosis by specialist');
INSERT INTO layout_options VALUES ('REF','reply_final_diag','2Counter-Referral','Final Diagnosis'        ,12, 2,1,30,255,''         ,1,1,'' ,'' ,'Final diagnosis by specialist');
INSERT INTO layout_options VALUES ('REF','reply_documents' ,'2Counter-Referral','Documents'              ,13, 2,1,30,255,''         ,1,1,'' ,'' ,'Where may related scanned or paper documents be found?');
INSERT INTO layout_options VALUES ('REF','reply_findings'  ,'2Counter-Referral','Findings'               ,14, 3,1,30,  3,''         ,1,1,'' ,'' ,'Findings by specialist');
INSERT INTO layout_options VALUES ('REF','reply_services'  ,'2Counter-Referral','Services Provided'      ,15, 3,1,30,  3,''         ,1,1,'' ,'' ,'Service provided by specialist');
INSERT INTO layout_options VALUES ('REF','reply_recommend' ,'2Counter-Referral','Recommendations'        ,16, 3,1,30,  3,''         ,1,1,'' ,'' ,'Recommendations by specialist');
INSERT INTO layout_options VALUES ('REF','reply_rx_refer'  ,'2Counter-Referral','Prescriptions/Referrals',17, 3,1,30,  3,''         ,1,1,'' ,'' ,'Prescriptions and/or referrals by specialist');

INSERT INTO layout_options VALUES ('HIS','usertext11','1General','Risk Factors',1,21,1,0,0,'riskfactors',1,1,'','' ,'Risk Factors');
INSERT INTO layout_options VALUES ('HIS','exams'     ,'1General','Exams/Tests' ,2,23,1,0,0,'exams'      ,1,1,'','' ,'Exam and test results');
INSERT INTO layout_options VALUES ('HIS','history_father'   ,'2Family History','Father'   ,1, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_mother'   ,'2Family History','Mother'   ,2, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_siblings' ,'2Family History','Siblings' ,3, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_spouse'   ,'2Family History','Spouse'   ,4, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_offspring','2Family History','Offspring',5, 2,1,20,255,'',1,3,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_cancer'             ,'3Relatives','Cancer'             ,1, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_tuberculosis'       ,'3Relatives','Tuberculosis'       ,2, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_diabetes'           ,'3Relatives','Diabetes'           ,3, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_high_blood_pressure','3Relatives','High Blood Pressure',4, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_heart_problems'     ,'3Relatives','Heart Problems'     ,5, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_stroke'             ,'3Relatives','Stroke'             ,6, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_epilepsy'           ,'3Relatives','Epilepsy'           ,7, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_mental_illness'     ,'3Relatives','Mental Illness'     ,8, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_suicide'            ,'3Relatives','Suicide'            ,9, 2,1,20,255,'',1,3,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','coffee'              ,'4Lifestyle','Coffee'              ,1, 2,1,20,255,'',1,1,'','' ,'Caffeine consumption');
INSERT INTO layout_options VALUES ('HIS','tobacco'             ,'4Lifestyle','Tobacco'             ,2, 2,1,20,255,'',1,1,'','' ,'Tobacco use');
INSERT INTO layout_options VALUES ('HIS','alcohol'             ,'4Lifestyle','Alcohol'             ,3, 2,1,20,255,'',1,1,'','' ,'Alcohol consumption');
INSERT INTO layout_options VALUES ('HIS','sleep_patterns'      ,'4Lifestyle','Sleep Patterns'      ,4, 2,1,20,255,'',1,1,'','' ,'Sleep patterns');
INSERT INTO layout_options VALUES ('HIS','exercise_patterns'   ,'4Lifestyle','Exercise Patterns'   ,5, 2,1,20,255,'',1,1,'','' ,'Exercise patterns');
INSERT INTO layout_options VALUES ('HIS','seatbelt_use'        ,'4Lifestyle','Seatbelt Use'        ,6, 2,1,20,255,'',1,1,'','' ,'Seatbelt use');
INSERT INTO layout_options VALUES ('HIS','counseling'          ,'4Lifestyle','Counseling'          ,7, 2,1,20,255,'',1,1,'','' ,'Counseling activities');
INSERT INTO layout_options VALUES ('HIS','hazardous_activities','4Lifestyle','Hazardous Activities',8, 2,1,20,255,'',1,1,'','' ,'Hazardous activities');
INSERT INTO layout_options VALUES ('HIS','name_1'            ,'5Other','Name/Value'        ,1, 2,1,10,255,'',1,1,'','' ,'Name 1' );
INSERT INTO layout_options VALUES ('HIS','value_1'           ,'5Other',''                  ,2, 2,1,10,255,'',0,0,'','' ,'Value 1');
INSERT INTO layout_options VALUES ('HIS','name_2'            ,'5Other','Name/Value'        ,3, 2,1,10,255,'',1,1,'','' ,'Name 2' );
INSERT INTO layout_options VALUES ('HIS','value_2'           ,'5Other',''                  ,4, 2,1,10,255,'',0,0,'','' ,'Value 2');
INSERT INTO layout_options VALUES ('HIS','additional_history','5Other','Additional History',5, 3,1,30,  3,'',1,3,'' ,'' ,'Additional history notes');

-- --------------------------------------------------------

-- 
-- Table structure for table `list_options`
-- 

DROP TABLE IF EXISTS `list_options`;
CREATE TABLE `list_options` (
  `list_id` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `option_id` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `seq` int(11) NOT NULL default '0',
  `is_default` tinyint(1) NOT NULL default '0',
  `option_value` float NOT NULL default '0',
  PRIMARY KEY  (`list_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `list_options`
-- 

INSERT INTO `list_options` VALUES ('yesno', 'NO', 'NO', 1, 0, 0);
INSERT INTO `list_options` VALUES ('yesno', 'YES', 'YES', 2, 0, 0);
INSERT INTO `list_options` VALUES ('titles', 'Mr.', 'Mr.', 1, 0, 0);
INSERT INTO `list_options` VALUES ('titles', 'Mrs.', 'Mrs.', 2, 0, 0);
INSERT INTO `list_options` VALUES ('titles', 'Ms.', 'Ms.', 3, 0, 0);
INSERT INTO `list_options` VALUES ('titles', 'Dr.', 'Dr.', 4, 0, 0);
INSERT INTO `list_options` VALUES ('sex', 'Female', 'Female', 1, 0, 0);
INSERT INTO `list_options` VALUES ('sex', 'Male', 'Male', 2, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'married', 'Married', 1, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'single', 'Single', 2, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'divorced', 'Divorced', 3, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'widowed', 'Widowed', 4, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'separated', 'Separated', 5, 0, 0);
INSERT INTO `list_options` VALUES ('marital', 'domestic partner', 'Domestic Partner', 6, 0, 0);
INSERT INTO `list_options` VALUES ('language', 'English', 'English', 1, 1, 0);
INSERT INTO `list_options` VALUES ('language', 'Spanish', 'Spanish', 2, 0, 0);
INSERT INTO `list_options` VALUES ('ethrace', 'Caucasian', 'Caucasian', 1, 0, 0);
INSERT INTO `list_options` VALUES ('ethrace', 'Asian', 'Asian', 2, 0, 0);
INSERT INTO `list_options` VALUES ('ethrace', 'Black', 'Black', 3, 0, 0);
INSERT INTO `list_options` VALUES ('ethrace', 'Hispanic', 'Hispanic', 4, 0, 0);
INSERT INTO `list_options` VALUES ('userlist1', 'sample', 'Sample', 1, 0, 0);
INSERT INTO `list_options` VALUES ('userlist2', 'sample', 'Sample', 1, 0, 0);
INSERT INTO `list_options` VALUES ('userlist3','sample','Sample',1,0,0);
INSERT INTO `list_options` VALUES ('userlist4','sample','Sample',1,0,0);
INSERT INTO `list_options` VALUES ('userlist5','sample','Sample',1,0,0);
INSERT INTO `list_options` VALUES ('userlist6','sample','Sample',1,0,0);
INSERT INTO `list_options` VALUES ('userlist7','sample','Sample',1,0,0);
INSERT INTO `list_options` VALUES ('pricelevel', 'standard', 'Standard', 1, 1, 0);
INSERT INTO `list_options` VALUES ('risklevel', 'low', 'Low', 1, 0, 0);
INSERT INTO `list_options` VALUES ('risklevel', 'medium', 'Medium', 2, 1, 0);
INSERT INTO `list_options` VALUES ('risklevel', 'high', 'High', 3, 0, 0);
INSERT INTO `list_options` VALUES ('boolean', '0', 'No', 1, 0, 0);
INSERT INTO `list_options` VALUES ('boolean', '1', 'Yes', 2, 0, 0);
INSERT INTO `list_options` VALUES ('country', 'USA', 'USA', 1, 0, 0);
INSERT INTO list_options VALUES ('refsource','Patient'      ,'Patient'      , 1,0,0);
INSERT INTO list_options VALUES ('refsource','Employee'     ,'Employee'     , 2,0,0);
INSERT INTO list_options VALUES ('refsource','Walk-In'      ,'Walk-In'      , 3,0,0);
INSERT INTO list_options VALUES ('refsource','Newspaper'    ,'Newspaper'    , 4,0,0);
INSERT INTO list_options VALUES ('refsource','Radio'        ,'Radio'        , 5,0,0);
INSERT INTO list_options VALUES ('refsource','T.V.'         ,'T.V.'         , 6,0,0);
INSERT INTO list_options VALUES ('refsource','Direct Mail'  ,'Direct Mail'  , 7,0,0);
INSERT INTO list_options VALUES ('refsource','Coupon'       ,'Coupon'       , 8,0,0);
INSERT INTO list_options VALUES ('refsource','Referral Card','Referral Card', 9,0,0);
INSERT INTO list_options VALUES ('refsource','Other'        ,'Other'        ,10,0,0);
INSERT INTO list_options VALUES ('riskfactors','vv' ,'Varicose Veins'                      , 1,0,0);
INSERT INTO list_options VALUES ('riskfactors','ht' ,'Hypertension'                        , 2,0,0);
INSERT INTO list_options VALUES ('riskfactors','db' ,'Diabetes'                            , 3,0,0);
INSERT INTO list_options VALUES ('riskfactors','sc' ,'Sickle Cell'                         , 4,0,0);
INSERT INTO list_options VALUES ('riskfactors','fib','Fibroids'                            , 5,0,0);
INSERT INTO list_options VALUES ('riskfactors','pid','PID (Pelvic Inflammatory Disease)'   , 6,0,0);
INSERT INTO list_options VALUES ('riskfactors','mig','Severe Migraine'                     , 7,0,0);
INSERT INTO list_options VALUES ('riskfactors','hd' ,'Heart Disease'                       , 8,0,0);
INSERT INTO list_options VALUES ('riskfactors','str','Thrombosis/Stroke'                   , 9,0,0);
INSERT INTO list_options VALUES ('riskfactors','hep','Hepatitis'                           ,10,0,0);
INSERT INTO list_options VALUES ('riskfactors','gb' ,'Gall Bladder Condition'              ,11,0,0);
INSERT INTO list_options VALUES ('riskfactors','br' ,'Breast Disease'                      ,12,0,0);
INSERT INTO list_options VALUES ('riskfactors','dpr','Depression'                          ,13,0,0);
INSERT INTO list_options VALUES ('riskfactors','all','Allergies'                           ,14,0,0);
INSERT INTO list_options VALUES ('riskfactors','inf','Infertility'                         ,15,0,0);
INSERT INTO list_options VALUES ('riskfactors','ast','Asthma'                              ,16,0,0);
INSERT INTO list_options VALUES ('riskfactors','ep' ,'Epilepsy'                            ,17,0,0);
INSERT INTO list_options VALUES ('riskfactors','cl' ,'Contact Lenses'                      ,18,0,0);
INSERT INTO list_options VALUES ('riskfactors','coc','Contraceptive Complication (specify)',19,0,0);
INSERT INTO list_options VALUES ('riskfactors','oth','Other (specify)'                     ,20,0,0);
INSERT INTO list_options VALUES ('exams' ,'brs','Breast Exam'          , 1,0,0);
INSERT INTO list_options VALUES ('exams' ,'cec','Cardiac Echo'         , 2,0,0);
INSERT INTO list_options VALUES ('exams' ,'ecg','ECG'                  , 3,0,0);
INSERT INTO list_options VALUES ('exams' ,'gyn','Gynecological Exam'   , 4,0,0);
INSERT INTO list_options VALUES ('exams' ,'mam','Mammogram'            , 5,0,0);
INSERT INTO list_options VALUES ('exams' ,'phy','Physical Exam'        , 6,0,0);
INSERT INTO list_options VALUES ('exams' ,'pro','Prostate Exam'        , 7,0,0);
INSERT INTO list_options VALUES ('exams' ,'rec','Rectal Exam'          , 8,0,0);
INSERT INTO list_options VALUES ('exams' ,'sic','Sigmoid/Colonoscopy'  , 9,0,0);
INSERT INTO list_options VALUES ('exams' ,'ret','Retinal Exam'         ,10,0,0);
INSERT INTO list_options VALUES ('exams' ,'flu','Flu Vaccination'      ,11,0,0);
INSERT INTO list_options VALUES ('exams' ,'pne','Pneumonia Vaccination',12,0,0);
INSERT INTO list_options VALUES ('exams' ,'ldl','LDL'                  ,13,0,0);
INSERT INTO list_options VALUES ('exams' ,'hem','Hemoglobin'           ,14,0,0);
INSERT INTO list_options VALUES ('exams' ,'psa','PSA'                  ,15,0,0);
INSERT INTO list_options VALUES ('drug_form','1','suspension' ,1,0,0);
INSERT INTO list_options VALUES ('drug_form','2','tablet'     ,2,0,0);
INSERT INTO list_options VALUES ('drug_form','3','capsule'    ,3,0,0);
INSERT INTO list_options VALUES ('drug_form','4','solution'   ,4,0,0);
INSERT INTO list_options VALUES ('drug_form','5','tsp'        ,5,0,0);
INSERT INTO list_options VALUES ('drug_form','6','ml'         ,6,0,0);
INSERT INTO list_options VALUES ('drug_form','7','units'      ,7,0,0);
INSERT INTO list_options VALUES ('drug_form','8','inhalations',8,0,0);
INSERT INTO list_options VALUES ('drug_form','9','gtts(drops)',9,0,0);
INSERT INTO list_options VALUES ('drug_units','1','mg'    ,1,0,0);
INSERT INTO list_options VALUES ('drug_units','2','mg/1cc',2,0,0);
INSERT INTO list_options VALUES ('drug_units','3','mg/2cc',3,0,0);
INSERT INTO list_options VALUES ('drug_units','4','mg/3cc',4,0,0);
INSERT INTO list_options VALUES ('drug_units','5','mg/4cc',5,0,0);
INSERT INTO list_options VALUES ('drug_units','6','mg/5cc',6,0,0);
INSERT INTO list_options VALUES ('drug_units','7','grams' ,7,0,0);
INSERT INTO list_options VALUES ('drug_units','8','mcg'   ,8,0,0);
INSERT INTO list_options VALUES ('drug_route', '1','Per Oris'         , 1,0,0);
INSERT INTO list_options VALUES ('drug_route', '2','Per Rectum'       , 2,0,0);
INSERT INTO list_options VALUES ('drug_route', '3','To Skin'          , 3,0,0);
INSERT INTO list_options VALUES ('drug_route', '4','To Affected Area' , 4,0,0);
INSERT INTO list_options VALUES ('drug_route', '5','Sublingual'       , 5,0,0);
INSERT INTO list_options VALUES ('drug_route',' 6','OS'               , 6,0,0);
INSERT INTO list_options VALUES ('drug_route', '7','OD'               , 7,0,0);
INSERT INTO list_options VALUES ('drug_route', '8','OU'               , 8,0,0);
INSERT INTO list_options VALUES ('drug_route', '9','SQ'               , 9,0,0);
INSERT INTO list_options VALUES ('drug_route','10','IM'               ,10,0,0);
INSERT INTO list_options VALUES ('drug_route','11','IV'               ,11,0,0);
INSERT INTO list_options VALUES ('drug_route','12','Per Nostril'      ,12,0,0);
INSERT INTO list_options VALUES ('drug_interval','1','b.i.d.',1,0,0);
INSERT INTO list_options VALUES ('drug_interval','2','t.i.d.',2,0,0);
INSERT INTO list_options VALUES ('drug_interval','3','q.i.d.',3,0,0);
INSERT INTO list_options VALUES ('drug_interval','4','q.3h'  ,4,0,0);
INSERT INTO list_options VALUES ('drug_interval','5','q.4h'  ,5,0,0);
INSERT INTO list_options VALUES ('drug_interval','6','q.5h'  ,6,0,0);
INSERT INTO list_options VALUES ('drug_interval','7','q.6h'  ,7,0,0);
INSERT INTO list_options VALUES ('drug_interval','8','q.8h'  ,8,0,0);
INSERT INTO list_options VALUES ('drug_interval','9','q.d.'  ,9,0,0);
INSERT INTO list_options VALUES ('lists' ,'boolean'      ,'Boolean'            , 1,0,0);
INSERT INTO list_options VALUES ('lists' ,'country'      ,'Country'            , 2,0,0);
INSERT INTO list_options VALUES ('lists' ,'drug_form'    ,'Drug Forms'         , 3,0,0);
INSERT INTO list_options VALUES ('lists' ,'drug_units'   ,'Drug Units'         , 4,0,0);
INSERT INTO list_options VALUES ('lists' ,'drug_route'   ,'Drug Routes'        , 5,0,0);
INSERT INTO list_options VALUES ('lists' ,'drug_interval','Drug Intervals'     , 6,0,0);
INSERT INTO list_options VALUES ('lists' ,'exams'        ,'Exams/Tests'        , 7,0,0);
INSERT INTO list_options VALUES ('lists' ,'feesheet'     ,'Fee Sheet'          , 8,0,0);
INSERT INTO list_options VALUES ('lists' ,'language'     ,'Language'           , 9,0,0);
INSERT INTO list_options VALUES ('lists' ,'marital'      ,'Marital Status'     ,10,0,0);
INSERT INTO list_options VALUES ('lists' ,'pricelevel'   ,'Price Level'        ,11,0,0);
INSERT INTO list_options VALUES ('lists' ,'ethrace'      ,'Race/Ethnicity'     ,12,0,0);
INSERT INTO list_options VALUES ('lists' ,'refsource'    ,'Referral Source'    ,13,0,0);
INSERT INTO list_options VALUES ('lists' ,'riskfactors'  ,'Risk Factors'       ,14,0,0);
INSERT INTO list_options VALUES ('lists' ,'risklevel'    ,'Risk Level'         ,15,0,0);
INSERT INTO list_options VALUES ('lists' ,'superbill'    ,'Service Category'   ,16,0,0);
INSERT INTO list_options VALUES ('lists' ,'sex'          ,'Sex'                ,17,0,0);
INSERT INTO list_options VALUES ('lists' ,'taxrate'      ,'Tax Rate'           ,18,0,0);
INSERT INTO list_options VALUES ('lists' ,'titles'       ,'Titles'             ,19,0,0);
INSERT INTO list_options VALUES ('lists' ,'yesno'        ,'Yes/No'             ,20,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist1'    ,'User Defined List 1',21,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist2'    ,'User Defined List 2',22,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist3'    ,'User Defined List 3',23,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist4'    ,'User Defined List 4',24,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist5'    ,'User Defined List 5',25,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist6'    ,'User Defined List 6',26,0,0);
INSERT INTO list_options VALUES ('lists' ,'userlist7'    ,'User Defined List 7',27,0,0);

-- --------------------------------------------------------

-- 
-- Table structure for table `lists`
-- 

DROP TABLE IF EXISTS `lists`;
CREATE TABLE `lists` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `type` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `title` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `begdate` date default NULL,
  `enddate` date default NULL,
  `returndate` date default NULL,
  `occurrence` int(11) default '0',
  `classification` int(11) default '0',
  `referredby` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `extrainfo` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `diagnosis` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `activity` tinyint(4) default NULL,
  `comments` longtext character set utf8 collate utf8_unicode_ci,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `outcome` int(11) NOT NULL default '0',
  `destination` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `lists`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `event` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `comments` longtext character set utf8 collate utf8_unicode_ci,
  `user_notes` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `notes`
-- 

DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL default '0',
  `foreign_id` int(11) NOT NULL default '0',
  `note` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `owner` int(11) default NULL,
  `date` datetime default NULL,
  `revision` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `foreign_id` (`owner`),
  KEY `foreign_id_2` (`foreign_id`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `notes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `onotes`
-- 

DROP TABLE IF EXISTS `onotes`;
CREATE TABLE `onotes` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `body` longtext character set utf8 collate utf8_unicode_ci,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `activity` tinyint(4) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `onotes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_module_vars`
-- 

DROP TABLE IF EXISTS `openemr_module_vars`;
CREATE TABLE `openemr_module_vars` (
  `pn_id` int(11) unsigned NOT NULL auto_increment,
  `pn_modname` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_value` longtext character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`pn_id`),
  KEY `pn_modname` (`pn_modname`),
  KEY `pn_name` (`pn_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=235 ;

-- 
-- Dumping data for table `openemr_module_vars`
-- 

INSERT INTO `openemr_module_vars` VALUES (234, 'PostCalendar', 'pcNotifyEmail', '');
INSERT INTO `openemr_module_vars` VALUES (233, 'PostCalendar', 'pcNotifyAdmin', '0');
INSERT INTO `openemr_module_vars` VALUES (232, 'PostCalendar', 'pcCacheLifetime', '3600');
INSERT INTO `openemr_module_vars` VALUES (231, 'PostCalendar', 'pcUseCache', '0');
INSERT INTO `openemr_module_vars` VALUES (230, 'PostCalendar', 'pcDefaultView', 'day');
INSERT INTO `openemr_module_vars` VALUES (229, 'PostCalendar', 'pcTimeIncrement', '5');
INSERT INTO `openemr_module_vars` VALUES (228, 'PostCalendar', 'pcAllowUserCalendar', '1');
INSERT INTO `openemr_module_vars` VALUES (227, 'PostCalendar', 'pcAllowSiteWide', '1');
INSERT INTO `openemr_module_vars` VALUES (226, 'PostCalendar', 'pcTemplate', 'default');
INSERT INTO `openemr_module_vars` VALUES (225, 'PostCalendar', 'pcEventDateFormat', '%Y-%m-%d');
INSERT INTO `openemr_module_vars` VALUES (224, 'PostCalendar', 'pcDisplayTopics', '0');
INSERT INTO `openemr_module_vars` VALUES (223, 'PostCalendar', 'pcListHowManyEvents', '15');
INSERT INTO `openemr_module_vars` VALUES (222, 'PostCalendar', 'pcAllowDirectSubmit', '1');
INSERT INTO `openemr_module_vars` VALUES (221, 'PostCalendar', 'pcUsePopups', '0');
INSERT INTO `openemr_module_vars` VALUES (220, 'PostCalendar', 'pcDayHighlightColor', '#EEEEEE');
INSERT INTO `openemr_module_vars` VALUES (219, 'PostCalendar', 'pcFirstDayOfWeek', '1');
INSERT INTO `openemr_module_vars` VALUES (218, 'PostCalendar', 'pcUseInternationalDates', '0');
INSERT INTO `openemr_module_vars` VALUES (217, 'PostCalendar', 'pcEventsOpenInNewWindow', '0');
INSERT INTO `openemr_module_vars` VALUES (216, 'PostCalendar', 'pcTime24Hours', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_modules`
-- 

DROP TABLE IF EXISTS `openemr_modules`;
CREATE TABLE `openemr_modules` (
  `pn_id` int(11) unsigned NOT NULL auto_increment,
  `pn_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_type` int(6) NOT NULL default '0',
  `pn_displayname` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_description` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_regid` int(11) unsigned NOT NULL default '0',
  `pn_directory` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_version` varchar(10) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_admin_capable` tinyint(1) NOT NULL default '0',
  `pn_user_capable` tinyint(1) NOT NULL default '0',
  `pn_state` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`pn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=47 ;

-- 
-- Dumping data for table `openemr_modules`
-- 

INSERT INTO `openemr_modules` VALUES (46, 'PostCalendar', 2, 'PostCalendar', 'PostNuke Calendar Module', 0, 'PostCalendar', '4.0.0', 1, 1, 3);

-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_postcalendar_categories`
-- 

DROP TABLE IF EXISTS `openemr_postcalendar_categories`;
CREATE TABLE `openemr_postcalendar_categories` (
  `pc_catid` int(11) unsigned NOT NULL auto_increment,
  `pc_catname` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_catcolor` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_catdesc` text character set utf8 collate utf8_unicode_ci,
  `pc_recurrtype` int(1) NOT NULL default '0',
  `pc_enddate` date default NULL,
  `pc_recurrspec` text character set utf8 collate utf8_unicode_ci,
  `pc_recurrfreq` int(3) NOT NULL default '0',
  `pc_duration` bigint(20) NOT NULL default '0',
  `pc_end_date_flag` tinyint(1) NOT NULL default '0',
  `pc_end_date_type` int(2) default NULL,
  `pc_end_date_freq` int(11) NOT NULL default '0',
  `pc_end_all_day` tinyint(1) NOT NULL default '0',
  `pc_dailylimit` int(2) NOT NULL default '0',
  PRIMARY KEY  (`pc_catid`),
  KEY `basic_cat` (`pc_catname`,`pc_catcolor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=11 ;

-- 
-- Dumping data for table `openemr_postcalendar_categories`
-- 

INSERT INTO `openemr_postcalendar_categories` VALUES (5, 'Office Visit', '#FFFFCC', 'Normal Office Visit', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 900, 0, 0, 0, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (4, 'Vacation', '#EFEFEF', 'Reserved for use to define Scheduled Vacation Time', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (1, 'No Show', '#DDDDDD', 'Reserved to define when an event did not occur as specified.', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (2, 'In Office', '#99CCFF', 'Reserved todefine when a provider may haveavailable appointments after.', 1, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 0, 1, 3, 2, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (3, 'Out Of Office', '#99FFFF', 'Reserved to define when a provider may not have available appointments after.', 1, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 0, 1, 3, 2, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (8, 'Lunch', '#FFFF33', 'Lunch', 1, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 3600, 0, 3, 2, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (9, 'Established Patient', '#CCFF33', '', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 900, 0, 0, 0, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (10,'New Patient', '#CCFFFF', '', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 1800, 0, 0, 0, 0, 0);
INSERT INTO `openemr_postcalendar_categories` VALUES (11,'Reserved','#FF7777','Reserved',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);

-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_postcalendar_events`
-- 

DROP TABLE IF EXISTS `openemr_postcalendar_events`;
CREATE TABLE `openemr_postcalendar_events` (
  `pc_eid` int(11) unsigned NOT NULL auto_increment,
  `pc_catid` int(11) NOT NULL default '0',
  `pc_multiple` int(10) unsigned NOT NULL,
  `pc_aid` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_pid` varchar(11) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_title` varchar(150) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_time` datetime default NULL,
  `pc_hometext` text character set utf8 collate utf8_unicode_ci,
  `pc_comments` int(11) default '0',
  `pc_counter` mediumint(8) unsigned default '0',
  `pc_topic` int(3) NOT NULL default '1',
  `pc_informant` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_eventDate` date NOT NULL default '0000-00-00',
  `pc_endDate` date NOT NULL default '0000-00-00',
  `pc_duration` bigint(20) NOT NULL default '0',
  `pc_recurrtype` int(1) NOT NULL default '0',
  `pc_recurrspec` text character set utf8 collate utf8_unicode_ci,
  `pc_recurrfreq` int(3) NOT NULL default '0',
  `pc_startTime` time default NULL,
  `pc_endTime` time default NULL,
  `pc_alldayevent` int(1) NOT NULL default '0',
  `pc_location` text character set utf8 collate utf8_unicode_ci,
  `pc_conttel` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_contname` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_contemail` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_website` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_fee` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_eventstatus` int(11) NOT NULL default '0',
  `pc_sharing` int(11) NOT NULL default '0',
  `pc_language` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_apptstatus` char(1) character set latin1 NOT NULL default '-',
  `pc_prefcatid` int(11) NOT NULL default '0',
  `pc_facility` smallint(6) NOT NULL default '0' COMMENT 'facility id for this event',
  `pc_sendalertsms` VARCHAR(3) NOT NULL DEFAULT 'NO',
  `pc_sendalertemail` VARCHAR( 3 ) NOT NULL DEFAULT 'NO',
  PRIMARY KEY  (`pc_eid`),
  KEY `basic_event` (`pc_catid`,`pc_aid`,`pc_eventDate`,`pc_endDate`,`pc_eventstatus`,`pc_sharing`,`pc_topic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `openemr_postcalendar_events`
-- 

INSERT INTO `openemr_postcalendar_events` VALUES (3, 2, 0, '1', '', 'In Office', '2005-03-03 12:22:31', ':text:', 0, 0, 0, '1', '2005-03-03', '2007-03-03', 0, 1, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, '09:00:00', '09:00:00', 0, 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;s:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}', '', '', '', '', '', 1, 1, '', '-', 0, 0);
INSERT INTO `openemr_postcalendar_events` VALUES (5, 3, 0, '1', '', 'Out Of Office', '2005-03-03 12:22:52', ':text:', 0, 0, 0, '1', '2005-03-03', '2007-03-03', 0, 1, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, '17:00:00', '17:00:00', 0, 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;s:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}', '', '', '', '', '', 1, 1, '', '-', 0, 0);
INSERT INTO `openemr_postcalendar_events` VALUES (6, 8, 0, '1', '', 'Lunch', '2005-03-03 12:23:31', ':text:', 0, 0, 0, '1', '2005-03-03', '2007-03-03', 3600, 1, 'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, '12:00:00', '13:00:00', 0, 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;s:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}', '', '', '', '', '', 1, 1, '', '-', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_postcalendar_limits`
-- 

DROP TABLE IF EXISTS `openemr_postcalendar_limits`;
CREATE TABLE `openemr_postcalendar_limits` (
  `pc_limitid` int(11) NOT NULL auto_increment,
  `pc_catid` int(11) NOT NULL default '0',
  `pc_starttime` time NOT NULL default '00:00:00',
  `pc_endtime` time NOT NULL default '00:00:00',
  `pc_limit` int(11) NOT NULL default '1',
  PRIMARY KEY  (`pc_limitid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `openemr_postcalendar_limits`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_postcalendar_topics`
-- 

DROP TABLE IF EXISTS `openemr_postcalendar_topics`;
CREATE TABLE `openemr_postcalendar_topics` (
  `pc_catid` int(11) unsigned NOT NULL auto_increment,
  `pc_catname` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_catcolor` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  `pc_catdesc` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`pc_catid`),
  KEY `basic_cat` (`pc_catname`,`pc_catcolor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `openemr_postcalendar_topics`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `openemr_session_info`
-- 

DROP TABLE IF EXISTS `openemr_session_info`;
CREATE TABLE `openemr_session_info` (
  `pn_sessid` varchar(32) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pn_ipaddr` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `pn_firstused` int(11) NOT NULL default '0',
  `pn_lastused` int(11) NOT NULL default '0',
  `pn_uid` int(11) NOT NULL default '0',
  `pn_vars` blob,
  PRIMARY KEY  (`pn_sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `openemr_session_info`
-- 

INSERT INTO `openemr_session_info` VALUES ('978d31441dccd350d406bfab98978f20', '127.0.0.1', 1109233952, 1109234177, 0, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `patient_data`
-- 

DROP TABLE IF EXISTS `patient_data`;
CREATE TABLE `patient_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `title` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `language` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `financial` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `fname` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `lname` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `mname` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `DOB` date default NULL,
  `street` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `postal_code` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `city` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `state` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `country_code` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `drivers_license` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `ss` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `occupation` longtext character set utf8 collate utf8_unicode_ci,
  `phone_home` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `phone_biz` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `phone_contact` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `phone_cell` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pharmacy_id` int(11) NOT NULL default '0',
  `status` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `contact_relationship` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `date` datetime default NULL,
  `sex` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `referrer` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `referrerID` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `providerID` int(11) default NULL,
  `email` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `ethnoracial` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `interpretter` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `migrantseasonal` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `family_size` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `monthly_income` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `homeless` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `financial_review` datetime default NULL,
  `pubpid` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pid` bigint(20) NOT NULL default '0',
  `genericname1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `genericval1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `genericname2` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `genericval2` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `hipaa_mail` varchar(3) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `hipaa_voice` varchar(3) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `hipaa_notice` varchar(3) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `hipaa_message` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `hipaa_allowsms` VARCHAR( 3 ) NOT NULL DEFAULT 'NO',
  `hipaa_allowemail` VARCHAR( 3 ) NOT NULL DEFAULT 'NO',
  `squad` varchar(32) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `fitness` int(11) NOT NULL default '0',
  `referral_source` varchar(30) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `usertext1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `usertext2` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist2` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist3` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist4` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist5` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist6` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `userlist7` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `pricelevel` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default 'standard',
  `regdate`     date DEFAULT NULL COMMENT 'Registration Date',
  `contrastart` date DEFAULT NULL COMMENT 'Date contraceptives initially used',
  UNIQUE KEY `pid` (`pid`),
  KEY `id` (`id`),
  KEY `pid_2` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `patient_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `payments`
-- 

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL auto_increment,
  `pid` bigint(20) NOT NULL default '0',
  `dtime` datetime NOT NULL,
  `encounter` bigint(20) NOT NULL default '0',
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `method` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `source` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `amount1` decimal(7,2) NOT NULL default '0.00',
  `amount2` decimal(7,2) NOT NULL default '0.00',
  `posted1` decimal(7,2) NOT NULL default '0.00',
  `posted2` decimal(7,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `payments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pharmacies`
-- 

DROP TABLE IF EXISTS `pharmacies`;
CREATE TABLE `pharmacies` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `transmit_method` int(11) NOT NULL default '1',
  `email` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `pharmacies`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `phone_numbers`
-- 

DROP TABLE IF EXISTS `phone_numbers`;
CREATE TABLE `phone_numbers` (
  `id` int(11) NOT NULL default '0',
  `country_code` varchar(5) character set utf8 collate utf8_unicode_ci default NULL,
  `area_code` char(3) character set latin1 default NULL,
  `prefix` char(3) character set latin1 default NULL,
  `number` varchar(4) character set utf8 collate utf8_unicode_ci default NULL,
  `type` int(11) default NULL,
  `foreign_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `foreign_id` (`foreign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `phone_numbers`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_bookmark`
-- 

DROP TABLE IF EXISTS `pma_bookmark`;
CREATE TABLE `pma_bookmark` (
  `id` int(11) NOT NULL auto_increment,
  `dbase` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `label` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `query` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks' AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `pma_bookmark`
-- 

INSERT INTO `pma_bookmark` VALUES (2, 'openemr', 'openemr', 'Aggregate Race Statistics', 'SELECT ethnoracial as "Race/Ethnicity", count(*) as Count FROM  `patient_data` WHERE 1 group by ethnoracial');
INSERT INTO `pma_bookmark` VALUES (9, 'openemr', 'openemr', 'Search by Code', 'SELECT  b.code, concat(pd.fname," ", pd.lname) as "Patient Name", concat(u.fname," ", u.lname) as "Provider Name", en.reason as "Encounter Desc.", en.date\r\nFROM billing as b\r\nLEFT JOIN users AS u ON b.user = u.id\r\nLEFT JOIN patient_data as pd on b.pid = pd.pid\r\nLEFT JOIN form_encounter as en on b.encounter = en.encounter and b.pid = en.pid\r\nWHERE 1 /* and b.code like ''%[VARIABLE]%'' */ ORDER BY b.code');
INSERT INTO `pma_bookmark` VALUES (8, 'openemr', 'openemr', 'Count No Shows By Provider since Interval ago', 'SELECT concat( u.fname,  " ", u.lname )  AS  "Provider Name", u.id AS  "Provider ID", count(  DISTINCT ev.pc_eid )  AS  "Number of No Shows"/* , concat(DATE_FORMAT(NOW(),''%Y-%m-%d''), '' and '',DATE_FORMAT(DATE_ADD(now(), INTERVAL [VARIABLE]),''%Y-%m-%d'') ) as "Between Dates" */ FROM  `openemr_postcalendar_events`  AS ev LEFT  JOIN users AS u ON ev.pc_aid = u.id WHERE ev.pc_catid =1/* and ( ev.pc_eventDate >= DATE_SUB(now(), INTERVAL [VARIABLE]) )  */\r\nGROUP  BY u.id;');
INSERT INTO `pma_bookmark` VALUES (6, 'openemr', 'openemr', 'Appointments By Race/Ethnicity from today plus interval', 'SELECT  count(pd.ethnoracial) as "Number of Appointments", pd.ethnoracial AS  "Race/Ethnicity" /* , concat(DATE_FORMAT(NOW(),''%Y-%m-%d''), '' and '',DATE_FORMAT(DATE_ADD(now(), INTERVAL [VARIABLE]),''%Y-%m-%d'') ) as "Between Dates" */ FROM openemr_postcalendar_events AS ev LEFT  JOIN   `patient_data`  AS pd ON  pd.pid = ev.pc_pid where ev.pc_eventstatus=1 and ev.pc_catid = 5 and ev.pc_eventDate >= now()  /* and ( ev.pc_eventDate <= DATE_ADD(now(), INTERVAL [VARIABLE]) )  */ group by pd.ethnoracial');

-- --------------------------------------------------------

-- 
-- Table structure for table `pma_column_info`
-- 

DROP TABLE IF EXISTS `pma_column_info`;
CREATE TABLE `pma_column_info` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `db_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `table_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `column_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `comment` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `mimetype` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `transformation` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `transformation_options` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column Information for phpMyAdmin' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pma_column_info`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_history`
-- 

DROP TABLE IF EXISTS `pma_history`;
CREATE TABLE `pma_history` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `db` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `table` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `timevalue` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `sqlquery` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`,`db`,`table`,`timevalue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pma_history`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_pdf_pages`
-- 

DROP TABLE IF EXISTS `pma_pdf_pages`;
CREATE TABLE `pma_pdf_pages` (
  `db_name` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `page_nr` int(10) unsigned NOT NULL auto_increment,
  `page_descr` varchar(50) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`page_nr`),
  KEY `db_name` (`db_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF Relationpages for PMA' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pma_pdf_pages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_relation`
-- 

DROP TABLE IF EXISTS `pma_relation`;
CREATE TABLE `pma_relation` (
  `master_db` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `master_table` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `master_field` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `foreign_db` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `foreign_table` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  `foreign_field` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`master_db`,`master_table`,`master_field`),
  KEY `foreign_field` (`foreign_db`,`foreign_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- 
-- Dumping data for table `pma_relation`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_table_coords`
-- 

DROP TABLE IF EXISTS `pma_table_coords`;
CREATE TABLE `pma_table_coords` (
  `db_name` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `table_name` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pdf_page_number` int(11) NOT NULL default '0',
  `x` float unsigned NOT NULL default '0',
  `y` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`db_name`,`table_name`,`pdf_page_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- 
-- Dumping data for table `pma_table_coords`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pma_table_info`
-- 

DROP TABLE IF EXISTS `pma_table_info`;
CREATE TABLE `pma_table_info` (
  `db_name` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `table_name` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `display_field` varchar(64) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`db_name`,`table_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- 
-- Dumping data for table `pma_table_info`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pnotes`
-- 

DROP TABLE IF EXISTS `pnotes`;
CREATE TABLE `pnotes` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `body` longtext character set utf8 collate utf8_unicode_ci,
  `pid` bigint(20) default NULL,
  `user` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `groupname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `activity` tinyint(4) default NULL,
  `authorized` tinyint(4) default NULL,
  `title` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `assigned_to` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pnotes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `prescriptions`
-- 

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) default NULL,
  `filled_by_id` int(11) default NULL,
  `pharmacy_id` int(11) default NULL,
  `date_added` date default NULL,
  `date_modified` date default NULL,
  `provider_id` int(11) default NULL,
  `start_date` date default NULL,
  `drug` varchar(150) character set utf8 collate utf8_unicode_ci default NULL,
  `drug_id` int(11) NOT NULL default '0',
  `form` int(3) default NULL,
  `dosage` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  `quantity` varchar(31) character set utf8 collate utf8_unicode_ci default NULL,
  `size` float unsigned default NULL,
  `unit` int(11) default NULL,
  `route` int(11) default NULL,
  `interval` int(11) default NULL,
  `substitute` int(11) default NULL,
  `refills` int(11) default NULL,
  `per_refill` int(11) default NULL,
  `filled_date` date default NULL,
  `medication` int(11) default NULL,
  `note` text character set utf8 collate utf8_unicode_ci,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `prescriptions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `prices`
-- 

DROP TABLE IF EXISTS `prices`;
CREATE TABLE `prices` (
  `pr_id` varchar(11) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pr_selector` varchar(15) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pr_level` varchar(31) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `pr_price` decimal(12,2) NOT NULL default '0.00' COMMENT 'price in local currency',
  PRIMARY KEY  (`pr_id`,`pr_selector`,`pr_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `prices`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `registry`
-- 

DROP TABLE IF EXISTS `registry`;
CREATE TABLE `registry` (
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `state` tinyint(4) default NULL,
  `directory` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `id` bigint(20) NOT NULL auto_increment,
  `sql_run` tinyint(4) default NULL,
  `unpackaged` tinyint(4) default NULL,
  `date` datetime default NULL,
  `priority` int(11) default '0',
  `category` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `nickname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=16 ;

-- 
-- Dumping data for table `registry`
-- 

INSERT INTO `registry` VALUES ('New Encounter Form', 1, 'newpatient', 1, 1, 1, '2003-09-14 15:16:45', 0, 'category', '');
INSERT INTO `registry` VALUES ('Review of Systems Checks', 1, 'reviewofs', 9, 1, 1, '2003-09-14 15:16:45', 0, 'category', '');
INSERT INTO `registry` VALUES ('Speech Dictation', 1, 'dictation', 10, 1, 1, '2003-09-14 15:16:45', 0, 'category', '');
INSERT INTO `registry` VALUES ('SOAP', 1, 'soap', 11, 1, 1, '2005-03-03 00:16:35', 0, 'category', '');
INSERT INTO `registry` VALUES ('Vitals', 1, 'vitals', 12, 1, 1, '2005-03-03 00:16:34', 0, 'category', '');
INSERT INTO `registry` VALUES ('Review Of Systems', 1, 'ros', 13, 1, 1, '2005-03-03 00:16:30', 0, 'category', '');
INSERT INTO `registry` VALUES ('Fee Sheet', 1, 'fee_sheet', 14, 1, 1, '2007-07-28 00:00:00', 0, 'category', '');
INSERT INTO `registry` VALUES ('Misc Billing Options HCFA', 1, 'misc_billing_options', 15, 1, 1, '2007-07-28 00:00:00', 0, 'category', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `sequences`
-- 

DROP TABLE IF EXISTS `sequences`;
CREATE TABLE `sequences` (
  `id` int(11) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `sequences`
-- 

INSERT INTO `sequences` VALUES (1);

-- --------------------------------------------------------

-- 
-- Table structure for table `transactions`
-- 

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id`                      bigint(20)   NOT NULL auto_increment,
  `date`                    datetime     default NULL,
  `title`                   varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `body`                    longtext     character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `pid`                     bigint(20)   default NULL,
  `user`                    varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `groupname`               varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `authorized`              tinyint(4)   default NULL,
  `refer_date`              date         DEFAULT NULL,
  `refer_from`              int(11)      NOT NULL DEFAULT 0,
  `refer_to`                int(11)      NOT NULL DEFAULT 0,
  `refer_diag`              varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `refer_risk_level`        varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `refer_vitals`            tinyint(1)   NOT NULL DEFAULT 0,
  `refer_external`          tinyint(1)   NOT NULL DEFAULT 0,
  `reply_date`              date         DEFAULT NULL,
  `reply_from`              varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_init_diag`         varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_final_diag`        varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_documents`         varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_findings`          text         character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_services`          text         character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_recommend`         text         character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_rx_refer`          text         character set utf8 collate utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `transactions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL auto_increment,
  `username` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `password` longtext character set utf8 collate utf8_unicode_ci,
  `authorized` tinyint(4) default NULL,
  `info` longtext character set utf8 collate utf8_unicode_ci,
  `source` tinyint(4) default NULL,
  `fname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `mname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `lname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `federaltaxid` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `federaldrugid` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `upin` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `facility` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `facility_id` int(11) NOT NULL default '0',
  `see_auth` int(11) NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  `npi` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  `title` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `specialty` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `billname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `email` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `url` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `assistant` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `organization` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `valedictory` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `street` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `streetb` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `city` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `state` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `zip` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `street2` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `streetb2` varchar(60) character set utf8 collate utf8_unicode_ci default NULL,
  `city2` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `state2` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `zip2` varchar(20) character set utf8 collate utf8_unicode_ci default NULL,
  `phone` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `fax` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `phonew1` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `phonew2` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `phonecell` varchar(30) character set utf8 collate utf8_unicode_ci default NULL,
  `notes` text character set utf8 collate utf8_unicode_ci,
  `cal_ui` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `users`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `x12_partners`
-- 

DROP TABLE IF EXISTS `x12_partners`;
CREATE TABLE `x12_partners` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `id_number` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_sender_id` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_receiver_id` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `x12_version` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `processing_format` enum('standard','medi-cal','cms','proxymed') character set latin1 default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `x12_partners`
-- 

------------------------------------------------------------------------------------- 
-- Table structure for table `automatic_notification`
-- 

DROP TABLE IF EXISTS `automatic_notification`;
CREATE TABLE `automatic_notification` (
  `notification_id` int(5) NOT NULL auto_increment,
  `sms_gateway_type` varchar(255) NOT NULL,
  `next_app_date` date NOT NULL,
  `next_app_time` varchar(10) NOT NULL,
  `provider_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `email_sender` varchar(100) NOT NULL,
  `email_subject` varchar(100) NOT NULL,
  `type` enum('SMS','Email') NOT NULL default 'SMS',
  `notification_sent_date` datetime NOT NULL,
  PRIMARY KEY  (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `automatic_notification`
-- 

INSERT INTO `automatic_notification` (`notification_id`, `sms_gateway_type`, `next_app_date`, `next_app_time`, `provider_name`, `message`, `email_sender`, `email_subject`, `type`, `notification_sent_date`) VALUES (1, 'CLICKATELL', '0000-00-00', ':', 'EMR GROUP 1 .. SMS', 'Welcome to EMR GROUP 1.. SMS', '', '', 'SMS', '0000-00-00 00:00:00'),
(2, '', '2007-10-02', '05:50', 'EMR GROUP', 'Welcome to EMR GROUP . Email', 'EMR Group', 'Welcome to EMR GROUP', 'Email', '2007-09-30 00:00:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `notification_log`
-- 

DROP TABLE IF EXISTS `notification_log`;
CREATE TABLE `notification_log` (
  `iLogId` int(11) NOT NULL auto_increment,
  `pid` int(7) NOT NULL,
  `pc_eid` int(11) unsigned NULL,
  `sms_gateway_type` varchar(50) NOT NULL,
  `smsgateway_info` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `email_sender` varchar(255) NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `type` enum('SMS','Email') NOT NULL,
  `patient_info` text NOT NULL,
  `pc_eventDate` date NOT NULL,
  `pc_endDate` date NOT NULL,
  `pc_startTime` time NOT NULL,
  `pc_endTime` time NOT NULL,
  `dSentDateTime` datetime NOT NULL,
  PRIMARY KEY  (`iLogId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `notification_log`
-- 
-- --------------------------------------------------------

-- 
-- Table structure for table `notification_settings`
-- 

DROP TABLE IF EXISTS `notification_settings`;
CREATE TABLE `notification_settings` (
  `SettingsId` int(3) NOT NULL auto_increment,
  `Send_SMS_Before_Hours` int(3) NOT NULL,
  `Send_Email_Before_Hours` int(3) NOT NULL,
  `SMS_gateway_username` varchar(100) NOT NULL,
  `SMS_gateway_password` varchar(100) NOT NULL,
  `SMS_gateway_apikey` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY  (`SettingsId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `notification_settings`
-- 

INSERT INTO `notification_settings` (`SettingsId`, `Send_SMS_Before_Hours`, `Send_Email_Before_Hours`, `SMS_gateway_username`, `SMS_gateway_password`, `SMS_gateway_apikey`, `type`) VALUES (1, 150, 150, 'sms username', 'sms password', 'sms api key', 'SMS/Email Settings');
------------------------------------------------------------------------------------- 
