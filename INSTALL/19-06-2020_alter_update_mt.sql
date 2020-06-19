USE `matecat`;

ALTER TABLE `users`
	CHANGE COLUMN `update_mt` `update_mt` INT NOT NULL DEFAULT '0' AFTER `mt_pretranslate`;

ALTER TABLE `project_settings`
    CHANGE COLUMN `update_mt` `update_mt` INT NOT NULL DEFAULT '0';