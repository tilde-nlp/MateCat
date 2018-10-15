USE `matecat`;

ALTER TABLE `users`
	CHANGE COLUMN `tm_pretranslate` `tm_pretranslate` INT(11) NOT NULL DEFAULT '0' AFTER `confirmation_token_created_at`;

ALTER TABLE `memory_settings`
	CHANGE COLUMN `write_memory` `write_memory` INT(11) NOT NULL DEFAULT '0' AFTER `read_memory`;


