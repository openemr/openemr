ALTER TABLE `prescriptions` CHANGE `dosage` `dosage` VARCHAR( 10 ) DEFAULT NULL;
ALTER TABLE `prescriptions` ADD `route` INT AFTER `unit` ;

ALTER TABLE `facility` ADD `domain_identifier` VARCHAR( 60 ) ;

ALTER TABLE `history_data` ADD `name_1` VARCHAR( 255 ) ,
ADD `value_1` VARCHAR( 255 ) ,
ADD `name_2` VARCHAR( 255 ) ,
ADD `value_2` VARCHAR( 255 ) ,
ADD `additional_history` TEXT;

CREATE TABLE `openemr_postcalendar_topics` (
  `pc_catid` int(11) unsigned NOT NULL auto_increment,
  `pc_catname` varchar(100) NOT NULL default 'Undefined',
  `pc_catcolor` varchar(50) NOT NULL default '#DDDDDD',
  `pc_catdesc` text,
  PRIMARY KEY  (`pc_catid`),
  KEY `basic_cat` (`pc_catname`,`pc_catcolor`)
) TYPE=MyISAM;

UPDATE `openemr_module_vars` SET `pn_value` = '1' WHERE pn_name = 'pcDisplayTopics';

CREATE TABLE `categories` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rght` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `lft` (`lft`,`rght`)
) TYPE=MyISAM;

CREATE TABLE `categories_seq` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

INSERT INTO `categories_seq` VALUES (0);

CREATE TABLE `config` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rght` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `lft` (`lft`,`rght`)
) TYPE=MyISAM;

CREATE TABLE `config_seq` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

INSERT INTO `config_seq` VALUES (0);

CREATE TABLE `documents` (
  `id` int(11) NOT NULL default '0',
  `type` enum('file_url','blob','web_url') default NULL,
  `size` int(11) default NULL,
  `date` datetime default NULL,
  `url` varchar(255) default NULL,
  `mimetype` varchar(255) default NULL,
  `pages` int(11) default NULL,
  `owner` int(11) default NULL,
  `revision` timestamp(14) NOT NULL,
  `foreign_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `revision` (`revision`),
  KEY `foreign_id` (`foreign_id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM;

CREATE TABLE `notes` (
  `id` int(11) NOT NULL default '0',
  `foreign_id` int(11) NOT NULL default '0',
  `note` varchar(255) default NULL,
  `owner` int(11) default NULL,
  `date` datetime default NULL,
  `revision` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `foreign_id` (`owner`),
  KEY `foreign_id_2` (`foreign_id`),
  KEY `date` (`date`)
) TYPE=MyISAM;

CREATE TABLE `categories_to_documents` (
  `category_id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`document_id`)
) TYPE=MyISAM;

CREATE TABLE `x12_partners` (
`id` INT NOT NULL ,
`name` VARCHAR( 255 ) ,
`x12_sender_id` VARCHAR( 255 ) ,
`x12_receiver_id` VARCHAR( 255 ) ,
`x12_version` VARCHAR( 255 ) ,
`processing_format` ENUM( 'standard', 'medi-cal', 'cms' ),
PRIMARY KEY ( `id` )
);

ALTER TABLE `insurance_companies` ADD `x12_default_partner_id` INT;

ALTER TABLE `billing` ADD `x12_partner_id` INT;

CREATE TABLE `geo_zone_reference` (
  `zone_id` int(5) NOT NULL auto_increment,
  `zone_country_id` int(5) NOT NULL default '0',
  `zone_code` varchar(5) NOT NULL default '',
  `zone_name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`zone_id`)
) TYPE=MyISAM;

INSERT INTO `geo_zone_reference` VALUES (1, 223, 'AL', 'Alabama');
INSERT INTO `geo_zone_reference` VALUES (2, 223, 'AK', 'Alaska');
INSERT INTO `geo_zone_reference` VALUES (3, 223, 'AS', 'American Samoa');
INSERT INTO `geo_zone_reference` VALUES (4, 223, 'AZ', 'Arizona');
INSERT INTO `geo_zone_reference` VALUES (5, 223, 'AR', 'Arkansas');
INSERT INTO `geo_zone_reference` VALUES (6, 223, 'AF', 'Armed Forces Africa');
INSERT INTO `geo_zone_reference` VALUES (7, 223, 'AA', 'Armed Forces Americas');
INSERT INTO `geo_zone_reference` VALUES (8, 223, 'AC', 'Armed Forces Canada');
INSERT INTO `geo_zone_reference` VALUES (9, 223, 'AE', 'Armed Forces Europe');
INSERT INTO `geo_zone_reference` VALUES (10, 223, 'AM', 'Armed Forces Middle East');
INSERT INTO `geo_zone_reference` VALUES (11, 223, 'AP', 'Armed Forces Pacific');
INSERT INTO `geo_zone_reference` VALUES (12, 223, 'CA', 'California');
INSERT INTO `geo_zone_reference` VALUES (13, 223, 'CO', 'Colorado');
INSERT INTO `geo_zone_reference` VALUES (14, 223, 'CT', 'Connecticut');
INSERT INTO `geo_zone_reference` VALUES (15, 223, 'DE', 'Delaware');
INSERT INTO `geo_zone_reference` VALUES (16, 223, 'DC', 'District of Columbia');
INSERT INTO `geo_zone_reference` VALUES (17, 223, 'FM', 'Federated States Of Micronesia');
INSERT INTO `geo_zone_reference` VALUES (18, 223, 'FL', 'Florida');
INSERT INTO `geo_zone_reference` VALUES (19, 223, 'GA', 'Georgia');
INSERT INTO `geo_zone_reference` VALUES (20, 223, 'GU', 'Guam');
INSERT INTO `geo_zone_reference` VALUES (21, 223, 'HI', 'Hawaii');
INSERT INTO `geo_zone_reference` VALUES (22, 223, 'ID', 'Idaho');
INSERT INTO `geo_zone_reference` VALUES (23, 223, 'IL', 'Illinois');
INSERT INTO `geo_zone_reference` VALUES (24, 223, 'IN', 'Indiana');
INSERT INTO `geo_zone_reference` VALUES (25, 223, 'IA', 'Iowa');
INSERT INTO `geo_zone_reference` VALUES (26, 223, 'KS', 'Kansas');
INSERT INTO `geo_zone_reference` VALUES (27, 223, 'KY', 'Kentucky');
INSERT INTO `geo_zone_reference` VALUES (28, 223, 'LA', 'Louisiana');
INSERT INTO `geo_zone_reference` VALUES (29, 223, 'ME', 'Maine');
INSERT INTO `geo_zone_reference` VALUES (30, 223, 'MH', 'Marshall Islands');
INSERT INTO `geo_zone_reference` VALUES (31, 223, 'MD', 'Maryland');
INSERT INTO `geo_zone_reference` VALUES (32, 223, 'MA', 'Massachusetts');
INSERT INTO `geo_zone_reference` VALUES (33, 223, 'MI', 'Michigan');
INSERT INTO `geo_zone_reference` VALUES (34, 223, 'MN', 'Minnesota');
INSERT INTO `geo_zone_reference` VALUES (35, 223, 'MS', 'Mississippi');
INSERT INTO `geo_zone_reference` VALUES (36, 223, 'MO', 'Missouri');
INSERT INTO `geo_zone_reference` VALUES (37, 223, 'MT', 'Montana');
INSERT INTO `geo_zone_reference` VALUES (38, 223, 'NE', 'Nebraska');
INSERT INTO `geo_zone_reference` VALUES (39, 223, 'NV', 'Nevada');
INSERT INTO `geo_zone_reference` VALUES (40, 223, 'NH', 'New Hampshire');
INSERT INTO `geo_zone_reference` VALUES (41, 223, 'NJ', 'New Jersey');
INSERT INTO `geo_zone_reference` VALUES (42, 223, 'NM', 'New Mexico');
INSERT INTO `geo_zone_reference` VALUES (43, 223, 'NY', 'New York');
INSERT INTO `geo_zone_reference` VALUES (44, 223, 'NC', 'North Carolina');
INSERT INTO `geo_zone_reference` VALUES (45, 223, 'ND', 'North Dakota');
INSERT INTO `geo_zone_reference` VALUES (46, 223, 'MP', 'Northern Mariana Islands');
INSERT INTO `geo_zone_reference` VALUES (47, 223, 'OH', 'Ohio');
INSERT INTO `geo_zone_reference` VALUES (48, 223, 'OK', 'Oklahoma');
INSERT INTO `geo_zone_reference` VALUES (49, 223, 'OR', 'Oregon');
INSERT INTO `geo_zone_reference` VALUES (50, 223, 'PW', 'Palau');
INSERT INTO `geo_zone_reference` VALUES (51, 223, 'PA', 'Pennsylvania');
INSERT INTO `geo_zone_reference` VALUES (52, 223, 'PR', 'Puerto Rico');
INSERT INTO `geo_zone_reference` VALUES (53, 223, 'RI', 'Rhode Island');
INSERT INTO `geo_zone_reference` VALUES (54, 223, 'SC', 'South Carolina');
INSERT INTO `geo_zone_reference` VALUES (55, 223, 'SD', 'South Dakota');
INSERT INTO `geo_zone_reference` VALUES (56, 223, 'TN', 'Tenessee');
INSERT INTO `geo_zone_reference` VALUES (57, 223, 'TX', 'Texas');
INSERT INTO `geo_zone_reference` VALUES (58, 223, 'UT', 'Utah');
INSERT INTO `geo_zone_reference` VALUES (59, 223, 'VT', 'Vermont');
INSERT INTO `geo_zone_reference` VALUES (60, 223, 'VI', 'Virgin Islands');
INSERT INTO `geo_zone_reference` VALUES (61, 223, 'VA', 'Virginia');
INSERT INTO `geo_zone_reference` VALUES (62, 223, 'WA', 'Washington');
INSERT INTO `geo_zone_reference` VALUES (63, 223, 'WV', 'West Virginia');
INSERT INTO `geo_zone_reference` VALUES (64, 223, 'WI', 'Wisconsin');
INSERT INTO `geo_zone_reference` VALUES (65, 223, 'WY', 'Wyoming');
INSERT INTO `geo_zone_reference` VALUES (66, 38, 'AB', 'Alberta');
INSERT INTO `geo_zone_reference` VALUES (67, 38, 'BC', 'British Columbia');
INSERT INTO `geo_zone_reference` VALUES (68, 38, 'MB', 'Manitoba');
INSERT INTO `geo_zone_reference` VALUES (69, 38, 'NF', 'Newfoundland');
INSERT INTO `geo_zone_reference` VALUES (70, 38, 'NB', 'New Brunswick');
INSERT INTO `geo_zone_reference` VALUES (71, 38, 'NS', 'Nova Scotia');
INSERT INTO `geo_zone_reference` VALUES (72, 38, 'NT', 'Northwest Territories');
INSERT INTO `geo_zone_reference` VALUES (73, 38, 'NU', 'Nunavut');
INSERT INTO `geo_zone_reference` VALUES (74, 38, 'ON', 'Ontario');
INSERT INTO `geo_zone_reference` VALUES (75, 38, 'PE', 'Prince Edward Island');
INSERT INTO `geo_zone_reference` VALUES (76, 38, 'QC', 'Quebec');
INSERT INTO `geo_zone_reference` VALUES (77, 38, 'SK', 'Saskatchewan');
INSERT INTO `geo_zone_reference` VALUES (78, 38, 'YT', 'Yukon Territory');
INSERT INTO `geo_zone_reference` VALUES (79, 61, 'QLD', 'Queensland');
INSERT INTO `geo_zone_reference` VALUES (80, 61, 'SA', 'South Australia');
INSERT INTO `geo_zone_reference` VALUES (81, 61, 'ACT', 'Australian Capital Territory');
INSERT INTO `geo_zone_reference` VALUES (82, 61, 'VIC', 'Victoria');

CREATE TABLE `geo_country_reference` (
  `countries_id` int(5) NOT NULL auto_increment,
  `countries_name` varchar(64) NOT NULL default '',
  `countries_iso_code_2` char(2) NOT NULL default '',
  `countries_iso_code_3` char(3) NOT NULL default '',
  PRIMARY KEY  (`countries_id`),
  KEY `IDX_COUNTRIES_NAME` (`countries_name`)
) TYPE=MyISAM;

INSERT INTO `geo_country_reference` VALUES (1, 'Afghanistan', 'AF', 'AFG');
INSERT INTO `geo_country_reference` VALUES (2, 'Albania', 'AL', 'ALB');
INSERT INTO `geo_country_reference` VALUES (3, 'Algeria', 'DZ', 'DZA');
INSERT INTO `geo_country_reference` VALUES (4, 'American Samoa', 'AS', 'ASM');
INSERT INTO `geo_country_reference` VALUES (5, 'Andorra', 'AD', 'AND');
INSERT INTO `geo_country_reference` VALUES (6, 'Angola', 'AO', 'AGO');
INSERT INTO `geo_country_reference` VALUES (7, 'Anguilla', 'AI', 'AIA');
INSERT INTO `geo_country_reference` VALUES (8, 'Antarctica', 'AQ', 'ATA');
INSERT INTO `geo_country_reference` VALUES (9, 'Antigua and Barbuda', 'AG', 'ATG');
INSERT INTO `geo_country_reference` VALUES (10, 'Argentina', 'AR', 'ARG');
INSERT INTO `geo_country_reference` VALUES (11, 'Armenia', 'AM', 'ARM');
INSERT INTO `geo_country_reference` VALUES (12, 'Aruba', 'AW', 'ABW');
INSERT INTO `geo_country_reference` VALUES (13, 'Australia', 'AU', 'AUS');
INSERT INTO `geo_country_reference` VALUES (14, 'Austria', 'AT', 'AUT');
INSERT INTO `geo_country_reference` VALUES (15, 'Azerbaijan', 'AZ', 'AZE');
INSERT INTO `geo_country_reference` VALUES (16, 'Bahamas', 'BS', 'BHS');
INSERT INTO `geo_country_reference` VALUES (17, 'Bahrain', 'BH', 'BHR');
INSERT INTO `geo_country_reference` VALUES (18, 'Bangladesh', 'BD', 'BGD');
INSERT INTO `geo_country_reference` VALUES (19, 'Barbados', 'BB', 'BRB');
INSERT INTO `geo_country_reference` VALUES (20, 'Belarus', 'BY', 'BLR');
INSERT INTO `geo_country_reference` VALUES (21, 'Belgium', 'BE', 'BEL');
INSERT INTO `geo_country_reference` VALUES (22, 'Belize', 'BZ', 'BLZ');
INSERT INTO `geo_country_reference` VALUES (23, 'Benin', 'BJ', 'BEN');
INSERT INTO `geo_country_reference` VALUES (24, 'Bermuda', 'BM', 'BMU');
INSERT INTO `geo_country_reference` VALUES (25, 'Bhutan', 'BT', 'BTN');
INSERT INTO `geo_country_reference` VALUES (26, 'Bolivia', 'BO', 'BOL');
INSERT INTO `geo_country_reference` VALUES (27, 'Bosnia and Herzegowina', 'BA', 'BIH');
INSERT INTO `geo_country_reference` VALUES (28, 'Botswana', 'BW', 'BWA');
INSERT INTO `geo_country_reference` VALUES (29, 'Bouvet Island', 'BV', 'BVT');
INSERT INTO `geo_country_reference` VALUES (30, 'Brazil', 'BR', 'BRA');
INSERT INTO `geo_country_reference` VALUES (31, 'British Indian Ocean Territory', 'IO', 'IOT');
INSERT INTO `geo_country_reference` VALUES (32, 'Brunei Darussalam', 'BN', 'BRN');
INSERT INTO `geo_country_reference` VALUES (33, 'Bulgaria', 'BG', 'BGR');
INSERT INTO `geo_country_reference` VALUES (34, 'Burkina Faso', 'BF', 'BFA');
INSERT INTO `geo_country_reference` VALUES (35, 'Burundi', 'BI', 'BDI');
INSERT INTO `geo_country_reference` VALUES (36, 'Cambodia', 'KH', 'KHM');
INSERT INTO `geo_country_reference` VALUES (37, 'Cameroon', 'CM', 'CMR');
INSERT INTO `geo_country_reference` VALUES (38, 'Canada', 'CA', 'CAN');
INSERT INTO `geo_country_reference` VALUES (39, 'Cape Verde', 'CV', 'CPV');
INSERT INTO `geo_country_reference` VALUES (40, 'Cayman Islands', 'KY', 'CYM');
INSERT INTO `geo_country_reference` VALUES (41, 'Central African Republic', 'CF', 'CAF');
INSERT INTO `geo_country_reference` VALUES (42, 'Chad', 'TD', 'TCD');
INSERT INTO `geo_country_reference` VALUES (43, 'Chile', 'CL', 'CHL');
INSERT INTO `geo_country_reference` VALUES (44, 'China', 'CN', 'CHN');
INSERT INTO `geo_country_reference` VALUES (45, 'Christmas Island', 'CX', 'CXR');
INSERT INTO `geo_country_reference` VALUES (46, 'Cocos (Keeling) Islands', 'CC', 'CCK');
INSERT INTO `geo_country_reference` VALUES (47, 'Colombia', 'CO', 'COL');
INSERT INTO `geo_country_reference` VALUES (48, 'Comoros', 'KM', 'COM');
INSERT INTO `geo_country_reference` VALUES (49, 'Congo', 'CG', 'COG');
INSERT INTO `geo_country_reference` VALUES (50, 'Cook Islands', 'CK', 'COK');
INSERT INTO `geo_country_reference` VALUES (51, 'Costa Rica', 'CR', 'CRI');
INSERT INTO `geo_country_reference` VALUES (52, 'Cote D Ivoire', 'CI', 'CIV');
INSERT INTO `geo_country_reference` VALUES (53, 'Croatia', 'HR', 'HRV');
INSERT INTO `geo_country_reference` VALUES (54, 'Cuba', 'CU', 'CUB');
INSERT INTO `geo_country_reference` VALUES (55, 'Cyprus', 'CY', 'CYP');
INSERT INTO `geo_country_reference` VALUES (56, 'Czech Republic', 'CZ', 'CZE');
INSERT INTO `geo_country_reference` VALUES (57, 'Denmark', 'DK', 'DNK');
INSERT INTO `geo_country_reference` VALUES (58, 'Djibouti', 'DJ', 'DJI');
INSERT INTO `geo_country_reference` VALUES (59, 'Dominica', 'DM', 'DMA');
INSERT INTO `geo_country_reference` VALUES (60, 'Dominican Republic', 'DO', 'DOM');
INSERT INTO `geo_country_reference` VALUES (61, 'East Timor', 'TP', 'TMP');
INSERT INTO `geo_country_reference` VALUES (62, 'Ecuador', 'EC', 'ECU');
INSERT INTO `geo_country_reference` VALUES (63, 'Egypt', 'EG', 'EGY');
INSERT INTO `geo_country_reference` VALUES (64, 'El Salvador', 'SV', 'SLV');
INSERT INTO `geo_country_reference` VALUES (65, 'Equatorial Guinea', 'GQ', 'GNQ');
INSERT INTO `geo_country_reference` VALUES (66, 'Eritrea', 'ER', 'ERI');
INSERT INTO `geo_country_reference` VALUES (67, 'Estonia', 'EE', 'EST');
INSERT INTO `geo_country_reference` VALUES (68, 'Ethiopia', 'ET', 'ETH');
INSERT INTO `geo_country_reference` VALUES (69, 'Falkland Islands (Malvinas)', 'FK', 'FLK');
INSERT INTO `geo_country_reference` VALUES (70, 'Faroe Islands', 'FO', 'FRO');
INSERT INTO `geo_country_reference` VALUES (71, 'Fiji', 'FJ', 'FJI');
INSERT INTO `geo_country_reference` VALUES (72, 'Finland', 'FI', 'FIN');
INSERT INTO `geo_country_reference` VALUES (73, 'France', 'FR', 'FRA');
INSERT INTO `geo_country_reference` VALUES (74, 'France, MEtropolitan', 'FX', 'FXX');
INSERT INTO `geo_country_reference` VALUES (75, 'French Guiana', 'GF', 'GUF');
INSERT INTO `geo_country_reference` VALUES (76, 'French Polynesia', 'PF', 'PYF');
INSERT INTO `geo_country_reference` VALUES (77, 'French Southern Territories', 'TF', 'ATF');
INSERT INTO `geo_country_reference` VALUES (78, 'Gabon', 'GA', 'GAB');
INSERT INTO `geo_country_reference` VALUES (79, 'Gambia', 'GM', 'GMB');
INSERT INTO `geo_country_reference` VALUES (80, 'Georgia', 'GE', 'GEO');
INSERT INTO `geo_country_reference` VALUES (81, 'Germany', 'DE', 'DEU');
INSERT INTO `geo_country_reference` VALUES (82, 'Ghana', 'GH', 'GHA');
INSERT INTO `geo_country_reference` VALUES (83, 'Gibraltar', 'GI', 'GIB');
INSERT INTO `geo_country_reference` VALUES (84, 'Greece', 'GR', 'GRC');
INSERT INTO `geo_country_reference` VALUES (85, 'Greenland', 'GL', 'GRL');
INSERT INTO `geo_country_reference` VALUES (86, 'Grenada', 'GD', 'GRD');
INSERT INTO `geo_country_reference` VALUES (87, 'Guadeloupe', 'GP', 'GLP');
INSERT INTO `geo_country_reference` VALUES (88, 'Guam', 'GU', 'GUM');
INSERT INTO `geo_country_reference` VALUES (89, 'Guatemala', 'GT', 'GTM');
INSERT INTO `geo_country_reference` VALUES (90, 'Guinea', 'GN', 'GIN');
INSERT INTO `geo_country_reference` VALUES (91, 'Guinea-bissau', 'GW', 'GNB');
INSERT INTO `geo_country_reference` VALUES (92, 'Guyana', 'GY', 'GUY');
INSERT INTO `geo_country_reference` VALUES (93, 'Haiti', 'HT', 'HTI');
INSERT INTO `geo_country_reference` VALUES (94, 'Heard and Mc Donald Islands', 'HM', 'HMD');
INSERT INTO `geo_country_reference` VALUES (95, 'Honduras', 'HN', 'HND');
INSERT INTO `geo_country_reference` VALUES (96, 'Hong Kong', 'HK', 'HKG');
INSERT INTO `geo_country_reference` VALUES (97, 'Hungary', 'HU', 'HUN');
INSERT INTO `geo_country_reference` VALUES (98, 'Iceland', 'IS', 'ISL');
INSERT INTO `geo_country_reference` VALUES (99, 'India', 'IN', 'IND');
INSERT INTO `geo_country_reference` VALUES (100, 'Indonesia', 'ID', 'IDN');
INSERT INTO `geo_country_reference` VALUES (101, 'Iran (Islamic Republic of)', 'IR', 'IRN');
INSERT INTO `geo_country_reference` VALUES (102, 'Iraq', 'IQ', 'IRQ');
INSERT INTO `geo_country_reference` VALUES (103, 'Ireland', 'IE', 'IRL');
INSERT INTO `geo_country_reference` VALUES (104, 'Israel', 'IL', 'ISR');
INSERT INTO `geo_country_reference` VALUES (105, 'Italy', 'IT', 'ITA');
INSERT INTO `geo_country_reference` VALUES (106, 'Jamaica', 'JM', 'JAM');
INSERT INTO `geo_country_reference` VALUES (107, 'Japan', 'JP', 'JPN');
INSERT INTO `geo_country_reference` VALUES (108, 'Jordan', 'JO', 'JOR');
INSERT INTO `geo_country_reference` VALUES (109, 'Kazakhstan', 'KZ', 'KAZ');
INSERT INTO `geo_country_reference` VALUES (110, 'Kenya', 'KE', 'KEN');
INSERT INTO `geo_country_reference` VALUES (111, 'Kiribati', 'KI', 'KIR');
INSERT INTO `geo_country_reference` VALUES (112, 'Korea, Democratic Peoples Republic of', 'KP', 'PRK');
INSERT INTO `geo_country_reference` VALUES (113, 'Korea, Republic of', 'KR', 'KOR');
INSERT INTO `geo_country_reference` VALUES (114, 'Kuwait', 'KW', 'KWT');
INSERT INTO `geo_country_reference` VALUES (115, 'Kyrgyzstan', 'KG', 'KGZ');
INSERT INTO `geo_country_reference` VALUES (116, 'Lao Peoples Democratic Republic', 'LA', 'LAO');
INSERT INTO `geo_country_reference` VALUES (117, 'Latvia', 'LV', 'LVA');
INSERT INTO `geo_country_reference` VALUES (118, 'Lebanon', 'LB', 'LBN');
INSERT INTO `geo_country_reference` VALUES (119, 'Lesotho', 'LS', 'LSO');
INSERT INTO `geo_country_reference` VALUES (120, 'Liberia', 'LR', 'LBR');
INSERT INTO `geo_country_reference` VALUES (121, 'Libyan Arab Jamahiriya', 'LY', 'LBY');
INSERT INTO `geo_country_reference` VALUES (122, 'Liechtenstein', 'LI', 'LIE');
INSERT INTO `geo_country_reference` VALUES (123, 'Lithuania', 'LT', 'LTU');
INSERT INTO `geo_country_reference` VALUES (124, 'Luxembourg', 'LU', 'LUX');
INSERT INTO `geo_country_reference` VALUES (125, 'Macau', 'MO', 'MAC');
INSERT INTO `geo_country_reference` VALUES (126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD');
INSERT INTO `geo_country_reference` VALUES (127, 'Madagascar', 'MG', 'MDG');
INSERT INTO `geo_country_reference` VALUES (128, 'Malawi', 'MW', 'MWI');
INSERT INTO `geo_country_reference` VALUES (129, 'Malaysia', 'MY', 'MYS');
INSERT INTO `geo_country_reference` VALUES (130, 'Maldives', 'MV', 'MDV');
INSERT INTO `geo_country_reference` VALUES (131, 'Mali', 'ML', 'MLI');
INSERT INTO `geo_country_reference` VALUES (132, 'Malta', 'MT', 'MLT');
INSERT INTO `geo_country_reference` VALUES (133, 'Marshall Islands', 'MH', 'MHL');
INSERT INTO `geo_country_reference` VALUES (134, 'Martinique', 'MQ', 'MTQ');
INSERT INTO `geo_country_reference` VALUES (135, 'Mauritania', 'MR', 'MRT');
INSERT INTO `geo_country_reference` VALUES (136, 'Mauritius', 'MU', 'MUS');
INSERT INTO `geo_country_reference` VALUES (137, 'Mayotte', 'YT', 'MYT');
INSERT INTO `geo_country_reference` VALUES (138, 'Mexico', 'MX', 'MEX');
INSERT INTO `geo_country_reference` VALUES (139, 'Micronesia, Federated States of', 'FM', 'FSM');
INSERT INTO `geo_country_reference` VALUES (140, 'Moldova, Republic of', 'MD', 'MDA');
INSERT INTO `geo_country_reference` VALUES (141, 'Monaco', 'MC', 'MCO');
INSERT INTO `geo_country_reference` VALUES (142, 'Mongolia', 'MN', 'MNG');
INSERT INTO `geo_country_reference` VALUES (143, 'Montserrat', 'MS', 'MSR');
INSERT INTO `geo_country_reference` VALUES (144, 'Morocco', 'MA', 'MAR');
INSERT INTO `geo_country_reference` VALUES (145, 'Mozambique', 'MZ', 'MOZ');
INSERT INTO `geo_country_reference` VALUES (146, 'Myanmar', 'MM', 'MMR');
INSERT INTO `geo_country_reference` VALUES (147, 'Namibia', 'NA', 'NAM');
INSERT INTO `geo_country_reference` VALUES (148, 'Nauru', 'NR', 'NRU');
INSERT INTO `geo_country_reference` VALUES (149, 'Nepal', 'NP', 'NPL');
INSERT INTO `geo_country_reference` VALUES (150, 'Netherlands', 'NL', 'NLD');
INSERT INTO `geo_country_reference` VALUES (151, 'Netherlands Antilles', 'AN', 'ANT');
INSERT INTO `geo_country_reference` VALUES (152, 'New Caledonia', 'NC', 'NCL');
INSERT INTO `geo_country_reference` VALUES (153, 'New Zealand', 'NZ', 'NZL');
INSERT INTO `geo_country_reference` VALUES (154, 'Nicaragua', 'NI', 'NIC');
INSERT INTO `geo_country_reference` VALUES (155, 'Niger', 'NE', 'NER');
INSERT INTO `geo_country_reference` VALUES (156, 'Nigeria', 'NG', 'NGA');
INSERT INTO `geo_country_reference` VALUES (157, 'Niue', 'NU', 'NIU');
INSERT INTO `geo_country_reference` VALUES (158, 'Norfolk Island', 'NF', 'NFK');
INSERT INTO `geo_country_reference` VALUES (159, 'Northern Mariana Islands', 'MP', 'MNP');
INSERT INTO `geo_country_reference` VALUES (160, 'Norway', 'NO', 'NOR');
INSERT INTO `geo_country_reference` VALUES (161, 'Oman', 'OM', 'OMN');
INSERT INTO `geo_country_reference` VALUES (162, 'Pakistan', 'PK', 'PAK');
INSERT INTO `geo_country_reference` VALUES (163, 'Palau', 'PW', 'PLW');
INSERT INTO `geo_country_reference` VALUES (164, 'Panama', 'PA', 'PAN');
INSERT INTO `geo_country_reference` VALUES (165, 'Papua New Guinea', 'PG', 'PNG');
INSERT INTO `geo_country_reference` VALUES (166, 'Paraguay', 'PY', 'PRY');
INSERT INTO `geo_country_reference` VALUES (167, 'Peru', 'PE', 'PER');
INSERT INTO `geo_country_reference` VALUES (168, 'Philippines', 'PH', 'PHL');
INSERT INTO `geo_country_reference` VALUES (169, 'Pitcairn', 'PN', 'PCN');
INSERT INTO `geo_country_reference` VALUES (170, 'Poland', 'PL', 'POL');
INSERT INTO `geo_country_reference` VALUES (171, 'Portugal', 'PT', 'PRT');
INSERT INTO `geo_country_reference` VALUES (172, 'Puerto Rico', 'PR', 'PRI');
INSERT INTO `geo_country_reference` VALUES (173, 'Qatar', 'QA', 'QAT');
INSERT INTO `geo_country_reference` VALUES (174, 'Reunion', 'RE', 'REU');
INSERT INTO `geo_country_reference` VALUES (175, 'Romania', 'RO', 'ROM');
INSERT INTO `geo_country_reference` VALUES (176, 'Russian Federation', 'RU', 'RUS');
INSERT INTO `geo_country_reference` VALUES (177, 'Rwanda', 'RW', 'RWA');
INSERT INTO `geo_country_reference` VALUES (178, 'Saint Kitts and Nevis', 'KN', 'KNA');
INSERT INTO `geo_country_reference` VALUES (179, 'Saint Lucia', 'LC', 'LCA');
INSERT INTO `geo_country_reference` VALUES (180, 'Saint Vincent and the Grenadines', 'VC', 'VCT');
INSERT INTO `geo_country_reference` VALUES (181, 'Samoa', 'WS', 'WSM');
INSERT INTO `geo_country_reference` VALUES (182, 'San Marino', 'SM', 'SMR');
INSERT INTO `geo_country_reference` VALUES (183, 'Sao Tome and Principe', 'ST', 'STP');
INSERT INTO `geo_country_reference` VALUES (184, 'Saudi Arabia', 'SA', 'SAU');
INSERT INTO `geo_country_reference` VALUES (185, 'Senegal', 'SN', 'SEN');
INSERT INTO `geo_country_reference` VALUES (186, 'Seychelles', 'SC', 'SYC');
INSERT INTO `geo_country_reference` VALUES (187, 'Sierra Leone', 'SL', 'SLE');
INSERT INTO `geo_country_reference` VALUES (188, 'Singapore', 'SG', 'SGP');
INSERT INTO `geo_country_reference` VALUES (189, 'Slovakia (Slovak Republic)', 'SK', 'SVK');
INSERT INTO `geo_country_reference` VALUES (190, 'Slovenia', 'SI', 'SVN');
INSERT INTO `geo_country_reference` VALUES (191, 'Solomon Islands', 'SB', 'SLB');
INSERT INTO `geo_country_reference` VALUES (192, 'Somalia', 'SO', 'SOM');
INSERT INTO `geo_country_reference` VALUES (193, 'south Africa', 'ZA', 'ZAF');
INSERT INTO `geo_country_reference` VALUES (194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS');
INSERT INTO `geo_country_reference` VALUES (195, 'Spain', 'ES', 'ESP');
INSERT INTO `geo_country_reference` VALUES (196, 'Sri Lanka', 'LK', 'LKA');
INSERT INTO `geo_country_reference` VALUES (197, 'St. Helena', 'SH', 'SHN');
INSERT INTO `geo_country_reference` VALUES (198, 'St. Pierre and Miquelon', 'PM', 'SPM');
INSERT INTO `geo_country_reference` VALUES (199, 'Sudan', 'SD', 'SDN');
INSERT INTO `geo_country_reference` VALUES (200, 'Suriname', 'SR', 'SUR');
INSERT INTO `geo_country_reference` VALUES (201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM');
INSERT INTO `geo_country_reference` VALUES (202, 'Swaziland', 'SZ', 'SWZ');
INSERT INTO `geo_country_reference` VALUES (203, 'Sweden', 'SE', 'SWE');
INSERT INTO `geo_country_reference` VALUES (204, 'Switzerland', 'CH', 'CHE');
INSERT INTO `geo_country_reference` VALUES (205, 'Syrian Arab Republic', 'SY', 'SYR');
INSERT INTO `geo_country_reference` VALUES (206, 'Taiwan, Province of China', 'TW', 'TWN');
INSERT INTO `geo_country_reference` VALUES (207, 'Tajikistan', 'TJ', 'TJK');
INSERT INTO `geo_country_reference` VALUES (208, 'Tanzania, United Republic of', 'TZ', 'TZA');
INSERT INTO `geo_country_reference` VALUES (209, 'Thailand', 'TH', 'THA');
INSERT INTO `geo_country_reference` VALUES (210, 'Togo', 'TG', 'TGO');
INSERT INTO `geo_country_reference` VALUES (211, 'Tokelau', 'TK', 'TKL');
INSERT INTO `geo_country_reference` VALUES (212, 'Tonga', 'TO', 'TON');
INSERT INTO `geo_country_reference` VALUES (213, 'Trinidad and Tobago', 'TT', 'TTO');
INSERT INTO `geo_country_reference` VALUES (214, 'Tunisia', 'TN', 'TUN');
INSERT INTO `geo_country_reference` VALUES (215, 'Turkey', 'TR', 'TUR');
INSERT INTO `geo_country_reference` VALUES (216, 'Turkmenistan', 'TM', 'TKM');
INSERT INTO `geo_country_reference` VALUES (217, 'Turks and Caicos Islands', 'TC', 'TCA');
INSERT INTO `geo_country_reference` VALUES (218, 'Tuvalu', 'TV', 'TUV');
INSERT INTO `geo_country_reference` VALUES (219, 'Uganda', 'UG', 'UGA');
INSERT INTO `geo_country_reference` VALUES (220, 'Ukraine', 'UA', 'UKR');
INSERT INTO `geo_country_reference` VALUES (221, 'United Arab Emirates', 'AE', 'ARE');
INSERT INTO `geo_country_reference` VALUES (222, 'United Kingdom', 'GB', 'GBR');
INSERT INTO `geo_country_reference` VALUES (223, 'United States', 'US', 'USA');
INSERT INTO `geo_country_reference` VALUES (224, 'United States Minor Outlying Islands', 'UM', 'UMI');
INSERT INTO `geo_country_reference` VALUES (225, 'Uruguay', 'UY', 'URY');
INSERT INTO `geo_country_reference` VALUES (226, 'Uzbekistan', 'UZ', 'UZB');
INSERT INTO `geo_country_reference` VALUES (227, 'Vanuatu', 'VU', 'VUT');
INSERT INTO `geo_country_reference` VALUES (228, 'Vatican City State (Holy See)', 'VA', 'VAT');
INSERT INTO `geo_country_reference` VALUES (229, 'Venezuela', 'VE', 'VEN');
INSERT INTO `geo_country_reference` VALUES (230, 'Viet Nam', 'VN', 'VNM');
INSERT INTO `geo_country_reference` VALUES (231, 'Virgin Islands (British)', 'VG', 'VGB');
INSERT INTO `geo_country_reference` VALUES (232, 'Virgin Islands (U.S.)', 'VI', 'VIR');
INSERT INTO `geo_country_reference` VALUES (233, 'Wallis and Futuna Islands', 'WF', 'WLF');
INSERT INTO `geo_country_reference` VALUES (234, 'Western Sahara', 'EH', 'ESH');
INSERT INTO `geo_country_reference` VALUES (235, 'Yemen', 'YE', 'YEM');
INSERT INTO `geo_country_reference` VALUES (236, 'Yugoslavia', 'YU', 'YUG');
INSERT INTO `geo_country_reference` VALUES (237, 'Zaire', 'ZR', 'ZAR');
INSERT INTO `geo_country_reference` VALUES (238, 'Zambia', 'ZM', 'ZMB');
INSERT INTO `geo_country_reference` VALUES (239, 'Zimbabwe', 'ZW', 'ZWE');

CREATE TABLE `integration_mapping` (
`id` INT NOT NULL ,
`foreign_id` INT NOT NULL ,
`foreign_table` VARCHAR( 125 ) NOT NULL ,
`local_id` INT NOT NULL ,
`local_table` VARCHAR( 125 ) NOT NULL ,
PRIMARY KEY ( `id` ),
UNIQUE (`foreign_id`,`foreign_table`,`local_id`,`local_table`)
);

ALTER TABLE `x12_partners` ADD `id_number` VARCHAR( 255 ) AFTER `name` ;

ALTER TABLE `phone_numbers` CHANGE `prefix` `prefix` VARCHAR( 3 ) DEFAULT NULL; 
ALTER TABLE `phone_numbers` CHANGE `area_code` `area_code` VARCHAR( 3 ) DEFAULT NULL;
ALTER TABLE `phone_numbers` CHANGE `number` `number` VARCHAR( 4 ) DEFAULT NULL; 

#
# Table structure for table `integration_mapping`
#

CREATE TABLE `integration_mapping` (
  `id` int(11) NOT NULL default '0',
  `foreign_id` int(11) NOT NULL default '0',
  `foreign_table` varchar(125) NOT NULL default '',
  `local_id` int(11) NOT NULL default '0',
  `local_table` varchar(125) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `foreign_id` (`foreign_id`,`foreign_table`,`local_id`,`local_table`)
) TYPE=MyISAM;
