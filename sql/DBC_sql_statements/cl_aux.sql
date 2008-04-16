-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:50 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.9
-- 
-- Database: `openemr`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cl_aux`
-- 

CREATE TABLE `cl_aux` (
  `aux_id` varchar(50) NOT NULL default '',
  `aux_varc` varchar(100) NOT NULL default '',
  `aux_varn` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='auxiliary table for storing different variables';

-- 
-- Dumping data for table `cl_aux`
-- 

INSERT INTO `cl_aux` (`aux_id`, `aux_varc`, `aux_varn`) VALUES 
('dn_id1250', '20080403', 1),
('dn_id1007', '', 1),
('dn_id1008', '0', 1),
('vk_0116_invoice', '20080401', 1),
('vk_0426_invoice', '', 1);
