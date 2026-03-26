-- Поля для командных лиг в таблице bs_turnirs
ALTER TABLE `bs_turnirs`
  ADD COLUMN `is_team_qual` TINYINT(1) DEFAULT 0 COMMENT '1=відбірковий тур для командних ліг' AFTER `is_command`,
  ADD COLUMN `team_leagues_count` INT(11) DEFAULT 0 COMMENT 'Кількість ліг після відбору' AFTER `is_team_qual`;
