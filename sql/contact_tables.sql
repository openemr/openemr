

-- --------------------------------------------------------
--
-- Table structure for table `contact`
--
DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
   	`id` BIGINT(20) NOT NULL auto_increment,
   	`foreign_table` VARCHAR(255) NOT NULL DEFAULT '',
   	`foreign_id` BIGINT(20) NOT NULL DEFAULT '0',
   	PRIMARY KEY (`id`),
   	KEY (`foreign_id`),
	INDEX idx_contact_foreign (foreign_table, foreign_id),
	INDEX idx_contact_lookup (foreign_table, foreign_id, id)
) ENGINE = InnoDB;

-- --------------------------------------------------------
--
-- Table structure for table `contact_address`
--
DROP TABLE IF EXISTS `contact_address`;
    CREATE TABLE `contact_address` (
    `id` BIGINT(20) NOT NULL auto_increment,
    `contact_id` BIGINT(20) NOT NULL,
    `address_id` BIGINT(20) NOT NULL,
    `priority` INT(11) NULL,
    `type` VARCHAR(255) NULL COMMENT 'FK to list_options.option_id for list_id address-types',
    `use` VARCHAR(255) NULL COMMENT 'FK to list_options.option_id for list_id address-uses',
    `notes` TINYTEXT,
    `status` CHAR(1) NULL COMMENT 'A=active,I=inactive',
    `is_primary` CHAR(1) NULL COMMENT 'Y=yes,N=no',
    `period_start` DATETIME NULL COMMENT 'Date the address became active',
    `period_end` DATETIME NULL COMMENT 'Date the address became deactivated',
    `inactivated_reason` VARCHAR(45) NULL DEFAULT NULL COMMENT '[Values: Moved, Mail Returned, etc]',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    PRIMARY KEY (`id`),
    KEY (`contact_id`),
    KEY (`address_id`),
    KEY contact_address_idx (`contact_id`,`address_id`)
) ENGINE = InnoDB ;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL default '0',
  `line1` varchar(255) default NULL,
  `line2` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `state` varchar(35) default NULL,
  `zip` varchar(10) default NULL,
  `plus_four` varchar(4) default NULL,
  `country` varchar(255) default NULL,
  `foreign_id` int(11) default NULL,
  `district` VARCHAR(255) DEFAULT NULL COMMENT 'The county or district of the address',
  PRIMARY KEY  (`id`),
  KEY `foreign_id` (`foreign_id`)
) ENGINE=InnoDB;



DROP TABLE IF EXISTS `contact_telecom`;
    CREATE TABLE `contact_telecom` (
    `id` BIGINT(20) NOT NULL auto_increment,
    `contact_id` BIGINT(20) NOT NULL,
    `rank` INT(11) NULL COMMENT 'Specify preferred order of use (1 = highest)',
    `system` VARCHAR(255) NULL
    	COMMENT 'FK to list_options.option_id for list_id telecom-systems [phone, fax, email, pager, url, sms, other]',
    `use` VARCHAR(255) NULL
    	COMMENT 'FK to list_options.option_id for list_id telecom-uses [home, work, temp, old, mobile]',
    `value` varchar(255) default NULL,
    `status` CHAR(1) NULL COMMENT 'A=active,I=inactive',
    `is_primary` CHAR(1) NULL COMMENT 'Y=yes,N=no',
    `notes` TINYTEXT,
    `period_start` DATETIME NULL COMMENT 'Date the telecom became active',
    `period_end` DATETIME NULL COMMENT 'Date the telecom became deactivated',
    `inactivated_reason` VARCHAR(45) DEFAULT NULL COMMENT '[Values: ???, etc]',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
   PRIMARY KEY (`id`),
    KEY (`contact_id`)
) ENGINE = InnoDB ;


#IfNotTable person
CREATE TABLE `person` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `uuid` BINARY(16) DEFAULT NULL,
    `title` VARCHAR(31) DEFAULT NULL COMMENT 'Mr., Mrs., Dr., etc.',
    `first_name` VARCHAR(63) DEFAULT NULL,
    `middle_name` VARCHAR(63) DEFAULT NULL,
    `last_name` VARCHAR(63) DEFAULT NULL,
    `preferred_name` VARCHAR(63) DEFAULT NULL COMMENT 'Name person prefers to be called',
    `gender` VARCHAR(31) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `death_date` DATE DEFAULT NULL,
    `marital_status` VARCHAR(31) DEFAULT NULL,
    `race` VARCHAR(63) DEFAULT NULL,
    `ethnicity` VARCHAR(63) DEFAULT NULL,
    `preferred_language` VARCHAR(63) DEFAULT NULL COMMENT 'ISO 639-1 code',
    `communication` VARCHAR(254) DEFAULT NULL COMMENT 'Communication preferences/needs',
    `ssn` VARCHAR(31) DEFAULT NULL COMMENT 'Should be encrypted in application',
    `active` TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    `inactive_reason` VARCHAR(255) DEFAULT NULL,
    `inactive_date` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uuid` (`uuid`),
    KEY `idx_person_name` (`last_name`, `first_name`),
    KEY `idx_person_dob` (`birth_date`),
    KEY `idx_person_search` (`last_name`, `first_name`, `birth_date`),
    KEY `idx_person_active` (`active`),
    CONSTRAINT `fk_person_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_person_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Core person demographics - contact info in contact_telecom';
#EndIf


#IfNotTable contact_relation
CREATE TABLE `contact_relation` (
    `id`  BIGINT(20) NOT NULL auto_increment,
    `contact_id`  BIGINT(20) NOT NULL,
    `target_table`  VARCHAR(255) NOT NULL DEFAULT '',
    `target_id`  BIGINT(20) NOT NULL,
    `active` BOOLEAN DEFAULT TRUE,
    `role` VARCHAR(63)  DEFAULT NULL,
    `relationship` VARCHAR(63)  DEFAULT NULL,
    `contact_priority` INT DEFAULT 1, -- 1 = highest priority
    `is_primary_contact` BOOLEAN DEFAULT FALSE,
    `is_emergency_contact` BOOLEAN DEFAULT FALSE,
    `can_make_medical_decisions` BOOLEAN DEFAULT FALSE,
    `can_receive_medical_info` BOOLEAN DEFAULT FALSE,
    `start_date` DATE,
    `end_date` DATE,
    `notes` TEXT,
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
    `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL COMMENT 'users.id',
   PRIMARY KEY (`id`),
   KEY (`contact_id`),
   INDEX idx_contact_target_table (target_table, target_id);
) ENGINE = InnoDB;
#EndIf


#IfNotTable person_patient_link
CREATE TABLE `person_patient_link` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `person_id` BIGINT(20) NOT NULL COMMENT 'FK to person.id',
    `patient_id` BIGINT(20) NOT NULL COMMENT 'FK to patient_data.id',
    `linked_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
    `linked_by` BIGINT(20) DEFAULT NULL COMMENT 'FK to users.id - who created the link',
    `link_method` VARCHAR(50) DEFAULT 'manual' COMMENT 'How link was created: manual, auto_detected, migrated, import',
    `notes` TEXT COMMENT 'Optional notes about why/how they were linked',
    `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether link is active (allows soft delete)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_active_link` (`person_id`, `patient_id`, `active`),
    CONSTRAINT `fk_ppl_person` FOREIGN KEY (`person_id`)
        REFERENCES `person`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ppl_patient` FOREIGN KEY (`patient_id`)
        REFERENCES `patient_data`(`id`) ON DELETE CASCADE,
    KEY `idx_ppl_person` (`person_id`),
    KEY `idx_ppl_patient` (`patient_id`),
    KEY `idx_ppl_active` (`active`),
    KEY `idx_ppl_linked_date` (`linked_date`),
    KEY `idx_ppl_method` (`link_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Links person records to patient_data records when person becomes patient';




#IfNotColumnType contact foreign_table VARCHAR(255)
ALTER TABLE `contact`
  	CHANGE COLUMN `foreign_table_name` `foreign_table`
  	VARCHAR(255) NOT NULL DEFAULT '';
#EndIf

or
ALTER TABLE `contact`
	CHANGE COLUMN `foreign_table_name` `foreign_table`
	VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';




-- -------------------------------------------------------------------------------------------------------------------------------------------------------
-- relatedperson-relationshiptype Valuesets
-- https://terminology.hl7.org/6.5.0/ValueSet-v3-PersonalRelationshipRoleType.html

#IfNotRow2D list_options list_id lists option_id related_person-relationships
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','related_person-relationship','Related Person Relationships',0, 1, 0);

INSERT INTO list_options
    (list_id,option_id,title,seq,is_default,activity)
VALUES
    -- Spouse/Partner
    ('related_person-relationship','SPS','spouse',10,0,1),
    ('related_person-relationship','HUSB','husband',20,0,1),
    ('related_person-relationship','WIFE','wife',30,0,1),
    ('related_person-relationship','DOMPART','domestic partner',40,0,1),
    ('related_person-relationship','SIGOTHR','significant other',50,0,1),
    ('related_person-relationship','FMRSPS','former spouse',60,0,1),

    -- Parents
    ('related_person-relationship','PRN','parent',70,0,1),
    ('related_person-relationship','NPRN','natural parent',80,0,1),
    ('related_person-relationship','FTH','father',90,0,1),
    ('related_person-relationship','NFTH','natural father',100,0,1),
    ('related_person-relationship','MTH','mother',110,0,1),
    ('related_person-relationship','NMTH','natural mother',120,0,1),
    ('related_person-relationship','ADOPTF','adoptive father',130,0,1),
    ('related_person-relationship','ADOPTM','adoptive mother',140,0,1),
    ('related_person-relationship','ADOPTP','adoptive parent',150,0,1),
    ('related_person-relationship','FTHFOST','foster father',160,0,1),
    ('related_person-relationship','MTHFOST','foster mother',170,0,1),
    ('related_person-relationship','PRNFOST','foster parent',180,0,1),
    ('related_person-relationship','STPFTH','stepfather',190,0,1),
    ('related_person-relationship','STPMTH','stepmother',200,0,1),
    ('related_person-relationship','STPPRN','step parent',210,0,1),
    ('related_person-relationship','GESTM','gestational mother',220,0,1),

    -- Children
    ('related_person-relationship','CHILD','Child',230,0,1),
    ('related_person-relationship','NCHILD','natural child',240,0,1),
    ('related_person-relationship','DAUC','daughter',250,0,1),
    ('related_person-relationship','DAU','natural daughter',260,0,1),
    ('related_person-relationship','SONC','son',270,0,1),
    ('related_person-relationship','SON','natural son',280,0,1),
    ('related_person-relationship','CHLDADOPT','Adopted Child',290,0,1),
    ('related_person-relationship','DAUADOPT','Adopted Daughter',300,0,1),
    ('related_person-relationship','SONADOPT','Adopted Son',310,0,1),
    ('related_person-relationship','CHLDFOST','Foster Child',320,0,1),
    ('related_person-relationship','DAUFOST','foster daughter',330,0,1),
    ('related_person-relationship','SONFOST','foster son',340,0,1),
    ('related_person-relationship','STPCHLD','step child',350,0,1),
    ('related_person-relationship','STPDAU','stepdaughter',360,0,1),
    ('related_person-relationship','STPSON','stepson',370,0,1),

    -- Siblings
    ('related_person-relationship','SIB','sibling',380,0,1),
    ('related_person-relationship','NSIB','natural sibling',390,0,1),
    ('related_person-relationship','BRO','brother',400,0,1),
    ('related_person-relationship','NBRO','natural brother',410,0,1),
    ('related_person-relationship','SIS','sister',420,0,1),
    ('related_person-relationship','NSIS','natural sister',430,0,1),
    ('related_person-relationship','HBRO','half-brother',440,0,1),
    ('related_person-relationship','HSIS','half-sister',450,0,1),
    ('related_person-relationship','HSIB','half-sibling',460,0,1),
    ('related_person-relationship','STPBRO','stepbrother',470,0,1),
    ('related_person-relationship','STPSIS','stepsister',480,0,1),
    ('related_person-relationship','STPSIB','step sibling',490,0,1),
    ('related_person-relationship','TWIN','twin',500,0,1),
    ('related_person-relationship','TWINBRO','twin brother',510,0,1),
    ('related_person-relationship','TWINSIS','twin sister',520,0,1),
    ('related_person-relationship','FTWIN','fraternal twin',530,0,1),
    ('related_person-relationship','FTWINBRO','fraternal twin brother',540,0,1),
    ('related_person-relationship','FTWINSIS','fraternal twin sister',550,0,1),
    ('related_person-relationship','ITWIN','identical twin',560,0,1),
    ('related_person-relationship','ITWINBRO','identical twin brother',570,0,1),
    ('related_person-relationship','ITWINSIS','identical twin sister',580,0,1),

    -- Grandparents
    ('related_person-relationship','GRPRN','grandparent',590,0,1),
    ('related_person-relationship','GRFTH','grandfather',600,0,1),
    ('related_person-relationship','GRMTH','grandmother',610,0,1),
    ('related_person-relationship','MGRPRN','maternal grandparent',620,0,1),
    ('related_person-relationship','MGRFTH','maternal grandfather',630,0,1),
    ('related_person-relationship','MGRMTH','maternal grandmother',640,0,1),
    ('related_person-relationship','PGRPRN','paternal grandparent',650,0,1),
    ('related_person-relationship','PGRFTH','paternal grandfather',660,0,1),
    ('related_person-relationship','PGRMTH','paternal grandmother',670,0,1),

    -- Great Grandparents
    ('related_person-relationship','GGRPRN','great grandparent',680,0,1),
    ('related_person-relationship','GGRFTH','great grandfather',690,0,1),
    ('related_person-relationship','GGRMTH','great grandmother',700,0,1),
    ('related_person-relationship','MGGRPRN','maternal great-grandparent',710,0,1),
    ('related_person-relationship','MGGRFTH','maternal great-grandfather',720,0,1),
    ('related_person-relationship','MGGRMTH','maternal great-grandmother',730,0,1),
    ('related_person-relationship','PGGRPRN','paternal great-grandparent',740,0,1),
    ('related_person-relationship','PGGRFTH','paternal great-grandfather',750,0,1),
    ('related_person-relationship','PGGRMTH','paternal great-grandmother',760,0,1),

    -- Grandchildren
    ('related_person-relationship','GRNDCHILD','grandchild',770,0,1),
    ('related_person-relationship','GRNDDAU','granddaughter',780,0,1),
    ('related_person-relationship','GRNDSON','grandson',790,0,1),

    -- Extended Family
    ('related_person-relationship','FAMMEMB','Family Member',800,0,1),
    ('related_person-relationship','EXT','extended family member',810,0,1),
    ('related_person-relationship','AUNT','aunt',820,0,1),
    ('related_person-relationship','MAUNT','maternal aunt',830,0,1),
    ('related_person-relationship','PAUNT','paternal aunt',840,0,1),
    ('related_person-relationship','UNCLE','uncle',850,0,1),
    ('related_person-relationship','MUNCLE','maternal uncle',860,0,1),
    ('related_person-relationship','PUNCLE','paternal uncle',870,0,1),
    ('related_person-relationship','COUSN','maternal cousin',880,0,1),
    ('related_person-relationship','MCOUSN','maternal cousin',890,0,1),
    ('related_person-relationship','PCOUSN','paternal cousin',900,0,1),
    ('related_person-relationship','NEPHEW','nephew',910,0,1),
    ('related_person-relationship','NIECE','niece',920,0,1),

    -- In-Laws
    ('related_person-relationship','INLAW','inlaw',930,0,1),
    ('related_person-relationship','PRNINLAW','parent in-law',940,0,1),
    ('related_person-relationship','FTHINLAW','father-in-law',950,0,1),
    ('related_person-relationship','MTHINLAW','mother-in-law',960,0,1),
    ('related_person-relationship','SIBINLAW','sibling in-law',970,0,1),
    ('related_person-relationship','BROINLAW','brother-in-law',980,0,1),
    ('related_person-relationship','SISINLAW','sister-in-law',990,0,1),
    ('related_person-relationship','DAUINLAW','daughter in-law',1000,0,1),
    ('related_person-relationship','SONINLAW','son in-law',1010,0,1),

    -- Legal/Guardian Relationships
    -- ('related_person-relationship','GUADLTM','guardian ad lidem',1030,0,1),
    -- ('related_person-relationship','SPOWATT','special power of attorney',1050,0,1),

    -- Other Relationships
    ('related_person-relationship','FRND','unrelated friend',1070,0,1),
    ('related_person-relationship','NBOR','neighbor',1080,0,1),
    ('related_person-relationship','ROOM','Roommate',1090,0,1),

    -- Self
    ('related_person-relationship','ONESELF','self',1100,0,1);

#Endif


#IfNotRow2D list_options list_id lists option_id related_person-role
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','related_person-role','Related Person Role',0, 1, 0);

INSERT INTO list_options
    (list_id,option_id,title,seq,is_default,activity)
VALUES
    ('related_person-role','ECON','Emergency Contact',10,0,1),
    ('related_person-role','NOK','Next of Kin',20,0,1),
    ('related_person-role','GUARD','Guardian',30,0,1),
    ('related_person-role','DEPEN','Dependent',40,0,1),
    ('related_person-role','CON','contact',50,0,1),
    ('related_person-role','EMP','Employee',60,0,1),
    ('related_person-role','GUAR','Guarantor',70,0,1),
    ('related_person-role','CAREGIVER','Caregiver',80,0,1),
    ('related_person-role','POWATT','Power of Attorney',90,0,1),
    ('related_person-role','DPOWATT','Durable Power of Attorney',100,0,1),
    ('related_person-role','HPOWATT','Healthcare Power of Attorney',110,0,1),
    ('related_person-role','BILL','Billing Contact',120,0,1),
    ('related_person-role','E','Employer',130,0,1),
    ('related_person-role','POLHOLD','Policy Holder',140,0,1),
    ('related_person-role','PAYEE','Payee',150,0,1),
    ('related_person-role','NOT','Notary Public',160,0,1),
    ('related_person-role','PROV','Healthcare Provider',170,0,1),
    ('related_person-role','WIT','Witness',180,0,1),
    ('related_person-role','O','Other',190,0,1),
    ('related_person-role','U','Unknown',200,0,1);
#EndIf

INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','telecom-systems','Telecom Systems',0, 1, 0);

INSERT INTO list_options
    (list_id,option_id,title,seq,is_default,activity)
VALUES
    ('telecom-systems','PHONE','phone',10,0,1),
    ('telecom-systems','FAX','fax',20,0,1),
    ('telecom-systems','EMAIL','email',30,0,1),
    ('telecom-systems','PAGER','pager',40,0,1),
    ('telecom-systems','URL','url',50,0,1),
    ('telecom-systems','SMS','sms',60,0,1),
    ('telecom-systems','OTHER','other',70,0,1);

INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value)
    VALUES ('lists','telecom-uses','Telecom Uses',0, 1, 0);

INSERT INTO list_options
    (list_id,option_id,title,seq,is_default,activity)
VALUES
    ('telecom-uses','HOME','home',10,0,1),
    ('telecom-uses','WORK','work',20,0,1),
    ('telecom-uses','TEMP','temp',30,0,1),
    ('telecom-uses','OLD','old',40,0,1),
    ('telecom-uses','MOBILE','mobile',50,0,1);

INSERT IGNORE INTO list_options
	(list_id, option_id, title, seq, is_default)
VALUES
	('lists', 'person_patient_link_method', 'Person-Patient Link Method', 1, 0);

INSERT INTO list_options
	(list_id, option_id, title, seq, is_default, option_value, notes)
VALUES
    ('person_patient_link_method', 'manual', 'Manually Linked by User', 10, 1, 0, 'User explicitly linked person to patient'),
    ('person_patient_link_method', 'auto_detected', 'Auto-Detected at Registration', 20, 0, 0, 'System detected match during patient registration'),
    ('person_patient_link_method', 'migrated', 'Migrated from Legacy System', 30, 0, 0, 'Link created during data migration'),
    ('person_patient_link_method', 'import', 'Imported from External System', 40, 0, 0, 'Link created during data import'),
    ('person_patient_link_method', 'merge', 'Merged Duplicate Records', 50, 0, 0, 'Link created when merging duplicate records')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    seq = VALUES(seq);


#IfRow2D layout_group_properties grp_form_id DEM grp_title Guardian
UPDATE layout_group_properties
SET grp_title = 'Related'
WHERE grp_title = 'Guardian'
  AND grp_form_id = 'DEM';

#Elseif
    #IfNotRow2D layout_group_properties grp_form_id DEM grp_title Related
    INSERT INTO layout_group_properties
        (grp_form_id, grp_group_id, grp_title, grp_mapping)
    VALUES
        ('DEM', @newgrp, 'Related','');
    #Endif
#Endif

#IfRow2D layout_options form_id DEM field_id guardiansname
	UPDATE layout_options
	SET title = 'Guardian Name'
		WHERE form_id = 'DEM'
  		AND field_id = 'guardiansname';
#Endif


#IfNotRow2D layout_options form_id DEM field_id related_persons

    #IfRow2D layout_options form_id DEM field_id guardianemail
    SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='guardianemail' AND form_id='DEM');
    SET @seq_add_to = (SELECT max(seq) FROM layout_options WHERE group_id = @group_id AND form_id='DEM');
    INSERT INTO `layout_options`
        (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`)
    VALUES
        ('DEM','related_persons',@group_id,'',@seq_add_to+10,56,1,0,0,'',4,4,'','[\"J\",\"SP\"]','Related Persons',0);
    #Elseif
    SET @group_id = (SELECT `group_id` FROM layout_options WHERE grp_title='related_persons' AND form_id='DEM');
    INSERT INTO `layout_options`
        (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`)
    VALUES
        ('DEM','related_persons',@group_id,'',1,56,1,0,0,'',4,4,'','[\"J\",\"SP\"]','Related Persons',0);
    #Endif

#Endif


#IfNotRow2D layout_options form_id DEM field_id additional_telecoms
    #IfRow2D layout_options form_id DEM field_id additional_addresses
    SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='additional_addresses' AND form_id='DEM');
    SET @max_seq = (SELECT max(seq) FROM layout_options WHERE group_id = @group_id AND form_id='DEM');
    UPDATE layout_options SET seq = @max_seq+19 WHERE form_id = 'DEM' AND field_id = 'additional_addresses';
    INSERT INTO `layout_options`
        (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`)
    VALUES
        ('DEM','additional_telecoms',@group_id,'',@max_seq+9,55,1,0,0,'',4,4,'','[\"J\",\"SP\"]','Additional Telecoms',0);
    #Endif

#Endif

CREATE INDEX idx_patient_name ON patient_data(lname, fname);
CREATE INDEX idx_patient_dob ON patient_data(DOB);



