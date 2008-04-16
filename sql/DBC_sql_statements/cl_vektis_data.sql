-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:11 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_vektis_data`
--

CREATE TABLE IF NOT EXISTS `cl_vektis_data` (
  `cvd_session` varchar(14) collate utf8_bin NOT NULL default '' COMMENT 'unique session identifier',
  `cvd_pid` int(10) unsigned NOT NULL default '0' COMMENT 'patient id',
  `cvd_date` date NOT NULL default '0000-00-00',
  `cvd_116` varchar(12) collate utf8_bin NOT NULL default '' COMMENT 'unique invoice number per file',
  `cvd_uzovi` varchar(4) collate utf8_bin NOT NULL default '',
  `cvd_ztn` varchar(12) collate utf8_bin NOT NULL default '',
  `cvd_tariff` int(10) unsigned NOT NULL default '0',
  `cvd_426` varchar(20) collate utf8_bin NOT NULL default '' COMMENT 'unique invoice number per patient',
  `cvd_dbcid` int(10) unsigned NOT NULL default '0' COMMENT 'dbc id',
  KEY `cvd_pid` (`cvd_pid`),
  KEY `cvd_session` (`cvd_session`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='vektis data  - retain some values from the files';
