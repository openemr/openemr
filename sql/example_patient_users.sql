# fmg: the example patients' data (example_patient_data.sql) references providers
# not created by defaults.sql so that some functions cause odd matches in the
# program. For example, adding a visit for such a patient shows that visit as
# applicable to several providers (with id's such as "a a", "1 1", etc.).
#
# This inserts (iff don't already exist) the referenced users
INSERT INTO `users` VALUES (4, 'davis', '1a1dc91c907325c69271ddf0c944bc72', 1, NULL, NULL, 'Admin', NULL, 'davis', '', NULL, '');
INSERT INTO `users` VALUES (5, 'hamming', '1a1dc91c907325c69271ddf0c944bc72', 1, NULL, NULL, 'Admin', NULL, 'hamming', '', NULL, '');
