USE `matecat`;

ALTER TABLE `jobs`
	ADD COLUMN `tm_pretranslate` INT NOT NULL DEFAULT '0' AFTER `editing_time`,
	ADD COLUMN `mt_pretranslate` INT NOT NULL DEFAULT '0' AFTER `tm_pretranslate`;