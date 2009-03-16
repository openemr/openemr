CREATE TABLE IF NOT EXISTS `form_CAMOS` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
category TEXT,
subcategory TEXT,
item TEXT,
content TEXT,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_category` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

category TEXT,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_subcategory` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

subcategory TEXT,
category_id bigint(20) NOT NULL,

PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `form_CAMOS_item` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

item TEXT,
content TEXT,
subcategory_id bigint(20) NOT NULL,

PRIMARY KEY (id)
) TYPE=MyISAM;


INSERT INTO `form_CAMOS_category` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,'prescriptions');
INSERT INTO `form_CAMOS_category` VALUES (2,NULL,NULL,NULL,NULL,NULL,NULL,'radiology');
INSERT INTO `form_CAMOS_category` VALUES (3,NULL,NULL,NULL,NULL,NULL,NULL,'lab');
INSERT INTO `form_CAMOS_category` VALUES (22,NULL,NULL,'admin',NULL,NULL,NULL,'referral');
INSERT INTO `form_CAMOS_category` VALUES (7,NULL,NULL,NULL,NULL,NULL,NULL,'DME');
INSERT INTO `form_CAMOS_category` VALUES (12,NULL,NULL,NULL,NULL,NULL,NULL,'exam');


INSERT INTO `form_CAMOS_subcategory` VALUES (2,NULL,NULL,NULL,NULL,NULL,NULL,'x-ray',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (3,NULL,NULL,NULL,NULL,NULL,NULL,'ultrasound',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (4,NULL,NULL,NULL,NULL,NULL,NULL,'mri',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (5,NULL,NULL,NULL,NULL,NULL,NULL,'ct',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (6,NULL,NULL,NULL,NULL,NULL,NULL,'antibiotics',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (7,NULL,NULL,NULL,NULL,NULL,NULL,'uri',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (8,NULL,NULL,NULL,NULL,NULL,NULL,'sleep',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (9,NULL,NULL,NULL,NULL,NULL,NULL,'gi',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (10,NULL,NULL,NULL,NULL,NULL,NULL,'ed',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (11,NULL,NULL,NULL,NULL,NULL,NULL,'htn',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (12,NULL,NULL,NULL,NULL,NULL,NULL,'anxiolytic',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (13,NULL,NULL,NULL,NULL,NULL,NULL,'muscle relaxers',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (16,NULL,NULL,NULL,NULL,NULL,NULL,'packages',3);
INSERT INTO `form_CAMOS_subcategory` VALUES (18,NULL,NULL,NULL,NULL,NULL,NULL,'respiratory',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (19,NULL,NULL,NULL,NULL,NULL,NULL,'allergy',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (20,NULL,NULL,NULL,NULL,NULL,NULL,'cough',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (22,NULL,NULL,NULL,NULL,NULL,NULL,'antidepressant',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (23,NULL,NULL,NULL,NULL,NULL,NULL,'mammogram',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (24,NULL,NULL,NULL,NULL,NULL,NULL,'echocardiogram',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (26,NULL,NULL,NULL,NULL,NULL,NULL,'orthopedic',7);
INSERT INTO `form_CAMOS_subcategory` VALUES (28,NULL,NULL,NULL,NULL,NULL,NULL,'weight loss',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (29,NULL,NULL,NULL,NULL,NULL,NULL,'lipid',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (30,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (31,NULL,NULL,NULL,NULL,NULL,NULL,'thyroid',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (32,NULL,NULL,NULL,NULL,NULL,NULL,'ear drops',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (33,NULL,NULL,NULL,NULL,NULL,NULL,'yeast',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (41,NULL,NULL,NULL,NULL,NULL,NULL,'gout',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (42,NULL,NULL,NULL,NULL,NULL,NULL,'doppler',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (44,NULL,NULL,NULL,NULL,NULL,NULL,'topical',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (48,NULL,NULL,NULL,NULL,NULL,NULL,'corticosteroids',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (49,NULL,NULL,NULL,NULL,NULL,NULL,'NSAIDS',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (50,NULL,NULL,NULL,NULL,NULL,NULL,'eye drops',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (51,NULL,NULL,NULL,NULL,NULL,NULL,'vertigo',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (54,NULL,NULL,NULL,NULL,NULL,NULL,'complete',3);
INSERT INTO `form_CAMOS_subcategory` VALUES (55,NULL,NULL,NULL,NULL,NULL,NULL,'psychiatric',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (63,NULL,NULL,NULL,NULL,NULL,NULL,'dexa',2);
INSERT INTO `form_CAMOS_subcategory` VALUES (64,NULL,NULL,NULL,NULL,NULL,NULL,'osteoporosis',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (65,NULL,NULL,NULL,NULL,NULL,NULL,'migraine',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (68,NULL,NULL,NULL,NULL,NULL,NULL,'blood thinners',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (71,NULL,NULL,NULL,NULL,NULL,NULL,'htn-lipid',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (73,NULL,NULL,NULL,NULL,NULL,NULL,'soap by dx',12);
INSERT INTO `form_CAMOS_subcategory` VALUES (170,NULL,NULL,NULL,NULL,NULL,NULL,'warnings',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (76,NULL,NULL,NULL,NULL,NULL,NULL,'herpes',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (78,NULL,NULL,NULL,NULL,NULL,NULL,'influenza',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (82,NULL,NULL,NULL,NULL,NULL,NULL,'hormones',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (83,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes',7);
INSERT INTO `form_CAMOS_subcategory` VALUES (85,NULL,NULL,NULL,NULL,NULL,NULL,'antiemetics',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (87,NULL,NULL,NULL,NULL,NULL,NULL,'respiratory',7);
INSERT INTO `form_CAMOS_subcategory` VALUES (88,NULL,NULL,NULL,NULL,NULL,NULL,'siezure',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (89,NULL,NULL,NULL,NULL,NULL,NULL,'circulation',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (90,NULL,NULL,NULL,NULL,NULL,NULL,'hair loss',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (93,NULL,NULL,NULL,NULL,NULL,NULL,'vitamins',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (99,NULL,NULL,NULL,NULL,NULL,NULL,'cold',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (100,NULL,NULL,NULL,NULL,NULL,NULL,'heart',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (102,NULL,NULL,NULL,NULL,NULL,NULL,'antifungal',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (103,NULL,NULL,NULL,NULL,NULL,NULL,'preop',3);
INSERT INTO `form_CAMOS_subcategory` VALUES (104,NULL,NULL,NULL,NULL,NULL,NULL,'motion sickness',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (106,NULL,NULL,NULL,NULL,NULL,NULL,'preop',12);
INSERT INTO `form_CAMOS_subcategory` VALUES (108,NULL,NULL,NULL,NULL,NULL,NULL,'urology',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (109,NULL,NULL,NULL,NULL,NULL,NULL,'other',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (111,NULL,NULL,NULL,NULL,NULL,NULL,'nausea',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (113,NULL,NULL,NULL,NULL,NULL,NULL,'sleepiness',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (114,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes',3);
INSERT INTO `form_CAMOS_subcategory` VALUES (116,NULL,NULL,NULL,NULL,NULL,NULL,'anthelmintics',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (124,NULL,NULL,NULL,NULL,NULL,NULL,'adhd',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (126,NULL,NULL,NULL,NULL,NULL,NULL,'dementia',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (131,NULL,NULL,NULL,NULL,NULL,NULL,'neuro',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (133,NULL,NULL,NULL,NULL,NULL,NULL,'chemotherapy',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (137,NULL,NULL,NULL,NULL,NULL,NULL,'gyn',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (145,NULL,NULL,NULL,NULL,NULL,NULL,'rls',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (148,NULL,NULL,NULL,NULL,NULL,NULL,'smoking',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (152,NULL,NULL,NULL,NULL,NULL,NULL,'birth control',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (158,NULL,NULL,NULL,NULL,NULL,NULL,'urology',7);
INSERT INTO `form_CAMOS_subcategory` VALUES (160,NULL,NULL,NULL,NULL,NULL,NULL,'otc',1);
INSERT INTO `form_CAMOS_subcategory` VALUES (175,NULL,NULL,'drleeds',NULL,NULL,NULL,'codes',3);


INSERT INTO `form_CAMOS_item` VALUES (12,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 500mg 3 day','Azithromycin 500mg Three Day Pack\r\n\r\n#3/three tablets.  \r\n\r\nTake one tablet once daily for three days.',6);
INSERT INTO `form_CAMOS_item` VALUES (3,NULL,NULL,NULL,NULL,NULL,NULL,'chest pa and lat','chest x-ray\r\n\r\nPA and Lateral\r\n\r\nDx: bronchitis',2);
INSERT INTO `form_CAMOS_item` VALUES (4,NULL,NULL,NULL,NULL,NULL,NULL,'zmax','Zmax \r\n(azithromycin extended release) \r\noral suspension\r\n\r\ndrink liquid as single dose.  review patient instructions provided with product.',6);
INSERT INTO `form_CAMOS_item` VALUES (7,NULL,NULL,NULL,NULL,NULL,NULL,'cephalexin 500mg','cephalexin 500mg\r\n\r\n#40/forty capsules.\r\n\r\nOne capsule po q6hrs x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (10,NULL,NULL,NULL,NULL,NULL,NULL,'lunesta','Lunesta 3mg\r\n\r\n#7/seven tablets.  \r\n\r\none tablet by mouth at bedtime as needed for sleep.',8);
INSERT INTO `form_CAMOS_item` VALUES (13,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 250mg 5 day','Azithromycin 250mg\r\n\r\n#6/six tablets.  \r\n\r\nTake 2 tablets the first day at the same time.  Take 1 tablet daily  for days 2-5.',6);
INSERT INTO `form_CAMOS_item` VALUES (14,NULL,NULL,NULL,NULL,NULL,NULL,'ambien 10mg','Ambien 10mg\r\n\r\n#15/fifteen tablets.\r\n#30/thirty tablets.\r\n\r\nTake one tablet by mouth at bedtime as needed for sleep.',8);
INSERT INTO `form_CAMOS_item` VALUES (15,NULL,NULL,NULL,NULL,NULL,NULL,'cipro','Cipro 500mg\r\n\r\n#20/twenty tablets \r\n\r\ntake one tablet by mouth every twelve hours (twice daily) for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (16,NULL,NULL,NULL,NULL,NULL,NULL,'uri','testing\r\n\r\n/*billing::CPT4::99213::unknown visit baby:: :: ::29.95*/\r\n',7);
INSERT INTO `form_CAMOS_item` VALUES (17,NULL,NULL,NULL,NULL,NULL,NULL,'tigan','Tigan 300mg tablets\r\n\r\n#10/ten tablets.  \r\n\r\nOne tablet by mouth every 8 hours as needed for nausea.',9);
INSERT INTO `form_CAMOS_item` VALUES (18,NULL,NULL,NULL,NULL,NULL,NULL,'lomotil #30','Lomotil\r\n\r\n#20/twenty tablets.  \r\n\r\nOne tablet by mouth every 8 hours as needed for diarrhea.',9);
INSERT INTO `form_CAMOS_item` VALUES (20,NULL,NULL,NULL,NULL,NULL,NULL,'viagra','Viagra 50mg\r\n\r\n#6/six tablets.  \r\n\r\nOne half to one tablet by mouth once daily as needed for sexual activity. \r\n \r\n5 additional refills.',10);
INSERT INTO `form_CAMOS_item` VALUES (391,NULL,NULL,NULL,NULL,NULL,NULL,'phenergan','phenergan 25mg\r\n\r\n#30/thirty\r\n\r\none tablet q8 hours prn nausea',111);
INSERT INTO `form_CAMOS_item` VALUES (22,NULL,NULL,NULL,NULL,NULL,NULL,'maxzide','Mazide 37.5/25mg\r\n\r\n#30/thirty capsules\r\n\r\nOne capsule by mouth every morning for high blood pressure.',11);
INSERT INTO `form_CAMOS_item` VALUES (214,NULL,NULL,NULL,NULL,NULL,NULL,'bronchitis','Subjective:\r\n\r\nComplains of productive cough for several days.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nHEENT: unremarkable\r\nheart: rrr\r\nlungs: bilateral expiratory wheezing noted.\r\n\r\nAssessment:\r\n\r\nbronchitis\r\n\r\nPlan:\r\n',73);
INSERT INTO `form_CAMOS_item` VALUES (28,NULL,NULL,NULL,NULL,NULL,NULL,'albuterol mdi','Albuterol HFA Inhaler\r\n\r\none unit\r\n\r\n2 inhalations every four to six hours as needed for shortness of breath.',18);
INSERT INTO `form_CAMOS_item` VALUES (29,NULL,NULL,NULL,NULL,NULL,NULL,'flonase nasal spray','Flonase Nasal Spray\r\n\r\n2 sprays in each nostril once daily for allergies.  \r\n\r\n2 additional refills.',19);
INSERT INTO `form_CAMOS_item` VALUES (30,NULL,NULL,NULL,NULL,NULL,NULL,'tussionex','Tussionex Cough Syrup\r\n\r\n2/two ounces.  \r\n\r\n1 tspn q12hrs prn cough.',20);
INSERT INTO `form_CAMOS_item` VALUES (363,NULL,NULL,NULL,NULL,NULL,NULL,'compazine','compazine 10mg\r\n\r\n#30/thirty\r\n\r\none tablet q8 hours prn nausea\r\n\r\n2 additional refills.',111);
INSERT INTO `form_CAMOS_item` VALUES (33,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin suspension','amoxicillin suspension 125mg/5cc\r\n\r\ndispense: 150cc/one hundred fifty mL.\r\n\r\n5cc po q8hrs x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (34,NULL,NULL,NULL,NULL,NULL,NULL,'Rondec DM','Rondec DM Oral Drops\r\n\r\ndispense: thirty mL bottle\r\n\r\ndirections: 3/4 dropper qid prn cough, congestion.',20);
INSERT INTO `form_CAMOS_item` VALUES (35,NULL,NULL,NULL,NULL,NULL,NULL,'metronidazole 250mg','Metronidazole 250mg\r\n\r\n#21/twenty-one tablets.  \r\n\r\none tablet by mouth every 8 hours \r\n(three times daily) x one week.',6);
INSERT INTO `form_CAMOS_item` VALUES (36,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin','Amoxicillin 500mg\r\n\r\n#30/thirty capsules.  \r\n\r\nOne capsule by mouth every eight hours for 10 days.\r\n\r\n/*lock::*/',6);
INSERT INTO `form_CAMOS_item` VALUES (410,NULL,NULL,NULL,NULL,NULL,NULL,'remeron','Remeron 30mg\r\n\r\n#30/thirty tablets.  \r\n\r\none tablet by mouth once daily.  \r\n\r\nTwo additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (39,NULL,NULL,NULL,NULL,NULL,NULL,'hctz','HCTZ 12.5mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth once daily for high blood pressure.  \r\n\r\n2 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (41,NULL,NULL,NULL,NULL,NULL,NULL,'lasix','Lasix 40mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily.',11);
INSERT INTO `form_CAMOS_item` VALUES (42,NULL,NULL,NULL,NULL,NULL,NULL,'zoloft 50mg 1st month','Zoloft\r\n\r\n#7/seven of the 25mg tablets.\r\n#21/twenty-one of the 50mg tablets.\r\n(on refills, refill #30/thirty 50mg tablets)\r\n\r\nTake one 25mg tablet by mouth daily \r\nfor the first week.   \r\n\r\nTake one 50mg tablet by mouth daily \r\nuntil medically directed to discontinue.\r\n\r\n5 additional refills.\r\n\r\n/*lock::*/',22);
INSERT INTO `form_CAMOS_item` VALUES (44,NULL,NULL,NULL,NULL,NULL,NULL,'Nexium 40mg','Nexium 40mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth daily.  \r\n\r\ntwo additional refills.',9);
INSERT INTO `form_CAMOS_item` VALUES (45,NULL,NULL,NULL,NULL,NULL,NULL,'Prevacid 30mg','Prevacid 30mg\r\n\r\n#30/thirty capsules.  \r\n\r\nOne tablet by mouth daily.',9);
INSERT INTO `form_CAMOS_item` VALUES (46,NULL,NULL,NULL,NULL,NULL,NULL,'mammogram','routine yearly screening mammogram\r\n\r\nv70.0',23);
INSERT INTO `form_CAMOS_item` VALUES (47,NULL,NULL,NULL,NULL,NULL,NULL,'MRI','MRI Lumbar spine.\r\n\r\nDx: chronic low back pain.',4);
INSERT INTO `form_CAMOS_item` VALUES (48,NULL,NULL,NULL,NULL,NULL,NULL,'echocardiogram','Echocardiogram\r\n\r\nDx: rheumatic heart disease',24);
INSERT INTO `form_CAMOS_item` VALUES (50,NULL,NULL,NULL,NULL,NULL,NULL,'prozac','Prozac 20mg\r\n\r\n#30/thirty capsules.\r\n\r\none capsule by mouth once daily.  ',22);
INSERT INTO `form_CAMOS_item` VALUES (51,NULL,NULL,NULL,NULL,NULL,NULL,'walker','',26);
INSERT INTO `form_CAMOS_item` VALUES (52,NULL,NULL,NULL,NULL,NULL,NULL,'Zanaflex 4mg','ZanaFlex 4mg\r\n\r\n#90/ninety.  \r\n\r\nOne by mouth every 8 hours as needed for muscle spasm.',13);
INSERT INTO `form_CAMOS_item` VALUES (55,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 1000mg','azithromycin 1000mg\r\n\r\n#2/two 500mg tablets.\r\n\r\nTake both tablets, one after the other, by mouth, as a single 1000mg dose.',6);
INSERT INTO `form_CAMOS_item` VALUES (56,NULL,NULL,NULL,NULL,NULL,NULL,'phentermine 30mg','#30/thirty tablets.  One tablet by mouth once daily, in the morning.',28);
INSERT INTO `form_CAMOS_item` VALUES (58,NULL,NULL,NULL,NULL,NULL,NULL,'Biaxin XL 500mg','Biaxin XL 500mg\r\n\r\n#28/twenty-eight tablets.  \r\n\r\nTwo tablets by mouth once daily for 14 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (59,NULL,NULL,NULL,NULL,NULL,NULL,'singulair','Singulair 10mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth once daily for allergies.\r\n\r\n/*lock::*/',19);
INSERT INTO `form_CAMOS_item` VALUES (60,NULL,NULL,NULL,NULL,NULL,NULL,'Lipitor 10mg','Lipitor 10mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth daily.\r\n\r\ntwo additional refills.',29);
INSERT INTO `form_CAMOS_item` VALUES (62,NULL,NULL,NULL,NULL,NULL,NULL,'avandia','Avandia 2mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet by mouth twice daily.',30);
INSERT INTO `form_CAMOS_item` VALUES (64,NULL,NULL,NULL,NULL,NULL,NULL,'synthroid','Synthroid 25mcg\r\nSynthroid 50mcg\r\nSynthroid 75mcg\r\nSynthroid 100mcg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet daily.',31);
INSERT INTO `form_CAMOS_item` VALUES (65,NULL,NULL,NULL,NULL,NULL,NULL,'augmentin','Augmentin 875mg\\r\\n\\r\\n#20/twenty.  \\r\\n\\r\\nOne by mouth every twelve hours for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (66,NULL,NULL,NULL,NULL,NULL,NULL,'Cipro HC Otic','Cipro HC Otic\r\n\r\nThree drops in the affected ear(s) every twelve hours for one week.',32);
INSERT INTO `form_CAMOS_item` VALUES (67,NULL,NULL,NULL,NULL,NULL,NULL,'augmentin XR','Augmentin XR 1000 MG\r\n\r\n#40/forty tablets.  \r\n\r\nTwo tablets by mouth every twelve hours for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (68,NULL,NULL,NULL,NULL,NULL,NULL,'Diflucan 150mg','Diflucan 150mg\r\n\r\n#1/one tablet.  \r\n\r\nTake one tablet by mouth.',33);
INSERT INTO `form_CAMOS_item` VALUES (69,NULL,NULL,NULL,NULL,NULL,NULL,'Maxair Autoinhaler','#1/one canister.  Two inhalations every 4-6 hours as needed for shortness of breath due to exacerbations of asthma.',18);
INSERT INTO `form_CAMOS_item` VALUES (71,NULL,NULL,NULL,NULL,NULL,NULL,'Cortisporin Otic Solution','Cortisporin Otic Solution\r\n\r\n4 drops in each ear four times daily for one week.',32);
INSERT INTO `form_CAMOS_item` VALUES (77,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin 875mg','Amoxicillin 875mg\r\n\r\n#20/twenty.  \r\n\r\nOne by mouth every twelve hours for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (78,NULL,NULL,NULL,NULL,NULL,NULL,'armour thyroid','Armour Thyroid 60mg\r\n\r\n#90/ninety\r\n\r\nOne tablet by mouth once daily.  \r\n\r\n3 additional refills.',31);
INSERT INTO `form_CAMOS_item` VALUES (79,NULL,NULL,NULL,NULL,NULL,NULL,'Librax','Librax Capsules\r\n\r\n#90/ninety capsules.  \r\n\r\nOne capsule by mouth up to three times daily, eight hours apart with meals, as needed for short term relief of mild gastrointestinal discomfort.',9);
INSERT INTO `form_CAMOS_item` VALUES (82,NULL,NULL,NULL,NULL,NULL,NULL,'valium','Valium 5mg\\r\\n\\r\\n#12/twelve tablets\\r\\n\\r\\ntake one half to one tablet by mouth every eight hours as \\r\\nneeded for muscle spasm.',13);
INSERT INTO `form_CAMOS_item` VALUES (84,NULL,NULL,NULL,NULL,NULL,NULL,'Zyloprim 100mg','Zyloprim 100mg\r\n\r\n#90/ninety.  \r\n\r\nOne tablet by mouth daily for gout.  \r\n\r\nOne additional refill.',41);
INSERT INTO `form_CAMOS_item` VALUES (85,NULL,NULL,NULL,NULL,NULL,NULL,'Lotrel 5/10','Lotrel 5/10mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth daily for high blood pressure.  \r\n\r\nfive additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (86,NULL,NULL,NULL,NULL,NULL,NULL,'diovan','Diovan 80mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily for high blood pressure.  \r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (87,NULL,NULL,NULL,NULL,NULL,NULL,'venous doppler u/s','venous doppler ultrasound right leg\r\n\r\ndx: right calf pain',42);
INSERT INTO `form_CAMOS_item` VALUES (88,NULL,NULL,NULL,NULL,NULL,NULL,'Avelox 400mg','Avelox 400mg\r\n\r\n#10/ten tablets.  \r\n\r\nOne tablet daily x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (90,NULL,NULL,NULL,NULL,NULL,NULL,'levitra','Levitra 20mg\r\n\r\n#3/three tablets.  \r\n\r\nOne half to one tablet by mouth daily as needed for erectile dysfunction.\r\n\r\n5 additional refills.',10);
INSERT INTO `form_CAMOS_item` VALUES (91,NULL,NULL,NULL,NULL,NULL,NULL,'CT Scan','',5);
INSERT INTO `form_CAMOS_item` VALUES (92,NULL,NULL,NULL,NULL,NULL,NULL,'neck','Ultrasound of anterior neck.  Dx: palpable, non-tender nodules.',3);
INSERT INTO `form_CAMOS_item` VALUES (94,NULL,NULL,NULL,NULL,NULL,NULL,'Xanax','Xanax 2mg\r\n\r\n#90/ninety tablets.\r\n\r\ntake one half to one tablet by mouth every eight hours as needed for anxiety.',12);
INSERT INTO `form_CAMOS_item` VALUES (96,NULL,NULL,NULL,NULL,NULL,NULL,'Zovirax Cream 5%','Zovirax Cream 5%\r\n\r\napply 5 times daily (every 3 hours)\r\nfor 4 days.',44);
INSERT INTO `form_CAMOS_item` VALUES (112,NULL,NULL,NULL,NULL,NULL,NULL,'Medrol Dose Pack','Medrol Dose Pack\r\n\r\nFollow package instructions for use.',48);
INSERT INTO `form_CAMOS_item` VALUES (114,NULL,NULL,NULL,NULL,NULL,NULL,'atenolol','atenolol 50mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth once daily for high blood pressure.\r\n\r\nFive additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (116,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide ER','glipizide ER 2.5mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily.',30);
INSERT INTO `form_CAMOS_item` VALUES (117,NULL,NULL,NULL,NULL,NULL,NULL,'enalapril','Enalapril 5mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth daily for high blood pressure.\r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (118,NULL,NULL,NULL,NULL,NULL,NULL,'low back','X-Ray L/S spine.\r\n\r\nDx: chronic low back pain',2);
INSERT INTO `form_CAMOS_item` VALUES (119,NULL,NULL,NULL,NULL,NULL,NULL,'X-Ray','',2);
INSERT INTO `form_CAMOS_item` VALUES (120,NULL,NULL,NULL,NULL,NULL,NULL,'Tobrex ophthalmic solution','Tobrex Ophthalmic Solution\\r\\n\\r\\n5/five ml.  \\r\\n\\r\\nTwo drops in affected eye(s) every hour \\r\\nfor the first day until sleep and four times \\r\\ndaily (every four hours).',50);
INSERT INTO `form_CAMOS_item` VALUES (121,NULL,NULL,NULL,NULL,NULL,NULL,'lubricating drops','lubricating/wetting eye drops.  use as directed for dryness of the eyes.  follow package instructions.',50);
INSERT INTO `form_CAMOS_item` VALUES (122,NULL,NULL,NULL,NULL,NULL,NULL,'antivert','Antivert 12.5mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet every eight hours prn dizziness.',51);
INSERT INTO `form_CAMOS_item` VALUES (125,NULL,NULL,NULL,NULL,NULL,NULL,'Wellbutrin XL','Wellbutrin XL 300mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet once daily.\r\n\r\ntwo additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (126,NULL,NULL,NULL,NULL,NULL,NULL,'Floxin Otic Solution','Floxin Otic Solution\r\n\r\nTen drops instilled into the affected ear twice daily for fourteen days. ',32);
INSERT INTO `form_CAMOS_item` VALUES (127,NULL,NULL,NULL,NULL,NULL,NULL,'ibuprofen 800mg','Ibuprofen 800mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth every eight hours \r\nas needed for pain.  \r\n\r\nTake with food.',49);
INSERT INTO `form_CAMOS_item` VALUES (128,NULL,NULL,NULL,NULL,NULL,NULL,'lexapro','Lexapro 20mg\r\n\r\n#30/thirty tablets.\r\n\r\nOne tablet by mouth once daily.\r\n\r\n',22);
INSERT INTO `form_CAMOS_item` VALUES (130,NULL,NULL,NULL,NULL,NULL,NULL,'temazepam','temazepam 30mg\r\n\r\n#15/fifteen tablets.\r\n#30/thirty tablets.\r\n\r\nTake one tablet by mouth at bedtime as needed for sleep.',8);
INSERT INTO `form_CAMOS_item` VALUES (132,NULL,NULL,NULL,NULL,NULL,NULL,'lotrisone','Lotrisone Cream\r\n\r\n45 gram tube\r\n\r\napply to affected area twice daily for 14 days.',44);
INSERT INTO `form_CAMOS_item` VALUES (133,NULL,NULL,NULL,NULL,NULL,NULL,'phentermine 37.5mg','Adipex 37.5\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth once daily, in the morning.',28);
INSERT INTO `form_CAMOS_item` VALUES (134,NULL,NULL,NULL,NULL,NULL,NULL,'Klonipin 0.5mg','Klonipin 0.5mg\r\n\r\n#15/fifteen tablets.\r\n#30/thirty tablets.\r\n#60/sixty tablets.\r\n\r\ntake one tablet by mouth every twelve hours as needed for anxiety.',12);
INSERT INTO `form_CAMOS_item` VALUES (135,NULL,NULL,NULL,NULL,NULL,NULL,'Depakote 500mg','',55);
INSERT INTO `form_CAMOS_item` VALUES (138,NULL,NULL,NULL,NULL,NULL,NULL,'toprol','Toprol 25mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily.  \r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (139,NULL,NULL,NULL,NULL,NULL,NULL,'westcort','Westcort Cream\r\n\r\n30 Grams\r\n\r\napply twice daily to small affected area \r\nas directed for one week.',44);
INSERT INTO `form_CAMOS_item` VALUES (141,NULL,NULL,NULL,NULL,NULL,NULL,'pelvic','pelvic ultrasound.  dx: pelvic pain.',3);
INSERT INTO `form_CAMOS_item` VALUES (142,NULL,NULL,NULL,NULL,NULL,NULL,'DynaCirc CR 5mg','DynaCirc CR 5mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily for high blood pressure.  \r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (252,NULL,NULL,NULL,NULL,NULL,NULL,'Lidoderm Patch','Lidoderm Patches\r\n\r\n#30/thirty patches\r\n\r\nApply 1 patch 12 hours on and 12 hours off within a 24 hour period for pain.',44);
INSERT INTO `form_CAMOS_item` VALUES (151,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide','glipizide 10mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet by mouth twice daily.\r\n\r\ntwo additional refills',30);
INSERT INTO `form_CAMOS_item` VALUES (152,NULL,NULL,NULL,NULL,NULL,NULL,'actos','Actos 30mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily for diabetes.',30);
INSERT INTO `form_CAMOS_item` VALUES (153,NULL,NULL,NULL,NULL,NULL,NULL,'Bactroban Cream','Bactroban Cream\r\n\r\n30 Gram.  \r\n\r\napply bid as directed.',44);
INSERT INTO `form_CAMOS_item` VALUES (158,NULL,NULL,NULL,NULL,NULL,NULL,'Klonipin 1mg','Klonipin 1mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet by mouth twice daily\r\nas needed for anxiety.',12);
INSERT INTO `form_CAMOS_item` VALUES (160,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes yearly','CMP, CBC, Lipid Profile, Thyroid function tests (T3, T4, T7, TSH, Total T3, TBG), Hemoglobin A1C, U/A, urine microalbumin.\r\n\r\nDx: 250.03',16);
INSERT INTO `form_CAMOS_item` VALUES (162,NULL,NULL,NULL,NULL,NULL,NULL,'plendil 5mg','Plendil 5mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily for high blood pressure.  \r\n\r\n11 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (305,NULL,NULL,NULL,NULL,NULL,NULL,'Zocor','Zocor 20mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily, at bedtime.\r\n\r\n5 additional refills.',29);
INSERT INTO `form_CAMOS_item` VALUES (165,NULL,NULL,NULL,NULL,NULL,NULL,'norvasc','Norvasc 5mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily for high blood pressure.\r\n\r\n2 additional refills',11);
INSERT INTO `form_CAMOS_item` VALUES (166,NULL,NULL,NULL,NULL,NULL,NULL,'hctz 25mg','HCTZ 12.5mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth daily for high blood pressure.  \r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (170,NULL,NULL,NULL,NULL,NULL,NULL,'cane','cane to assist with ambulatory difficulty due to degenerative joint disease of the ankle, s/p surgery.',26);
INSERT INTO `form_CAMOS_item` VALUES (175,NULL,NULL,NULL,NULL,NULL,NULL,'prozac 10mg','Fluoxetine 20mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth once daily for depression. \r\ntake every day.\r\n\r\n5 additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (177,NULL,NULL,NULL,NULL,NULL,NULL,'DEXA','DEXA - Osteoporosis scan\r\n\r\nDx: 250.03',63);
INSERT INTO `form_CAMOS_item` VALUES (187,NULL,NULL,NULL,NULL,NULL,NULL,'Imitrex 100mg','Imitrex 100mg\r\n\r\n#9/nine.  \r\n\r\nOne tablet by mouth at the earliest sign of a migraine headache.  No more than one tablet in a 24 hour period.\r\n\r\nTwo additional refills.',65);
INSERT INTO `form_CAMOS_item` VALUES (194,NULL,NULL,NULL,NULL,NULL,NULL,'Coumadin','Coumadin 4mg\\r\\n\\r\\n#30/thirty.\\r\\n\\r\\nOne tablet once daily.',68);
INSERT INTO `form_CAMOS_item` VALUES (196,NULL,NULL,NULL,NULL,NULL,NULL,'colchicine','Colchicine 0.6mg\r\n\r\n#30/thirty.  \r\n\r\ntake 2 tablets (1.2mg) by oral route initially, then take 1 tablet every hour until pain is relieved or nausea, vomiting or diarrhea start.',41);
INSERT INTO `form_CAMOS_item` VALUES (197,NULL,NULL,NULL,NULL,NULL,NULL,'indocin','Indocin 50mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth every eight hours \r\nas needed for pain.  \r\n\r\nTake with food.',49);
INSERT INTO `form_CAMOS_item` VALUES (204,NULL,NULL,NULL,NULL,NULL,NULL,'arterial doppler u/s','arterial doppler ultrasound right leg\r\n\r\ndx: right leg pain',42);
INSERT INTO `form_CAMOS_item` VALUES (205,NULL,NULL,NULL,NULL,NULL,NULL,'caduet','Caduet 5/10mg\r\n\r\n#30/thirty\r\n\r\nOne tablet by mouth daily for high blood pressure\r\nand high cholesterol.',71);
INSERT INTO `form_CAMOS_item` VALUES (208,NULL,NULL,NULL,NULL,NULL,NULL,'knee','bilateral knee x-ray\r\n\r\nDx: degenerative joint disease',2);
INSERT INTO `form_CAMOS_item` VALUES (209,NULL,NULL,NULL,NULL,NULL,NULL,'chronic back pain initial','Subjective:\\r\\n\\r\\nChronic back pain due to old injury with periodic acute exacerbations.\\r\\n\\r\\nObjective:\\r\\n\\r\\nvital  signs: stable.\\r\\nheart: rrr\\r\\nlungs: cta\\r\\nback:  decreased range of motion,\\r\\npalpable paravertebral muscle spasms\\r\\nwith tenderness to palpation.\\r\\n\\r\\nAssessment:\\r\\n\\r\\nChronic back pain.\\r\\n\\r\\nPlan:\\r\\n\\r\\nSee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (210,NULL,NULL,NULL,NULL,NULL,NULL,'Flexeril 10mg','Flexeril 10mg\\r\\n\\r\\n#15/fifteen tablets.\\r\\n\\r\\ntake one tablet by mouth at bedtime as needed for muscle spasms.',13);
INSERT INTO `form_CAMOS_item` VALUES (213,NULL,NULL,NULL,NULL,NULL,NULL,'anxiety','Subjective:\r\n\r\nComplains of ongoing anxiety.  Denies suicidal ideations.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\nAssessment:\r\n\r\nanxiety\r\n\r\nPlan:\r\n\r\nSee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (682,NULL,NULL,NULL,NULL,NULL,NULL,'hgh','',82);
INSERT INTO `form_CAMOS_item` VALUES (216,NULL,NULL,NULL,NULL,NULL,NULL,'valtrex','Valtrex 500mg\r\n\r\n#6/six tablets\r\n\r\nOne tablet by mouth twice daily for three days.\r\n\r\n/*for the treatment of recurrent genital herpes*/',76);
INSERT INTO `form_CAMOS_item` VALUES (699,NULL,NULL,'drleeds',NULL,NULL,NULL,'diabetic yearly','/*billing::CPT4::microalbu::microalbumin urine::::0::120.00*/\r\n/*billing::CPT4::86687::hiv::::0::50.00*/\r\n/*billing::CPT4::RPR::RPR::::0::50.00*/\r\n/*billing::CPT4::hep c::hepatitis c::::0::80.00*/\r\n/*billing::CPT4::00000::urinalysis dip stick::::0::25.00*/\r\n/*billing::OTHER::HA1C::HA1C::::0::45.00*/\r\n/*billing::CPT4::80061::lipid pro::::0::45.00*/\r\n/*billing::CPT4::85024::CBC::::0::25.00*/\r\n/*billing::CPT4::80054::CMP::::0::50.00*/',175);
INSERT INTO `form_CAMOS_item` VALUES (692,NULL,NULL,NULL,NULL,NULL,NULL,'safety','The patient was counseled on safety regarding opioid and anxiolytic controlled medications.  Specifically, never give any prescribed medication to another person for any reason.  Take prescribed medication only as directed and only for the reason for which it has been prescribed.  For medication that has been prescribed to be taken as needed for symptomatic relief, take the least amount possible as directed to obtain adequate relief without significant side effects.  Report leftover medication to the physician at the next visit so adjustments can be made.  Keep medication in a safe place, out of the reach of other people, especially children.  A locked box or safe is ideal.  Always report possible side effects, particularly with controlled medication, the possibility of developing psychological dependence.',170);
INSERT INTO `form_CAMOS_item` VALUES (676,NULL,NULL,NULL,NULL,NULL,NULL,'isordil','Isordil 10mg\r\n\r\n#90/ninety\r\n\r\none tablet tid, one half hour before meals.',100);
INSERT INTO `form_CAMOS_item` VALUES (678,NULL,NULL,NULL,NULL,NULL,NULL,'other','',3);
INSERT INTO `form_CAMOS_item` VALUES (679,NULL,NULL,NULL,NULL,NULL,NULL,'basic male','CMP, CBC, Lipid Profile, PSA, TSH, total testosterone, U/A.\r\n\r\nDx: V70.0',16);
INSERT INTO `form_CAMOS_item` VALUES (680,NULL,NULL,NULL,NULL,NULL,NULL,'basic female','CMP, CBC, Lipid Profile, TSH, B-HCG qualitative, U/A.\r\n\r\nDx: V70.0',16);
INSERT INTO `form_CAMOS_item` VALUES (224,NULL,NULL,NULL,NULL,NULL,NULL,'tonsillitis','Subjective:\r\n\r\nsore throat, swollen lymph nodes of anterior neck.\r\n\r\nObjective:\r\nvital  signs: stable.\r\nThroat: tonsils +3\r\nNeck: enlarged anterior neck lymph nodes.\r\nheart: regular rate, rhythm\r\nlungs: clear\r\n\r\nAssessment:\r\n\r\ntonsillitis\r\n\r\nPlan:\r\n\r\nrest, see rx orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (221,NULL,NULL,NULL,NULL,NULL,NULL,'health','Subjective:\r\n\r\nhealth checkup\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\nAssessment:\r\n\r\nhealth checkup\r\n\r\nPlan:\r\n\r\nf/u prn.',73);
INSERT INTO `form_CAMOS_item` VALUES (222,NULL,NULL,NULL,NULL,NULL,NULL,'tamiflu','Tamiflu 75mg\r\n#10\r\n1 tablet by mouth twice daily for five days.',78);
INSERT INTO `form_CAMOS_item` VALUES (223,NULL,NULL,NULL,NULL,NULL,NULL,'uri','Subjective:\r\n\r\nrunny nose, cough\r\n\r\nObjective:\r\nHEENT: ears unremarkable, throat: red\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: clear\r\nabdomen: unremarkable\r\n\r\nAssessment:\r\n\r\nupper respiratory infection\r\n\r\nPlan:\r\n\r\nrest, see rx orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (226,NULL,NULL,NULL,NULL,NULL,NULL,'htn','Subjective:\r\n\r\nDenies chest pain and shortness of breath.\r\n\r\nObjective:\r\n\r\nvital signs stable\r\nheart: regular rate and rhythm\r\nlungs: clear\r\n\r\nAssessment:\r\n\r\nhypertension.\r\n\r\nPlan:\r\n\r\nDecrease salt intake, avoid caffiene and alcohol.  Eat healthy foods, including fruits and vegetables.  Avoid fatty foods, including beef, pork, butter.  Read food labels to identify fat content.  Exercise regularly.  A daily, moderately paced, 30 minute walk is recommended.  Check blood pressure regularly.  I recommend that the patient purchase an Omron HEM-711 home blood pressure monitor and check it daily and keep a log.  If the patient experiences chest pain, shortness of breath, severe headache, neurological changes (for example: vision loss, weakness or loss of function of extremities), or any other concerning symptoms, call 911 immediately.\r\n',73);
INSERT INTO `form_CAMOS_item` VALUES (230,NULL,NULL,NULL,NULL,NULL,NULL,'dicloxacillin','dicloxacillin 250mg\r\n\r\n#40/forty\r\n\r\none tablet by mouth every six hours for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (231,NULL,NULL,NULL,NULL,NULL,NULL,'zyrtec','Zyrtec 10mg\\r\\n\\r\\n#30/thirty tablets.  \\r\\n\\r\\nOne tablet by mouth once daily for allergies.\\r\\n',19);
INSERT INTO `form_CAMOS_item` VALUES (233,NULL,NULL,NULL,NULL,NULL,NULL,'testicular','',3);
INSERT INTO `form_CAMOS_item` VALUES (235,NULL,NULL,NULL,NULL,NULL,NULL,'progesterone','Medroxyprogesterone acetate 10mg tablets\r\n\r\n#10/ten tablets\r\n\r\nOne tablet by mouth once daily for 10 days.\r\n',82);
INSERT INTO `form_CAMOS_item` VALUES (237,NULL,NULL,NULL,NULL,NULL,NULL,'strips','50 Blood Glucose Test Strips\r\n\r\nDx: 250.00',83);
INSERT INTO `form_CAMOS_item` VALUES (238,NULL,NULL,NULL,NULL,NULL,NULL,'djd knee','Subjective:\r\n\r\nright knee pain\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nright knee: crepitus, decreased range of motion, swelling.\r\n\r\nAssessment:\r\n\r\ndjd\r\n\r\nPlan:\r\n\r\nsee orders',73);
INSERT INTO `form_CAMOS_item` VALUES (240,NULL,NULL,NULL,NULL,NULL,NULL,'otitis','Subjective:\r\n\r\near discomfort\r\n\r\nObjective:\r\nvital  signs: stable.\r\nHEENT: ear: tm dull appearing, exudate in canal.\r\nheart: rrr\r\nlungs: clear\r\n\r\nAssessment:\r\n\r\nright ear infection\r\n\r\nPlan:\r\n\r\nrest, see rx orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (242,NULL,NULL,NULL,NULL,NULL,NULL,'phenergan','Phenergan 25mg Suppository\r\n\r\n#30/thiry\r\n\r\nUse one suppository rectally as directed every\r\nfour to six hours as needed for nausea.',85);
INSERT INTO `form_CAMOS_item` VALUES (243,NULL,NULL,NULL,NULL,NULL,NULL,'aciphex','Aciphex 20mg\r\n\r\n#30/thirty\r\n\r\nOne by mouth daily.\r\n\r\nTwo additional refills.',9);
INSERT INTO `form_CAMOS_item` VALUES (245,NULL,NULL,NULL,NULL,NULL,NULL,'nebulizer','Nebulizer with mask kit.\r\n\r\nDx: 493.9 Asthma',87);
INSERT INTO `form_CAMOS_item` VALUES (246,NULL,NULL,NULL,NULL,NULL,NULL,'albuterol mix','Albuterol 2.5mg/3cc NS\r\n\r\n#30/thirty units\r\n\r\nUse one unit in nebulizer as directed for breathing treatment every six hours as needed for shortness of breath.',18);
INSERT INTO `form_CAMOS_item` VALUES (247,NULL,NULL,NULL,NULL,NULL,NULL,'sinusitis','Subjective:\r\n\r\nsinus tenderness, congestion.\r\n\r\nObjective:\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: clear\r\n\r\nAssessment:\r\n\r\nsinusitis\r\n\r\nPlan:\r\n\r\nrest, see rx orders.\r\n\r\n/*lock::*/',73);
INSERT INTO `form_CAMOS_item` VALUES (253,NULL,NULL,NULL,NULL,NULL,NULL,'pharyngitis','Subjective:\r\n\r\nthroat discomfort\r\n\r\nObjective:\r\nvital  signs: stable.\r\nthroat: red.\r\nheart: rrr\r\nlungs: clear\r\n\r\nAssessment:\r\n\r\npharyngitis\r\n\r\nPlan:\r\n\r\nrest, see rx orders.\r\n\r\n\r\n/*lock::*/',73);
INSERT INTO `form_CAMOS_item` VALUES (302,NULL,NULL,NULL,NULL,NULL,NULL,'clonidine','clonidine 0.1mg\r\n\r\n#60/sixty tablets.  \r\n\r\n1 tablet by mouth every 12 hours for high blood pressure.',11);
INSERT INTO `form_CAMOS_item` VALUES (254,NULL,NULL,NULL,NULL,NULL,NULL,'plavix','Plavix 75mg\r\n\r\n#30/thirty\r\n\r\n1 tablet by mouth once daily.\r\n\r\n5 additional refills.',89);
INSERT INTO `form_CAMOS_item` VALUES (249,NULL,NULL,NULL,NULL,NULL,NULL,'dilantin','Dilantin (Phenytoin Sodium) 100mg\r\n\r\n#90/ninety\r\n\r\n1 capsule by mouth every morning\r\nand 2 capsules by mouth every night at\r\nbedtime.',88);
INSERT INTO `form_CAMOS_item` VALUES (251,NULL,NULL,NULL,NULL,NULL,NULL,'skin infection','Subjective:\r\n\r\nskin infection\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\ninflammed area on back.\r\n\r\nAssessment:\r\n\r\nskin infection\r\n\r\nPlan:\r\n\r\nsee orders',73);
INSERT INTO `form_CAMOS_item` VALUES (255,NULL,NULL,NULL,NULL,NULL,NULL,'pletal','Pletal 100mg\r\n\r\n#60/sixty tablets.\r\n\r\n1 tablet by mouth twice daily.\r\n\r\n5 additional refills.',89);
INSERT INTO `form_CAMOS_item` VALUES (256,NULL,NULL,NULL,NULL,NULL,NULL,'propecia','Propecia 1mg\r\n\r\n#30/thirty\r\n\r\nOne tablet by mouth once daily.\r\n\r\nTwo additional refills.',90);
INSERT INTO `form_CAMOS_item` VALUES (258,NULL,NULL,NULL,NULL,NULL,NULL,'valium','Valium 10mg\r\n\r\n#90/ninety tablets\r\n\r\ntake one tablet by mouth every eight hours as needed for anxiety.',12);
INSERT INTO `form_CAMOS_item` VALUES (259,NULL,NULL,NULL,NULL,NULL,NULL,'Nizoral Cream','Nizoral Cream\r\n\r\n#30/thirty gram tube\r\n\r\napply bid x 2 weeks.',44);
INSERT INTO `form_CAMOS_item` VALUES (260,NULL,NULL,NULL,NULL,NULL,NULL,'fioricet plain','Fioricet Plain\r\n\r\n#60/sixty\r\n\r\none by mouth every 6 hours as needed for headache.',65);
INSERT INTO `form_CAMOS_item` VALUES (262,NULL,NULL,NULL,NULL,NULL,NULL,'Advair Disc','Advair Disc 100/50\r\n\r\n#1/one disc\r\n\r\nOne inhalation every 12 hours for asthma.\r\n\r\n11 additional refills.\r\n\r\n',18);
INSERT INTO `form_CAMOS_item` VALUES (264,NULL,NULL,NULL,NULL,NULL,NULL,'allopurinol','allopurinol 100mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily for gout.',41);
INSERT INTO `form_CAMOS_item` VALUES (267,NULL,NULL,NULL,NULL,NULL,NULL,'KCL ER','KCL ER 10meq\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily.',11);
INSERT INTO `form_CAMOS_item` VALUES (269,NULL,NULL,NULL,NULL,NULL,NULL,'flovent','Flovent 110 mcg\r\n\r\none unit\r\n\r\n2 inhalations every 12 hours for asthma.',18);
INSERT INTO `form_CAMOS_item` VALUES (271,NULL,NULL,NULL,NULL,NULL,NULL,'prenatal','Citracal Prenatal Vitamins\r\n\r\n#30/thirty\r\n\r\nOne tablet by mouth once daily.\r\n\r\n2 additional refills.',93);
INSERT INTO `form_CAMOS_item` VALUES (273,NULL,NULL,NULL,NULL,NULL,NULL,'halcion','Halcion 0.25mg\r\n\r\n#15/fifteen tablets.\r\n#30/thirty tablets.\r\n\r\nTake one tablet by mouth at bedtime as needed for sleep.',8);
INSERT INTO `form_CAMOS_item` VALUES (687,NULL,NULL,NULL,NULL,NULL,NULL,'exforge','Exforge 10/160mg\r\n\r\n#30/thirty\r\n\r\n1/2 tablet by mouth once daily for high blood pressure.',11);
INSERT INTO `form_CAMOS_item` VALUES (688,NULL,NULL,NULL,NULL,NULL,NULL,'tenoretic','Tenoretic 50mg\r\n\r\n#30/thirty tablets\r\n\r\nOne tablet by mouth once daily for high blood pressure.\r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (281,NULL,NULL,NULL,NULL,NULL,NULL,'Naprosyn','Naprosyn 500mg\r\n\r\n#30/thirty tablets\r\n\r\none tablet by mouth every 12 hours as needed for pain',49);
INSERT INTO `form_CAMOS_item` VALUES (282,NULL,NULL,NULL,NULL,NULL,NULL,'lidocaine-hydrocortisone','Lidocaine HCL 3% - Hydrocortisone Acetate 0.5% Cream\r\n\r\n#7/Seven Gram Tube\r\n\r\nUse as directed',44);
INSERT INTO `form_CAMOS_item` VALUES (284,NULL,NULL,NULL,NULL,NULL,NULL,'trazadone','Trazadone 50mg\r\n\r\n#90/ninety tablets\r\n\r\n150mg(3 tablets) qhs.\r\n\r\n',22);
INSERT INTO `form_CAMOS_item` VALUES (286,NULL,NULL,NULL,NULL,NULL,NULL,'glucometer','Glucometer with 100 test strips\r\n\r\ndirections: test three to four times daily.\r\n\r\nDx: 250.00 \r\ninsulin dependent diabetes',83);
INSERT INTO `form_CAMOS_item` VALUES (287,NULL,NULL,NULL,NULL,NULL,NULL,'lancets','50 lancets\r\n\r\nDx: 250.00',83);
INSERT INTO `form_CAMOS_item` VALUES (288,NULL,NULL,NULL,NULL,NULL,NULL,'zestoretic','Zestoretic 10/12.5mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily.\r\n\r\n2 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (289,NULL,NULL,NULL,NULL,NULL,NULL,'Cialis','Cialis 20mg\r\n\r\n#6/six tablets.  \r\n\r\nOne tablet by mouth q72hrs as \r\nneeded for sexual activity. \r\n\r\n6 additional refills\r\n\r\n/*lock::*/',10);
INSERT INTO `form_CAMOS_item` VALUES (290,NULL,NULL,NULL,NULL,NULL,NULL,'compound lozenges','Progesterone 50mg lozenge\r\nDirections: one lozenge sublingually at bedtime\r\n\r\nE1E2 0.05mg/E3 0.4mg/ Testosterone 1mg lozenge\r\nDirections: one lozenge sublingually twice daily\r\n\r\nDHEA 5mg Drop\r\nDirections: one to two drops sublingually once daily\r\n\r\n\r\nDispense either one month supply with six total refills\r\nor three month supply with two total refills.\r\n(that\'s a total of six months either way)',82);
INSERT INTO `form_CAMOS_item` VALUES (293,NULL,NULL,NULL,NULL,NULL,NULL,'zoloft','Zoloft 50mg\r\n\r\n#30/thirty tablets.\r\n\r\none tablet by mouth once daily.\r\n\r\n5 additional refills.\r\n\r\n/*lock::*/',22);
INSERT INTO `form_CAMOS_item` VALUES (294,NULL,NULL,NULL,NULL,NULL,NULL,'kidney stone','Subjective:\r\n\r\nfour days polyuria and right flank pain\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd:   soft, mild tenderness to palpation right lower quad.  bs present x 4q.  +right cva tenderness.\r\n\r\nAssessment:\r\n\r\nrenal stone\r\n\r\nPlan:\r\n\r\nincrease fluids',73);
INSERT INTO `form_CAMOS_item` VALUES (295,NULL,NULL,NULL,NULL,NULL,NULL,'renal','Renal Ultrasound\r\n\r\nRight side\r\n\r\nDx: nephrolithiasis, eval for hydronephrosis',3);
INSERT INTO `form_CAMOS_item` VALUES (296,NULL,NULL,NULL,NULL,NULL,NULL,'abdomen','',2);
INSERT INTO `form_CAMOS_item` VALUES (297,NULL,NULL,NULL,NULL,NULL,NULL,'other','Subjective:\r\n\r\nleft shoulder pain from lifting at work.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\nleft shoulder: palpable muscle spasms, decreased rom.\r\n\r\nAssessment:\r\n\r\nmuscle spasm,health checkup\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (299,NULL,NULL,NULL,NULL,NULL,NULL,'diovan hct','Diovan HCT 160/12.5mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily for high blood pressure. \r\n \r\n3 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (300,NULL,NULL,NULL,NULL,NULL,NULL,'neurontin','',88);
INSERT INTO `form_CAMOS_item` VALUES (301,NULL,NULL,NULL,NULL,NULL,NULL,'silvadene','Silvadene Cream 1%\r\n\r\n30/thirty grams\r\n\r\nApply bid x 10 days\r\n',44);
INSERT INTO `form_CAMOS_item` VALUES (303,NULL,NULL,NULL,NULL,NULL,NULL,'Prevpac','Prevpac\r\n\r\nDispense: Two week supply\r\n\r\nDirections: Take as directed for two weeks\r\n\r\n',9);
INSERT INTO `form_CAMOS_item` VALUES (327,NULL,NULL,NULL,NULL,NULL,NULL,'lamisil tablets','Lamisil 250mg\r\n\r\n#90/ninety tablets.\r\n\r\nOne tablet daily for three months.\r\n\r\n',102);
INSERT INTO `form_CAMOS_item` VALUES (304,NULL,NULL,NULL,NULL,NULL,NULL,'metformin','metformin 1000mg\r\n\r\n#60/sixty tablets\r\n\r\nOne tablet by mouth twice daily for diabetes.',30);
INSERT INTO `form_CAMOS_item` VALUES (306,NULL,NULL,NULL,NULL,NULL,NULL,'hepatic','Hepatic Ultrasound\r\n\r\nDx: abdominal distension, 789.3.',3);
INSERT INTO `form_CAMOS_item` VALUES (308,NULL,NULL,NULL,NULL,NULL,NULL,'lotrimin','lotrimin cream\r\n\r\n#30/thirty gram tube\r\n\r\napply bid 2 weeks',33);
INSERT INTO `form_CAMOS_item` VALUES (309,NULL,NULL,NULL,NULL,NULL,NULL,'zestril','Zestril 10mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth daily.\r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (310,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 2000mg','azithromycin 2000mg\r\n\r\n#4/four 500mg tablets.\r\n\r\nTake four 500mg tablets, one after the other,\r\nby mouth, as a single 2000mg dose.',6);
INSERT INTO `form_CAMOS_item` VALUES (311,NULL,NULL,NULL,NULL,NULL,NULL,'voltaren xr','Voltaren XR 100mg\r\n\r\n#30/thirty\r\n\r\nOne tablet once daily.\r\n\r\n5 additional refills.',49);
INSERT INTO `form_CAMOS_item` VALUES (313,NULL,NULL,NULL,NULL,NULL,NULL,'abdominal','abdominal aortic ultrasound\r\n\r\ndx: abdominal aortic bruit auscultated',3);
INSERT INTO `form_CAMOS_item` VALUES (314,NULL,NULL,NULL,NULL,NULL,NULL,'Toprol XL','Toprol XL 50mg\r\n\r\n#30/thirty.  \r\n\r\none tablet by mouth once daily.  \r\n\r\n2 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (315,NULL,NULL,NULL,NULL,NULL,NULL,'Crestor','Crestor 10mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet by mouth once daily.  Two month supply.',29);
INSERT INTO `form_CAMOS_item` VALUES (317,NULL,NULL,NULL,NULL,NULL,NULL,'tetracycline','tetracycline 500mg\r\n\r\n#40/forty.\r\n\r\nOne po q6hrs x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (318,NULL,NULL,NULL,NULL,NULL,NULL,'maxifed','Maxifed DM\r\n\r\n#12/twelve tablets.\r\n\r\nOne tablet every 12 hours as needed for congestion',99);
INSERT INTO `form_CAMOS_item` VALUES (319,NULL,NULL,NULL,NULL,NULL,NULL,'other','',2);
INSERT INTO `form_CAMOS_item` VALUES (320,NULL,NULL,NULL,NULL,NULL,NULL,'lisinopril','Lisinopril 20mg\r\n\r\n#30/thirty\r\n\r\none tablet once daily for blood pressure',11);
INSERT INTO `form_CAMOS_item` VALUES (321,NULL,NULL,NULL,NULL,NULL,NULL,'Omeprazole','Omeprazole 20mg\r\n\r\n#60/sixty\r\n\r\nOne tablet twice daily.',9);
INSERT INTO `form_CAMOS_item` VALUES (322,NULL,NULL,NULL,NULL,NULL,NULL,'digoxin','Digoxin 0.125mg\r\n\r\n#15/fifteen\r\n\r\n1/2 tablet once daily.',100);
INSERT INTO `form_CAMOS_item` VALUES (323,NULL,NULL,NULL,NULL,NULL,NULL,'Plavix','Plavix 75mg\r\n\r\n#30/thirty\r\n\r\nOne tablet once daily.',68);
INSERT INTO `form_CAMOS_item` VALUES (324,NULL,NULL,NULL,NULL,NULL,NULL,'trental','Trental 400mg\r\n\r\n#90/ninety\r\n\r\n1 tablet by mouth three times daily.',89);
INSERT INTO `form_CAMOS_item` VALUES (326,NULL,NULL,NULL,NULL,NULL,NULL,'GERD','Subjective:\r\n\r\nGERD\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\n\r\nAssessment:\r\n\r\nGERD\r\n\r\nPlan:\r\n\r\nsee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (329,NULL,NULL,NULL,NULL,NULL,NULL,'Wellbutrin SR','Wellbutrin SR 150mg\r\n\r\n#60/sixty\r\n\r\nOne twice daily.\r\n\r\nFive additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (330,NULL,NULL,NULL,NULL,NULL,NULL,'singulair 4mg chewable','Singulair 4mg Chewable Tablet\r\n\r\n#30/thirty tablets\r\n\r\nOne tablet by mouth once every evening.\r\n\r\n5 additional refills.\r\n\r\n/*\r\n\r\nfor 2-5 year old children\r\n\r\n*/',18);
INSERT INTO `form_CAMOS_item` VALUES (331,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin suspension','Azithromycin Suspension 250mg/5cc\r\n\r\nDirections: 5cc (one tspn) once daily for 5 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (332,NULL,NULL,NULL,NULL,NULL,NULL,'preop01','CMP, CBC, PT/PTT, U/A',103);
INSERT INTO `form_CAMOS_item` VALUES (333,NULL,NULL,NULL,NULL,NULL,NULL,'ativan','Ativan 0.5mg\r\n\r\n#30/thirty tablets.\r\n\r\nOne tablet at bedtime as needed for anxiety.\r\n\r\n2 additional refills.',12);
INSERT INTO `form_CAMOS_item` VALUES (334,NULL,NULL,NULL,NULL,NULL,NULL,'paxil','Paxil 20mg\r\n\r\n#30/thirty tablets.  \r\n\r\none tablet by mouth once daily.  \r\n\r\nTwo additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (337,NULL,NULL,NULL,NULL,NULL,NULL,'TRANSDERM-SCOP 1.5 MG/72HR','TRANSDERM-SCOP 1.5 MG/72HR\r\n\r\n#8/eight patches\r\n\r\napply one patch every three days as needed for motion sickness.',104);
INSERT INTO `form_CAMOS_item` VALUES (338,NULL,NULL,NULL,NULL,NULL,NULL,'clindamycin','Clindamycin 300mg capsules\r\n\r\n#40/forty capsules.\r\n\r\none capsule every six hours x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (339,NULL,NULL,NULL,NULL,NULL,NULL,'xopenex','Xopenex HFA\r\n\r\none unit\r\n\r\n2 inhalations every four to six hours as needed for shortness of breath.',18);
INSERT INTO `form_CAMOS_item` VALUES (340,NULL,NULL,NULL,NULL,NULL,NULL,'Proctofoam HC','Proctofoam HC\r\n\r\n#1/one unit\r\n\r\nUse 2-3 times daily as needed.',9);
INSERT INTO `form_CAMOS_item` VALUES (341,NULL,NULL,NULL,NULL,NULL,NULL,'allegra','Allegra 180mg\r\n\r\n#30/thirty tablets.\r\n\r\nOne tablet by mouth once daily for allergies.\r\n\r\n5 additional refills.',19);
INSERT INTO `form_CAMOS_item` VALUES (347,NULL,NULL,NULL,NULL,NULL,NULL,'lisinopril HCT','Lisinopril HCTZ 10/12.5mg\r\n\r\n#30/thirty\r\n\r\nOne tablet once daily for blood pressure.\r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (348,NULL,NULL,NULL,NULL,NULL,NULL,'oxybutynin chloride','oxybutynin chloride 5mg\r\n\r\n#120/one hundred twenty\r\n\r\none tablet every six hours\r\n\r\nthree additional refills.',108);
INSERT INTO `form_CAMOS_item` VALUES (349,NULL,NULL,NULL,NULL,NULL,NULL,'electric wheelchair','',26);
INSERT INTO `form_CAMOS_item` VALUES (350,NULL,NULL,NULL,NULL,NULL,NULL,'tigan','tigan 200mg rectal suppository\r\n\r\n#20/twenty\r\n\r\ninsert 1 suppository (200mg) by rectal route 3 times per day as needed',85);
INSERT INTO `form_CAMOS_item` VALUES (351,NULL,NULL,NULL,NULL,NULL,NULL,'Zetia','Zetia 10mg\r\n\r\n#30/thirty\r\n\r\nOne tablet once daily for high cholesterol.\r\n\r\n3 additional refills.',29);
INSERT INTO `form_CAMOS_item` VALUES (352,NULL,NULL,NULL,NULL,NULL,NULL,'Diprolene','Diprolene (Betamethasone Dipropionate Ointment USP, 0.05%)\r\n\r\ndispense: 45/forty-five gram tube.\r\n\r\nDirections: apply thin film to affected area daily for 1-2 weeks.',44);
INSERT INTO `form_CAMOS_item` VALUES (354,NULL,NULL,NULL,NULL,NULL,NULL,'cymbalta','Cymbalta 30mg\r\n\r\n#30/thirty\r\n\r\nOne tablet once daily.\r\n\r\ntwo additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (355,NULL,NULL,NULL,NULL,NULL,NULL,'diamox','',109);
INSERT INTO `form_CAMOS_item` VALUES (356,NULL,NULL,NULL,NULL,NULL,NULL,'hycodan','Hycodan cough syrup\r\n\r\n#8/eight ounces\r\n\r\none tspn q6hrs prn cough.',20);
INSERT INTO `form_CAMOS_item` VALUES (358,NULL,NULL,NULL,NULL,NULL,NULL,'triamcinolone','triamcinolone acetonide cream usp, 0.1%\r\n\r\napply bid as directed for 1-2 weeks.',44);
INSERT INTO `form_CAMOS_item` VALUES (360,NULL,NULL,NULL,NULL,NULL,NULL,'b12','B12 vial\r\n\r\n#25/twenty-five syringes with needles.\r\n\r\n1/2 cc once weekly',93);
INSERT INTO `form_CAMOS_item` VALUES (361,NULL,NULL,NULL,NULL,NULL,NULL,'prednisone','Prednisone 5mg\r\n\r\n#28/twenty eight tablets\r\n\r\ntapering dose: \r\n\r\ntake 7 pills by mouth on day 1 (35mg)\r\ntake 6 pills by mouth on day 2 (30mg)\r\ntake 5 pills by mouth on day 3 (25mg)\r\ntake 4 pills by mouth on day 4 (20mg)\r\ntake 3 pills by mouth on day 5 (15mg)\r\ntake 2 pills by mouth on day 6 (10mg)\r\ntake 1 pill  by mouth on day 7 (5mg)\r\n\r\n\r\n/*lock::*/',48);
INSERT INTO `form_CAMOS_item` VALUES (365,NULL,NULL,NULL,NULL,NULL,NULL,'tenuate cr','Tenuate 75mg CR\r\n\r\n#30/thirty\r\n\r\none daily\r\n\r\none additional refill.',28);
INSERT INTO `form_CAMOS_item` VALUES (366,NULL,NULL,NULL,NULL,NULL,NULL,'seroquel','Seroquel 300mg\r\n\r\n#30/thirty\r\n\r\n1 po qhs',22);
INSERT INTO `form_CAMOS_item` VALUES (367,NULL,NULL,NULL,NULL,NULL,NULL,'aclovate cream','ACLOVATE 0.05% CREAM\r\n\r\n15/fifteen Gram\r\n\r\nApply once daily for 1-2 weeks as directed.',44);
INSERT INTO `form_CAMOS_item` VALUES (369,NULL,NULL,NULL,NULL,NULL,NULL,'lipid monitoring','Lipid Panel, Hepatic Panel.\r\n\r\n272.4 hyperlipidemia\r\n\r\n\r\n/*lock::*/',16);
INSERT INTO `form_CAMOS_item` VALUES (370,NULL,NULL,NULL,NULL,NULL,NULL,'provigil','Provigil 200mg\r\n\r\n#30/thirty tablets\r\n\r\nTake 1/2 to 1 tablet every morning as needed.',113);
INSERT INTO `form_CAMOS_item` VALUES (372,NULL,NULL,NULL,NULL,NULL,NULL,'H. A1C','H. A1C\r\n\r\nDx: 250.00',114);
INSERT INTO `form_CAMOS_item` VALUES (373,NULL,NULL,NULL,NULL,NULL,NULL,'micardis hct','micardis 80/12.5mg\r\n\r\n#90/ninety.  \r\n\r\nOne tablet by mouth once daily \r\nfor high blood pressure.\r\n\r\n3 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (374,NULL,NULL,NULL,NULL,NULL,NULL,'hydralazine','hydralazine 25mg\r\n\r\n#90/ninety.  \r\n\r\nOne tablet by mouth once daily \r\nfor high blood pressure.\r\n\r\n3 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (375,NULL,NULL,NULL,NULL,NULL,NULL,'spironolactone','Spironolactone 25mg\r\n\r\n#180/one hundred eighty.  \r\n\r\nOne tablet by mouth twice daily\r\nfor high blood pressure.\r\n\r\n3 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (377,NULL,NULL,NULL,NULL,NULL,NULL,'UTI','Subjective:\r\n\r\ndiscomfort with urination\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\n\r\nAssessment:\r\n\r\nUTI\r\n\r\nPlan:\r\n\r\nsee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (378,NULL,NULL,NULL,NULL,NULL,NULL,'celexa','Celexa 40mg\r\n\r\n#30/thirty tablets.\r\n\r\nOne tablet by mouth once daily.\r\n\r\n2 additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (379,NULL,NULL,NULL,NULL,NULL,NULL,'Acute Gastroenteritis','Subjective:\r\n\r\ndiarrhea, nausea and vomiting.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: soft, nontender, bs+ x4q\r\n\r\nAssessment:\r\n\r\nacute gastroenteritis\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (381,NULL,NULL,NULL,NULL,NULL,NULL,'actonel','Actonel 35mg\r\n\r\n#4/four tablets\r\n\r\nOne tablet once weekly.\r\n\r\nTwo additional refills.',64);
INSERT INTO `form_CAMOS_item` VALUES (383,NULL,NULL,NULL,NULL,NULL,NULL,'maxalt','Maxalt 10mg\r\n\r\n#9/nine.  \r\n\r\nOne tablet by mouth at the earliest sign of a migraine headache.  No more than one tablet in a 24 hour period.\r\n\r\nTwo additional refills.',65);
INSERT INTO `form_CAMOS_item` VALUES (384,NULL,NULL,NULL,NULL,NULL,NULL,'urethritis','Subjective:\r\n\r\ndiscomfort with urination, penile discharge\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\n\r\nAssessment:\r\n\r\nurethritis\r\n\r\nPlan:\r\n\r\nsee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (385,NULL,NULL,NULL,NULL,NULL,NULL,'gastroenteritis','Subjective:\r\n\r\ndiarrhea for 2 days.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: nontender, bs+ x 4q.\r\n\r\nAssessment:\r\n\r\nacute gastroenteritis\r\n\r\nPlan:\r\n\r\nsee orders',73);
INSERT INTO `form_CAMOS_item` VALUES (502,NULL,NULL,NULL,NULL,NULL,NULL,'breast','',3);
INSERT INTO `form_CAMOS_item` VALUES (503,NULL,NULL,NULL,NULL,NULL,NULL,'requip','requip 0.25mg\r\n\r\n#30/thirty.\r\n\r\nTake 1 tablet (0.25 mg) by oral route 1-3 hours before bedtime.',145);
INSERT INTO `form_CAMOS_item` VALUES (505,NULL,NULL,NULL,NULL,NULL,NULL,'nitroglycerin sublingual spray','nitroglycerin sublingual spray\r\n\r\nuse as directed',100);
INSERT INTO `form_CAMOS_item` VALUES (506,NULL,NULL,NULL,NULL,NULL,NULL,'sonata','Sonata 10mg\r\n\r\n#60/sixty\r\n\r\none or two tablets at bedtime as needed for sleep.\r\n',8);
INSERT INTO `form_CAMOS_item` VALUES (527,NULL,NULL,NULL,NULL,NULL,NULL,'dyazide','triamterene 37.5mg/HCTZ 25mg\r\n\r\n#90/ninety\r\n\r\nTake one tablet by mouth every day.',11);
INSERT INTO `form_CAMOS_item` VALUES (387,NULL,NULL,NULL,NULL,NULL,NULL,'phenobarbital','Dilantin (Phenytoin Sodium) 100mg\r\n\r\n#90/ninety\r\n\r\n1 capsule by mouth every morning\r\nand 2 capsules by mouth every night at\r\nbedtime.',88);
INSERT INTO `form_CAMOS_item` VALUES (388,NULL,NULL,NULL,NULL,NULL,NULL,'amaryl','',30);
INSERT INTO `form_CAMOS_item` VALUES (393,NULL,NULL,NULL,NULL,NULL,NULL,'Mintezol','Mintezol 500mg\r\n\r\ndispense: 12/twelve tablets\r\n\r\ndirections: chew 3 tablets (1.5g) by oral route 2 times per day after meals for 2 days.  for cutaneous larva migrans.',116);
INSERT INTO `form_CAMOS_item` VALUES (397,NULL,NULL,NULL,NULL,NULL,NULL,'3 month','H. A1C, Lipid Panel.\r\n\r\nDx: 250.00',114);
INSERT INTO `form_CAMOS_item` VALUES (406,NULL,NULL,NULL,NULL,NULL,NULL,'Ritalin','/*CAMOS::prescriptions::psychiatric::ritalin::ritalin 10mg\\r\\n\\r\\n#60/sixty\\r\\n\\r\\none tablet by mouth twice daily.*/\\r\\n',124);
INSERT INTO `form_CAMOS_item` VALUES (407,NULL,NULL,NULL,NULL,NULL,NULL,'allergic rhinitis','Subjective:\r\n\r\nnasal congestion, sneezing.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\n\r\nAssessment:\r\n\r\nallergy\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (441,NULL,NULL,NULL,NULL,NULL,NULL,'deconamine','deconamine\r\n\r\n#90/ninety\r\n\r\none by mouth three times daily as needed for allergies.\r\n',19);
INSERT INTO `form_CAMOS_item` VALUES (408,NULL,NULL,NULL,NULL,NULL,NULL,'Elimite','Elimite Cream\r\n\r\n60/sixty grams\r\n\r\napply head to toe (not on the face) and leave on for 12-14 hours.  Then, wash off with soap and water.',44);
INSERT INTO `form_CAMOS_item` VALUES (412,NULL,NULL,NULL,NULL,NULL,NULL,'glucovance','glucovance 5/500mg\r\n\r\n#60/sixty\r\n\r\nOne tablet by mouth every 12 hours.\r\n\r\nFive additional refills.',30);
INSERT INTO `form_CAMOS_item` VALUES (414,NULL,NULL,NULL,NULL,NULL,NULL,'liver/gb','Liver and Gallbladder Ultrasound\r\n\r\nDx: right upper quadrant pain.',3);
INSERT INTO `form_CAMOS_item` VALUES (415,NULL,NULL,NULL,NULL,NULL,NULL,'aricept','Aricept 10mg\r\n\r\n#30/thirty\r\n\r\none tablet by mouth once daily for memory loss.\r\n\r\nfive additional refills.',126);
INSERT INTO `form_CAMOS_item` VALUES (416,NULL,NULL,NULL,NULL,NULL,NULL,'Namenda','Namenda Titration Pack\r\n\r\nUse as directed.  Follow up in the office one week before the pack is finished.',126);
INSERT INTO `form_CAMOS_item` VALUES (417,NULL,NULL,NULL,NULL,NULL,NULL,'Ambien CR','Ambien CR 12.5mg\r\n\r\n#15/fifteen tablets.\r\n#30/thirty tablets.\r\n\r\nTake one tablet by mouth at bedtime as needed for sleep.',8);
INSERT INTO `form_CAMOS_item` VALUES (420,NULL,NULL,NULL,NULL,NULL,NULL,'vasotec','Vasotec 10mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily.\r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (422,NULL,NULL,NULL,NULL,NULL,NULL,'heating pad','heating pad\r\n\r\n724.2 back pain\r\n',26);
INSERT INTO `form_CAMOS_item` VALUES (423,NULL,NULL,NULL,NULL,NULL,NULL,'neck pain','Subjective:\r\n\r\nChronic neck pain due to old injury with periodic acute exacerbations.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nneck:  decreased range of motion,\r\n       palpable paravertebral muscle spasms\r\n       with tenderness to palpation.\r\n\r\nAssessment:\r\n\r\nChronic neck pain.\r\n\r\nPlan:\r\n\r\nSee orders.\r\n',73);
INSERT INTO `form_CAMOS_item` VALUES (426,NULL,NULL,NULL,NULL,NULL,NULL,'nitroglycerin transdermal system','Nitroglycerin Transdermal System\r\n0.2mg/hr\r\n\r\n#30 systems\r\n\r\napply one patch daily as directed\r\n\r\ntwo additional refills.',100);
INSERT INTO `form_CAMOS_item` VALUES (428,NULL,NULL,NULL,NULL,NULL,NULL,'Primidone','primidone 50mg\r\n\r\n#60/sixty\r\n\r\n1 po q12\r\n\r\n/*suzanne oneal*/',55);
INSERT INTO `form_CAMOS_item` VALUES (429,NULL,NULL,NULL,NULL,NULL,NULL,'ketoconazole','Ketoconazole 200mg tablets\r\n\r\n#7/seven.\r\n\r\none tablet once daily.',102);
INSERT INTO `form_CAMOS_item` VALUES (430,NULL,NULL,NULL,NULL,NULL,NULL,'nizoral shampoo','Nizoral Shampoo\r\n\r\napply daily x 2 weeks.',44);
INSERT INTO `form_CAMOS_item` VALUES (433,NULL,NULL,NULL,NULL,NULL,NULL,'foot puncture wound','Subjective:\r\n\r\nstepped on nail.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\npuncture wound noted on plantar surface of foot.  no motor/sensory deficit noted.\r\n\r\nAssessment:\r\n\r\npuncture wound\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (434,NULL,NULL,NULL,NULL,NULL,NULL,'doxazosin','doxazosin 8mg\r\n\r\n#30/thirty tablets.\r\n\r\none tablet by mouth every evening.\r\n\r\n5 additional refills.',108);
INSERT INTO `form_CAMOS_item` VALUES (435,NULL,NULL,NULL,NULL,NULL,NULL,'humulin 70/30','humulog 70/30\r\n\r\none month supply\r\n\r\n15 units subq in the morning\r\n10 units subq in the evening',30);
INSERT INTO `form_CAMOS_item` VALUES (436,NULL,NULL,NULL,NULL,NULL,NULL,'singulair 10mg','Singulair 10mg\r\n\r\n#30/thirty\r\n\r\none tablet at bedtime for asthma.',18);
INSERT INTO `form_CAMOS_item` VALUES (437,NULL,NULL,NULL,NULL,NULL,NULL,'sinemet','Sinemet 25/100mg\r\n\r\n#30/thirty\r\n\r\none tablet once daily.',131);
INSERT INTO `form_CAMOS_item` VALUES (438,NULL,NULL,NULL,NULL,NULL,NULL,'Bactrim DS','Bactrim DS\r\n\r\n#20/twenty\r\n\r\none tablet twice daily for 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (439,NULL,NULL,NULL,NULL,NULL,NULL,'pyridium','pyridium 200mg\r\n\r\n#6/six tablets.\r\n\r\nOne tablet by mouth every eight hours for two days.',108);
INSERT INTO `form_CAMOS_item` VALUES (442,NULL,NULL,NULL,NULL,NULL,NULL,'inderal','inderal 20mg\r\n\r\n#60/sixty tablets.  \r\n\r\n1 tablet by mouth twice daily for high blood pressure.\r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (444,NULL,NULL,NULL,NULL,NULL,NULL,'lantus','Lantus Insulin\r\n\r\n35 units once daily in the morning.',30);
INSERT INTO `form_CAMOS_item` VALUES (445,NULL,NULL,NULL,NULL,NULL,NULL,'levaquin','Levaquin 500mg\r\n\r\n#10/ten\r\n\r\nOne tablet once daily for ten days.',6);
INSERT INTO `form_CAMOS_item` VALUES (446,NULL,NULL,NULL,NULL,NULL,NULL,'levothyroxine','Levothyroxine 0.3mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet daily.',31);
INSERT INTO `form_CAMOS_item` VALUES (447,NULL,NULL,NULL,NULL,NULL,NULL,'combivent','Combivent inhaler\r\n\r\n2 inhalations every 6 hours.\r\n\r\n',18);
INSERT INTO `form_CAMOS_item` VALUES (448,NULL,NULL,NULL,NULL,NULL,NULL,'allegra D','Allegra D\r\n\r\n#30/thirty tablets.\r\n\r\nOne tablet by mouth once daily for allergies.\r\n\r\n5 additional refills.',19);
INSERT INTO `form_CAMOS_item` VALUES (450,NULL,NULL,NULL,NULL,NULL,NULL,'Klor-Con','Klor-Con 8meq\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily.\r\n\r\n2 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (451,NULL,NULL,NULL,NULL,NULL,NULL,'isosorbide mononitrate','Isosorbide Mononitrate Sustained Release 60mg\r\n\r\n#30/thirty\r\n\r\nOne tablet by mouth once daily.\r\n\r\n2 additional refills.',100);
INSERT INTO `form_CAMOS_item` VALUES (452,NULL,NULL,NULL,NULL,NULL,NULL,'ranitidine','Ranitidine 150mg\r\n\r\n#30/thirty\r\n\r\none tablet once daily.\r\n\r\n2 additional refills.',9);
INSERT INTO `form_CAMOS_item` VALUES (453,NULL,NULL,NULL,NULL,NULL,NULL,'tricor','Tricor 145mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth once daily, at bedtime.\r\n\r\n2 additional refills.',29);
INSERT INTO `form_CAMOS_item` VALUES (454,NULL,NULL,NULL,NULL,NULL,NULL,'tazorac','',44);
INSERT INTO `form_CAMOS_item` VALUES (455,NULL,NULL,NULL,NULL,NULL,NULL,'avandaryl','Avandaryl 4/2mg\r\n\r\n#60/sixty\r\n\r\none tablet twice daily for diabetes.\r\n\r\n5 additional refills.',30);
INSERT INTO `form_CAMOS_item` VALUES (456,NULL,NULL,NULL,NULL,NULL,NULL,'sinus','chest x-ray\r\n\r\nPA and Lateral\r\n\r\nDx: bronchitis',2);
INSERT INTO `form_CAMOS_item` VALUES (458,NULL,NULL,NULL,NULL,NULL,NULL,'clobetasol cream','clobetasol cream 0.05%\r\n\r\n15 gram tube\r\n\r\napply qd as directed',44);
INSERT INTO `form_CAMOS_item` VALUES (459,NULL,NULL,NULL,NULL,NULL,NULL,'erythromycin','erythromycin 500mg\r\n\r\n#40/forty capsules.\r\n\r\nOne capsule po q6hrs x 10 days.',6);
INSERT INTO `form_CAMOS_item` VALUES (462,NULL,NULL,NULL,NULL,NULL,NULL,'aderall xr','Aderall XR 30mg\r\n\r\n#60/sixty\r\n\r\nOne capsule twice daily.',55);
INSERT INTO `form_CAMOS_item` VALUES (464,NULL,NULL,NULL,NULL,NULL,NULL,'adderall','Adderall 30mg\r\n\r\n#90/ninety\r\n\r\nOne tablet every eight hours.',55);
INSERT INTO `form_CAMOS_item` VALUES (465,NULL,NULL,NULL,NULL,NULL,NULL,'cardizem cd','Cardizem CD 360mg\r\n\r\n#30/thirty tablets.  \r\n\r\n1 tablet by mouth daily for high blood pressure.  \r\n\r\n2 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (466,NULL,NULL,NULL,NULL,NULL,NULL,'ritalin','ritalin 20mg\r\n\r\n#60/sixty\r\n\r\none tablet by mouth twice daily.',55);
INSERT INTO `form_CAMOS_item` VALUES (467,NULL,NULL,NULL,NULL,NULL,NULL,'acyclovir','acyclovir 400mg\r\n\r\n#25/twenty-five\r\n\r\none capsule by mouth five times daily (one every four hours) for five days.\r\n\r\n2 additional refills.',76);
INSERT INTO `form_CAMOS_item` VALUES (468,NULL,NULL,NULL,NULL,NULL,NULL,'metrogel','METROGEL-VAGINAL 0.75% GEL\r\n\r\nuse as directed once daily at bedtime for five days.',137);
INSERT INTO `form_CAMOS_item` VALUES (469,NULL,NULL,NULL,NULL,NULL,NULL,'protonix','Protonix 40mg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet by mouth daily.  \r\n\r\n5 additional refills.',9);
INSERT INTO `form_CAMOS_item` VALUES (470,NULL,NULL,NULL,NULL,NULL,NULL,'viscous lidocaine','',109);
INSERT INTO `form_CAMOS_item` VALUES (472,NULL,NULL,NULL,NULL,NULL,NULL,'duoneb','DuoNeb\r\n\r\n#90/ninety\r\n\r\ntreatment every four hours as needed for asthma.',18);
INSERT INTO `form_CAMOS_item` VALUES (473,NULL,NULL,NULL,NULL,NULL,NULL,'mask kit','mask kit for nebulizer.\r\n\r\nrefill prn.\r\n\r\nDx: 493.9 Asthma',87);
INSERT INTO `form_CAMOS_item` VALUES (475,NULL,NULL,NULL,NULL,NULL,NULL,'selenium sulfide','selenium sulfide lotion 2.5%\r\n\r\napply once daily for seven days.',44);
INSERT INTO `form_CAMOS_item` VALUES (476,NULL,NULL,NULL,NULL,NULL,NULL,'forearm crutches','',26);
INSERT INTO `form_CAMOS_item` VALUES (478,NULL,NULL,NULL,NULL,NULL,NULL,'condylox','condylox\r\n\r\napply by topical route 2 times per day for 3 consecutive days, then discontinue for 4 consecutive days. This one week cycle of treatment may be repeated until there is no visible wart tissue or for a maximum of four cycles.',44);
INSERT INTO `form_CAMOS_item` VALUES (481,NULL,NULL,NULL,NULL,NULL,NULL,'carotid','Carotid U/S\r\n\r\nDx: carotid stenosis',3);
INSERT INTO `form_CAMOS_item` VALUES (482,NULL,NULL,NULL,NULL,NULL,NULL,'glyburide','glipizide 10mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet by mouth twice daily.\r\n\r\ntwo additional refills',30);
INSERT INTO `form_CAMOS_item` VALUES (483,NULL,NULL,NULL,NULL,NULL,NULL,'ankle sprain','Subjective:\r\n\r\ntwisted ankle\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\nleft shoulder: palpable muscle spasms, decreased rom.\r\n\r\nAssessment:\r\n\r\nmuscle spasm,health checkup\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (489,NULL,NULL,NULL,NULL,NULL,NULL,'kenalog in orabase','kenalog in orabase\r\n\r\napply a small amount to the affected area by topical route 2-3 times per day after meals',44);
INSERT INTO `form_CAMOS_item` VALUES (492,NULL,NULL,NULL,NULL,NULL,NULL,'miacalcin','miacalcin ns\r\n\r\none spray in one nostril once daily.  Alternate nostril for each dose.\r\n\r\nThree additional refills.',64);
INSERT INTO `form_CAMOS_item` VALUES (493,NULL,NULL,NULL,NULL,NULL,NULL,'elocon cream','elocon cream\r\n\r\n45 grams\r\n\r\napply qd 2 weeks',44);
INSERT INTO `form_CAMOS_item` VALUES (494,NULL,NULL,NULL,NULL,NULL,NULL,'accupril','accupril 20mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth daily for high blood pressure.\r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (496,NULL,NULL,NULL,NULL,NULL,NULL,'buspar','',12);
INSERT INTO `form_CAMOS_item` VALUES (497,NULL,NULL,NULL,NULL,NULL,NULL,'lovastatin','lovastatin 20mg\r\n\r\n#90/ninety\r\n\r\none tablet daily',29);
INSERT INTO `form_CAMOS_item` VALUES (499,NULL,NULL,NULL,NULL,NULL,NULL,'insomnia','Subjective:\r\n\r\ninsomnia\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\nAssessment:\r\n\r\ninsomnia\r\n\r\nPlan:\r\n\r\nf/u prn.',73);
INSERT INTO `form_CAMOS_item` VALUES (500,NULL,NULL,NULL,NULL,NULL,NULL,'vertigo','Subjective:\r\n\r\nleft shoulder pain from lifting at work.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\nleft shoulder: palpable muscle spasms, decreased rom.\r\n\r\nAssessment:\r\n\r\nmuscle spasm,health checkup\r\n\r\nPlan:\r\n\r\nsee orders, f/u 1 week.',73);
INSERT INTO `form_CAMOS_item` VALUES (528,NULL,NULL,NULL,NULL,NULL,NULL,'benzaclin','',44);
INSERT INTO `form_CAMOS_item` VALUES (529,NULL,NULL,NULL,NULL,NULL,NULL,'Strattera','Strattera 25mg\r\n\r\n#30/thirty capsules\r\n\r\nOne capsule by mouth once daily.',55);
INSERT INTO `form_CAMOS_item` VALUES (530,NULL,NULL,NULL,NULL,NULL,NULL,'nystatin cream','',44);
INSERT INTO `form_CAMOS_item` VALUES (508,NULL,NULL,NULL,NULL,NULL,NULL,'coreg ','Coreg 6.25mg\r\n\r\n#60/sixty\r\n\r\nOne tablet every 12 hours.\r\n\r\nTwo additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (512,NULL,NULL,NULL,NULL,NULL,NULL,'doxycycline','doxycycline 100mg\r\n\r\n#20/twenty\r\n\r\none capsule by mouth every twelve hours for ten days.',6);
INSERT INTO `form_CAMOS_item` VALUES (513,NULL,NULL,NULL,NULL,NULL,NULL,'xenical','xenical 120mg\r\n\r\n#90/ninety\r\n\r\none daily tid\r\n\r\n1 refill.',28);
INSERT INTO `form_CAMOS_item` VALUES (515,NULL,NULL,NULL,NULL,NULL,NULL,'flomax','flomax 0.4mg\r\n\r\n#7/seven\r\n\r\none tablet daily.',108);
INSERT INTO `form_CAMOS_item` VALUES (522,NULL,NULL,NULL,NULL,NULL,NULL,'Zyban','Zyban 150mg\r\n\r\n#60/sixty\r\n\r\none tablet by mouth twice daily, at least eight hours apart to help quit smoking.\r\n\r\n5 additional refills.',148);
INSERT INTO `form_CAMOS_item` VALUES (524,NULL,NULL,NULL,NULL,NULL,NULL,'sinus','CT sinuses\r\n\r\ndx: sinusitis',5);
INSERT INTO `form_CAMOS_item` VALUES (526,NULL,NULL,NULL,NULL,NULL,NULL,'Effexor','',22);
INSERT INTO `form_CAMOS_item` VALUES (532,NULL,NULL,NULL,NULL,NULL,NULL,'humulog 75/25','humulog 75/25\r\n\r\none month supply\r\n\r\n25 units bid',30);
INSERT INTO `form_CAMOS_item` VALUES (533,NULL,NULL,NULL,NULL,NULL,NULL,'insulin syringes','1/2 cc insulin syringe with needle \r\n\r\n#100/one-hundred\r\n\r\nDx: 250.00 diabetes\r\n\r\nrefill PRN.',83);
INSERT INTO `form_CAMOS_item` VALUES (534,NULL,NULL,NULL,NULL,NULL,NULL,'glimepiride','glimepiride 4mg\r\n\r\n#60/sixty\r\n\r\none tablet bid\r\n\r\nTwo additional refills.',30);
INSERT INTO `form_CAMOS_item` VALUES (537,NULL,NULL,NULL,NULL,NULL,NULL,'cytomel','Synthroid 25mcg\r\nSynthroid 50mcg\r\nSynthroid 75mcg\r\nSynthroid 100mcg\r\n\r\n#30/thirty.  \r\n\r\nOne tablet daily.',31);
INSERT INTO `form_CAMOS_item` VALUES (538,NULL,NULL,NULL,NULL,NULL,NULL,'terazol','',33);
INSERT INTO `form_CAMOS_item` VALUES (539,NULL,NULL,NULL,NULL,NULL,NULL,'librium','librium 5 mg\r\n\r\n#30/thirty\r\n\r\ntake one by mouth at bedtime\r\n\r\nFive additional refills.',12);
INSERT INTO `form_CAMOS_item` VALUES (540,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes','Subjective:\r\n\r\nRoutine diabetic care.\r\n\r\nObjective:\r\n\r\nvital signs stable\r\nheart: regular rate and rhythm\r\nlungs: clear\r\nextremities: good pedal pulses, no skin lesions noted.\r\n\r\nAssessment:\r\n\r\ndiabetes\r\n\r\nPlan:\r\n\r\nEat healthy foods, including fruits and vegetables.  Avoid fatty foods, including beef, pork, butter.  Read food labels to identify sugar and fat content.  Exercise regularly.  A daily, moderately paced, 30 minute walk is recommended.  Check blood sugar regularly.  F/U every 3 months for labs and evaluation.\r\n',73);
INSERT INTO `form_CAMOS_item` VALUES (541,NULL,NULL,NULL,NULL,NULL,NULL,'avandamet','avandamet 2/500mg\r\n\r\n#60/sixty tablets.\r\n\r\none tablet by mouth twice daily.\r\n\r\nTwo additional refills.',30);
INSERT INTO `form_CAMOS_item` VALUES (545,NULL,NULL,NULL,NULL,NULL,NULL,'dicyclomine','dicyclomine 10mg\r\n\r\n#30/thirty\r\n\r\nOne capsule by mouth every six hours as needed for cramps.',9);
INSERT INTO `form_CAMOS_item` VALUES (563,NULL,NULL,NULL,NULL,NULL,NULL,'chantix','Chantix 1mg\r\n\r\nDispense 12 week supply\r\n\r\nUse as directed.',148);
INSERT INTO `form_CAMOS_item` VALUES (566,NULL,NULL,NULL,NULL,NULL,NULL,'humulin N','Humulin N Insulin\r\n\r\n25 units in the morning\r\n\r\n17 units at 4 PM\r\n\r\n',30);
INSERT INTO `form_CAMOS_item` VALUES (586,NULL,NULL,NULL,NULL,NULL,NULL,'mavik','Mavik 4mg\r\n\r\n#30/thirty capsules\r\n\r\nOne by mouth every morning for high blood pressure.\r\n\r\n5 additional refills.',11);
INSERT INTO `form_CAMOS_item` VALUES (572,NULL,NULL,NULL,NULL,NULL,NULL,'Nystop powder','Nystop Powder\r\n\r\n15gm\r\n\r\napply as directed\r\n\r\n5 additional refills.',44);
INSERT INTO `form_CAMOS_item` VALUES (575,NULL,NULL,NULL,NULL,NULL,NULL,'Nitroquick','NitroQuick sublingual tablets 0.4mg\r\n\r\n#100/one-hundred tablets.\r\n\r\nplace tablet under tongue for chest pain as directed.\r\n\r\nno additional refills.',100);
INSERT INTO `form_CAMOS_item` VALUES (578,NULL,NULL,NULL,NULL,NULL,NULL,'prempro','Prempro 0.625/2.5mg\r\n\r\n#30/thirty\r\n\r\none tablet by mouth once daily.',82);
INSERT INTO `form_CAMOS_item` VALUES (581,NULL,NULL,NULL,NULL,NULL,NULL,'spleen','',3);
INSERT INTO `form_CAMOS_item` VALUES (582,NULL,NULL,NULL,NULL,NULL,NULL,'ribs','',2);
INSERT INTO `form_CAMOS_item` VALUES (583,NULL,NULL,NULL,NULL,NULL,NULL,'Yaz','Yaz\r\n\r\nOne month supply\r\n\r\nUse as directed.',152);
INSERT INTO `form_CAMOS_item` VALUES (584,NULL,NULL,NULL,NULL,NULL,NULL,'wellbutrin','Wellbutrin 75mg\r\n\r\n#60/sixty.  \r\n\r\nOne tablet twice daily.\r\n\r\ntwo additional refills.',22);
INSERT INTO `form_CAMOS_item` VALUES (587,NULL,NULL,NULL,NULL,NULL,NULL,'tens','tens\r\n\r\ndx: low back and neck pain.',26);
INSERT INTO `form_CAMOS_item` VALUES (589,NULL,NULL,NULL,NULL,NULL,NULL,'lyrica','lyrica 75mg\r\n\r\n#120/one-hundred-twenty\r\n\r\ntwo tablets by mouth twice daily.\r\n\r\nTwo additional refills.',88);
INSERT INTO `form_CAMOS_item` VALUES (694,NULL,NULL,'drleeds',NULL,NULL,NULL,'januvia','Januvia 100mg\r\n\r\n#30/thirty\r\n\r\nTake one tablet by mouth once a day\r\n\r\nTwo additional refills',30);
INSERT INTO `form_CAMOS_item` VALUES (601,NULL,NULL,NULL,NULL,NULL,NULL,'tegretol','tegretol 200mg\r\n\r\n#60/sixty\r\n\r\none tablet twice daily.',88);
INSERT INTO `form_CAMOS_item` VALUES (607,NULL,NULL,NULL,NULL,NULL,NULL,'finasteride','oxybutynin chloride 5mg\r\n\r\n#180/one hundred eighty\r\n\r\n1/2 tablet every six hours',108);
INSERT INTO `form_CAMOS_item` VALUES (611,NULL,NULL,NULL,NULL,NULL,NULL,'terazol cream','',137);
INSERT INTO `form_CAMOS_item` VALUES (614,NULL,NULL,NULL,NULL,NULL,NULL,'diabetic monitoring','CMP, Lipid Panel, H. A1C.\r\n\r\n272.4\r\n250.03\r\n\r\n\r\n/*lock::*/',16);
INSERT INTO `form_CAMOS_item` VALUES (616,NULL,NULL,NULL,NULL,NULL,NULL,'chronic back pain f/u','Subjective:\\r\\n\\r\\nChronic back pain.\\r\\n\\r\\nObjective:\\r\\n\\r\\nvital  signs: stable.\\r\\nheart: rrr\\r\\nlungs: cta\\r\\nback:  decreased range of motion.\\r\\n\\r\\nAssessment:\\r\\n\\r\\nChronic back pain.\\r\\n\\r\\nPlan:\\r\\n\\r\\nSee orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (618,NULL,NULL,NULL,NULL,NULL,NULL,'shoes','diabetic shoes\r\n\r\ndx: 250.0',83);
INSERT INTO `form_CAMOS_item` VALUES (622,NULL,NULL,NULL,NULL,NULL,NULL,'Guaifenesin DM cough Syrup','Guaifenesin DM Cough Syrup\r\n\r\n4/four ounces\r\n\r\none teaspoon by mouth every six hours as needed for cough',20);
INSERT INTO `form_CAMOS_item` VALUES (625,NULL,NULL,NULL,NULL,NULL,NULL,'hyoscyamine','hyoscyamine 0.25mg\r\n\r\n#120/one hundred twenty\r\n\r\ntwo tablets twice daily\r\n\r\nthree additional refills\r\n',9);
INSERT INTO `form_CAMOS_item` VALUES (626,NULL,NULL,NULL,NULL,NULL,NULL,'gabapentin','gabapentin 300mg\r\n\r\n#30/thirty\r\n\r\none tablet daily\r\n\r\nthree additional refills.',131);
INSERT INTO `form_CAMOS_item` VALUES (629,NULL,NULL,NULL,NULL,NULL,NULL,'catheters','Mentor 14 French Female Urinary Tract Catheter\r\n\r\n#90/ninety\r\n\r\nuse as directed.\r\n\r\n2 additional refills.',158);
INSERT INTO `form_CAMOS_item` VALUES (630,NULL,NULL,NULL,NULL,NULL,NULL,'hypothyroidism','Subjective:\r\n\r\nhypothyroidism\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\n\r\nAssessment:\r\n\r\nhypothyroidism\r\n\r\nPlan:\r\n\r\nf/u 3-4 weeks.',73);
INSERT INTO `form_CAMOS_item` VALUES (633,NULL,NULL,NULL,NULL,NULL,NULL,'prosthesis','',26);
INSERT INTO `form_CAMOS_item` VALUES (635,NULL,NULL,NULL,NULL,NULL,NULL,'thoracic spine','X-Ray Thoracic Spine\r\n\r\nDx: back pain',2);
INSERT INTO `form_CAMOS_item` VALUES (636,NULL,NULL,NULL,NULL,NULL,NULL,'ed','Subjective:\r\n\r\nErectile dysfunction.  Requests medication.  No history of diabetes, prostate cancer, heart disease.\r\n\r\nObjective:\r\n\r\nvital  signs: stable.\r\nheart: rrr\r\nlungs: cta\r\nabd: unremarkable\r\n\r\nAssessment:\r\n\r\nED\r\n\r\nPlan:\r\n\r\nRefer to urologist.  See orders.',73);
INSERT INTO `form_CAMOS_item` VALUES (637,NULL,NULL,NULL,NULL,NULL,NULL,'ipratropium bromide mix','Albuterol 2.5mg/3cc NS\r\n\r\n#30/thirty units\r\n\r\nUse one unit in nebulizer as directed for breathing treatment every six hours as needed for shortness of breath.',18);
INSERT INTO `form_CAMOS_item` VALUES (642,NULL,NULL,NULL,NULL,NULL,NULL,'metamucil','No prescription needed:\r\n\r\nMetamucil powder\r\n\r\nuse as directed',160);
INSERT INTO `form_CAMOS_item` VALUES (643,NULL,NULL,NULL,NULL,NULL,NULL,'ocean nasal spray','No prescription needed:\r\n\r\nOcean Nasal Spray\r\n\r\nuse as directed',160);
INSERT INTO `form_CAMOS_item` VALUES (644,NULL,NULL,NULL,NULL,NULL,NULL,'Source of Life','Source of Life multivitamin\r\n\r\nAvailable at the Vitamin Shoppe',93);
INSERT INTO `form_CAMOS_item` VALUES (645,NULL,NULL,NULL,NULL,NULL,NULL,'lamictal','Lamictal 200mg\r\n\r\n#60/sixty\r\n\r\ntwo by mouth daily\r\n\r\nPRN refills',131);
INSERT INTO `form_CAMOS_item` VALUES (646,NULL,NULL,NULL,NULL,NULL,NULL,'promethazine with codeine','Promethazine with codeine Cough Syrup\r\n\r\n4/four ounces\r\n\r\none teaspoon by mouth every six hours as needed for cough',20);
INSERT INTO `form_CAMOS_item` VALUES (648,NULL,NULL,NULL,NULL,NULL,NULL,'insulin pen needles','1/2 cc insulin syringe with needle \r\n\r\n#100/one-hundred\r\n\r\nDx: 250.00 diabetes\r\n\r\nrefill PRN.',83);
INSERT INTO `form_CAMOS_item` VALUES (655,NULL,NULL,NULL,NULL,NULL,NULL,'clindamycin','clindamycin 1%\r\n\r\n60gm tube\r\n\r\napply qd as directed for 2 weeks.',44);
INSERT INTO `form_CAMOS_item` VALUES (657,NULL,NULL,NULL,NULL,NULL,NULL,'cartia','Cartia 300mg XT Capsules\r\n\r\n#30/thirty\r\n\r\none tablet once daily for blood pressure',11);
INSERT INTO `form_CAMOS_item` VALUES (658,NULL,NULL,NULL,NULL,NULL,NULL,'spiriva','Singulair 10mg\r\n\r\n#30/thirty\r\n\r\none tablet at bedtime for asthma.',18);
INSERT INTO `form_CAMOS_item` VALUES (666,NULL,NULL,NULL,NULL,NULL,NULL,'Benazepril HCT','atenolol 50mg\r\n\r\n#30/thirty tablets.  \r\n\r\nOne tablet by mouth once daily for high blood pressure.\r\n\r\nFive additional refills.',11);
