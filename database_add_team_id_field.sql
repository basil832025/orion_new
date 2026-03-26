-- Добавление поля team_id в таблицу bs_players для привязки игроков к командам
-- ШАГ 3: Добавление игроков в команды

-- team_id: ID команды, к которой привязан игрок (NULL для игроков без команды или для самих команд)
ALTER TABLE bs_players ADD COLUMN `team_id` INT(11) DEFAULT NULL COMMENT 'ID команды (ссылка на bs_players.id, где is_team=1)';

-- Индекс для быстрого поиска игроков по команде
ALTER TABLE bs_players ADD INDEX `idx_team_id` (`team_id`);

-- Внешний ключ (опционально, можно закомментировать, если нужна более гибкая структура)
-- ALTER TABLE bs_players ADD CONSTRAINT `fk_team_id` FOREIGN KEY (`team_id`) REFERENCES `bs_players` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;


