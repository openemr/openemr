-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:10 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_referers`
--

CREATE TABLE IF NOT EXISTS `cl_referers` (
  `ref_pid` int(10) unsigned NOT NULL default '0' COMMENT 'patient id',
  `ref_code` varchar(10) character set latin1 NOT NULL default '',
  `ref_company` varchar(50) character set latin1 NOT NULL default '',
  `ref_initials` varchar(10) character set latin1 NOT NULL default '',
  `ref_prefix` varchar(7) character set latin1 NOT NULL default '',
  `ref_lname` varchar(50) character set latin1 NOT NULL default '',
  `ref_street` varchar(255) collate utf8_bin NOT NULL default '',
  `ref_number` mediumint(8) unsigned NOT NULL default '0',
  `ref_addition` varchar(5) character set latin1 NOT NULL default '',
  `ref_city` varchar(30) character set latin1 NOT NULL default '',
  `ref_zipcode` varchar(10) character set latin1 NOT NULL default '',
  `ref_phone` varchar(15) character set latin1 NOT NULL default '',
  `ref_fax` varchar(15) character set latin1 NOT NULL default '',
  `ref_email` varchar(25) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`ref_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='referers for demographics_full;';
