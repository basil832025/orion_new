-- Добавление полей для команд в таблицу bs_players
-- ШАГ 2: Создание команд

-- is_team: 1 = команда, 0 или NULL = игрок
ALTER TABLE bs_players ADD COLUMN `is_team` TINYINT(1) DEFAULT 0 COMMENT '1=команда, 0=игрок';

-- logo: путь к логотипу команды
ALTER TABLE bs_players ADD COLUMN `logo` VARCHAR(255) DEFAULT NULL COMMENT 'Путь к логотипу команды';

-- captain_id: ID капитана команды (ссылка на игрока)
ALTER TABLE bs_players ADD COLUMN `captain_id` INT(11) DEFAULT NULL COMMENT 'ID капитана команды (ссылка на bs_players.id, где is_team=0)';

-- city: город команды (используется то же поле, что и в лигах)
-- Поле city уже может существовать, если нет - раскомментируйте следующую строку:
-- ALTER TABLE bs_players ADD COLUMN `city` INT(11) DEFAULT NULL COMMENT 'Город команды (ProstSpr id_spis=4)';

-- club: организатор/клуб (используется то же поле, что и у игроков)
-- Поле club уже существует в таблице для игроков

-- Индекс для быстрого поиска команд
ALTER TABLE bs_players ADD INDEX `idx_is_team` (`is_team`);

-- Индекс для связи капитана
ALTER TABLE bs_players ADD INDEX `idx_captain_id` (`captain_id`);

