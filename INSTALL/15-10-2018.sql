USE `matecat`;

ALTER TABLE `users`
	CHANGE COLUMN `tm_pretranslate` `tm_pretranslate` INT(11) NOT NULL DEFAULT '0' AFTER `confirmation_token_created_at`;

