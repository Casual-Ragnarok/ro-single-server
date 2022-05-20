/*
--------------------------------------------
-         以下适用于全部v8.9.0用户         -
--------------------------------------------
*/

-- 更新于 2015.01.03 --
ALTER TABLE `picklog` MODIFY `type` ENUM('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$') NOT NULL DEFAULT 'S';

-- 更新于 2015.01.31 --
ALTER TABLE `bonus_script` MODIFY COLUMN `char_id` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `bonus_script` MODIFY COLUMN `script` TEXT NOT NULL;
ALTER TABLE `bonus_script` MODIFY COLUMN `tick` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bonus_script` MODIFY COLUMN `flag` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bonus_script` MODIFY COLUMN `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bonus_script` MODIFY COLUMN `icon` SMALLINT(3) NOT NULL DEFAULT '-1';

-- 更新于 2015.02.11 --
UPDATE `skill` SET `lv` = `flag` - 3 WHERE `flag` >= 3;
UPDATE `skill` SET `flag` = 0 WHERE `flag` >= 3;
UPDATE `skill` SET `flag` = 3 WHERE `id` = 681 OR `id` = 2535;
