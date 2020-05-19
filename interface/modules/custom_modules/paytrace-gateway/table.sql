SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `cc_vault`
(
      `id` int(5) NOT NULL AUTO_INCREMENT,
      `customerid` varchar(30) NOT NULL,
      `pid` int(6) NOT NULL,
      `date` date NOT NULL,
      `fname` varchar(15) DEFAULT NULL,
      `lname` varchar(15) DEFAULT NULL,
      `address` varchar(25) DEFAULT NULL,
      `address2` varchar(40) DEFAULT NULL,
      `city` varchar(25) DEFAULT NULL,
      `state` varchar(2) DEFAULT NULL,
      `zip` int(9) DEFAULT NULL,
      `phone` varchar(15) DEFAULT NULL,
      `phone2` varchar(15) DEFAULT NULL,
      `email` varchar(40) DEFAULT NULL,
      `relationship` varchar(40) DEFAULT NULL,
      `receipt` text,
      `last4` varchar(17) NOT NULL,
      `ex_mo` int(2) NOT NULL,
      `ex_yr` int(2) NOT NULL,
      UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
