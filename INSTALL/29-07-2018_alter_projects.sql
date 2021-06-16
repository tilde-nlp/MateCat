USE `matecat`;

ALTER TABLE `projects`
	ADD COLUMN `mt_system_id` VARCHAR(50) NULL AFTER `due_date`;
