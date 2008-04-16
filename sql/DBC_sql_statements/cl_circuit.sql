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
-- Table structure for table `cl_circuit`
--

CREATE TABLE IF NOT EXISTS `cl_circuit` (
  `cl_circuit_begindatum` date NOT NULL default '0000-00-00',
  `cl_circuit_einddatum` date NOT NULL default '0000-00-00',
  `cl_circuit_code` smallint(6) NOT NULL default '0',
  `cl_circuit_beschrijving` varchar(100) NOT NULL default '',
  `cl_circuit_sorteervolgorde` int(11) NOT NULL default '0',
  `cl_circuit_sysid` int(11) NOT NULL default '0',
  `cl_circuit_branche_indicatie` tinyint(4) NOT NULL default '0' COMMENT 'new in 2008',
  PRIMARY KEY  (`cl_circuit_sysid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='used in edit facility';

--
-- Dumping data for table `cl_circuit`
--

INSERT INTO `cl_circuit` (`cl_circuit_begindatum`, `cl_circuit_einddatum`, `cl_circuit_code`, `cl_circuit_beschrijving`, `cl_circuit_sorteervolgorde`, `cl_circuit_sysid`, `cl_circuit_branche_indicatie`) VALUES
('2000-01-01', '2005-04-01', -1, 'Niet beschikbaar', 10, 90000, 1),
('2000-01-01', '9999-12-31', 1, 'Volwassenen lang', 20, 90001, 1),
('2000-01-01', '9999-12-31', 2, 'Volwassenen kort', 30, 90002, 1),
('2000-01-01', '9999-12-31', 3, 'Ouderen', 40, 90003, 1),
('2000-01-01', '9999-12-31', 4, 'Kinder & Jeugd', 50, 90004, 1),
('2000-01-01', '9999-12-31', 5, 'Verslavingszorg', 60, 90005, 1),
('2000-01-01', '2007-12-31', 6, 'Forensisch', 70, 90006, 1),
('2008-01-01', '9999-12-31', 7, 'Forensisch Jeugd', 71, 90007, 1),
('2007-01-01', '9999-12-31', 8, 'Forensisch Volwassenen in strafrechtelijk kader (SK)', 72, 90008, 2),
('2008-01-01', '9999-12-31', 9, 'Forensisch Volwassenen niet in strafrechtelijk kader (SK)', 73, 90009, 1);
