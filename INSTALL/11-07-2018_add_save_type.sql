USE `matecat`;

ALTER TABLE `segment_translations`
	ADD COLUMN `save_type` VARCHAR(50) NULL DEFAULT NULL AFTER `edit_distance`,
	ADD COLUMN `save_match` INT NULL DEFAULT NULL AFTER `save_type`;