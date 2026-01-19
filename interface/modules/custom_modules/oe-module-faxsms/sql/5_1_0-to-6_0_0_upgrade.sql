#IfMissingColumn oe_faxsms_queue status
ALTER TABLE `oe_faxsms_queue` ADD `status` varchar(50) DEFAULT NULL COMMENT 'Fax status (queued, sent, delivered, received, failed, etc)';
#Endif

#IfMissingColumn oe_faxsms_queue direction
ALTER TABLE `oe_faxsms_queue` ADD `direction` varchar(20) DEFAULT 'inbound' COMMENT 'inbound or outbound';
#Endif

#IfMissingColumn oe_faxsms_queue site_id
ALTER TABLE `oe_faxsms_queue` ADD `site_id` varchar(63) DEFAULT 'default' COMMENT 'Site identifier for multi-site support';
ALTER TABLE `oe_faxsms_queue` ADD KEY `site_id` (`site_id`);
#Endif

#IfMissingColumn oe_faxsms_queue patient_id
ALTER TABLE `oe_faxsms_queue` ADD `patient_id` int(11) DEFAULT NULL COMMENT 'Patient ID if assigned';
ALTER TABLE `oe_faxsms_queue` ADD KEY `patient_id` (`patient_id`);
#Endif

#IfMissingColumn oe_faxsms_queue document_id
ALTER TABLE `oe_faxsms_queue` ADD `document_id` int(11) DEFAULT NULL COMMENT 'OpenEMR document ID if stored';
#Endif

#IfMissingColumn oe_faxsms_queue media_path
ALTER TABLE `oe_faxsms_queue` ADD `media_path` longtext COMMENT 'Path to stored fax media file';
#Endif

#IfMissingColumn oe_faxsms_queue job_id
ALTER TABLE `oe_faxsms_queue` ADD KEY `job_id` (`job_id`(255));
#Endif
