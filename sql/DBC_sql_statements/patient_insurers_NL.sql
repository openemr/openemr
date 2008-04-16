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
-- Table structure for table `patient_insurers_NL`
--

CREATE TABLE IF NOT EXISTS `patient_insurers_NL` (
  `pin_pid` int(10) unsigned NOT NULL default '0' COMMENT 'patient id',
  `pin_provider` mediumint(8) unsigned NOT NULL default '0' COMMENT 'insurer',
  `pin_date` date NOT NULL default '0000-00-00' COMMENT 'starting date for insurance',
  `pin_policy` varchar(30) collate utf8_bin NOT NULL default '' COMMENT 'policy number',
  KEY `pin_pid` (`pin_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='insurance providers and patients';
