-- Добавление поля is_team_league в таблицу bs_leagues
-- Выполнить этот SQL запрос в базе данных

ALTER TABLE `bs_leagues` 
ADD COLUMN `is_team_league` TINYINT(1) DEFAULT 0 COMMENT '1=командная ліга, 0=обычная ліга' 
AFTER `status`;

-- Установить значение по умолчанию для существующих записей (опционально)
-- UPDATE `bs_leagues` SET `is_team_league` = 0 WHERE `is_team_league` IS NULL;


