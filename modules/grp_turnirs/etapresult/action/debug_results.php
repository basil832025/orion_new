<?php
/**
 * Диагностический скрипт для проверки данных в all_results_table_comm
 */

class debug_resultsAction extends ActionModule {
    protected $content = '';
    
    function init() {
        $etap_id = poste('etap_id');
        $turnir_id = poste('turnir_id');
        
        if (empty($etap_id) || empty($turnir_id)) {
            $this->content = '<div style="padding:20px;"><h3>Ошибка</h3><p>Не указаны etap_id или turnir_id</p></div>';
            return;
        }
        
        $etap_id = (int)$etap_id;
        $turnir_id = (int)$turnir_id;
        
        $output = '<div style="padding:20px;font-family:monospace;font-size:12px;">';
        $output .= '<h2>Диагностика результатов для командного турнира</h2>';
        $output .= '<p>etap_id='.$etap_id.', turnir_id='.$turnir_id.'</p>';
        $output .= '<hr>';
        
        // Получаем данные из базы
        $sql = 'SELECT  (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE  r.pl_id_1=m.player_id AND m.etap_id=r.etap_id) AS mesto1, 
 (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE r.pl_id_2=m.player_id AND m.etap_id=r.etap_id) AS mesto2,r.* 
FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
        
        $aResults = db_list($sql);
        
        $output .= '<h3>Данные из базы (ДО нормализации):</h3>';
        $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
        $output .= '<tr><th>ID</th><th>pl_id_1</th><th>pl_id_2</th><th>pl_num_grp1</th><th>pl_num_grp2</th><th>set_1</th><th>set_2</th><th>pair_number</th><th>match_id</th></tr>';
        
        foreach ($aResults as $aRec) {
            $output .= '<tr>';
            $output .= '<td>'.$aRec['id'].'</td>';
            $output .= '<td>'.$aRec['pl_id_1'].'</td>';
            $output .= '<td>'.$aRec['pl_id_2'].'</td>';
            $output .= '<td>'.$aRec['pl_num_grp1'].'</td>';
            $output .= '<td>'.$aRec['pl_num_grp2'].'</td>';
            $output .= '<td>'.$aRec['set_1'].'</td>';
            $output .= '<td>'.$aRec['set_2'].'</td>';
            $output .= '<td>'.($aRec['pair_number'] ?? 'NULL').'</td>';
            $output .= '<td>'.($aRec['match_id'] ?? 'NULL').'</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        
        // Нормализация
        $aTemp=[];
        foreach ($aResults as $aRec){
            if ($aRec['pl_num_grp1']<$aRec['pl_num_grp2']){
                $aTemp[]=$aRec;
            }else{
                $aTempRec=$aRec;
                $aRec['pl_num_grp2']=$aTempRec['pl_num_grp1'];
                $aRec['pl_num_grp1']=$aTempRec['pl_num_grp2'];
                $aRec['pl_id_1']=$aTempRec['pl_id_2'];
                $aRec['pl_id_2']=$aTempRec['pl_id_1'];
                $aRec['set_1']=$aTempRec['set_2'];
                $aRec['set_2']=$aTempRec['set_1'];
                $aRec['win_player']=$aTempRec['lose_player'];
                $aRec['lose_player']=$aTempRec['win_player'];
                $aRec['break_1']=$aTempRec['break_2'];
                $aRec['break_2']=$aTempRec['break_1'];
                $aTemp[]=$aRec;
            }
        }
        $aResults=$aTemp;
        
        $output .= '<h3>Данные ПОСЛЕ нормализации:</h3>';
        $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
        $output .= '<tr><th>ID</th><th>pl_id_1</th><th>pl_id_2</th><th>pl_num_grp1</th><th>pl_num_grp2</th><th>set_1</th><th>set_2</th><th>mesto1</th><th>mesto2</th></tr>';
        
        foreach ($aResults as $aRec) {
            $output .= '<tr>';
            $output .= '<td>'.$aRec['id'].'</td>';
            $output .= '<td>'.$aRec['pl_id_1'].'</td>';
            $output .= '<td>'.$aRec['pl_id_2'].'</td>';
            $output .= '<td>'.$aRec['pl_num_grp1'].'</td>';
            $output .= '<td>'.$aRec['pl_num_grp2'].'</td>';
            $output .= '<td>'.$aRec['set_1'].'</td>';
            $output .= '<td>'.$aRec['set_2'].'</td>';
            $output .= '<td>'.($aRec['mesto1'] ?? 'NULL').'</td>';
            $output .= '<td>'.($aRec['mesto2'] ?? 'NULL').'</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        
        // Обработка результатов
        $output .= '<h3>Обработка результатов (первые 5 игр):</h3>';
        $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
        $output .= '<tr><th>ID</th><th>pl_num_grp1</th><th>pl_num_grp2</th><th>set_1</th><th>set_2</th><th>$ochko</th><th>$ochko2</th><th>$team_ochko</th><th>HTML itog</th></tr>';
        
        $count = 0;
        foreach ($aResults as $aRec) {
            if ($count >= 5) break;
            $count++;
            
            if (!empty($aRec['mesto1'])) {
                $pl_num_grp1 = $aRec['mesto1'];
                $pl_num_grp2 = $aRec['mesto2'];
            } else {
                $pl_num_grp1 = $aRec['pl_num_grp1'];
                $pl_num_grp2 = $aRec['pl_num_grp2'];
            }
            
            $set_1 = $aRec['set_1']=='W' ? 3 : $aRec['set_1'];
            $set_2 = $aRec['set_2']=='W' ? 3 : $aRec['set_2'];
            
            $ochko = 0;
            $ochko2 = 0;
            $colorClass = '';
            $team_ochko = '';
            
            if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){
                if ($set_1>0 && $set_1>$set_2 ){
                    $ochko = 1;
                    $ochko2 = 0;
                    $colorClass='green_color';
                } else {
                    $ochko = 0;
                    $ochko2 = 1;
                    $colorClass='coral_color';
                }
                if ($aRec['set_1']=='W' ){
                    $ochko = 1;
                    $ochko2 = 0;
                    $colorClass='green_color';
                } else if ($aRec['set_1']=='L' ) {
                    $ochko = 0;
                    $ochko2 = 1;
                    $colorClass='coral_color';
                }
                
                // Подсчет team_ochko
                if (($set_1 > 0 && $set_1 > $set_2) || $aRec['set_1'] == 'W') {
                    $team_ochko = 2;
                } elseif (($set_2 > 0 && $set_2 > $set_1) || $aRec['set_2'] == 'W' || $aRec['set_1'] == 'L') {
                    $team_ochko = 1;
                }
                
                $ochko_html = $team_ochko !== '' ? '<br />'.$team_ochko : '';
                $itog_html = '<span class="'.$colorClass.'">'.$aRec['set_1'].':'.$aRec['set_2'].'</span>'.$ochko_html;
            } else {
                $itog_html = 'нет результата';
            }
            
            $output .= '<tr>';
            $output .= '<td>'.$aRec['id'].'</td>';
            $output .= '<td>'.$pl_num_grp1.'</td>';
            $output .= '<td>'.$pl_num_grp2.'</td>';
            $output .= '<td>'.$aRec['set_1'].'</td>';
            $output .= '<td>'.$aRec['set_2'].'</td>';
            $output .= '<td>'.$ochko.'</td>';
            $output .= '<td>'.$ochko2.'</td>';
            $output .= '<td>'.$team_ochko.'</td>';
            $output .= '<td>'.$itog_html.'</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        
        // Имена команд
        $output .= '<h3>Имена команд:</h3>';
        $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
        $output .= '<tr><th>ID команды</th><th>Название</th></tr>';
        
        $team_ids = array();
        foreach ($aResults as $aRec) {
            if (!empty($aRec['pl_id_1'])) $team_ids[$aRec['pl_id_1']] = true;
            if (!empty($aRec['pl_id_2'])) $team_ids[$aRec['pl_id_2']] = true;
        }
        
        foreach (array_keys($team_ids) as $team_id) {
            $name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.(int)$team_id, 'name');
            $output .= '<tr><td>'.$team_id.'</td><td>'.($name ?: 'не найдено').'</td></tr>';
        }
        $output .= '</table>';
        
        // Соответствие pl_num_grp и pl_id
        $output .= '<h3>Соответствие pl_num_grp и pl_id (из bs_etaps_players_mesta):</h3>';
        $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
        $output .= '<tr><th>grp_num</th><th>player_id</th><th>Название</th><th>is_command_num</th></tr>';
        
        $mesta_sql = 'SELECT grp_num, player_id, is_command_num FROM '.T_ETAPS_PLAYER_MESTA.' WHERE etap_id='.$etap_id.' AND turnir_id='.$turnir_id.' ORDER BY is_command_num, grp_num';
        $mesta_list = db_list($mesta_sql);
        
        foreach ($mesta_list as $mesta) {
            $name = '';
            if (!empty($mesta['player_id'])) {
                $name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.(int)$mesta['player_id'], 'name');
            }
            $output .= '<tr>';
            $output .= '<td>'.$mesta['grp_num'].'</td>';
            $output .= '<td>'.$mesta['player_id'].'</td>';
            $output .= '<td>'.($name ?: 'не найдено').'</td>';
            $output .= '<td>'.$mesta['is_command_num'].'</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        
        // Проверка конкретной игры ID 139672
        $output .= '<h3>Детальная проверка игры ID 139672 (ОРИОН vs ВОЛЯ):</h3>';
        $game_139672 = null;
        foreach ($aResults as $aRec) {
            if ($aRec['id'] == 139672) {
                $game_139672 = $aRec;
                break;
            }
        }
        
        if ($game_139672) {
            $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;margin-bottom:20px;">';
            $output .= '<tr><th>Поле</th><th>Значение</th><th>Комментарий</th></tr>';
            
            $pl1_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.(int)$game_139672['pl_id_1'], 'name');
            $pl2_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.(int)$game_139672['pl_id_2'], 'name');
            
            $output .= '<tr><td>pl_id_1</td><td>'.$game_139672['pl_id_1'].'</td><td>'.$pl1_name.'</td></tr>';
            $output .= '<tr><td>pl_id_2</td><td>'.$game_139672['pl_id_2'].'</td><td>'.$pl2_name.'</td></tr>';
            $output .= '<tr><td>pl_num_grp1</td><td>'.$game_139672['pl_num_grp1'].'</td><td>номер в группе для pl_id_1</td></tr>';
            $output .= '<tr><td>pl_num_grp2</td><td>'.$game_139672['pl_num_grp2'].'</td><td>номер в группе для pl_id_2</td></tr>';
            $output .= '<tr><td>set_1</td><td>'.$game_139672['set_1'].'</td><td>счет для pl_id_1 ('.$pl1_name.')</td></tr>';
            $output .= '<tr><td>set_2</td><td>'.$game_139672['set_2'].'</td><td>счет для pl_id_2 ('.$pl2_name.')</td></tr>';
            
            // Проверяем соответствие через bs_etaps_players_mesta
            $mesta1 = db_row('SELECT grp_num, player_id FROM '.T_ETAPS_PLAYER_MESTA.' WHERE etap_id='.$etap_id.' AND turnir_id='.$turnir_id.' AND player_id='.(int)$game_139672['pl_id_1']);
            $mesta2 = db_row('SELECT grp_num, player_id FROM '.T_ETAPS_PLAYER_MESTA.' WHERE etap_id='.$etap_id.' AND turnir_id='.$turnir_id.' AND player_id='.(int)$game_139672['pl_id_2']);
            
            $output .= '<tr><td>pl_num_grp1 в базе</td><td>'.$game_139672['pl_num_grp1'].'</td><td>ожидается: '.($mesta1['grp_num'] ?? 'не найдено').'</td></tr>';
            $output .= '<tr><td>pl_num_grp2 в базе</td><td>'.$game_139672['pl_num_grp2'].'</td><td>ожидается: '.($mesta2['grp_num'] ?? 'не найдено').'</td></tr>';
            
            $output .= '</table>';
        }
        
        $output .= '</div>';
        
        $this->content = $output;
    }
    
    function getContent() {
        return $this->content;
    }
}