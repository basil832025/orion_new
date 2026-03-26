-- Создание таблицы для хранения пар игроков в командных матчах
-- Заменяет JSON поле team_pairs в bs_etaps_work

CREATE TABLE IF NOT EXISTS `bs_team_pairs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `etap_id` INT(11) NOT NULL COMMENT 'ID этапа (ссылка на bs_etaps_work.id)',
    `match_id` VARCHAR(100) NOT NULL COMMENT 'Уникальный ID матча (etap_id_teamA_teamB)',
    `pair_number` INT(11) NOT NULL COMMENT 'Номер пары в матче (1-5)',
    `team_a_id` INT(11) NOT NULL COMMENT 'ID команды A (ссылка на bs_players.id, где is_team=1)',
    `team_b_id` INT(11) NOT NULL COMMENT 'ID команды B (ссылка на bs_players.id, где is_team=1)',
    `team_a_player_id` INT(11) NOT NULL COMMENT 'ID игрока команды A (ссылка на bs_players.id)',
    `team_b_player_id` INT(11) NOT NULL COMMENT 'ID игрока команды B (ссылка на bs_players.id)',
    `created_at` DATETIME DEFAULT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME DEFAULT NULL COMMENT 'Дата обновления',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_match_pair` (`match_id`, `pair_number`),
    INDEX `idx_etap_id` (`etap_id`),
    INDEX `idx_match_id` (`match_id`),
    INDEX `idx_pair_number` (`pair_number`),
    INDEX `idx_team_a_id` (`team_a_id`),
    INDEX `idx_team_b_id` (`team_b_id`),
    INDEX `idx_team_a_player_id` (`team_a_player_id`),
    INDEX `idx_team_b_player_id` (`team_b_player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Пары игроков в командных матчах';

-- После создания таблицы можно удалить поле team_pairs из bs_etaps_work (опционально)
-- ALTER TABLE `bs_etaps_work` DROP COLUMN `team_pairs`;

