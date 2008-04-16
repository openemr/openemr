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
-- Table structure for table `cl_user_beroep`
--

CREATE TABLE IF NOT EXISTS `cl_user_beroep` (
  `cl_beroep_userid` mediumint(9) NOT NULL default '0',
  `cl_beroep_sysid` mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (`cl_beroep_userid`),
  KEY `cl_beroep_sysid` (`cl_beroep_sysid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='corelation between users and their dutch jobs';
