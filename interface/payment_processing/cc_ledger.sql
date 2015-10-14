-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2015 at 01:09 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `openemriudemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_ledger`
--

CREATE TABLE IF NOT EXISTS `cc_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_id` varchar(40) NOT NULL,
  `amount` varchar(6) NOT NULL,
  `pid` int(8) NOT NULL,
  `desk_clerk` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `processed` int(2) NOT NULL,
  `processor` varchar(5) NOT NULL,
  `process_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
