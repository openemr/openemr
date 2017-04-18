SET character_set_client = utf8;
DELETE FROM list_options WHERE list_id = 'PHQ_score';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'PHQ_score';
-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `list_options`
--
-- WHERE:  list_id = 'lists' AND option_id = 'PHQ_score' OR list_id = 'PHQ_score'

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('lists','PHQ_score','PHQ score',303,1,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ_score','0','',50,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ_score','1','0-Not at all',10,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ_score','2','1-Several days',20,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ_score','3','2-More than half the days',30,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ_score','4','3-Nearly everyday',40,0,0,'','','',0,0,1,'');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-16 11:12:08
DELETE FROM list_options WHERE list_id = 'PHQ9_Impact';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'PHQ9_Impact';
-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `list_options`
--
-- WHERE:  list_id = 'lists' AND option_id = 'PHQ9_Impact' OR list_id = 'PHQ9_Impact'

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('lists','PHQ9_Impact','PHQ9 Impact',306,1,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Impact','extreme_diff','Extremely difficult',40,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Impact','not_at_all','Not difficult at all',10,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Impact','somewhat','Somewhat difficult',20,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Impact','very_diff','Very difficult',30,0,0,'','','',0,0,1,'');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-16 11:12:08
DELETE FROM list_options WHERE list_id = 'PHQ9_Severity';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'PHQ9_Severity';
-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `list_options`
--
-- WHERE:  list_id = 'lists' AND option_id = 'PHQ9_Severity' OR list_id = 'PHQ9_Severity'

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('lists','PHQ9_Severity','PHQ9 Severity',301,1,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Severity','1','0-4: None',10,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Severity','2','5-9: Mild',20,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Severity','3','10-14: Moderate',30,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Severity','4','15-19: Moderately Severe',40,0,0,'','','',0,0,1,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('PHQ9_Severity','5','20-27: Severe',50,0,0,'','','',0,0,1,'');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-16 11:12:08
DELETE FROM layout_options WHERE form_id = 'LBFPHQ9';
DELETE FROM list_options WHERE list_id = 'lbfnames' AND option_id = 'LBFPHQ9';
-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `list_options`
--
-- WHERE:  list_id = 'lbfnames' AND option_id = 'LBFPHQ9'

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('lbfnames','LBFPHQ9','PHQ9 Questionnaire',30,0,0,'','','',0,0,1,'');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-16 11:12:08
-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: openemr
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `layout_options`
--
-- WHERE:  form_id = 'LBFPHQ9'

INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','appetite','1PHQ_9 Scoring','',60,31,1,0,100,'',1,0,'','','5. Poor appetite or overeating?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','app_score','1PHQ_9 Scoring','5. Appetite score',65,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','arrow','1PHQ_9 Scoring','',114,31,2,0,0,'',1,0,'','','Click Total Score textbox to update Total Score, Severity, and whether Follow-up is needed------------------------------------->',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank','1PHQ_9 Scoring','',15,31,2,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank1','1PHQ_9 Scoring','',16,31,2,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank2','1PHQ_9 Scoring','',102,31,1,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank3','1PHQ_9 Scoring','',120,31,1,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank4','1PHQ_9 Scoring','',130,31,1,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','blank5','1PHQ_9 Scoring','',155,31,0,0,0,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','chkbx','1PHQ_9 Scoring','',115,2,0,1,5,'',0,0,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','concentration','1PHQ_9 Scoring','',85,31,1,0,100,'',1,0,'','','7. Trouble concentrating on things, such as reading the newspaper or watching television?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','conc_score','1PHQ_9 Scoring','7. Concentration score',90,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','depressed','1PHQ_9 Scoring','',30,31,1,0,50,'',1,0,'','','2. Feeling down, depressed, or hopeless?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','dep_score','1PHQ_9 Scoring','2. Depression score',35,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','energy','1PHQ_9 Scoring','',50,31,1,0,100,'',1,0,'','','4. Feeling tired or having little energy?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','energy_score','1PHQ_9 Scoring','4. Low energy score',55,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','esteem_ques','1PHQ_9 Scoring','',70,31,1,0,100,'',1,0,'','','6. Feeling bad about yourself - or that you are a failure or have let yourself or others down?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','esteem_score','1PHQ_9 Scoring','6. Esteem score',80,1,1,0,0,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','impact_ques','1PHQ_9 Scoring','',111,31,1,0,50,'',1,0,'','','10. If you checked off any problems, how difficulty have these problems \r\nmade it for you to do your work, take care of things at home, or get along \r\nwith other people?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','impact_score','1PHQ_9 Scoring','10. Impact on daily life',112,1,1,0,50,'PHQ9_Impact',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','interest','1PHQ_9 Scoring','1. Interest score',20,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','int_quest','1PHQ_9 Scoring','',18,31,1,0,50,'',1,0,'','','1. Little interest or pleasure in doing things?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','lead_question','1PHQ_9 Scoring','',10,31,2,0,100,'',1,0,'','','Over the last two weeks, how often have you been bothered by any of the following problems?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','line','1PHQ_9 Scoring','',113,31,1,0,0,'',1,6,'','','------------------------------------------------------------------------------------------------------',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','line2','1PHQ_9 Scoring','',165,31,0,0,50,'',1,0,'','','Click Text Box to update Total Score & Severity------------->',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','line3','1PHQ_9 Scoring','',117,31,1,0,100,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','line4','1PHQ_9 Scoring','',118,31,1,0,50,'',1,6,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','PM_score','1PHQ_9 Scoring','8. Psychomotor score',100,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','provider_ref','1PHQ_9 Scoring','Follow-up with Provider/IPP partner',160,1,1,0,10,'yesno',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','psychomotor','1PHQ_9 Scoring','',95,31,1,0,255,'',1,0,'','','8. Moving or speaking so slowly that other people could have noticed?\r\n-- Or the opposite --\r\nBeing so fidgety or restless that you have been moving around a lot more than usual?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','severity','1PHQ_9 Scoring','Severity',127,1,1,0,20,'PHQ9_Severity',1,3,'','1','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','severity_exp','1PHQ_9 Scoring','',125,31,1,0,255,'',1,0,'','','Depression Severity: 0-4 none, 5-9 mild, 10-14 moderate, 15-19 moderately severe, 20-27 severe.',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','sev_text','1PHQ_9 Scoring','',150,31,1,0,0,'',1,0,'','','***A score greater than 9 requires follow-up with a provider.***',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','SI','1PHQ_9 Scoring','',105,31,1,0,100,'',1,0,'','','9. Thoughts that you would be better off dead, or of hurting yourself in some way?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','SI_score','1PHQ_9 Scoring','9. SI score',110,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','sleep','1PHQ_9 Scoring','',40,31,1,0,50,'',1,0,'','','3. Trouble falling or staying asleep, or sleeping too much?',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','sleep_score','1PHQ_9 Scoring','3. Sleep score',45,1,1,0,50,'PHQ_score',1,3,'','','',0,'','F','');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`) VALUES ('LBFPHQ9','total_score','1PHQ_9 Scoring','Total Score',116,2,1,5,10,'',1,3,'','1','',0,'','F','');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-16 11:12:08
