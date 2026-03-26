-- Добавление полей для командной лиги в таблицу bs_etaps_work
-- Эти поля хранят составы команд, пары игроков и статус блокировки составов

ALTER TABLE `bs_etaps_work` 
ADD COLUMN `team_lineups` TEXT DEFAULT NULL COMMENT 'JSON с составами команд: {"team_a_id": [player_id_1, player_id_2, ...], "team_b_id": [player_id_4, ...]}',
ADD COLUMN `team_pairs` TEXT DEFAULT NULL COMMENT 'JSON с парами игроков: [{"team_a_player_id": X, "team_b_player_id": Y, "pair_number": 1}, ...]',
ADD COLUMN `lineups_locked` TINYINT(1) DEFAULT 0 COMMENT '1 = составы заблокированы (после СТАРТ), 0 = можно редактировать';

-- Добавление индекса для быстрого поиска по статусу блокировки
ALTER TABLE `bs_etaps_work` ADD INDEX `idx_lineups_locked` (`lineups_locked`);

-- Также нужно добавить поля в bs_reiting для хранения информации о командных матчах
ALTER TABLE `bs_reiting`
ADD COLUMN `match_id` VARCHAR(50) DEFAULT NULL COMMENT 'Уникальный ID матча (etap_id + team_a_id + team_b_id)',
ADD COLUMN `pair_number` INT(11) DEFAULT NULL COMMENT 'Номер пары в матче (1-5)',
ADD COLUMN `team_a_id` INT(11) DEFAULT NULL COMMENT 'ID команды A (ссылка на bs_players.id, где is_team=1)',
ADD COLUMN `team_b_id` INT(11) DEFAULT NULL COMMENT 'ID команды B (ссылка на bs_players.id, где is_team=1)';

-- Индексы для быстрого поиска матчей
ALTER TABLE `bs_reiting` 
ADD INDEX `idx_match_id` (`match_id`),
ADD INDEX `idx_pair_number` (`pair_number`),
ADD INDEX `idx_team_a_id` (`team_a_id`),
ADD INDEX `idx_team_b_id` (`team_b_id`);

