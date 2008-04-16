-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:03 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_patient_number`
--

CREATE TABLE IF NOT EXISTS `cl_patient_number` (
  `pn_oemrid` bigint(11) NOT NULL default '0' COMMENT 'openemr id',
  `pn_id1250` char(14) NOT NULL default '' COMMENT 'unique patient number',
  PRIMARY KEY  (`pn_oemrid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
