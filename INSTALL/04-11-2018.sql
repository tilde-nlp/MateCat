USE `matecat`;

UPDATE jobs
SET tm_pretranslate = 0,
mt_pretranslate = 0;

ALTER TABLE `jobs`
	ADD COLUMN `start_tm_pretranslate` INT(11) NOT NULL DEFAULT '0' AFTER `mt_pretranslate`,
	ADD COLUMN `start_mt_pretranslate` INT(11) NOT NULL DEFAULT '0' AFTER `start_tm_pretranslate`;
