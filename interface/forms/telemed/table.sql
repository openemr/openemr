CREATE TABLE IF NOT EXISTS `form_telemed` (
  `id` bigint(20) NOT NULL,
  `date` date DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `tm_duration` time DEFAULT NULL,
  `tm_subj` text,
  `tm_obj` text,
  `tm_imp` text,
  `tm_plan` text,
  `provider_id` int(11) NOT NULL
) ENGINE=InnoDB;
