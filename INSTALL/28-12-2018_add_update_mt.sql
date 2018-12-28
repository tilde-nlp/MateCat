USE `matecat`;

ALTER TABLE `users`
	ADD COLUMN `update_mt` INT NOT NULL DEFAULT '1' AFTER `mt_pretranslate`;
