-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:09 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_providers`
--

CREATE TABLE IF NOT EXISTS `cl_providers` (
  `pro_pid` int(10) unsigned NOT NULL default '0' COMMENT 'patient id',
  `pro_company` varchar(50) character set latin1 NOT NULL default '',
  `pro_initials` varchar(10) character set latin1 NOT NULL default '',
  `pro_prefix` varchar(7) character set latin1 NOT NULL default '',
  `pro_lname` varchar(50) character set latin1 NOT NULL default '',
  `pro_street` varchar(255) collate utf8_bin NOT NULL default '',
  `pro_number` mediumint(8) unsigned NOT NULL default '0',
  `pro_addition` varchar(5) character set latin1 NOT NULL default '',
  `pro_city` varchar(30) character set latin1 NOT NULL default '',
  `pro_zipcode` varchar(10) character set latin1 NOT NULL default '',
  `pro_phone` varchar(15) character set latin1 NOT NULL default '',
  `pro_fax` varchar(15) character set latin1 NOT NULL default '',
  `pro_email` varchar(25) character set latin1 NOT NULL default '',
  `pro_referer` tinyint(1) NOT NULL default '0' COMMENT 'if true the referer is the provider itself',
  PRIMARY KEY  (`pro_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='primary care provider';
