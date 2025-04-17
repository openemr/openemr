--
-- Table structure for table `requisition`
--

CREATE TABLE `requisition` (
  `id` bigint(19) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `req_id` varchar(90) NOT NULL,
  `pid` int(11) NOT NULL,
  `lab_id` bigint(35) NOT NULL
) ENGINE=InnoDB;

