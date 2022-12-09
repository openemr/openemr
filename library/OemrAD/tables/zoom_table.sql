#IfNotTable zoom_appointment_events
CREATE TABLE IF NOT EXISTS `zoom_appointment_events` (
  `id` bigint(20) NOT NULL auto_increment,
  `pc_eid` bigint(20) NOT NULL,
  `m_id` varchar(255) NOT NULL,
  `host_email` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `duration` datetime NOT NULL,
  `timezone` varchar(255) NOT NULL,
  `join_url` longtext,
  `password` longtext,
  `responce_data` longtext,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY (`pc_eid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
#EndIf

#IfNotTable zoom_telehealth_appointments
CREATE TABLE IF NOT EXISTS `zoom_telehealth_appointments` (
  `id` bigint(20) NOT NULL auto_increment,
  `pc_eid` bigint(20) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `duration` datetime NOT NULL,
  `timezone` varchar(255) NOT NULL,
  `provider_join_url` longtext,
  `patient_join_url` longtext,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY (`pc_eid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
#EndIf

#IfMissingColumn zoom_appointment_events start_url
ALTER TABLE `zoom_appointment_events` ADD COLUMN `start_url` longtext AFTER `timezone`;
#EndIf

#IfNotColumnType zoom_appointment_events duration varchar
ALTER TABLE `zoom_appointment_events` MODIFY `duration` varchar(255);
#EndIf

#IfMissingColumn zoom_appointment_events pc_aid
ALTER TABLE `zoom_appointment_events` ADD COLUMN `pc_aid` bigint(20) AFTER `pc_eid`;
#EndIf