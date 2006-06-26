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


-- MySQL dump 10.9
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	4.1.10a-standard

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `form_CAMOS_category`
--

DROP TABLE IF EXISTS `form_CAMOS_category`;
CREATE TABLE `form_CAMOS_category` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `category` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_CAMOS_category`
--


/*!40000 ALTER TABLE `form_CAMOS_category` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_category` WRITE;
INSERT INTO `form_CAMOS_category` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,'prescriptions'),(2,NULL,NULL,NULL,NULL,NULL,NULL,'radiology'),(3,NULL,NULL,NULL,NULL,NULL,NULL,'lab'),(5,NULL,NULL,NULL,NULL,NULL,NULL,'communications'),(6,NULL,NULL,NULL,NULL,NULL,NULL,'referral'),(7,NULL,NULL,NULL,NULL,NULL,NULL,'DME'),(8,NULL,NULL,NULL,NULL,NULL,NULL,'scheduling');
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_category` ENABLE KEYS */;

--
-- Table structure for table `form_CAMOS_subcategory`
--

DROP TABLE IF EXISTS `form_CAMOS_subcategory`;
CREATE TABLE `form_CAMOS_subcategory` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `subcategory` text,
  `category_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_CAMOS_subcategory`
--


/*!40000 ALTER TABLE `form_CAMOS_subcategory` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_subcategory` WRITE;
INSERT INTO `form_CAMOS_subcategory` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,'analgesics',1),(2,NULL,NULL,NULL,NULL,NULL,NULL,'x-ray',2),(3,NULL,NULL,NULL,NULL,NULL,NULL,'ultrasound',2),(4,NULL,NULL,NULL,NULL,NULL,NULL,'mri',2),(5,NULL,NULL,NULL,NULL,NULL,NULL,'ct',2),(6,NULL,NULL,NULL,NULL,NULL,NULL,'antibiotics',1),(7,NULL,NULL,NULL,NULL,NULL,NULL,'uri',1),(8,NULL,NULL,NULL,NULL,NULL,NULL,'sleep',1),(9,NULL,NULL,NULL,NULL,NULL,NULL,'gi',1),(10,NULL,NULL,NULL,NULL,NULL,NULL,'ed',1),(11,NULL,NULL,NULL,NULL,NULL,NULL,'htn',1),(12,NULL,NULL,NULL,NULL,NULL,NULL,'anxiolytic',1),(13,NULL,NULL,NULL,NULL,NULL,NULL,'muscle relaxers',1),(14,NULL,NULL,NULL,NULL,NULL,NULL,'chemistry',3),(15,NULL,NULL,NULL,NULL,NULL,NULL,'hematology',3),(16,NULL,NULL,NULL,NULL,NULL,NULL,'packages',3),(18,NULL,NULL,NULL,NULL,NULL,NULL,'respiratory',1),(19,NULL,NULL,NULL,NULL,NULL,NULL,'allergy',1),(20,NULL,NULL,NULL,NULL,NULL,NULL,'cough',1),(21,NULL,NULL,NULL,NULL,NULL,NULL,'excuse',5),(22,NULL,NULL,NULL,NULL,NULL,NULL,'antidepressant',1),(23,NULL,NULL,NULL,NULL,NULL,NULL,'mammogram',2),(24,NULL,NULL,NULL,NULL,NULL,NULL,'echocardiogram',2),(25,NULL,NULL,NULL,NULL,NULL,NULL,'general surgeon',6),(26,NULL,NULL,NULL,NULL,NULL,NULL,'orthopedic',7),(27,NULL,NULL,NULL,NULL,NULL,NULL,'followup',8),(28,NULL,NULL,NULL,NULL,NULL,NULL,'weight loss',1),(29,NULL,NULL,NULL,NULL,NULL,NULL,'lipid',1),(30,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes',1),(31,NULL,NULL,NULL,NULL,NULL,NULL,'thyroid',1),(32,NULL,NULL,NULL,NULL,NULL,NULL,'ear drops',1),(33,NULL,NULL,NULL,NULL,NULL,NULL,'yeast',1),(34,NULL,NULL,NULL,NULL,NULL,NULL,'orthopedic surgeon',6),(35,NULL,NULL,NULL,NULL,NULL,NULL,'neurology',6),(36,NULL,NULL,NULL,NULL,NULL,NULL,'cardiology',6),(37,NULL,NULL,NULL,NULL,NULL,NULL,'pulmonology',6),(38,NULL,NULL,NULL,NULL,NULL,NULL,'gastroenterology',6),(39,NULL,NULL,NULL,NULL,NULL,NULL,'podiatry',6),(41,NULL,NULL,NULL,NULL,NULL,NULL,'gout',1),(42,NULL,NULL,NULL,NULL,NULL,NULL,'doppler',2),(43,NULL,NULL,NULL,NULL,NULL,NULL,'otolaryngologist',6),(44,NULL,NULL,NULL,NULL,NULL,NULL,'topical',1),(46,NULL,NULL,NULL,NULL,NULL,NULL,'allergist',6),(47,NULL,NULL,NULL,NULL,NULL,NULL,'dermatologist',6),(48,NULL,NULL,NULL,NULL,NULL,NULL,'corticosteroids',1),(49,NULL,NULL,NULL,NULL,NULL,NULL,'NSAIDS',1),(50,NULL,NULL,NULL,NULL,NULL,NULL,'eye drops',1),(51,NULL,NULL,NULL,NULL,NULL,NULL,'vertigo',1),(52,NULL,NULL,NULL,NULL,NULL,NULL,'ophthalmology',6),(53,NULL,NULL,NULL,NULL,NULL,NULL,'endocrinology',6),(54,NULL,NULL,NULL,NULL,NULL,NULL,'complete',3),(55,NULL,NULL,NULL,NULL,NULL,NULL,'psychiatric',1),(56,NULL,NULL,NULL,NULL,NULL,NULL,'psychiatry',6),(57,NULL,NULL,NULL,NULL,NULL,NULL,'urology',6),(58,NULL,NULL,NULL,NULL,NULL,NULL,'rheumatology',6),(59,NULL,NULL,NULL,NULL,NULL,NULL,'optometry',6),(60,NULL,NULL,NULL,NULL,NULL,NULL,'OB/GYN',6),(61,NULL,NULL,NULL,NULL,NULL,NULL,'chiropractic physician',6),(62,NULL,NULL,NULL,NULL,NULL,NULL,'infectious disease',6),(63,NULL,NULL,NULL,NULL,NULL,NULL,'dexa',2),(64,NULL,NULL,NULL,NULL,NULL,NULL,'osteoporosis',1),(65,NULL,NULL,NULL,NULL,NULL,NULL,'migraine',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_subcategory` ENABLE KEYS */;

--
-- Table structure for table `form_CAMOS_item`
--

DROP TABLE IF EXISTS `form_CAMOS_item`;
CREATE TABLE `form_CAMOS_item` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `pid` bigint(20) default NULL,
  `user` varchar(255) default NULL,
  `groupname` varchar(255) default NULL,
  `authorized` tinyint(4) default NULL,
  `activity` tinyint(4) default NULL,
  `item` text,
  `content` text,
  `subcategory_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_CAMOS_item`
--


/*!40000 ALTER TABLE `form_CAMOS_item` DISABLE KEYS */;
LOCK TABLES `form_CAMOS_item` WRITE;
INSERT INTO `form_CAMOS_item` VALUES (12,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 500mg 3 day','#3/three tablets.  Take one tablet once daily for three days.',6),(3,NULL,NULL,NULL,NULL,NULL,NULL,'chest pa and lat','chest x-ray',2),(4,NULL,NULL,NULL,NULL,NULL,NULL,'zmax','drink liquid as single dose.  review patient instructions provided with product.  testing <>',6),(7,NULL,NULL,NULL,NULL,NULL,NULL,'cephalexin 500mg','cephalexin 500mg, #40/forty, 1 po q6hrs x 10 days.',6),(9,NULL,NULL,NULL,NULL,NULL,NULL,'Duragesic Patch 75mcg','#15/fifteen patches.  apply 1 patch every 2-3 days for pain.',1),(10,NULL,NULL,NULL,NULL,NULL,NULL,'lunesta 2mg','#30/thirty.  one tablet by mouth at bedtime as needed for sleep. 2 additional refills.',8),(13,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 250mg 5 day','#5/five tablets.  Take 2 tablets the first day at the same time.  Take 1 tablet daily  for days 2-5.',6),(14,NULL,NULL,NULL,NULL,NULL,NULL,'ambien 10mg','#30/thirty tablets.  Take one tablet by mouth at bedtime as needed for sleep.',8),(15,NULL,NULL,NULL,NULL,NULL,NULL,'cipro 500mg 10 days','#20/twenty, take one tablet by mouth every twelve hours (twice daily) for 10 days.',6),(16,NULL,NULL,NULL,NULL,NULL,NULL,'uri','This is a test. __billing::test1::test2::test3',7),(17,NULL,NULL,NULL,NULL,NULL,NULL,'tigan 300mg tablets #30','#30/thirty tablets.  One tablet by mouth every 8 hours as needed for nausea.',9),(18,NULL,NULL,NULL,NULL,NULL,NULL,'lomotil #30','#30/thirty tablets.  One tablet by mouth every 8 hours as needed for diarrhea.',9),(20,NULL,NULL,NULL,NULL,NULL,NULL,'Viagra 50mg','#12/twelve tablets.  One tablet by mouth once daily as needed for sexual activity.  5 additional refills.',10),(21,NULL,NULL,NULL,NULL,NULL,NULL,'Viagra 100mg','#12/twelve tablets.  One tablet by mouth once daily as needed for sexual activity.  5 additional refills.',10),(22,NULL,NULL,NULL,NULL,NULL,NULL,'maxzide 25','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.  5 additional refills.',11),(23,NULL,NULL,NULL,NULL,NULL,NULL,'Xanax 1mg','#90/ninety.  1/2 to 1 tablet q8hrs prn anxiety.',12),(24,NULL,NULL,NULL,NULL,NULL,NULL,'soma 350mg','#30/thirty tablets.  take one tablet by mouth at bedtime as needed for muscle spasms.',13),(26,NULL,NULL,NULL,NULL,NULL,NULL,'male 50+','CMP, CBC, Lipid Profile, PSA, U/A',16),(28,NULL,NULL,NULL,NULL,NULL,NULL,'albuterol mdi','2 inhalations every four to six hours as needed for shortness of breath.',18),(29,NULL,NULL,NULL,NULL,NULL,NULL,'flonase nasal spray','2 sprays in each nostril once daily for allergies.  2 additional refills.',19),(30,NULL,NULL,NULL,NULL,NULL,NULL,'tussionex','2/two ounces.  1 tspn q12hrs prn cough.',20),(32,NULL,NULL,NULL,NULL,NULL,NULL,'Percocet 5/325','#12/twelve tablets.  take one tablet by mouth every four to six hours as needed for pain.',1),(33,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin 250/5cc','150cc/one hundred fifty.  5cc po q8hrs x 10 days.',6),(34,NULL,NULL,NULL,NULL,NULL,NULL,'Rondec DM','4oz/four.  One tspn q6hrs prn cough and/or congestion.',20),(35,NULL,NULL,NULL,NULL,NULL,NULL,'metronidazole 250mg','#21/twenty-one tablets.  one tablet by mouth every 8 hours (three times daily) x one week.',6),(36,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin 500mg capsules','#30/thirty capsules.  One capsule by mouth every eight hours for 10 days.',6),(37,NULL,NULL,NULL,NULL,NULL,NULL,'work note','Please excuse this patient from work for three days due to illness.',21),(38,NULL,NULL,NULL,NULL,NULL,NULL,'activity note','Please excuse this patient from activities for three days due to illness.',21),(39,NULL,NULL,NULL,NULL,NULL,NULL,'hctz 12.5mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.  5 additional refills.',11),(40,NULL,NULL,NULL,NULL,NULL,NULL,'fioricet plain','#30/thirty tablets.  take one tablet by mouth every six hours as needed for headache.',1),(41,NULL,NULL,NULL,NULL,NULL,NULL,'Lasix 20mg','#30/thirty tablets.  1 tablet by mouth daily.',11),(42,NULL,NULL,NULL,NULL,NULL,NULL,'zoloft 50mg 1st month','#7/seven of the 25mg tablets.  Take one 25mg tablet every morning for the first week.  #21/twenty-one 50mg tablets.  Take one 50mg tablet every morning until medically directed to discontinue.',22),(43,NULL,NULL,NULL,NULL,NULL,NULL,'xanax 0.25mg','#30/thirty.  1 po q8hrs prn anxiety.',12),(44,NULL,NULL,NULL,NULL,NULL,NULL,'Nexium 40mg','#30/thirty.  One tablet by mouth daily.  5 additional refills.',9),(45,NULL,NULL,NULL,NULL,NULL,NULL,'Prevacid 30mg','#30/thirty.  One tablet by mouth daily.  5 additional refills.',9),(46,NULL,NULL,NULL,NULL,NULL,NULL,'mammogram','routine yearly screening mammogram',23),(47,NULL,NULL,NULL,NULL,NULL,NULL,'MRI','',4),(48,NULL,NULL,NULL,NULL,NULL,NULL,'echocardiogram','',24),(50,NULL,NULL,NULL,NULL,NULL,NULL,'prozac 20mg','#30/thirty tablets.  one tablet by mouth once daily.  Two additional refills.',22),(51,NULL,NULL,NULL,NULL,NULL,NULL,'walker','',26),(52,NULL,NULL,NULL,NULL,NULL,NULL,'Zanaflex 4mg','#90/ninety.  One by mouth every 8 hours as needed for muscle spasm.',13),(53,NULL,NULL,NULL,NULL,NULL,NULL,'one month','Schedule a follow up appointment for about 1 month from today.',27),(54,NULL,NULL,NULL,NULL,NULL,NULL,'one week','Schedule a follow up appointment for about 1 week from today.',27),(55,NULL,NULL,NULL,NULL,NULL,NULL,'azithromycin 1000mg','#2/two 500mg tablets.  Take both tablets, one after the other, by mouth, as a single 1000mg dose.',6),(56,NULL,NULL,NULL,NULL,NULL,NULL,'phentermine 30mg','#30/thirty tablets.  One tablet by mouth once daily, in the morning.',28),(58,NULL,NULL,NULL,NULL,NULL,NULL,'Biaxin XL 500mg','#28/twenty-eight tablets.  Two tablets by mouth once daily for 14 days.',6),(59,NULL,NULL,NULL,NULL,NULL,NULL,'Singulair 10mg','#30/thirty tablets.  One tablet by mouth once daily for allergies.',19),(60,NULL,NULL,NULL,NULL,NULL,NULL,'Lipitor 10mg','#30/thirty.  One tablet by mouth daily.',29),(61,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide 5mg','#60/sixty.  One tablet by mouth twice daily.',30),(62,NULL,NULL,NULL,NULL,NULL,NULL,'avandia 4mg','#60/sixty.  One tablet by mouth twice daily.',30),(63,NULL,NULL,NULL,NULL,NULL,NULL,'Darvocet N-100','#30/thirty.  One tablet by mouth every 6 hours as needed for pain.',1),(64,NULL,NULL,NULL,NULL,NULL,NULL,'synthroid 75mcg','#30/thirty.  One tablet daily.',31),(65,NULL,NULL,NULL,NULL,NULL,NULL,'Augmentin 875mg','#20/twenty.  One tablet by mouth every twelve hours for 10 days.',6),(66,NULL,NULL,NULL,NULL,NULL,NULL,'Cipro HC Otic','Three drops in the affected ear(s) every twelve hours for one week.',32),(67,NULL,NULL,NULL,NULL,NULL,NULL,'Augmentin XR 1000mg','#40/forty tablets.  Two tablets by mouth every twelve hours for 10 days.',6),(68,NULL,NULL,NULL,NULL,NULL,NULL,'Diflucan 150mg','#1/one tablet.  Take one tablet by mouth.',33),(69,NULL,NULL,NULL,NULL,NULL,NULL,'Maxair Autoinhaler','#1/one canister.  Two inhalations every 4-6 hours as needed for shortness of breath due to exacerbations of asthma.',18),(71,NULL,NULL,NULL,NULL,NULL,NULL,'Cortisporin Otic Solution','4 drops in each ear three times daily for one week.',32),(72,NULL,NULL,NULL,NULL,NULL,NULL,'Hyzaar 50/12.5','#30/thirty.  One tablet by mouth once daily for high blood pressure.  5 additional refills.',11),(75,NULL,NULL,NULL,NULL,NULL,NULL,'ibuprofen 800mg','#30/thirty tablets.  take one tablet by mouth every six hours as needed for pain.',1),(77,NULL,NULL,NULL,NULL,NULL,NULL,'amoxicillin 875mg','#20/twenty.  One tablet by mouth every twelve hours for 10 days.',6),(78,NULL,NULL,NULL,NULL,NULL,NULL,'Armour Thyroid 90mg','#90/ninety.  One tablet by mouth once daily.  3 additional refills.',31),(79,NULL,NULL,NULL,NULL,NULL,NULL,'Librax','#15/fifteen.  One tablet by mouth up to three times daily, eight hours apart, as needed for short term relief of mild gastrointestinal discomfort.',9),(82,NULL,NULL,NULL,NULL,NULL,NULL,'Valium 10mg','#120/one-hundred-twenty.  One tablet by mouth up to every 6 hours as needed for muscle spasm.',13),(84,NULL,NULL,NULL,NULL,NULL,NULL,'Zyloprim 100mg','#90/ninety.  One tablet by mouth daily for gout.  One additional refill.',41),(85,NULL,NULL,NULL,NULL,NULL,NULL,'Lotrel 5/10','#90/ninety.  One tablet by mouth daily for high blood pressure.  One additional refill.',11),(86,NULL,NULL,NULL,NULL,NULL,NULL,'Diovan 80mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.  5 additional refills.',11),(87,NULL,NULL,NULL,NULL,NULL,NULL,'venous doppler u/s','Left leg pain after long airplane flight. Evaluate for DVT.',42),(88,NULL,NULL,NULL,NULL,NULL,NULL,'Avelox 400mg','#10/ten tablets.  One tablet daily x 10 days.',6),(90,NULL,NULL,NULL,NULL,NULL,NULL,'levitra 10mg','#3/three.  One tablet by mouth daily.  sample pack.',10),(91,NULL,NULL,NULL,NULL,NULL,NULL,'CT Scan','',5),(92,NULL,NULL,NULL,NULL,NULL,NULL,'neck','Ultrasound of anterior neck.  Dx: palpable, non-tender nodules.',3),(94,NULL,NULL,NULL,NULL,NULL,NULL,'Xanax 2mg','#60/sixty.  One half to one tablet by mouth every 8 hours as needed for anxiety. ',12),(96,NULL,NULL,NULL,NULL,NULL,NULL,'Zovirax Cream 5%','apply 5 times daily for 4 days.',44),(112,NULL,NULL,NULL,NULL,NULL,NULL,'Medrol Dose Pack','Follow package instructions for use.',48),(113,NULL,NULL,NULL,NULL,NULL,NULL,'Indocin 25mg','#30/thirty.  One tablet by mouth every eight hours as needed for pain.  Take with food.',49),(114,NULL,NULL,NULL,NULL,NULL,NULL,'atenolol 25mg','#30/thirty tablets.  1 tablet by mouth once daily for high blood pressure.',11),(116,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide ER 10mg','#30/thirty.  One tablet by mouth once daily.',30),(117,NULL,NULL,NULL,NULL,NULL,NULL,'enalapril 5mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.',11),(118,NULL,NULL,NULL,NULL,NULL,NULL,'low back','',2),(119,NULL,NULL,NULL,NULL,NULL,NULL,'X-Ray','',2),(120,NULL,NULL,NULL,NULL,NULL,NULL,'Tobrex ophthalmic solution','5/five ml.  Two drops in affected eye(s) every hour for the first day until sleep and four times daily (every four hours) for the next six days.',50),(121,NULL,NULL,NULL,NULL,NULL,NULL,'lubricating drops','lubricating/wetting eye drops.  use as directed for dryness of the eyes.  follow package instructions.',50),(122,NULL,NULL,NULL,NULL,NULL,NULL,'antivert 25mg','#30/thirty.  One tablet every eight hours prn dizziness.  five additional refills.',51),(125,NULL,NULL,NULL,NULL,NULL,NULL,'Wellbutrin 150mg XL','#30/thirty.  One tablet once daily.',22),(126,NULL,NULL,NULL,NULL,NULL,NULL,'Floxin Otic Solution','Ten drops instilled into the affected ear twice daily for fourteen days. ',32),(127,NULL,NULL,NULL,NULL,NULL,NULL,'ibuprofen 800mg','#30/thirty.  One tablet by mouth every eight hours as needed for pain.  Take with food.',49),(128,NULL,NULL,NULL,NULL,NULL,NULL,'Lexapro 10mg','#30/thirty tablets.  One tablet once daily for depression.  Two additional refills.',22),(130,NULL,NULL,NULL,NULL,NULL,NULL,'temazepam 30mg','#30/thirty tablets.  Take one capsule by mouth at bedtime as needed for sleep.',8),(131,NULL,NULL,NULL,NULL,NULL,NULL,'complete','CMP, CBC, Lipid Profile.  Dx v70.0',16),(132,NULL,NULL,NULL,NULL,NULL,NULL,'Lotrisone Cream 45 gram tube','apply to affected area twice daily for 14 days.',44),(133,NULL,NULL,NULL,NULL,NULL,NULL,'phentermine 37.5mg','#30/thirty tablets.  One tablet by mouth once daily, in the morning.',28),(134,NULL,NULL,NULL,NULL,NULL,NULL,'Klonipin 0.5mg','#30/thirty.  one tablet by mouth q12hrs prn anxiety.',12),(135,NULL,NULL,NULL,NULL,NULL,NULL,'Depakote 500mg','',55),(138,NULL,NULL,NULL,NULL,NULL,NULL,'toprol 50mg','#30/thirty.  1/2 tablet twice daily.  5 additional refills.',11),(139,NULL,NULL,NULL,NULL,NULL,NULL,'Westcort Cream 30 gram tube','apply twice daily to small affected area as directed for one week.',44),(140,NULL,NULL,NULL,NULL,NULL,NULL,'Vicodin ES','#15/fifteen tablets.  take one tablet by mouth every six hours as needed for pain.',1),(141,NULL,NULL,NULL,NULL,NULL,NULL,'pelvic','pelvic ultrasound.  dx: pelvic pain.',3),(142,NULL,NULL,NULL,NULL,NULL,NULL,'DynaCirc CR 5mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.  5 additional refills.',11),(143,NULL,NULL,NULL,NULL,NULL,NULL,'atenolol 50mg','#30/thirty.  One tablet by mouth once daily for high blood pressure.  2 additional refills.',11),(144,NULL,NULL,NULL,NULL,NULL,NULL,'htn male 50+','CMP, CBC, Lipid Profile, PSA, U/A, urine microalbumen. dx 401.9, 600.',16),(147,NULL,NULL,NULL,NULL,NULL,NULL,'Phenergan with Codeine','8/eight ounces.  1 teaspoon (5 milliliters) by mouth as needed for cough every 4 to 6 hours, not to exceed 6 teaspoons, or 30 milliliters, in 24 hours.',20),(149,NULL,NULL,NULL,NULL,NULL,NULL,'Flexeril 5mg','#30/thirty.  One tablet by mouth every eight hours as needed for muscle spasms.',13),(150,NULL,NULL,NULL,NULL,NULL,NULL,'Ultram 50mg','#30/thirty.  One tablet every six hours by mouth as needed for pain.',1),(151,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide 10mg','#60/sixty.  One tablet by mouth twice daily.',30),(152,NULL,NULL,NULL,NULL,NULL,NULL,'Actos 30mg','#30/thirty.  One tablet by mouth once daily.',30),(153,NULL,NULL,NULL,NULL,NULL,NULL,'Bactroban Cream','30 Gram.  apply bid as directed.',44),(158,NULL,NULL,NULL,NULL,NULL,NULL,'Klonipin 1mg','#60/sixty.  One tablet by mouth twice daily as needed for anxiety.  Two additional refills.',12),(160,NULL,NULL,NULL,NULL,NULL,NULL,'diabetes yearly','CMP, CBC, Lipid profile, TSH, Hemoglobin A1C, U/A, urine microalbumin.',16),(161,NULL,NULL,NULL,NULL,NULL,NULL,'captopril 12.5mg','#60/sixty.  One tablet twice daily for high blood pressure.  Two additional refills.',11),(162,NULL,NULL,NULL,NULL,NULL,NULL,'plendil 5mg','#30/thirty.  One tablet by mouth once daily for high blood pressure.  Two additional refills.',11),(163,NULL,NULL,NULL,NULL,NULL,NULL,'glipizide ER 5mg','#30/thirty.  One tablet by mouth once daily.',30),(165,NULL,NULL,NULL,NULL,NULL,NULL,'Norvasc 10mg','#30/thirty.  One tablet by mouth once daily for high blood pressure.  Two additional refills.',11),(166,NULL,NULL,NULL,NULL,NULL,NULL,'hctz 25mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.  2 additional refills.',11),(170,NULL,NULL,NULL,NULL,NULL,NULL,'cane','cane to assist with ambulatory difficulty due to degenerative joint disease of the ankle, s/p surgery.',26),(175,NULL,NULL,NULL,NULL,NULL,NULL,'prozac 10mg','#30/thirty tablets.  one tablet by mouth once daily.  Two additional refills.',22),(176,NULL,NULL,NULL,NULL,NULL,NULL,'female htn 50+','CMP, CBC, Lipid Profile, Thyroid profile, U/A, urine microalbumin, home hemoccult test x 3. dx 401.9, v70.0.',16),(177,NULL,NULL,NULL,NULL,NULL,NULL,'DEXA','Screen for osteoporosis',63),(183,NULL,NULL,NULL,NULL,NULL,NULL,'enalapril 10mg','#30/thirty tablets.  1 tablet by mouth daily for high blood pressure.',11),(184,NULL,NULL,NULL,NULL,NULL,NULL,'Fosamax 70mg','#4/four tablets.  Take by mouth with a glass of water once weekly.  Follow package directions carefully.',64),(187,NULL,NULL,NULL,NULL,NULL,NULL,'Imitrex 100mg','#9/nine.  One tablet by mouth at the earliest sign of a migraine headache.  No more than one tablet in a 24 hour period.',65),(188,NULL,NULL,NULL,NULL,NULL,NULL,'Tylenol #3','#30/thirty tablets.  take one tablet by mouth every six hours as needed for pain.',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `form_CAMOS_item` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

