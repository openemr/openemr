DELETE FROM list_options WHERE list_id = 'contrameth';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','con','Condom (Male or Female)'         , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','dia','Diaphragm'                       , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','ec' ,'Emergency Contraception'         , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','fab','Fertility Awareness Based'       , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','fc' ,'Foam & Condom'                   , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','pat','Hormonal Patch'                  , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','imp','Implant'                         , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','inj','Injectable'                      , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','iud','IUCD'                            ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','or' ,'Oral'                            ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','cap','Pessary/Cervicap Cap'            ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','sp' ,'Spermicides'                     ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','vsc','Voluntary Surgical Contraception',14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('contrameth','no' ,'None'                            ,16,0,0);

DELETE FROM list_options WHERE list_id = 'mcreason';
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'sef','Side Effects of Current Method'   , 1,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'pop','Partner Opposes'                  , 2,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'eco','Economic (cost)'                  , 3,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'com','Method Too Complicated'           , 4,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'med','Medical/Health Condition'         , 5,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'men','Menopause'                        , 6,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'rel','Religious'                        , 7,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'mov','Personal - Moved Away'            , 8,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'prv','Personal - Privacy'               , 9,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oop','Personal - Social/Family Pressure',10,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'con','Personal - Lacks Confidence'      ,11,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'prg','Wants to Become Pregnant'         ,12,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oth','Other'                            ,13,0,0);

INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'eco','Economic (cost)'                         , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'mda','Medical - Allergy'                       , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'mdb','Medical - Breast Feeding'                , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'mdc','Medical - Contraindication'              , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'men','Medical - Menopause'                     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'sef','Medical - Side Effects of Current Method', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'com','Method Too Complicated'                  , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'fop','Personal - Family Pressure/Advice'       , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'con','Personal - Fear of Infertility'          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oth','Personal - Other Reason'                 , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'pop','Personal - Partner Opposes'              , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'rel','Personal - Religious'                    , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'pse','Personal - Side Effects Concern'         , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oop','Personal - Social Pressure/Friend Advice', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'prg','Planning Pregnancy'                      , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'stc','Sterilization of Client'                 , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'stp','Sterilization of Partner'                , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'nav','Unavailable at Clinic'                   , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'uns','Unspecified - No Reason Provided'        , 1,0,0);

DELETE FROM list_options WHERE list_id = 'abreasons';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'lif','Danger to life of pregnant woman'                                          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'phy','Grave injury to physical health of pregnant woman'                         , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'men','Grave injury to mental health of pregnant woman'                           , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'rap','Pregnancy caused by rape'                                                  , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'abn','Substantial risk that child would be severely handicapped by abnormalities', 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'con','Failure of contraceptive device or method'                                 , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'env','Risk to health of woman in current or forseeable environment'              , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abreasons' ,'oth','Other (specify)'                                                           , 8,0,0);

DELETE FROM list_options WHERE list_id = 'flowtype';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('flowtype'  ,'lit','Light'   , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('flowtype'  ,'mod','Moderate', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('flowtype'  ,'hvy','Heavy'   , 3,0,0);

DELETE FROM list_options WHERE list_id = 'lists' AND seq > 50;
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists' ,'contrameth','Contraceptive Methods'               ,51,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists' ,'mcreason'  ,'Reason for Method Change/Termination',52,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists' ,'abreasons' ,'Reasons for Abortion'                ,58,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists' ,'flowtype'  ,'Flow Types'                          ,59,0,0);

DELETE FROM codes WHERE code_type = '11';
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '110000000', '', 'FAMILY PLANNING METHODS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111100000', '', 'CONTRACEPTIVES -  ORAL CONTRACEPTIVES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111100119', '', 'Contraceptives - Oral Contraceptives - OC - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111100999', '', 'Contraceptives - Oral Contraceptives - OC - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111101000', '', 'CONTRACEPTIVES -  COMBINED & PROGESTOGEN-ONLY ORAL CONTRACEPTIVES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111101110', '', 'Contraceptives - Oral Contraceptives - COC & POC - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111101111', '', 'Contraceptives - Oral Contraceptives - COC & POC - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111101999', '', 'Contraceptives - Oral Contraceptives - COC & POC - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111110000', '', 'CONTRACEPTIVES -  INJECTABLE CONTRACEPTIVES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111110119', '', 'Contraceptives - Injectable Contraceptives - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111110999', '', 'Contraceptives - Injectable Contraceptives - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111111000', '', 'CONTRACEPTIVES -  COMBINED INJECTABLE CONTRACEPTIVES - CIC' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111111110', '', 'Contraceptives - Combined Injectable Contraceptives (1 month) -  Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111111111', '', 'Contraceptives - Combined Injectable Contraceptives (1 month) - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111111999', '', 'Contraceptives - Combined Injectable Contraceptives (1 month) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111112000', '', 'CONTRACEPTIVES -  PROGESTOGEN ONLY INJECTABLES (2 MONTHS)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111112110', '', 'Contraceptives - Progestogen Only Injectables (2 months) - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111112111', '', 'Contraceptives - Progestogen Only Injectables (2 months) - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111112999', '', 'Contraceptives - Progestogen Only Injectables (2 months) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111113000', '', 'CONTRACEPTIVES -  PROGESTOGEN ONLY INJECTABLES (3 MONTHS)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111113110', '', 'Contraceptives - Progestogen Only Injectables (3 months) - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111113111', '', 'Contraceptives - Progestogen Only Injectables (3 months) - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111113999', '', 'Contraceptives - Progestogen Only Injectables (3 months) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111120000', '', 'CONTRACEPTIVES -  SUBDERMAL IMPLANTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111120112', '', 'Contraceptives - Subdermal Implants - Removal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111120119', '', 'Contraceptives - Subdermal Implants - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111120999', '', 'Contraceptives - Subdermal Implants - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111122000', '', 'CONTRACEPTIVES -  SUBDERMAL IMPLANTS 6 rods' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111122110', '', 'Contraceptives - Subdermal implants 6 rods - Initial Consultation/Insertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111122111', '', 'Contraceptives - Subdermal implants 6 rods - Follow up/Reinsertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111122999', '', 'Contraceptives - Subdermal implants 6 rods - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111123000', '', 'CONTRACEPTIVES -  SUBDERMAL IMPLANTS 2 rods' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111123110', '', 'Contraceptives - Subdermal implants 2 rods - Initial Consultation/Insertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111123111', '', 'Contraceptives - Subdermal implants 2 rods - Follow up/Reinsertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111123999', '', 'Contraceptives - Subdermal implants 2 rods - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111124000', '', 'CONTRACEPTIVES -  SUBDERMAL IMPLANTS 1 rods' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111124110', '', 'Contraceptives - Subdermal implants 1 rod - Initial Consultation/Insertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111124111', '', 'Contraceptives - Subdermal implants 1 rod - Follow up/Reinsertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111124999', '', 'Contraceptives - Subdermal implants 1 rod - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111130000', '', 'CONTRACEPTIVES -  OTHER HORMONAL METHODS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111130999', '', 'Contraceptives - Other hormonal methods - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111132000', '', 'CONTRACEPTIVES - OTHER -  TRANSDERMAL PATCH (1 month)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111132110', '', 'Contraceptives - Other methods - Transdermal Patch (1 month) - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111132111', '', 'Contraceptives - Other methods - Transdermal Patch (1 month) - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111132119', '', 'Contraceptives - Other methods - Transdermal Patch (1 month) - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111132999', '', 'Contraceptives - Other methods - Transdermal Patch (1 month) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111133000', '', 'CONTRACEPTIVES - OTHER -  VAGINAL RING (1 month)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111133110', '', 'Contraceptives - Other methods - Vaginal Ring (1 month) - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111133111', '', 'Contraceptives - Other methods - Vaginal Ring (1 month) - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111133119', '', 'Contraceptives - Other methods - Vaginal Ring (1 month) - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '111133999', '', 'Contraceptives - Other methods - Vaginal Ring (1 month) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112140000', '', 'CONTRACEPTIVES -  CONDOMS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112140999', '', 'Contraceptives - Condoms (Male and Female) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112141000', '', 'CONTRACEPTIVES -  MALE CONDOMS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112141110', '', 'Contraceptives - Condoms - Male Condom - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112141111', '', 'Contraceptives - Condoms - Male Condom - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112141119', '', 'Contraceptives - Condoms - Male Condom - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112141999', '', 'Contraceptives - Condoms - Male Condom - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112142000', '', 'CONTRACEPTIVES -  FEMALE CONDOMS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112142110', '', 'Contraceptives - Condoms - Female Condom - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112142111', '', 'Contraceptives - Condoms - Female Condom - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112142119', '', 'Contraceptives - Condoms - Female Condom - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112142999', '', 'Contraceptives - Condoms - Female Condom - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112150000', '', 'CONTRACEPTIVES -  TYPES OF DIAPHRAGMS / CERVICAL CAPS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112150119', '', 'Contraceptives - Diaphragm / Cervical Cap - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112150999', '', 'Contraceptives - Diaphragm / Cervical Cap - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112151000', '', 'CONTRACEPTIVES -  DIAPHRAGMS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112151110', '', 'Contraceptives - Diaphragm - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112151111', '', 'Contraceptives - Diaphragm - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112151999', '', 'Contraceptives - Diaphragm - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112152000', '', 'CONTRACEPTIVES -  CERVICAL CAPS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112152010', '', 'Contraceptives - Cervical Cap - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112152011', '', 'Contraceptives - Cervical Cap - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112152999', '', 'Contraceptives - Cervical Cap - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112160000', '', 'CONTRACEPTIVES -  SPERMICIDES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112160119', '', 'Contraceptives - Spermicides - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112160999', '', 'Contraceptives - Spermicides - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112161000', '', 'CONTRACEPTIVES - SPERMICIDES -  FOAM TABS/TUBE/SUPPOSITIORIES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112161110', '', 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112161111', '', 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112161999', '', 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112162000', '', 'CONTRACEPTIVES - SPERMICIDES -  FOAM TAB/STRIPS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112162110', '', 'Contraceptives - Spermicides - Foam Tabs/Strip - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112162111', '', 'Contraceptives - Spermicides - Foam Tabs/Strip - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112162999', '', 'Contraceptives - Spermicides - Foam Tabs/Strip - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112163000', '', 'CONTRACEPTIVES - SPERMICIDES -  FOAM CANS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112163110', '', 'Contraceptives - Spermicides - Foam Cans - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112163111', '', 'Contraceptives - Spermicides - Foam Cans - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112163999', '', 'Contraceptives - Spermicides - Foam Cans - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112164000', '', 'CONTRACEPTIVES - SPERMICIDES -  CREAM & JELLY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112164110', '', 'Contraceptives - Spermicides - Cream & Jelly - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112164111', '', 'Contraceptives - Spermicides - Cream & Jelly - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112164999', '', 'Contraceptives - Spermicides - Cream & Jelly - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112165000', '', 'CONTRACEPTIVES - SPERMICIDES -  PESSARIES / C-FILM' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112165110', '', 'Contraceptives - Spermicides - Pessaries / C-film - Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112165111', '', 'Contraceptives - Spermicides - Pessaries / C-film - Follow up/Resupply' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '112165999', '', 'Contraceptives - Spermicides - Pessaries / C-film - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113170000', '', 'CONTRACEPTIVES -  IUD' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113170112', '', 'Contraceptives - IUD - Removal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113170119', '', 'Contraceptives - IUD - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113170999', '', 'Contraceptives - IUD - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113171000', '', 'CONTRACEPTIVES - IUD (5 years)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113171110', '', 'Contraceptives - IUD (5 years) - Initial Consultation/Insertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113171111', '', 'Contraceptives - IUD (5 years) - Follow up/Reinsertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113171999', '', 'Contraceptives - IUD (5 years) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113172000', '', 'CONTRACEPTIVES - IUD (10 years)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113172110', '', 'Contraceptives - IUD (10 years) - Initial Consultation/Insertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113172111', '', 'Contraceptives - IUD (10 years) - Follow up/Reinsertion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '113172999', '', 'Contraceptives - IUD (10 years) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '120180000', '', 'CONTRACEPTION -  VOLUNTARY SURGICAL CONTRACEPTION (VSC)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '120180999', '', 'Contraception - Voluntary Surgical Contraception (VSC) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181000', '', 'CONTRACEPTION -  FEMALE VOLUNTARY SURGICAL CONTRACEPTION (FVSC)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181112', '', 'Contraception Surgical - Female VSC - Reversal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181119', '', 'Contraception Surgical - Female VSC - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181211', '', 'Contraception Surgical - Female VSC - Minilaparatomy - Follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181213', '', 'Contraception Surgical - Female VSC - Minilaparatomy - Contraceptive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181311', '', 'Contraception Surgical - Female VSC - Laparoscopy - Follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181313', '', 'Contraception Surgical - Female VSC - Laparoscopy - Contraceptive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181411', '', 'Contraception Surgical - Female VSC - Laparotomy - Follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181413', '', 'Contraception Surgical - Female VSC - Laparotomy - Contraceptive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '121181999', '', 'Contraception Surgical - Female VSC - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182000', '', 'CONTRACEPTION -  MALE VOLUNTARY SURGICAL CONTRACEPTION (MVSC)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182112', '', 'Contraception Surgical - Male VSC - Reversal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182119', '', 'Contraception Surgical - Male VSC - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182211', '', 'Contraception Surgical - Male VSC - Incisional vasectomy - Follow up (Sperm count)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182213', '', 'Contraception Surgical - Male VSC - Incisional vasectomy - Contraceptive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182311', '', 'Contraception Surgical - Male VSC - No-scalpel Vasectomy - Follow up  (Sperm count)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182313', '', 'Contraception Surgical - Male VSC - No-scalpel Vasectomy - Contraceptive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '122182999', '', 'Contraception Surgical - Male VSC - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '130190000', '', 'CONTRACEPTION -  AWARENESS-BASED METHODS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '130190999', '', 'Contraception -  Awareness-Based Methods - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191000', '', 'CONTRACEPTION -  FERTILITY AWARENESS-BASED METHODS (FABM)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191119', '', 'Contraception FAB Methods - Method Specific Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191210', '', 'Contraception FAB Methods - Cervical Mucous Method (CMM) - Initial Consultation/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191211', '', 'Contraception FAB Methods - Cervical Mucous Method (CMM) - Follow up/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191310', '', 'Contraception FAB Methods - Calendar Based Method (CBM) - Initial Consultation/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191311', '', 'Contraception FAB Methods - Calendar Based Method (CBM) - Follow up/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191410', '', 'Contraception FAB Methods - Sympto-thermal method - Initial Consultation/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191411', '', 'Contraception FAB Methods - Sympto-thermal method - Follow up/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191510', '', 'Contraception FAB Methods - Standard days method - Initial Consultation/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191511', '', 'Contraception FAB Methods - Standard days method - Follow up/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191610', '', 'Contraception FAB Methods - Basal Body Temperature (BBT) - Initial Consultation/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191611', '', 'Contraception FAB Methods - Basal Body Temperature (BBT) - Follow up/Training' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '131191999', '', 'Contraception - FAB Methods - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '141200000', '', 'FAMILY PLANNING GENERAL COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '141200118', '', 'Contraception - FP General Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '141200218', '', 'Contraception - FP General Counselling - Combined Counselling (FP - HIV/AIDS incl. Dual protection' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '141200999', '', 'Contraception - FP General Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145210000', '', 'EMERGENCY CONTRACEPTION SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145210999', '', 'Emergency Contraception Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145211000', '', 'EC - COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145211119', '', 'EC - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145211999', '', 'EC - Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212000', '', 'EC - THERAPEUTIC' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212110', '', 'EC - Combined Oral Contraceptives - Yuzpe - Contraceptive Supply (Treatment)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212111', '', 'EC - Combined Oral Contraceptives - Yuzpe - Follow Up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212210', '', 'EC Progestogen Only Pills - Contraceptive Supply (Treatment)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212211', '', 'EC Progestogen Only Pills - Follow Up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212310', '', 'EC Dedicated Product - Contraceptive Supply (Treatment)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212311', '', 'EC Dedicated Product - Follow Up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212410', '', 'EC Copper releasing IUD - DIU Insertion (Treatment)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212411', '', 'EC Copper releasing IUD - Follow Up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '145212999', '', 'EC - Therapeutic - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '250000000', '', 'SRH (NON FAMILY PLANNING) SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252220000', '', 'ABORTION SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252220999', '', 'Abortion Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252221000', '', 'ABORTION / PRE ABORTION COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252221129', '', 'Abortion / Pre Abortion Counselling - Pregnancy options Counseling - Including Family Planning' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252221229', '', 'Abortion / Pre Abortion Counselling - Counselling on HIV Testing' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252221329', '', 'Abortion / Pre Abortion Counselling  Harm Reduction Initial Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252221999', '', 'Abortion / Pre Abortion  Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222000', '', 'ABORTION / PRE-ABORTION DIAGNOSTICS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222121', '', 'Abortion Diagnosis - Exclusion of Anaemia (Haemoglobin and/or Hematocrit tests)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222221', '', 'Abortion Diagnosis - Tests for ABO and Rhesus (Rh) blood groups typing' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222321', '', 'Abortion Diagnosis - Exclusion of ectopic pregnancy (through ultrasound)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222421', '', 'Abortion Diagnosis - Cervical cytology (Pap test or visual acid test)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222521', '', 'Abortion Diagnosis - HIV testing' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252222999', '', 'Abortion Diagnosis - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252223000', '', 'ABORTION / INDUCED - SURGICAL' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252223123', '', 'Abortion Induced (Surgical) - Dilatation And Curettage (D&C)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252223223', '', 'Abortion Induced (Surgical) - Dilatation And Evacuation (D&E) (2nd trimester of pregnancy)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252223323', '', 'Abortion Induced (Surgical) - Vacuum Aspiration (Manual or Electrical)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252223999', '', 'Abortion Induced (Surgical) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252224000', '', 'ABORTION (MEDICAL)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252224122', '', 'Abortion Induced (Medical) - Drug induced (combination of mifepristone and misopristol))' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252224222', '', 'Abortion Induced (Medical) - Drug induced (Misoprostol Only)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252224999', '', 'Abortion Induced (Medical) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252225000', '', 'ABORTION / INCOMPLETE ABORTION  TREATMENT (SURGICAL/MEDICAL)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252225123', '', 'Abortion (Incomplete Abortion) - Surgical treatment / D&C or D&E' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252225223', '', 'Abortion (Incomplete Abortion) - Surgical treatment / Vacuum aspiration' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252225722', '', 'Abortion (Incomplete Abortion) - Medical treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252225999', '', 'Abortion (Incomplete Abortion) - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252226000', '', 'ABORTION / POST ABORTION FOLLOW UP' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252226120', '', 'Abortion - Post - Follow-up incl. Uterine Involution Monitoring & Bimanual Pelvic Exam.' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252226999', '', 'Abortion - Post Abortion Follow-up - OTHER (including treatment of complications)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252227000', '', 'ABORTION / POST ABORTION COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252227129', '', 'Abortion Counselling - Post Abortion Counseling - Including Family Planning' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252227229', '', 'Abortion Counselling  Harm Reduction Follow-up Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '252227999', '', 'Abortion Counselling - Post Abortion Counseling and family planning counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253230000', '', 'HIV and AIDS SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253230999', '', 'HIV and AIDS Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231000', '', 'HIV and AIDS TREATMENT' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231122', '', 'HIV and AIDS - Treatment- Anti Retro Viral (ARV)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231222', '', 'HIV and AIDS - Treatment - Opportunistic Infection (OI)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231322', '', 'HIV and AIDS - Treatment - Post Exposure Prophylaxis (PEP)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231422', '', 'HIV and AIDS - Treatment - Psycho-Social Support' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231522', '', 'HIV and AIDS - Treatment - Home Care' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253231999', '', 'HIV and AIDS - Treatment - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232000', '', 'HIV and AIDS DIAGNOSTIC LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232121', '', 'HIV and AIDS Diagnostic Lab Tests - ELISA (Blood) Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232221', '', 'HIV and AIDS Diagnostic Lab Tests - Western Blot (WB) Assay' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232321', '', 'HIV and AIDS Diagnostic Lab Tests - Indirect Immunofluorescence Assay (IFA)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232421', '', 'HIV and AIDS Diagnostic Lab Tests - Rapid Test (Murex-SUDS)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232521', '', 'HIV and AIDS Diagnostic Lab  tests - Urine Test for HIV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253232999', '', 'HIV and AIDS Lab Tests - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253233000', '', 'HIV and AIDS STAGING AND MONITORING TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253233121', '', 'HIV and AIDS Other Lab Tests - Urine Test for HIV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253233221', '', 'HIV and AIDS Staging and monitoring Tests - Assessment of Immunologic Function (Viral Load)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253233321', '', 'HIV and AIDS Staging and monitoring Tests - Assessment of Immunologic Function (CD4 count)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253233999', '', 'HIV and AIDS Staging and monitoring Tests - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253234000', '', 'HIV and AIDS PREVENTION COUNSELING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253234129', '', 'HIV and AIDS Prevention Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253234999', '', 'HIV and AIDS Prevention Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235000', '', 'HIV and AIDS PRE/POST TEST COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235129', '', 'HIV and AIDS Counselling - PRE Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235229', '', 'HIV and AIDS Counselling - POST Test (Positive) - Clients Only' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235329', '', 'HIV and AIDS Counseling - POST Test (Negative)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235429', '', 'HIV and AIDS Counseling - POST Test (Positive) - Sexual Partners' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235529', '', 'HIV and AIDS Counselling - POST Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '253235999', '', 'HIV and AIDS Counseling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254240000', '', 'STI/RTI SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254240999', '', 'STI/RTI Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254241000', '', 'STI/RTI PREVENTION / POST TEST COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254241129', '', 'STI/RTI Counseling - Prevention Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254241229', '', 'STI/RTI Counseling - POST Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254241999', '', 'STI/RTI Prevention / Post Test Counseling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254242000', '', 'STI/RTI CONSULTATION' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254242120', '', 'STI/RTI Consultation - Follow Up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254242999', '', 'STI/RTI Consultation - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243000', '', 'STI/RTI LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243121', '', 'STI/RTI Test - Bacterial Vaginosis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243221', '', 'STI/RTI Test - Candidiasis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243321', '', 'STI/RTI Test - Chancroid' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243421', '', 'STI/RTI Test - Chlamydia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243521', '', 'STI/RTI Test - Gonorrhea' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254243999', '', 'STI/RTI Test - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244000', '', 'STI/RTI LAB TESTS (CONTINUED)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244121', '', 'STI/RTI Test - Herpes Simplex (HSV)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244221', '', 'STI/RTI Test - Human Papillomavirus (HPV)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244321', '', 'STI/RTI Test - Syphilis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244421', '', 'STI/RTI Test - Trichomoniasis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244521', '', 'STI/RTI Test - Hepatitis A and B' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244621', '', 'STI/RTI Test - Hepatitis A' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244721', '', 'STI/RTI Test - Hepatitis B' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254244999', '', 'STI/RTI Test - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245000', '', 'STI/RTI TREATMENT (including prophylactics)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245122', '', 'STI/RTI Treatment - based on syndromic approach' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245222', '', 'STI/RTI Treatment - Etiological diagnosis with clinical treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245322', '', 'STI/RTI Treatment - Hepatitis A vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245422', '', 'STI/RTI Treatment - Hepatitis B vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245522', '', 'STI/RTI Treatment - HPV vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254245999', '', 'STI/RTI Treatment - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246000', '', 'STI/RTI TREATMENT 1' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246122', '', 'STI Treatment for Bacterial Vaginosis based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246222', '', 'STI Treatment for Candidiasis based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246322', '', 'STI Treatment for Chancroid based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246422', '', 'STI Treatment for Chlamydia based on positive lab tes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254246522', '', 'STI Treatment for Gonorrhea based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247000', '', 'STI/RTI TREATMENT 2' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247122', '', 'STI Treatment for Herpes Simplex (HSV) based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247222', '', 'STI Treatment for Human Papillomavirus (HPV) based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247322', '', 'STI Treatment for Syphilis based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247422', '', 'STI Treatment for Trichomoniasis based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247522', '', 'STI Treatment for Hepatitis A based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247622', '', 'STI Treatment for Hepatitis B based on positive lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '254247999', '', 'STI/RTI Treatment  based on laboratory diagnostic tests -OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255250000', '', 'GYNECOLOGICAL SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255250999', '', 'Gynecological Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251000', '', 'GYNECOLOGICAL BIOPSY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251123', '', 'Gynecological Biopsy - Conization' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251223', '', 'Gynecological Biopsy - Needle Biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251323', '', 'Gynecological Biopsy - Aspiration Biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251423', '', 'Gynecological Biopsy - Dilatation & Curretage (D&C)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255251999', '', 'Gynecological Biopsy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252000', '', 'GYNECOLOGICAL ENDOSCOPY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252123', '', 'Gynecological Endoscopy - Colposcopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252223', '', 'Gynecological Endoscopy - Laparoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252323', '', 'Gynecological Endoscopy - Hysteroscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252423', '', 'Gynecological Endoscopy - Culdoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252523', '', 'Gynecological Endoscopy - Hysteretomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252623', '', 'Gynecological Endoscopy - Ovariectomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252723', '', 'Gynecological Endoscopy - Mastectomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252823', '', 'Gynecological Endoscopy - Lumpectomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255252999', '', 'Gynecological Endoscopy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253000', '', 'GYNECOLOGICAL DIAGNOSTIC IMAGING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253121', '', 'Gynecological Diagnostic Imaging - Radiography - Hysterosalpingography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253221', '', 'Gynecological Diagnostic Imaging - Radiography - Mammography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253321', '', 'Gynecological Diagnostic Imaging - Ultrasonography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253421', '', 'Gynecological Diagnostic Imaging - Tomography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253521', '', 'Gynecological Diagnostic Imaging - Dexa, Bone Density Scan' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255253999', '', 'Gynecological Diagnostic Imaging - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254000', '', 'GYNECOLOGICAL EXAM DIAGNOSIS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254121', '', 'Gynecological Exam - Manual Pelvic Exam (includes Palpation)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254221', '', 'Gynecological Exam - Manual Breast Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254321', '', 'Gynecological Exam - Cervical cancer screening (Pap smear)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254421', '', 'Gynecological Exam - Consultation without pelvic exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254521', '', 'Gynecological Exam  Cervical cancer screening  Visual Inspection (VIA or VILI)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254621', '', 'Gynecological Exam  Cervical cancer screening - Liquid-based cytology (sampling procedure)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254721', '', 'Gynecological Exam  Cervical cancer screening - HPV DNA test (sampling procedure)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255254999', '', 'Gynecological Exam - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255255000', '', 'GYNECOLOGICAL CYTOLOGIC TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255255121', '', 'Gynecological Lab Test - Cytology Analysis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255255221', '', 'Gynecological Lab Test - Cytology Analysis - Liquid-based cytology' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255255321', '', 'Gynecological Lab Test -Cervical cancer screening - HPV DNA test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255255999', '', 'Gynecological Lab Test - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256000', '', 'GYNECOLOGICAL THERAPIES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256122', '', 'Gynecological Therapies - Menopause Consultations, Hormonal Replacement Therapy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256222', '', 'Gynecological Therapies - Menstrual regulation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256322', '', 'Gynecological Therapies - Female Genital Mutilation Treatment of Complications' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256422', '', 'Gynecological Therapies  Treatment of erratic menstruation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255256999', '', 'Gynecological Therapies - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255257000', '', 'GYNECOLOGICAL SURGERIES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255257123', '', 'Gynecological Surgeries - Cryosurgery - Cervical' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255257223', '', 'Gynecological Surgeries - Cauterization (Cervical / Vaginal)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255257323', '', 'Gynecological Surgeries - Female Genital Mutilation Reconstructive Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255257999', '', 'Gynecological Surgeries - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258000', '', 'GYNECOLOGICAL COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258129', '', 'Gynecological Counselling - Menopause Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258229', '', 'Gynecological Counselling - Pap Smear - Pre-test counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258329', '', 'Gynecological Counselling - Pap Smear, Abnormal Results (post test follow-up)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258429', '', 'Gynecological Counselling - Breast Exam Results, Mammography/Biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258529', '', 'Gynecological Counselling - Female Genital Mutilation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258629', '', 'Gynecological Counselling-  Pap smear - Post-test counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '255258999', '', 'Gynecological Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256260000', '', 'OBSTETRICS SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256260999', '', 'Obstetric Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261000', '', 'OBSTETRICS - PRE NATAL DIAGNOSTIC PROCEDURES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261121', '', 'Obstetrics - Pre-Natal Diagnostic - Fetoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261221', '', 'Obstetrics - Pre-Natal Diagnostic - Ultrasonography, Pre-natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261321', '', 'Obstetrics - Pre-Natal Diagnostic - Pelvimetry' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261421', '', 'Obstetrics - Pre-Natal Diagnostic - Placental Function Tests' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256261999', '', 'Obstetrics - Pre-Natal Diagnostic - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256262000', '', 'OBSTETRICS - PRE NATAL CARE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256262121', '', 'Obstetrics - Pre natal Care - Uterine Monitoring' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256262221', '', 'Obstetrics - Pre natal Care - Fetal Monitoring' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256262422', '', 'Obstetrics - Pre natal Care - Immunisations' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256262999', '', 'Obstetrics - Pre natal Care - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256263000', '', 'OBSTETRICS - PRE NATAL COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256263129', '', 'Obstetrics - Pre natal Counselling - Pre Natal Care Info' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256263229', '', 'Obstetrics - Pre natal Counselling - Unplanned Pregnancy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256263329', '', 'Obstetrics - Pre natal Counselling - HIV Prevention and Testing' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256263999', '', 'Obstetrics - Pre natal Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256264000', '', 'OBSTETRICS - PREGNANCY TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256264121', '', 'Obstetrics - Pregnancy Tests - Agglutination Inhibition - Urine 1 test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256264221', '', 'Obstetrics - Pregnancy Tests - Radioimmunoasays - Blood test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256264999', '', 'Obstetrics - Pregnancy Tests - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265000', '', 'OBSTETRICS - PRE NATAL LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265121', '', 'Obstetrics - Pre-Natal Lab Tests - Urine 1' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265221', '', 'Obstetrics - Pre-Natal Lab Tests - Fasting blood sugar' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265321', '', 'Obstetrics - Pre-Natal Lab Tests - Hemoglobin (HB)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265421', '', 'Obstetrics - Pre-Natal Lab Tests - Blood Type' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265521', '', 'Obstetrics - Pre-Natal Lab Tests - VDRL' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265621', '', 'Obstetrics - Pre-Natal Lab Tests - HIV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265721', '', 'Obstetrics - Pre-Natal Lab Tests - Amniocentesis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265821', '', 'Obstetrics - Pre-Natal Lab Tests - Chorionic Villi Sampling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256265999', '', 'Obstetrics - Pre-Natal Lab Tests - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256267000', '', 'OBSTETRICS - CHILD BIRTH' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256267123', '', 'Obstetrics - Child Birth, Vaginal Delivery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256267223', '', 'Obstetrics - Child Birth, Cesarean Delivery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256267323', '', 'Obstetrics - Emergency Obstetric Care (EmOC)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256267999', '', 'Obstetrics - Surgery - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256268000', '', 'OBSTETRICS - POST NATAL CARE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256268120', '', 'Obstetrics - Post natal Care - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256268999', '', 'Obstetrics - Post natal Care - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256269000', '', 'OBSTETRICS - POST NATAL COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256269129', '', 'Obstetrics - Post-Natal Counselling - FP Methods' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256269229', '', 'Obstetrics - Post-Natal Counselling - Breastfeeding Advice' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256269329', '', 'Obstetrics - Post-Natal Counselling - HIV Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '256269999', '', 'Obstetrics - Post-Natal Counselling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257270000', '', 'UROLOGICAL SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257270999', '', 'Urological Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257271000', '', 'UROLOGICAL ENDOSCOPY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257271123', '', 'Urological Endoscopy - Cystoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257271223', '', 'Urological Endoscopy - Ureteroscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257271999', '', 'Urological Endoscopy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257272000', '', 'UROLOGICAL DIAGNOSTIC IMAGING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257272121', '', 'Urological Diagnostic Imaging - Urography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257272999', '', 'Urological Diagnostic Imaging - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273000', '', 'UROLOGICAL DIAGNOSIS (OTHER )' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273121', '', 'Urological Diagnosis Other - Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273221', '', 'Urological Diagnosis Other - Prostate Cancer Screening' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273321', '', 'Urological Diagnosis Other - Peniscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273421', '', 'Urological Diagnosis Other - Other Urogenital Services' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257273999', '', 'Urological Diagnosis Other - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257274000', '', 'UROLOGICAL SURGERY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257274123', '', 'Urological Male Surgery - Biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257274223', '', 'Urological Male Surgery - Circumcision' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257274323', '', 'Urological Male Surgery - Other Surgical Services' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '257274999', '', 'Urological Male Surgery - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258280000', '', 'INFERTILITY SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258280999', '', 'Infertility/Subfertility - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258281000', '', 'INFERTILITY BIOPSY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258281123', '', 'Infertility Biopsy - Endometrial biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258281223', '', 'Infertility Biopsy - Testicular biopsy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258281999', '', 'Infertility Biopsy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258282000', '', 'INFERTILITY ENDOSCOPY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258282123', '', 'Infertility Endoscopy - Laparoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258282223', '', 'Infertility Endoscopy - Histeroscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258282999', '', 'Infertility Endoscopy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258283000', '', 'INFERTILITY DIAGNOSTIC IMAGING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258283121', '', 'Infertility Diagnostic Imaging - Histerosalpingography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258283221', '', 'Infertility Diagnostic Imaging - Ovarian ultrasound' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258283321', '', 'Infertility Diagnostic Imaging - Transvaginal ecography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258283999', '', 'Infertility Diagnostic Imaging - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284000', '', 'INFERTILITY LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284121', '', 'Infertility Lab Test - Post-coital test or Sims-Huhner test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284221', '', 'Infertility Lab Test - Fallopian Tube Patency Tests' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284321', '', 'Infertility Lab Test - Clomiphene citrate challenge test (CCCT)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284421', '', 'Infertility Lab Test - Semen analysis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284521', '', 'Infertility Lab Test - Basal Temperature' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284621', '', 'Infertility Lab Test - Mucose Analysis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284721', '', 'Infertility Lab Test - Sperm Count' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284821', '', 'Infertility Lab Test - Spermiogram' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284921', '', 'Infertility Lab Test - Hormonal analysis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258284999', '', 'Infertility Lab Test - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286000', '', 'INFERTILITY TREATMENT' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286122', '', 'Infertility Treatment - Ovulation Induction' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286222', '', 'Infertility Treatment - Embryo Transfer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286322', '', 'Infertility Treatment - Fertilization in Vitro' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286422', '', 'Infertility Treatment - Gamete Intrafallopian Transfer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286522', '', 'Infertility Treatment - Artificial Insemination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286622', '', 'Infertility Treatment - Oocyte Donation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286722', '', 'Infertility Treatment - Zygote Intrafallopian Transfer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258286999', '', 'Infertility Treatment - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258288000', '', 'INFERTILITY CONSULTATION' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258288120', '', 'Infertility/Subfertility Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258288999', '', 'Infertility/Subfertility Consultation - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258289000', '', 'INFERTILITY COUNSELLING' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258289129', '', 'Infertility/Subfertility  Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '258289999', '', 'Infertility/Subfertility  Counseling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '260290000', '', 'OTHER SPECIALIZED COUNSELLING SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '260290999', '', 'Other Specialized Counselling Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291000', '', 'COUNSELLING - GBV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291129', '', 'Counselling - GBV - Individual Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291229', '', 'Counselling - GBV - Support Groups for Survivors' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291329', '', 'Counselling - GBV - Legal Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291429', '', 'Counselling - GBV - Intimate Partner Sexual Abuse' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291529', '', 'Counselling - GBV - Intimate Partner Physical  Abuse' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291629', '', 'Counselling - GBV - Intimate Partner Emotional Abuse' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291729', '', 'Counselling - GBV - NonIntimate Partner Sexual Assalt/Rape' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291829', '', 'Counselling - GBV - Screening Only' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '261291999', '', 'Counselling - GBV - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262292000', '', 'COUNSELLING - DOMESTIC VIOLENCE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262292129', '', 'Counselling - Domestic Violence' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262292229', '', 'Counselling - Domestic Violence, Screening' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262292999', '', 'Counselling - Domestic Violence - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262293000', '', 'COUNSELLING - FAMILY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262293129', '', 'Counselling - Family - Parent/Child Relationship' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262293229', '', 'Counselling - Family- Family Conflict' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262293329', '', 'Counselling - Family, Delinquency' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262293999', '', 'Counselling - Family - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262294000', '', 'COUNSELLING - PRE-MARITAL / MARITAL' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262294129', '', 'Counselling - Pre-Marital including Pre-Marital Family Planning' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262294229', '', 'Counselling - Marital - Relationship, Partner Negotiation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262294329', '', 'Counselling - Marital - Sexuality / Sexual Disfunction' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262294999', '', 'Counselling - Marital - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295000', '', 'COUNSELLING - YOUTH (less than 25 yrs)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295129', '', 'Counselling - Youth - Life Skills Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295229', '', 'Counselling - Youth - Sexuality' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295329', '', 'Counselling - Youth - Telephone / Internet Hotline Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295429', '', 'Counselling - Youth - SRH Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262295999', '', 'Counselling - Youth - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262296000', '', 'COUNSELLING - MALE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262296129', '', 'Counselling - Male - SRH Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262296229', '', 'Counselling - Male - Sexuality' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262296329', '', 'Counselling - Male - GBV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '262296999', '', 'Counselling - Male - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '263297000', '', 'COUNSELLING SERVICES (OTHER)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '263297129', '', 'Counseling - Other - Sexuality Issues ( 25 years and over)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '263297999', '', 'Counseling - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298000', '', 'OTHER SRH MEDICAL SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298120', '', 'Other SRH medical services - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298221', '', 'Other SRH medical services - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298322', '', 'Other SRH medical services - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298423', '', 'Other SRH medical services - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '269298999', '', 'Other SRH medical services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '370000000', '', 'MEDICAL SPECIALTY SERVICIES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371300000', '', 'MEDICAL SPECIALTIES - SYSTEM ORIENTED SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371300999', '', 'Medical Specialties - System Oriented Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301000', '', 'ANGIOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301130', '', 'Angiology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301231', '', 'Angiology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301332', '', 'Angiology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301433', '', 'Angiology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371301999', '', 'Angiology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311000', '', 'CARDIOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311130', '', 'Cardiology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311231', '', 'Cardiology - Diagnostic EKG' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311332', '', 'Cardiology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311433', '', 'Cardiology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371311999', '', 'Cardiology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321000', '', 'DENTISTRY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321131', '', 'Dentistry - Diagnosis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321232', '', 'Dentistry -Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321332', '', 'Dentistry - Orthodontics' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321432', '', 'Dentistry - Periodontics' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321533', '', 'Dentistry - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371321999', '', 'Dentistry - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331000', '', 'DERMATOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331130', '', 'Dermatology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331231', '', 'Dermatology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331332', '', 'Dermatology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331433', '', 'Dermatology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371331999', '', 'Dermatology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341000', '', 'ENDOCRINOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341130', '', 'Endocrinology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341231', '', 'Endocrinology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341332', '', 'Endocrinology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341433', '', 'Endocrinology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371341999', '', 'Endocrinology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351000', '', 'GASTROENTEROLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351130', '', 'Gastroenterology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351231', '', 'Gastroenterology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351332', '', 'Gastroenterology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351433', '', 'Gastroenterology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371351999', '', 'Gastroenterology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361000', '', 'GENETICS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361129', '', 'Genetics - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361230', '', 'Genetics - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361331', '', 'Genetics - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361432', '', 'Genetics - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371361999', '', 'Genetics - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371000', '', 'NEPHROLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371130', '', 'Nephrology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371231', '', 'Nephrology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371332', '', 'Nephrology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371433', '', 'Nephrology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371371999', '', 'Nephrology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381000', '', 'NEUMOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381130', '', 'Neumology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381231', '', 'Neumology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381332', '', 'Neumology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381433', '', 'Neumology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371381999', '', 'Neumology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391000', '', 'NEUROLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391130', '', 'Neurology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391231', '', 'Neurology - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391332', '', 'Neurology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391433', '', 'Neurology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371391999', '', 'Neurology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401000', '', 'OPHTALMOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401130', '', 'Ophtalmology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401231', '', 'Ophtalmology - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401332', '', 'Ophtalmology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401433', '', 'Ophtalmology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371401999', '', 'Ophtalmology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411000', '', 'ORTHOPEDICS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411130', '', 'Orthopedics - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411231', '', 'Orthopedics - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411332', '', 'Orthopedics - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411433', '', 'Orthopedics - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371411999', '', 'Orthopedics - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421000', '', 'OTHORHINOLARINGOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421130', '', 'Othorhinolaringology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421231', '', 'Othorhinolaringology - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421332', '', 'Othorhinolaringology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421433', '', 'Othorhinolaringology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371421999', '', 'Othorhinolaringology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431000', '', 'PODOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431130', '', 'Podology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431231', '', 'Podology - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431332', '', 'Podology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431433', '', 'Podology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371431999', '', 'Podology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441000', '', 'RHEUMATOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441130', '', 'Rheumatology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441231', '', 'Rheumatology - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441332', '', 'Rheumatology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441433', '', 'Rheumatology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '371441999', '', 'Rheumatology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372500000', '', 'MEDICAL SPECIALTIES - DISEASE ORIENTED SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372500999', '', 'Medical Specialties - Disease Oriented Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372501000', '', 'OPTOMETRY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372501130', '', 'Optometry - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372501231', '', 'Optometry - Diagnostic Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372501999', '', 'Optometry - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372511000', '', 'PSYCHIATRY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372511131', '', 'Psychiatry - Diagnostic consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372511232', '', 'Psychiatry - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372511999', '', 'Psychiatry - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372521000', '', 'PSYCHOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372521131', '', 'Psychology - Diagnostic consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372521232', '', 'Psychology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372521999', '', 'Psychology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372531000', '', 'RADIOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372531131', '', 'Radiology - Diagnostic Imaging' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372531232', '', 'Radiology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372531999', '', 'Radiology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372541000', '', 'ONCOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372541131', '', 'Oncology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372541232', '', 'Oncology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372541333', '', 'Oncology - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372541999', '', 'Oncology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372551000', '', 'ALLERGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372551130', '', 'Allergy - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372551231', '', 'Allergy - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372551332', '', 'Allergy - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372551999', '', 'Allergy - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372561000', '', 'IMMUNOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372561130', '', 'Immunology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372561231', '', 'Immunology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '372561999', '', 'Immunology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373600000', '', 'MEDICAL SPECIALTIES - COMMUNITY ORIENTED SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373600999', '', 'Medical Specialties - Community Oriented Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601000', '', 'FAMILY HEALTH' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601131', '', 'Family Health -  Hypertension Screening' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601231', '', 'Family Health -  Physical Exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601331', '', 'Family Health -  Weight & Vital Signs' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601431', '', 'Family Health -  Diabetes Screening' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601531', '', 'Family Health -  Urinalysis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601631', '', 'Family Health -  Cholesterol screening' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601729', '', 'Family Health -  Nutrition Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601829', '', 'Family Health -  Diet/Weight Control Counseling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373601999', '', 'Family Health - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373621000', '', 'GERIATRICS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373621130', '', 'Geriatrics - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373621231', '', 'Geriatrics - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373621332', '', 'Geriatrics - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373621999', '', 'Geriatrics - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641000', '', 'PEDIATRICS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641130', '', 'Pediatrics - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641231', '', 'Pediatrics - Diagnostic - Neonatal Screening (at Birth)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641331', '', 'Pediatrics - Diagnostic - Well Baby Care / Infant Health Check' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641432', '', 'Pediatrics - Therapy / Treatment - Nutrition' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641532', '', 'Pediatrics - Therapy / Treatment - Immunization' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641632', '', 'Pediatrics - Therapy / Treatment - Oral rehydration (ORT/ORS)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641732', '', 'Pediatrics - Therapy / Treatment - Neonatal Intensive Care' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641833', '', 'Pediatrics - Surgery - Circumcision' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373641999', '', 'Pediatrics - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661000', '', 'PHYSICAL MEDICINE & REHABILITATION' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661130', '', 'Physical Medicine & Rehabilitation - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661231', '', 'Physical Medicine & Rehabilitation - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661332', '', 'Physical Medicine & Rehabilitation - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661433', '', 'Physical Medicine & Rehabilitation - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373661999', '', 'Physical Medicine & Rehabilitation - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373671000', '', 'PREVENTIVE MEDICINE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373671130', '', 'Preventive Medicine - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373671231', '', 'Preventive Medicine - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373671999', '', 'Preventive Medicine - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373681000', '', 'EMERGENCY MEDICINE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373681131', '', 'Emergency Medicine - Evaluation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373681232', '', 'Emergency Medicine - Initial Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373681333', '', 'Emergency Medicine - Emergency Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373681999', '', 'Emergency Medicine - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373691000', '', 'HOSPITALIZATION' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373691140', '', 'Hospitalization - Ambulatory (1 day)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373691241', '', 'Hospitalization - Extended (>1day)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '373691999', '', 'Hospitalization - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374700000', '', 'MEDICAL SPECIALTIES DIAGNOSTIC/THERAPEUTIC PROCEDURES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374700999', '', 'Medical Specialties - Diagnostic/Therapeutic Procedures - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374701000', '', 'HEMATOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374701130', '', 'Hematology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374701231', '', 'Hematology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374701332', '', 'Hematology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374701999', '', 'Hematology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374721000', '', 'TOXICOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374721130', '', 'Toxicology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374721231', '', 'Toxicology - Diagnostic tests' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374721332', '', 'Toxicology - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374721999', '', 'Toxicology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374741000', '', 'CHEMICAL PATHOLOGY LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374741130', '', 'Chemical Patology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374741231', '', 'Chemical Patology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374751999', '', 'Chemical Patology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374761000', '', 'PATHOLOGY LAB TESTS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374761130', '', 'Pathology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374761231', '', 'Pathology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374761999', '', 'Pathology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374781000', '', 'MICROBIOLOGY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374781130', '', 'Microbiology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374781231', '', 'Microbiology - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '374781999', '', 'Microbiology - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375800000', '', 'MEDICAL SPECIALTIES - OTHER SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375800999', '', 'Medical Specialties - Other Services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375801000', '', 'CHIROPRACTICE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375801130', '', 'Chiropractice - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375801232', '', 'Chiropractice - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375801999', '', 'Chiropractice - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375811000', '', 'OSTEOPHATY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375811130', '', 'Osteophaty - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375811232', '', 'Osteophaty - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375811331', '', 'Osteophaty - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375811999', '', 'Osteophaty - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375821000', '', 'PLASTIC SURGERY' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375821130', '', 'Plastic Surgery - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375821232', '', 'Plastic Surgery - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375821333', '', 'Plastic Surgery - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375821999', '', 'Plastic Surgery - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831000', '', 'OTHER NON SRH MEDICAL SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831130', '', 'Other non-SRH medical services - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831231', '', 'Other non-SRH medical services - Diagnostic Test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831332', '', 'Other non-SRH medical services - Therapy / Treatment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831433', '', 'Other non-SRH medical services - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831539', '', 'Other non-SRH medical services - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '375831999', '', 'Other non-SRH medical services - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '376100000', '', 'PREVENTION AND TREATMENT SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '376101000', '', 'MALARIA PREVENTION AND TREATMENT SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '376101132', '', 'Malaria prevention and treatment services  for children under 5 years' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '376101232', '', 'Malaria prevention and treatment services  for pregnant mothers' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '376101999', '', 'Malaria prevention and treatment services  OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '380910000', '', 'OTHER NON SRH SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '380910999', '', 'ALL OTHER NON SRH SERVICES - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '380911000', '', 'SALES & RENTALS' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '380911999', '', 'Sales & Rentals - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '381912000', '', 'SALES OF MEDICINES, SUPPLIES AND EQUIPMENT' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '381912150', '', 'Sales of Medicines' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '381912250', '', 'Sales Medical Supplies' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '381912350', '', 'Sales Medical Equipment' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '381913999', '', 'Sales - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '382914000', '', 'RENTAL OF MEDICAL EQUIPMENT / INFRASTRUCTURE' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '382914450', '', 'Rental Medical Infrastructure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '382915999', '', 'Rental Medical Equipment / Infrastructure - OTHER' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '490000000', '', 'NON-MEDICAL PRODUCTS AND SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '491990000', '', 'OTHER NON MEDICAL PRODUCTS & SERVICES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '491990190', '', 'Other non-medical products - Sales of IEC Materials' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '491990290', '', 'Other non-medical Products & Services - Free distribution of IEC materials' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '491990999', '', 'Other non-medical products - Other Generic Products' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '492992000', '', 'OTHER NON MEDICAL SERVICES SALES' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '492992090', '', 'Other non-medical services - Sales of IEC Services' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 11, '492992999', '', 'Other non-medical services - OTHER' );

CREATE TABLE IF NOT EXISTS lists_ippf_gcac (
  id            bigint(20)   NOT NULL,
  client_status varchar(255) NOT NULL DEFAULT '',
  in_ab_proc    varchar(255) NOT NULL DEFAULT '',
  ab_types      varchar(255) NOT NULL DEFAULT '',
  ab_location   varchar(255) NOT NULL DEFAULT '',
  pr_status     varchar(255) NOT NULL DEFAULT '',
  gest_age_by   varchar(255) NOT NULL DEFAULT '',
  blood_group   varchar(255) NOT NULL DEFAULT '',
  sti           varchar(255) NOT NULL DEFAULT '',
  rh_factor     varchar(255) NOT NULL DEFAULT '',
  prep_procs    varchar(255) NOT NULL DEFAULT '',
  reason        varchar(255) NOT NULL DEFAULT '',
  exp_p_i       varchar(255) NOT NULL DEFAULT '',
  exp_pop       varchar(255) NOT NULL DEFAULT '',
  ab_contraind  varchar(255) NOT NULL DEFAULT '',
  screening     varchar(255) NOT NULL DEFAULT '',
  pre_op        varchar(255) NOT NULL DEFAULT '',
  anesthesia    varchar(255) NOT NULL DEFAULT '',
  side_eff      varchar(255) NOT NULL DEFAULT '',
  rec_compl     varchar(255) NOT NULL DEFAULT '',
  post_op       varchar(255) NOT NULL DEFAULT '',
  qc_ind        varchar(255) NOT NULL DEFAULT '',
  contrameth    varchar(255) NOT NULL DEFAULT '',
  fol_compl     varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- DELETE FROM list_options WHERE list_id = 'clientstatus';
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','maaa'  ,'MA Client Accepting Abortion', 1,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','mara'  ,'MA Client Refusing Abortion' , 2,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','refout','Outbound Referral'           , 3,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','refin' ,'Inbound Referral'            , 4,0,0);
DELETE FROM list_options WHERE list_id = 'in_ab_proc';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','s_dnc','Surgical - D&C'                      , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','s_dne','Surgical - D&E'                      , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','s_mva','Surgical - MVA/EVA'                  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','s_oth','Surgical - Other'                    , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','m_mis','Medical - Misoprostol'               , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','m_mm' ,'Medical - Mifepristone + Misoprostol', 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('in_ab_proc','m_oth','Medical - Other'                     , 8,0,0);
DELETE FROM list_options WHERE list_id = 'ab_types';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','missed' ,'Missed Abortion'                     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','spon'   ,'Spontaneous Abortion'                , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','legin'  ,'Legally Induced Abortion'            , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','illegal','Illegal Abortion'                    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','unspec' ,'Unspecified Type of Abortion'        , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','legun'  ,'Legally Unspecified Type of Abortion', 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','threat' ,'Threatened Abortion'                 , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_types','fail'   ,'Failed Attempted Abortion'           , 8,0,0);
-- DELETE FROM list_options WHERE list_id = 'ab_location';
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','proc' ,'Procedure at this site'              , 1,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','ma'   ,'Followup procedure from this site'   , 2,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','part' ,'Followup procedure from partner site', 3,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','oth'  ,'Followup procedure from other site'  , 4,0,0);
DELETE FROM list_options WHERE list_id = 'pr_status';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_status','n_u'   ,'Normal - Urine Test'           , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_status','n_bpe' ,'Normal - Bimanual Pelvic Exam' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_status','a_ep'  ,'Abnormal - Ectopic Pregnancy'  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_status','a_mp'  ,'Abnormal - Molar Pregnancy'    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_status','dis'   ,'Discarded'                     , 5,0,0);
DELETE FROM list_options WHERE list_id = 'gest_age_by';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gest_age_by','lmp','Last Monthly Period', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gest_age_by','son','Sonogram'           , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gest_age_by','oth','Other'              , 3,0,0);
DELETE FROM list_options WHERE list_id = 'bloodgroup';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bloodgroup','a' ,'A' , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bloodgroup','b' ,'B' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bloodgroup','ab','AB', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bloodgroup','o' ,'O' , 4,0,0);
-- DELETE FROM list_options WHERE list_id = 'sti';
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','hiv' ,'HIV'                  , 1,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','chla','Chlamydia'            , 2,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','gono','Gonorrhea'            , 3,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','syph','Syphilis'             , 4,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','hpv' ,'HPV'                  , 5,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','herp','Herpes (Type 1 and 2)', 6,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','tric','Trichomoniasis'       , 7,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sti','oth' ,'Other'                , 8,0,0);
DELETE FROM list_options WHERE list_id = 'rh_factor';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('rh_factor', 'pos' ,'Positive', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('rh_factor', 'neg' ,'Negative', 2,0,0);
DELETE FROM list_options WHERE list_id = 'prep_procs';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('prep_procs', 'cons','Informed Consent Signed'                     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('prep_procs', 'imm' ,'Immunoglobin Given to RH(-) Patients'        , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('prep_procs', 'hcg' ,'Initial HCG Level Verifying Pregnancy Status', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('prep_procs', 'hema','Hematocrit Taken for Risk Assessment'        , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('prep_procs', 'vit' ,'Vital Signs Recorded'                        , 5,0,0);
DELETE FROM list_options WHERE list_id = 'exp_p_i';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_p_i', 'med' , 'Medical Procedures Explained'     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_p_i', 'sur' , 'Surgical Procedures Explained'    , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_p_i', 'risk', 'Risks and Complications Explained', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_p_i', 'side', 'Common Side Effects Explained'    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_p_i', 'rec' , 'Recovery Process Explained'       , 5,0,0);
DELETE FROM list_options WHERE list_id = 'exp_pop';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_pop', 'cont' , 'Continue Pregnancy'      , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_pop', 'adopt', 'Explore Adoption'        , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_pop', 'term' , 'Termination of Pregnancy', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('exp_pop', 'oth'  , 'Other'                   , 4,0,0);
DELETE FROM list_options WHERE list_id = 'ab_contraind';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','iud'     ,'IUD in Place'                          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','allergy' ,'Allergy to Prostaglandins/Mifepristone', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','adrenal' ,'Chronic adrenal failure'               , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','steroid' ,'Long term corticosteroid treatment'    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','anticoag','Concurrent anticouagulant therapy'     , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','ectopic' ,'Ectopic pregnancy'                     , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','hem'     ,'Hemorrhagic disorder'                  , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_contraind','oth'     ,'Other'                                 , 8,0,0);
DELETE FROM list_options WHERE list_id = 'screening';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('screening','gb_viol' ,'Gender-Based Violence', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('screening','fam_viol','Family Violence'      , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('screening','hiv'     ,'HIV'                  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('screening','sti'     ,'STI'                  , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('screening','discrim' ,'Stigma/Discrimination', 5,0,0);
DELETE FROM list_options WHERE list_id = 'pre_op';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pre_op','exam'  ,'Pelvic Exam'          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pre_op','analg' ,'Analgesic Provided'   , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pre_op','antis' ,'Antiseptic Provided'  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pre_op','clean' ,'Cervix/Vagina Cleaned', 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pre_op','vitals','Vital Signs Recorded' , 5,0,0);
DELETE FROM list_options WHERE list_id = 'anesthesia';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('anesthesia','local' ,'Local'                  , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('anesthesia','locsed','Local + Sedation'       , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('anesthesia','sed'   ,'Sedation Only'          , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('anesthesia','gen'   ,'General Anesthesia'     , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('anesthesia','parac' ,'Paracervical Anesthesia', 5,0,0);
DELETE FROM list_options WHERE list_id = 'side_eff';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','incomp','Procedure Incomplete, Abandoned', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','bleed' ,'Extra Bleeding'                 , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','injury','Injury/Perforation'             , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','shock' ,'Shock'                          , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','pain'  ,'Severe Abdominal Pain'          , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','card'  ,'Cardiac Arrest'                 , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','anes'  ,'Anesthesia Complications'       , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('side_eff','oth'   ,'Other, Specify'                 , 8,0,0);
DELETE FROM list_options WHERE list_id = 'complication';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','incomp' ,'Retention of Product', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','bleed'  ,'Excessive Bleeding/Hemorrhage'           , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','trauma' ,'Trauma to Vagina, Cervix, or Uterus'     , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','shock'  ,'Shock'                                   , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','infect' ,'Infection'                               , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','ectopic','Ectopic/Molar Pregnancy'                 , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','failed' ,'Continuing Pregnancy (Failed Attempt)'   , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('complication','oth'    ,'Other Complications'                     , 8,0,0);
DELETE FROM list_options WHERE list_id = 'post_op';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('post_op','texam'  ,'Tissue Examination'        , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('post_op','tpath'  ,'Tissue Sent for Pathology' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('post_op','confirm','Confirmation of Completion', 3,0,0);
DELETE FROM list_options WHERE list_id = 'qc_ind';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('qc_ind','eprov' ,'Emergency Written Instructions Provided', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('qc_ind','eaware','Client Aware of Emergency Procedures'   , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('qc_ind','couns' ,'Contraceptive Counseling Provided'      , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('qc_ind','appt'  ,'Followup Appointment Recorded'          , 4,0,0);

DELETE FROM list_options WHERE list_id = 'lists' AND seq > 69;
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','clientstatus','GCAC Client Statuses'                      ,70,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','in_ab_proc'  ,'GCAC Induced Abortion Procedures'          ,71,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','ab_types'    ,'GCAC Abortion Types'                       ,72,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','ab_location' ,'GCAC Visit Types'                          ,73,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','pr_status'   ,'GCAC Pregnancy Statuses'                   ,74,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','gest_age_by' ,'GCAC Gestational Age Confirmed By'         ,75,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','bloodgroup'  ,'GCAC Blood Groups'                         ,76,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','sti'         ,'GCAC Detection of STI'                     ,77,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','rh_factor'   ,'GCAC RH Factor'                            ,78,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','prep_procs'  ,'GCAC Preparation Procedures'               ,79,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','exp_p_i'     ,'GCAC Explanations of Procedures and Issues',81,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','exp_pop'     ,'GCAC Explanations of Pregnancy Options'    ,82,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','ab_contraind','GCAC Contraindications'                    ,83,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','screening'   ,'GCAC Screening for SRHR Concerns'          ,84,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','pre_op'      ,'GCAC Pre-Surgery Procedures'               ,85,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','anesthesia'  ,'GCAC Types of Anesthesia'                  ,86,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','side_eff'    ,'GCAC Immediate Side Effects'               ,87,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','complication','GCAC Post Abortion Complications'          ,88,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','post_op'     ,'GCAC Post-Surgery Procedures'              ,89,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','qc_ind'      ,'GCAC Quality of Care Indicators'           ,90,0,0);

CREATE TABLE IF NOT EXISTS lists_ippf_con (
  id            bigint(20)   NOT NULL,
  prev_method   varchar(255) NOT NULL DEFAULT '',
  new_method    varchar(255) NOT NULL DEFAULT '',
  reason_chg    varchar(255) NOT NULL DEFAULT '',
  reason_term   varchar(255) NOT NULL DEFAULT '',
-- risks         varchar(255) NOT NULL DEFAULT '',
  hor_history   varchar(255) NOT NULL DEFAULT '',
  hor_lmp       varchar(255) NOT NULL DEFAULT '',
  hor_flow      varchar(255) NOT NULL DEFAULT '',
  hor_bleeding  varchar(255) NOT NULL DEFAULT '',
  hor_contra    varchar(255) NOT NULL DEFAULT '',
  iud_history   varchar(255) NOT NULL DEFAULT '',
  iud_lmp       varchar(255) NOT NULL DEFAULT '',
  iud_pain      varchar(255) NOT NULL DEFAULT '',
  iud_upos      varchar(255) NOT NULL DEFAULT '',
  iud_contra    varchar(255) NOT NULL DEFAULT '',
  sur_screen    varchar(255) NOT NULL DEFAULT '',
  sur_anes      varchar(255) NOT NULL DEFAULT '',
  sur_type      varchar(255) NOT NULL DEFAULT '',
  sur_post_ins  varchar(255) NOT NULL DEFAULT '',
  sur_contra    varchar(255) NOT NULL DEFAULT '',
  nat_reason    varchar(255) NOT NULL DEFAULT '',
  nat_method    varchar(255) NOT NULL DEFAULT '',
  emg_reason    varchar(255) NOT NULL DEFAULT '',
  emg_method    varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DELETE FROM list_options WHERE list_id = 'menhist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','normen' ,'Normal Menarche'                 , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','norlen' ,'Normal Length of Menstrual Cycle', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','norflow','Normal Flow'                     , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','bleed'  ,'Intermenstrual Bleeding'         , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','dysmen' ,'Dysmenorrhea'                    , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','disch'  ,'Discharge'                       , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menhist','oth'    ,'Other'                           , 7,0,0);

DELETE FROM list_options WHERE list_id = 'lmp';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lmp','lw'  ,'Last week'                 , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lmp','lm'  ,'Last month'                , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lmp','1to3','Between 1 and 3 months ago', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lmp','gt3' ,'More than 3 months ago'    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lmp','oth' ,'Other'                     , 5,0,0);

DELETE FROM list_options WHERE list_id = 'bleeding';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','none' ,'None'    , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','mod'  ,'Moderate', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','stain','Staining', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','mild' ,'Mild'    , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','heavy','Heavy'   , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','spot' ,'Spotting', 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','sev'  ,'Severe'  , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('bleeding','oth'  ,'Other'   , 8,0,0);

DELETE FROM list_options WHERE list_id = 'hor_contra';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','bfeed','Breast-feeding'                             , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','heart','Ishaemic Heart Disease or Stroke'           , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','smoke','Smoking if 35 Years or More'                , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','ht'   ,'Raised Blood Pressure'                      , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','htvd' ,'Hypertension with Vascular Disease'         , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','mig'  ,'Migraine with Aura'                         , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','dia'  ,'Diabetes Mellitus w/Vascular Complications' , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','throm','Deep Vein Thrombosis or Pulmonary'          , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','immob','Major Surgery with Prolonged Immobilization', 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','tmut' ,'Known Thrombogenic Mutations'               ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','cvhd' ,'Complicated Valvular Heart Disease'         ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','bcan' ,'Breast Cancer within the Past 5 Years'      ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','hep'  ,'Active Viral Hepatitis'                     ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','liver','Benign or Malignant Liver Tumour'           ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hor_contra','cirr' ,'Severe (Decompensated) Cirrhosis'           ,15,0,0);

DELETE FROM list_options WHERE list_id = 'menpain';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menpain','none','None'     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menpain','lb'  ,'Low Back' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menpain','ab'  ,'Abdominal', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('menpain','oth' ,'Other'    , 4,0,0);

DELETE FROM list_options WHERE list_id = 'uteruspos';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uteruspos','ant','Anteverted'  , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uteruspos','rev','Reverted'    , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uteruspos','mid','Mid Position', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uteruspos','oth','Other'       , 4,0,0);

DELETE FROM list_options WHERE list_id = 'iud_contra';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','preg','Known or Suspected Pregnancy'                                    , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','sep' ,'Puerperal or Post-Abortion Sepsis'                               , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','mon' ,'Months'                                                          , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','pid' ,'Pelvic Inflammatory Disease (PID)'                               , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','sti' ,'Sexually Transmitted Infection (STI)'                            , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','pcer','Purulent Cervicitis'                                             , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','mgt' ,'Malignancy of the Genital Tract'                                 , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','uvb' ,'Unexplained Vaginal Bleeding'                                    , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','ccan','Cervical Cancer Awaiting Treatment'                              , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','ecan','Endometrial Cancer'                                              ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','uter','Congenital Uterine Abnormalities or Benign Tumours of the Uterus',11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','iud' ,'IUD Placement'                                                   ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','mgtd','Malignant Gestational Trophoblastic Disease'                     ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('iud_contra','ptur','Known Pelvic Tuberculosis'                                       ,14,0,0);

DELETE FROM list_options WHERE list_id = 'sur_screen';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','vol'   ,'The client is making a voluntary and informed choice without coercion'                          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','regret','Any non-medical factors likely to cause regret are identified'                                  , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','fit'   ,'Clients fitness for sterilization'                                                              , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','risks' ,'Conditions present that may increase the risks are identified'                                  , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','app'   ,'The most appropriate surgical approach, anaesthetic regimen and type of facility are determined', 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_screen','couns' ,'Counselling specific to method'                                                                 , 6,0,0);

DELETE FROM list_options WHERE list_id = 'sur_type';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_type','mini','Minilaparotomy'           , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_type','lap' ,'Laparoscopy'              , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_type','oth' ,'Other Surgical Approaches', 3,0,0);

DELETE FROM list_options WHERE list_id = 'sur_post_ins';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','care','How to Care for the Wound'                                        , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','meds','How to Use any Post-Operative Medications that are Given'         , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','rest','Instructions to Rest at Home for the Rest of the Day'             , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','warn','What Warning Signs to Look for and What to Do about Each of Them' , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','act' ,'When to Resume Normal Activities, including Sexual Intercourse'   , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','emg' ,'Where to Go and Whom to Contact in Case of Emergency'             , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_post_ins','foll','When and Where to Return for a Follow-up Visit'                   , 7,0,0);

DELETE FROM list_options WHERE list_id = 'sur_contra';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','preg'  ,'Pregnancy'                                                 , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','ppart' ,'Postpartum (7 to <42 days)'                                , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','eclam' ,'Severe pre-eclampsia/eclampsia'                            , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','rupt'  ,'Prolonged rupture of membranes (24 hours or more)'         , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','psep'  ,'Puerperal sepsis or intrapartum/puerperal fever'           , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','phem'  ,'Severe antepartum or postpartum haemorrhage'               , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','dtraum','Severe trauma to the genital tract at the time of delivery', 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','asep'  ,'Post-abortion sepsis'                                      , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','ahem'  ,'Severe post-abortion haemorrhage'                          , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','atraum','Severe trauma to the genital tract at the time of abortion',10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','haema' ,'Acute haematometra'                                        ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','throm' ,'Deep vein thrombosis /pulmonary embolism'                  ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','immob' ,'Major surgery with prolonged immobilization'               ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','ihd'   ,'Current ischaemic heart disease'                           ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','uvb'   ,'Unexplained vaginal bleeding before evaluation'            ,15,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','mgtd'  ,'Malignant gestational trophoblastic disease'               ,16,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','ccan'  ,'Cervical cancer awaiting treatment'                        ,17,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','pid'   ,'Pelvic inflammatory disease (PID)'                         ,18,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','sti'   ,'Sexually transmitted infection'                            ,19,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','gall'  ,'Current gallbladder disease'                               ,20,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','hep'   ,'Active viral hepatitis'                                    ,21,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','iron'  ,'Iron deficiency anaemia'                                   ,22,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','asi'   ,'Local abdominal skin infection'                            ,23,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','brpn'  ,'Acute bronchitis or pneumonia'                             ,24,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','sig'   ,'Systemic infection or gastroenteritis'                     ,25,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('sur_contra','absur' ,'Sterilization concurrent with abdominal surgery'           ,26,0,0);

DELETE FROM list_options WHERE list_id = 'nat_reason';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_reason','side','Fear of side-effects'                   , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_reason','god' ,'Religious or other cultural constraints', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_reason','acc' ,'Difficult access to other methods'      , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_reason','oth' ,'Other'                                  , 4,0,0);

DELETE FROM list_options WHERE list_id = 'nat_method';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','bbt' ,'Basal body temperature (BBT)'          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','bill','Cervical mucus or ovulation (Billings)', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','ok'  ,'Calendar or rhythm (Ogino-Klaus)'      , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','stm' ,'Sympto-thermal method'                 , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','sdm' ,'Standard days method (SDM)'            , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('nat_method','oth' ,'Other'                                 , 6,0,0);

DELETE FROM list_options WHERE list_id = 'emg_reason';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','mcop','Three or more combined oral contraceptive pills missed in consecutive days'     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','mpro','One progestogen-only contraceptive pill taken 3 or more hours late'             , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','con' ,'Condom rupture or slippage'                                                     , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','dia' ,'Diaphragm dislodgement or early removal'                                        , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','fci' ,'Failed coitus interruptus (e.g. ejaculation in vagina or on external genitalia)', 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','fabm','Miscalculation of the safe period when using a FABM'                            , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','iud' ,'IUD expulsion'                                                                  , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','vsa' ,'victim of sexual assault'                                                       , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_reason','oth' ,'Other'                                                                          , 9,0,0);

DELETE FROM list_options WHERE list_id = 'emg_method';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_method','ecp' ,'Emergency contraceptive pills (ECPs): progestogen-only or combined', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_method','opoc','Oestrogen/progestogen oral contraceptives'                         , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_method','iud' ,'Copper-releasing IUDs'                                             , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('emg_method','oth' ,'Other'                                                             , 4,0,0);

INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','menhist'     ,'Contraceptive Menstrual History'                   , 92,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','lmp'         ,'Contraceptive Last Menstrual Period'               , 93,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','bleeding'    ,'Menstrual Bleeding Characteristics'                , 94,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','hor_contra'  ,'Contraindications of Hormonal Contraception'       , 95,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','menpain'     ,'Type of Menstrual Pain'                            , 96,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','uteruspos'   ,'Uterus Positions'                                  , 97,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','iud_contra'  ,'Contraindications of Barrier/IUD Contraception'    , 98,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','sur_screen'  ,'Pre-Operative Screening for Surgical Contraception', 99,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','sur_type'    ,'Types of Contraceptive Surgery'                    ,100,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','sur_post_ins','Post-Operative Instructions'                       ,101,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','sur_contra'  ,'Contraindications of Surgical Contraception'       ,102,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','nat_reason'  ,'Reasons for Adopting Natural (FABM) Contraception' ,103,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','nat_method'  ,'Types of Natural (FABM) Contraception'             ,104,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','emg_reason'  ,'Reasons for Using Emergency Contraception'         ,105,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','emg_method'  ,'Types of Emergency Contraception'                  ,106,0,0);

-- The following added on 2009-01-04.
-- Note history_data includes usertext11-30, userdate11-15, userarea11-12.

DELETE FROM layout_options WHERE form_id = 'HIS';

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext12'          ,'1','Blood Group'         , 1, 1,1, 0,  0,'bloodgroup' ,1,1,'','' ,'Blood Group');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext13'          ,'1','RH Factor'           , 2, 1,1, 0,  0,'rh_factor'  ,1,1,'','' ,'RH Factor');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext11'          ,'1','Risk Factors'        , 3,21,1, 0,  0,'riskfactors',1,1,'','' ,'Risk Factors');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','exams'               ,'1','Exams/Tests'         , 4,23,1, 0,  0,'exams'      ,1,1,'','' ,'Exam and test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext14'          ,'1','Surgical History'    , 5,25,1, 0,  0,'surghist'   ,1,3,'','' ,'Surgeries with dates/notes');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','coffee'              ,'1','Coffee'              , 6, 2,1,20,255,''           ,1,1,'','' ,'Caffeine consumption');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','tobacco'             ,'1','Tobacco'             , 7, 2,1,20,255,''           ,1,1,'','' ,'Tobacco use');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','alcohol'             ,'1','Alcohol'             , 8, 2,1,20,255,''           ,1,1,'','' ,'Alcohol consumption');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','sleep_patterns'      ,'1','Sleep Patterns'      , 9, 2,1,20,255,''           ,1,1,'','' ,'Sleep patterns');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','exercise_patterns'   ,'1','Exercise Patterns'   ,10, 2,1,20,255,''           ,1,1,'','' ,'Exercise patterns');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','seatbelt_use'        ,'1','Seatbelt Use'        ,11, 2,1,20,255,''           ,1,1,'','' ,'Seatbelt use');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','counseling'          ,'1','Counseling'          ,12, 2,1,20,255,''           ,1,1,'','' ,'Counseling activities');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','hazardous_activities','1','Hazardous Activities',13, 2,1,20,255,''           ,1,1,'','' ,'Hazardous activities');

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','history_father'               ,'2','Father'             , 1, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','history_mother'               ,'2','Mother'             , 2, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','history_siblings'             ,'2','Siblings'           , 3, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','history_spouse'               ,'2','Spouse'             , 4, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','history_offspring'            ,'2','Offspring'          , 5, 2,1,20,255,'',1,3,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_cancer'             ,'2','Cancer'             , 6, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_tuberculosis'       ,'2','Tuberculosis'       , 7, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_diabetes'           ,'2','Diabetes'           , 8, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_high_blood_pressure','2','High Blood Pressure', 9, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_heart_problems'     ,'2','Heart Problems'     ,10, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_stroke'             ,'2','Stroke'             ,11, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_epilepsy'           ,'2','Epilepsy'           ,12, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_mental_illness'     ,'2','Mental Illness'     ,13, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','relatives_suicide'            ,'2','Suicide'            ,14, 2,1,20,255,'',1,3,'','' ,'');

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext15','3','Menstrual', 1,22,1, 0,  0,'genmenhist',1,1,'','' ,'Menstrual History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext16','3','Obstetric', 2,22,1, 0,  0,'genobshist',1,1,'','' ,'Obstetric History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext17','3','Abortion' , 3,22,1, 0,  0,'genabohist',1,1,'','' ,'Abortion History');

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext18','4','HIV/AIDS' , 1,21,1, 0,  0,'genhivhist',1,1,'','' ,'HIV/AIDS History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext19','4','ITS/ITR'  , 2,21,1, 0,  0,'genitshist',1,1,'','' ,'ITS/ITR History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext20','4','Fertility', 3,21,1, 0,  0,'genferhist',1,1,'','' ,'Infertility/Subfertility History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','usertext21','4','Urology'  , 4,21,1, 0,  0,'genurohist',1,1,'','' ,'Urology History');

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','name_1'            ,'5','Name/Value'        ,1, 2,1,10,255,'',1,1,'','' ,'Name 1' );
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','value_1'           ,'5',''                  ,2, 2,1,10,255,'',0,0,'','' ,'Value 1');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','name_2'            ,'5','Name/Value'        ,3, 2,1,10,255,'',1,1,'','' ,'Name 2' );
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','value_2'           ,'5',''                  ,4, 2,1,10,255,'',0,0,'','' ,'Value 2');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','additional_history','5','Additional History',5, 3,1,30,  3,'',1,3,'' ,'' ,'Additional history notes');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','userarea11','5','User Defined Area 11',6,3,0,30,3,'',1,3,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('HIS','userarea12','5','User Defined Area 12',7,3,0,30,3,'',1,3,'','','User Defined');

DELETE FROM list_options WHERE list_id = 'surghist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','gi'    ,'Gastrointestinal Tract',  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','bil'   ,'Biliary System'        ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','spleen','Spleen'                ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','liver' ,'Liver'                 ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','pan'   ,'Pancreas'              ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','hernia','Hernia'                ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','endo'  ,'Endocrine Glands'      ,  7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','thorax','Thorax'                ,  8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('surghist','oth'   ,'Others'                ,  9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','surghist','Surgical History',110,0,0);

DELETE FROM list_options WHERE list_id = 'genmenhist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','agemen'   ,'Age of Menarche'                     ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','durabs'   ,'Duration of Absence (days)'          ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','days'     ,'Average No. of Days'                 ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','lmp'      ,'Last Normal Period (date)'           ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','cyclen'   ,'Cycle Length (days)'                 ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','flowlen'  ,'Flow Length (days)'                  ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','bleedflow','Flow Bleeding (L/M/H)'               ,  7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genmenhist','bleedslp' ,'Bleeding/spotting since last period?',  8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genmenhist','General Menstrual History',111,0,0);

DELETE FROM list_options WHERE list_id = 'genobshist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','npreg','Total No. of Pregnancies'        ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','agefp','Age at First Pregnancy'          ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','nlc'  ,'Number of Living Children'       ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','nacw' ,'How Many More Children Wanted'   ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','nclw' ,'No. Children Now Living with You',  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','nlb'  ,'Number of Live Births'           ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','nvd'  ,'Number of Vaginal Deliveries'    ,  7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','ncs'  ,'Number of C Sections'            ,  8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genobshist','dlpe' ,'Date Last Pregnancy Ended'       ,  9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genobshist','General Obstetric History',112,0,0);

DELETE FROM list_options WHERE list_id = 'genabohist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genabohist','age1st','Age at First Abortion'                 ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genabohist','nmis'  ,'No. Miscarriages/Spontaneous Abortions',  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genabohist','nstill','Number of Stillbirths'                 ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genabohist','ntubal','No. of Tubal Pregnancies (Ectopic)'    ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genabohist','nia'   ,'Number of Induced Abortions'           ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genabohist','General Abortion History',113,0,0);

DELETE FROM list_options WHERE list_id = 'genhivhist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','info'  ,'Client is informed about HIV transmission'       ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','couns' ,'Client has received HIV Test Counselling'        ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','test'  ,'Client has been HIV tested'                      ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','hpos'  ,'Client has tested HIV positive'                  ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','apos'  ,'Client has developed AIDS'                       ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','infect','Client has developed an oportunistic infection'  ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','treat' ,'Client has received HIV/AIDS treatment'          ,  7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','psych' ,'Client has received psycho-social support'       ,  8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genhivhist','discr' ,'Client has  been victim of stigma/discrimination',  9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genhivhist','HIV/AIDS Basic History',114,0,0);

DELETE FROM list_options WHERE list_id = 'genitshist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','info' ,'Client is informed about ITS transmission',  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','couns','Client has received ITS Counselling'      ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','test' ,'Client has been ITS tested'               ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','pos'  ,'Client has tested ITS positive'           ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','treat','Client has received ITS treatment'        ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genitshist','ITS/ITR Basic History',115,0,0);

DELETE FROM list_options WHERE list_id = 'genferhist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genferhist','nc12','No conception after 12 months w/o FP & female <34 years',  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genferhist','nc6' ,'No conception after 6 months w/o FP & female >35 years' ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genferhist','finc','Female is incapable of carrying a pregnancy to term'    ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genferhist','year','Tried unsuccessfully to have a child for a year or more',  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genferhist','Infertility/Subfertility Basic History',116,0,0);

DELETE FROM list_options WHERE list_id = 'genurohist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','ucon'  ,'Client reports urinary concerns'       ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','icon'  ,'Client reports incontinency concerns'  ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','infect','Client reports genital infection'      ,  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','pain'  ,'Client reports genital pain'           ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','treat' ,'Client has undergone genital treatment',  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genurohist','sur'   ,'Client has undergone genital surgery'  ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','genurohist','Urology Basic History',117,0,0);

DELETE FROM list_options WHERE list_id = 'conrisks';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'conrisks';

-- The following added/moved/revised on 2009-01-16.

DELETE FROM list_options WHERE list_id = 'sti';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'sti';

INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', '' , 'Contraception Issues', 'Core');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'a', 'Statistics'          , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'b', 'Hormonal'            , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'c', 'Barrier/IUD'         , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'd', 'Surgical'            , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'e', 'Natural'             , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('CON', 'f', 'Emergency'           , ''    );
DELETE FROM layout_options WHERE form_id = 'CON';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','prev_method'  ,'a','Last Method Used'             , 1,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'Last Contraceptive Method Used');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','new_method'   ,'a','New Method Adopted'           , 2,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'Contraceptive Method Adopted at This Visit');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','reason_chg'   ,'a','Reason for Method Change'     , 3,21,1, 2, 0,'mcreason'    ,1,3,'','' ,'Reasons for Method Change');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','reason_term'  ,'a','Reason for Method Termination', 4,21,1, 2, 0,'mcreason'    ,1,3,'','' ,'Reasons for Method Termination');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','usertext11'   ,'a','General Risk Factors'         , 5,21,1, 2, 0,'riskfactors' ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','hor_history'  ,'b','Menstrual History'            , 1,21,1, 2, 0,'menhist'     ,1,3,'','' ,'Menstrual History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','hor_lmp'      ,'b','Last Menstrual Period'        , 2, 1,1, 0, 0,'lmp'         ,1,3,'','' ,'Last Menstrual Period');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','hor_flow'     ,'b','Nature of Menstrual Flow'     , 3,21,1, 4, 0,'flowtype'    ,1,3,'','' ,'Nature of Menstrual Flow');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','hor_bleeding' ,'b','Bleeding'                     , 4,21,1, 4, 0,'bleeding'    ,1,3,'','' ,'Menstrual Bleeding Characteristics');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','hor_contra'   ,'b','Contraindications'            , 5,21,1, 1, 0,'hor_contra'  ,1,3,'','' ,'Contraindications of Hormonal Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','iud_history'  ,'c','Menstrual History'            , 1,21,1, 2, 0,'menhist'     ,1,3,'','' ,'Menstrual History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','iud_lmp'      ,'c','Last Menstrual Period'        , 2, 1,1, 0, 0,'lmp'         ,1,3,'','' ,'Last Menstrual Period');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','iud_pain'     ,'c','Pain during Menses'           , 3,21,1, 2, 0,'menpain'     ,1,3,'','' ,'Type of Pain during Menses');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','iud_upos'     ,'c','Uterus Position'              , 4, 1,1, 0, 0,'uteruspos'   ,1,3,'','' ,'Uterus Position');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','iud_contra'   ,'c','Contraindications'            , 5,21,1, 0, 0,'iud_contra'  ,1,3,'','' ,'Contraindications of Barrier/IUD Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','sur_screen'   ,'d','Pre-Operative Screening'      , 1,21,1, 1, 0,'sur_screen'  ,1,3,'','' ,'Pre-Operative Screening for Surgical Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','sur_anes'     ,'d','Type of Anesthesia'           , 2, 1,1, 0, 0,'anesthesia'  ,1,3,'','' ,'Type of Anesthesia for Surgical Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','sur_type'     ,'d','Type of Surgical Approach'    , 3, 1,1, 0, 0,'sur_type'    ,1,3,'','' ,'Type of Contraceptive Surgery');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','sur_post_ins' ,'d','Post-Operative Instructions'  , 4,21,1, 0, 0,'sur_post_ins',1,3,'','' ,'Post-Operative Instructions');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','sur_contra'   ,'d','Contraindications'            , 5,21,1, 0, 0,'sur_contra'  ,1,3,'','' ,'Contraindications of Surgical Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','nat_reason'   ,'e','Reason for Adopting a FABM'   , 1,21,1, 2, 0,'nat_reason'  ,1,3,'','' ,'Reasons for Adopting Natural Contracepation');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','nat_method'   ,'e','FABM Method Adopted'          , 2, 1,1, 0, 0,'nat_method'  ,1,3,'','' ,'Type of Natural Contraception');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','emg_reason'   ,'f','Reason for Using EC'          , 1,21,1, 1, 0,'emg_reason'  ,1,3,'','' ,'Reasons for Using Emergency Contracepation');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('CON','emg_method'   ,'f','EC Method Adopted'            , 2, 1,1, 0, 0,'emg_method'  ,1,3,'','' ,'Type of Emergency Contraception');

CREATE TABLE IF NOT EXISTS lists_ippf_srh (
  id          bigint(20)   NOT NULL,
  men_hist    varchar(255) NOT NULL DEFAULT '',
  men_compl   varchar(255) NOT NULL DEFAULT '',
  pap_hist    varchar(255) NOT NULL DEFAULT '',
  gyn_exams   varchar(255) NOT NULL DEFAULT '',
  pr_status   varchar(255) NOT NULL DEFAULT '',
  gest_age_by varchar(255) NOT NULL DEFAULT '',
  obs_exams   varchar(255) NOT NULL DEFAULT '',
  pr_outcome  varchar(255) NOT NULL DEFAULT '',
  pr_compl    varchar(255) NOT NULL DEFAULT '',
  abo_exams   varchar(255) NOT NULL DEFAULT '',
  hiv_exams   varchar(255) NOT NULL DEFAULT '',
  its_exams   varchar(255) NOT NULL DEFAULT '',
  fer_exams   varchar(255) NOT NULL DEFAULT '',
  fer_causes  varchar(255) NOT NULL DEFAULT '',
  fer_treat   varchar(255) NOT NULL DEFAULT '',
  uro_exams   varchar(255) NOT NULL DEFAULT '',
  uro_disease varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('SRH', '' , 'IPPF SRH Data'             , 'Core');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('SRH', 'a', 'Gynecology'                , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('SRH', 'b', 'Obstetrics'                , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('SRH', 'c', 'Basic RH (female only)'    , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('SRH', 'd', 'Basic RH (female and male)', ''    );
DELETE FROM layout_options WHERE form_id = 'SRH';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext15' ,'a'                ,'Menstrual History'             , 1,22,1, 0, 0,'genmenhist'  ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','men_hist'   ,'a'                ,'Recent Menstrual History'      , 2,21,1, 2, 0,'menhist'     ,1,3,'','','Recent Menstrual History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','men_compl'  ,'a'                ,'Menstrual Complications'       , 3,21,1, 2, 0,'men_compl'   ,1,3,'','','Menstrual Complications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','pap_hist'   ,'a'                ,'Pap Smear Recent History'      , 4,22,1, 0, 0,'pap_hist'    ,1,3,'','','Pap Smear Recent History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','gyn_exams'  ,'a'                ,'Gynecological Tests'           , 5,23,1, 0, 0,'gyn_exams'   ,1,1,'','','Gynecological test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','pr_status'  ,'b'                ,'Pregnancy Status Confirmed'    , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','','Pregnancy Status Confirmed');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','gest_age_by','b'                ,'Gestational Age Confirmed By'  , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','','Gestational Age Confirmed By');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext12' ,'b'                ,'Blood Group'                   , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext13' ,'b'                ,'RH Factor'                     , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','obs_exams'  ,'b'                ,'Obstetric Tests'               , 5,23,1, 0, 0,'obs_exams'   ,1,1,'','','Obstetric test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext16' ,'b'                ,'Obstetric History'             , 6,22,1, 0, 0,'genobshist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','pr_outcome' ,'b'                ,'Outcome of Last Pregnancy'     , 7,21,1, 2, 0,'pr_outcome'  ,1,3,'','','Outcome of Last Pregnancy');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','pr_compl'   ,'b'                ,'Pregnancy Complications'       , 8,21,1, 2, 0,'pr_compl'    ,1,3,'','','Pregnancy Complications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext17' ,'c'    ,'Abortion Basic History'        , 1,22,1, 0, 0,'genabohist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','abo_exams'  ,'c'    ,'Abortion Tests'                , 2,23,1, 0, 0,'abo_exams'   ,1,1,'','','Abortion test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext18' ,'d','HIV/AIDS Basic History'        , 1,21,1, 0, 0,'genhivhist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','hiv_exams'  ,'d','HIV/AIDS Tests'                , 2,23,1, 0, 0,'hiv_exams'   ,1,1,'','','HIV/AIDS test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext19' ,'d','ITS/ITR Basic History'         , 3,21,1, 0, 0,'genitshist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','its_exams'  ,'d','ITS/ITR Tests'                 , 4,23,1, 0, 0,'its_exams'   ,1,1,'','','ITS/ITR test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext20' ,'d','Fertility Basic History'       , 5,21,1, 0, 0,'genferhist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','fer_exams'  ,'d','Fertility Tests'               , 6,23,1, 0, 0,'fer_exams'   ,1,1,'','','Infertility/subfertility test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','fer_causes' ,'d','Causes of Infertility'         , 7,21,1, 2, 0,'fer_causes'  ,1,3,'','','Causes of Infertility');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','fer_treat'  ,'d','Infertility Treatment'         , 8,21,1, 2, 0,'fer_treat'   ,1,3,'','','Infertility Treatment');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','usertext21' ,'d','Urology Basic History'         , 9,21,1, 0, 0,'genurohist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','uro_exams'  ,'d','Urology Tests'                 ,10,23,1, 0, 0,'uro_exams'   ,1,1,'','','Urology test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('SRH','uro_disease','d','Male Genitourinary diseases'   ,11,21,1, 2, 0,'uro_disease' ,1,3,'','','Male Genitourinary diseases');

DELETE FROM list_options WHERE list_id = 'men_compl';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','bleed'    ,'Heavy Menstrual Bleeding'  ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','nomen'    ,'Absence of Menstruation'   ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','abn'      ,'Abnormal Menstrual Periods',  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','perpain'  ,'Painful Periods'           ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','menpain'  ,'Painful Menstruation'      ,  5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('men_compl','oth'      ,'Other'                     ,  6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists'    ,'men_compl','Menstrual Complications'   ,118,0,0);

DELETE FROM list_options WHERE list_id = 'pap_hist';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pap_hist','dtlast'    ,'Date of Last Pap Smear'       ,  1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pap_hist','results'   ,'Results of Last Pap Smear'    ,  2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pap_hist','treat'     ,'Treatment for cervical cancer',  3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pap_hist','dtnext'    ,'Date of Next Pap Smear'       ,  4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists'   ,'pap_hist'  ,'Pap Smear History'            ,119,0,0);

DELETE FROM list_options WHERE list_id = 'gyn_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'pap','Pap / Cytology'         , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'img','Diagnostic Imaging'     , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'hys','Hysterosalpingography'  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'mam','Mammography'            , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'son','Ultrasonography'        , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'tom','Tomography'             , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'bds','Dexa, Bone Density Scan', 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'man','Manual exams'           , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'mpe','Manual Pelvic Exam'     , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'mbe','Manual Breast Exam'     ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('gyn_exams' ,'oth','Other'                  ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists'     ,'gyn_exams','Gynecological Tests',120,0,0);

DELETE FROM list_options WHERE list_id = 'obs_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'fet','Fetoscopy'                     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'son','Ultrasonography, Pre-natal'    , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'pel','Pelvimetry'                    , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'pla','Placental Function Tests'      , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'utm','Uterine Monitoring'            , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'fem','Fetal Monitoring'              , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('obs_exams' ,'pnc','Pre natal Care - Immunisations', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists'     ,'obs_exams','Obstetric Tests',121,0,0);

DELETE FROM list_options WHERE list_id = 'pr_outcome';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_outcome' ,'live','Live birth'  , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_outcome' ,'still','Still birth', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_outcome' ,'abo','Abortion'     , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_outcome' ,'ect','Ectopic'      , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_outcome' ,'oth','Other'        , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','pr_outcome','Pregnancy Outcomes',122,0,0);

DELETE FROM list_options WHERE list_id = 'pr_compl';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'vag'    ,'Bacterial Vaginosis'                       , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'rest'   ,'Bed Rest'                                  , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'bleed'  ,'Bleeding During Pregnancy'                 , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'bofum'  ,'Blighted Ovum'                             , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'cercl'  ,'Cervical Cerclage'                         , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'cpox'   ,'Chicken Pox'                               , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'chol'   ,'Cholestasis of Pregnancy'                  , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'compl'  ,'Common Pregnancy Complications'            , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'confet' ,'Concerns regarding Early Fetal Development', 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'cytomeg','Cytomegalovirus (CMV) Infection'           ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'dnc'    ,'D&C procedure after a Miscarriage'         ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'ect'    ,'Ectopic Pregnancy'                         ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'fasd'   ,'Fetal Alcohol Spectrum Disorders'          ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'fgr'    ,'Fetal Growth Restriction'                  ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'dia'    ,'Gestational Diabetes'                      ,15,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'strep'  ,'Group B Strep Infection'                   ,16,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'hafl'   ,'High Amniotic Fluid Levels: Polyhydramnios',17,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'hiv'    ,'HIV/AIDS during Pregnancy'                 ,18,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'hyperm' ,'Hyperemesis Gravidarum'                    ,19,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'incerv' ,'Incompetent Cervix'                        ,20,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'iugr'   ,'Intrauterine Growth Restriction (IUGR)'    ,21,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'sga'    ,'Small for Gestational Age (SGA)'           ,22,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'list'   ,'Listeria'                                  ,23,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'lafl'   ,'Low Amniotic Fluid Levels: Oligohydramnios',24,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'mis'    ,'Miscarriage'                               ,25,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'molar'  ,'Molar Pregnancy'                           ,26,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'accr'   ,'Placenta Accreta'                          ,27,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'prev'   ,'Placenta Previa'                           ,28,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'abrup'  ,'Placental Abruption'                       ,29,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'preecl' ,'Preeclampsia'                              ,30,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'pih'    ,'Pregnancy Induced Hypertension (PIH)'      ,31,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'rhf'    ,'RH Factor'                                 ,32,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'sti'    ,'STDs/STIs During Pregnancy'                ,33,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'tip'    ,'Tipped Uterus'                             ,34,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'tox'    ,'Toxoplasmosis'                             ,35,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'uti'    ,'Urinary Tract Infection'                   ,36,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'vts'    ,'Vanishing Twin Syndrome'                   ,37,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('pr_compl' ,'yeast'  ,'Yeast Infection'                           ,38,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','pr_compl','Pregnancy Complications',123,0,0);

DELETE FROM list_options WHERE list_id = 'abo_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'ane','Exclusion of Anaemia'          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'bgr','ABO and Rh blood groups'       , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'ect','Exclusion of ectopic pregnancy', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'cer','Cervical cytology'             , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'hiv','HIV testing'                   , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('abo_exams' ,'oth','Other'                         , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists'     ,'abo_exams','Abortion Tests',124,0,0);

DELETE FROM list_options WHERE list_id = 'hiv_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'eli','ELISA (Blood) Test', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'wba','Western Blot Assay', 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'ifa','IFA'               , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'rat','Rapid Test'        , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'uri','Urine Test for HIV', 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'vil','Viral Load'        , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'cd4','CD4 count'         , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('hiv_exams' ,'oth','Other'             , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','hiv_exams','HIV/AIDS Tests',125,0,0);

DELETE FROM list_options WHERE list_id = 'its_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'vag','Bacterial Vaginosis'       , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'can','Candidiasis'               , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'cha','Chancroid'                 , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'chl','Chlamydia'                 , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'gon','Gonorrhea'                 , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'hsv','Herpes Simplex (HSV)'      , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'hpv','Human Papillomavirus (HPV)', 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'syp','Syphilis'                  , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'tri','Trichomoniasis'            , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'hep','Hepatitis B'               ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('its_exams' ,'oth','Other'                     ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','its_exams','ITS/ITR Tests',126,0,0);

DELETE FROM list_options WHERE list_id = 'fer_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'end','Endometrial biopsy'          , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'tes','Testicular biopsy'           , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'lap','Laparoscopy'                 , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'hsc','Histeroscopy'                , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'hsa','Histerosalpingography'       , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'ova','Ovarian ultrasound'          , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'tve','Transvaginal ecography'      , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'pct','Post-coital test'            , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'ftp','Fallopian Tube Patency Tests', 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'ccc','CCCT'                        ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'sem','Semen analysis'              ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'bas','Basal Temperature'           ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'muc','Mucose Analysis'             ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'spc','Sperm Count'                 ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'spg','Spermiogram'                 ,15,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'hor','Hormonal analysis'           ,16,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_exams' ,'oth','Other'                       ,17,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','fer_exams','Infertility/Subfertility Tests',127,0,0);

DELETE FROM list_options WHERE list_id = 'fer_causes';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'edy','Male erectile dysfunction'         , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'end','Female Endometriosis'              , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'pos','Female Polycystic ovarian syndrome', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'alc','Alcohol Abuse and Alcoholism'      , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'dep','Depression'                        , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'dia','Diabetes'                          , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'hyp','High Blood Pressure'               , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'kid','Kidney Failure'                    , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'msc','Multiple Sclerosis'                , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'prc','Prostate Cancer'                   ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'smo','Smoking (How to Quit Smoking)'     ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'str','Stress'                            ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'ath','Atherosclerosis'                   ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'ins','Injuries or Surgery'               ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'med','Medications'                       ,15,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_causes' ,'oth','Other'                             ,16,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','fer_causes','Causes of Infertility/Subfertility',128,0,0);

DELETE FROM list_options WHERE list_id = 'fer_treat';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'ovi','Ovulation Induction'           , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'emb','Embryo Transfer'               , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'fer','Fertilization in Vitro'        , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'git','Gamete Intrafallopian Transfer', 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'sem','Artificial Insemination'       , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'ocd','Oocyte Donation'               , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'zit','Zygote Intrafallopian Transfer', 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('fer_treat' ,'oth','Other'                         , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','fer_treat','Infertility/Subfertility Treatments',129,0,0);

DELETE FROM list_options WHERE list_id = 'uro_exams';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'cys','Cystoscopy'               , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'ure','Ureteroscopy'             , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'uro','Urography'                , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'pro','Prostate Cancer Screening', 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'pen','Peniscopy'                , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_exams' ,'oth','Other'                    , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','uro_exams','Urology Tests',130,0,0);

DELETE FROM list_options WHERE list_id = 'uro_disease';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'bal','Balanitis'            , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'cry','Cryptorchidism'       , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'ect','Ectopia Testis'       , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'eps','Epispadias'           , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'epd','Epididymitis'         , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'edy','Erectile Dysfunction' , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'fre','Frenulum Breve'       , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'hyd','Hydrocele'            , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'hyp','Hypospadias'          , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'mic','Micropenis'           ,10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'orc','Orchitis'             ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'par','Paraphimosis'         ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'pec','Penile Cancer'        ,13,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'pef','Penile Fracture'      ,14,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'pey','Peyronies Disease'    ,15,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'phi','Phimosis'             ,16,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'pre','Premature Ejaculation',17,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'prt','Prostatitis'          ,18,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'prc','Prostate Cancer'      ,19,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'spe','Spermatocele'         ,20,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'tec','Testicular Cancer'    ,21,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'tet','Testicular Torsion'   ,22,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('uro_disease' ,'var','Varicocele'           ,23,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','uro_disease','Male Genitourinary diseases',131,0,0);

-- The following added 2009-07-24

-- DELETE FROM list_options WHERE list_id = 'lbfnames';
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lbfnames','LBFgcac','IPPF GCAC',1,0,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lbfnames','LBFsrh' ,'IPPF SRH' ,2,0,0);

INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFsrh', '' , 'IPPF SRH'                  , 'Clinical');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFsrh', '1', 'Gynecology'                , ''        );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFsrh', '2', 'Obstetrics'                , ''        );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFsrh', '3', 'Basic RH (female only)'    , ''        );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFsrh', '4', 'Basic RH (female and male)', ''        );
DELETE FROM layout_options WHERE form_id = 'LBFsrh';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext15' ,'1','Menstrual History'             , 1,22,1, 0, 0,'genmenhist'  ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','men_hist'   ,'1','Recent Menstrual History'      , 2,21,1, 2, 0,'menhist'     ,1,3,'','','Recent Menstrual History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','men_compl'  ,'1','Menstrual Complications'       , 3,21,1, 2, 0,'men_compl'   ,1,3,'','','Menstrual Complications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','pap_hist'   ,'1','Pap Smear Recent History'      , 4,22,1, 0, 0,'pap_hist'    ,1,3,'','','Pap Smear Recent History');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','gyn_exams'  ,'1','Gynecological Tests'           , 5,23,1, 0, 0,'gyn_exams'   ,1,1,'','','Gynecological test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','pr_status'  ,'2','Pregnancy Status Confirmed'    , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','','Pregnancy Status Confirmed');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','gest_age_by','2','Gestational Age Confirmed By'  , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','','Gestational Age Confirmed By');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext12' ,'2','Blood Group'                   , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext13' ,'2','RH Factor'                     , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','obs_exams'  ,'2','Obstetric Tests'               , 5,23,1, 0, 0,'obs_exams'   ,1,1,'','','Obstetric test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext16' ,'2','Obstetric History'             , 6,22,1, 0, 0,'genobshist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','pr_outcome' ,'2','Outcome of Last Pregnancy'     , 7,21,1, 2, 0,'pr_outcome'  ,1,3,'','','Outcome of Last Pregnancy');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','pr_compl'   ,'2','Pregnancy Complications'       , 8,21,1, 2, 0,'pr_compl'    ,1,3,'','','Pregnancy Complications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext17' ,'3','Abortion Basic History'        , 1,22,1, 0, 0,'genabohist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','abo_exams'  ,'3','Abortion Tests'                , 2,23,1, 0, 0,'abo_exams'   ,1,1,'','','Abortion test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext18' ,'4','HIV/AIDS Basic History'        , 1,21,1, 0, 0,'genhivhist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','hiv_exams'  ,'4','HIV/AIDS Tests'                , 2,23,1, 0, 0,'hiv_exams'   ,1,1,'','','HIV/AIDS test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext19' ,'4','ITS/ITR Basic History'         , 3,21,1, 0, 0,'genitshist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','its_exams'  ,'4','ITS/ITR Tests'                 , 4,23,1, 0, 0,'its_exams'   ,1,1,'','','ITS/ITR test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext20' ,'4','Fertility Basic History'       , 5,21,1, 0, 0,'genferhist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','fer_exams'  ,'4','Fertility Tests'               , 6,23,1, 0, 0,'fer_exams'   ,1,1,'','','Infertility/subfertility test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','fer_causes' ,'4','Causes of Infertility'         , 7,21,1, 2, 0,'fer_causes'  ,1,3,'','','Causes of Infertility');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','fer_treat'  ,'4','Infertility Treatment'         , 8,21,1, 2, 0,'fer_treat'   ,1,3,'','','Infertility Treatment');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','usertext21' ,'4','Urology Basic History'         , 9,21,1, 0, 0,'genurohist'  ,1,1,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','uro_exams'  ,'4','Urology Tests'                 ,10,23,1, 0, 0,'uro_exams'   ,1,1,'','','Urology test results');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFsrh','uro_disease','4','Male Genitourinary diseases'   ,11,21,1, 2, 0,'uro_disease' ,1,3,'','','Male Genitourinary diseases');

-- The following revised or added 2009-07-28


INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '' , 'IPPF GCAC'   , 'Core');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '2', 'Counseling'  , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '3', 'Admission'   , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '4', 'Preparatory' , ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '5', 'Intervention', ''    );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('GCA', '6', 'Followup'    , ''    );
DELETE FROM layout_options WHERE form_id = 'GCA';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','reason'       ,'2','Reason for Termination'          , 1,21,1, 0, 0,'abreasons'   ,1,3,'','' ,'Reasons for Termination of Pregnancy');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','exp_p_i'      ,'2','Explanation of Procedures/Issues', 2,21,1, 2, 0,'exp_p_i'     ,1,3,'','' ,'Explanation of Procedures and Issues');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','exp_pop'      ,'2','Explanation of Pregnancy Options', 3,21,1, 2, 0,'exp_pop'     ,1,3,'','' ,'Explanation of Pregnancy Options');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','ab_contraind' ,'2','Contraindications'               , 4,21,1, 2, 0,'ab_contraind',1,3,'','' ,'Contraindications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','screening'    ,'2','Screening for SRHR Concerns'     , 5,21,1, 2, 0,'screening'   ,1,3,'','' ,'Screening for SRHR Concerns');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','in_ab_proc'   ,'3','Induced Abortion Procedure'      , 2, 1,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Abortion Procedure Accepted or Performed');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','ab_types'     ,'3','Abortion Types'                  , 3,21,1, 2, 0,'ab_types'    ,1,3,'','' ,'Abortion Types');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','pr_status'    ,'4','Pregnancy Status Confirmed'      , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','' ,'Pregnancy Status Confirmed');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','gest_age_by'  ,'4','Gestational Age Confirmed By'    , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','' ,'Gestational Age Confirmed By');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','usertext12'   ,'4','Blood Group'                     , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','usertext13'   ,'4','RH Factor'                       , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','prep_procs'   ,'4','Preparation Procedures'          , 6,21,1, 0, 0,'prep_procs'  ,1,3,'','' ,'Preparation Procedures');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','pre_op'       ,'5','Pre-Surgery Procedures'          , 1,21,1, 2, 0,'pre_op'      ,1,3,'','' ,'Pre-Surgery Procedures');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','anesthesia'   ,'5','Anesthesia'                      , 2, 1,1, 0, 0,'anesthesia'  ,1,3,'','' ,'Type of Anesthesia Used');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','side_eff'     ,'5','Immediate Side Effects'          , 3,21,1, 2, 0,'side_eff'    ,1,3,'','' ,'Immediate Side Effects (observed at intervention');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','post_op'      ,'5','Post-Surgery Procedures'         , 5,21,1, 2, 0,'post_op'     ,1,3,'','' ,'Post-Surgery Procedures');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('GCA','qc_ind'       ,'6','Quality of Care Indicators'      , 1,21,1, 0, 0,'qc_ind'      ,1,3,'','' ,'Quality of Care Indicators');

-- DELETE FROM layout_options WHERE form_id = 'LBFgcac';
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','client_status','1Basic Information','Client Status'               , 1,27,2, 0, 0,'clientstatus',1,1,'','' ,'Client Status');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','ab_location'  ,'1Basic Information','Type of Visit'               , 2,27,2, 0, 0,'ab_location' ,1,1,'','' ,'Nature of this visit');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','in_ab_proc'   ,'1Basic Information','Associated Induced Procedure', 3,27,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Applies regardless of when or where done');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','complications','2Complications','Complications'                   , 1,21,1, 2, 0,'complication',1,3,'','' ,'Post-Abortion Complications');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','main_compl'   ,'2Complications','Main Complication'               , 2, 1,1, 2, 0,'complication',1,3,'','' ,'Primary Complication');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','contrameth'   ,'3Contraception','New Method'                      , 1,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'New method adopted');

-- The following revised or added 2009-08-19

DELETE FROM list_options WHERE list_id = 'occupations' AND option_id = 'oth';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('occupations','oth','Other', 1,0,0);
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'occupations';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lists','occupations','Occupations',61,0,0);
UPDATE layout_options SET data_type = 26, list_id = 'occupations'  WHERE form_id = 'DEM' AND field_id = 'occupation';
UPDATE layout_options SET data_type = 26, title = 'Religion'       WHERE form_id = 'DEM' AND field_id = 'userlist5';
UPDATE layout_options SET data_type = 26, title = 'Monthly Income' WHERE form_id = 'DEM' AND field_id = 'userlist3';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'ethnoracial';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'language';
UPDATE layout_options SET data_type = 26 WHERE form_id = 'DEM' AND field_id = 'status';
UPDATE layout_options SET uor = 0 WHERE form_id = 'DEM' AND field_id = 'providerID';

-- The following added 2009-10-12

UPDATE layout_options AS a, list_options AS i SET a.group_id = '1', a.title = 'Transgender', a.seq = 13, a.data_type = 26, a.uor = 1, a.description = 'Transgender', i.title = 'Transgender' WHERE a.form_id = 'DEM' AND a.field_id = 'userlist6' AND a.uor = 0 AND i.list_id = 'lists' AND i.option_id = 'userlist6';

-- The following added 2010-01-17 (duplicated in ippf_upgrade.sql)

UPDATE list_options SET mapping = ':2522231'   WHERE list_id = 'in_ab_proc' AND option_id = 's_dnc';
UPDATE list_options SET mapping = ':2522232'   WHERE list_id = 'in_ab_proc' AND option_id = 's_dne';
UPDATE list_options SET mapping = ':2522233'   WHERE list_id = 'in_ab_proc' AND option_id = 's_mva';
UPDATE list_options SET mapping = ':2522239'   WHERE list_id = 'in_ab_proc' AND option_id = 's_oth';
UPDATE list_options SET mapping = ':2522242'   WHERE list_id = 'in_ab_proc' AND option_id = 'm_mis';
UPDATE list_options SET mapping = ':2522241'   WHERE list_id = 'in_ab_proc' AND option_id = 'm_mm';
UPDATE list_options SET mapping = ':2522249'   WHERE list_id = 'in_ab_proc' AND option_id = 'm_oth';
UPDATE list_options SET mapping = ':11214'     WHERE list_id = 'contrameth' AND option_id = 'con';
UPDATE list_options SET mapping = ':11215'     WHERE list_id = 'contrameth' AND option_id = 'dia';
UPDATE list_options SET mapping = ':14521'     WHERE list_id = 'contrameth' AND option_id = 'ec';
UPDATE list_options SET mapping = ':13119'     WHERE list_id = 'contrameth' AND option_id = 'fab';
UPDATE list_options SET mapping = ':11216'     WHERE list_id = 'contrameth' AND option_id = 'fc';
UPDATE list_options SET mapping = ':11113'     WHERE list_id = 'contrameth' AND option_id = 'pat';
UPDATE list_options SET mapping = ':11112'     WHERE list_id = 'contrameth' AND option_id = 'imp';
UPDATE list_options SET mapping = ':11111'     WHERE list_id = 'contrameth' AND option_id = 'inj';
UPDATE list_options SET mapping = ':11317'     WHERE list_id = 'contrameth' AND option_id = 'iud';
UPDATE list_options SET mapping = ':11110'     WHERE list_id = 'contrameth' AND option_id = 'or';
UPDATE list_options SET mapping = ':11215'     WHERE list_id = 'contrameth' AND option_id = 'cap';
UPDATE list_options SET mapping = ':11216'     WHERE list_id = 'contrameth' AND option_id = 'sp';
UPDATE list_options SET mapping = ':12.18'     WHERE list_id = 'contrameth' AND option_id = 'vsc';
UPDATE list_options SET mapping = ':00000'     WHERE list_id = 'contrameth' AND option_id = 'no';

-- The following added 2010-02-25:

UPDATE list_options SET mapping = 'F' WHERE list_id = 'sex' AND option_id = 'Female';
UPDATE list_options SET mapping = 'M' WHERE list_id = 'sex' AND option_id = 'Male';

UPDATE list_options SET title = 'Education' WHERE list_id = 'lists' AND option_id = 'userlist2';
DELETE FROM list_options WHERE list_id = 'userlist2';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, mapping ) VALUES ('userlist2','1','Illiterate',1,0,'0');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, mapping ) VALUES ('userlist2','2','Basic Schooling',2,1,'1');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, mapping ) VALUES ('userlist2','3','Advanced Schooling',3,0,'2');

insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'New Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = '(New Patient)' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Active visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Active Encounter' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Active Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Active Patient' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Active Client:' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Active Patient:' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Add Payment' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Add Copay' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Add Product' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Add Drug' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Create a new OpenEMR record' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Add Patient Record' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Appointments and Visits' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Appointments and Encounters' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Charges by Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Appointments and Visits' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Appt-Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Appt-Enc' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Svcs Provider Cash Rec' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Cash Rec' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Service Provider Cash Receipts' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Cash Receipts' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'System ID' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Chart' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Reason of Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Chief Complaint' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Reason of Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Chief Compliant' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Cliente' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Client' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Payment' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'CO-PAY' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Collection Report' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Collections' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Payment' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'COPAY' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'C3' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'CPT4' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Encounter' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Visit Forms to include in this Report:' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Encounter Forms to Include in this Report:' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Visits' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Encounters' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Visits Report' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Encounters Report' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Clinic ID' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Facility' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Tally Sheet' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Fee Sheet' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Find Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Find Patient' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'ID' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'ID' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'GCAC Form (example)' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'IPPF SRH Data' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'GCAC Form (example) for' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'IPPF SRH Data for' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Last name' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Last' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'New Encounter' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'New Visit Form' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'New Encounter Form' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'New Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'New Patient' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Past Visits and Documents' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Past Encounters and Documents' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client Appointment' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Appointment' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client Visit Form' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Encounter Form' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client Notes' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Notes' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client number' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Number' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client Record Report' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Record Report' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client Report' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Report' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Referrals and Other Transactions' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient Transactions' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Patient/Client' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client ID (assign by the system)' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'PID' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Payments' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Prepay' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Service Provider' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Provider' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Client notes/Auth' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Pt Notes/Auth' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Default' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Rendering' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Prescp & Disp' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Rx' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Sales by Item' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Sales by Item' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Save Demographic Client  Data' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Save Patient Demographic' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Search or Add Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Search or Add Patient ' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Select Client' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Select Patient' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Select Client by Last' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Select Patient by Last' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Risk' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Sensitivity' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'National ID' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'SSN' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Tally Sheet' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Superbill' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'This Visit' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'This Encounter' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Referrals' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Transact' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'User Administration' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'User & Group Administration' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'View Comprehensive Client Report' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'View Comprehensive Patient Report' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Diagnostic Labs & Orders' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Procedures' and ld.cons_id is null;
insert into lang_definitions ( cons_id, lang_id, definition ) select lc.cons_id, 1, 'Program' from lang_constants as lc left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1 where lc.constant_name = 'Layout Based' and ld.cons_id is null;

UPDATE openemr_postcalendar_categories SET pc_catname = '1 Admission', pc_catcolor = '#FFFFFF' WHERE pc_catid = 10 AND pc_catname = 'New Patient';
UPDATE openemr_postcalendar_categories SET pc_catname = '2 Re-Visit', pc_catcolor = '#CCFFFF' WHERE pc_catid = 9 AND pc_catname = 'Established Patient';

INSERT INTO `openemr_postcalendar_categories`
 (pc_catid, pc_catname, pc_catcolor, pc_catdesc, pc_recurrtype, pc_enddate, pc_recurrspec, pc_recurrfreq, pc_duration, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_dailylimit) VALUES
 (12,'3 Counselling Only','#FFFFCC','Counselling',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);
INSERT INTO `openemr_postcalendar_categories`
 (pc_catid, pc_catname, pc_catcolor, pc_catdesc, pc_recurrtype, pc_enddate, pc_recurrspec, pc_recurrfreq, pc_duration, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_dailylimit) VALUES
 (13,'4 Supply/Re-Supply','#CCCCCC','Supply/Re-Supply',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);
INSERT INTO `openemr_postcalendar_categories`
 (pc_catid, pc_catname, pc_catcolor, pc_catdesc, pc_recurrtype, pc_enddate, pc_recurrspec, pc_recurrfreq, pc_duration, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_dailylimit) VALUES
 (14,'5 Administrative','#FFFFFF','Supply/Re-Supply',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);

-- The following added 2010-05-12:

INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'full_new_patient_form'       , 0, '3' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'patient_search_results_style', 0, '1' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'simplified_demographics'     , 0, '1' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'online_support_link'         , 0, ''  );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'units_of_measurement'        , 0, '2' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'specific_application'        , 0, '2' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'inhouse_pharmacy'            , 0, '2' );
INSERT INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'configuration_import_export' , 0, '1' );

-- The following added 2010-05-21:

DELETE FROM code_types;
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('MA'  ,12, 1, 0, '', 1, 1, 0, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('IPPF',11, 2, 0, '', 0, 0, 1, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('ICD9', 2, 3, 2, '', 0, 0, 0, 1);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('ACCT',13, 4, 0, '', 0, 0, 1, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('REF' ,16, 5, 0, '', 0, 1, 1, 0);
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag ) VALUES ('ADM' ,17, 6, 0, '', 1, 0, 0, 0);

-- The following revised/added 2010-06-11:

INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFgcac', '' , 'IPPF GCAC'        , 'Clinical');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFgcac', '1', 'Basic Information', ''        );
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFgcac', '2', 'Complications'    , ''        );
DELETE FROM layout_options WHERE form_id = 'LBFgcac';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','client_status','1','Client Status'               , 1,27,2, 0, 0,'clientstatus',1,1,'','' ,'Client Status');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','ab_location'  ,'1','Type of Visit'               , 2,27,2, 0, 0,'ab_location' ,1,1,'','' ,'Nature of this visit');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','in_ab_proc'   ,'1','Associated Induced Procedure', 3,27,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Applies regardless of when or where done');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','complications','2','Complications'               , 1,21,1, 2, 0,'complication',1,3,'','' ,'Post-Abortion Complications');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','main_compl'   ,'2','Main Complication'           , 2, 1,1, 2, 0,'complication',1,3,'','' ,'Primary Complication');

-- The following moved to ippf_c3_layout.sql 2011-01-18:

-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','gc_rreason'   ,'3IPPA CAC Section','Reason when Rejected/Referred', 1, 1,0, 0, 0,'gc_rreason'   ,1,3,'','','Reason for rejecting or referring services');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','gc_reason'    ,'3IPPA CAC Section','Main Reason for MR Services'  , 2, 1,0, 0, 0,'gc_reason'   ,1,3,'','' ,'Main reason for requesting MR services');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','gc_condition' ,'3IPPA CAC Section','Aborted Conception Condition' , 3, 1,0, 0 ,0,'gc_condition',1,3,'','' ,'Condition of Aborted Conception');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','gc_efforts'   ,'3IPPA CAC Section','Efforts Prior to Visit'       , 4, 1,0, 0, 0,'gc_efforts'  ,1,3,'','' ,'Other efforts conducted before visiting the clinic');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFgcac','gc_complaint' ,'3IPPA CAC Section','Complaint from Client'        , 5, 1,0, 0, 0,'gc_complaint',1,3,'','' ,'Complaint from Client');
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','gc_rreason','GCAC Reason to Reject/Refer Services',88);
-- DELETE FROM list_options WHERE list_id = 'gc_rreason';
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','1' ,'Service not available', 1);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','2' ,'Cost of service'      , 2);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','3' ,'Single'               , 3);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','4' ,'Medical reason'       , 4);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','5' ,'No responsible person', 5);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_rreason','6' ,'Weeks of pregnancy'   , 6);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','gc_reason','GCAC Main Reason for MR Services',88);
-- DELETE FROM list_options WHERE list_id = 'gc_reason';
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','1' ,'Have already enough children'                , 1);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','2' ,'The children are still babies'               , 2);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','3' ,'Too young to have baby'                      , 3);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','4' ,'Too old to have other child'                 , 4);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','5' ,'Not / not yet married'                       , 5);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','6' ,'Still goes to school / college'              , 6);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','7' ,'Engage with Official'                        , 7);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','8' ,'Could not stand pain / sickness of pregnancy', 8);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_reason','9' ,'Others'                                      , 9);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','gc_condition','GCAC Aborted Conception Condition',88);
-- DELETE FROM list_options WHERE list_id = 'gc_condition';
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_condition','1' ,'Fresh' , 1);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_condition','2' ,'Dark'  , 2);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_condition','3' ,'Sticky', 3);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_condition','4' ,'Others', 4);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_condition','5' ,'N/A'   , 0);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','gc_efforts','GCAC Prior Efforts',88);
-- DELETE FROM list_options WHERE list_id = 'gc_efforts';
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','1' ,'None'                                         , 1);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','2' ,'Drinking herbs / medicines'                   , 2);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','3' ,'Had been taken care by paramedic'             , 3);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','4' ,'Massage / went to traditional birth attendant', 4);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','5' ,'Other efforts'                                , 5);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_efforts','6' ,'Emergency'                                    , 6);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','gc_complaint','GCAC Complaint from Client',88);
-- DELETE FROM list_options WHERE list_id = 'gc_complaint';
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_complaint','1' ,'Facility'         , 1);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_complaint','2' ,'Time of Queue'    , 2);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_complaint','3' ,'Charge of Service', 3);
-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('gc_complaint','4' ,'None'             , 4);

DELETE FROM list_options WHERE list_id = 'clientstatus';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','maaa'  ,'MA Client Accepting Abortion', 1,1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','mara'  ,'MA Client Refusing Abortion' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','refin' ,'Inbound Referral'            , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','self'  ,'Self Referred'               , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','defer' ,'Deferring / Undecided'       , 5,0,0);

DELETE FROM list_options WHERE list_id = 'ab_location';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','proc' ,'Procedure at this site'              , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','ma'   ,'Followup procedure from this site'   , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','part' ,'Followup procedure from partner site', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','oth'  ,'Followup procedure from other site'  , 4,0,0);

-- The following revised/added 2010-07-13:

-- DELETE FROM layout_options WHERE form_id = 'REF';
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_date'        ,'1Referral','Referral Date'                  , 5, 4,2, 0,  0,''         ,1,1,'C','D','Date of referral');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_from'        ,'1Referral','Referred By'                    ,10,10,2, 0,  0,''         ,1,1,'' ,'' ,'Referral By');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_external'    ,'1Referral','External Referral'              ,15, 1,2, 0,  0,'boolean'  ,1,1,'' ,'' ,'External referral?');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_to'          ,'1Referral','Referred To'                    ,20,14,2, 0,  0,''         ,1,1,'' ,'' ,'Referral To');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','body'              ,'1Referral','Reason'                         ,25, 3,2,30,  3,''         ,1,1,'' ,'' ,'Reason for referral');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_risk_level'  ,'1Referral','Risk Level'                     ,30, 1,1, 0,  0,'risklevel',1,1,'' ,'' ,'Level of urgency');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_vitals'      ,'1Referral','Include Vital Signs'            ,35, 1,1, 0,  0,'boolean'  ,1,1,'' ,'' ,'Include vitals data?');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_reply_date'  ,'1Referral','Expected Reply Date'            ,40, 4,2, 0,  0,''         ,1,1,'' ,'D','Expected date of reply');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_related_code','1Referral','Requested Service'              ,45,15,2,30,255,''         ,1,1,'' ,'' ,'Billing Code for Requested Service');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','refer_diag'        ,'1Referral','Preliminary Diagnosis'          ,50, 2,1,30,255,''         ,1,1,'' ,'X','Referrer diagnosis');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_date'        ,'2Counter-Referral','Reply Date'             , 5, 4,1, 0,  0,''         ,1,1,'' ,'D','Date of reply');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_from'        ,'2Counter-Referral','Reply From'             ,10, 2,1,30,255,''         ,1,1,'' ,'' ,'Who replied?');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_init_diag'   ,'2Counter-Referral','Presumed Diagnosis'     ,15, 2,0,30,255,''         ,1,1,'' ,'' ,'Presumed diagnosis by specialist');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_final_diag'  ,'2Counter-Referral','Final Diagnosis'        ,20, 2,1,30,255,''         ,1,1,'' ,'' ,'Final diagnosis by specialist');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_documents'   ,'2Counter-Referral','Documents'              ,25, 2,1,30,255,''         ,1,1,'' ,'' ,'Where may related scanned or paper documents be found?');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_findings'    ,'2Counter-Referral','Findings'               ,30, 3,1,30,  3,''         ,1,1,'' ,'' ,'Findings by specialist');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_services'    ,'2Counter-Referral','Services Provided'      ,35, 3,0,30,  3,''         ,1,1,'' ,'' ,'Service provided by specialist');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_related_code','2Counter-Referral','Service Provided'       ,40,15,1,30,255,''         ,1,1,'' ,'' ,'Billing Code for actual services provided');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_recommend'   ,'2Counter-Referral','Recommendations'        ,45, 3,1,30,  3,''         ,1,1,'' ,'' ,'Recommendations by specialist');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('REF','reply_rx_refer'    ,'2Counter-Referral','Prescriptions/Referrals',50, 3,1,30,  3,''         ,1,1,'' ,'' ,'Prescriptions and/or referrals by specialist');

-- The following added 2010-08-11:

ALTER TABLE patient_data
  ADD usertext11 varchar(255) NOT NULL DEFAULT '',
  ADD usertext12 varchar(255) NOT NULL DEFAULT '',
  ADD usertext13 varchar(255) NOT NULL DEFAULT '',
  ADD usertext14 varchar(255) NOT NULL DEFAULT '',
  ADD usertext15 varchar(255) NOT NULL DEFAULT '',
  ADD usertext16 varchar(255) NOT NULL DEFAULT '',
  ADD usertext17 varchar(255) NOT NULL DEFAULT '',
  ADD usertext18 varchar(255) NOT NULL DEFAULT '',
  ADD usertext19 varchar(255) NOT NULL DEFAULT '',
  ADD usertext20 varchar(255) NOT NULL DEFAULT '';

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext11', '6', 'User Defined Text 11', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext12', '6', 'User Defined Text 12', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext13', '6', 'User Defined Text 13', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext14', '6', 'User Defined Text 14', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext15', '6', 'User Defined Text 15', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext16', '6', 'User Defined Text 16', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext17', '6', 'User Defined Text 17', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext18', '6', 'User Defined Text 18', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext19', '6', 'User Defined Text 19', 8,2,0,10,63,'',1,1,'','','User Defined');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('DEM', 'usertext20', '6', 'User Defined Text 20', 8,2,0,10,63,'',1,1,'','','User Defined');

-- The following added 2010-12-01:

INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','actorest','Actual or Estimated', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('actorest','act'  ,'Actual'   ,10,1);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('actorest','est'  ,'Estimated',20,0);
UPDATE layout_options SET group_id = '1', title='', seq = 7, data_type = 1,
  uor = 1, fld_length = 0, list_id = 'actorest', titlecols = 0, datacols = 0,
  description = 'Indicates if DOB is estimated' WHERE
  form_id = 'DEM' AND field_id = 'usertext3' AND uor = 0;

-- The following added 2011-04-04:

-- UPDATE layout_options SET data_type = 14 WHERE form_id = 'REF' AND field_id = 'refer_from';
-- UPDATE layout_options SET title='Referral Type', list_id = 'reftype'  WHERE form_id = 'REF' AND field_id = 'refer_external' AND list_id = 'boolean';

-- The following added 2011-06-09 and changed 2017-03-24:

insert into lang_definitions ( cons_id, lang_id, definition )
  select lc.cons_id, 1, 'DHIS2 Code' from lang_constants as lc
  left join lang_definitions as ld on ld.cons_id = lc.cons_id and ld.lang_id = 1
  where lc.constant_name = 'CLIA Number' and ld.cons_id is null;

-- The following re-added 2011-08-15 because LV asked for it:

INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','posref','Channels of Distribution', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','01','Static Clinic'         ,01,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','02','Mobile/Outreach Clinic',02,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','03','Associated Clinics'    ,03,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','04','Private Physicians'    ,04,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','05','CBD / CBS'             ,05,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','06','MA Social Marketing'   ,06,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','07','Commercial Marketing'  ,07,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','08','Government'            ,08,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('posref','09','Other Agencies'        ,09,0);

INSERT INTO lang_constants ( constant_name ) VALUES ( 'POS Code' );
INSERT INTO lang_definitions ( cons_id, lang_id, definition ) SELECT lc.cons_id, 1, 'COD Code' FROM lang_constants AS lc LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND ld.lang_id = 1 WHERE lc.constant_name = 'POS Code' AND ld.cons_id IS NULL;

-- The following added 2011-09-21:

-- These lines mirror line-for-line the spreadsheet "CYP Factors 2010.1.xlsx".
UPDATE codes SET cyp_factor = 0.0666667 WHERE code_type = 11 AND code LIKE '11110_%';
UPDATE codes SET cyp_factor = 0.0769230 WHERE code_type = 11 AND code LIKE '111111%';
UPDATE codes SET cyp_factor = 0.1666667 WHERE code_type = 11 AND code LIKE '111112%';
UPDATE codes SET cyp_factor = 0.2500000 WHERE code_type = 11 AND code LIKE '111113%';
UPDATE codes SET cyp_factor = 3.5000000 WHERE code_type = 11 AND code LIKE '111122%';
UPDATE codes SET cyp_factor = 3.5000000 WHERE code_type = 11 AND code LIKE '111123%';
UPDATE codes SET cyp_factor = 2.5000000 WHERE code_type = 11 AND code LIKE '111124%';
UPDATE codes SET cyp_factor = 0.0666667 WHERE code_type = 11 AND code LIKE '111132%';
UPDATE codes SET cyp_factor = 0.0666667 WHERE code_type = 11 AND code LIKE '111133%';
UPDATE codes SET cyp_factor = 0.0083333 WHERE code_type = 11 AND code LIKE '112141%';
UPDATE codes SET cyp_factor = 0.0083333 WHERE code_type = 11 AND code LIKE '112142%';
UPDATE codes SET cyp_factor = 1.0000000 WHERE code_type = 11 AND code LIKE '112151%';
UPDATE codes SET cyp_factor = 1.0000000 WHERE code_type = 11 AND code LIKE '112152%';
UPDATE codes SET cyp_factor = 0.1333333 WHERE code_type = 11 AND code LIKE '112161%';
UPDATE codes SET cyp_factor = 0.1333333 WHERE code_type = 11 AND code LIKE '112162%';
UPDATE codes SET cyp_factor = 0.1333333 WHERE code_type = 11 AND code LIKE '112163%';
UPDATE codes SET cyp_factor = 0.1333333 WHERE code_type = 11 AND code LIKE '112164%';
UPDATE codes SET cyp_factor = 0.1333333 WHERE code_type = 11 AND code LIKE '112165%';
UPDATE codes SET cyp_factor = 3.5000000 WHERE code_type = 11 AND code LIKE '113171%';
UPDATE codes SET cyp_factor = 3.5000000 WHERE code_type = 11 AND code LIKE '113172%';
UPDATE codes SET cyp_factor = 10.000000 WHERE code_type = 11 AND code LIKE '121181%';
UPDATE codes SET cyp_factor = 10.000000 WHERE code_type = 11 AND code LIKE '122182%';
UPDATE codes SET cyp_factor = 0.0500000 WHERE code_type = 11 AND code LIKE '145212%';
-- Next line clears cyp for codes corresponding to removal of contraception.
UPDATE codes SET cyp_factor = 0         WHERE code_type = 11 AND code LIKE '1_____112';

INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','ippfconmeth','IPPF Contraceptive Methods', 1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111101110','COC & POC',1,0,0,'Pills');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111111110','Injectable (1 month)',2,0,0,'Injectables');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111112110','Injectable (2 months)',3,0,0,'Injectables');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111113110','Injectables (3 months)',4,0,0,'Injectables');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111122110','Implants 6 rods',5,0,0,'Implants');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111123110','Implants 2 rods',6,0,0,'Implants');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111124110','Implants 1 rod',7,0,0,'Implants');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111132110','Transdermal Patch (1 month)',8,0,0,'Hormonal Other');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','111133110','Vaginal Ring (1 month)',9,0,0,'Hormonal Other');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112141110','Male Condom',10,0,0,'Barrier');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112142110','Female Condom',11,0,0,'Barrier');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112151110','Diaphragm',12,0,0,'Barrier');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112152010','Cervical Cap',13,0,0,'Barrier');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112161110','Spermicides - Foam Tabs/Tube/Suppositories',14,0,0,'Spermicides');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112162110','Spermicides - Foam Tabs/Strip',15,0,0,'Spermicides');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112163110','Spermicides - Foam Cans',16,0,0,'Spermicides');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112164110','Spermicides - Cream & Jelly',17,0,0,'Spermicides');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','112165110','Spermicides - Pessaries / C-film',18,0,0,'Spermicides');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','113171110','Hormone releasing IUD (5 years)',19,0,0,'IUD');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','113172110','Copper releasing IUD (10 years)',20,0,0,'IUD');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','121181213','Female VSC - Minilaparatomy',21,0,0,'Female VSC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','121181313','Female VSC - Laparoscopy',22,0,0,'Female VSC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','121181413','Female VSC - Laparotomy',23,0,0,'Female VSC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','122182213','Male VSC - Incisional vasectomy',24,0,0,'Male VSC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','122182313','Male VSC - No-scalpel Vasectomy',25,0,0,'Male VSC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','145212110','Emergency Contraception',26,0,0,'EC');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`) VALUES ('ippfconmeth','NoMethod','No Method',30,0,0,'No Method');

-- The following revised/added 2011-10-12 and then revoked before release:

-- INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lbfnames','LBFcontra','Contraception',1);
-- DELETE FROM layout_options WHERE form_id = 'LBFcontra';
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFcontra','contratype' ,'1','Action'    , 1,1,2, 0, 0,'contratype' ,1,3,'','' ,'Contraception action');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFcontra','ippfconmeth','1','Method'    , 2,1,1, 0, 0,'ippfconmeth',1,3,'','' ,'Contraception method');
-- INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`) VALUES ('LBFcontra','contrastart','1','Start Date', 3,4,1,10,10,''           ,1,3,'','D','Contraception start date');
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','contratype','Contraception Event Types', 1,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('contratype','2','Starting contractption at association' ,2,0);
-- INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('contratype','4','Method change',4,0);

-- The following revised/added 2012-01-05:

INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFccicon', '' , 'Contraception', 'Clinical');
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFccicon', '1', ''             , ''        );
DELETE FROM layout_options WHERE form_id = 'LBFccicon';
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'newmauser', '1', 'First contraceptive at this clinic?',
  1,  1, 2, 0, 0, 'boolean'    , 1, 3, '', '', 'Is this the first contraceptive accepted at this clinic?');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'curmethod', '1', 'Current Method',
  2,  1, 1, 0, 0, 'contrameth' , 1, 3, '', '', 'Method in use at start of visit');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'pastmodern','1', 'Previous modern contraceptive use?',
  3,  1, 1, 0, 0, 'boolean'    , 1, 3, '', '', 'Was a modern contraceptive method used at some time in the past?');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'reqmethod', '1', 'Requested Method',
  4,  1, 1, 0, 0, 'contrameth' , 1, 3, '', '', 'Method requested by the client');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'newmethod', '1', 'Adopted Method',
  5,  1, 1, 0, 0, 'ippfconmeth', 1, 3, '', '', 'Method adopted in this visit');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'provider' , '1', 'Service Provider',
  6, 10, 1, 0, 0, ''           , 1, 3, '', '', 'Provider of this initial consultation');
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('LBFccicon', 'mcreason' , '1', 'Reason for Method Change',
  7,  1, 1, 0, 0, 'mcreason'   , 1, 3, '', '', 'Reason for method change');

-- The following added 2012-03-17:

-- Set group names for IPPF contraceptive methods.
UPDATE list_options SET mapping = 'Pills'          WHERE list_id = 'ippfconmeth' AND option_id LIKE '11110%'  AND mapping = '';
UPDATE list_options SET mapping = 'Injectables'    WHERE list_id = 'ippfconmeth' AND option_id LIKE '11111%'  AND mapping = '';
UPDATE list_options SET mapping = 'Implants'       WHERE list_id = 'ippfconmeth' AND option_id LIKE '11112%'  AND mapping = '';
UPDATE list_options SET mapping = 'Hormonal Other' WHERE list_id = 'ippfconmeth' AND option_id LIKE '11113%'  AND mapping = '';
UPDATE list_options SET mapping = 'Barrier'        WHERE list_id = 'ippfconmeth' AND option_id LIKE '11214%'  AND mapping = '';
UPDATE list_options SET mapping = 'Barrier'        WHERE list_id = 'ippfconmeth' AND option_id LIKE '11215%'  AND mapping = '';
UPDATE list_options SET mapping = 'Spermicides'    WHERE list_id = 'ippfconmeth' AND option_id LIKE '11216%'  AND mapping = '';
UPDATE list_options SET mapping = 'IUD'            WHERE list_id = 'ippfconmeth' AND option_id LIKE '11317%'  AND mapping = '';
UPDATE list_options SET mapping = 'Female VSC'     WHERE list_id = 'ippfconmeth' AND option_id LIKE '121181%' AND mapping = '';
UPDATE list_options SET mapping = 'Male VSC'       WHERE list_id = 'ippfconmeth' AND option_id LIKE '121182%' AND mapping = '';
UPDATE list_options SET mapping = 'EC'             WHERE list_id = 'ippfconmeth' AND option_id LIKE '14521%'  AND mapping = '';

-- Set flags to indicate which are the modern conraceptive methods.
UPDATE list_options SET option_value = 0 WHERE list_id = 'contrameth';
UPDATE list_options SET option_value = 1 WHERE list_id = 'contrameth' AND mapping LIKE '%:1%';

-- The following added 2012-12-30:

INSERT INTO code_types (ct_key,ct_id,ct_seq,ct_mod,ct_just,ct_fee,ct_rel,ct_nofs,ct_diag) VALUES ('IPPF2',31,7,0,'',0,0,1,0);
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1110010900000', '', 'Contraceptives - Counselling - General' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1121120000000', '', 'Oral Contraceptives - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1122120000000', '', 'Injectable - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1123020000000', '', 'Patch / Ring - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1124020000800', '', 'Male / Female condom - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1124120000000', '', 'Male condom - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1124220000000', '', 'Female condom - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1125020000000', '', 'Diaphragm / Cervical cap - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1126020000000', '', 'Spermicides - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1131020211000', '', 'Implant - Consultation - Removal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1131120000000', '', 'Implant - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1132020211000', '', 'IUD - Consultation - Removal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1132120000000', '', 'IUD - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141000000800', '', 'F / MVSC - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141110000000', '', 'FVSC - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141120000000', '', 'FVSC - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302101', '', 'FVSC - Management - Surgical - Reversal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302102', '', 'FVSC - Management - Surgical - Minilaparotomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302103', '', 'FVSC - Management - Surgical - Laparoscopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302104', '', 'FVSC - Management - Surgical - Laparotomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302105', '', 'FVSC - Management - Surgical - Hysteroscopy (ESSURE®)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302106', '', 'FVSC - Management - Surgical - Trans vaginal tubal ligation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130302107', '', 'FVSC - Management - Surgical - FVSC follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1141130000800', '', 'FVSC - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142010000000', '', 'MVSC - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142020000000', '', 'MVSC - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142030302101', '', 'MVSC - Management - Surgical - Reversal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142030302201', '', 'MVSC - Management - Surgical - Incisional Vasectomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142030302202', '', 'MVSC - Management - Surgical - No-scalpel Vasectomy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142030302203', '', 'MVSC - Management - Surgical - MVSC follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1142030000800', '', 'MVSC - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1151010000000', '', 'EC - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1151020000000', '', 'EC - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '1210020000000', '', 'FAB - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2111010121000', '', 'Abortion - Counselling - Pre-abortion / Options Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2111010122000', '', 'Abortion - Counselling - Post-abortion' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2111010000800', '', 'Abortion - Counselling - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2112020200000', '', 'Abortion - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2112020201101', '', 'Abortion - Consultation - Initial consultation - Harm reduction model' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2112020202101', '', 'Abortion - Consultation - Follow up consultation - Harm reduction model' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2112020000800', '', 'Abortion - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301101', '', 'Abortion - Management - Medical - Misoprostol' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301102', '', 'Abortion - Management - Medical - Mifepristone and misoprostol' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301103', '', 'Abortion - Management - Medical - Methotrexate and misoprostol' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301110', '', 'Abortion - Management - Medical - Treatment of complications' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301800', '', 'Abortion - Management - Medical -  Unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113130301104', '', 'Abortion - Management - Medical - follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302301', '', 'Abortion - Management - Surgical - D&C' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302302', '', 'Abortion - Management - Surgical - D&E' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302304', '', 'Abortion - Management - Surgical - Vacuum aspiration' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302305', '', 'Abortion - Management - Surgical - Ethacridine lactate' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302310', '', 'Abortion - Management - Surgical - Treatment of complications' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302800', '', 'Abortion - Management - Surgical - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2113230302307', '', 'Abortion - Management - Surgical - follow up' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2114030301101', '', 'Incomplete abortion - Management - Medical - Misoprostol' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2114030302303', '', 'Incomplete abortion - Management - Surgical - D&C or D&E' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2114030302304', '', 'Incomplete abortion - Management - Surgical - Vacuum aspiration' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2114030302800', '', 'Incomplete abortion - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2121010111000', '', 'HIV and AIDS - Counselling - Pre-test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2121010112000', '', 'HIV and AIDS - Counselling - Post-test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2121010123000', '', 'HIV and AIDS - Counselling - Risk reduction' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2121010124000', '', 'HIV and AIDS - Counselling - Psycho-social support' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2121010000800', '', 'HIV and AIDS - Counselling - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2122020200000', '', 'HIV and AIDS - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2123030301301', '', 'HIV and AIDS - Management - Medical - ARVs' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2123030301501', '', 'HIV and AIDS - Management - Medical - OI (TB)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2123030301502', '', 'HIV and AIDS - Management - Medical - OI (Malaria)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2123030301509', '', 'HIV and AIDS - Management - Medical - OI (Other)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2123030000800', '', 'HIV and AIDS - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2124040401101', '', 'HIV and AIDS - Prevention - Prophylaxis - ARVs' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050502000', '', 'HIV and AIDS - Investigation - Examination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503101', '', 'HIV and AIDS - Investigation - Lab test - Diagnostic Ab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503102', '', 'HIV and AIDS - Investigation - Lab test - Diagnostic Ag test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503103', '', 'HIV and AIDS - Investigation - Lab test - Diagnostic PCR test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503104', '', 'HIV and AIDS - Investigation - Lab test - Diagnostic Rapid test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503105', '', 'HIV and AIDS - Investigation - Lab test - Monitoring viral load test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503106', '', 'HIV and AIDS - Investigation - Lab test - Monitoring CD4 count test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050503888', '', 'HIV and AIDS - Investigation - Lab test - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2125050504000', '', 'HIV and AIDS - Investigation - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2131010111000', '', 'STI / RTI - Counselling - Pre-test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2131010112000', '', 'STI / RTI - Counselling - Post-test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2131010123000', '', 'STI / RTI - Counselling - Risk reduction' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2131010000800', '', 'STI / RTI - Counselling - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2132020200000', '', 'STI / RTI - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133130303000', '', 'STI/RTI - Management - Syndromic' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304000', '', 'STI/RTI - Management - Etiological - Other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304101', '', 'STI/RTI - Management - Etiological - Chlamydia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304102', '', 'STI/RTI - Management - Etiological - Chancroid' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304103', '', 'STI/RTI - Management - Etiological - Gonorrhoea' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304104', '', 'STI/RTI - Management - Etiological - Syphilis' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304105', '', 'STI/RTI - Management - Etiological - Human Papillomavirus (HPV)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2133230304800', '', 'STI/RTI - Management - Etiological - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2134040401201', '', 'STI/RTI - Prevention - Prophylaxis - Hep A vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2134040401202', '', 'STI/RTI - Prevention - Prophylaxis - Hep B vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2134040401203', '', 'STI/RTI - Prevention - Prophylaxis - HPV vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2134040401800', '', 'STI / RTI - Prevention - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2135050502000', '', 'STI/RTI - Investigation - Examination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2135050503000', '', 'STI/RTI - Investigation - Lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2135050504000', '', 'STI/RTI - Investigation - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2135050505000', '', 'STI/RTI - Investigation - Lab test - Human Papilloma virus (HPV)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2135050000800', '', 'STI / RTI - Investigation - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2141010111101', '', 'Gynecology - Counselling - Pre test - Cervical cancer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2141010112101', '', 'Gynecology - Counselling - Post test - Cervical cancer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2141010900102', '', 'Gynecology - Counselling - General - Breast cancer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2141010900999', '', 'Gynecology - Counselling - General - other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2141010000800', '', 'Gynecology - Counselling - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2142020200000', '', 'Gynecology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2142020000800', '', 'Gynecology - Consultation - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143130301201', '', 'Gynecology - Management - Medical - Menstrual Regulation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143130301202', '', 'Gynecology - Management - Medical - Erratic mensturation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143130301999', '', 'Gynecology - Management - Medical - Other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143130000800', '', 'Gynecology - Management - Medical - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302401', '', 'Gynecology - Management - Surgical - Cervical Cancer Related - Cryosurgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302402', '', 'Gynecology - Management - Surgical - Cervical Cancer Related - Cauterization' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302501', '', 'Gynecology - Management - Surgical - cervical cancer related - Loop Electrosurgical Excision Procedure (LEEP)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302701', '', 'Gynecology - Management - Surgical - Breast cancer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302306', '', 'Gynecology - Management - Surgical - Menstrual Regulation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302999', '', 'Gynecology - Management - Surgical - other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2143230302800', '', 'Gynecology - Management - Surgical - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2144040402101', '', 'Gynecology - Prevention - Screening - PAP (sampling procedure)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2144040402102', '', 'Gynecology - Prevention - Screening - PAP (lab test)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2144040402103', '', 'Gynecology - Prevention - Screening - Visual inspection (VIA or VILI)' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2144040402800', '', 'Gynecology - Prevention - Screening - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050501000', '', 'Gynecology - Investigation - Diagnostic Imaging - other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050501101', '', 'Gynecology - Investigation - Diagnostic Imaging - Mamography' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050501102', '', 'Gynecology - Investigation - Diagnostic Imaging - Colposcopy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050502101', '', 'Gynecology - Investigation - Examination - Manual breast exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050502102', '', 'Gynecology - Investigation - Examination - Bimanual pelvic exam' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050502999', '', 'Gynecology - Investigation - Examination - other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050503000', '', 'Gynecology - Investigation - Lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050504000', '', 'Gynecology - Investigation - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2145050000800', '', 'Gynecology - Investigation - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2151010131000', '', 'Obstetrics - Counselling - Ante natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2151010132000', '', 'Obstetrics - Counselling - Post natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2151010000800', '', 'Obstetrics - Counselling - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2152020221000', '', 'Obstetrics - Consultation - Ante natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2152020222000', '', 'Obstetrics - Consultation - Post natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2152020000800', '', 'Obstetrics - Consultation - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2153130301401', '', 'Obstetrics -Management - Medical - Vaginal Delivery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2153130301402', '', 'Obstetrics -Management - Medical - EmOC' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2153130301800', '', 'Obstetrics - management - Medical - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2153230302501', '', 'Obstetrics - Management - Surgical - C-Section' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2153230302800', '', 'Obstetrics - Management - Surgical - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2154040401301', '', 'Obstetrics - Prevention - Prophylaxis - Ante-natal vaccinations' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050501201', '', 'Obstetrics - Investigations - Diagnostic imaging - Ante natal ultrasound' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050501202', '', 'Obstetrics - Investigations - Diagnostic imaging - Post natal ultrasound' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2152088000000', '', 'Obstetrics - Investigations - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050502201', '', 'Obstetrics - Investigations - Examination - Ante natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050503202', '', 'Obstetrics - Investigations - Examination - Post natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050503301', '', 'Obstetrics - Investigations - Lab tests - Pregnancy test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050503401', '', 'Obstetrics - Investigations - Lab tests - Ante natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050503402', '', 'Obstetrics - Investigations - Lab tests - Post natal' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2155050504000', '', 'Obstetrics - Investigations - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2161010100000', '', 'Urology - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2162020200000', '', 'Urology - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2163130301000', '', 'Urology - Management - Medical' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2163230302601', '', 'Urology - Management - Surgery - Male Circumcision' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2163230302999', '', 'Urology - Management - Surgery - other' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2163230000800', '', 'Urology - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2164040402201', '', 'Urology - Prevention - Screening - Prostate cancer' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2165050501000', '', 'Urology - Investigations - Diagnostic Imaging' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2165050502000', '', 'Urology - Investigations - Examination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2165050503000', '', 'Urology - Investigations - Lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2165050504000', '', 'Urology - Investigations - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2165050000800', '', 'Urology - Investigations - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2171110000000', '', 'Subfertility - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2171110000800', '', 'Subfertility - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2172120000000', '', 'Subfertility - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2173130301203', '', 'Subfertility - Management - Medical - Hormone / ovulation therapy' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2173130301403', '', 'Subfertility - Management - Medical - Assisted Conception' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2173130301800', '', 'Subfertility - Management - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2173230302000', '', 'Subfertility - Management - Surgery' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2174040501000', '', 'Subfertility - Investigations - Diagnostic imaging' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2174040502000', '', 'Subfertility - Investigations - Examination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2174040503000', '', 'Subfertility - Investigations - Lab test' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2174040504000', '', 'Subfertility - Investigations - Sampling procedure' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2174040000800', '', 'Subfertility - Investigations - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2181110141000', '', 'Specialised SRH services - Counselling - GBV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2181210142000', '', 'Specialised SRH services - Counselling - Relationship' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2181310143000', '', 'Specialised SRH services - Counselling - Sexuality' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2184140100301', '', 'Specialised SRH services - Prevention - Screening - GBV' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2184100000800', '', 'Specialised SRH Services - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2191010000000', '', 'Paediatrics - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2192020000000', '', 'Paediatrics - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2192020900401', '', 'Paediatrics - Consultation - General - obesity' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2192020900999', '', 'Paediatrics - Consultation - General - all other non-obesity' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2193030000000', '', 'Paediatrics - Management' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2193030101205', '', 'Paediatrics - Management - Medical - Asthma' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2193030101999', '', 'Paediatrics - Management - Medical - all other non-asthma' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2194040000000', '', 'Paediatrics - Prevention' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2194040401401', '', 'Paediatrics - Prevention - Prophylaxis - Vaccination' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2195050000000', '', 'Paediatrics - Investigations' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2196000000800', '', 'Paediatrics - unable to categorise' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2201010000000', '', 'SRH - Other - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2202020000000', '', 'SRH - Other - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2203030000000', '', 'SRH - Other - Management' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2204040000000', '', 'SRH - Other - Prevention' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '2205050000000', '', 'SRH - Other - Investigation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '4100000000000', '', 'Administration' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010000000', '', 'Non-SRH Medical - Counselling' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010900201', '', 'Non-SRH Medical - Counselling - General - Diabetes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010900202', '', 'Non-SRH Medical - Counselling - General - Hypertension' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010900203', '', 'Non-SRH Medical - Counselling - General - Hyperlipidemia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010900301', '', 'Non-SRH Medical - Counselling - General - Mental health' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010900401', '', 'Non-SRH Medical - Counselling - General - Obesity' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010123201', '', 'Non-SRH Medical - Counselling - Risk reduction - Diabetes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010123202', '', 'Non-SRH Medical - Counselling - Risk reduction - Hypertension' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010123203', '', 'Non-SRH Medical - Counselling - Risk reduction - Hyperlipidemia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010123204', '', 'Non-SRH Medical - Counselling - Risk reduction - COPD' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3110010123501', '', 'Non-SRH Medical - Counselling - Risk reduction - Alcohol' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3120020000000', '', 'Non-SRH Medical - Consultation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3120020900201', '', 'Non-SRH Medical - Consultation - General - Diabetes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3120020900202', '', 'Non-SRH Medical - Consultation - General - Hypertension' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3120020900203', '', 'Non-SRH Medical - Consultation - General - Hyperlipidemia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030000000', '', 'Non-SRH Medical - Management' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030101201', '', 'Non-SRH Medical - Management - Medical - Diabetes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030101202', '', 'Non-SRH Medical - Management - Medical - Hypertension' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030101203', '', 'Non-SRH Medical - Management - Medical - Hyperlipidemia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030101204', '', 'Non-SRH Medical - Management - Medical - COPD' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3130030101301', '', 'Non-SRH Medical - Management - Medical - Mental health' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3140040000000', '', 'Non-SRH Medical - Prevention' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3140040402201', '', 'Non-SRH Medical - Prevention - Screening - Diabetes' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3140040402202', '', 'Non-SRH Medical - Prevention - Screening - Hypertension' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3140040402203', '', 'Non-SRH Medical - Prevention - Screening - Hyperlipidemia' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3150050000000', '', 'Non-SRH Medical - Investigation' );
INSERT INTO codes ( code_type, code, modifier, code_text ) VALUES ( 31, '3150000000800', '', 'Non-SRH Medical - unable to categorise' );

INSERT INTO code_types (ct_key,ct_id,ct_seq,ct_mod,ct_just,ct_fee,ct_rel,ct_nofs,ct_diag) VALUES ('IPPFCM',32,8,0,'',0,0,1,0);
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4360','', 0.066667,'or' ,'Oral Contraceptives (combined)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4361','', 0.066667,'or' ,'Oral Contraceptives (progestin only)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4370','', 0.076923,'inj','Injectables (1 month)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4380','', 0.166667,'inj','Injectables (2 month)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4390','', 0.250000,'inj','Injectables (3 month)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4400','', 3.800000,'imp','Implants (5 year)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4410','', 3.200000,'imp','Implants (4 year)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4420','', 2.500000,'imp','Implants (3 year)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4430','', 0.066667,'pat','Patch');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4440','', 0.066667,'pat','Ring');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4450','', 0.008333,'con','Condoms (Male)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4460','', 0.008333,'con','Condoms (Female)');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4470','', 1.000000,'dia','Diapraghms');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4480','', 1.000000,'cap','Cervical Caps');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4490','', 0.133333,'sp' ,'Spermicides');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4540','', 3.300000,'iud','IUD Hormone 5 yr');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4550','', 4.600000,'iud','IUD Copper 10 yr');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4560','',10.000000,'vsc','Voluntary Surgical Contraception - Female');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4570','',10.000000,'vsc','Voluntary Surgical Contraception - Male');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4580','', 0.000000,'fab','Awareness-Based Methods - CMM');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4590','', 0.000000,'fab','Awareness-Based Methods - CBM');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4600','', 0.000000,'fab','Awareness-Based Methods - STM');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4610','', 0.000000,'fab','Awareness-Based Methods - SDM');
INSERT INTO codes (code_type,code,modifier,cyp_factor,code_text_short,code_text) VALUES (32,'4620','', 0.050000,'ec' ,'Emergency Contraception (progestin only pills)');

-- Assign related codes to IPPFCM codes for statistical reporting purposes.
UPDATE codes SET related_code = 'IPPF:111101110;IPPF2:1121120000000' WHERE code_type = 32 AND code = '4360';
UPDATE codes SET related_code = 'IPPF:111101110;IPPF2:1121120000000' WHERE code_type = 32 AND code = '4361';
UPDATE codes SET related_code = 'IPPF:111111110;IPPF2:1122120000000' WHERE code_type = 32 AND code = '4370';
UPDATE codes SET related_code = 'IPPF:111112110;IPPF2:1122120000000' WHERE code_type = 32 AND code = '4380';
UPDATE codes SET related_code = 'IPPF:111113110;IPPF2:1122120000000' WHERE code_type = 32 AND code = '4390';
UPDATE codes SET related_code = 'IPPF:111122110;IPPF2:1131120000000' WHERE code_type = 32 AND code = '4400';
UPDATE codes SET related_code = 'IPPF:111123110;IPPF2:1131120000000' WHERE code_type = 32 AND code = '4410';
UPDATE codes SET related_code = 'IPPF:111124110;IPPF2:1131120000000' WHERE code_type = 32 AND code = '4420';
UPDATE codes SET related_code = 'IPPF:111132110;IPPF2:1123020000000' WHERE code_type = 32 AND code = '4430';
UPDATE codes SET related_code = 'IPPF:111133110;IPPF2:1123020000000' WHERE code_type = 32 AND code = '4440';
UPDATE codes SET related_code = 'IPPF:112141110;IPPF2:1124120000000' WHERE code_type = 32 AND code = '4450';
UPDATE codes SET related_code = 'IPPF:112142110;IPPF2:1124220000000' WHERE code_type = 32 AND code = '4460';
UPDATE codes SET related_code = 'IPPF:112151110;IPPF2:1125020000000' WHERE code_type = 32 AND code = '4470';
UPDATE codes SET related_code = 'IPPF:112152010;IPPF2:1125020000000' WHERE code_type = 32 AND code = '4480';
UPDATE codes SET related_code = 'IPPF:112160000;IPPF2:1126020000000' WHERE code_type = 32 AND code = '4490';
UPDATE codes SET related_code = 'IPPF:113171110;IPPF2:1132120000000' WHERE code_type = 32 AND code = '4540';
UPDATE codes SET related_code = 'IPPF:113172110;IPPF2:1132120000000' WHERE code_type = 32 AND code = '4550';
UPDATE codes SET related_code = 'IPPF:121181000;IPPF2:1141130000800' WHERE code_type = 32 AND code = '4560';
UPDATE codes SET related_code = 'IPPF:122182000;IPPF2:1142030000800' WHERE code_type = 32 AND code = '4570';
UPDATE codes SET related_code = 'IPPF:145212000;IPPF2:1151020000000' WHERE code_type = 32 AND code = '4620';

-- This came from an export of the new LBF Vital Signs form and its dependent lists.
DELETE FROM list_options WHERE list_id = 'VIT_GenAppear';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'VIT_GenAppear';
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('lists','VIT_GenAppear','VIT_GenAppear',404,1,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','A&O','Alert and oriented',10,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','Let','Lethargy',40,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','Other','Other',100,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','Sigj','Signs of jaundice',60,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','Sigv','Signs/marks of physical violence',50,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','Wek','Weakness',30,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GenAppear','WoA','Without anxiety',20,0,0,'','','');
DELETE FROM list_options WHERE list_id = 'VIT_GlucoseTestType';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'VIT_GlucoseTestType';
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('lists','VIT_GlucoseTestType','VIT_GlucoseTestType',430,1,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','2HR','2 Hour Blood Sugar',3,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','A1C','Hemoglobin A1C',3,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','FBS','Fasting Blood Sugar',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','NS','Not Specified',5,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','OGTT','Oral Glucose Tolerance',3,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','Other','Other',4,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_GlucoseTestType','RBS','Random Blood Sugar',1,0,0,'','','');
DELETE FROM list_options WHERE list_id = 'VIT_TempLocation';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'VIT_TempLocation';
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('lists','VIT_TempLocation','VIT_TempLocation',419,1,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_TempLocation','Axil','Axillary',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_TempLocation','Oral','Oral',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_TempLocation','Rectal','Rectal',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_TempLocation','Temporal','Temporal Artery',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('VIT_TempLocation','Tympanic','Tympanic',0,0,0,'','','');

-- Added 2016-12-31:

INSERT INTO list_options (list_id, option_id, title, seq, is_default) VALUES ('lists','LAB_BloodConcentration','LAB_BloodConcentration', 1,0);
INSERT INTO list_options (list_id, option_id, title, seq, is_default) VALUES ('LAB_BloodConcentration','mg_dl' ,'mg/dl' ,1,1);
INSERT INTO list_options (list_id, option_id, title, seq, is_default) VALUES ('LAB_BloodConcentration','mmol_l','mmol/L',2,0);

INSERT INTO layout_group_properties (grp_form_id, grp_title, grp_mapping, grp_repeats) VALUES ('LBFVitals', 'Vital Signs', 'Clinical', 5);
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFVitals', '1', 'Vitals', '');

INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','DOB'                 ,'1','DOB',165,4,0,0,255,'',1,3,'','DNA0','',0,'D','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_AppearGen'       ,'1','General Appearance',100,21,0,1,255,'VIT_GenAppear',1,3,'','','General Appearance',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_BMI'             ,'1','BMI',80,2,1,10,255,'',1,3,'','G','BMI',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_BMI_status'      ,'1','BMI Status',85,2,1,0,255,'',1,3,'','','BMI status',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_BPDiast'         ,'1','BP Diastolic',40,2,1,10,255,'',1,3,'','G','Blood pressure diastolic',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_BPSyst'          ,'1','BP Systolic',30,2,1,10,255,'',1,3,'','G','Blood pressure systolic',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Glucose'         ,'1','Glucose (mg/dl)',150,2,1,5,255,'',1,3,'','G','Glucose (mg/dl)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Glucose_TestType','1','Glucose Test Type',155,1,1,0,255,'VIT_GlucoseTestType',1,3,'','','Glucose Test Type',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Head_circum_cm'  ,'1','Head Circumference (cm)',180,2,1,10,255,'',1,3,'','G','Head circumference (cms)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Head_circum_in'  ,'1','Head Circumference (In)',170,2,1,10,255,'',1,3,'','G','Head circumference (ins))',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Height_cm'       ,'1','Height (cm)',20,2,1,10,255,'',1,3,'','G','Height (cms)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Height_in'       ,'1','Height (in)',25,2,1,10,255,'',1,3,'','G','Height (ins)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Hip_circum_cm'   ,'1','Hip Circumference (cm)',220,2,0,0,10,'',1,3,'','G','Hip Circumference (cm)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Hip_circum_in'   ,'1','Hip Circumference (in)',210,2,0,0,10,'',1,3,'','G','Hip Circumference (in)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_HR'              ,'1','Heart Rate',60,2,0,10,255,'',1,3,'','G','Heart rate',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_O2_Satur'        ,'1','Oxygen Saturation',75,2,1,10,255,'',1,3,'','G','Oxygen saturation',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Othernotes'      ,'1','Notes',160,2,1,30,255,'',1,3,'','','Other general appearance',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Pulse'           ,'1','Pulse (per min)',65,2,1,10,255,'',1,3,'','G','Pulse per min',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_RespRate'        ,'1','Respiratory Rate',70,2,1,10,255,'',1,3,'','G','Respiratory rate',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_TempC'           ,'1','Temperature (C)',45,2,1,10,255,'',1,3,'','G','Temperature (C)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_TempF'           ,'1','Temperature (F)',50,2,1,10,255,'',1,3,'','G','Temperature (F)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_TempLoc'         ,'1','Temperature Location',55,1,1,0,255,'VIT_TempLocation',1,3,'','','Temperature location',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Waist_circum_cm' ,'1','Waist Circumference (cm)',200,2,1,10,10,'',1,3,'','G','Waist Circumference (cm)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Waist_circum_in' ,'1','Waist Circumference (in)',190,2,1,10,255,'',1,3,'','G','Waist circumference (in)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Weight_kg'       ,'1','Weight (kg)',15,2,1,10,255,'',1,3,'','G','Weight (kgs)',0,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFVitals','VIT_Weight_lb'       ,'1','Weight (lb)',10,2,1,10,10,'',1,3,'','G','Weight (lbs)',0,'F','');

INSERT INTO layout_group_properties (grp_form_id, grp_title, grp_mapping, grp_repeats) VALUES ('LBFVNote', 'Visit Notes', 'Clinical', 5);
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFVNote', '1', 'Visit Notes', '');
DELETE FROM layout_options WHERE form_id = 'LBFVNote';
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`)
  VALUES ('LBFVNote','Notes','1','Notes',20,3,1,50,255,'',1,3,'','','',10,'F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`)
  VALUES ('LBFVNote','Provider','1','Provider',10,10,1,0,0,'',1,3,'','','',0,'F','');

-- Form and associated list added 2016-10-18:

DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'Relation_to_Client';
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('lists','Relation_to_Client','Relation to Client',298,1,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Aunt','Aunt',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Bfriend','Boyfriend',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','BfriendLive','Boyfriend- LiveIn',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','BIL','Brother in Law',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Cousin','Cousin',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Daughter','Daughter',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Ex','Ex-Partner',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','FamFriend','Friend of Family',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Father','Father',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','FIL','Father in Law',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Gfriend','Girlfriend',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','GfriendLive','Girlfriend - LiveIn',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Godfather','Godfather',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Godmother','Godmother',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Grandfather','Grandfather',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Grandmother','Grandmother',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Husband','Husband',0,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','MIL','Mother in Law',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Mother','Mother',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Nephew','Nephew',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Niece','Niece',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Other','Other',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','SIL','Sister in Law',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Son','Son',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Stepdaugh','Step-Daughter',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','StepFather','Step-Father',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','StepMother','Step-Mother',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Stepson','Step-Son',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Uncle','Uncle',1,0,0,'','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES ('Relation_to_Client','Wife','Wife',0,0,0,'','','');

INSERT INTO layout_group_properties (grp_form_id, grp_title, grp_mapping, grp_repeats ) VALUES ('LBFGBV', 'GBV Screening', 'Clinical', 5);
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping) VALUES ('LBFGBV', '1', 'GBV Screening', '');
DELETE FROM layout_options WHERE form_id = 'LBFGBV';
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Child'         ,'1','Were you ever touched inappropriately as a child?',40,1,1,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Child_When'    ,'1','___If so, when?',41,2,1,20,0,'',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Child_Who'     ,'1','___By Whom?',42,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Emotional'     ,'1','Have you ever been emotionally/psychologically  abused?',10,1,2,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Emotional_When','1','___If so, when?',11,2,1,20,0,'',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Emotional_Who' ,'1','___By Whom?',12,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Fear'          ,'1','Are you afraid of being harmed?',71,1,1,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Fear_Who'      ,'1','___By Whom?',72,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Physical'      ,'1','Have you ever been physically abused?',20,1,2,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Physical_When' ,'1','___If so, when?',21,2,1,20,0,'',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Physical_Who'  ,'1','___By Whom?',22,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Pregnancy'     ,'1','Have you been abused since you''ve been pregnant?',60,1,1,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Preg_When'     ,'1','___If so, when?',61,2,1,20,0,'',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Preg_Who'      ,'1','___By Whom?',62,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_SafeHome'      ,'1','Will you be safe when you go home?',70,1,1,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Sexual'        ,'1','Have you ever been sexually abused?',30,1,1,0,0,'yesno',1,3,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Sexual_When'   ,'1','___If so, when?',31,2,1,20,0,'',0,0,'','','',0,'E','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `source`, `conditions`) VALUES ('LBFGBV','GBV_Sexual_Who'    ,'1','___By Whom?',32,1,1,0,0,'Relation_to_Client',0,0,'','','',0,'E','');
