-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:12 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `patient_data_NL`
--

CREATE TABLE IF NOT EXISTS `patient_data_NL` (
  `pdn_id` int(10) unsigned NOT NULL default '0',
  `pdn_pxlast` varchar(10) collate utf8_bin NOT NULL default '' COMMENT 'prefix of last name',
  `pdn_pxlastpar` varchar(10) collate utf8_bin NOT NULL default '' COMMENT 'prefix of last name partner',
  `pdn_lastpar` varchar(50) collate utf8_bin NOT NULL default '' COMMENT 'last name partner',
  `pdn_street` varchar(255) collate utf8_bin NOT NULL default '' COMMENT 'straat',
  `pdn_number` mediumint(8) unsigned NOT NULL default '0' COMMENT 'huisnummer',
  `pdn_addition` varchar(5) collate utf8_bin NOT NULL default '' COMMENT 'achtervoegsel',
  `pdn_initials` varchar(10) collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`pdn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='aditional informations only for Netherlands use';
