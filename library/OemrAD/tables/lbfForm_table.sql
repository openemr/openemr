
#IfMissingColumn layout_group_properties grp_activate_copy
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_activate_copy` tinyint(1) default NULL AFTER `grp_diags`;
#EndIf

#IfMissingColumn layout_group_properties grp_rto_action
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_rto_action` varchar(255) default NULL AFTER `grp_activate_copy`;
#EndIf