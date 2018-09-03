USE 'matecat';

ALTER TABLE `jobs`
	ADD COLUMN ``editing_time` BIGINT NOT NULL DEFAULT '0' AFTER `total_raw_wc`;