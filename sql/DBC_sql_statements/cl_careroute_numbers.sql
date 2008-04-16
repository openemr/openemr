-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 03:59 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_careroute_numbers`
--

CREATE TABLE IF NOT EXISTS `cl_careroute_numbers` (
  `cn_ztn` char(7) NOT NULL default '0000000' COMMENT 'ZorgTrajectNummer',
  `cn_pid` bigint(20) NOT NULL default '0',
  `cn_dopen` date NOT NULL default '0000-00-00' COMMENT 'open date',
  `cn_dclosed` date NOT NULL default '9999-12-31' COMMENT 'close date',
  `cn_open` tinyint(1) NOT NULL default '0' COMMENT 'open/close switch',
  PRIMARY KEY  (`cn_ztn`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Care Route Numbers (Zorgtrajectnummer)';
