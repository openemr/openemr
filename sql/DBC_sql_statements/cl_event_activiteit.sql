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
-- Table structure for table `cl_event_activiteit`
--

CREATE TABLE IF NOT EXISTS `cl_event_activiteit` (
  `event_id` int(11) NOT NULL default '0',
  `activity_sysid` mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='saves corelation between events and activities';
