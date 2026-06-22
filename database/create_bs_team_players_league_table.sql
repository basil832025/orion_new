CREATE TABLE IF NOT EXISTS `bs_team_players_league` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `league_id` INT(11) NOT NULL,
    `team_id` INT(11) NOT NULL,
    `player_id` INT(11) NOT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_league_player` (`league_id`, `player_id`),
    UNIQUE KEY `uniq_league_team_player` (`league_id`, `team_id`, `player_id`),
    KEY `idx_league_team` (`league_id`, `team_id`),
    KEY `idx_team_id` (`team_id`),
    KEY `idx_player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `bs_team_players_league`
    (`league_id`, `team_id`, `player_id`, `created_at`, `updated_at`)
SELECT DISTINCT
    tp.`league_id`,
    p.`team_id`,
    p.`id`,
    NOW(),
    NOW()
FROM `bs_players` p
INNER JOIN `bs_turnirplayers` tp
    ON tp.`player_id` = p.`team_id`
WHERE p.`is_team` = 0
  AND p.`team_id` IS NOT NULL
  AND p.`team_id` > 0
  AND tp.`league_id` IS NOT NULL
  AND tp.`league_id` > 0;
