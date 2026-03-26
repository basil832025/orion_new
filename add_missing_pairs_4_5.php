<?php
/**
 * Скрипт для добавления недостающих пар 4 и 5 в bs_team_pairs
 * для матчей, которые были созданы до изменений с поддержкой 5 игр
 */

// Подключаем конфигурацию
chdir(dirname(__FILE__));
if (file_exists('config/init.php')){
    include_once 'config/init.php';
} else {
    die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
}

// Параметры матча из URL
$game_id = 139570; // ID игры из URL
$turnir_id = 1269;
$etap_id = 3482;

echo "<h2>Добавление пар 4 и 5 для игры ID=$game_id</h2>";

// Получаем информацию об игре
$game = db_row('SELECT * FROM `'.T_REITING.'` WHERE id='.$game_id);
if (empty($game)) {
    die("Гра не знайдено!");
}

echo "<p>Гра: ID={$game['id']}, etap_id={$game['etap_id']}, match_id={$game['match_id']}</p>";

// Определяем match_id
$match_id = $game['match_id'];
if (empty($match_id)) {
    // Формируем match_id из etap_id и команд
    $team_a_id = !empty($game['team_a_id']) ? (int)$game['team_a_id'] : 0;
    $team_b_id = !empty($game['team_b_id']) ? (int)$game['team_b_id'] : 0;
    
    if (empty($team_a_id) || empty($team_b_id)) {
        $team_a_id = (int)$game['pl_id_1'];
        $team_b_id = (int)$game['pl_id_2'];
    }
    
    if ($team_a_id > 0 && $team_b_id > 0) {
        $min_team = min($team_a_id, $team_b_id);
        $max_team = max($team_a_id, $team_b_id);
        $match_id = 'match_'.$game['etap_id'].'_'.$min_team.'_'.$max_team;
    }
}

echo "<p>Match ID: $match_id</p>";

if (empty($match_id)) {
    die("Не вдалося визначити match_id!");
}

// Получаем существующие пары
$existing_pairs = db_list('SELECT pair_number, team_a_player_id, team_b_player_id 
    FROM bs_team_pairs 
    WHERE match_id="'.addslashes($match_id).'" 
    AND etap_id='.$game['etap_id'].'
    ORDER BY pair_number');

echo "<h3>Існуючі пари:</h3>";
echo "<ul>";
foreach ($existing_pairs as $pair) {
    $player_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$pair['team_a_player_id'], 'name');
    $player_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$pair['team_b_player_id'], 'name');
    echo "<li>Пара {$pair['pair_number']}: $player_a_name vs $player_b_name</li>";
}
echo "</ul>";

// Получаем составы команд из bs_team_lineups
$team_a_id = (int)$game['pl_id_1'];
$team_b_id = (int)$game['pl_id_2'];

// Проверяем, не поменялись ли команды местами в базе
// Сначала получаем все составы для этого матча
$all_lineups = db_list('SELECT team_id, position, player_id FROM bs_team_lineups 
    WHERE match_id="'.addslashes($match_id).'" 
    AND etap_id='.$game['etap_id'].'
    ORDER BY team_id, position_order');

// Определяем, какая команда имеет позиции A, B, C (это команда A)
// а какая имеет Y, X, Z (это команда B)
$team_with_abc = null;
$team_with_xyz = null;

$team_a_lineup = array();
$team_b_lineup = array();

foreach ($all_lineups as $lineup) {
    $lineup_team_id = (int)$lineup['team_id'];
    $position = trim($lineup['position']);
    $player_id = (int)$lineup['player_id'];
    
    // Если позиция начинается с A, B или C - это команда A
    if (in_array($position, ['A', 'B', 'C'])) {
        if ($team_with_abc === null) {
            $team_with_abc = $lineup_team_id;
        }
        if ($lineup_team_id == $team_with_abc) {
            $team_a_lineup[] = array('position' => $position, 'player_id' => $player_id);
        }
    }
    
    // Если позиция начинается с X, Y или Z - это команда B
    if (in_array($position, ['X', 'Y', 'Z'])) {
        if ($team_with_xyz === null) {
            $team_with_xyz = $lineup_team_id;
        }
        if ($lineup_team_id == $team_with_xyz) {
            $team_b_lineup[] = array('position' => $position, 'player_id' => $player_id);
        }
    }
}

// Если не удалось определить через позиции, используем прямой запрос
if (empty($team_a_lineup) || empty($team_b_lineup)) {
    $team_a_lineup = db_list('SELECT position, player_id FROM bs_team_lineups 
        WHERE match_id="'.addslashes($match_id).'" 
        AND etap_id='.$game['etap_id'].'
        AND team_id='.$team_a_id.'
        ORDER BY position_order');

    $team_b_lineup = db_list('SELECT position, player_id FROM bs_team_lineups 
        WHERE match_id="'.addslashes($match_id).'" 
        AND etap_id='.$game['etap_id'].'
        AND team_id='.$team_b_id.'
        ORDER BY position_order');
}

// Формируем массивы игроков по позициям
// Сначала пытаемся получить из состава команд
$players_a = array();
foreach ($team_a_lineup as $lineup) {
    $pos = trim($lineup['position']);
    // Проверяем, что позиция в правильном формате (A, B, C для команды A)
    if (in_array($pos, ['A', 'B', 'C'])) {
        $players_a[$pos] = (int)$lineup['player_id'];
    }
}

$players_b = array();
foreach ($team_b_lineup as $lineup) {
    $pos = trim($lineup['position']);
    // Проверяем, что позиция в правильном формате (Y, X, Z для команды B)
    if (in_array($pos, ['Y', 'X', 'Z'])) {
        $players_b[$pos] = (int)$lineup['player_id'];
    }
}

// Если не удалось определить игроков через позиции, используем существующие пары
// Пара 1: A-Y => team_a_player_id = A, team_b_player_id = Y
// Пара 2: B-X => team_a_player_id = B, team_b_player_id = X  
// Пара 3: C-Z => team_a_player_id = C, team_b_player_id = Z
if (empty($players_a['A']) || empty($players_b['Y'])) {
    // Определяем из существующих пар
    if (count($existing_pairs) >= 3) {
        // Пара 1: A-Y
        if (!empty($existing_pairs[0])) {
            $players_a['A'] = (int)$existing_pairs[0]['team_a_player_id'];
            $players_b['Y'] = (int)$existing_pairs[0]['team_b_player_id'];
        }
        
        // Пара 2: B-X
        if (!empty($existing_pairs[1])) {
            $players_a['B'] = (int)$existing_pairs[1]['team_a_player_id'];
            $players_b['X'] = (int)$existing_pairs[1]['team_b_player_id'];
        }
        
        // Пара 3: C-Z
        if (!empty($existing_pairs[2])) {
            $players_a['C'] = (int)$existing_pairs[2]['team_a_player_id'];
            $players_b['Z'] = (int)$existing_pairs[2]['team_b_player_id'];
        }
    }
}

// Если игроки не найдены через позиции, определяем их из существующих пар
if (empty($players_a['A']) || empty($players_b['Y'])) {
    // Пара 1: A-Y
    if (!empty($existing_pairs[0])) {
        $players_a['A'] = (int)$existing_pairs[0]['team_a_player_id'];
        $players_b['Y'] = (int)$existing_pairs[0]['team_b_player_id'];
    }
    
    // Пара 2: B-X
    if (!empty($existing_pairs[1])) {
        $players_a['B'] = (int)$existing_pairs[1]['team_a_player_id'];
        $players_b['X'] = (int)$existing_pairs[1]['team_b_player_id'];
    }
    
    // Пара 3: C-Z
    if (!empty($existing_pairs[2])) {
        $players_a['C'] = (int)$existing_pairs[2]['team_a_player_id'];
        $players_b['Z'] = (int)$existing_pairs[2]['team_b_player_id'];
    }
}

echo "<h3>Склад команди A (по позиціям A, B, C):</h3>";
echo "<ul>";
foreach (['A', 'B', 'C'] as $pos) {
    if (!empty($players_a[$pos])) {
        $player_id = $players_a[$pos];
        $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
        echo "<li>$pos: $player_name (ID: $player_id)</li>";
    } else {
        echo "<li>$pos: <span style='color:red;'>Не знайдено</span></li>";
    }
}
echo "</ul>";

echo "<h3>Склад команди B (по позиціям Y, X, Z):</h3>";
echo "<ul>";
foreach (['Y', 'X', 'Z'] as $pos) {
    if (!empty($players_b[$pos])) {
        $player_id = $players_b[$pos];
        $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
        echo "<li>$pos: $player_name (ID: $player_id)</li>";
    } else {
        echo "<li>$pos: <span style='color:red;'>Не знайдено</span></li>";
    }
}
echo "</ul>";

// Проверяем, какие пары нужно добавить
$existing_pair_numbers = array();
foreach ($existing_pairs as $ep) {
    $existing_pair_numbers[] = (int)$ep['pair_number'];
}

echo "<h3>Перевірка пар:</h3>";

// Пара 4: A - X
if (!in_array(4, $existing_pair_numbers)) {
    if (!empty($players_a['A']) && !empty($players_b['X'])) {
        $player_a_id = (int)$players_a['A'];
        $player_b_id = (int)$players_b['X'];
        
        $player_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_a_id, 'name');
        $player_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_b_id, 'name');
        
        $now = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO `bs_team_pairs` 
                (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                VALUES 
                ('.$game['etap_id'].', "'.addslashes($match_id).'", 4, '.$team_a_id.', '.$team_b_id.', '.$player_a_id.', '.$player_b_id.', "'.$now.'", "'.$now.'")';
        
        echo "<p>✅ Додаємо пару 4: A ($player_a_name) - X ($player_b_name)</p>";
        echo "<pre>$sql</pre>";
        
        db_query($sql);
        echo "<p style='color: green;'>✓ Пара 4 успішно додана!</p>";
    } else {
        echo "<p style='color: red;'>❌ Не знайдено гравців для пари 4: A=".($players_a['A'] ?? 'empty').", X=".($players_b['X'] ?? 'empty')."</p>";
    }
} else {
    echo "<p>⚠️ Пара 4 вже існує</p>";
}

// Пара 5: B - Y
if (!in_array(5, $existing_pair_numbers)) {
    if (!empty($players_a['B']) && !empty($players_b['Y'])) {
        $player_a_id = (int)$players_a['B'];
        $player_b_id = (int)$players_b['Y'];
        
        $player_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_a_id, 'name');
        $player_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_b_id, 'name');
        
        $now = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO `bs_team_pairs` 
                (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                VALUES 
                ('.$game['etap_id'].', "'.addslashes($match_id).'", 5, '.$team_a_id.', '.$team_b_id.', '.$player_a_id.', '.$player_b_id.', "'.$now.'", "'.$now.'")';
        
        echo "<p>✅ Додаємо пару 5: B ($player_a_name) - Y ($player_b_name)</p>";
        echo "<pre>$sql</pre>";
        
        db_query($sql);
        echo "<p style='color: green;'>✓ Пара 5 успішно додана!</p>";
    } else {
        echo "<p style='color: red;'>❌ Не знайдено гравців для пари 5: B=".($players_a['B'] ?? 'empty').", Y=".($players_b['Y'] ?? 'empty')."</p>";
    }
} else {
    echo "<p>⚠️ Пара 5 вже існує</p>";
}

// Показываем итоговый список пар
$final_pairs = db_list('SELECT pair_number, team_a_player_id, team_b_player_id 
    FROM bs_team_pairs 
    WHERE match_id="'.addslashes($match_id).'" 
    AND etap_id='.$game['etap_id'].'
    ORDER BY pair_number');

echo "<h3>Фінальний список пар:</h3>";
echo "<ul>";
foreach ($final_pairs as $pair) {
    $player_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$pair['team_a_player_id'], 'name');
    $player_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$pair['team_b_player_id'], 'name');
    $config = '';
    if ($pair['pair_number'] == 1) $config = ' (A-Y)';
    elseif ($pair['pair_number'] == 2) $config = ' (B-X)';
    elseif ($pair['pair_number'] == 3) $config = ' (C-Z)';
    elseif ($pair['pair_number'] == 4) $config = ' (A-X, додаткова)';
    elseif ($pair['pair_number'] == 5) $config = ' (B-Y, вирішальна)';
    echo "<li>Пара {$pair['pair_number']}$config: $player_a_name vs $player_b_name</li>";
}
echo "</ul>";

echo "<p style='color: green; font-weight: bold;'>✅ Готово!</p>";
?>
