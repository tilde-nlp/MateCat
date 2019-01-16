USE `matecat`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `context_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_project` int(11) NOT NULL,
  `id_segment` bigint(20) unsigned DEFAULT NULL,
  `id_file` int(10) unsigned DEFAULT NULL,
  `context_json` varchar(16320) NOT NULL,
  PRIMARY KEY (`id`,`id_project`),
  KEY `id_segment_idx` (`id_segment`) USING BTREE,
  KEY `id_file_idx` (`id_file`) USING BTREE,
  KEY `id_project_idx` (`id_project`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

ALTER TABLE `qa_chunk_reviews`
	CHANGE COLUMN `is_pass` `is_pass` TINYINT(4) NULL DEFAULT NULL AFTER `num_errors`;

ALTER TABLE `segment_translation_versions`
	ADD COLUMN `old_status` INT(11) NULL DEFAULT NULL AFTER `raw_diff`,
	ADD COLUMN `new_status` INT(11) NULL DEFAULT NULL AFTER `old_status`;

INSERT INTO `phinxlog` VALUES (20180921144444,'2018-09-21 16:47:50','2018-09-21 16:47:52');
INSERT INTO `phinxlog` VALUES (20180924143503,'2018-09-25 10:47:17','2018-09-25 10:47:20');
INSERT INTO `phinxlog` VALUES (20181026145655,'2018-10-31 12:59:37','2018-10-31 12:59:38');
