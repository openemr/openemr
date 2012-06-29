CREATE TABLE IF NOT EXISTS `form_urinary_soap` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    urinary_complaints varchar(255),
    previous_cultures varchar(255),
    other TEXT,
    duration varchar(255),
    exam varchar(255),
    diagnosis varchar(255),
    plan varchar(255),
    PRIMARY KEY (id)
) ENGINE=InnoDB;
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinary_Complaints',
    title='Urinary Complaints';
INSERT IGNORE INTO list_options SET list_id='Urinary_Complaints',
    option_id='1',
    title='Frequency',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinary_Complaints',
    option_id='2',
    title='Urgency',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinary_Complaints',
    option_id='3',
    title='Incontinence',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinary_Complaints',
    option_id='4',
    title='Dysuria',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinary_Complaints',
    option_id='5',
    title='Other',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinary_Exam',
    title='Urinary Exam';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='1',
    title='Vulvar',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='2',
    title='Vaginal',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='3',
    title='Cervix',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='4',
    title='Uterus',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='5',
    title='Adnexae',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='6',
    title='Rectal',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Urinary_Exam',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinary_Diagnosis',
    title='Urinary Diagnosis';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='1',
    title='UTI',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='2',
    title='Hematuria',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='3',
    title='Chronic/Recurrent Cystitis',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='4',
    title='Duration',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='5',
    title='OAB (Overactive Bladder)',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Urinary_Diagnosis',
    option_id='6',
    title='Other',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Urinary_Plans',
    title='Urinary Plans';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='1',
    title='Antibiotics',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='2',
    title='Urine Cultire and Sensitivity',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='3',
    title='Cystoscopy and Cystometrogram',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='4',
    title='Anticholinergic',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='5',
    title='Behavior Modification',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Urinary_Plans',
    option_id='6',
    title='Other',
    seq='6';

