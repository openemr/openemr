-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 03:58 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_beroep`
--

CREATE TABLE IF NOT EXISTS `cl_beroep` (
  `cl_beroep_begindatum` date NOT NULL default '0000-00-00',
  `cl_beroep_einddatum` date NOT NULL default '0000-00-00',
  `cl_beroep_code` varchar(30) collate utf8_bin NOT NULL default '',
  `cl_beroep_groepcode` varchar(10) collate utf8_bin NOT NULL default '',
  `cl_beroep_element` varchar(50) collate utf8_bin NOT NULL default '',
  `cl_beroep_beschrijving` varchar(100) collate utf8_bin NOT NULL default '',
  `cl_beroep_hierarchieniveau` tinyint(4) NOT NULL default '0',
  `cl_beroep_selecteerbaar` tinyint(1) NOT NULL default '0',
  `cl_beroep_sorteervolgorde` mediumint(9) NOT NULL default '0',
  `cl_beroep_sysid` mediumint(9) NOT NULL default '0',
  `cl_beroep_branche_indicatie` tinyint(4) NOT NULL default '0' COMMENT 'new in 2008'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Jobs type';

--
-- Dumping data for table `cl_beroep`
--

INSERT INTO `cl_beroep` (`cl_beroep_begindatum`, `cl_beroep_einddatum`, `cl_beroep_code`, `cl_beroep_groepcode`, `cl_beroep_element`, `cl_beroep_beschrijving`, `cl_beroep_hierarchieniveau`, `cl_beroep_selecteerbaar`, `cl_beroep_sorteervolgorde`, `cl_beroep_sysid`, `cl_beroep_branche_indicatie`) VALUES
('2000-01-01', '2005-04-01', 'nb', '', 'Niet beschikbaar', '', 0, 1, 10, 100000, 1),
('2000-01-01', '2006-12-31', 'AD', '', 'Administratief', 'Administratief', 1, 1, 20, 100501, 1),
('2000-01-01', '9999-12-31', 'AG', '', 'Agogische beroepen', 'Agogische beroepen', 1, 0, 30, 100001, 0),
('2000-01-01', '9999-12-31', 'AG.BG', 'AG', 'Basisberoep Gezondheidszorg', 'Agogische beroepen: Basisberoep Gezondheidszorg', 2, 0, 40, 100002, 0),
('2000-01-01', '2005-04-01', 'AG.BG.actbeg', 'AG.BG', 'GZ-agoog activiteitenbegeleider', '', 3, 1, 50, 100003, 1),
('2000-01-01', '9999-12-31', 'AG.BG.agoog', 'AG.BG', 'GGZ-agoog', 'GGZ-agoog', 3, 1, 60, 100502, 0),
('2000-01-01', '2005-04-01', 'AG.BG.mw', 'AG.BG', 'GZ-agoog maatschappelijk werkende', '', 3, 1, 70, 100004, 1),
('2000-01-01', '2005-04-01', 'AG.BG.sph', 'AG.BG', 'GZ-agoog sociaal psychiatrisch hulpverlener', '', 3, 1, 80, 100005, 1),
('2000-01-01', '9999-12-31', 'AG.BI', 'AG', 'Basisberoep initieel', 'GGZ-agoog: Basisberoep initieel', 2, 0, 90, 100006, 0),
('2000-01-01', '2005-04-01', 'AG.BI.msd', 'AG.BI', 'Medewerker sociale dienstverlening', '', 3, 1, 100, 100007, 1),
('2000-01-01', '2005-04-01', 'AG.BI.mw', 'AG.BI', 'Maatschappelijk werkende', '', 3, 1, 110, 100008, 1),
('2000-01-01', '9999-12-31', 'AG.BI.mwd', 'AG.BI', 'Maatschappelijk werkende', 'Maatschappelijk werkende', 3, 1, 120, 100503, 0),
('2000-01-01', '2005-04-01', 'AG.BI.orthop', 'AG.BI', 'Orthopedagoog', '', 3, 1, 130, 100009, 1),
('2000-01-01', '2005-04-01', 'AG.BI.pedmed', 'AG.BI', 'Pedagogisch medewerker', '', 3, 1, 140, 100010, 1),
('2000-01-01', '2005-04-01', 'AG.BI.rehmed', 'AG.BI', 'Rehabilitatiemedewerker', '', 3, 1, 150, 100011, 1),
('2000-01-01', '2005-04-01', 'AG.BI.socioth', 'AG.BI', 'Sociothereapeut', '', 3, 1, 160, 100012, 1),
('2000-01-01', '9999-12-31', 'AG.BI.sph', 'AG.BI', 'Sociaal Pedagogisch Hulpverlener', 'Sociaal Pedagogisch Hulpverlener', 3, 1, 170, 100013, 0),
('2000-01-01', '2005-04-01', 'AG.BI.trbeg', 'AG.BI', 'Trajectbegeleider', '', 3, 1, 180, 100014, 1),
('2000-01-01', '9999-12-31', 'AG.SF', 'AG', 'Specialisatie / functiedifferentiatie', 'Sociaal Pedagogisch Hulpverlener: Specialisatie / functiedifferentiatie', 2, 0, 190, 100504, 0),
('2000-01-01', '9999-12-31', 'AG.SF.kjpsych', 'AG.SF', 'Agoog K&J psychiatrie', 'Agoog K&J psychiatrie', 3, 1, 200, 100505, 0),
('2000-01-01', '9999-12-31', 'AG.SF.overig', 'AG.SF', 'Overig Agogisch SF', 'Overig Agogisch SF', 3, 1, 210, 100506, 0),
('2000-01-01', '9999-12-31', 'AG.SF.vrstgeh', 'AG.SF', 'Agoog verstandelijk gehandicapten', 'Agoog verstandelijk gehandicapten', 3, 1, 220, 100507, 0),
('2000-01-01', '9999-12-31', 'MB', '', 'Medische beroepen', 'Medische beroepen', 1, 0, 230, 100015, 0),
('2000-01-01', '9999-12-31', 'MB.BG', 'MB', 'Basisberoep Gezondheidszorg', 'Medische beroepen: Basisberoep Gezondheidszorg', 2, 0, 240, 100016, 0),
('2000-01-01', '2005-04-01', 'MB.BG.agio', 'MB.BG', 'Agio', '', 3, 1, 250, 100017, 1),
('2000-01-01', '2005-04-01', 'MB.BG.agnio', 'MB.BG', 'Agnio', '', 3, 1, 260, 100018, 1),
('2000-01-01', '9999-12-31', 'MB.BG.basis', 'MB.BG', 'Arts (waaronder Agio/Agnio)', 'Arts (waaronder Agio/Agnio)', 3, 1, 270, 100019, 0),
('2000-01-01', '9999-12-31', 'MB.SF', 'MB', 'Specialisatie / functiedifferentiatie', 'Medische beroepen: Specialisatie / functiedifferentiatie', 2, 0, 280, 100020, 0),
('2000-01-01', '2005-04-01', 'MB.SF.geriat', 'MB.SF', 'Geriater', '', 3, 1, 290, 100021, 1),
('2000-01-01', '9999-12-31', 'MB.SF.overig', 'MB.SF', 'Overig medisch SF', 'Overig medisch SF', 3, 1, 300, 100508, 0),
('2000-01-01', '9999-12-31', 'MB.SF.sger', 'MB.SF', 'Sociaal geriater', 'Sociaal geriater', 3, 1, 310, 100509, 0),
('2007-01-01', '9999-12-31', 'MB.SF.AVG', 'MB.SF', 'Arts voor verstandelijk gehandicapten (AVG)', 'Arts voor verstandelijk gehandicapten (AVG)', 3, 1, 315, 100071, 2),
('2000-01-01', '9999-12-31', 'MB.SF.vslarts', 'MB.SF', 'Arts verslavingszorg', 'Arts verslavingszorg', 3, 1, 320, 100022, 0),
('2000-01-01', '9999-12-31', 'MB.SP', 'MB', 'Specialisme', 'Medische beroepen: Specialisme', 2, 0, 330, 100023, 0),
('2000-01-01', '9999-12-31', 'MB.SP.psych', 'MB.SP', 'Psychiater', 'Psychiater', 3, 1, 340, 100024, 0),
('2000-01-01', '2005-04-01', 'MB.SP.vplarts', 'MB.SP', 'Verpleeghuisarts', '', 3, 1, 350, 100025, 1),
('2000-01-01', '9999-12-31', 'OV', '', 'Somatische beroepen (wet BIG)', 'Somatische beroepen (wet BIG)', 1, 0, 360, 100027, 0),
('2000-01-01', '9999-12-31', 'OV.BG', 'OV', 'Basisberoep Gezondheidszorg', 'Somatische beroepen (wet BIG): Basisberoep Gezondheidszorg', 2, 0, 370, 100028, 0),
('2000-01-01', '2005-04-01', 'OV.BG.ad', 'OV.BG', 'Administratief', '', 3, 1, 380, 100064, 1),
('2000-01-01', '9999-12-31', 'OV.BG.diet', 'OV.BG', 'Dietist', 'Dietist', 3, 1, 390, 100029, 0),
('2000-01-01', '9999-12-31', 'OV.BG.ergo', 'OV.BG', 'Ergotherapeut', 'Ergotherapeut', 3, 1, 400, 100030, 0),
('2000-01-01', '9999-12-31', 'OV.BG.fysio', 'OV.BG', 'Fysiotherapeut', 'Fysiotherapeut', 3, 1, 410, 100031, 0),
('2007-01-01', '9999-12-31', 'OV.BG.logo', 'OV.BG', 'Logopedist', 'Logopedist', 3, 1, 410, 110031, 0),
('2000-01-01', '9999-12-31', 'OV.SP', 'OV', 'Specialisme', 'Somatische beroepen (wet BIG): Specialisme', 2, 0, 420, 100510, 0),
('2000-01-01', '9999-12-31', 'OV.SP.artsmg', 'OV.SP', 'Arts maatschappij en gezondheid', 'Arts maatschappij en gezondheid', 3, 1, 430, 100511, 0),
('2000-01-01', '9999-12-31', 'OV.SP.harts', 'OV.SP', 'Huisarts', 'Huisarts', 3, 1, 440, 100512, 0),
('2000-01-01', '9999-12-31', 'OV.SP.karts', 'OV.SP', 'Kinderarts', 'Kinderarts', 3, 1, 450, 100513, 0),
('2000-01-01', '9999-12-31', 'OV.SP.kger', 'OV.SP', 'Klinisch geriater', 'Klinisch geriater', 3, 1, 460, 100514, 0),
('2000-01-01', '9999-12-31', 'OV.SP.neur', 'OV.SP', 'Neuroloog', 'Neuroloog', 3, 1, 470, 100515, 0),
('2000-01-01', '9999-12-31', 'PB', '', 'Psychologische beroepen', 'Psychologische beroepen', 1, 0, 480, 100032, 0),
('2000-01-01', '9999-12-31', 'PB.BG', 'PB', 'Basisberoep Gezondheidszorg', 'Psychologische beroepen: Basisberoep Gezondheidszorg', 2, 0, 490, 100033, 0),
('2000-01-01', '9999-12-31', 'PB.BG.gzpsy', 'PB.BG', 'GZ-psycholoog', 'GZ-psycholoog', 3, 1, 500, 100516, 0),
('2000-01-01', '2005-04-01', 'PB.BG.psy', 'PB.BG', 'GZ-psycholoog', '', 3, 1, 510, 100034, 1),
('2000-01-01', '9999-12-31', 'PB.BI', 'PB', 'Basisberoep initieel', 'Psychologische beroepen: Basisberoep initieel', 2, 0, 520, 100035, 0),
('2000-01-01', '9999-12-31', 'PB.BI.gzkd', 'PB.BI', 'GGZ gezondheidskundige', 'GGZ gezondheidskundige', 3, 1, 530, 100517, 0),
('2000-01-01', '2005-04-01', 'PB.BI.opl', 'PB.BI', 'Psychologisch beroep in opleiding', '', 3, 1, 540, 100036, 1),
('2000-01-01', '9999-12-31', 'PB.BI.ped', 'PB.BI', 'Pedagoog (waaronder orthopedagoog)', 'Pedagoog (waaronder orthopedagoog)', 3, 1, 550, 100037, 0),
('2000-01-01', '2005-04-01', 'PB.BI.psdiagn', 'PB.BI', 'Psychodiagnostisch medewerker', '', 3, 1, 560, 100038, 1),
('2000-01-01', '9999-12-31', 'PB.BI.psy', 'PB.BI', 'Psycholoog (geen verdere specialisatie)', 'Psycholoog (geen verdere specialisatie)', 3, 1, 570, 100039, 0),
('2000-01-01', '2005-04-01', 'PB.BI.testass', 'PB.BI', 'Psychologisch testassistent', '', 3, 1, 580, 100040, 1),
('2000-01-01', '9999-12-31', 'PB.SF', 'PB', 'Specialisatie / functiedifferentiatie', 'Psychologische beroepen: Specialisatie / functiedifferentiatie', 2, 0, 590, 100041, 0),
('2000-01-01', '9999-12-31', 'PB.SF.gedrth', 'PB.SF', 'Gedragstherapeut', 'Gedragstherapeut', 3, 1, 600, 100042, 0),
('2000-01-01', '9999-12-31', 'PB.SF.kjth', 'PB.SF', 'K&J-therapeut', 'K&J-therapeut', 3, 1, 610, 100518, 0),
('2000-01-01', '9999-12-31', 'PB.SF.overig', 'PB.SF', 'Overig psychologisch SF', 'Overig psychologisch SF', 3, 1, 620, 100519, 0),
('2000-01-01', '2005-04-01', 'PB.SF.systth', 'PB.SF', 'Systeemtherapeut', '', 3, 1, 630, 100043, 1),
('2000-01-01', '9999-12-31', 'PB.SP', 'PB', 'Specialisme', 'Psychologische beroepen: Specialisme', 2, 0, 640, 100044, 0),
('2000-01-01', '9999-12-31', 'PB.SP.klinps', 'PB.SP', 'Klinisch psycholoog', 'Klinisch psycholoog', 3, 1, 650, 100045, 0),
('2000-01-01', '2005-04-01', 'PB.SP.psychth', 'PB.SP', 'Psychotherapeut', '', 3, 1, 660, 100046, 1),
('2000-01-01', '9999-12-31', 'PT', '', 'Psychotherapeutische beroepen', 'Psychotherapeutische beroepen', 1, 0, 670, 100520, 0),
('2000-01-01', '9999-12-31', 'PT.BG', 'PT', 'Basisberoep Gezondheidszorg', 'Psychotherapeutische beroepen: Basisberoep Gezondheidszorg', 2, 0, 680, 100521, 0),
('2000-01-01', '9999-12-31', 'PT.BG.psth', 'PT.BG', 'Psychotherapeut', 'Psychotherapeut', 3, 1, 690, 100522, 0),
('2000-01-01', '9999-12-31', 'VB', '', 'Verpleegkundige beroepen', 'Verpleegkundige beroepen', 1, 0, 700, 100047, 0),
('2000-01-01', '9999-12-31', 'VB.BG', 'VB', 'Basisberoep Gezondheidszorg', 'Verpleegkundige beroepen: Basisberoep Gezondheidszorg', 2, 0, 710, 100048, 0),
('2000-01-01', '2005-04-01', 'VB.BG.pv', 'VB.BG', 'Psychiatrisch Verpleegkundige', '', 3, 1, 720, 100049, 1),
('2000-01-01', '9999-12-31', 'VB.BG.vrplk', 'VB.BG', 'Verpleegkundige (art.3)', 'Verpleegkundige (art.3)', 3, 1, 730, 100523, 0),
('2000-01-01', '2005-04-01', 'VB.BI', 'VB', 'Basisberoep initieel (BI)', '', 2, 0, 740, 100050, 1),
('2000-01-01', '2005-04-01', 'VB.BI.verpopl', 'VB.BI', 'Verpleegkundig beroep in opleiding', '', 3, 1, 750, 100065, 1),
('2000-01-01', '2005-04-01', 'VB.BI.ziekverz', 'VB.BI', 'Ziekenverzorger', '', 3, 1, 760, 100066, 1),
('2000-01-01', '9999-12-31', 'VB.SF', 'VB', 'Specialisatie / functiedifferentiatie', 'Verpleegkundige beroepen: Specialisatie / functiedifferentiatie', 2, 0, 770, 100051, 0),
('2000-01-01', '9999-12-31', 'VB.SF.cpv', 'VB.SF', 'Consultatief Psych. Verpleegkundige', 'Consultatief Psych. Verpleegkundige', 3, 1, 780, 100052, 0),
('2000-01-01', '9999-12-31', 'VB.SF.fpv', 'VB.SF', 'Forensisch Psychiatrisch Verpleegkundige', 'Forensisch Psychiatrisch Verpleegkundige', 3, 1, 790, 100524, 0),
('2000-01-01', '9999-12-31', 'VB.SF.overig', 'VB.SF', 'Overig verpleegkundig SF', 'Overig verpleegkundig SF', 3, 1, 800, 100525, 0),
('2000-01-01', '2005-04-01', 'VB.SF.ptv', 'VB.SF', 'Psychiatrisch Thuiszorg Verpleegkundige', '', 3, 1, 810, 100053, 1),
('2000-01-01', '9999-12-31', 'VB.SF.spv', 'VB.SF', 'Sociaal Psych. Verpleegkundige', 'Sociaal Psych. Verpleegkundige', 3, 1, 820, 100054, 0),
('2000-01-01', '9999-12-31', 'VB.SP', 'VB', 'Specialisme', 'Verpleegkundige beroepen: Specialisme', 2, 0, 830, 100055, 0),
('2000-01-01', '2005-04-01', 'VB.SP.vpspec', 'VB.SP', 'GGZ Verpleegkundig specialist', '', 3, 1, 840, 100056, 1),
('2000-01-01', '9999-12-31', 'VB.SP.vrplsp', 'VB.SP', 'GGZ Verpleegkundige specialist', 'GGZ Verpleegkundige specialist', 3, 1, 850, 100526, 0),
('2000-01-01', '9999-12-31', 'VK', '', 'Vaktherapeutische beroepen', 'Vaktherapeutische beroepen', 1, 0, 860, 100057, 0),
('2000-01-01', '9999-12-31', 'VK.BG', 'VK', 'Basisberoep Gezondheidszorg', 'Vaktherapeutische beroepen: Basisberoep Gezondheidszorg', 2, 0, 870, 100058, 0),
('2000-01-01', '2005-04-01', 'VK.BG.crea', 'VK.BG', 'Vaktherepeut creatief', '', 3, 1, 880, 100059, 1),
('2000-01-01', '2005-04-01', 'VK.BG.psychmt', 'VK.BG', 'Vaktherapeut psychomotorisch', '', 3, 1, 890, 100060, 1),
('2000-01-01', '9999-12-31', 'VK.BG.vakth', 'VK.BG', 'GZ- vaktherapeut', 'GZ- vaktherapeut', 3, 1, 900, 100527, 0),
('2000-01-01', '9999-12-31', 'VK.BI', 'VK', 'Basisberoep initieel', 'Vaktherapeutische beroepen: Basisberoep initieel', 2, 0, 910, 100061, 0),
('2000-01-01', '2005-04-01', 'VK.BI.arbth', 'VK.BI', 'Arbeidstherapeut', '', 3, 1, 920, 100062, 1),
('2000-01-01', '9999-12-31', 'VK.BI.ct', 'VK.BI', 'Vaktherapeut creatief', 'Vaktherapeut creatief', 3, 1, 930, 100528, 0),
('2000-01-01', '2005-04-01', 'VK.BI.medarb', 'VK.BI', 'Medewerker arbeidsgerichte activiteiten', '', 3, 1, 940, 100067, 1),
('2000-01-01', '2005-04-01', 'VK.BI.mededu', 'VK.BI', 'Medewerker educatieve activiteiten', '', 3, 1, 950, 100068, 1),
('2000-01-01', '2005-04-01', 'VK.BI.medrecr', 'VK.BI', 'Medewerker recreatieve activiteiten', '', 3, 1, 960, 100069, 1),
('2000-01-01', '2005-04-01', 'VK.BI.medspsp', 'VK.BI', 'Medewerker sport en spel', '', 3, 1, 970, 100063, 1),
('2000-01-01', '9999-12-31', 'VK.BI.pmt', 'VK.BI', 'Vaktherapeut psychomotorisch', 'Vaktherapeut psychomotorisch', 3, 1, 980, 100529, 0),
('2000-01-01', '2005-04-01', 'VK.BI.vaard', 'VK.BI', 'Vaardigheidstrainer', '', 3, 1, 990, 100070, 1),
('2000-01-01', '9999-12-31', 'VK.SF', 'VK', 'Specialisatie / functiedifferentiatie', 'Vaktherapeutische beroepen: Specialisatie / functiedifferentiatie', 2, 0, 1000, 100530, 0),
('2000-01-01', '9999-12-31', 'VK.SF.overig', 'VK.SF', 'Overig vaktherapeutisch SF', 'Overig vaktherapeutisch SF', 3, 1, 1010, 100531, 0),
('2000-01-01', '9999-12-31', 'VK.SF.vakth', 'VK.SF', 'GGZ-vaktherapeut', 'GGZ-vaktherapeut', 3, 1, 1020, 100532, 0);
