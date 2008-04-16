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
-- Table structure for table `cl_redensluiten`
--

CREATE TABLE IF NOT EXISTS `cl_redensluiten` (
  `cl_redensluiten_begindatum` date NOT NULL default '0000-00-00',
  `cl_redensluiten_einddatum` date NOT NULL default '0000-00-00',
  `cl_redensluiten_code` smallint(6) NOT NULL default '0',
  `cl_redensluiten_beschrijving` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `cl_redensluiten_sorteervolgorde` smallint(6) NOT NULL default '0',
  `cl_redensluiten_sysid` mediumint(9) NOT NULL default '0',
  `cl_redensluiten_branche_indicatie` tinyint(3) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cl_redensluiten`
--

INSERT INTO `cl_redensluiten` (`cl_redensluiten_begindatum`, `cl_redensluiten_einddatum`, `cl_redensluiten_code`, `cl_redensluiten_beschrijving`, `cl_redensluiten_sorteervolgorde`, `cl_redensluiten_sysid`, `cl_redensluiten_branche_indicatie`) VALUES
('2000-01-01', '2005-04-01', -1, 'Niet beschikbaar', 0, 110000, 1),
('2000-01-01', '9999-12-31', 1, 'Reden voor afsluiting bij patiënt/ niet bij behandelaar', 10, 110001, 0),
('2000-01-01', '9999-12-31', 2, 'Reden voor afsluiting bij behandelaar/ om inhoudelijke redenen', 20, 110002, 0),
('2007-01-01', '9999-12-31', 6, 'Reden voor afsluiten door beëindigen strafrechtelijke titel', 25, 110006, 2),
('2000-01-01', '9999-12-31', 3, 'In onderling overleg beëindigd zorgtraject/ patiënt uitbehandeld', 30, 110003, 0),
('2000-01-01', '9999-12-31', 4, 'Afsluiten DBC administratief of vanwege openen vervolg-DBC', 40, 110004, 0),
('2000-01-01', '9999-12-31', 5, 'Afsluiting na alleen pré-intake/intake/diagnostiek/crisisopvang', 50, 110005, 0);
