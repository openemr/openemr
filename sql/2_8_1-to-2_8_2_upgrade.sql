## alters to categorize forms / per Mark 

ALTER TABLE registry ADD COLUMN priority INT DEFAULT 0 AFTER date;
ALTER TABLE registry ADD COLUMN category VARCHAR(255) DEFAULT "category" AFTER priority;
ALTER TABLE registry ADD COLUMN nickname VARCHAR(255) DEFAULT '' AFTER category;
