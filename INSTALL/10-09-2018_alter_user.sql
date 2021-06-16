USE `matecat`;

ALTER TABLE `users`
	ADD COLUMN `tm_pretranslate` INT NOT NULL DEFAULT '1' AFTER `confirmation_token_created_at`,
	ADD COLUMN `mt_pretranslate` INT NOT NULL DEFAULT '0' AFTER `tm_pretranslate`;