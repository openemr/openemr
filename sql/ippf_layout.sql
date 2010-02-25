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
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'sef','Side Effects of Current Method'   , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'pop','Partner Opposes'                  , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'eco','Economic (cost)'                  , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'com','Method Too Complicated'           , 4,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'med','Medical/Health Condition'         , 5,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'men','Menopause'                        , 6,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'rel','Religious'                        , 7,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'mov','Personal - Moved Away'            , 8,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'prv','Personal - Privacy'               , 9,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oop','Personal - Social/Family Pressure',10,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'con','Personal - Lacks Confidence'      ,11,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'prg','Wants to Become Pregnant'         ,12,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('mcreason'  ,'oth','Other'                            ,13,0,0);

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
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Oral Contraceptives - OC - Method Specific Counselling', '111100119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Oral Contraceptives - OC - OTHER', '111100999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Oral Contraceptives - COC & POC - Initial Consultation', '111101110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Oral Contraceptives - COC & POC - Follow up/Resupply', '111101111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Oral Contraceptives - COC & POC - OTHER', '111101999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Injectable Contraceptives - Method Specific Counselling', '111110119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Injectable Contraceptives - OTHER', '111110999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Combined Injectable Contraceptives (1 month) -  Initial Consultation', '111111110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Combined Injectable Contraceptives (1 month) - Follow up/Resupply', '111111111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Combined Injectable Contraceptives (1 month) - OTHER', '111111999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (2 months) - Initial Consultation', '111112110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (2 months) - Follow up/Resupply', '111112111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (2 months) - OTHER', '111112999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (3 months) - Initial Consultation', '111113110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (3 months) - Follow up/Resupply', '111113111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Progestogen Only Injectables (3 months) - OTHER', '111113999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal Implants - Removal', '111120112', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal Implants - Method Specific Counselling', '111120119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal Implants - OTHER', '111120999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 6 rods - Initial Consultation/Insertion', '111122110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 6 rods - Follow up/Reinsertion', '111122111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 6 rods - OTHER', '111122999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 2 rods - Initial Consultation/Insertion', '111123110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 2 rods - Follow up/Reinsertion', '111123111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 2 rods - OTHER', '111123999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 1 rod - Initial Consultation/Insertion', '111124110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 1 rod - Follow up/Reinsertion', '111124111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Subdermal implants 1 rod - OTHER', '111124999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other hormonal methods - OTHER', '111130999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Transdermal Patch (1 month) - Initial Consultation', '111132110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Transdermal Patch (1 month) - Follow up/Resupply', '111132111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Transdermal Patch (1 month) - Method Specific Counselling', '111132119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Transdermal Patch (1 month) - OTHER', '111132999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Vaginal Ring (1 month) - Initial Consultation', '111133110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Vaginal Ring (1 month) - Follow up/Resupply', '111133111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Vaginal Ring (1 month) - Method Specific Counselling', '111133119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Other methods - Vaginal Ring (1 month) - OTHER', '111133999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms (Male and Female) - OTHER', '112140999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Male Condom - Initial Consultation', '112141110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Male Condom - Follow up/Resupply', '112141111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Male Condom - Method Specific Counselling', '112141119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Male Condom - OTHER', '112141999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Female Condom - Initial Consultation', '112142110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Female Condom - Follow up/Resupply', '112142111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Female Condom - Method Specific Counselling', '112142119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Condoms - Female Condom - OTHER', '112142999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Diaphragm / Cervical Cap - Method Specific Counselling', '112150119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Diaphragm / Cervical Cap - OTHER', '112150999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Diaphragm - Initial Consultation', '112151110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Diaphragm - Follow up/Resupply', '112151111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Diaphragm - OTHER', '112151999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Cervical Cap - Initial Consultation', '112152010', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Cervical Cap - Follow up/Resupply', '112152011', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Cervical Cap - OTHER', '112152999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Method Specific Counselling', '112160119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - OTHER', '112160999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - Initial Consultation', '112161110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - Follow up/Resupply', '112161111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Tube/Suppositories - OTHER', '112161999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Strip - Initial Consultation', '112162110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Strip - Follow up/Resupply', '112162111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Tabs/Strip - OTHER', '112162999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Cans - Initial Consultation', '112163110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Cans - Follow up/Resupply', '112163111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Foam Cans - OTHER', '112163999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Cream & Jelly - Initial Consultation', '112164110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Cream & Jelly - Follow up/Resupply', '112164111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Cream & Jelly - OTHER', '112164999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Pessaries / C-film - Initial Consultation', '112165110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Pessaries / C-film - Follow up/Resupply', '112165111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - Spermicides - Pessaries / C-film - OTHER', '112165999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Removal', '113170112', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Method Specific Counselling', '113170119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - OTHER', '113170999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Hormone releasing IUD (5 years) - Initial Consultation/Insertion', '113171110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Hormone releasing IUD (5 years) - Follow up/Reinsertion', '113171111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Hormone releasing IUD (5 years) - OTHER', '113171999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Copper releasing IUD (10 years) - Initial Consultation/Insertion', '113172110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Copper releasing IUD (10 years) - Follow up/Reinsertion', '113172111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraceptives - IUD - Copper releasing IUD (10 years) - OTHER', '113172999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception - Voluntary Surgical Contraception (VSC) - OTHER', '120180999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Reversal', '121181112', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Method Specific Counselling', '121181119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Minilaparatomy - Follow up', '121181211', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Minilaparatomy - Contraceptive Surgery', '121181213', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Laparoscopy - Follow up', '121181311', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Laparoscopy - Contraceptive Surgery', '121181313', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Laparotomy - Follow up', '121181411', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - Laparotomy - Contraceptive Surgery', '121181413', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Female VSC - OTHER', '121181999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - Reversal', '122182112', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - Method Specific Counselling', '122182119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - Incisional vasectomy - Follow up (Sperm count)', '122182211', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - Incisional vasectomy - Contraceptive Surgery', '122182213', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - No-scalpel Vasectomy - Follow up  (Sperm count)', '122182311', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - No-scalpel Vasectomy - Contraceptive Surgery', '122182313', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception Surgical - Male VSC - OTHER', '122182999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception -  Awareness-Based Methods - OTHER', '130190999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Method Specific Counselling', '131191119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Cervical Mucous Method (CMM) - Initial Consultation/Training', '131191210', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Cervical Mucous Method (CMM) - Follow up/Training', '131191211', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Calendar Based Method (CBM) - Initial Consultation/Training', '131191310', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Calendar Based Method (CBM) - Follow up/Training', '131191311', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Sympto-thermal method - Initial Consultation/Training', '131191410', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Sympto-thermal method - Follow up/Training', '131191411', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Standard days method - Initial Consultation/Training', '131191510', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Standard days method - Follow up/Training', '131191511', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Basal Body Temperature (BBT) - Initial Consultation/Training', '131191610', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception FAB Methods - Basal Body Temperature (BBT) - Follow up/Training', '131191611', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception - FAB Methods - OTHER', '131191999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception - FP General Counselling', '141200118', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception - FP General Counselling - Combined Counselling (FP - HIV/AIDS incl. Dual protection', '141200218', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Contraception - FP General Counselling - OTHER', '141200999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Emergency Contraception Services - OTHER', '145210999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC - Counselling', '145211119', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC - Counselling - OTHER', '145211999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC - Combined Oral Contraceptives - Yuzpe - Contraceptive Supply (Treatment)', '145212110', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC - Combined Oral Contraceptives - Yuzpe - Follow Up', '145212111', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Progestogen Only Pills - Contraceptive Supply (Treatment)', '145212210', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Progestogen Only Pills - Follow Up', '145212211', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Dedicated Product - Contraceptive Supply (Treatment)', '145212310', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Dedicated Product - Follow Up', '145212311', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Copper releasing IUD - DIU Insertion (Treatment)', '145212410', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC Copper releasing IUD - Follow Up', '145212411', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'EC - Therapeutic - OTHER', '145212999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Services - OTHER', '252220999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Counselling - Pre - Abortion Counseling', '252221129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Counselling - Counselling on HIV Testing', '252221229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Counselling - OTHER', '252221999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - Exclusion of Anaemia (Haemoglobin/Hematocrit tests)', '252222121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - Tests for ABO and Rhesus (Rh) blood groups typing', '252222221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - Exclusion of ectopic pregnancy (through ultrasound)', '252222321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - Cervical cytology (Pap smear citology test)', '252222421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - HIV testing', '252222521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Diagnosis - OTHER', '252222999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Induced (Surgical) - Dilatation And Curettage (D&C)', '252223123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Induced (Surgical) - Dilatation And Evacuation (D&E) (2nd trimester of pregnancy)', '252223223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Induced (Surgical) - Manual Vacuum Aspiration (MVA)', '252223323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Induced (Surgical) - OTHER', '252223999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Care - Induced Abortion (Medical) - Pharmaceutical/Medical', '252224122', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Care - Induced Abortion (Medical) - OTHER', '252224999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion (Incomplete Abortion) - Surgical / D&C', '252225123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion (Incomplete Abortion) - Surgical / MVA', '252225223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion  (Incomplete Abortion) - Medical / Pharmaceutical', '252225722', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion (Incomplete Abortion) - Surgical/Medical OTHER', '252225999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Care - Post - Follow-up incl. Uterine Involution Monitoring & Bimanual Pelvic Exam.', '252226120', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Care - Post - OTHER', '252226999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Counselling - Post Abortion Counseling - Including Family Planning', '252227129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Abortion Counselling - Post Abortion Counseling - OTHER', '252227999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Services - OTHER', '253230999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment- Anti Retro Viral (ARV)', '253231122', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment - Opportunistic Infection (OI)', '253231222', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment - Post Exposure Prophylaxis (PEP)', '253231322', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment - Psycho-Social Support', '253231422', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment - Home Care', '253231522', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Consult - Treatment - OTHER', '253231999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Antibody Lab Tests - ELISA (Blood) Test', '253232121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Antibody Lab Tests - Western Blot (WB) Assay', '253232221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Antibody Lab Tests - Indirect Immunofluorescence Assay (IFA)', '253232321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - Rapid Test (Murex-SUDS)', '253232421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - OTHER', '253232999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - Urine Test for HIV', '253233121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - Assessment of Immunologic Function (Viral Load)', '253233221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - Assessment of Immunologic Function (CD4 count)', '253233321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Other Lab Tests - OTHER', '253233999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Prevention Counselling', '253234129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Prevention Counselling - OTHER', '253234999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Counselling - PRE Voluntary Counselling & Testing (VCT)', '253235129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Counselling - POST Test (Positive) - Clients Only', '253235229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Counseling - POST Test (Negative)', '253235329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Counseling - POST Test (Positive) - Sexual Partners', '253235429', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'HIV/AIDS Counseling - OTHER', '253235999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Services - OTHER', '254240999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Counseling - Prevention Counseling', '254241129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Counseling - POST Test', '254241229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Counseling - OTHER', '254241999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Consultation - Follow Up', '254242120', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Consultation - OTHER', '254242999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Bacterial Vaginosis', '254243121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Candidiasis', '254243221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Chancroid', '254243321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Chlamydia', '254243421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Gonorrhea', '254243521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - OTHER', '254243999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Herpes Simplex (HSV)', '254244121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Human Papillomavirus (HPV)', '254244221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Syphilis', '254244321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Trichomoniasis', '254244421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Hepatitis B', '254244521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Test - Part I & II OTHER', '254244999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Treatment - Syndromic diagnosis with clinical treatment', '254245122', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Treatment - Etiological diagnosis with clinical treatment', '254245222', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'STI/RTI Treatment - OTHER', '254245999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Services - OTHER', '255250999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Biopsy - Conization', '255251123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Biopsy - Needle Biopsy', '255251223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Biopsy - Aspiration Biopsy', '255251323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Biopsy - Dilatation & Curretage (D&C)', '255251423', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Biopsy - OTHER', '255251999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Colposcopy', '255252123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Laparoscopy', '255252223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Hysteroscopy', '255252323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Culdoscopy', '255252423', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Hysteretomy', '255252523', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Ovariectomy', '255252623', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Mastectomy', '255252723', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - Lumpectomy', '255252823', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Endoscopy - OTHER', '255252999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - Radiography - Hysterosalpingography', '255253121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - Radiography - Mammography', '255253221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - Ultrasonography', '255253321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - Tomography', '255253421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - Dexa, Bone Density Scan', '255253521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Diagnostic Imaging - OTHER', '255253999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Exam - Manual Pelvic Exam (includes Palpation)', '255254121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Exam - Manual Breast Exam', '255254221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Exam - Vaginal Smears Sampling (Pap smear)', '255254321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Exam - Consultation without pelvic exam', '255254421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Exam - OTHER', '255254999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Lab Test - Cytology Analysis', '255255121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Lab Test - OTHER', '255255999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Therapies - Menopause Consultations, Hormonal Replacement Therapy', '255256122', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Therapies - Menstrual regulation', '255256222', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Therapies - Female Genital Mutilation Treatment of Complications', '255256322', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Therapies - OTHER', '255256999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Surgeries - Cryosurgery - Cervical', '255257123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Surgeries - Cauterization (Cervical / Vaginal)', '255257223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Surgeries - Female Genital Mutilation Reconstructive Surgery', '255257323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Surgeries - OTHER', '255257999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - Menopause Consultations, Counseling', '255258129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - Pap Smear, Importance (pre test guidance)', '255258229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - Pap Smear, Abnormal Results (post test follow-up guidance)', '255258329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - Breast Exam Results, Mammography/Biopsy', '255258429', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - Female Genital Mutilation', '255258529', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gynecological Counselling - OTHER', '255258999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetric Services - OTHER', '256260999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Diagnosis - Fetoscopy', '256261121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Diagnosis - Ultrasonography, Pre-natal', '256261221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Diagnosis - Pelvimetry', '256261321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Diagnosis - Placental Function Tests', '256261421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Diagnosis - OTHER', '256261999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Care - Uterine Monitoring', '256262121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Care - Fetal Monitoring', '256262221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Care - Immunisations', '256262422', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Care - OTHER', '256262999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Counselling - Pre Natal Care Info', '256263129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Counselling - Unplanned Pregnancy', '256263229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Counselling - HIV Prevention and Testing', '256263329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre natal Counselling - OTHER', '256263999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Lab Tests - Pregnancy Tests - Agglutination Inhibition - Urine 1 test', '256264121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Lab Tests - Pregnancy Tests - Radioimmunoasays - Blood test', '256264221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Lab Tests - Pregnancy Tests - OTHER', '256264999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Urine 1', '256265121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Glicemia de Jejum', '256265221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Hemoglobin (HB)', '256265321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Blood Type', '256265421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - VDRL', '256265521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - HIV', '256265621', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Amniocentesis', '256265721', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - Chorionic Villi Sampling', '256265821', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Pre-Natal Lab Tests - OTHER', '256265999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Surgery - Child Birth, Vaginal Delivery', '256267123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Surgery - Child Birth, Cesarean Delivery', '256267223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Surgery - Emergency Obstetric Care (EmOC)', '256267323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Surgery - OTHER', '256267999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post natal Care - Consultation including Uterine Involution Monitoring', '256268120', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post natal Care - OTHER', '256268999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post-Natal Counselling - FP Methods', '256269129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post-Natal Counselling - Breastfeeding Advice', '256269229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post-Natal Counselling - HIV Counselling', '256269329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Obstetrics - Post-Natal Counselling - OTHER', '256269999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Services - OTHER', '257270999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Endoscopy - Cystoscopy', '257271123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Endoscopy - Ureteroscopy', '257271223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Endoscopy - OTHER', '257271999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnostic Imaging - Urography', '257272121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnostic Imaging - OTHER', '257272999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnosis Other - Exam', '257273121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnosis Other - Prostate Cancer Screening', '257273221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnosis Other - Peniscopy', '257273321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnosis Other - Other Urogenital Services', '257273421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Diagnosis Other - OTHER', '257273999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Male Surgery - Biopsy', '257274123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Male Surgery - Circumcision', '257274223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Male Surgery - Other Surgical Services', '257274323', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Urological Male Surgery - OTHER', '257274999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility/Subfertility - OTHER', '258280999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Biopsy - Endometrial biopsy', '258281123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Biopsy - Testicular biopsy', '258281223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Biopsy - OTHER', '258281999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Endoscopy - Laparoscopy', '258282123', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Endoscopy - Histeroscopy', '258282223', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Endoscopy - OTHER', '258282999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Diagnostic Imaging - Histerosalpingography', '258283121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Diagnostic Imaging - Ovarian ultrasound', '258283221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Diagnostic Imaging - Transvaginal ecography', '258283321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Diagnostic Imaging - OTHER', '258283999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Post-coital test or Sims-Huhner test', '258284121', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Fallopian Tube Patency Tests', '258284221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Clomiphene citrate challenge test (CCCT)', '258284321', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Semen analysis', '258284421', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Basal Temperature', '258284521', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Mucose Analysis', '258284621', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Sperm Count', '258284721', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Spermiogram', '258284821', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - Hormonal analysis', '258284921', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Lab Test - OTHER', '258284999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Ovulation Induction', '258286122', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Embryo Transfer', '258286222', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Fertilization in Vitro', '258286322', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Gamete Intrafallopian Transfer', '258286422', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Artificial Insemination', '258286522', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Oocyte Donation', '258286622', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - Zygote Intrafallopian Transfer', '258286722', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility Treatment - OTHER', '258286999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility/Subfertility Consultation', '258288120', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility/Subfertility Consultation - OTHER', '258288999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility/Subfertility  Counseling', '258289129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Infertility/Subfertility  Counseling - OTHER', '258289999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Specialized Counselling Services - OTHER', '260290999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Individual Counseling', '261291129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Support Groups for Survivors', '261291229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Legal Counseling', '261291329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Intimate Partner Sexual Abuse', '261291429', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Intimate Partner Physical  Abuse', '261291529', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Intimate Partner Emotional Abuse', '261291629', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - NonIntimate Partner Sexual Assalt/Rape', '261291729', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - Screening Only  - Gender Based Violence (GBV)', '261291829', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - GBV - OTHER', '261291999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Domestic Violence, Child Abuse', '262292129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Domestic Violence, Screening  Only Child Abuse', '262292229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Domestic Violence - OTHER', '262292999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Family - Parent/Child Relationship', '262293129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Family- Family Conflict', '262293229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Family, Delinquency', '262293329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Family - OTHER', '262293999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Pre-Marital including Pre-Marital Family Planning', '262294129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Marital - Relationship, Partner Negotiation', '262294229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Marital - Sexuality / Sexual Disfunction', '262294329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Marital - OTHER', '262294999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Youth - Life Skills Counseling', '262295129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Youth - Sexuality', '262295229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Youth - Telephone / Internet Hotline Counseling', '262295329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Youth - SRH Counselling', '262295429', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Youth - OTHER', '262295999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Male - SRH Counselling', '262296129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Male - Sexuality', '262296229', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Male - GBV', '262296329', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counselling - Male - OTHER', '262296999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counseling - Other - Sexuality Issues ( 25 years and over)', '263297129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Counseling - OTHER', '263297999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other SRH Medical Services - Consultation', '269298120', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other SRH Medical Services - Diagnostic Test', '269298221', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other SRH Medical Services - Therapy / Treatment', '269298322', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other SRH Medical Services - Surgery', '269298423', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other SRH Medical Services - OTHER', '269298999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Medical Specialties - System Oriented Services - OTHER', '371300999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Angiology - Consultation', '371301130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Angiology - Diagnostic Test', '371301231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Angiology - Therapy / Treatment', '371301332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Angiology - Surgery', '371301433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Angiology - OTHER', '371301999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Cardiology - Consultation', '371311130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Cardiology - Diagnostic EKG', '371311231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Cardiology - Therapy / Treatment', '371311332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Cardiology - Surgery', '371311433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Cardiology - OTHER', '371311999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry - Diagnosis', '371321131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry -Therapy / Treatment', '371321232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry - Orthodontics', '371321332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry - Periodontics', '371321432', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry - Surgery', '371321533', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dentistry - OTHER', '371321999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dermatology - Consultation', '371331130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dermatology - Diagnostic Test', '371331231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dermatology - Therapy / Treatment', '371331332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dermatology - Surgery', '371331433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Dermatology - OTHER', '371331999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Endocrinology - Consultation', '371341130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Endocrinology - Diagnostic Test', '371341231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Endocrinology - Therapy / Treatment', '371341332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Endocrinology - Surgery', '371341433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Endocrinology - OTHER', '371341999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gastroenterology - Consultation', '371351130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gastroenterology - Diagnostic Test', '371351231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gastroenterology - Therapy / Treatment', '371351332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gastroenterology - Surgery', '371351433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Gastroenterology - OTHER', '371351999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Genetics - Counselling', '371361129', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Genetics - Consultation', '371361230', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Genetics - Diagnostic Test', '371361331', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Genetics - Therapy / Treatment', '371361432', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Genetics - OTHER', '371361999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Nephrology - Consultation', '371371130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Nephrology - Diagnostic Test', '371371231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Nephrology - Therapy / Treatment', '371371332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Nephrology - Surgery', '371371433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Nephrology - OTHER', '371371999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neumology - Consultation', '371381130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neumology - Diagnostic Test', '371381231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neumology - Therapy / Treatment', '371381332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neumology - Surgery', '371381433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neumology - OTHER', '371381999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neurology - Consultation', '371391130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neurology - Diagnostic Exam', '371391231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neurology - Therapy / Treatment', '371391332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neurology - Surgery', '371391433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Neurology - OTHER', '371391999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Ophtalmology - Consultation', '371401130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Ophtalmology - Diagnostic Exam', '371401231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Ophtalmology - Therapy / Treatment', '371401332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Ophtalmology - Surgery', '371401433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Ophtalmology - OTHER', '371401999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Orthopedics - Consultation', '371411130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Orthopedics - Diagnostic Exam', '371411231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Orthopedics - Therapy / Treatment', '371411332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Orthopedics - Surgery', '371411433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Orthopedics - OTHER', '371411999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Othorhinolaringology - Consultation', '371421130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Othorhinolaringology - Diagnostic Exam', '371421231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Othorhinolaringology - Therapy / Treatment', '371421332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Othorhinolaringology - Surgery', '371421433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Othorhinolaringology - OTHER', '371421999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Podology - Consultation', '371431130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Podology - Diagnostic Exam', '371431231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Podology - Therapy / Treatment', '371431332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Podology - Surgery', '371431433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Podology - OTHER', '371431999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rheumatology - Consultation', '371441130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rheumatology - Diagnostic Exam', '371441231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rheumatology - Therapy / Treatment', '371441332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rheumatology - Surgery', '371441433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rheumatology - OTHER', '371441999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Medical Specialties - Disease Oriented Services - OTHER', '372500999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Optometry - Consultation', '372501130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Optometry - Diagnostic Exam', '372501231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Optometry - OTHER', '372501999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychiatry - Diagnostic consultation', '372511131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychiatry - Therapy / Treatment', '372511232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychiatry - OTHER', '372511999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychology - Diagnostic consultation', '372521131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychology - Therapy / Treatment', '372521232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Psychology - OTHER', '372521999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Radiology - Diagnostic Imaging', '372531131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Radiology - Therapy / Treatment', '372531232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Radiology - OTHER', '372531999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Oncology - Diagnostic Test', '372541131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Oncology - Therapy / Treatment', '372541232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Oncology - Surgery', '372541333', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Oncology - OTHER', '372541999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Allergy - Consultation', '372551130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Allergy - Diagnostic Test', '372551231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Allergy - Therapy / Treatment', '372551332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Allergy - OTHER', '372551999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Immunology - Consultation', '372561130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Immunology - Diagnostic Test', '372561231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Immunology - OTHER', '372561999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Medical Specialties - Community Oriented Services - OTHER', '373600999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Hypertension Screening', '373601131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Physical Exam', '373601231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Weight & Vital Signs', '373601331', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Diabetes Screening', '373601431', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Urinalysis', '373601531', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Cholesterol screening', '373601631', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Nutrition Counseling', '373601729', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health -  Diet/Weight Control Counseling', '373601829', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Family Health - OTHER', '373601999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Geriatrics - Consultation', '373621130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Geriatrics - Diagnostic Test', '373621231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Geriatrics - Therapy / Treatment', '373621332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Geriatrics - OTHER', '373621999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Consultation', '373641130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Diagnostic - Neonatal Screening (at Birth)', '373641231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Diagnostic - Well Baby Care / Infant Health Check', '373641331', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Therapy / Treatment - Nutrition', '373641432', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Therapy / Treatment - Immunization', '373641532', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Therapy / Treatment - Oral rehydration (ORT/ORS)', '373641632', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Therapy / Treatment - Neonatal Intensive Care', '373641732', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - Surgery - Circumcision', '373641833', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pediatrics - OTHER', '373641999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Physical Medicine & Rehabilitation - Consultation', '373661130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Physical Medicine & Rehabilitation - Diagnostic Test', '373661231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Physical Medicine & Rehabilitation - Therapy / Treatment', '373661332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Physical Medicine & Rehabilitation - Surgery', '373661433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Physical Medicine & Rehabilitation - OTHER', '373661999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Preventive Medicine - Consultation', '373671130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Preventive Medicine - Diagnostic Test', '373671231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Preventive Medicine - OTHER', '373671999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Emergency Medicine - Evaluation', '373681131', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Emergency Medicine - Initial Treatment', '373681232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Emergency Medicine - Emergency Surgery', '373681333', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Emergency Medicine - OTHER', '373681999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hospitalization - Ambulatory (1 day)', '373691140', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hospitalization - Extended (>1day)', '373691241', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hospitalization - OTHER', '373691999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Medical Specialties - Diagnostic/Therapeutic Procedures - OTHER', '374700999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hematology - Consultation', '374701130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hematology - Diagnostic Test', '374701231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hematology - Therapy / Treatment', '374701332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Hematology - OTHER', '374701999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Toxicology - Consultation', '374721130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Toxicology - Diagnostic tests', '374721231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Toxicology - Therapy / Treatment', '374721332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Toxicology - OTHER', '374721999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chemical Patology - Consultation', '374741130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chemical Patology - Diagnostic Test', '374741231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chemical Patology - OTHER', '374751999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pathology - Consultation', '374761130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pathology - Diagnostic Test', '374761231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Pathology - OTHER', '374761999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Microbiology - Consultation', '374781130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Microbiology - Diagnostic Test', '374781231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Microbiology - OTHER', '374781999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Medical Specialties - Other Services - OTHER', '375800999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chiropractice - Consultation', '375801130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chiropractice - Therapy / Treatment', '375801232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Chiropractice - OTHER', '375801999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Osteophaty - Consultation', '375811130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Osteophaty - Therapy / Treatment', '375811232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Osteophaty - Diagnostic Test', '375811331', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Osteophaty - OTHER', '375811999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Plastic Surgery - Consultation', '375821130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Plastic Surgery - Therapy / Treatment', '375821232', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Plastic Surgery - Surgery', '375821333', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Plastic Surgery - OTHER', '375821999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - Consultation', '375831130', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - Diagnostic Test', '375831231', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - Therapy / Treatment', '375831332', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - Surgery', '375831433', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - Counselling', '375831539', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non SRH Medical Services - OTHER', '375831999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'ALL OTHER NON SRH SERVICES - OTHER', '380910999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Sales & Rentals - OTHER', '380911999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Sales of Medicines', '381912150', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Sales Medical Supplies', '381912250', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Sales Medical Equipment', '381912350', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Sales - OTHER', '381913999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rental Medical Infrastructure', '382914450', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rental Medical Infrastructure', '382914450', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Rental Medical Equipment / Infrastructure - OTHER', '382915999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non Medical Products & Services - Sales of IEC Materials', '491990190', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non Medical Products & Services - Other Generic Products', '491990999', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non Medical Products & Services - Sales of IEC Services', '492992090', 11, '' );
INSERT INTO codes ( code_text, code, code_type, modifier ) VALUES ( 'Other Non Medical Products & Services - OTHER', '492992999', 11, '' );

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

DELETE FROM list_options WHERE list_id = 'clientstatus';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','maaa'  ,'MA Client Accepting Abortion', 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','mara'  ,'MA Client Refusing Abortion' , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','refout','Outbound Referral'           , 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('clientstatus','refin' ,'Inbound Referral'            , 4,0,0);
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
DELETE FROM list_options WHERE list_id = 'ab_location';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','proc' ,'Procedure at this site'              , 1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','ma'   ,'Followup procedure from this site'   , 2,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','part' ,'Followup procedure from partner site', 3,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('ab_location','oth'  ,'Followup procedure from other site'  , 4,0,0);
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

INSERT INTO layout_options VALUES ('HIS','usertext12'          ,'1Personal','Blood Group'         , 1, 1,1, 0,  0,'bloodgroup' ,1,1,'','' ,'Blood Group');
INSERT INTO layout_options VALUES ('HIS','usertext13'          ,'1Personal','RH Factor'           , 2, 1,1, 0,  0,'rh_factor'  ,1,1,'','' ,'RH Factor');
INSERT INTO layout_options VALUES ('HIS','usertext11'          ,'1Personal','Risk Factors'        , 3,21,1, 0,  0,'riskfactors',1,1,'','' ,'Risk Factors');
INSERT INTO layout_options VALUES ('HIS','exams'               ,'1Personal','Exams/Tests'         , 4,23,1, 0,  0,'exams'      ,1,1,'','' ,'Exam and test results');
INSERT INTO layout_options VALUES ('HIS','usertext14'          ,'1Personal','Surgical History'    , 5,25,1, 0,  0,'surghist'   ,1,3,'','' ,'Surgeries with dates/notes');
INSERT INTO layout_options VALUES ('HIS','coffee'              ,'1Personal','Coffee'              , 6, 2,1,20,255,''           ,1,1,'','' ,'Caffeine consumption');
INSERT INTO layout_options VALUES ('HIS','tobacco'             ,'1Personal','Tobacco'             , 7, 2,1,20,255,''           ,1,1,'','' ,'Tobacco use');
INSERT INTO layout_options VALUES ('HIS','alcohol'             ,'1Personal','Alcohol'             , 8, 2,1,20,255,''           ,1,1,'','' ,'Alcohol consumption');
INSERT INTO layout_options VALUES ('HIS','sleep_patterns'      ,'1Personal','Sleep Patterns'      , 9, 2,1,20,255,''           ,1,1,'','' ,'Sleep patterns');
INSERT INTO layout_options VALUES ('HIS','exercise_patterns'   ,'1Personal','Exercise Patterns'   ,10, 2,1,20,255,''           ,1,1,'','' ,'Exercise patterns');
INSERT INTO layout_options VALUES ('HIS','seatbelt_use'        ,'1Personal','Seatbelt Use'        ,11, 2,1,20,255,''           ,1,1,'','' ,'Seatbelt use');
INSERT INTO layout_options VALUES ('HIS','counseling'          ,'1Personal','Counseling'          ,12, 2,1,20,255,''           ,1,1,'','' ,'Counseling activities');
INSERT INTO layout_options VALUES ('HIS','hazardous_activities','1Personal','Hazardous Activities',13, 2,1,20,255,''           ,1,1,'','' ,'Hazardous activities');

INSERT INTO layout_options VALUES ('HIS','history_father'               ,'2Relatives','Father'             , 1, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_mother'               ,'2Relatives','Mother'             , 2, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_siblings'             ,'2Relatives','Siblings'           , 3, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_spouse'               ,'2Relatives','Spouse'             , 4, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','history_offspring'            ,'2Relatives','Offspring'          , 5, 2,1,20,255,'',1,3,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_cancer'             ,'2Relatives','Cancer'             , 6, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_tuberculosis'       ,'2Relatives','Tuberculosis'       , 7, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_diabetes'           ,'2Relatives','Diabetes'           , 8, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_high_blood_pressure','2Relatives','High Blood Pressure', 9, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_heart_problems'     ,'2Relatives','Heart Problems'     ,10, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_stroke'             ,'2Relatives','Stroke'             ,11, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_epilepsy'           ,'2Relatives','Epilepsy'           ,12, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_mental_illness'     ,'2Relatives','Mental Illness'     ,13, 2,1,20,255,'',1,1,'','' ,'');
INSERT INTO layout_options VALUES ('HIS','relatives_suicide'            ,'2Relatives','Suicide'            ,14, 2,1,20,255,'',1,3,'','' ,'');

INSERT INTO layout_options VALUES ('HIS','usertext15','3Reproductive Women','Menstrual', 1,22,1, 0,  0,'genmenhist',1,1,'','' ,'Menstrual History');
INSERT INTO layout_options VALUES ('HIS','usertext16','3Reproductive Women','Obstetric', 2,22,1, 0,  0,'genobshist',1,1,'','' ,'Obstetric History');
INSERT INTO layout_options VALUES ('HIS','usertext17','3Reproductive Women','Abortion' , 3,22,1, 0,  0,'genabohist',1,1,'','' ,'Abortion History');

INSERT INTO layout_options VALUES ('HIS','usertext18','4Reproductive General','HIV/AIDS' , 1,21,1, 0,  0,'genhivhist',1,1,'','' ,'HIV/AIDS History');
INSERT INTO layout_options VALUES ('HIS','usertext19','4Reproductive General','ITS/ITR'  , 2,21,1, 0,  0,'genitshist',1,1,'','' ,'ITS/ITR History');
INSERT INTO layout_options VALUES ('HIS','usertext20','4Reproductive General','Fertility', 3,21,1, 0,  0,'genferhist',1,1,'','' ,'Infertility/Subfertility History');
INSERT INTO layout_options VALUES ('HIS','usertext21','4Reproductive General','Urology'  , 4,21,1, 0,  0,'genurohist',1,1,'','' ,'Urology History');

INSERT INTO layout_options VALUES ('HIS','name_1'            ,'5Other','Name/Value'        ,1, 2,1,10,255,'',1,1,'','' ,'Name 1' );
INSERT INTO layout_options VALUES ('HIS','value_1'           ,'5Other',''                  ,2, 2,1,10,255,'',0,0,'','' ,'Value 1');
INSERT INTO layout_options VALUES ('HIS','name_2'            ,'5Other','Name/Value'        ,3, 2,1,10,255,'',1,1,'','' ,'Name 2' );
INSERT INTO layout_options VALUES ('HIS','value_2'           ,'5Other',''                  ,4, 2,1,10,255,'',0,0,'','' ,'Value 2');
INSERT INTO layout_options VALUES ('HIS','additional_history','5Other','Additional History',5, 3,1,30,  3,'',1,3,'' ,'' ,'Additional history notes');
INSERT INTO layout_options VALUES ('HIS','userarea11','5Other','User Defined Area 11',6,3,0,30,3,'',1,3,'','','User Defined');
INSERT INTO layout_options VALUES ('HIS','userarea12','5Other','User Defined Area 12',7,3,0,30,3,'',1,3,'','','User Defined');

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
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('genitshist','couns','Client has received ITS  Counselling'     ,  2,0,0);
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

DELETE FROM layout_options WHERE form_id = 'CON';
INSERT INTO layout_options VALUES ('CON','prev_method'  ,'aStatistics' ,'Last Method Used'             , 1,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'Last Contraceptive Method Used');
INSERT INTO layout_options VALUES ('CON','new_method'   ,'aStatistics' ,'New Method Adopted'           , 2,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'Contraceptive Method Adopted at This Visit');
INSERT INTO layout_options VALUES ('CON','reason_chg'   ,'aStatistics' ,'Reason for Method Change'     , 3,21,1, 2, 0,'mcreason'    ,1,3,'','' ,'Reasons for Method Change');
INSERT INTO layout_options VALUES ('CON','reason_term'  ,'aStatistics' ,'Reason for Method Termination', 4,21,1, 2, 0,'mcreason'    ,1,3,'','' ,'Reasons for Method Termination');
INSERT INTO layout_options VALUES ('CON','usertext11'   ,'aStatistics' ,'General Risk Factors'         , 5,21,1, 2, 0,'riskfactors' ,1,3,'','H','');
INSERT INTO layout_options VALUES ('CON','hor_history'  ,'bHormonal'   ,'Menstrual History'            , 1,21,1, 2, 0,'menhist'     ,1,3,'','' ,'Menstrual History');
INSERT INTO layout_options VALUES ('CON','hor_lmp'      ,'bHormonal'   ,'Last Menstrual Period'        , 2, 1,1, 0, 0,'lmp'         ,1,3,'','' ,'Last Menstrual Period');
INSERT INTO layout_options VALUES ('CON','hor_flow'     ,'bHormonal'   ,'Nature of Menstrual Flow'     , 3,21,1, 4, 0,'flowtype'    ,1,3,'','' ,'Nature of Menstrual Flow');
INSERT INTO layout_options VALUES ('CON','hor_bleeding' ,'bHormonal'   ,'Bleeding'                     , 4,21,1, 4, 0,'bleeding'    ,1,3,'','' ,'Menstrual Bleeding Characteristics');
INSERT INTO layout_options VALUES ('CON','hor_contra'   ,'bHormonal'   ,'Contraindications'            , 5,21,1, 1, 0,'hor_contra'  ,1,3,'','' ,'Contraindications of Hormonal Contraception');
INSERT INTO layout_options VALUES ('CON','iud_history'  ,'cBarrier/IUD','Menstrual History'            , 1,21,1, 2, 0,'menhist'     ,1,3,'','' ,'Menstrual History');
INSERT INTO layout_options VALUES ('CON','iud_lmp'      ,'cBarrier/IUD','Last Menstrual Period'        , 2, 1,1, 0, 0,'lmp'         ,1,3,'','' ,'Last Menstrual Period');
INSERT INTO layout_options VALUES ('CON','iud_pain'     ,'cBarrier/IUD','Pain during Menses'           , 3,21,1, 2, 0,'menpain'     ,1,3,'','' ,'Type of Pain during Menses');
INSERT INTO layout_options VALUES ('CON','iud_upos'     ,'cBarrier/IUD','Uterus Position'              , 4, 1,1, 0, 0,'uteruspos'   ,1,3,'','' ,'Uterus Position');
INSERT INTO layout_options VALUES ('CON','iud_contra'   ,'cBarrier/IUD','Contraindications'            , 5,21,1, 0, 0,'iud_contra'  ,1,3,'','' ,'Contraindications of Barrier/IUD Contraception');
INSERT INTO layout_options VALUES ('CON','sur_screen'   ,'dSurgical'   ,'Pre-Operative Screening'      , 1,21,1, 1, 0,'sur_screen'  ,1,3,'','' ,'Pre-Operative Screening for Surgical Contraception');
INSERT INTO layout_options VALUES ('CON','sur_anes'     ,'dSurgical'   ,'Type of Anesthesia'           , 2, 1,1, 0, 0,'anesthesia'  ,1,3,'','' ,'Type of Anesthesia for Surgical Contraception');
INSERT INTO layout_options VALUES ('CON','sur_type'     ,'dSurgical'   ,'Type of Surgical Approach'    , 3, 1,1, 0, 0,'sur_type'    ,1,3,'','' ,'Type of Contraceptive Surgery');
INSERT INTO layout_options VALUES ('CON','sur_post_ins' ,'dSurgical'   ,'Post-Operative Instructions'  , 4,21,1, 0, 0,'sur_post_ins',1,3,'','' ,'Post-Operative Instructions');
INSERT INTO layout_options VALUES ('CON','sur_contra'   ,'dSurgical'   ,'Contraindications'            , 5,21,1, 0, 0,'sur_contra'  ,1,3,'','' ,'Contraindications of Surgical Contraception');
INSERT INTO layout_options VALUES ('CON','nat_reason'   ,'eNatural'    ,'Reason for Adopting a FABM'   , 1,21,1, 2, 0,'nat_reason'  ,1,3,'','' ,'Reasons for Adopting Natural Contracepation');
INSERT INTO layout_options VALUES ('CON','nat_method'   ,'eNatural'    ,'FABM Method Adopted'          , 2, 1,1, 0, 0,'nat_method'  ,1,3,'','' ,'Type of Natural Contraception');
INSERT INTO layout_options VALUES ('CON','emg_reason'   ,'fEmergency'  ,'Reason for Using EC'          , 1,21,1, 1, 0,'emg_reason'  ,1,3,'','' ,'Reasons for Using Emergency Contracepation');
INSERT INTO layout_options VALUES ('CON','emg_method'   ,'fEmergency'  ,'EC Method Adopted'            , 2, 1,1, 0, 0,'emg_method'  ,1,3,'','' ,'Type of Emergency Contraception');

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

DELETE FROM layout_options WHERE form_id = 'SRH';
INSERT INTO layout_options VALUES ('SRH','usertext15' ,'aGynecology'                ,'Menstrual History'             , 1,22,1, 0, 0,'genmenhist'  ,1,3,'','H','');
INSERT INTO layout_options VALUES ('SRH','men_hist'   ,'aGynecology'                ,'Recent Menstrual History'      , 2,21,1, 2, 0,'menhist'     ,1,3,'','','Recent Menstrual History');
INSERT INTO layout_options VALUES ('SRH','men_compl'  ,'aGynecology'                ,'Menstrual Complications'       , 3,21,1, 2, 0,'men_compl'   ,1,3,'','','Menstrual Complications');
INSERT INTO layout_options VALUES ('SRH','pap_hist'   ,'aGynecology'                ,'Pap Smear Recent History'      , 4,22,1, 0, 0,'pap_hist'    ,1,3,'','','Pap Smear Recent History');
INSERT INTO layout_options VALUES ('SRH','gyn_exams'  ,'aGynecology'                ,'Gynecological Tests'           , 5,23,1, 0, 0,'gyn_exams'   ,1,1,'','','Gynecological test results');
INSERT INTO layout_options VALUES ('SRH','pr_status'  ,'bObstetrics'                ,'Pregnancy Status Confirmed'    , 1, 1,1, 0, 0,'pr_status'   ,1,3,'','','Pregnancy Status Confirmed');
INSERT INTO layout_options VALUES ('SRH','gest_age_by','bObstetrics'                ,'Gestational Age Confirmed By'  , 2, 1,1, 0, 0,'gest_age_by' ,1,3,'','','Gestational Age Confirmed By');
INSERT INTO layout_options VALUES ('SRH','usertext12' ,'bObstetrics'                ,'Blood Group'                   , 3, 1,1, 0, 0,'bloodgroup'  ,1,3,'','H','');
INSERT INTO layout_options VALUES ('SRH','usertext13' ,'bObstetrics'                ,'RH Factor'                     , 4, 1,1, 0, 0,'rh_factor'   ,1,3,'','H','');
INSERT INTO layout_options VALUES ('SRH','obs_exams'  ,'bObstetrics'                ,'Obstetric Tests'               , 5,23,1, 0, 0,'obs_exams'   ,1,1,'','','Obstetric test results');
INSERT INTO layout_options VALUES ('SRH','usertext16' ,'bObstetrics'                ,'Obstetric History'             , 6,22,1, 0, 0,'genobshist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','pr_outcome' ,'bObstetrics'                ,'Outcome of Last Pregnancy'     , 7,21,1, 2, 0,'pr_outcome'  ,1,3,'','','Outcome of Last Pregnancy');
INSERT INTO layout_options VALUES ('SRH','pr_compl'   ,'bObstetrics'                ,'Pregnancy Complications'       , 8,21,1, 2, 0,'pr_compl'    ,1,3,'','','Pregnancy Complications');
INSERT INTO layout_options VALUES ('SRH','usertext17' ,'cBasic RH (female only)'    ,'Abortion Basic History'        , 1,22,1, 0, 0,'genabohist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','abo_exams'  ,'cBasic RH (female only)'    ,'Abortion Tests'                , 2,23,1, 0, 0,'abo_exams'   ,1,1,'','','Abortion test results');
INSERT INTO layout_options VALUES ('SRH','usertext18' ,'dBasic RH (female and male)','HIV/AIDS Basic History'        , 1,21,1, 0, 0,'genhivhist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','hiv_exams'  ,'dBasic RH (female and male)','HIV/AIDS Tests'                , 2,23,1, 0, 0,'hiv_exams'   ,1,1,'','','HIV/AIDS test results');
INSERT INTO layout_options VALUES ('SRH','usertext19' ,'dBasic RH (female and male)','ITS/ITR Basic History'         , 3,21,1, 0, 0,'genitshist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','its_exams'  ,'dBasic RH (female and male)','ITS/ITR Tests'                 , 4,23,1, 0, 0,'its_exams'   ,1,1,'','','ITS/ITR test results');
INSERT INTO layout_options VALUES ('SRH','usertext20' ,'dBasic RH (female and male)','Fertility Basic History'       , 5,21,1, 0, 0,'genferhist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','fer_exams'  ,'dBasic RH (female and male)','Fertility Tests'               , 6,23,1, 0, 0,'fer_exams'   ,1,1,'','','Infertility/subfertility test results');
INSERT INTO layout_options VALUES ('SRH','fer_causes' ,'dBasic RH (female and male)','Causes of Infertility'         , 7,21,1, 2, 0,'fer_causes'  ,1,3,'','','Causes of Infertility');
INSERT INTO layout_options VALUES ('SRH','fer_treat'  ,'dBasic RH (female and male)','Infertility Treatment'         , 8,21,1, 2, 0,'fer_treat'   ,1,3,'','','Infertility Treatment');
INSERT INTO layout_options VALUES ('SRH','usertext21' ,'dBasic RH (female and male)','Urology Basic History'         , 9,21,1, 0, 0,'genurohist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('SRH','uro_exams'  ,'dBasic RH (female and male)','Urology Tests'                 ,10,23,1, 0, 0,'uro_exams'   ,1,1,'','','Urology test results');
INSERT INTO layout_options VALUES ('SRH','uro_disease','dBasic RH (female and male)','Male Genitourinary diseases'   ,11,21,1, 2, 0,'uro_disease' ,1,3,'','','Male Genitourinary diseases');

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

DELETE FROM list_options WHERE list_id = 'lbfnames';
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lbfnames','LBFgcac','IPPF GCAC',1,0,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, option_value ) VALUES ('lbfnames','LBFsrh' ,'IPPF SRH' ,2,0,0);

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
INSERT INTO layout_options VALUES ('LBFsrh','usertext21' ,'4Basic RH (female and male)','Urology Basic History'         , 9,21,1, 0, 0,'genurohist'  ,1,1,'','H','');
INSERT INTO layout_options VALUES ('LBFsrh','uro_exams'  ,'4Basic RH (female and male)','Urology Tests'                 ,10,23,1, 0, 0,'uro_exams'   ,1,1,'','','Urology test results');
INSERT INTO layout_options VALUES ('LBFsrh','uro_disease','4Basic RH (female and male)','Male Genitourinary diseases'   ,11,21,1, 2, 0,'uro_disease' ,1,3,'','','Male Genitourinary diseases');

-- The following revised or added 2009-07-28

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
INSERT INTO layout_options VALUES ('LBFgcac','client_status','1Basic Information','Client Status'               , 1,27,2, 0, 0,'clientstatus',1,1,'','' ,'Client Status');
INSERT INTO layout_options VALUES ('LBFgcac','ab_location'  ,'1Basic Information','Type of Visit'               , 2,27,2, 0, 0,'ab_location' ,1,1,'','' ,'Nature of this visit');
INSERT INTO layout_options VALUES ('LBFgcac','in_ab_proc'   ,'1Basic Information','Associated Induced Procedure', 3,27,1, 0, 0,'in_ab_proc'  ,1,3,'','' ,'Applies regardless of when or where done');
INSERT INTO layout_options VALUES ('LBFgcac','complications','2Complications','Complications'                   , 1,21,1, 2, 0,'complication',1,3,'','' ,'Post-Abortion Complications');
INSERT INTO layout_options VALUES ('LBFgcac','main_compl'   ,'2Complications','Main Complication'               , 2, 1,1, 2, 0,'complication',1,3,'','' ,'Primary Complication');
INSERT INTO layout_options VALUES ('LBFgcac','contrameth'   ,'3Contraception','New Method'                      , 1,21,1, 2, 0,'contrameth'  ,1,3,'','' ,'New method adopted');

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

UPDATE layout_options AS a, list_options AS i SET a.group_name = '1Basic Data', a.title = 'Transgender', a.seq = 13, a.data_type = 26, a.uor = 1, a.description = 'Transgender', i.title = 'Transgender' WHERE a.form_id = 'DEM' AND a.field_id = 'userlist6' AND a.uor = 0 AND i.list_id = 'lists' AND i.option_id = 'userlist6';

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
INSERT INTO `openemr_postcalendar_categories` VALUES (12,'3 Counselling Only','#FFFFCC','Counselling',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);
INSERT INTO `openemr_postcalendar_categories` VALUES (13,'4 Supply/Re-Supply','#CCCCCC','Supply/Re-Supply',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);
INSERT INTO `openemr_postcalendar_categories` VALUES (14,'5 Administrative','#FFFFFF','Supply/Re-Supply',1,NULL,'a:5:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"4\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}',0,900,0,3,2,0,0);

