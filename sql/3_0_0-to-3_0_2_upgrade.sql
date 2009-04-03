#IfMissingColumn pnotes deleted
ALTER TABLE `pnotes` 
    ADD `deleted` TINYINT DEFAULT '0' COMMENT 'flag indicates note is deleted'
#EndIf
