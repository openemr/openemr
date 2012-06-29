CREATE TABLE IF NOT EXISTS `form_vaginitis_soap` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    vaginitis_complaints varchar(255),
    other TEXT,
    duration varchar(255),
    objective_exam varchar(255),
    vaginitis varchar(255),
    plan_medications varchar(255),
    plan_behavior_modification TEXT,
    plan_cultures varchar(255),
    plan_other TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Vaginitis_Complaints',
    title='Vaginitis Complaints';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='1',
    title='Itching (pruritus)',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='2',
    title='Discharge (leukorrhea)',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='3',
    title='Odor',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='4',
    title='Pain',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='6',
    title='Previous Cultures',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='7',
    title='Previous Prescriptions',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Complaints',
    option_id='8',
    title='Other',
    seq='8';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Vaginitis_Exam',
    title='Vaginitis Exam';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='1',
    title='Vulvar',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='2',
    title='Vaginal',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='3',
    title='Cervix',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='4',
    title='Uterus',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='5',
    title='Adnexae',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='6',
    title='Rectal',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Exam',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Vaginitis_Diagnosis',
    title='Vaginitis Diagnosis';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='1',
    title='Affirm',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='2',
    title='Yeast',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='3',
    title='Bacterial Vaginosis',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='4',
    title='Vulvar Vaginitis',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='5',
    title='Lichen Schlerosis',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='6',
    title='Atrophic Vaginitis',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Diagnosis',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Vaginitis_Medications',
    title='Vaginitis Medications';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='1',
    title='Estrace Cream',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='2',
    title='Flagyl',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='3',
    title='Terazol-3',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='4',
    title='Terazol-7',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='5',
    title='Diflucan',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='6',
    title='Lotrisone',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='7',
    title='Tindamax',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Medications',
    option_id='8',
    title='Other',
    seq='8';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Vaginitis_Cultures',
    title='Vaginitis Cultures';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='1',
    title='Affirm',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='2',
    title='GC/CT',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='3',
    title='Ureaplasma/Mycoplasma',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='4',
    title='Genital',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='5',
    title='Viral',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Vaginitis_Cultures',
    option_id='6',
    title='Other',
    seq='6';

