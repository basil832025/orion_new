<?php
// Действие для управления составами команд на этапе (командная лига)
// Используем класс ActionModule для совместимости с существующей системой

class TeamLineupsAction extends ActionModule {
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';
    
    function init() {
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login']))) {
            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        
        // Проверяем, сохраняем ли состав или пары
        $save_lineup = poste('save_lineup');
        $save_pairs = poste('save_pairs');
        
        if (!empty($save_lineup)) {
            $this->saveLineup();
            return;
        }
        
        if (!empty($save_pairs)) {
            $this->savePairs();
            return;
        }
        
        // Отображаем интерфейс
        $this->show();
    }
    
    function saveLineup() {
        $etap_id = poste('etap_id');
        $turnir_id = poste('turnir_id');
        $team_id = poste('team_id');
        
        if (empty($etap_id) || empty($team_id)) {
            window_mess('Помилка: не вказано етап або команду');
            $this->show();
            return;
        }
        
        // Получаем текущие составы
        $etap = db_row('SELECT team_lineups, lineups_locked FROM `'.T_ETAPS.'` WHERE id='.$etap_id);
        if (empty($etap)) {
            window_mess('Помилка: етап не знайдено');
            $this->show();
            return;
        }
        
        // Проверяем блокировку
        if (!empty($etap['lineups_locked'])) {
            window_mess('Помилка: склади заблоковані після СТАРТ');
            $this->show();
            return;
        }
        
        $team_lineups = !empty($etap['team_lineups']) ? json_decode($etap['team_lineups'], true) : array();
        
        // Получаем игроков из POST как массив
        $players = array();
        $players_post = poste('players');
        if (is_array($players_post)) {
            $players = array_map('intval', $players_post);
        } elseif (!empty($players_post)) {
            // Если это строка, пытаемся декодировать JSON
            $players_decoded = json_decode($players_post, true);
            if (is_array($players_decoded)) {
                $players = array_map('intval', $players_decoded);
            }
        }
        
        // Сохраняем состав команды
        $team_lineups[$team_id] = $players;
        
        // Обновляем в БД
        global $dsn;
        $team_lineups_json = json_encode($team_lineups, JSON_UNESCAPED_UNICODE);
        $team_lineups_escaped = mysqli_real_escape_string($dsn, $team_lineups_json);
        db_query('UPDATE `'.T_ETAPS.'` SET team_lineups="'.$team_lineups_escaped.'" WHERE id='.$etap_id);
        
        window_mess('Склад команди збережено!');
        $this->show();
    }
    
    function savePairs() {
        $etap_id = poste('etap_id');
        $turnir_id = poste('turnir_id');
        $pairs = poste('pairs');
        
        if (empty($etap_id)) {
            window_mess('Помилка: не вказано етап');
            $this->show();
            return;
        }
        
        // Получаем текущие данные
        $etap = db_row('SELECT team_lineups, team_pairs, lineups_locked FROM `'.T_ETAPS.'` WHERE id='.$etap_id);
        if (empty($etap)) {
            window_mess('Помилка: етап не знайдено');
            $this->show();
            return;
        }
        
        // Проверяем блокировку
        if (!empty($etap['lineups_locked'])) {
            window_mess('Помилка: пари заблоковані після СТАРТ');
            $this->show();
            return;
        }
        
        // Получаем team_lineups для определения команд
        $team_lineups = !empty($etap['team_lineups']) ? json_decode($etap['team_lineups'], true) : array();
        if (empty($team_lineups) || count($team_lineups) < 2) {
            window_mess('Помилка: спочатку потрібно зберегти склади обох команд');
            $this->show();
            return;
        }
        
        $team_ids = array_keys($team_lineups);
        $team_a_id = $team_ids[0];
        $team_b_id = $team_ids[1];
        
        // Формируем массив пар из POST данных
        $team_pairs = array();
        if (!empty($pairs) && is_array($pairs)) {
            foreach ($pairs as $pair_num => $pair_data) {
                if (!empty($pair_data['team_a_player_id']) && !empty($pair_data['team_b_player_id'])) {
                    $team_pairs[] = array(
                        'team_a_id' => $team_a_id,
                        'team_b_id' => $team_b_id,
                        'team_a_player_id' => (int)$pair_data['team_a_player_id'],
                        'team_b_player_id' => (int)$pair_data['team_b_player_id'],
                        'pair_number' => (int)$pair_num
                    );
                }
            }
        }
        
        // Сортируем по номеру пары
        usort($team_pairs, function($a, $b) {
            return $a['pair_number'] - $b['pair_number'];
        });
        
        // Сохраняем в БД
        global $dsn;
        $team_pairs_json = json_encode($team_pairs, JSON_UNESCAPED_UNICODE);
        $team_pairs_escaped = mysqli_real_escape_string($dsn, $team_pairs_json);
        db_query('UPDATE `'.T_ETAPS.'` SET team_pairs="'.$team_pairs_escaped.'" WHERE id='.$etap_id);
        
        window_mess('Пари гравців збережено!');
        $this->show();
    }
    
    function show() {
        $etap_id = poste('etap_id');
        $etap_id = !empty($etap_id) ? $etap_id : poste('id');
        $turnir_id = poste('turnir_id');
        $league_id = poste('league_id');

        if (empty($etap_id) || empty($turnir_id)) {
            window_mess('Помилка: не вказано етап або турнір');
            $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: не вказано етап або турнір</div></div>';
            return;
        }

// Получаем информацию об этапе
$etap = db_row('SELECT * FROM `'.T_ETAPS.'` WHERE id='.$etap_id);
if (empty($etap)) {
    window_mess('Помилка: етап не знайдено');
    $this->list_show();
    return;
}

// Проверяем, что это командная лига
$is_team_league = 0;
if (!empty($league_id)) {
    $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
}

if (!$is_team_league) {
    window_mess('Помилка: це не командна ліга');
    $this->list_show();
    return;
}

// Получаем все команды турнира (из bs_turnir_players, где player_id указывает на команду)
$teams = db_list('SELECT DISTINCT p.id, p.name, p.logo 
    FROM `'.T_TURNIR_PLAYERS.'` tp
    INNER JOIN `'.T_PLAYERS.'` p ON p.id = tp.player_id
    WHERE tp.turnir_id='.$turnir_id.' AND p.is_team=1 AND p.not_use=0
    ORDER BY p.name');

if (empty($teams)) {
    window_mess('Помилка: в турнірі немає команд');
    $this->list_show();
    return;
}

// Получаем сохраненные составы и пары
$team_lineups = !empty($etap['team_lineups']) ? json_decode($etap['team_lineups'], true) : array();
$team_pairs = !empty($etap['team_pairs']) ? json_decode($etap['team_pairs'], true) : array();
$lineups_locked = !empty($etap['lineups_locked']) ? $etap['lineups_locked'] : 0;

// Формируем HTML для интерфейса
$content = '<div class="container-fluid">';
$content .= '<h4>Управління складами команд на етапі "'.$etap['name_etap'].'"</h4>';

// Формируем таблицу с парами команд
$content .= '<div class="row mt-3">';
$content .= '<div class="col-12">';

// Для каждой пары команд создаем раздел
// Пока берем первые 2 команды, но потом нужно будет получать пары из расписания
if (count($teams) >= 2) {
    $team_a = $teams[0];
    $team_b = $teams[1];
    
    $content .= '<div class="card mb-3">';
    $content .= '<div class="card-header bg-primary text-white"><h5>Матч: '.$team_a['name'].' vs '.$team_b['name'].'</h5></div>';
    $content .= '<div class="card-body">';
    
    $team_a_id = $team_a['id'];
    $team_b_id = $team_b['id'];
    
    // Получаем составы для этих команд
    $lineup_a = !empty($team_lineups[$team_a_id]) ? $team_lineups[$team_a_id] : array();
    $lineup_b = !empty($team_lineups[$team_b_id]) ? $team_lineups[$team_b_id] : array();
    
    // Команда A
    $content .= '<div class="row">';
    $content .= '<div class="col-md-6">';
    $content .= '<h6>Команда A: '.$team_a['name'].'</h6>';
    $content .= '<form id="lineup_form_a" method="post" action="#" class="ajax_form">';
    $content .= '<input type="hidden" name="action" value="team_lineups">';
    $content .= '<input type="hidden" name="module" value="etaps">';
    $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
    $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
    $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
    $content .= '<input type="hidden" name="team_id" value="'.$team_a_id.'">';
    $content .= '<input type="hidden" name="team_type" value="team_a">';
    
    // Получаем игроков команды A
    $players_a = db_list('SELECT id, name, phone, city 
        FROM `'.T_PLAYERS.'` 
        WHERE team_id='.$team_a_id.' AND is_team=0 AND not_use=0 AND ispara=0
        ORDER BY name');
    
    $content .= '<div class="mb-3">';
    $content .= '<label class="form-label">Виберіть гравців:</label>';
    $content .= '<select name="players[]" multiple class="form-control" size="10" id="players_a" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
    foreach ($players_a as $player) {
        $selected = in_array($player['id'], $lineup_a) ? 'selected' : '';
        $city_name = '';
        if (!empty($player['city']) && is_numeric($player['city'])) {
            $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
        }
        $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
        $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
    }
    $content .= '</select>';
    $content .= '<small class="form-text text-muted">Утримуйте Ctrl (Cmd на Mac) для вибору кількох гравців</small>';
    $content .= '</div>';
    
    if (empty($lineups_locked)) {
        $content .= '<button type="submit" class="btn btn-primary">Зберегти склад команди A</button>';
        $content .= '<input type="hidden" name="save_lineup" value="1">';
    } else {
        $content .= '<span class="badge bg-warning">Склад заблоковано (після СТАРТ)</span>';
    }
    
    if (!empty($lineup_a)) {
        $content .= '<div class="mt-2"><strong>Поточний склад ('.count($lineup_a).' гравців):</strong><ul>';
        foreach ($lineup_a as $player_id) {
            $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
            if (!empty($player_name)) {
                $content .= '<li>'.$player_name.'</li>';
            }
        }
        $content .= '</ul></div>';
    }
    
    $content .= '</form>';
    $content .= '</div>';
    
    // Команда B
    $content .= '<div class="col-md-6">';
    $content .= '<h6>Команда B: '.$team_b['name'].'</h6>';
    $content .= '<form id="lineup_form_b" method="post" action="#" class="ajax_form">';
    $content .= '<input type="hidden" name="action" value="team_lineups">';
    $content .= '<input type="hidden" name="module" value="etaps">';
    $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
    $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
    $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
    $content .= '<input type="hidden" name="team_id" value="'.$team_b_id.'">';
    $content .= '<input type="hidden" name="team_type" value="team_b">';
    
    // Получаем игроков команды B
    $players_b = db_list('SELECT id, name, phone, city 
        FROM `'.T_PLAYERS.'` 
        WHERE team_id='.$team_b_id.' AND is_team=0 AND not_use=0 AND ispara=0
        ORDER BY name');
    
    $content .= '<div class="mb-3">';
    $content .= '<label class="form-label">Виберіть гравців:</label>';
    $content .= '<select name="players[]" multiple class="form-control" size="10" id="players_b" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
    foreach ($players_b as $player) {
        $selected = in_array($player['id'], $lineup_b) ? 'selected' : '';
        $city_name = '';
        if (!empty($player['city']) && is_numeric($player['city'])) {
            $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
        }
        $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
        $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
    }
    $content .= '</select>';
    $content .= '<small class="form-text text-muted">Утримуйте Ctrl (Cmd на Mac) для вибору кількох гравців</small>';
    $content .= '</div>';
    
    if (empty($lineups_locked)) {
        $content .= '<button type="submit" class="btn btn-primary">Зберегти склад команди B</button>';
        $content .= '<input type="hidden" name="save_lineup" value="1">';
    } else {
        $content .= '<span class="badge bg-warning">Склад заблоковано (після СТАРТ)</span>';
    }
    
    if (!empty($lineup_b)) {
        $content .= '<div class="mt-2"><strong>Поточний склад ('.count($lineup_b).' гравців):</strong><ul>';
        foreach ($lineup_b as $player_id) {
            $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
            if (!empty($player_name)) {
                $content .= '<li>'.$player_name.'</li>';
            }
        }
        $content .= '</ul></div>';
    }
    
    $content .= '</form>';
    $content .= '</div>';
    $content .= '</div>'; // row
    
    // Раздел формирования пар
    if (!empty($lineup_a) && !empty($lineup_b)) {
        $content .= '<hr>';
        $content .= '<h6>Формування пар гравців</h6>';
        
        // Проверяем количество игроков
        if (count($lineup_a) == count($lineup_b)) {
            $content .= '<form id="pairs_form" method="post" action="#" class="ajax_form">';
            $content .= '<input type="hidden" name="action" value="team_lineups">';
            $content .= '<input type="hidden" name="module" value="etaps">';
            $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
            $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
            $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
            $content .= '<input type="hidden" name="save_pairs" value="1">';
            
            // Показываем существующие пары или формируем автоматически
            $pairs_for_match = array();
            foreach ($team_pairs as $pair) {
                if ((!empty($pair['team_a_id']) && $pair['team_a_id'] == $team_a_id && !empty($pair['team_b_id']) && $pair['team_b_id'] == $team_b_id) ||
                    (!empty($pair['team_a_player_id']) && in_array($pair['team_a_player_id'], $lineup_a) && !empty($pair['team_b_player_id']) && in_array($pair['team_b_player_id'], $lineup_b))) {
                    $pairs_for_match[] = $pair;
                }
            }
            
            $content .= '<div class="table-responsive">';
            $content .= '<table class="table table-bordered">';
            $content .= '<thead><tr><th>№ пари</th><th>Гравець команди A</th><th>vs</th><th>Гравець команди B</th></tr></thead>';
            $content .= '<tbody>';
            
            $num_pairs = max(count($lineup_a), count($lineup_b), !empty($pairs_for_match) ? count($pairs_for_match) : 0);
            for ($i = 1; $i <= $num_pairs; $i++) {
                $pair = !empty($pairs_for_match[$i-1]) ? $pairs_for_match[$i-1] : null;
                $selected_a = !empty($pair['team_a_player_id']) ? $pair['team_a_player_id'] : '';
                $selected_b = !empty($pair['team_b_player_id']) ? $pair['team_b_player_id'] : '';
                
                $content .= '<tr>';
                $content .= '<td>'.$i.'</td>';
                $content .= '<td><select name="pairs['.$i.'][team_a_player_id]" class="form-control" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
                $content .= '<option value="">-- Виберіть гравця --</option>';
                foreach ($lineup_a as $player_id) {
                    $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
                    $sel = ($selected_a == $player_id) ? 'selected' : '';
                    $content .= '<option value="'.$player_id.'" '.$sel.'>'.$player_name.'</option>';
                }
                $content .= '</select></td>';
                $content .= '<td class="text-center">vs</td>';
                $content .= '<td><select name="pairs['.$i.'][team_b_player_id]" class="form-control" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
                $content .= '<option value="">-- Виберіть гравця --</option>';
                foreach ($lineup_b as $player_id) {
                    $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
                    $sel = ($selected_b == $player_id) ? 'selected' : '';
                    $content .= '<option value="'.$player_id.'" '.$sel.'>'.$player_name.'</option>';
                }
                $content .= '</select></td>';
                $content .= '</tr>';
            }
            
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '</div>';
            
            if (empty($lineups_locked)) {
                $content .= '<button type="submit" class="btn btn-success">Зберегти пари</button>';
                $content .= '<button type="button" class="btn btn-secondary ms-2" onclick="autoPairs()">Автоматично 1-1, 2-2, 3-3...</button>';
            } else {
                $content .= '<span class="badge bg-warning">Пари заблоковані (після СТАРТ)</span>';
            }
            
            $content .= '</form>';
        } else {
            $content .= '<div class="alert alert-warning">Кількість гравців у командах не співпадає (A: '.count($lineup_a).', B: '.count($lineup_b).')</div>';
        }
    }
    
    $content .= '</div>'; // card-body
    $content .= '</div>'; // card
} else {
    $content .= '<div class="alert alert-warning">В турнірі менше 2 команд. Для командного матчу потрібно щонайменше 2 команди.</div>';
}

$content .= '</div>'; // col-12
$content .= '</div>'; // row

// JavaScript для автоматического формирования пар
$js = '
<script>
function autoPairs() {
    var form = document.getElementById("pairs_form");
    if (!form) return;
    
    var selectsA = form.querySelectorAll("select[name*=\'[team_a_player_id]\']");
    var selectsB = form.querySelectorAll("select[name*=\'[team_b_player_id]\']");
    
    // Автоматически заполняем пары 1-1, 2-2, 3-3...
    for (var i = 0; i < selectsA.length && i < selectsB.length; i++) {
        var optionsA = Array.from(selectsA[i].options).filter(function(opt) { return opt.value !== ""; });
        var optionsB = Array.from(selectsB[i].options).filter(function(opt) { return opt.value !== ""; });
        
        if (optionsA[i] && optionsB[i]) {
            selectsA[i].value = optionsA[i].value;
            selectsB[i].value = optionsB[i].value;
        }
    }
}

// Обработка отправки форм через существующую AJAX систему
jQuery(document).ready(function($) {
    $(".ajax_form").on("submit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);
        
        // Для форм составов добавляем выбранных игроков
        if (form.attr("id") === "lineup_form_a" || form.attr("id") === "lineup_form_b") {
            var selectId = form.attr("id") === "lineup_form_a" ? "players_a" : "players_b";
            var select = document.getElementById(selectId);
            var players = [];
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].selected) {
                    players.push(select.options[i].value);
                }
            }
            // Удаляем старое значение, если есть
            formData.delete("players[]");
            // Добавляем каждый игрок отдельно
            for (var j = 0; j < players.length; j++) {
                formData.append("players[]", players[j]);
            }
        }
        
        // Используем существующую AJAX систему
        $.ajax({
            url: "",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.content) {
                    // Обновляем контент через существующую систему
                    if (typeof content_return === "function") {
                        window.json = response;
                        content_return();
                    } else {
                        location.reload();
                    }
                }
            },
            error: function() {
                location.reload();
            }
        });
    });
});
</script>
';

$this->content = $content . $js;
$this->subMenu = array(
    'back' => array('module' => 'etaps', 'action' => 'list', 'post' => 'turnir_id='.$turnir_id.'&league_id='.$league_id)
);

    function getContent() {
        return $this->content;
    }
    
    function getSubMenu() {
        return $this->subMenu;
    }
    
    function getJavaScript() {
        return $this->Java_script;
    }
}
?>


