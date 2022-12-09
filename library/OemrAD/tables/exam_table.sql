
#IfMissingColumn form_ext_exam2 tmp_ge_gen
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_gen` tinyint(1) default NULL AFTER `printed`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_head
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_head` varchar(50) default NULL AFTER `tmp_ge_gen`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_eyes
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_eyes` varchar(50) default NULL AFTER `tmp_ge_head`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_ears
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_ears` varchar(50) default NULL AFTER `tmp_ge_eyes`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_nose
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_nose` varchar(50) default NULL AFTER `tmp_ge_ears`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_mouth
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_mouth` varchar(50) default NULL AFTER `tmp_ge_nose`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_throat
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_throat` varchar(50) default NULL AFTER `tmp_ge_mouth`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_neck
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_neck` varchar(50) default NULL AFTER `tmp_ge_throat`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_thyroid
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_thyroid` varchar(50) default NULL AFTER `tmp_ge_neck`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_lymph
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_lymph` varchar(50) default NULL AFTER `tmp_ge_thyroid`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_breast
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_breast` varchar(50) default NULL AFTER `tmp_ge_lymph`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_cardio
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_cardio` varchar(50) default NULL AFTER `tmp_ge_breast`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_pulmo
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_pulmo` varchar(50) default NULL AFTER `tmp_ge_cardio`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_gastro
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_gastro` varchar(50) default NULL AFTER `tmp_ge_pulmo`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_neuro
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_neuro` varchar(50) default NULL AFTER `tmp_ge_gastro`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_musc
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_musc` varchar(50) default NULL AFTER `tmp_ge_neuro`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_ext
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_ext` varchar(50) default NULL AFTER `tmp_ge_musc`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_dia
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_dia` varchar(50) default NULL AFTER `tmp_ge_ext`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_test
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_test` varchar(50) default NULL AFTER `tmp_ge_dia`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_rectal
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_rectal` varchar(50) default NULL AFTER `tmp_ge_test`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_skin
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_skin` varchar(50) default NULL AFTER `tmp_ge_rectal`;
#EndIf

#IfMissingColumn form_ext_exam2 tmp_ge_psych
ALTER TABLE `form_ext_exam2` ADD COLUMN `tmp_ge_psych` varchar(50) default NULL AFTER `tmp_ge_skin`;
#EndIf