
#IfNotTable module_bamboo_credentials
CREATE TABLE IF NOT EXISTS `module_bamboo_credentials` (
    `id` int NOT NULL,
    `username` varchar(255) DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `date` datetime(6) NOT NULL
);
ALTER TABLE `module_bamboo_credentials` ADD PRIMARY KEY(`id`);
ALTER TABLE `module_bamboo_credentials` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;
#EndIf
