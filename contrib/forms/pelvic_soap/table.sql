CREATE TABLE IF NOT EXISTS `form_pelvic_soap` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    pelvic_complaints varchar(255),
    pelvic_exam varchar(255),
    pelvic_assessment varchar(255),
    pelvic_plan varchar(255),
    plan_discussion TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Pelvic_Complaints',
    title='Pelvic Complaints';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='1',
    title='Pressure',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='2',
    title='Something Down There',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='3',
    title='Bladder Changes',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='4',
    title='Bleeding',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='5',
    title='Pain',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Complaints',
    option_id='6',
    title='Other',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Pelvic_Exam',
    title='Pelvic Exam';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='1',
    title='Apical',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='2',
    title='Cystocele',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='3',
    title='Rectocele',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='4',
    title='POP Q',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='5',
    title='Urethra',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='6',
    title='Kegels',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Exam',
    option_id='7',
    title='Other',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Pelvic_Assessment',
    title='Pelvic Assessment';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='1',
    title='POP',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='2',
    title='Cystocele',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='3',
    title='Rectocele',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='4',
    title='Apical Prolapse',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='5',
    title='Hypermobile Urethra',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Assessment',
    option_id='6',
    title='Other',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='lists',
    option_id='Pelvic_Plan',
    title='Pelvic Plan';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='1',
    title='Anterior Repair Elevate',
    seq='1';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='2',
    title='Posterior Repair Elevate',
    seq='2';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='3',
    title='Robotic ASC',
    seq='3';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='4',
    title='Mini-arc',
    seq='4';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='5',
    title='Transobterator',
    seq='5';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='6',
    title='Transvaginal Tape (TVT)',
    seq='6';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='7',
    title='Sonogram',
    seq='7';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='8',
    title='Cystoscopy',
    seq='8';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='9',
    title='Cystometrogram',
    seq='9';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='10',
    title='Pessary',
    seq='10';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='11',
    title='Kegels',
    seq='11';
INSERT IGNORE INTO list_options SET list_id='Pelvic_Plan',
    option_id='12',
    title='Other',
    seq='12';

