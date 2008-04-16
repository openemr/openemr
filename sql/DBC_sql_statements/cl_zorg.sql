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
-- Table structure for table `cl_zorg`
--

CREATE TABLE IF NOT EXISTS `cl_zorg` (
  `cl_zorgtype_begindatum` date NOT NULL default '0000-00-00',
  `cl_zorgtype_einddatum` date NOT NULL default '0000-00-00',
  `cl_zorgtype_code` varchar(10) NOT NULL default '',
  `cl_zorgtype_groepcode` smallint(6) NOT NULL default '0',
  `cl_zorgtype_element` varchar(50) NOT NULL default '',
  `cl_zorgtype_beschrijving` varchar(50) NOT NULL default '',
  `cl_zorgtype_hierarchieniveau` smallint(6) NOT NULL default '0',
  `cl_zorgtype_selecteerbaar` smallint(6) NOT NULL default '0',
  `cl_zorgtype_sorteervolgorde` smallint(6) NOT NULL default '0',
  `cl_zorgtype_prestatiecodedeel` smallint(6) unsigned NOT NULL default '0',
  `cl_zorgtype_mutatie` tinyint(3) unsigned NOT NULL default '0',
  `cl_zorgtype_sysid` int(11) NOT NULL default '0',
  `cl_zorgtype_branche_indicatie` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cl_zorgtype_sysid`),
  KEY `cl_zorgtype_begindatum` (`cl_zorgtype_begindatum`,`cl_zorgtype_einddatum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cl_zorg`
--

INSERT INTO `cl_zorg` (`cl_zorgtype_begindatum`, `cl_zorgtype_einddatum`, `cl_zorgtype_code`, `cl_zorgtype_groepcode`, `cl_zorgtype_element`, `cl_zorgtype_beschrijving`, `cl_zorgtype_hierarchieniveau`, `cl_zorgtype_selecteerbaar`, `cl_zorgtype_sorteervolgorde`, `cl_zorgtype_prestatiecodedeel`, `cl_zorgtype_mutatie`, `cl_zorgtype_sysid`, `cl_zorgtype_branche_indicatie`) VALUES
('2000-01-01', '2005-04-01', '-1', 0, 'Niet beschikbaar', 'Niet beschikbaar', 1, 0, 10, 0, 0, 180000, 1),
('2000-01-01', '2005-04-01', 'conv2004', 0, 'Conversie 2004', 'Conversie 2004', 1, 1, 20, 0, 0, 182000, 1),
('2000-01-01', '9999-12-31', '100', 0, 'Initieel zorgtype', 'Initieel zorgtype', 1, 0, 30, 0, 0, 180100, 1),
('2000-01-01', '9999-12-31', '101', 100, 'Reguliere zorg', 'Reguliere zorg', 2, 1, 40, 101, 0, 180101, 1),
('2000-01-01', '9999-12-31', '102', 100, 'Eenmalig spoedeisend consult/crisisinterventie', 'Eenmalig spoedeisend consult/crisisinterventie', 2, 1, 50, 102, 0, 180102, 1),
('2000-01-01', '9999-12-31', '103', 100, 'Acute opname', 'Acute opname', 2, 1, 60, 103, 0, 180103, 1),
('2000-01-01', '9999-12-31', '104', 100, 'Intercollegiaal consult', 'Intercollegiaal consult', 2, 1, 70, 104, 0, 180104, 1),
('2000-01-01', '9999-12-31', '105', 100, 'Medebehandeling', 'Medebehandeling', 2, 1, 80, 105, 0, 180105, 1),
('2000-01-01', '9999-12-31', '106', 100, 'Second opinion', 'Second opinion', 2, 1, 90, 106, 0, 180106, 1),
('2000-01-01', '9999-12-31', '107', 100, 'Zorg op basis van tertiaire verwijzing', 'Zorg op basis van tertiaire verwijzing', 2, 1, 100, 107, 0, 180107, 1),
('2000-01-01', '9999-12-31', '108', 100, 'Langdurig periodieke controle (bij overname)', 'Langdurig periodieke controle (bij overname)', 2, 1, 110, 108, 0, 180108, 1),
('2000-01-01', '9999-12-31', '109', 100, 'Bemoeizorg', 'Bemoeizorg', 2, 1, 120, 109, 0, 180109, 1),
('2000-01-01', '9999-12-31', '110', 100, 'Rechtelijke machtiging', 'Rechtelijke machtiging', 2, 1, 130, 101, 0, 180110, 1),
('2000-01-01', '9999-12-31', '111', 100, 'Inbewaringstelling', 'Inbewaringstelling', 2, 1, 140, 101, 0, 180111, 1),
('2000-01-01', '2007-12-31', '112', 100, 'Terbeschikkingstelling', 'Terbeschikkingstelling', 2, 1, 150, 0, 2, 180112, 1),
('2008-01-01', '9999-12-31', '112', 100, 'Terbeschikkingstelling', 'Terbeschikkingstelling', 2, 1, 150, 0, 1, 181112, 2),
('2000-01-01', '2007-12-31', '113', 100, 'Terbeschikkingstelling met voorwaarden', 'Terbeschikkingstelling met voorwaarden', 2, 1, 160, 0, 2, 180113, 1),
('2007-01-01', '9999-12-31', '113', 100, 'Terbeschikkingstelling met voorwaarden', 'Terbeschikkingstelling met voorwaarden', 2, 1, 160, 0, 1, 181113, 2),
('2007-01-01', '9999-12-31', '118', 100, 'Detentie bijzondere zorg in gevangeniswezen', 'Detentie bijzondere zorg in gevangeniswezen', 2, 1, 161, 0, 1, 180118, 2),
('2007-01-01', '9999-12-31', '119', 100, 'Detentie bijzondere zorg buiten gevangeniswezen', 'Detentie bijzondere zorg buiten gevangeniswezen', 2, 1, 162, 0, 1, 180119, 2),
('2007-01-01', '9999-12-31', '120', 100, 'Titels met voorwaarden + artikel 37 Sr', 'Titels met voorwaarden + artikel 37 Sr', 2, 1, 163, 0, 1, 180120, 2),
('2000-01-01', '2007-12-31', '114', 100, 'Strafrechtelijke machtiging', 'Strafrechtelijke machtiging', 2, 1, 170, 0, 2, 180114, 1),
('2000-01-01', '9999-12-31', '115', 100, 'Ondertoezichtstelling', 'Ondertoezichtstelling', 2, 1, 180, 101, 0, 180115, 1),
('2008-01-01', '9999-12-31', '116', 100, 'Rechtelijke machtiging met voorwaarden', 'Rechtelijke machtiging met voorwaarden', 2, 1, 181, 101, 1, 180116, 1),
('2008-01-01', '9999-12-31', '117', 100, 'Jeugdstrafrecht', 'Jeugdstrafrecht', 2, 1, 182, 101, 1, 180117, 1),
('2000-01-01', '9999-12-31', '200', 0, 'Vervolgzorgtype', 'Vervolgzorgtype', 1, 0, 190, 0, 0, 180200, 1),
('2000-01-01', '9999-12-31', '201', 200, '(Langdurige periodieke) controle', '(Langdurige periodieke) controle', 2, 1, 200, 201, 0, 180201, 1),
('2000-01-01', '9999-12-31', '202', 200, 'Voortgezette behandeling', 'Voortgezette behandeling', 2, 1, 210, 202, 0, 180202, 1),
('2000-01-01', '9999-12-31', '203', 200, 'Uitloop', 'Uitloop', 2, 1, 220, 203, 0, 180203, 1),
('2000-01-01', '9999-12-31', '204', 200, 'Exacerbatie/recidive', 'Exacerbatie/recidive', 2, 1, 230, 204, 0, 180204, 1),
('2000-01-01', '9999-12-31', '205', 200, 'Bemoeizorg', 'Bemoeizorg', 2, 1, 240, 205, 0, 180205, 1),
('2000-01-01', '9999-12-31', '206', 200, 'Rechtelijke machtiging', 'Rechtelijke machtiging', 2, 1, 250, 202, 0, 180206, 1),
('2000-01-01', '2007-12-31', '207', 200, 'Terbeschikkingstelling', 'Terbeschikkingstelling', 2, 1, 260, 0, 2, 180207, 1),
('2007-01-01', '9999-12-31', '207', 200, 'Terbeschikkingstelling', 'Terbeschikkingstelling', 2, 1, 260, 0, 1, 181207, 2),
('2000-01-01', '2007-12-31', '208', 200, 'Terbeschikkingstelling met voorwaarden', 'Terbeschikkingstelling met voorwaarden', 2, 1, 270, 0, 2, 180208, 1),
('2007-01-01', '9999-12-31', '208', 200, 'Terbeschikkingstelling met voorwaarden', 'Terbeschikkingstelling met voorwaarden', 2, 1, 270, 0, 1, 181208, 2),
('2007-01-01', '9999-12-31', '213', 200, 'Detentie bijzondere zorg in gevangeniswezen', 'Detentie bijzondere zorg in gevangeniswezen', 2, 1, 271, 0, 1, 180213, 2),
('2007-01-01', '9999-12-31', '214', 200, 'Detentie bijzondere zorg buiten gevangeniswezen', 'Detentie bijzondere zorg buiten gevangeniswezen', 2, 1, 272, 0, 1, 180214, 2),
('2007-01-01', '9999-12-31', '215', 200, 'Titels met voorwaarden + artikel 37 Sr', 'Titels met voorwaarden + artikel 37 Sr', 2, 1, 273, 0, 1, 180215, 2),
('2000-01-01', '2007-12-31', '209', 200, 'Strafrechtelijke machtiging', 'Strafrechtelijke machtiging', 2, 1, 280, 0, 2, 180209, 1),
('2000-01-01', '9999-12-31', '210', 200, 'Ondertoezichtstelling', 'Ondertoezichtstelling', 2, 1, 290, 202, 0, 180210, 1),
('2008-01-01', '9999-12-31', '211', 200, 'Rechtelijke machtiging met voorwaarden', 'Rechtelijke machtiging met voorwaarden', 2, 1, 300, 202, 1, 180211, 1),
('2008-01-01', '9999-12-31', '212', 200, 'Jeugdstrafrecht', 'Jeugdstrafrecht', 2, 1, 310, 202, 1, 180212, 1);
