ALTER TABLE `char` ADD CONSTRAINT `name_key` UNIQUE (`name`);
ALTER TABLE `inventory`  ADD COLUMN `favorite` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `expire_time`;