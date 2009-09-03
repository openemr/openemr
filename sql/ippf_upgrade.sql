DELETE FROM list_options WHERE list_id = 'ab_location';
INSERT INTO list_options VALUES ('ab_location','proc' ,'Procedure at this site'              , 1,0,0);
INSERT INTO list_options VALUES ('ab_location','ma'   ,'Followup procedure from this site'   , 2,0,0);
INSERT INTO list_options VALUES ('ab_location','part' ,'Followup procedure from partner site', 3,0,0);
INSERT INTO list_options VALUES ('ab_location','oth'  ,'Followup procedure from other site'  , 4,0,0);

DELETE FROM list_options WHERE list_id = 'lbfnames';
INSERT INTO list_options VALUES ('lbfnames','LBFgcac','IPPF GCAC',1,0,0);
INSERT INTO list_options VALUES ('lbfnames','LBFsrh' ,'IPPF SRH' ,2,0,0);

DELETE FROM layout_options WHERE form_id = 'LBFsrh';
INSERT INTO layout_options VALUES ('LBFsrh','usertext15' ,'1Gynecology'                ,'Menstrual History'             , 1,22,1, 0, 0,'genmenhist'  ,1,3,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','men_hist'   ,'1Gynecology'                ,'Recent Menstrual History'      , 2,21,1, 2, 0,'menhist'     ,1,3,'','','Recent Menstrual History');
INSERT INTO layout_options VALUES ('LBFsrh','men_compl'  ,'1Gynecology'                ,'Menstrual Complications'       , 3,21,1, 2, 0,'men_compl'   ,1,3,'','','Menstrual Complications');
INSERT INTO layout_options VALUES ('LBFsrh','pap_hist'   ,'1Gynecology'                ,'Pap Smear Recent History'      , 4,22,1, 0, 0,'pap_hist'    ,1,3,'','','Pap Smear Recent History');
INSERT INTO layout_options VALUES ('LBFsrh','gyn_exams'  ,'1Gynecology'                ,'Gynecological Tests'           , 5,23,1, 0, 0,'gyn_exams'   ,1,1,'','','Gynecological test results');
INSERT INTO layout_options VALUES ('LBFsrh','pr_status'  ,'2Obstetrics'                ,'Pregnancy Status Confirmed'    , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','','Pregnancy Status Confirmed');
INSERT INTO layout_options VALUES ('LBFsrh','gest_age_by','2Obstetrics'                ,'Gestational Age Confirmed By'  , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','','Gestational Age Confirmed By');
INSERT INTO layout_options VALUES ('LBFsrh','usertext12' ,'2Obstetrics'                ,'Blood Group'                   , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','usertext13' ,'2Obstetrics'                ,'RH Factor'                     , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','obs_exams'  ,'2Obstetrics'                ,'Obstetric Tests'               , 5,23,1, 0, 0,'obs_exams'   ,1,1,'','','Obstetric test results');
INSERT INTO layout_options VALUES ('LBFsrh','usertext16' ,'2Obstetrics'                ,'Obstetric History'             , 6,22,1, 0, 0,'genobshist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','pr_outcome' ,'2Obstetrics'                ,'Outcome of Last Pregnancy'     , 7,21,1, 2, 0,'pr_outcome'  ,1,3,'','','Outcome of Last Pregnancy');
INSERT INTO layout_options VALUES ('LBFsrh','pr_compl'   ,'2Obstetrics'                ,'Pregnancy Complications'       , 8,21,1, 2, 0,'pr_compl'    ,1,3,'','','Pregnancy Complications');
INSERT INTO layout_options VALUES ('LBFsrh','usertext17' ,'3Basic RH (female only)'    ,'Abortion Basic History'        , 1,22,1, 0, 0,'genabohist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','abo_exams'  ,'3Basic RH (female only)'    ,'Abortion Tests'                , 2,23,1, 0, 0,'abo_exams'   ,1,1,'','','Abortion test results');
INSERT INTO layout_options VALUES ('LBFsrh','usertext18' ,'4Basic RH (female and male)','HIV/AIDS Basic History'        , 1,21,1, 0, 0,'genhivhist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','hiv_exams'  ,'4Basic RH (female and male)','HIV/AIDS Tests'                , 2,23,1, 0, 0,'hiv_exams'   ,1,1,'','','HIV/AIDS test results');
INSERT INTO layout_options VALUES ('LBFsrh','usertext19' ,'4Basic RH (female and male)','ITS/ITR Basic History'         , 3,21,1, 0, 0,'genitshist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','its_exams'  ,'4Basic RH (female and male)','ITS/ITR Tests'                 , 4,23,1, 0, 0,'its_exams'   ,1,1,'','','ITS/ITR test results');
INSERT INTO layout_options VALUES ('LBFsrh','usertext20' ,'4Basic RH (female and male)','Fertility Basic History'       , 5,21,1, 0, 0,'genferhist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','fer_exams'  ,'4Basic RH (female and male)','Fertility Tests'               , 6,23,1, 0, 0,'fer_exams'   ,1,1,'','','Infertility/subfertility test results');
INSERT INTO layout_options VALUES ('LBFsrh','fer_causes' ,'4Basic RH (female and male)','Causes of Infertility'         , 7,21,1, 2, 0,'fer_causes'  ,1,3,'','','Causes of Infertility');
INSERT INTO layout_options VALUES ('LBFsrh','fer_treat'  ,'4Basic RH (female and male)','Infertility Treatment'         , 8,21,1, 2, 0,'fer_treat'   ,1,3,'','','Infertility Treatment');
INSERT INTO layout_options VALUES ('LBFsrh','usertext20' ,'4Basic RH (female and male)','Urology Basic History'         , 9,21,1, 0, 0,'genurohist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','uro_exams'  ,'4Basic RH (female and male)','Urology Tests'                 ,10,23,1, 0, 0,'uro_exams'   ,1,1,'','','Urology test results');
INSERT INTO layout_options VALUES ('LBFsrh','uro_disease','4Basic RH (female and male)','Male Genitourinary diseases'   ,11,21,1, 2, 0,'uro_disease' ,1,3,'','','Male Genitourinary diseases');

DELETE FROM layout_options WHERE form_id = 'GCA';
INSERT INTO layout_options VALUES ('GCA','reason'       ,'2Counseling'  ,'Reason for Termination'          , 1,21,1, 0, 0,'abreasons'   ,1,3,'','' ,'Reasons for Termination of Pregnancy');
INSERT INTO layout_options VALUES ('GCA','exp_p_i'      ,'2Counseling'  ,'Explanation of Procedures/Issues', 2,21,1, 2, 0,'exp_p_i'     ,1,3,'','' ,'Explanation of Procedures and Issues');
INSERT INTO layout_options VALUES ('GCA','exp_pop'      ,'2Counseling'  ,'Explanation of Pregnancy Options', 3,21,1, 2, 0,'exp_pop'     ,1,3,'','' ,'Explanation of Pregnancy Options');
INSERT INTO layout_options VALUES ('GCA','ab_contraind' ,'2Counseling'  ,'Contraindications'               , 4,21,1, 2, 0,'ab_contraind',1,3,'','' ,'Contraindications');
INSERT INTO layout_options VALUES ('GCA','screening'    ,'2Counseling'  ,'Screening for SRHR Concerns'     , 5,21,1, 2, 0,'screening'   ,1,3,'','' ,'Screening for SRHR Concerns');
INSERT INTO layout_options VALUES ('GCA','in_ab_proc'   ,'3Admission'   ,'Induced Abortion Procedure'      , 2, 1,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Abortion Procedure Accepted or Performed');
INSERT INTO layout_options VALUES ('GCA','ab_types'     ,'3Admission'   ,'Abortion Types'                  , 3,21,1, 2, 0,'ab_types'    ,1,3,'','' ,'Abortion Types');
INSERT INTO layout_options VALUES ('GCA','pr_status'    ,'4Preparatory' ,'Pregnancy Status Confirmed'      , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','' ,'Pregnancy Status Confirmed');
INSERT INTO layout_options VALUES ('GCA','gest_age_by'  ,'4Preparatory' ,'Gestational Age Confirmed By'    , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','' ,'Gestational Age Confirmed By');
INSERT INTO layout_options VALUES ('GCA','usertext12'   ,'4Preparatory' ,'Blood Group'                     , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO layout_options VALUES ('GCA','usertext13'   ,'4Preparatory' ,'RH Factor'                       , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO layout_options VALUES ('GCA','prep_procs'   ,'4Preparatory' ,'Preparation Procedures'          , 6,21,1, 0, 0,'prep_procs'  ,1,3,'','' ,'Preparation Procedures');
INSERT INTO layout_options VALUES ('GCA','pre_op'       ,'5Intervention','Pre-Surgery Procedures'          , 1,21,1, 2, 0,'pre_op'      ,1,3,'','' ,'Pre-Surgery Procedures');
INSERT INTO layout_options VALUES ('GCA','anesthesia'   ,'5Intervention','Anesthesia'                      , 2, 1,1, 0, 0,'anesthesia'  ,1,3,'','' ,'Type of Anesthesia Used');
INSERT INTO layout_options VALUES ('GCA','side_eff'     ,'5Intervention','Immediate Side Effects'          , 3,21,1, 2, 0,'side_eff'    ,1,3,'','' ,'Immediate Side Effects (observed at intervention');
INSERT INTO layout_options VALUES ('GCA','post_op'      ,'5Intervention','Post-Surgery Procedures'         , 5,21,1, 2, 0,'post_op'     ,1,3,'','' ,'Post-Surgery Procedures');
INSERT INTO layout_options VALUES ('GCA','qc_ind'       ,'6Followup'    ,'Quality of Care Indicators'      , 1,21,1, 0, 0,'qc_ind'      ,1,3,'','' ,'Quality of Care Indicators');

DELETE FROM layout_options WHERE form_id = 'LBFgcac';
INSERT INTO layout_options VALUES ('LBFgcac','client_status','1Basic Information','Client Status'               , 1, 1,2, 0, 0,'clientstatus',1,1,'','' ,'Client Status');
INSERT INTO layout_options VALUES ('LBFgcac','ab_location'  ,'1Basic Information','Type of Visit'               , 2, 1,2, 0, 0,'ab_location' ,1,1,'','' ,'Nature of this visit');
INSERT INTO layout_options VALUES ('LBFgcac','in_ab_proc'   ,'1Basic Information','Associated Induced Procedure', 3, 1,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Applies regardless of when or where done');
INSERT INTO layout_options VALUES ('LBFgcac','complications','2Complications','Complications'                   , 1,21,1, 2, 0,'complication',1,3,'','' ,'Post-Abortion Complications');
INSERT INTO layout_options VALUES ('LBFgcac','main_compl'   ,'2Complications','Main Complication'               , 2, 1,1, 2, 0,'complication',1,3,'','' ,'Primary Complication');
INSERT INTO layout_options VALUES ('LBFgcac','contrameth'   ,'3Contraception','New Method'                      , 1,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'New method adopted');

#IfNotRow list_options list_id occupations
INSERT INTO list_options VALUES ('occupations','oth','Other', 1,0,0);
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'occupations';
INSERT INTO list_options VALUES ('lists','occupations','Occupations',61,0,0);
#endIf

UPDATE layout_options SET data_type = 26, list_id = 'occupations'  WHERE form_id = 'DEM' AND field_id = 'occupation';
UPDATE layout_options SET data_type = 26, title = 'Religion'       WHERE form_id = 'DEM' AND field_id = 'userlist5';
UPDATE layout_options SET data_type = 26, title = 'Monthly Income' WHERE form_id = 'DEM' AND field_id = 'userlist3';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'ethnoracial';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'language';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'status';
UPDATE layout_options SET uor = 0 WHERE form_id = 'DEM' AND field_id = 'providerID';
UPDATE layout_options SET seq = 3 WHERE form_id = 'REF' AND field_id = 'refer_external' AND seq = 5;
UPDATE layout_options SET seq = 4 WHERE form_id = 'REF' AND field_id = 'refer_to'       AND seq = 3;
UPDATE layout_options SET seq = 5 WHERE form_id = 'REF' AND field_id = 'body'           AND seq = 4;

