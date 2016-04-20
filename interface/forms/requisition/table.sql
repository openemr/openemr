CREATE TABLE IF NOT EXISTS `requisition` (
  `req_id` varchar(90) NOT NULL,
  `pid` int(5) NOT NULL,
  `field_id` varchar(35) NOT NULL,
  `field_data` varchar(190) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;