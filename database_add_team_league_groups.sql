-- Таблица для хранения распределения команд по лигам внутри командной лиги
CREATE TABLE IF NOT EXISTS `bs_league_team_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `league_id` INT(11) NOT NULL,
  `team_id` INT(11) NOT NULL,
  `group_num` INT(11) NOT NULL DEFAULT 0 COMMENT 'Номер лиги (1..N)',
  `turnir_id` INT(11) DEFAULT NULL COMMENT 'Турнир-отбор, по которому распределяли',
  `date_create` DATETIME DEFAULT NULL,
  `date_update` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_league_team` (`league_id`,`team_id`),
  KEY `idx_league_group` (`league_id`,`group_num`),
  KEY `idx_team` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
