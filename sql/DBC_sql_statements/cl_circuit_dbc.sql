-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:00 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_circuit_dbc`
--

CREATE TABLE IF NOT EXISTS `cl_circuit_dbc` (
  `ccd_circuitcode` int(11) NOT NULL default '0' COMMENT 'circuit code',
  `ccd_dbcid` int(11) NOT NULL default '0' COMMENT 'id for dbc',
  PRIMARY KEY  (`ccd_dbcid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
