#IfNotTable form_templates_template
CREATE TABLE IF NOT EXISTS `form_templates_template` (
    `template_id` bigint(21) UNSIGNED NOT NULL AUTO_INCREMENT,
    `form_id` bigint(20) NOT NULL COMMENT 'Form ID FK',
    `acl` varchar(255) NOT NULL COMMENT 'Which ACL can use this template',
    `beg_effective_date` DATETIME COMMENT 'When is this template effective from, defaulting to now',
    `end_effective_date` DATETIME COMMENT 'When this template is no longer effective, defaults to 100 years from now',
    `active` tinyint(1) DEFAULT 1 COMMENT 'Is this template active',
    `form_data` text NOT NULL COMMENT 'The serialized form data for every element and its values',
    PRIMARY KEY (`template_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable form_templates_forms
CREATE TABLE IF NOT EXISTS `form_templates_form` (
    `form_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `display_name` varchar(255) NOT NULL COMMENT 'Display name of this form',
    `machine_name` varchar(255) NOT NULL COMMENT 'Machine-readbale name of the form, usually the name attribute of the HTML Input Element',
    `method` varchar(10) NOT NULL DEFAULT 'POST' COMMENT 'The method of submitting the form',
    `action` varchar(255) NULL COMMENT 'The full path of the PHP file processing the request',
    `active` tinyint(1) DEFAULT 1 COMMENT 'Is this form active',
    PRIMARY KEY (`form_id`)
) ENGINE=InnoDB;
#EndIf
