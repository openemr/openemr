-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 13, 2014 at 09:49 AM
-- Server version: 5.1.70
-- PHP Version: 5.3.2-1ubuntu4.20

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `dbREFRACTIONS`
--

CREATE TABLE IF NOT EXISTS `dbREFRACTIONS` (
`UID` varchar(50) DEFAULT NULL,
`REFDATE` char(10) DEFAULT NULL,
`REFTYPE` char(10) DEFAULT NULL,
`ODSPH` char(10) DEFAULT NULL,
`ODCYL` char(10) DEFAULT NULL,
`ODAXIS` char(10) DEFAULT NULL,
`OSSPH` char(10) DEFAULT NULL,
`OSCYL` char(10) DEFAULT NULL,
`OSAXIS` char(10) DEFAULT NULL,
`ODPRISM` varchar(50) DEFAULT NULL,
`ODBASE` varchar(50) DEFAULT NULL,
`OSPRISM` varchar(50) DEFAULT NULL,
`OSBASE` varchar(50) DEFAULT NULL,
`ODADD` varchar(50) DEFAULT NULL,
`OSADD` varchar(50) DEFAULT NULL,
`ODADD2` varchar(50) DEFAULT NULL,
`OSADD2` varchar(50) DEFAULT NULL,
`PD` varchar(50) DEFAULT NULL,
`RXCOMMENTS` mediumtext,
`ODBRAND` varchar(50) DEFAULT NULL,
`OSBRAND` varchar(50) DEFAULT NULL,
`ODDIAM` varchar(50) DEFAULT NULL,
`ODBC` varchar(50) DEFAULT NULL,
`OSDIAM` varchar(50) DEFAULT NULL,
`OSBC` varchar(50) DEFAULT NULL,
`COMMENTS` char(10) DEFAULT NULL,
`PRESCRIBED` bit(1) DEFAULT NULL,
`PRESCRIBEDBY` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dbREFRACTIONS`
--


-- --------------------------------------------------------

--
-- Table structure for table `dbSelectFindings`
--

CREATE TABLE IF NOT EXISTS `dbSelectFindings` (
`PEZONE` char(25) NOT NULL,
`LOCATION` char(25) NOT NULL,
`LOCATION_text` varchar(25) NOT NULL,
`id` bigint(20) NOT NULL,
`selection` varchar(255) NOT NULL,
`ZONE_ORDER` int(11) DEFAULT NULL,
`VALUE` varchar(10) DEFAULT '0',
`ordering` tinyint(4) DEFAULT NULL,
UNIQUE KEY `id` (`id`,`PEZONE`,`LOCATION`,`selection`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dbSelectFindings`
--

INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'quiet', 11, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'injection', 11, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'pinquecula', 11, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'papillae', 11, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'clear', 12, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'abrasion', 12, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'MDFP dystrophy', 12, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'metallic FB', 12, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'l', 'LENS', 3, 'PXE', 14, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'a/c', 'AC', 3, 'clear', 13, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'a/c', 'AC', 3, 'F/C', 13, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'l', 'LENS', 3, 'PSC', 14, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'a/c', 'AC', 3, 'narrow', 13, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'ptosis', 1, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'rhytids', 1, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'l', 'LENS', 3, 'NS', 14, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'scar', 1, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, 'dermatochalasis', 2, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, '2mm ptosis', 2, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, '3mm ptosis', 2, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, 'lesion', 2, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'lesion', 3, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'ectropion', 3, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'entropion', 3, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'trichiasis', 3, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MCT', 'MCT', 3, 'lesion', 4, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MCT', 'MCT', 3, 'NLDO, acute', 4, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MCT', 'MCT', 3, 'NLDO, chronic', 4, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'i', 'IRIS', 3, 'post synchia', 15, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'i', 'IRIS', 3, 'TI', 15, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'i', 'IRIS', 3, 'PXE', 15, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'i', 'IRIS', 3, 'PI', 15, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'follicles', 11, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'p', 'PERIPH', 3, 'NVE', 35, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'cup', 'CUP', 3, '0.1', 31, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'cup', 'CUP', 3, '0.3', 31, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'cup', 'CUP', 3, '0.5', 31, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'cup', 'CUP', 3, '0.8', 31, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'd', 'DISC', 3, 'notch', 32, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'd', 'DISC', 3, 'pallor', 32, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'd', 'DISC', 3, 'NVD', 32, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'd', 'DISC', 3, 'at risk', 32, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'm', 'MACULA', 3, 'hard drusen', 33, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'm', 'MACULA', 3, 'soft drusen', 33, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'm', 'MACULA', 3, 'PED', 33, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'm', 'MACULA', 3, 'CSR', 33, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'v', 'VESSELS', 3, '1:2', 34, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'v', 'VESSELS', 3, 'BDR', 34, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'v', 'VESSELS', 3, 'PDR', 34, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'v', 'VESSELS', 3, 'BRVO', 34, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'p', 'PERIPH', 3, 'PVD', 35, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'p', 'PERIPH', 3, 'VH', 35, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'p', 'PERIPH', 3, 'tear', 35, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'p', 'PERIPH', 3, 'schisis', 35, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('RETINA', 'cup', 'CUP', 3, '0.95', 31, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'mucopurulence', 11, NULL, 7);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'pterygium', 11, NULL, 8);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'edema', 12, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'striae', 12, NULL, 6);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'mutton fat KP', 12, NULL, 7);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'stellate KP', 12, NULL, 8);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'l', 'LENS', 3, 'PCIOL', 14, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'dendrite 	', 12, '0', 7);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'EXAM', 'EXAM', 3, 'EXAM', 57, 'QP', NULL);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'i', 'IRIS', 3, 'nevus', 15, NULL, 7);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'VA', 'Vision', 3, 'RS', 51, '1', 10);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'CR', 'Cycloplegic Refracti', 3, 'CR', 53, '0', 3);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'MR', 'Manifest Refraction', 3, 'MR', 54, '0', 2);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'Color', 'COLOR', 3, '11/11', 20, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'Color', 'COLOR', 3, '15/15', 20, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, 'orthophoria', 21, NULL, 1);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, 'flick X(T)', 21, NULL, 2);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '10 X(T)', 21, NULL, 3);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '15 X(T)', 21, NULL, 4);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '20 (XT)', 21, NULL, 5);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, 'Flick E(T)', 21, NULL, 6);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '5 E(T)', 21, NULL, 7);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '10 E(T)', 21, NULL, 8);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '15 E(T)', 21, NULL, 9);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '20 E(T)', 21, NULL, 10);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, 'flick RH(T)', 21, NULL, 11);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '2 RH(T)', 21, NULL, 12);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '4 RH(T)', 21, NULL, 13);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '6 RH(T)', 21, NULL, 14);
INSERT INTO `dbSelectFindings` VALUES('NEURO', 'ACT', 'ACT', 3, '8 RHT(T)', 21, NULL, 15);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'CTL', 'Contact Lens', 3, 'CTL', 55, '0', 4);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'ADDITIONAL', 'Additional Data Poin', 3, 'ADDITIONAL', 56, '0', 5);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'W', 'Current RX', 3, 'W', 52, '0', 2);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'seb ker', 1, '0', 5);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'actinic keratosis', 1, '0', 6);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'BCC', 1, '0', 7);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'BROW', 'BROW', 3, 'SCC', 1, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'moderate bleb', 10, '0', 9);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MRD', 'MRD', 3, '0', 1, '0', 10);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MRD', 'MRD', 3, '1', 1, '0', 11);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MRD', 'MRD', 3, '2', 1, '0', 12);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'MRD', 'MRD', 3, '3', 1, '0', 13);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LF', 'LF', 3, '17', 1, '0', 14);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LF', 'LF', 3, '15', 1, '0', 15);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LF', 'LF', 3, '13', 1, '0', 16);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'fat prolapse', 3, '0', 5);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'erythema', 3, '0', 6);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'flat bleb', 10, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'tenderness', 3, '0', 9);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'chalazion', 3, '0', 10);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'LL', 'LL', 3, 'stye', 3, '0', 11);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, 'chalazion', 2, '0', 5);
INSERT INTO `dbSelectFindings` VALUES('EXT', 'UL', 'UL', 3, 'stye', 2, '0', 6);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'pyogenic granuloma', 10, '0', 5);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'c', 'CONJ', 3, 'siedel negative', 10, '0', 10);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'tight wound', 11, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'k', 'CORNEA', 3, 'stromal scar', 12, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'a/c', 'AC', 3, 'hyphema', 13, '0', 7);
INSERT INTO `dbSelectFindings` VALUES('ANTSEG', 'a/c', 'AC', 3, 'hypopion', 13, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'CLINICAL', 'CLINICAL', 3, 'CLINICAL', 58, '0', 8);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'REFRACTION', 'REFRACTION', 3, '59', 8, '+', 9);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'CYLINDER', 'CYL', 3, 'CYL', 8, '+', 9);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'EXT_VIEW', 'Mid Face', 3, 'EXT_VIEW', 59, '1', NULL);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'ANTSEG_VIEW', 'ANTSEG View', 3, 'ANTSEG_VIEW', 60, '1', NULL);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'RETINA_VIEW', 'Retina View', 3, 'RETINA_VIEW', 60, '1', NULL);
INSERT INTO `dbSelectFindings` VALUES('PREFS', 'NEURO_VIEW', 'Neuro View', 3, 'NEURO_VIEW', 61, 'undefined', NULL);

-- --------------------------------------------------------
--
-- Table structure for table `form_eye_mag`
--

CREATE TABLE IF NOT EXISTS `form_eye_mag` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`date` datetime DEFAULT NULL,
`pid` bigint(20) DEFAULT NULL,
`user` varchar(255) DEFAULT NULL,
`groupname` varchar(255) DEFAULT NULL,
`authorized` tinyint(4) DEFAULT NULL,
`activity` tinyint(4) DEFAULT NULL,
`Narrative` mediumtext,
`VISITTYPE` varchar(50) DEFAULT NULL,

`CC` longtext,
`HPI` mediumtext,
`SNOMEDCC` longtext,
`QUALITY` mediumtext,
`TIMING` mediumtext,
`DURATION` mediumtext,
`CONTEXT` mediumtext,
`SEVERITY` mediumtext,
`MODIFY` mediumtext,
`ASSOCIATED` mediumtext,
`LOCATION` mediumtext,

`CC2` longtext,
`HPI2` mediumtext,
`SNOMEDCC2` longtext,
`QUALITY2` mediumtext,
`TIMING2` mediumtext,
`DURATION2` mediumtext,
`CONTEXT2` mediumtext,
`SEVERITY2` mediumtext,
`MODIFY2` mediumtext,
`ASSOCIATED2` mediumtext,
`LOCATION2` mediumtext,

`CC3` longtext,
`HPI3` mediumtext,
`SNOMEDCC3` longtext,
`QUALITY3` mediumtext,
`TIMING3` mediumtext,
`DURATION3` mediumtext,
`CONTEXT3` mediumtext,
`SEVERITY3` mediumtext,
`MODIFY3` mediumtext,
`ASSOCIATED3` mediumtext,
`LOCATION3` mediumtext,

`PMH` mediumtext,
`POH` mediumtext,
`PSURGHX` mediumtext,
`EYEMEDICATIONS` mediumtext,
`MEDICATIONS` mediumtext,
`ALLERGIES` mediumtext,
`SOCHX` mediumtext,
`FH` mediumtext,
`EYESURGHX` mediumtext,
`ROSGENERAL` longtext,
`ROSHEENT` longtext,
`ROSCV` longtext,
`ROSPULM` longtext,
`ROSGI` longtext,
`ROSGU` longtext,
`ROSDERM` longtext,
`ROSNEURO` longtext,
`ROSPSYCH` longtext,
`ROSMUSCULO` longtext,
`ROSIMMUNO` longtext,
`ROSENDOCRINE` longtext,

`alert` tinyint(1) DEFAULT '1',
`oriented` tinyint(1) DEFAULT '1',
`confused` tinyint(1) DEFAULT NULL,

`SCODVA` varchar(100) DEFAULT NULL,
`SCOSVA` varchar(100) DEFAULT NULL,
`PHODVA` varchar(100) DEFAULT NULL,
`PHOSVA` varchar(100) DEFAULT NULL,
`WODVA` varchar(100) DEFAULT NULL,
`WOSVA` varchar(100) DEFAULT NULL,
`CTLODVA` varchar(100) DEFAULT NULL,
`CTLOSVA` varchar(100) DEFAULT NULL,
`MRODVA` varchar(100) DEFAULT NULL,
`MROSVA` varchar(100) DEFAULT NULL,
`SCNEARODVA` varchar(100) DEFAULT NULL,
`SCNEAROSVA` varchar(100) DEFAULT NULL,
`WNEARODVA` varchar(10) DEFAULT NULL,
`WNEAROSVA` varchar(10) DEFAULT NULL,
`MRNEARODVA` varchar(100) DEFAULT NULL,
`MRNEAROSVA` varchar(100) DEFAULT NULL,
`GLAREODVA` varchar(100) DEFAULT NULL,
`GLAREOSVA` varchar(100) DEFAULT NULL,
`GLARECOMMENTS` varchar(100) DEFAULT NULL,
`ARODVA` char(10) DEFAULT NULL,
`AROSVA` char(10) DEFAULT NULL,
`CRODVA` varchar(100) DEFAULT NULL,
`CROSVA` varchar(50) DEFAULT NULL,
`CTLODVA1` varchar(50) DEFAULT NULL,
`CTLOSVA1` varchar(50) DEFAULT NULL,
`PAMODVA` varchar(50) DEFAULT NULL,
`PAMOSVA` varchar(50) DEFAULT NULL,
`WODVANEAR` varchar(50) DEFAULT NULL,
`OSVANEARCC` varchar(50) DEFAULT NULL,
`NVOCHECKED` varchar(50) DEFAULT NULL,
`ADDCHECKED` varchar(50) DEFAULT NULL,

`WODSPH` varchar(100) DEFAULT NULL,
`WODCYL` varchar(100) DEFAULT NULL,
`WODAXIS` varchar(100) DEFAULT NULL,
`WODADD1` varchar(100) DEFAULT NULL,
`WODADD2` varchar(100) DEFAULT NULL,
`WOSSPH` varchar(100) DEFAULT NULL,
`WOSCYL` varchar(100) DEFAULT NULL,
`WOSAXIS` varchar(100) DEFAULT NULL,
`WOSADD1` varchar(100) DEFAULT NULL,
`WOSADD2` varchar(100) DEFAULT NULL,
`WODPRISM` varchar(50) DEFAULT NULL,
`WODBASE` varchar(50) DEFAULT NULL,
`WOSPRISM` varchar(50) DEFAULT NULL,
`WOSBASE` varchar(50) DEFAULT NULL,
`WODCYLNEAR` varchar(50) DEFAULT NULL,
`WODAXISNEAR` varchar(50) DEFAULT NULL,
`WODPRISMNEAR` varchar(50) DEFAULT NULL,
`WODBASENEAR` varchar(50) DEFAULT NULL,
`WOSCYLNEAR` varchar(50) DEFAULT NULL,
`WOSAXISNEAR` varchar(50) DEFAULT NULL,
`WOSPRISMNEAR` varchar(50) DEFAULT NULL,
`WOSBASENEAR` varchar(50) DEFAULT NULL,
`WCOMMENTS` varchar(100) DEFAULT NULL,

`MRODSPH` varchar(100) DEFAULT NULL,
`MRODCYL` varchar(100) DEFAULT NULL,
`MRODAXIS` varchar(100) DEFAULT NULL,
`MRODPRISM` varchar(50) DEFAULT NULL,
`MRODBASE` varchar(50) DEFAULT NULL,
`MRODADD` varchar(100) DEFAULT NULL,
`MROSSPH` varchar(100) DEFAULT NULL,
`MROSCYL` varchar(100) DEFAULT NULL,
`MROSAXIS` varchar(100) DEFAULT NULL,
`MROSPRISM` varchar(50) DEFAULT NULL,
`MROSBASE` varchar(50) DEFAULT NULL,
`MROSADD` varchar(100) DEFAULT NULL,
`MRODNEARSPHERE` varchar(50) DEFAULT NULL,
`MRODNEARCYL` varchar(100) DEFAULT NULL,
`MRODNEARAXIS` varchar(100) DEFAULT NULL,
`MRODPRISMNEAR` varchar(50) DEFAULT NULL,
`MRODBASENEAR` varchar(50) DEFAULT NULL,
`MROSNEARSHPERE` varchar(50) DEFAULT NULL,
`MROSNEARCYL` varchar(100) DEFAULT NULL,
`MROSNEARAXIS` varchar(100) DEFAULT NULL,
`MROSPRISMNEAR` varchar(50) DEFAULT NULL,
`MROSBASENEAR` varchar(50) DEFAULT NULL,

`CRODSPH` varchar(100) DEFAULT NULL,
`CRODCYL` varchar(100) DEFAULT NULL,
`CRODAXIS` varchar(100) DEFAULT NULL,
`CROSSPH` varchar(100) DEFAULT NULL,
`CROSCYL` varchar(100) DEFAULT NULL,
`CROSAXIS` varchar(100) DEFAULT NULL,
`DIL_RISKS` char(1) DEFAULT NULL,

`ARODSPH` char(10) DEFAULT NULL,
`ARODCYL` char(10) DEFAULT NULL,
`ARODAXIS` char(10) DEFAULT NULL,
`AROSSPH` char(10) DEFAULT NULL,
`AROSCYL` char(10) DEFAULT NULL,
`AROSAXIS` char(10) DEFAULT NULL,
`ARODADD` varchar(10) DEFAULT NULL,
`AROSADD` varchar(10) DEFAULT NULL,
`ARNEARODVA` varchar(10) DEFAULT NULL,
`ARNEAROSVA` varchar(10) DEFAULT NULL,
`ARODPRISM` varchar(20) DEFAULT NULL,
`AROSPRISM` varchar(20) DEFAULT NULL,

`CTLODSPH` varchar(50) DEFAULT NULL,
`CTLODCYL` varchar(50) DEFAULT NULL,
`CTLODAXIS` varchar(50) DEFAULT NULL,
`CTLODBC` varchar(50) DEFAULT NULL,
`CTLODDIAM` varchar(50) DEFAULT NULL,
`CTLOSSPH` varchar(50) DEFAULT NULL,
`CTLOSCYL` varchar(50) DEFAULT NULL,
`CTLOSAXIS` varchar(50) DEFAULT NULL,
`CTLOSBC` varchar(50) DEFAULT NULL,
`CTLOSDIAM` varchar(50) DEFAULT NULL,
`CTL_COMMENTS` mediumtext,
`CTLMANUFACTUREROD` varchar(50) DEFAULT NULL,
`CTLSUPPLIEROD` varchar(50) DEFAULT NULL,
`CTLBRANDOD` varchar(50) DEFAULT NULL,
`CTLMANUFACTUREROS` varchar(50) DEFAULT NULL,
`CTLSUPPLIEROS` varchar(50) DEFAULT NULL,
`CTLBRANDOS` varchar(50) DEFAULT NULL,
`CTLODADD` varchar(50) DEFAULT NULL,
`CTLOSADD` varchar(50) DEFAULT NULL,

`ODIOPAP` varchar(50) DEFAULT NULL,
`OSIOPAP` varchar(50) DEFAULT NULL,
`ODIOPTPN` varchar(10) DEFAULT NULL,
`OSIOPTPN` varchar(10) DEFAULT NULL,
`ODIOPFTN` varchar(10) DEFAULT NULL,
`OSIOPFTN` varchar(10) DEFAULT NULL,
`IOPTIME` time DEFAULT NULL,

`AMSLEROD` smallint(1) DEFAULT NULL,
`AMSLEROS` smallint(1) DEFAULT NULL,

`ODK1` varchar(50) DEFAULT NULL,
`ODK2` varchar(50) DEFAULT NULL,
`ODK2AXIS` varchar(50) DEFAULT NULL,
`OSK1` varchar(50) DEFAULT NULL,
`OSK2` varchar(50) DEFAULT NULL,
`OSK2AXIS` varchar(50) DEFAULT NULL,

`ODAXIALLENGTH` varchar(50) DEFAULT NULL,
`OSAXIALLENGTH` varchar(50) DEFAULT NULL,
`ODACD` varchar(50) DEFAULT NULL,
`OSACD` varchar(50) DEFAULT NULL,
`ODW2W` varchar(10) DEFAULT NULL,
`OSW2W` varchar(10) DEFAULT NULL,
`ODLT` varchar(20) DEFAULT NULL,
`OSLT` varchar(20) DEFAULT NULL,
`ODPDMeasured` varchar(25) DEFAULT NULL,
`OSPDMeasured` varchar(25) DEFAULT NULL,

`ACT` tinyint(1) DEFAULT NULL,
`ACTPRIMCCDIST` varchar(50) DEFAULT NULL,
`ACT1CCDIST` varchar(50) DEFAULT NULL,
`ACT2CCDIST` varchar(50) DEFAULT NULL,
`ACT3CCDIST` varchar(50) DEFAULT NULL,
`ACT4CCDIST` varchar(50) DEFAULT NULL,
`ACT6CCDIST` varchar(50) DEFAULT NULL,
`ACT7CCDIST` varchar(50) DEFAULT NULL,
`ACT8CCDIST` varchar(50) DEFAULT NULL,
`ACT9CCDIST` varchar(50) DEFAULT NULL,
`ACTRTILTCCDIST` varchar(50) DEFAULT NULL,
`ACTLTILTCCDIST` varchar(50) DEFAULT NULL,
`ACT1SCDIST` varchar(50) DEFAULT NULL,
`ACT2SCDIST` varchar(50) DEFAULT NULL,
`ACT3SCDIST` varchar(50) DEFAULT NULL,
`ACT4SCDIST` varchar(50) DEFAULT NULL,
`ACTPRIMSCDIST` varchar(50) DEFAULT NULL,
`ACT6SCDIST` varchar(50) DEFAULT NULL,
`ACT7SCDIST` varchar(50) DEFAULT NULL,
`ACT8SCDIST` varchar(50) DEFAULT NULL,
`ACT9SCDIST` varchar(50) DEFAULT NULL,
`ACTRTILTSCDIST` varchar(50) DEFAULT NULL,
`ACTLTILTSCDIST` varchar(50) DEFAULT NULL,
`ACT1SCNEAR` varchar(50) DEFAULT NULL,
`ACT2SCNEAR` varchar(50) DEFAULT NULL,
`ACT3SCNEAR` varchar(50) DEFAULT NULL,
`ACT4SCNEAR` varchar(50) DEFAULT NULL,
`ACTPRIMCCNEAR` varchar(50) DEFAULT NULL,
`ACT6CCNEAR` varchar(50) DEFAULT NULL,
`ACT7CCNEAR` varchar(50) DEFAULT NULL,
`ACT8CCNEAR` varchar(50) DEFAULT NULL,
`ACT9CCNEAR` varchar(50) DEFAULT NULL,
`ACTRTILTCCNEAR` varchar(50) DEFAULT NULL,
`ACTLTILTCCNEAR` varchar(50) DEFAULT NULL,
`ACTPRIMSCNEAR` varchar(50) DEFAULT NULL,
`ACT6SCNEAR` varchar(50) DEFAULT NULL,
`ACT7SCNEAR` varchar(50) DEFAULT NULL,
`ACT8SCNEAR` varchar(50) DEFAULT NULL,
`ACT9SCNEAR` varchar(50) DEFAULT NULL,
`ACTRTILTSCNEAR` varchar(50) DEFAULT NULL,
`ACTLTILTSCNEAR` varchar(50) DEFAULT NULL,
`ACT1CCNEAR` varchar(50) DEFAULT NULL,
`ACT2CCNEAR` varchar(50) DEFAULT NULL,
`ACT3CCNEAR` varchar(50) DEFAULT NULL,
`ACT4CCNEAR` varchar(50) DEFAULT NULL,
`ODVF1` tinyint(1) DEFAULT NULL,
`ODVF2` tinyint(1) DEFAULT NULL,
`ODVF3` tinyint(1) NOT NULL,
`ODVF4` tinyint(1) NOT NULL,
`OSVF1` tinyint(1) NOT NULL,
`OSVF2` tinyint(1) NOT NULL,
`OSVF3` tinyint(1) NOT NULL,
`OSVF4` tinyint(1) NOT NULL,
`MOTILITY_RS` int(1) DEFAULT NULL,
`MOTILITY_RI` int(1) DEFAULT NULL,
`MOTILITY_RR` int(1) DEFAULT NULL,
`MOTILITY_RL` int(1) DEFAULT NULL,
`MOTILITY_LS` int(1) DEFAULT NULL,
`MOTILITY_LI` int(1) DEFAULT NULL,
`MOTILITY_LR` int(1) DEFAULT NULL,
`MOTILITY_LL` int(1) DEFAULT NULL,
`STEREOPSIS` varchar(25) DEFAULT NULL,
`ODNPA` varchar(50) DEFAULT NULL,
`OSNPA` varchar(50) DEFAULT NULL,
`VERTFUSAMPS` varchar(50) DEFAULT NULL,
`DIVERGENCEAMPS` varchar(50) DEFAULT NULL,
`NPC` varchar(10) DEFAULT NULL,
`CASCDIST` varchar(10) DEFAULT NULL,
`CASCNEAR` varchar(10) DEFAULT NULL,
`CACCDIST` varchar(10) DEFAULT NULL,
`CACCNEAR` varchar(10) DEFAULT NULL,
`ODCOLOR` char(5) DEFAULT NULL,
`OSCOLOR` char(5) DEFAULT NULL,
`ODCOINS` char(5) DEFAULT NULL,
`OSCOINS` char(5) DEFAULT NULL,
`ODREDDESAT` varchar(10) DEFAULT NULL,
`OSREDDESAT` varchar(10) DEFAULT NULL,
`OD_NEURO_DRAWING` mediumblob,
`OS_NEURO_DRAWING` mediumblob,
`NEURO_COMMENTS` mediumtext,

`RUL` mediumtext,
`LUL` mediumtext,
`RLL` mediumtext,
`LLL` mediumtext,
`RBROW` text,
`LBROW` text,
`RMCT` mediumtext,
`LMCT` mediumtext,
`RADNEXA` varchar(255) DEFAULT NULL,
`LADNEXA` varchar(255) DEFAULT NULL,
`RMRD` varchar(25) DEFAULT NULL,
`LMRD` varchar(25) DEFAULT NULL,
`RLF` varchar(50) DEFAULT NULL,
`LLF` varchar(50) DEFAULT NULL,
`RVFISSURE` varchar(10) DEFAULT NULL,
`LVFISSURE` varchar(10) DEFAULT NULL,
`ODHERTEL` varchar(10) DEFAULT NULL,
`OSHERTEL` varchar(10) DEFAULT NULL,
`HERTELBASE` varchar(50) DEFAULT NULL,
`OD_EXT_DRAWING` mediumblob,
`OS_EXT_DRAWING` mediumblob,`EXT_COMMENTS` mediumtext,

`OSCONJ` mediumtext,
`ODCONJ` mediumtext,
`ODCORNEA` mediumtext,
`OSCORNEA` mediumtext,
`ODAC` mediumtext,
`OSAC` mediumtext,
`ODLENS` mediumtext,
`OSLENS` mediumtext,
`ODIRIS` mediumtext,
`OSIRIS` mediumtext,
`ODKTHICKNESS` varchar(50) DEFAULT NULL,
`OSKTHICKNESS` varchar(50) DEFAULT NULL,
`ODGONIO` varchar(50) DEFAULT NULL,
`OSGONIO` varchar(50) DEFAULT NULL,
`OD_ANTSEG_DRAWING` mediumblob,
`OS_ANTSEG_DRAWING` mediumblob,
`ANTSEG_COMMENTS` mediumtext,

`ODPUPILSIZE1` varchar(50) DEFAULT NULL,
`ODPUPILSIZE2` varchar(50) DEFAULT NULL,
`ODPUPILREACTIVITY` char(10) DEFAULT NULL,
`ODAPD` varchar(50) DEFAULT NULL,
`OSPUPILSIZE1` varchar(50) DEFAULT NULL,
`OSPUPILSIZE2` varchar(50) DEFAULT NULL,
`OSPUPILREACTIVITY` char(10) DEFAULT NULL,
`OSAPD` varchar(50) DEFAULT NULL,
`DIMODPUPILSIZE1` varchar(50) DEFAULT NULL,
`DIMODPUPILSIZE2` varchar(50) DEFAULT NULL,
`DIMODPUPILREACTIVITY` varchar(50) DEFAULT NULL,
`DIMOSPUPILSIZE1` varchar(50) DEFAULT NULL,
`DIMOSPUPILSIZE2` varchar(50) DEFAULT NULL,
`DIMOSPUPILREACTIVITY` varchar(50) DEFAULT NULL,
`PUPIL_COMMENTS` varchar(255) DEFAULT NULL,

`ODVFCONFRONTATION1` int(11) DEFAULT NULL,
`ODVFCONFRONTATION2` int(11) DEFAULT NULL,
`ODVFCONFRONTATION3` int(11) DEFAULT NULL,
`ODVFCONFRONTATION4` int(11) DEFAULT NULL,
`ODVFCONFRONTATION5` int(11) DEFAULT NULL,
`OSVFCONFRONTATION1` int(11) DEFAULT NULL,
`OSVFCONFRONTATION2` int(11) DEFAULT NULL,
`OSVFCONFRONTATION3` int(11) DEFAULT NULL,
`OSVFCONFRONTATION4` int(11) DEFAULT NULL,
`OSVFCONFRONTATION5` int(11) DEFAULT NULL,

`ODDISC` varchar(100) DEFAULT NULL,
`OSDISC` varchar(100) DEFAULT NULL,
`ODCUP` varchar(100) DEFAULT NULL,
`OSCUP` varchar(100) DEFAULT NULL,
`ODMACULA` varchar(100) DEFAULT NULL,
`OSMACULA` varchar(100) DEFAULT NULL,
`ODVESSELS` varchar(100) DEFAULT NULL,
`OSVESSELS` varchar(100) DEFAULT NULL,
`ODPERIPH` varchar(100) DEFAULT NULL,
`OSPERIPH` varchar(100) DEFAULT NULL,
`OD_RETINA_DRAWING` mediumblob,
`OS_RETINA_DRAWING` mediumblob,
`ODCMT` varchar(255) DEFAULT NULL,
`OSCMT` varchar(255) DEFAULT NULL,
`RETINA_COMMENTS` mediumtext,

`IMP` mediumtext NOT NULL,
`PLAN` mediumtext NOT NULL,

`LetterID` varchar(30) DEFAULT NULL,
`LetterTo` char(50) DEFAULT NULL,
`LetterCc1` char(50) DEFAULT NULL,
`LetterCc2` char(50) DEFAULT NULL,
`LetterCc3` char(50) DEFAULT NULL,
`LetterCc4` char(50) DEFAULT NULL,
`LetterCc5` char(50) DEFAULT NULL,
`LocationOFFICE` varchar(50) DEFAULT NULL,
`Technician` varchar(50) DEFAULT NULL,
`Doctor` varchar(50) DEFAULT NULL,
`Resource` varchar(50) DEFAULT NULL,
`LOCKED` bit(1) DEFAULT NULL,
`LockedDATE` datetime DEFAULT NULL,
`LOCKEDBY` varchar(50) DEFAULT NULL,
`FINISHED` char(25) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;




