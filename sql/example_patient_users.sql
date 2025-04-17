# fmg: the example patients' data (example_patient_data.sql) references providers
# not created by defaults.sql so that some functions cause odd matches in the
# program. For example, adding a visit for such a patient shows that visit as
# applicable to several providers (with id's such as "a a", "1 1", etc.).
#
# This inserts (iff don't already exist) the referenced users

INSERT INTO `users` ( `username`, `password`, `authorized`, `info`, `source`, `fname`, `mname`, `lname`, `federaltaxid`, `federaldrugid`, `upin`) VALUES ( 'davis', '9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684', 1, NULL, NULL, 'Admin', NULL, 'davis', '', NULL, '');
INSERT INTO `users` ( `username`, `password`, `authorized`, `info`, `source`, `fname`, `mname`, `lname`, `federaltaxid`, `federaldrugid`, `upin`) VALUES ( 'hamming', '9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684', 1, NULL, NULL, 'Admin', NULL, 'hamming', '', NULL, '');
