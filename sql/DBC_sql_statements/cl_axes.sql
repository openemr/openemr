-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 03:56 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_axes`
--

CREATE TABLE IF NOT EXISTS `cl_axes` (
  `ax_id` int(11) NOT NULL auto_increment,
  `ax_ztn` varchar(7) NOT NULL default '' COMMENT 'ztn related',
  `ax_open` tinyint(1) NOT NULL default '1' COMMENT '1 - open 0 - close',
  `ax_as1` text NOT NULL COMMENT 'serialized as1',
  `ax_as2` text NOT NULL COMMENT 'serialized as2',
  `ax_as3` text NOT NULL COMMENT 'serialized as3',
  `ax_as4` text NOT NULL COMMENT 'serialized as4',
  `ax_as5` text NOT NULL COMMENT 'serialized as5',
  `ax_odate` date NOT NULL default '0000-00-00' COMMENT 'open date',
  `ax_cdate` date NOT NULL default '0000-00-00' COMMENT 'close date',
  `ax_sti` tinyint(1) NOT NULL default '0' COMMENT 'sent to insurer? 1-true',
  `ax_pcode` varchar(12) NOT NULL default '',
  `ax_vkstatus` tinyint(3) unsigned NOT NULL default '0' COMMENT 'vektis status',
  PRIMARY KEY  (`ax_id`),
  KEY `ax_ztn` (`ax_ztn`,`ax_open`),
  KEY `ax_date` (`ax_odate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Save Axes Information' AUTO_INCREMENT=1 ;
