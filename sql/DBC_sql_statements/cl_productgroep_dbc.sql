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
-- Table structure for table `cl_productgroep_dbc`
--

CREATE TABLE IF NOT EXISTS `cl_productgroep_dbc` (
  `pc_productcode` mediumint(9) NOT NULL default '0' COMMENT 'sysid for productgroep',
  `pc_dbc` int(11) NOT NULL default '0' COMMENT 'dbc id',
  PRIMARY KEY  (`pc_dbc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
