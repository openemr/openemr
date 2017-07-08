-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 06, 2017 at 03:10 PM
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
-- Table structure for table `medex_icons`
--

CREATE TABLE IF NOT EXISTS `medex_icons` (
  `i_UID` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` varchar(50) NOT NULL,
  `msg_status` varchar(10) NOT NULL,
  `i_description` varchar(255) NOT NULL,
  `i_html` text NOT NULL,
  `i_blob` longtext NOT NULL,
  PRIMARY KEY (`i_UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

--
-- Dumping data for table `medex_icons`
--
-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 03, 2017 at 08:27 AM
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
-- Table structure for table `medex_icons`
--

CREATE TABLE IF NOT EXISTS `medex_icons` (
  `i_UID` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` varchar(50) NOT NULL,
  `msg_status` varchar(10) NOT NULL,
  `i_description` varchar(255) NOT NULL,
  `i_html` text NOT NULL,
  `i_blob` longtext NOT NULL,
  PRIMARY KEY (`i_UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

--
-- Dumping data for table `medex_icons`
--

INSERT INTO `medex_icons` (`i_UID`, `msg_type`, `msg_status`, `i_description`, `i_html`, `i_blob`) VALUES
(1, 'SMS', 'ALLOWED', 'Message type allowed', '<i title="SMS is possible." class="fa fa-commenting-o fa-fw"></i>', ''),
(2, 'SMS', 'NotAllowed', 'Message type not allowed', '<span class="fa-stack" title="SMS not possible"><i class="fa fa-commenting-o fa-stack-1x fa-fw"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(3, 'SMS', 'SCHEDULED', 'SMS: scheduled', '<span class="btn scheduled" title="SMS scheduled"><i class="fa fa-commenting-o fa-fw"></i></span>', ''),
(4, 'SMS', 'SENT', 'SMS Sent, not read - in process', '<span class="btn" title="SMS Sent - in process" style="padding:5px;background-color:yellow;color:black;"><i class="fa fa-commenting-o fa-fw"></i></span>', ''),
(5, 'SMS', 'READ', 'SMS delivered/read', '<span class="btn" title="SMS Sent and Read - waiting for response" aria-label="SMS Delivered" style="padding:5px;background-color:#146abd;"><i class="fa fa-commenting-o fa-flip-horizontal fa-fw" aria-hidden="true"></i></span>', ''),
(6, 'SMS', 'FAILURE', 'SMS delivery failed', '<span class="btn" title="SMS Failed to be delivered" style="padding:5px;background-color:red;"><i class="fa fa-commenting-o fa-inverse fa-fw"></i></span>', ''),
(7, 'SMS', 'CONFIRMED', 'Confirmed', '<span class="btn" title="Confirmed by SMS" style="padding:5px;background-color:green;"><i class="fa fa-commenting-o fa-inverse fa-fw"></i></span>', ''),
(8, 'SMS', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(9, 'SMS', 'EXTRA', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-stack-1x fa-fw fa-inverse"></i></span>', ''),
(10, 'SMS', 'STOP', 'Optout of SMS please', '<span class="btn btn-danger" title="OptOut of SMS Messaging. Demographics updated." aria-label=''Optout SMS''><i class="fa fa-commenting" aria-hidden="true"> STOP</i></span>', ''),
(11, 'AVM', 'ALLOWED', 'Message type allowed', '<span title="Automated Voice Messages are possible" class="fa fa-phone fa-fw"></span>', ''),
(12, 'AVM', 'NotAllowed', 'Message type not allowed', '<span class="fa-stack" title="Automated Voice Messages are not allowed"><i class="fa fa-phone fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(13, 'AVM', 'SCHEDULED', 'AVM: scheduled', '<span class="btn scheduled" title="AVM scheduled"><i class="fa fa-phone fa-fw"></i></span>', ''),
(14, 'AVM', 'SENT', 'AVM in process', '<span class="btn" title="AVM in process, no response" style="padding:5px;background-color:yellow;color:black;"><i class="fa fa-volume-control-phone fa-fw"></i></span>', ''),
(15, 'AVM', 'FAILURE', 'AVM: Message failed', '<span class="btn" title="AVM: Failed.  Check patient''s phone numbers." style="padding:5px;background-color:red;"><i class="fa fa-phone fa-inverse fa-fw"></i></span>', ''),
(16, 'AVM', 'CONFIRMED', 'Confirmed', '<span class="btn" title="Confirmed by AVM" style="padding:5px;background-color:green;"><i class="fa fa-phone fa-inverse fa-fw"></i></span>', ''),
(17, 'AVM', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(18, 'AVM', 'Other', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-fw fa-stack-1x fa-inverse"></i></span>', ''),
(19, 'AVM', 'STOP', 'Optout of AVM please', '<span class="btn btn-danger" title="OptOut of Voice Messaging. Demographics updated." aria-label=‚ÄúOptout AVM‚Äù><i class="fa fa-phone" aria-hidden="true"> STOP</i></span>', ''),
(20, 'EMAIL', 'ALLOWED', 'EMAIL:  allowed', '<span title="EMAIL is possible" class="fa fa-envelope-o fa-fw"></span>', ''),
(21, 'EMAIL', 'NotAllowed', 'EMAIL: not allowed', '<span class="fa-stack" title="EMAIL is not possible"><i class="fa fa-envelope-o fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(22, 'EMAIL', 'SCHEDULED', 'EMAIL: scheduled', '<span class=''btn scheduled'' title=''EMAIL scheduled''><i class="fa fa-envelope-o fa-fw"></i></span>', ''),
(23, 'EMAIL', 'SENT', 'EMAIL: sent', '<span class="btn" style="padding:5px;background-color:yellow;color:black;" title="EMAIL Message sent, not opened"><i class="fa fa-envelope-o fa-fw"></i></span>', ''),
(24, 'EMAIL', 'READ', 'EMAIL was opened/read', '<a class="btn" style="padding:5px;background-color:#146abd;" title="E-Mail was read/opened by patient" aria-label="Confirmed via email"><i class="fa fa-envelope-o fa-inverse fa-fw" aria-hidden="true"></i></a>', ''),
(25, 'EMAIL', 'FAILURE', 'EMAIL Message failed', '<span class="btn" title="EMAIL: Failed.  Check patient\'s email address." style="padding:5px;background-color:red;"><i class="fa fa-envelope-o fa-inverse fa-fw"></i></span>', ''),
(26, 'EMAIL', 'CONFIRMED', 'EMAIL Confirmed', '<a class="btn btn-success" style="padding:5px;background-color: green;" title="Confirmed by E-Mail" aria-label="Confirmed via email"><i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i></a>', ''),
(27, 'EMAIL', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(28, 'EMAIL', 'Other', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-fw fa-stack-1x fa-inverse fa-fw"></i></span>', ''),
(29, 'EMAIL', 'STOP', 'Optout of EMAIL please', '<span class="btn btn-danger" title="OptOut of EMAIL Messaging. Demographics updated." aria-label=‚ÄúOptout EMAIL‚Äù><i class="fa fa-envelope-o" aria-hidden="true"> STOP</i></span>', ''),
(30, 'POSTCARD', 'SENT', 'POSTCARD sent', '<span class="btn" title="Postcard Sent - in process" style="padding:5px;background-color:yellow;color:black"><i class="fa fa-image fa-fw"></i></span>', ''),
(31, 'POSTCARD', 'READ', 'e-POSTCARD delivered', '<a class="btn" style="padding:5px;background-color:#146abd;" title="e-Postcard was delivered" aria-label="Postcard Delivered"><i class="fa fa-image fa-fw" aria-hidden="true"></i></a>', ''),
(32, 'POSTCARD', 'FAILURE', 'e-POSTCARD failed', '<span class="fa-stack fa-lg" title="Delivery Failure - check Address for this patient"><i class="fa fa-image fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(33, 'POSTCARD', 'SCHEDULED', 'Postcard Campaign Event is scheduled.', '<span class="btn scheduled" title="Postcard Campaign Event is scheduled."><i class="fa fa-image fa-fw"></i></span>', ''),
(36, 'AVM', 'READ', 'AVM delivered', '<span class="btn" title="AVM completed - waiting for manual response" aria-label="AVM Delivered" style="padding:5px;background-color:#146abd;"><i class="fa fa-phone fa-fw" aria-hidden="true"></i></span>', ''),
(37, 'NOTES', 'CALLED', 'Callback Completed', '<span class="btn btn-success" style="padding:5px;background-color:#146abd;" title="Patient requests Office Call: COMPLETED">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', '');

-- --------------------------------------------------------

--
-- Table structure for table `medex_outgoing`
--

CREATE TABLE IF NOT EXISTS `medex_outgoing` (
  `msg_uid` int(11) NOT NULL AUTO_INCREMENT,
  `msg_pc_eid` varchar(11) NOT NULL,
  `campaign_uid` int(11) NOT NULL DEFAULT '0',
  `msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg_type` varchar(50) NOT NULL,
  `msg_reply` varchar(50) DEFAULT NULL,
  `msg_extra_text` text,
  PRIMARY KEY (`msg_uid`),
  UNIQUE KEY `msg_eid` (`msg_uid`,`msg_pc_eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `medex_outgoing`
--


-- --------------------------------------------------------

--
-- Table structure for table `medex_prefs`
--

CREATE TABLE IF NOT EXISTS `medex_prefs` (
  `MedEx_id` int(11) DEFAULT '0',
  `ME_username` varchar(100) DEFAULT NULL,
  `ME_api_key` text,
  `ME_facilities` varchar(50) DEFAULT NULL,
  `ME_providers` varchar(100) DEFAULT NULL,
  `ME_hipaa_default_override` varchar(3) DEFAULT NULL,
  `PHONE_country_code` int(4) NOT NULL DEFAULT '1',
  `MSGS_default_yes` varchar(3) DEFAULT NULL,
  `POSTCARDS_local` varchar(3) DEFAULT NULL,
  `POSTCARDS_remote` varchar(3) DEFAULT NULL,
  `LABELS_local` varchar(3) DEFAULT NULL,
  `LABELS_choice` varchar(50) DEFAULT NULL,
  `combine_time` tinyint(4) DEFAULT NULL,
  `notify` varchar(100) DEFAULT NULL,
  `MedEx_lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `ME_username` (`ME_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `medex_recalls`
--

CREATE TABLE IF NOT EXISTS `medex_recalls` (
  `r_ID` int(11) NOT NULL AUTO_INCREMENT,
  `r_PRACTID` int(11) NOT NULL,
  `r_pid` int(11) NOT NULL COMMENT 'PatientID from pat_data',
  `r_eventDate` date NOT NULL COMMENT 'Date of Appt or Recall',
  `r_facility` int(11) NOT NULL,
  `r_provider` int(11) NOT NULL,
  `r_reason` varchar(255) DEFAULT NULL,
  `r_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`r_ID`),
  UNIQUE KEY `r_PRACTID` (`r_PRACTID`,`r_pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=168 ;

--
-- Dumping data for table `background_services`
--

INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('MedEx', 'MedEx Messaging Service', 29, 0, '2017-05-09 17:39:10', 60, 'start_MedEx', '/library/MedEx/medex_background.php', 100);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('apptstat', 'AVM', 'AVM Confirmed', 110, 0, 0, '', 'F0FFE8|', '', 0, 0, 1, ''),
('apptstat', 'CALL', 'Callback requested', 130, 0, 0, '', 'FFDBE2|5', '', 0, 0, 1, ''),
('apptstat', 'SMS', 'SMS Confirmed', 90, 0, 0, '', 'F0FFE8|', '', 0, 0, 1, ''),
('apptstat', 'EMAIL', 'EMAIL Confimed', 20, 0, 0, '', 'FFEBE3|', '', 0, 0, 1, '');

