<?php
/**
 * Скрипт для миграции данных составов команд из JSON поля team_lineups (bs_etaps_work) 
 * в отдельную таблицу bs_team_lineups
 * 
 * ВНИМАНИЕ: Запускать только после создания таблицы bs_team_lineups!
 * Сначала выполните: database/create_bs_team_lineups_table.sql
 */

// Подключаем конфигурацию БД (нужно настроить под вашу систему)
require_once __DIR__ . '/../config/db_config.php'; // или ваш путь к конфигурации

// Или установите параметры подключения напрямую:
// $host = 'localhost';
// $db = 'your_database';
// $user = 'your_user';
// $pass = 'your_password';

// Подключение к БД
$dsn = mysqli_connect($host, $user, $pass, $db);
if (!$dsn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
mysqli_set_charset($dsn, 'utf8mb4');

echo "Начинаем миграцию данных составов команд из JSON в таблицу bs_team_lineups...\n\n";

// Определяем метки позиций
$team_a_labels = array('A', 'B', 'C', 'D', 'E');
$team_b_labels = array('Y', 'X', 'Z', 'W', 'V');

// Получаем все этапы с непустым team_lineups
$query = "SELECT id, team_lineups FROM `bs_etaps_work` WHERE team_lineups IS NOT NULL AND team_lineups != '' AND team_lineups != 'null'";
$result = mysqli_query($dsn, $query);

if (!$result) {
    die('Ошибка запроса: ' . mysqli_error($dsn));
}

$migrated_count = 0;
$error_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $etap_id = $row['id'];
    $team_lineups_json = $row['team_lineups'];
    
    // Декодируем JSON
    $all_match_lineups = json_decode($team_lineups_json, true);
    
    if (empty($all_match_lineups) || !is_array($all_match_lineups)) {
        echo "Этап ID $etap_id: пропущен (невалидный JSON или пусто)\n";
        continue;
    }
    
    echo "Этап ID $etap_id: найдено " . count($all_match_lineups) . " матчей\n";
    
    $now = date('Y-m-d H:i:s');
    
    // Обрабатываем каждый матч
    foreach ($all_match_lineups as $match_id => $match_lineups) {
        if (empty($match_lineups) || !is_array($match_lineups)) {
            continue;
        }
        
        $match_id_escaped = mysqli_real_escape_string($dsn, $match_id);
        $team_ids = array_keys($match_lineups);
        
        echo "  Матч $match_id: " . count($team_ids) . " команд\n";
        
        // Определяем команду A и B (первая команда - A, вторая - B)
        $team_a_id = null;
        $team_b_id = null;
        
        if (count($team_ids) >= 1) {
            $team_a_id = (int)$team_ids[0];
        }
        if (count($team_ids) >= 2) {
            $team_b_id = (int)$team_ids[1];
        } else {
            // Если только одна команда, используем минимальный/максимальный ID
            $team_b_id = $team_a_id;
        }
        
        // Обрабатываем каждую команду
        foreach ($match_lineups as $team_id => $players) {
            if (empty($players) || !is_array($players)) {
                continue;
            }
            
            $team_id_int = (int)$team_id;
            $is_team_a = ($team_id == $team_a_id);
            $labels = $is_team_a ? $team_a_labels : $team_b_labels;
            
            echo "    Команда ID $team_id: " . count($players) . " игроков\n";
            
            // Сохраняем каждого игрока
            foreach ($players as $index => $player_id) {
                if (empty($player_id) || $player_id <= 0) {
                    continue;
                }
                
                $position = !empty($labels[$index]) ? $labels[$index] : chr(65 + $index);
                $position_order = $index;
                $player_id_int = (int)$player_id;
                $etap_id_int = (int)$etap_id;
                
                $insert_sql = "INSERT INTO `bs_team_lineups` 
                    (etap_id, match_id, team_id, position, player_id, position_order, created_at, updated_at)
                    VALUES 
                    ($etap_id_int, '$match_id_escaped', $team_id_int, '" . addslashes($position) . "', $player_id_int, $position_order, '$now', '$now')
                    ON DUPLICATE KEY UPDATE
                    player_id=$player_id_int,
                    updated_at='$now'";
                
                if (mysqli_query($dsn, $insert_sql)) {
                    $migrated_count++;
                } else {
                    echo "      Ошибка при сохранении игрока на позиции $position: " . mysqli_error($dsn) . "\n";
                    $error_count++;
                }
            }
        }
    }
}

echo "\nМиграция завершена!\n";
echo "Успешно мигрировано записей составов: $migrated_count\n";
echo "Ошибок: $error_count\n\n";

// Опционально: после успешной миграции можно удалить старое поле
echo "После проверки данных можно удалить поле team_lineups из bs_etaps_work:\n";
echo "ALTER TABLE `bs_etaps_work` DROP COLUMN `team_lineups`;\n";

mysqli_close($dsn);

