USE `matecat`;

ALTER TABLE `users`
	ALTER `email` DROP DEFAULT;
ALTER TABLE `users`
	CHANGE COLUMN `email` `email` VARCHAR(512) NOT NULL AFTER `uid`;
