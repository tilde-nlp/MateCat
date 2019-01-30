USE `matecat`;

CREATE TABLE `project_settings` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`project_id` INT NOT NULL,
	`update_mt` INT NOT NULL DEFAULT '1',
	`mt_system` VARCHAR(120) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `project_memory_settings` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`read_memory` INT NOT NULL DEFAULT '1',
	`write_memory` INT NOT NULL DEFAULT '1',
	`memory_id` VARCHAR(255) NOT NULL,
	`project_settings_id` INT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `memory_id_project_settings_id` (`memory_id`, `project_settings_id`),
	CONSTRAINT `fk_project_memory_settings_project_settings_id` FOREIGN KEY (`project_settings_id`) REFERENCES `project_settings` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

