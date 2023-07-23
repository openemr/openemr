INSERT INTO `users` (username,password,lname,authorized,active) VALUES ('phimail-service','NoLogin','phiMail Gateway',0,0);
INSERT INTO `users` (username,password,lname,authorized,active) VALUES ('portal-user','NoLogin','Patient Portal User',0,0);
-- install system user and add the ACL for this user.
INSERT INTO `users` (`username`,`password`,`lname`,`authorized`,`active`) VALUES ('oe-system','NoLogin','System Operation User',0,0);
INSERT INTO `gacl_aro`(`id`, `section_value`, `value`, `order_value`, `name`, `hidden`)
    SELECT max(`id`)+1,'users','oe-system',10,'System Operation User', 0 FROM `gacl_aro`;
INSERT INTO `gacl_groups_aro_map`(`group_id`, `aro_id`)
    VALUES (
        (SELECT `id` FROM `gacl_aro_groups` WHERE parent_id=10 AND value='admin')
        ,(SELECT `id` FROM `gacl_aro` WHERE `section_value` = 'users' AND `value` = 'oe-system')
    );
UPDATE `gacl_aro_seq` SET `id` = (SELECT max(`id`)+1);