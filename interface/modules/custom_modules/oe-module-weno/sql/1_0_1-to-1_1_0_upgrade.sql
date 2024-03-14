-- phpMyAdmin SQL Dump
#IfNotColumnType weno_download_log status varchar(255)
ALTER TABLE `weno_download_log` CHANGE `value` `value` VARCHAR(63) NOT NULL, CHANGE `status` `status` VARCHAR(255) NOT NULL;
#EndIf
