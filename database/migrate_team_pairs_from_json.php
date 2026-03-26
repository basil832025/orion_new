<?php
/**
 * Скрипт для миграции данных пар из JSON поля team_pairs (bs_etaps_work) 
 * в отдельную таблицу bs_team_pairs
 * 
 * ВНИМАНИЕ: Запускать только после создания таблицы bs_team_pairs!
 * Сначала выполните: database/create_bs_team_pairs_table.sql
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

echo "Начинаем миграцию данных пар из JSON в таблицу bs_team_pairs...\n\n";

// Получаем все этапы с непустым team_pairs
$query = "SELECT id, team_pairs FROM `bs_etaps_work` WHERE team_pairs IS NOT NULL AND team_pairs != '' AND team_pairs != 'null'";
$result = mysqli_query($dsn, $query);

if (!$result) {
    die('Ошибка запроса: ' . mysqli_error($dsn));
}

$migrated_count = 0;
$error_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $etap_id = $row['id'];
    $team_pairs_json = $row['team_pairs'];
    
    // Декодируем JSON
    $all_match_pairs = json_decode($team_pairs_json, true);
    
    if (empty($all_match_pairs) || !is_array($all_match_pairs)) {
        echo "Этап ID $etap_id: пропущен (невалидный JSON или пусто)\n";
        continue;
    }
    
    echo "Этап ID $etap_id: найдено " . count($all_match_pairs) . " матчей\n";
    
    $now = date('Y-m-d H:i:s');
    
    // Обрабатываем каждый матч
    foreach ($all_match_pairs as $match_id => $pairs) {
        if (empty($pairs) || !is_array($pairs)) {
            continue;
        }
        
        echo "  Матч $match_id: " . count($pairs) . " пар\n";
        
        // Сохраняем каждую пару в таблицу
        foreach ($pairs as $pair) {
            if (empty($pair['pair_number']) || empty($pair['team_a_id']) || empty($pair['team_b_id']) 
                || empty($pair['team_a_player_id']) || empty($pair['team_b_player_id'])) {
                echo "    Пропущена пара: неполные данные\n";
                $error_count++;
                continue;
            }
            
            $match_id_escaped = mysqli_real_escape_string($dsn, $match_id);
            $pair_number = (int)$pair['pair_number'];
            $team_a_id = (int)$pair['team_a_id'];
            $team_b_id = (int)$pair['team_b_id'];
            $team_a_player_id = (int)$pair['team_a_player_id'];
            $team_b_player_id = (int)$pair['team_b_player_id'];
            $etap_id_int = (int)$etap_id;
            
            $insert_sql = "INSERT INTO `bs_team_pairs` 
                (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                VALUES 
                ($etap_id_int, '$match_id_escaped', $pair_number, $team_a_id, $team_b_id, $team_a_player_id, $team_b_player_id, '$now', '$now')
                ON DUPLICATE KEY UPDATE
                team_a_player_id=$team_a_player_id,
                team_b_player_id=$team_b_player_id,
                updated_at='$now'";
            
            if (mysqli_query($dsn, $insert_sql)) {
                $migrated_count++;
            } else {
                echo "    Ошибка при сохранении пары: " . mysqli_error($dsn) . "\n";
                $error_count++;
            }
        }
    }
}

echo "\nМиграция завершена!\n";
echo "Успешно мигрировано пар: $migrated_count\n";
echo "Ошибок: $error_count\n\n";

// Опционально: после успешной миграции можно удалить старое поле
echo "После проверки данных можно удалить поле team_pairs из bs_etaps_work:\n";
echo "ALTER TABLE `bs_etaps_work` DROP COLUMN `team_pairs`;\n";

mysqli_close($dsn);

