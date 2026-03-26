-- Создание таблицы для хранения составов команд в командных матчах
-- Заменяет JSON поле team_lineups в bs_etaps_work

CREATE TABLE IF NOT EXISTS `bs_team_lineups` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `etap_id` INT(11) NOT NULL COMMENT 'ID этапа (ссылка на bs_etaps_work.id)',
    `match_id` VARCHAR(100) NOT NULL COMMENT 'Уникальный ID матча (etap_id_teamA_teamB)',
    `team_id` INT(11) NOT NULL COMMENT 'ID команды (ссылка на bs_players.id, где is_team=1)',
    `position` VARCHAR(10) NOT NULL COMMENT 'Позиция игрока: A, B, C, D, E для команды A или Y, X, Z, W, V для команды B',
    `player_id` INT(11) NOT NULL COMMENT 'ID игрока на этой позиции (ссылка на bs_players.id)',
    `position_order` INT(11) NOT NULL COMMENT 'Порядковый номер позиции (0=A/Y, 1=B/X, 2=C/Z, и т.д.)',
    `created_at` DATETIME DEFAULT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME DEFAULT NULL COMMENT 'Дата обновления',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_match_team_position` (`match_id`, `team_id`, `position`),
    INDEX `idx_etap_id` (`etap_id`),
    INDEX `idx_match_id` (`match_id`),
    INDEX `idx_team_id` (`team_id`),
    INDEX `idx_player_id` (`player_id`),
    INDEX `idx_position` (`position`),
    INDEX `idx_match_team` (`match_id`, `team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Составы команд в командных матчах';

-- После создания таблицы и миграции данных можно удалить поле team_lineups из bs_etaps_work (опционально)
-- ALTER TABLE `bs_etaps_work` DROP COLUMN `team_lineups`;

