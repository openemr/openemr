CREATE TABLE `vh_predefined_lbf_selector_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `form_name` varchar(255) DEFAULT null,
  `group_id` varchar(255) DEFAULT null,
  `pid` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `is_global` tinyint(4) DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `vh_predefined_lbf_selector_data` (
  `form_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` varchar(31) NOT NULL,
  `field_value` longtext DEFAULT NULL,
  PRIMARY KEY (`form_id`,`field_id`)
);

#IfMissingColumn layout_group_properties grp_activate_copy
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_active_copy` tinyint(1) default NULL AFTER `grp_diags`;
#EndIf