USE `matecat`;

ALTER TABLE `jobs`
	ADD COLUMN `start_tm_pretranslate` INT NOT NULL DEFAULT '0' AFTER `mt_pretranslate`,
	ADD COLUMN `start_mt_pretranslate` INT NOT NULL DEFAULT '0' AFTER `start_tm_pretranslate`;
