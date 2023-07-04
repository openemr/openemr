
#IfNotTable module_payroll_data
CREATE TABLE IF NOT EXISTS `module_payroll_data` (
    `id` int NOT NULL,
    `userid` int DEFAULT NULL,
    `percentage` decimal(4,2) DEFAULT NULL,
    `flat` decimal(4,2) DEFAULT NULL
);
ALTER TABLE `module_payroll_data` ADD PRIMARY KEY (`id`);
ALTER TABLE `module_payroll_data` MODIFY `id` int NOT NULL AUTO_INCREMENT;
#EndIf
