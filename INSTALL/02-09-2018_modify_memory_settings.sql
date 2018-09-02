USE `matecat`;

ALTER TABLE `memory_settings`
	ADD COLUMN `concordance_search` INT NOT NULL DEFAULT '0' AFTER `user_id`;
