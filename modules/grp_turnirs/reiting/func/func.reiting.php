<?php
 // s('func_play');
function write_log_reiting($script_tochka,$where,$oper,$id_game=0)
{
    // Защита от записи строковых W/L в числовые поля лога set_1/set_2
    $where = preg_replace_callback('/(set_[12]\s*=\s*)(["\'])([^"\']*)(\2)/i', function ($m) {
        $val = trim((string)$m[3]);
        if (strcasecmp($val, 'W') === 0) {
            $val = '3';
        } elseif (strcasecmp($val, 'L') === 0 || $val === '') {
            $val = '0';
        } elseif (!is_numeric($val)) {
            $val = '0';
        } else {
            $val = (string)(int)$val;
        }
        return $m[1].'"'.$val.'"';
    }, $where);

    $sql = 'insert into bs_log_reitings set 
login_name="'.$_SESSION['gt']['user_login'].'",
dat_oper=now(),
script_tochka="'.$script_tochka.'",
oper="'.$oper.'",
id_game="'.$id_game.'",
'.$where;
    db_query($sql);
}
function get_etap_prim($field,$id,$data){
return $data['name_etap'].'/'.$data['etap_prim'];
}
function get_player($field,$id)
{
  //  s('get_play');
    $turnir_id = poste('turnir_id');
    $etap_id = poste('etap_id');
    // Если etap_id не передан через POST, получаем из данных игры
    if (empty($etap_id)) {
        $game_etap = db_field('SELECT etap_id FROM '.T_REITING.' WHERE id='.(int)$id, 'etap_id');
        if (!empty($game_etap)) {
            $etap_id = $game_etap;
        }
    }
 $num_player =  substr($field, -1);
 //r.rt_id_'.$num_player.'_beg as reiting,
  // Используем LEFT JOIN для bs_turnirplayers, так как для игроков из командных матчей может не быть записи
  // ВАЖНО: Для игр игроков (pair_number > 0) также получаем team_a_id и team_b_id, чтобы проверить привязку игрока к команде
  $sql='select  p.name, p.is_team, COALESCE(tp.beg_reiting, r.rt_id_'.$num_player.'_beg, p.start_reiting, 0) as beg_reiting, p.reiting_ukraine,
  r.group_num, r.pl_num_grp'.$num_player.'  as pl_num, r.type_game, r.olimp16_num, r.set_1, r.set_2, r.match_id, r.etap_id, r.pair_number, r.team_a_id, r.team_b_id, r.pl_id_1, r.pl_id_2      
  from '.T_REITING.' r
  INNER JOIN '.T_PLAYERS.' p ON p.id=r.'.$field.'
  LEFT JOIN bs_turnirplayers tp ON tp.player_id=p.id AND tp.turnir_id='.$turnir_id.'
  where r.id='.$id;
  //s($sql);
/*    $sql='select  name, p.reiting,p.reiting_ukraine,
  group_num, pl_num_grp'.$num_player.'  as pl_num,type_game, olimp16_num,r.set_1,r.set_2      
  from '.T_REITING.' r,'.T_PLAYERS.' p  where p.id=r.'.$field.' and  r.id='.$id;*/
  $aRes = db_row($sql); 
  // Определяем, является ли это командным турниром
  // Проверяем через лигу (is_team_league) или через type_etap этапа
  $is_team_tournament = false;
  $league_id = poste('league_id');
  if (!empty($league_id)) {
      // Проверяем через лигу
      $is_team_league = db_field('SELECT is_team_league FROM bs_leagues WHERE id='.$league_id, 'is_team_league');
      if ($is_team_league == 1) {
          $is_team_tournament = true;
      }
  }
  if (!$is_team_tournament) {
      // Если через лигу не определили, проверяем через type_etap этапа
      $etap_id_check = !empty($aRes['etap_id']) ? $aRes['etap_id'] : (!empty($etap_id) ? $etap_id : 0);
      if ($etap_id_check > 0) {
          $type_etap = db_field('SELECT type_etap FROM bs_etaps_work WHERE id='.$etap_id_check, 'type_etap');
          if (!empty($type_etap) && $type_etap == 66) {
              $is_team_tournament = true; // type_etap=66 означает "команда против команды"
          }
      }
  }
  if (!$is_team_tournament && !empty($aRes)) {
      // Если предыдущие проверки не сработали, проверяем is_team игрока или match_id
      $is_team_tournament = (!empty($aRes['is_team']) && $aRes['is_team'] == 1) || 
                            (!empty($aRes['match_id']) && trim($aRes['match_id']) != '');
  }
  $virt_name_player = $is_team_tournament ? 'Чекаємо команду' : 'Чекаємо гравця';
  if (!empty($aRes)) {
      $set_1 = $aRes['set_1'] == 'W' ? 3 : $aRes['set_1'];
      $set_1 = $set_1 == 'L' ? 0 : $set_1;
      $set_2 = $aRes['set_2'] == 'W' ? 3 : $aRes['set_2'];
      $set_2 = $set_2 == 'L' ? 0 : $set_2;
      $is_win_class = '';
      if ($num_player == 1 && $set_1 > $set_2) $is_win_class = ' fw-bold';
      if ($num_player == 2 && $set_2 > $set_1) $is_win_class = ' fw-bold';
      // Для командных турниров используем "команду", для обычных - "гравця"
      $waiting_text = $is_team_tournament ? 'команду' : 'гравця';
      $virt_name_player = ($aRes['type_game'] == 2 && !empty($aRes['olimp16_num'])) ? 'Очікується ' . $waiting_text . ' (' . $aRes['olimp16_num'] . ')' : '';
      $virt_name_player = ($aRes['type_game'] == 1) ? 'Група ' . $aRes['group_num'] . ' гравець ' . $aRes['pl_num'] : $virt_name_player;
      
      // Для командных игр (pair_number = 0 или NULL) не показываем рейтинг, только имя
      // Для игр игроков (pair_number > 0) показываем рейтинг
      $pair_number = !empty($aRes['pair_number']) ? (int)$aRes['pair_number'] : 0;
      $is_team_game = ($pair_number == 0 || empty($aRes['pair_number']));
      
      // Быстрый режим для списка: избегаем тяжелых дополнительных SQL-проверок
      // чтобы страница списка матчей командной лиги открывалась без зависаний.
      if (poste('action') == 'list' && $is_team_tournament) {
          if ($is_team_game) {
              $name_reiting = !empty($aRes['name']) ? '<div class="p-1 f14NewDis' . $is_win_class . '">' .$aRes['name'] . '</div>' : '';
          } else {
              $player_name_to_display = !empty($aRes['name']) ? $aRes['name'] : '';
              $player_reiting_to_display = !empty($aRes['beg_reiting']) ? round($aRes['beg_reiting']) : 0;
              $txt_FNTU = !empty($aRes['reiting_ukraine']) && $aRes['reiting_ukraine'] > 0 ? '/<span class="f14NewDis">' . $aRes['reiting_ukraine'] . '</span>' : '/0.00';
              $name_reiting = !empty($player_name_to_display) ? '<div class="p-1 f14NewDis' . $is_win_class . '">' .$player_name_to_display  . '<br> <span class="f14NewDis">' . $player_reiting_to_display . '</span>' . $txt_FNTU . '</div>' : '';
          }
          $name_reiting =  !empty($aRes['name']) ?  $name_reiting :  '<div class="p-1 f14NewDis">'.$virt_name_player.'</div>';
          return $name_reiting;
      }

      // ВАЖНО: Для игр игроков проверяем привязку к командам и исправляем отображение
      if (!$is_team_game && $is_team_tournament && $pair_number > 0 && !empty($aRes['match_id'])) {
          $player_id_in_field = !empty($aRes['pl_id_'.$num_player]) ? (int)$aRes['pl_id_'.$num_player] : 0;
          $left_team_id = 0;
          $right_team_id = 0;

          // Порядок команд берем из командной игры (pair_number=0)
          $team_game = db_row('SELECT pl_id_1, pl_id_2, team_a_id, team_b_id FROM `'.T_REITING.'` 
              WHERE match_id="'.addslashes($aRes['match_id']).'" 
              AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
              LIMIT 1');
          if (!empty($team_game)) {
              $team_left_candidate = !empty($team_game['pl_id_1']) ? (int)$team_game['pl_id_1'] : 0;
              $team_right_candidate = !empty($team_game['pl_id_2']) ? (int)$team_game['pl_id_2'] : 0;
              $is_left_team = !empty($team_left_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_left_candidate, 'is_team') : 0;
              $is_right_team = !empty($team_right_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_right_candidate, 'is_team') : 0;
              if ($is_left_team == 1 && $is_right_team == 1) {
                  $left_team_id = $team_left_candidate;
                  $right_team_id = $team_right_candidate;
              } else {
                  $left_team_id = !empty($team_game['team_a_id']) ? (int)$team_game['team_a_id'] : 0;
                  $right_team_id = !empty($team_game['team_b_id']) ? (int)$team_game['team_b_id'] : 0;
              }
          }
          if (empty($left_team_id) || empty($right_team_id)) {
              $left_team_id = !empty($aRes['team_a_id']) ? (int)$aRes['team_a_id'] : 0;
              $right_team_id = !empty($aRes['team_b_id']) ? (int)$aRes['team_b_id'] : 0;
          }

          // Получаем данные пары из bs_team_pairs
          $etap_condition = !empty($etap_id) ? ' AND etap_id='.(int)$etap_id : '';
          $player_team_check = db_row('SELECT 
              CASE 
                  WHEN team_a_player_id='.$player_id_in_field.' THEN team_a_id 
                  WHEN team_b_player_id='.$player_id_in_field.' THEN team_b_id 
                  ELSE 0 
              END as player_team_id,
              team_a_player_id,
              team_b_player_id,
              team_a_id,
              team_b_id
          FROM `bs_team_pairs` 
          WHERE match_id="'.addslashes($aRes['match_id']).'"'.$etap_condition.' 
          AND (team_a_player_id='.$player_id_in_field.' OR team_b_player_id='.$player_id_in_field.')
          AND pair_number='.$pair_number.'
          LIMIT 1');
          
          if (!empty($player_team_check)) {
              $correct_team_a_id = (int)$player_team_check['team_a_id'];
              $correct_team_b_id = (int)$player_team_check['team_b_id'];
              
              // Проверяем, является ли team_a_id и team_b_id командами (is_team=1)
              $is_team_a = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_a_id, 'is_team');
              $is_team_b = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_b_id, 'is_team');
              
              if ((!empty($is_team_a) && $is_team_a == 1) && (!empty($is_team_b) && $is_team_b == 1)) {
                  // Определяем, в какой колонке должен быть этот игрок по порядку команд из командной игры
                  $expected_team_id = ($num_player == 1) ? $left_team_id : $right_team_id;
                  $current_player_team_id = !empty($player_id_in_field) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$player_id_in_field, 'team_id') : 0;
                  
                  // Если игрок находится не в той колонке, нужно показать игрока из правильной команды
                  if ($current_player_team_id > 0 && $current_player_team_id != $expected_team_id) {
                      $team_a_player_id = (int)$player_team_check['team_a_player_id'];
                      $team_b_player_id = (int)$player_team_check['team_b_player_id'];
                      $team_a_player_team_id = !empty($team_a_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_a_player_id, 'team_id') : 0;
                      $team_b_player_team_id = !empty($team_b_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_b_player_id, 'team_id') : 0;
                      $correct_player_id = 0;
                      
                      if ($team_a_player_team_id == $expected_team_id) {
                          $correct_player_id = $team_a_player_id;
                      } elseif ($team_b_player_team_id == $expected_team_id) {
                          $correct_player_id = $team_b_player_id;
                      } elseif ($expected_team_id == $correct_team_a_id) {
                          $correct_player_id = $team_a_player_id;
                      } elseif ($expected_team_id == $correct_team_b_id) {
                          $correct_player_id = $team_b_player_id;
                      }
                      
                      if ($correct_player_id > 0 && $correct_player_id != $player_id_in_field) {
                          // Получаем имя правильного игрока
                          $correct_player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$correct_player_id, 'name');
                          if (!empty($correct_player_name)) {
                              // Переопределяем имя для отображения
                              $aRes['name'] = $correct_player_name;
                          }
                      }
                  }
              }
          }
      }
      
      if ($is_team_game && $is_team_tournament) {
          // Для командных игр - только имя, без рейтинга
          // ВАЖНО: Проверяем соответствие pl_id_1/pl_id_2 с team_a_id/team_b_id из bs_team_pairs
          // Если порядок перепутан, используем правильный порядок из bs_team_pairs
          $team_id_to_display = !empty($aRes['pl_id_'.$num_player]) ? (int)$aRes['pl_id_'.$num_player] : 0;
          $team_name_to_display = !empty($aRes['name']) ? $aRes['name'] : '';
          
          if (!empty($aRes['match_id'])) {
                  // Получаем правильный порядок команд из bs_team_pairs
                  $etap_condition = !empty($etap_id) ? ' AND etap_id='.(int)$etap_id : '';
                  $correct_team_order = db_row('SELECT team_a_id, team_b_id FROM bs_team_pairs 
                  WHERE match_id="'.addslashes($aRes['match_id']).'"'.$etap_condition.' 
                  AND pair_number > 0
                  ORDER BY pair_number ASC
                  LIMIT 1');
              
              if (!empty($correct_team_order)) {
                  $correct_team_a_id = (int)$correct_team_order['team_a_id'];
                  $correct_team_b_id = (int)$correct_team_order['team_b_id'];
                  
                  // Проверяем, является ли team_a_id и team_b_id командами (is_team=1)
                  $is_team_a = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_a_id, 'is_team');
                  $is_team_b = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_b_id, 'is_team');
                  
                  if ((!empty($is_team_a) && $is_team_a == 1) && (!empty($is_team_b) && $is_team_b == 1)) {
                      // Определяем, какая команда должна быть в этой колонке
                      $expected_team_id = ($num_player == 1) ? $correct_team_a_id : $correct_team_b_id;
                      
                      // Если команда в pl_id_1/pl_id_2 не соответствует ожидаемой, используем правильную команду
                      if ($team_id_to_display != $expected_team_id) {
                          // Получаем имя правильной команды
                          $correct_team_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$expected_team_id, 'name');
                          if (!empty($correct_team_name)) {
                              $team_name_to_display = $correct_team_name;
                              $team_id_to_display = $expected_team_id;
                          }
                      }
                  }
              }
          }
          
          $name_reiting = !empty($team_name_to_display) ? '<div class="p-1 f14NewDis' . $is_win_class . '">' .$team_name_to_display . '</div>' : '';
      } else {
          // Для игр игроков - имя с рейтингом
          // ВАЖНО: Проверяем порядок игроков и при необходимости исправляем имя
          $player_name_to_display = !empty($aRes['name']) ? $aRes['name'] : '';
          $player_reiting_to_display = !empty($aRes['beg_reiting']) ? round($aRes['beg_reiting']) : 0;
          
          if ($pair_number > 0 && !empty($aRes['match_id'])) {
              // Получаем правильный порядок игроков из bs_team_pairs, но порядок команд берем из командной игры
              $etap_condition = !empty($etap_id) ? ' AND etap_id='.(int)$etap_id : '';
              $player_game_pair = db_row('SELECT team_a_player_id, team_b_player_id, team_a_id, team_b_id FROM bs_team_pairs 
                  WHERE match_id="'.addslashes($aRes['match_id']).'"'.$etap_condition.' 
                  AND pair_number='.$pair_number.'
                  LIMIT 1');

              $expected_left_player_id = 0;
              $expected_right_player_id = 0;
              if (!empty($player_game_pair)) {
                  // Порядок команд по командной игре (pair_number=0)
                  $left_team_id = 0;
                  $right_team_id = 0;
                  $team_game = db_row('SELECT pl_id_1, pl_id_2, team_a_id, team_b_id FROM `'.T_REITING.'` 
                      WHERE match_id="'.addslashes($aRes['match_id']).'" 
                      AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
                      LIMIT 1');
                  if (!empty($team_game)) {
                      $team_left_candidate = !empty($team_game['pl_id_1']) ? (int)$team_game['pl_id_1'] : 0;
                      $team_right_candidate = !empty($team_game['pl_id_2']) ? (int)$team_game['pl_id_2'] : 0;
                      $is_left_team = !empty($team_left_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_left_candidate, 'is_team') : 0;
                      $is_right_team = !empty($team_right_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_right_candidate, 'is_team') : 0;
                      if ($is_left_team == 1 && $is_right_team == 1) {
                          $left_team_id = $team_left_candidate;
                          $right_team_id = $team_right_candidate;
                      } else {
                          $left_team_id = !empty($team_game['team_a_id']) ? (int)$team_game['team_a_id'] : 0;
                          $right_team_id = !empty($team_game['team_b_id']) ? (int)$team_game['team_b_id'] : 0;
                      }
                  }
                  if (empty($left_team_id) || empty($right_team_id)) {
                      $left_team_id = !empty($aRes['team_a_id']) ? (int)$aRes['team_a_id'] : 0;
                      $right_team_id = !empty($aRes['team_b_id']) ? (int)$aRes['team_b_id'] : 0;
                  }

                  $team_a_player_id = (int)$player_game_pair['team_a_player_id'];
                  $team_b_player_id = (int)$player_game_pair['team_b_player_id'];
                  $team_a_player_team_id = !empty($team_a_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_a_player_id, 'team_id') : 0;
                  $team_b_player_team_id = !empty($team_b_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_b_player_id, 'team_id') : 0;

                  if ($team_a_player_team_id == $left_team_id) {
                      $expected_left_player_id = $team_a_player_id;
                  } elseif ($team_b_player_team_id == $left_team_id) {
                      $expected_left_player_id = $team_b_player_id;
                  }
                  if ($team_a_player_team_id == $right_team_id) {
                      $expected_right_player_id = $team_a_player_id;
                  } elseif ($team_b_player_team_id == $right_team_id) {
                      $expected_right_player_id = $team_b_player_id;
                  }

                  if (empty($expected_left_player_id) || empty($expected_right_player_id)) {
                      $pair_team_a_id = (int)$player_game_pair['team_a_id'];
                      $pair_team_b_id = (int)$player_game_pair['team_b_id'];
                      if ($left_team_id == $pair_team_a_id) {
                          $expected_left_player_id = $team_a_player_id;
                      } elseif ($left_team_id == $pair_team_b_id) {
                          $expected_left_player_id = $team_b_player_id;
                      }
                      if ($right_team_id == $pair_team_a_id) {
                          $expected_right_player_id = $team_a_player_id;
                      } elseif ($right_team_id == $pair_team_b_id) {
                          $expected_right_player_id = $team_b_player_id;
                      }
                  }

                  $pl_id_1 = (int)$aRes['pl_id_1'];
                  $pl_id_2 = (int)$aRes['pl_id_2'];
                  $pair_matches_game =
                      (!empty($expected_left_player_id) && !empty($expected_right_player_id)) &&
                      (($pl_id_1 == $expected_left_player_id && $pl_id_2 == $expected_right_player_id)
                      || ($pl_id_1 == $expected_right_player_id && $pl_id_2 == $expected_left_player_id));

                  if ($pair_matches_game) {
                      $expected_player_id = ($num_player == 1) ? $expected_left_player_id : $expected_right_player_id;
                      $current_player_id = ($num_player == 1) ? $pl_id_1 : $pl_id_2;

                      // Если игрок в неправильной колонке, получаем правильного игрока
                      if (!empty($expected_player_id) && $current_player_id != $expected_player_id) {
                          $correct_player = db_row('SELECT name, start_reiting, reiting_ukraine FROM `'.T_PLAYERS.'` WHERE id='.$expected_player_id);
                          if (!empty($correct_player)) {
                              $player_name_to_display = $correct_player['name'];
                              // Получаем рейтинг правильного игрока
                              $sql_reit = 'SELECT COALESCE(tp.beg_reiting, r.rt_id_'.($num_player == 1 ? '1' : '2').'_beg, p.start_reiting, 0) as beg_reiting, p.reiting_ukraine
                                  FROM '.T_REITING.' r
                                  INNER JOIN '.T_PLAYERS.' p ON p.id='.$expected_player_id.'
                                  LEFT JOIN bs_turnirplayers tp ON tp.player_id=p.id AND tp.turnir_id='.$turnir_id.'
                                  WHERE r.id='.$id;
                              $correct_reiting = db_row($sql_reit);
                              if (!empty($correct_reiting)) {
                                  $player_reiting_to_display = !empty($correct_reiting['beg_reiting']) ? round($correct_reiting['beg_reiting']) : 0;
                                  $aRes['reiting_ukraine'] = !empty($correct_reiting['reiting_ukraine']) ? $correct_reiting['reiting_ukraine'] : 0;
                              }
                          }
                      }
                  }
              }
          }
          
          $txt_FNTU = !empty($aRes['reiting_ukraine']) && $aRes['reiting_ukraine'] > 0 ? '/<span class="f14NewDis">' . $aRes['reiting_ukraine'] . '</span>' : '/0.00';
          $name_reiting = !empty($player_name_to_display) ? '<div class="p-1 f14NewDis' . $is_win_class . '">' .$player_name_to_display  . '<br> <span class="f14NewDis">' . $player_reiting_to_display . '</span>' . $txt_FNTU . '</div>'
              : '';
      }
  }
    $name_reiting =  !empty($aRes['name']) ?  $name_reiting :  '<div class="p-1 f14NewDis">'.$virt_name_player.'</div>';

    return $name_reiting;
}

// Функция для отображения счета с учетом правильного порядка команд
function get_team_score($field, $id) {
    $turnir_id = poste('turnir_id');
    $etap_id = poste('etap_id');
    $num_player = substr($field, -1);
    
    // Получаем данные игры
    $sql = 'SELECT r.set_1, r.set_2, r.match_id, r.etap_id, r.pair_number, r.team_a_id, r.team_b_id, r.pl_id_1, r.pl_id_2
        FROM '.T_REITING.' r
        WHERE r.id='.$id;
    $aRes = db_row($sql);
    
    // Если etap_id не передан через POST, используем из данных игры
    if (empty($etap_id) && !empty($aRes['etap_id'])) {
        $etap_id = $aRes['etap_id'];
    }
    
    if (empty($aRes)) {
        return '';
    }
    
    // Проверяем, является ли это командной игрой
    $pair_number = !empty($aRes['pair_number']) ? (int)$aRes['pair_number'] : 0;
    $is_team_game = ($pair_number == 0 || empty($aRes['pair_number']));
    
    if (poste('action') == 'list') {
        if ($num_player == 1) {
            return ($aRes['set_2'] == "0" && $aRes['set_1'] == "0") ? "" : $aRes['set_1'];
        } else {
            return ($aRes['set_2'] == "0" && $aRes['set_1'] == "0") ? "" : $aRes['set_2'];
        }
    }

    if (!empty($aRes['match_id'])) {
        $left_team_id = 0;
        $right_team_id = 0;

        // Порядок команд берем из командной игры (pair_number=0)
        $team_game = db_row('SELECT pl_id_1, pl_id_2, team_a_id, team_b_id FROM `'.T_REITING.'` 
            WHERE match_id="'.addslashes($aRes['match_id']).'" 
            AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
            LIMIT 1');
        if (!empty($team_game)) {
            $team_left_candidate = !empty($team_game['pl_id_1']) ? (int)$team_game['pl_id_1'] : 0;
            $team_right_candidate = !empty($team_game['pl_id_2']) ? (int)$team_game['pl_id_2'] : 0;
            $is_left_team = !empty($team_left_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_left_candidate, 'is_team') : 0;
            $is_right_team = !empty($team_right_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_right_candidate, 'is_team') : 0;
            if ($is_left_team == 1 && $is_right_team == 1) {
                $left_team_id = $team_left_candidate;
                $right_team_id = $team_right_candidate;
            } else {
                $left_team_id = !empty($team_game['team_a_id']) ? (int)$team_game['team_a_id'] : 0;
                $right_team_id = !empty($team_game['team_b_id']) ? (int)$team_game['team_b_id'] : 0;
            }
        }

        if (empty($left_team_id) || empty($right_team_id)) {
            if ($is_team_game && !empty($aRes['team_a_id']) && !empty($aRes['team_b_id'])) {
                $left_team_id = (int)$aRes['team_a_id'];
                $right_team_id = (int)$aRes['team_b_id'];
            } else {
                // Получаем порядок команд из bs_team_pairs как fallback
                $etap_condition = !empty($etap_id) ? ' AND etap_id='.(int)$etap_id : '';
                $correct_team_order = db_row('SELECT team_a_id, team_b_id FROM bs_team_pairs 
                    WHERE match_id="'.addslashes($aRes['match_id']).'"'.$etap_condition.' 
                    AND pair_number > 0
                    ORDER BY pair_number ASC
                    LIMIT 1');
                if (!empty($correct_team_order)) {
                    $left_team_id = (int)$correct_team_order['team_a_id'];
                    $right_team_id = (int)$correct_team_order['team_b_id'];
                } else {
                    $left_team_id = !empty($aRes['team_a_id']) ? (int)$aRes['team_a_id'] : 0;
                    $right_team_id = !empty($aRes['team_b_id']) ? (int)$aRes['team_b_id'] : 0;
                }
            }
        }

        $correct_team_a_id = $left_team_id;
        $correct_team_b_id = $right_team_id;
        
        if (!empty($correct_team_a_id) && !empty($correct_team_b_id)) {
            
            // Проверяем, является ли team_a_id и team_b_id командами (is_team=1)
            $is_team_a = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_a_id, 'is_team');
            $is_team_b = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$correct_team_b_id, 'is_team');
            
            if ((!empty($is_team_a) && $is_team_a == 1) && (!empty($is_team_b) && $is_team_b == 1)) {
                $should_swap = false;
                
                if ($is_team_game) {
                    // Для командных игр проверяем порядок команд
                    $pl_id_1 = (int)$aRes['pl_id_1'];
                    $pl_id_2 = (int)$aRes['pl_id_2'];
                    
                    // Если порядок перепутан (pl_id_1 != correct_team_a_id), меняем местами счет
                    if ($pl_id_1 != $correct_team_a_id || $pl_id_2 != $correct_team_b_id) {
                        $should_swap = true;
                    }
                } else {
                    // Для игр игроков проверяем порядок игроков через bs_team_pairs, но с порядком команд из командной игры
                    $etap_condition = !empty($etap_id) ? ' AND etap_id='.(int)$etap_id : '';
                    $player_game_pair = db_row('SELECT team_a_player_id, team_b_player_id, team_a_id, team_b_id FROM bs_team_pairs 
                        WHERE match_id="'.addslashes($aRes['match_id']).'"'.$etap_condition.' 
                        AND pair_number='.$pair_number.'
                        LIMIT 1');
                    
                    if (!empty($player_game_pair)) {
                        $pl_id_1 = (int)$aRes['pl_id_1'];
                        $pl_id_2 = (int)$aRes['pl_id_2'];
                        $expected_left_player_id = 0;
                        $expected_right_player_id = 0;
                        $team_a_player_id = (int)$player_game_pair['team_a_player_id'];
                        $team_b_player_id = (int)$player_game_pair['team_b_player_id'];
                        $team_a_player_team_id = !empty($team_a_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_a_player_id, 'team_id') : 0;
                        $team_b_player_team_id = !empty($team_b_player_id) ? (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$team_b_player_id, 'team_id') : 0;

                        if (!empty($correct_team_a_id) && !empty($correct_team_b_id)) {
                            if ($team_a_player_team_id == $correct_team_a_id) {
                                $expected_left_player_id = $team_a_player_id;
                            } elseif ($team_b_player_team_id == $correct_team_a_id) {
                                $expected_left_player_id = $team_b_player_id;
                            }
                            if ($team_a_player_team_id == $correct_team_b_id) {
                                $expected_right_player_id = $team_a_player_id;
                            } elseif ($team_b_player_team_id == $correct_team_b_id) {
                                $expected_right_player_id = $team_b_player_id;
                            }
                        }

                        if (empty($expected_left_player_id) || empty($expected_right_player_id)) {
                            $pair_team_a_id = (int)$player_game_pair['team_a_id'];
                            $pair_team_b_id = (int)$player_game_pair['team_b_id'];
                            if ($correct_team_a_id == $pair_team_a_id) {
                                $expected_left_player_id = $team_a_player_id;
                            } elseif ($correct_team_a_id == $pair_team_b_id) {
                                $expected_left_player_id = $team_b_player_id;
                            }
                            if ($correct_team_b_id == $pair_team_a_id) {
                                $expected_right_player_id = $team_a_player_id;
                            } elseif ($correct_team_b_id == $pair_team_b_id) {
                                $expected_right_player_id = $team_b_player_id;
                            }
                        }

                        $pair_matches_game =
                            (!empty($expected_left_player_id) && !empty($expected_right_player_id)) &&
                            (($pl_id_1 == $expected_left_player_id && $pl_id_2 == $expected_right_player_id)
                            || ($pl_id_1 == $expected_right_player_id && $pl_id_2 == $expected_left_player_id));
                        
                        // Если порядок игроков перепутан в рамках той же пары, меняем местами счет
                        if ($pair_matches_game && ($pl_id_1 != $expected_left_player_id || $pl_id_2 != $expected_right_player_id)) {
                            $should_swap = true;
                        }
                    }
                }
                
                // Определяем счет для каждой команды
                // После swap: $set_1 = счет команды слева (team_a), $set_2 = счет команды справа (team_b)
                if ($should_swap) {
                    // Меняем местами счет, чтобы он соответствовал правильному порядку команд
                    $set_1 = $aRes['set_2']; // Счет команды слева (team_a) берем из set_2
                    $set_2 = $aRes['set_1']; // Счет команды справа (team_b) берем из set_1
                } else {
                    // Порядок правильный, счет уже соответствует
                    $set_1 = $aRes['set_1']; // Счет команды слева (team_a)
                    $set_2 = $aRes['set_2']; // Счет команды справа (team_b)
                }
                
                // Возвращаем счет для соответствующей колонки
                // num_player == 1: левая колонка (team_a) -> $set_1
                // num_player == 2: правая колонка (team_b) -> $set_2
                if ($num_player == 1) {
                    return ($set_2 == "0" && $set_1 == "0") ? "" : $set_1;
                } else {
                    return ($set_2 == "0" && $set_1 == "0") ? "" : $set_2;
                }
            }
        }
    }
    
    // Если не командная игра или не удалось определить порядок, возвращаем исходный счет
    if ($num_player == 1) {
        return ($aRes['set_2'] == "0" && $aRes['set_1'] == "0") ? "" : $aRes['set_1'];
    } else {
        return ($aRes['set_2'] == "0" && $aRes['set_1'] == "0") ? "" : $aRes['set_2'];
    }
}

  function sql_raschet($turnir_id, $etap_id,$this_aResults,$group_num)
    { global $mesto_in_grp,$aMestaPlayersGrp;
       
       $sql = 'SELECT p.name,tp.player_id,tp.`groups` as `groups`,grp_num,grp_win_set,grp_lose_set,grp_mesto,grp_ochki,case when reiting>0 then reiting else start_reiting end as beg_reit
 FROM '.T_ETAPS_PLAYER_MESTA.' tp, '.T_PLAYERS.' p where  turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and tp.`groups`='.$group_num.' and p.id=tp.player_id 
ORDER BY tp.`groups`, tp.grp_ochki desc,grp_num';
       $allPlayers = db_list($sql);
//s($sql);
       $gr  = 0;
     // $chel=0;
  ///   s($allPlayers);
  $aGroupsPlays=array();
     // загоним в массив по группам
       foreach ($allPlayers as $aRec)
       {
           if ($gr<>$aRec['groups'])
          {
            $gr=$aRec['groups'];
          }
          $aGroupsPlays[$gr][]=$aRec;
        }  
      //  s($this->aGroupsPlays); 
      // пройдемся по группам
       foreach ($aGroupsPlays  as $grp => $aGroupPlayers)
       {
          $mesto_in_grp=0; // обновляем место
          $aMestaPlayersGrp=array();
         // if ($grp==2) // temp
       //  $this->aGroupPlayers = $aGroupPlayers;
          obrGroup($grp,$aGroupPlayers,0,$this_aResults);
         // s($aMestaPlayersGrp);
          foreach($aMestaPlayersGrp as $mesto =>$aPlay)
          {
            db_query('update '.T_ETAPS_PLAYER_MESTA.' set grp_mesto='.$mesto.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.'  
            and `groups`='.$aPlay['groups'].' and grp_num='.$aPlay['grp_num']);
          }
        //  s('itog');s($this->aMestaPlayersGrp);
       }  
    }
    // занесения в массив по местам 
    function obrGroup($grp,$aGroupPlayers,$vhodRec,$this_aResults)
    {
       // s('$vhodRec= '.$vhodRec);
       // s($aGroupPlayers);
        $aTempPlayers=array();
        $grpoch=0;
        if ($vhodRec==0) $tp='grp_ochki';
        if ($vhodRec==1) $tp='win_ochok';
        if ($vhodRec==2) $tp='diff_sets';
        if ($vhodRec==3) $tp='diff_sets_all';
        if ($vhodRec==4) $tp='win_game_all';
        if ($vhodRec==5) $tp='lose_game_all';
        if ($vhodRec>=6) $tp='grp_num';
        // пройдемся по игрокам
        foreach ($aGroupPlayers as $aPlay)
        {
              if ($grpoch<>0 && $aPlay[$tp]<>$aPredPlayer[$tp])
           {
              addArrMesta($grp,$aTempPlayers,$vhodRec,$this_aResults);
            //  s($this->aMestaPlayersGrp);
              $aTempPlayers=array(); 
              
           } 
             $aTempPlayers[$aPlay['grp_num']] = $aPlay;
       
           $grpoch = 1;
           //$aTP = $aPlay[$tp];
           $aPredPlayer = $aPlay;
        //   $grpoch=1;
         // s($aPlay);  
         
        }
         addArrMesta($grp,$aTempPlayers,$vhodRec,$this_aResults); // последний игрок
    }
    function addArrMesta($grp,$aTempPlayers,$vhodRec,$this_aResults)
    {  global $mesto_in_grp,$aMestaPlayersGrp;
     //  s('tyt'); 
   //   s($aTempPlayers);
     // самый простой случай 1 человек в массиве
      $cnPlayers=count($aTempPlayers);
      if ($cnPlayers==1) 
        {
            $mesto_in_grp++;
           ///  list($key, $aPlay) = each($aTempPlayers); // старый метод заменили на свою функию
            list($key, $aPlay)   = myEach($aTempPlayers);
             $aMestaPlayersGrp[$mesto_in_grp] = $aPlay;
        
        }
       if ($cnPlayers==2) 
        {
            // Для пары игроков на первом шаге используем личную встречу
            if ($vhodRec==0)
            {
                $aTempPlayers=  obrabOdinOchek($aTempPlayers,$this_aResults);
                foreach ($aTempPlayers as $aPlay)
                {
                    $mesto_in_grp++;
                    $aMestaPlayersGrp[$mesto_in_grp]=$aPlay;
                }
            }
            // Если равенство пришло с более поздних критериев,
            // продолжаем цепочку критериев, не возвращаясь к личной встрече
            if ($vhodRec==1)
            {
                $aTempPlayers = obrPoSets($aTempPlayers,$this_aResults);
                obrGroup($grp,$aTempPlayers,2,$this_aResults);
            }
            if ($vhodRec==2)
            {
                $aTempPlayers = obrPoSetsAll($grp, $aTempPlayers, $this_aResults);
                obrGroup($grp,$aTempPlayers,3,$this_aResults);
            }
            if ($vhodRec==3)
            {
                $aTempPlayers = obrWinsLosesAll($grp, $aTempPlayers, $this_aResults);
                uasort($aTempPlayers, function($a, $b){
                    return (int)$b['win_game_all'] - (int)$a['win_game_all'];
                });
                obrGroup($grp,$aTempPlayers,4,$this_aResults);
            }
            if ($vhodRec==4)
            {
                $aTempPlayers = obrWinsLosesAll($grp, $aTempPlayers, $this_aResults);
                uasort($aTempPlayers, function($a, $b){
                    return (int)$a['lose_game_all'] - (int)$b['lose_game_all'];
                });
                obrGroup($grp,$aTempPlayers,5,$this_aResults);
            }
            // 7-й критерий: для 2 игроков используем личную встречу
            if ($vhodRec==5)
            {
                $aTempPlayers= obrabOdinOchek($aTempPlayers,$this_aResults);
                $aTempPlayers = stabilizeBySeeding($aTempPlayers, 'win_ochok');
                foreach ($aTempPlayers as $aPlay)
                {
                    $mesto_in_grp++;
                    $aMestaPlayersGrp[$mesto_in_grp]=$aPlay;
                }
            }
            // 8-й (финальный) критерий: посев
            if ($vhodRec>=6)
            {
                uasort($aTempPlayers, function($a, $b){
                    return (int)$a['grp_num'] - (int)$b['grp_num'];
                });
                foreach ($aTempPlayers as $aPlay)
                {
                    $mesto_in_grp++;
                    $aMestaPlayersGrp[$mesto_in_grp]=$aPlay;
                }
            }
         } 
      if ($cnPlayers>2) 
        {
            
          
          // загоняем в рекурсию при условии что  это первый обход 
          // сначала провека у кого больше побед
          if ($vhodRec==0) 
          {
            $aTempPlayers=  obrabOdinOchek($aTempPlayers,$this_aResults);
            obrGroup($grp,$aTempPlayers,1,$this_aResults); 
            }
         // проверка по сетам между собой
         if ($vhodRec==1) 
         { 
            $aTempPlayers = obrPoSets($aTempPlayers,$this_aResults);
            obrGroup($grp,$aTempPlayers,2,$this_aResults);
        //       s('$vhodRec='.$vhodRec);
       //   s($aTempPlayers);
         }
          // проверка разницы сетов по всем играм группы
          if ($vhodRec==2) 
          { 
             $aTempPlayers = obrPoSetsAll($grp, $aTempPlayers, $this_aResults);
             obrGroup($grp,$aTempPlayers,3,$this_aResults);
          }

          // больше побед по всем играм группы
          if ($vhodRec==3)
          {
             $aTempPlayers = obrWinsLosesAll($grp, $aTempPlayers, $this_aResults);
             uasort($aTempPlayers, function($a, $b){
                return (int)$b['win_game_all'] - (int)$a['win_game_all'];
             });
             obrGroup($grp,$aTempPlayers,4,$this_aResults);
          }

          // меньше поражений по всем играм группы
          if ($vhodRec==4)
          {
             $aTempPlayers = obrWinsLosesAll($grp, $aTempPlayers, $this_aResults);
             uasort($aTempPlayers, function($a, $b){
                return (int)$a['lose_game_all'] - (int)$b['lose_game_all'];
             });
             obrGroup($grp,$aTempPlayers,5,$this_aResults);
          }

          // 7-й критерий для >2 равных не применяется,
          // сразу переходим к 8-му критерию (посев)
          if ($vhodRec==5)
          {
             uasort($aTempPlayers, function($a, $b){
                return (int)$a['grp_num'] - (int)$b['grp_num'];
             });
             foreach ($aTempPlayers as $aPlay)
             {
                $mesto_in_grp++;
                $aMestaPlayersGrp[$mesto_in_grp] = $aPlay;
             }
          }

          if ($vhodRec>=6)
          {
             uasort($aTempPlayers, function($a, $b){
                return (int)$a['grp_num'] - (int)$b['grp_num'];
             });
             foreach ($aTempPlayers as $aPlay)
             {
                $mesto_in_grp++;
                $aMestaPlayersGrp[$mesto_in_grp] = $aPlay;
             }
          }
          
        }
    
    }
    // обработка по коефициентам 
    function obrPoSets($aTempPlayers,$this_aResults,$aAll=array())
    {
        $aTempPlayers2= !empty($aAll) ? $aAll : $aTempPlayers;
      //  s('obrab');
       // s($aTempPlayers);
        foreach ($aTempPlayers as $key => $aPlay)
        {
            $play_this = $aPlay['grp_num'];
            $group_this = $aPlay['groups'];
            $win_ochok=0;$rasn_pobed=0;$rasn_proigr=0;
             foreach ($aTempPlayers2 as $key2 => $aPlay2)
                {
                    if ($key2<>$key) 
                    {// кто победил между 2 игроками
                      $rasn_pobed =  $rasn_pobed+$this_aResults[$group_this][$play_this][$aPlay2['grp_num']]['first_res'];
                      $rasn_proigr = $rasn_proigr+ $this_aResults[$group_this][$play_this][$aPlay2['grp_num']]['second_res'];
                    // s('$aPlay2='.$play_this);s($aPlay2);s($rasn);
                   // $win_ochok=   $win_ochok+$rasn;
                    }
                }
                $aTempPlayers[$key]['diff_sets'] =round($rasn_pobed/$rasn_proigr,2)*100;
                
        }
      //    s('obrab');
       // s($aTempPlayers);
      // сортировка массива по полю win_ochok в обратном порядке
      uasort($aTempPlayers, function($a, $b){
            return -($a['diff_sets'] - $b['diff_sets']);
        });
      return $aTempPlayers;
    }

    function tie_set_to_int($setVal)
    {
        if ($setVal === 'W') return 3;
        if ($setVal === 'L') return 0;
        return (int)$setVal;
    }

    function obrPoSetsAll($grp, $aTempPlayers, $this_aResults)
    {
        foreach ($aTempPlayers as $key => $aPlay)
        {
            $win_sets_all = isset($aPlay['grp_win_set']) ? (int)$aPlay['grp_win_set'] : 0;
            $lose_sets_all = isset($aPlay['grp_lose_set']) ? (int)$aPlay['grp_lose_set'] : 0;
            $aTempPlayers[$key]['diff_sets_all'] = $win_sets_all - $lose_sets_all;
        }

        uasort($aTempPlayers, function($a, $b){
            return (int)$b['diff_sets_all'] - (int)$a['diff_sets_all'];
        });

        return $aTempPlayers;
    }

    function obrWinsLosesAll($grp, $aTempPlayers, $this_aResults)
    {
        foreach ($aTempPlayers as $key => $aPlay)
        {
            $play_this = (int)$aPlay['grp_num'];
            $wins_all = 0;
            $loses_all = 0;

            if (!empty($this_aResults[$grp][$play_this]))
            {
                foreach ($this_aResults[$grp][$play_this] as $play_enemy => $aGame)
                {
                    $play_enemy = (int)$play_enemy;
                    if ($play_enemy === $play_this) {
                        continue;
                    }
                    if (!isset($aGame['win'])) {
                        continue;
                    }

                    $id_win = (int)$aGame['win'];
                    if ($id_win === $play_this) {
                        $wins_all++;
                    } elseif ($id_win === (int)$play_enemy) {
                        $loses_all++;
                    }
                }
            }

            $aTempPlayers[$key]['win_game_all'] = $wins_all;
            $aTempPlayers[$key]['lose_game_all'] = $loses_all;
        }

        return $aTempPlayers;
    }

    function stabilizeBySeeding($aPlayers, $primary_field)
    {
        uasort($aPlayers, function($a, $b) use ($primary_field) {
            $a_val = isset($a[$primary_field]) ? (int)$a[$primary_field] : 0;
            $b_val = isset($b[$primary_field]) ? (int)$b[$primary_field] : 0;
            if ($a_val === $b_val) {
                return (int)$a['grp_num'] - (int)$b['grp_num'];
            }
            return $b_val - $a_val;
        });

        return $aPlayers;
    }
  /*    function obrPoSets_old($aTempPlayers,$aAll=array())
    {
        $aTempPlayers2= !empty($aAll) ? $aAll : $aTempPlayers;
      //  s('obrab');
       // s($aTempPlayers);
        foreach ($aTempPlayers as $key => $aPlay)
        {
            $play_this = $aPlay['grp_num'];
            $group_this = $aPlay['groups'];
            $win_ochok=0;
             foreach ($aTempPlayers2 as $key2 => $aPlay2)
                {
                    if ($key2<>$key) 
                    {// кто победил между 2 игроками
                      $rasn =  $this->aResults[$group_this][$play_this][$aPlay2['grp_num']]['first_res']-$this->aResults[$group_this][$play_this][$aPlay2['grp_num']]['second_res'];
                    // s('$aPlay2='.$play_this);s($aPlay2);s($rasn);
                    $win_ochok=   $win_ochok+$rasn;
                    }
                }
                $aTempPlayers[$key]['diff_sets'] =$win_ochok;
                
        }
        //  s('obrab');
       // s($aTempPlayers);
      // сортировка массива по полю win_ochok в обратном порядке
      uasort($aTempPlayers, function($a, $b){
            return -($a['diff_sets'] - $b['diff_sets']);
        });
      return $aTempPlayers;
    }
    */
    // обработка одинаковы мест самая сложная задача много вариантов
    function obrabOdinOchek($aTempPlayers,$this_aResults)
    {
        $aTempPlayers2= $aTempPlayers;
      //  s('obrab');
       // s($aTempPlayers);
        foreach ($aTempPlayers as $key => $aPlay)
        {
            $play_this = $aPlay['grp_num'];
            $group_this = $aPlay['groups'];
            $win_ochok=0;
             foreach ($aTempPlayers2 as $key2 => $aPlay2)
                {
                     if ($key2<>$key) 
                     {// кто победил между 2 игроками
                      $id_win = isset($this_aResults[$group_this][$play_this][$aPlay2['grp_num']]['win'])
                          ? $this_aResults[$group_this][$play_this][$aPlay2['grp_num']]['win']
                          : 0;
                      if ($id_win==$play_this) $win_ochok++; 
                     }
                 }
                $aTempPlayers[$key]['win_ochok'] = $win_ochok;
        }
        //  s('obrab');
        //s($aTempPlayers);
      // сортировка массива по полю win_ochok в обратном порядке
      uasort($aTempPlayers, function($a, $b){
            return -($a['win_ochok'] - $b['win_ochok']);
        });
      return $aTempPlayers;
}
   
function all_results ($turnir_id,$etap_id)
   {
      //получаем результаты 
     $sql='SELECT * FROM '.T_REITING.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.'  and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
     $aResults = db_list($sql);
     
     $this_aResults =array();
     $grp_num_cache = array();
     if (!empty($aResults)){
    // пройдемся по всем резульаьам обработаем 
    foreach ($aResults as $aRec)
    {
        $group_num = $aRec['group_num'];
        $pl_num_grp1 = $aRec['pl_num_grp1'];
        $pl_num_grp2 = $aRec['pl_num_grp2'];

        // Для командных игр (pair_number=0 и есть match_id) берем grp_num из таблицы участников группы
        if (!empty($aRec['match_id']) && (empty($aRec['pair_number']) || $aRec['pair_number'] == 0)) {
            $team_id_1 = (int)$aRec['pl_id_1'];
            $team_id_2 = (int)$aRec['pl_id_2'];
            if (!isset($grp_num_cache[$group_num][$team_id_1])) {
                $grp_num_cache[$group_num][$team_id_1] = (int)db_field('SELECT grp_num FROM '.T_ETAPS_PLAYER_MESTA.' WHERE etap_id='.$etap_id.' AND `groups`='.(int)$group_num.' AND player_id='.$team_id_1.' LIMIT 1', 'grp_num');
            }
            if (!isset($grp_num_cache[$group_num][$team_id_2])) {
                $grp_num_cache[$group_num][$team_id_2] = (int)db_field('SELECT grp_num FROM '.T_ETAPS_PLAYER_MESTA.' WHERE etap_id='.$etap_id.' AND `groups`='.(int)$group_num.' AND player_id='.$team_id_2.' LIMIT 1', 'grp_num');
            }
            if (!empty($grp_num_cache[$group_num][$team_id_1])) {
                $pl_num_grp1 = $grp_num_cache[$group_num][$team_id_1];
            }
            if (!empty($grp_num_cache[$group_num][$team_id_2])) {
                $pl_num_grp2 = $grp_num_cache[$group_num][$team_id_2];
            }
        }

        $this_aResults[$group_num][$pl_num_grp1][$pl_num_grp2]['first_res'] = $aRec['set_1'];
        $this_aResults[$group_num][$pl_num_grp1][$pl_num_grp2]['second_res'] = $aRec['set_2'];
        if ($aRec['set_1']>$aRec['set_2'])
            $this_aResults[$group_num][$pl_num_grp1][$pl_num_grp2]['win'] = $pl_num_grp1;
         else     
           $this_aResults[$group_num][$pl_num_grp1][$pl_num_grp2]['win'] = $pl_num_grp2;
        
          // теперь перевоачиваем для нижней части таблицы
        $this_aResults[$group_num][$pl_num_grp2][$pl_num_grp1]['first_res'] = $aRec['set_2'];
        $this_aResults[$group_num][$pl_num_grp2][$pl_num_grp1]['second_res'] = $aRec['set_1'];
     if ($aRec['set_2']>$aRec['set_1'])
            $this_aResults[$group_num][$pl_num_grp2][$pl_num_grp1]['win'] = $pl_num_grp2;
         else     
           $this_aResults[$group_num][$pl_num_grp2][$pl_num_grp1]['win'] = $pl_num_grp1;
        
    }   
    }
    return $this_aResults;
 //  s($this->aResults); 
   } 
function get_num_game_pars($num_posev_olimp,$aVariants2minuska_16)
{
  //  global $aVariants2minuska_16;
    $playNum=0;
    foreach ($aVariants2minuska_16 as $num => $aPars)
    {
        if ($aPars['player1']==$num_posev_olimp) { $playNum=1;  break;}
        if ($aPars['player2']==$num_posev_olimp) { $playNum=2;  break;}
    }
    return array($num,$playNum);
}
function grpSetMestaAllZmeyka($etap_id)
{
       
$sql = 'select  t.* from '.T_ETAPS.' t  where  t.id='.$etap_id;  
// s($sql);
$aEtap =db_row($sql); 
$cntGroups = 1;
if (!empty($aEtap['cnt_grp']))
$cntGroups= $aEtap['cnt_grp'];
else
{
  if (!empty($aEtap['group_id'])) 
  {
    $sql = 'select  v.* from '.T_ETAPS.' t, '.T_TURNIR_VARIANTS.' v where  t.id='.$etap_id.' and v.id=group_id ';
        $aVariants = db_row($sql);
        $cntGroups = $aVariants['cntGroups'];
  }
}  
     
     $sql='SELECT e.*  FROM `'.T_ETAPS_PLAYER_MESTA.'` e 
 where  etap_id='.$etap_id.' order by  grp_mesto,`groups`,grp_num';
$aPlayersMesta = db_list($sql);
  $cnPeople = count($aPlayersMesta);
    foreach ($aPlayersMesta as $aMesto)
    { 
        $tmpMesto = !empty($aMesto['grp_mesto']) ? $aMesto['grp_mesto'] : $aMesto['grp_num'];
        $aGroupMesto[$aMesto['groups']][$tmpMesto] = $aMesto;
    } 
  // s($aGroupMesto) ;
 /* foreach ($aPlayersMesta as $aPlayer)
     { $mesto++;
         $sql ='update '.T_ETAPS_PLAYER_MESTA.' set mesto_all='.$mesto.' 
                where id='.$aPlayer['id'];
                db_query($sql);
     }   
    */
//$mesto=0;
$n =1 ; // порядковый номер 
$por_zmeyki = 1; // 1 вниз 2 вверх 
$numGrpZmeyki = 1; // порядковый номер группы
$mestowork=1; // начинаем с 1 мест
//s('$cntGroups='.$cntGroups);
//s('$cnPeople='.$cnPeople);
while ($n<=$cnPeople) 
{
   // проходим змейкой туда сюда и  зхаполняем места
     $sug=1;
   //  s('nnnnnnnnnn===='.$n);
    // запускаем змейку по группам в прямом порядке
    if ( $sug == 1 && $por_zmeyki == 1)
    { 
       $player_id = isset($aGroupMesto[$numGrpZmeyki][$mestowork]['player_id'])
           ? (int)$aGroupMesto[$numGrpZmeyki][$mestowork]['player_id']
           : 0;
       if ($player_id > 0) {
         $sql ='update '.T_ETAPS_PLAYER_MESTA.' set mesto_all='.$n.' 
                where player_id='.$player_id.' and etap_id='.$etap_id ;
                db_query($sql);
       }
       //  s('$numGrpZmeyki='.$numGrpZmeyki. ' $por_zmeyki='.$por_zmeyki.' $mestowork='.$mestowork.' $n='.$n);
      //  s($sql);
        $numGrpZmeyki++; 
       
        //}
        if ($numGrpZmeyki>$cntGroups)
        {
            if ($cntGroups==2)
            {
               $numGrpZmeyki = 1; // порядковый номер группы
                $por_zmeyki=1;  
            }
            else
            {
               $numGrpZmeyki = $cntGroups; // порядковый номер группы
                $por_zmeyki=2;  
            }
            
            $sug=0;
            $mestowork++;  
        }
    } //---- конец змейки в прямом порядке
    if ( $sug == 1 && $por_zmeyki == 2)
    { 
           $player_id = isset($aGroupMesto[$numGrpZmeyki][$mestowork]['player_id'])
               ? (int)$aGroupMesto[$numGrpZmeyki][$mestowork]['player_id']
               : 0;
         if ($player_id > 0) {
         $sql ='update '.T_ETAPS_PLAYER_MESTA.' set mesto_all='.$n.' 
               where player_id='.$player_id.' and etap_id='.$etap_id ;
                db_query($sql);
         }
         //              s('$numGrpZmeyki='.$numGrpZmeyki. ' $por_zmeyki='.$por_zmeyki.' $mestowork='.$mestowork.' $n='.$n);
       // s($sql);  
          $numGrpZmeyki--; 
       if ($numGrpZmeyki<1) 
       {
            $por_zmeyki=1; 
            $sug=0;  
            $numGrpZmeyki = 1; // порядковый номер группы
            $mestowork++;  
       }
    
    }    //---- конец змейки в обратном порядке   
    $n++;

}

}
// переносим сыграные игры с предыдущего этапа
function setPernosGamesFromIstochn($form,$turnir_id,$etap_id)
{
    // пройдемся по всех играх этого этапа где есть пара живых игроков
    $sql='select * from '.T_REITING.' where etap_id='.$etap_id.' and pl_id_1>0 and pl_id_2>0';
    $aGamesThisEtap = db_list($sql);
    // найдем сыграные игры в предедущем этапе
    $sql='select * from '.T_REITING.' where etap_id='.$form['istochnik_posev'].' and pl_id_1>0 and pl_id_2>0';
    $aGamesPredEtap = db_list($sql);
    // пройдемся по масиву и запишем ключи в интерсеном формате , для быстрого поиск игр
    $aGamesPredEtapItog=[];
    if (!empty($aGamesPredEtap))
        foreach ($aGamesPredEtap as $predGame)
        {
            if ($predGame['pl_id_1']<$predGame['pl_id_2']) $key=$predGame['pl_id_1'].'-'.$predGame['pl_id_2'];
            else  $key=$predGame['pl_id_2'].'-'.$predGame['pl_id_1'];
            $aGamesPredEtapItog[$key] = $predGame;
        }
    // s($aGamesPredEtapItog);
    //пройдемся по играх в этом этапе и поищем игры в предыдущем , если найдем то запишем
    if (!empty($aGamesThisEtap))
        foreach ($aGamesThisEtap as $aGame)
        {
            if ($aGame['pl_id_1']<$aGame['pl_id_2']) $key=$aGame['pl_id_1'].'-'.$aGame['pl_id_2'];
            else  $key=$aGame['pl_id_2'].'-'.$aGame['pl_id_1'];
            //если нашли игру в предыдущем этапе групп переносим
            if (!empty($aGamesPredEtapItog[$key]))
            {   // если игрок 1 совпал с игроком 1 предыдущего этапа
                if ($aGamesPredEtapItog[$key]['pl_id_1']==$aGame['pl_id_1'])
                {
                    $setSql = 'set_1="'.$aGamesPredEtapItog[$key]['set_1'].'",set_2="'.$aGamesPredEtapItog[$key]['set_2'].'"
            ,win_player='.$aGamesPredEtapItog[$key]['win_player'].',
            lose_player='.$aGamesPredEtapItog[$key]['lose_player'].',
             perenos_etap='.$form['istochnik_posev'].'';
                }
                if ($aGamesPredEtapItog[$key]['pl_id_1']==$aGame['pl_id_2'])
                {
                    $setSql = 'set_1="'.$aGamesPredEtapItog[$key]['set_2'].'",set_2="'.$aGamesPredEtapItog[$key]['set_1'].'"
            ,win_player='.$aGamesPredEtapItog[$key]['win_player'].',
            lose_player='.$aGamesPredEtapItog[$key]['lose_player'].',
             perenos_etap='.$form['istochnik_posev'].'';
                }
                $sql ='update '.T_REITING.'  SET '.$setSql. ' where id='.$aGame['id']  ;
                // s($sql);
                db_query($sql);
                // ЗАПИШЕМ СЕТИ И ОЧКИ
                setOchkiSetsForGrp($aGamesPredEtapItog[$key]['win_player'],$aGamesPredEtapItog[$key]['lose_player'],$etap_id,$turnir_id);


            }

        }
}

function setOchkiSetsForGrp($win,$lose,$etap_id,$turnir_id)
{
    // Проверяем, является ли это командой
    $is_team_win = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$win, 'is_team');
    $is_team_lose = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$lose, 'is_team');
    
    // Если это команды, обрабатываем командные игры (где pair_number = 0 или NULL)
    if (!empty($is_team_win) && $is_team_win == 1 && !empty($is_team_lose) && $is_team_lose == 1) {
        // Командный турнир - обрабатываем командные игры
        $sql = 'SELECT r.* FROM '.T_REITING.' r 
            WHERE ((r.pl_id_1='.$win.' AND r.pl_id_2='.$lose.') OR (r.pl_id_1='.$lose.' AND r.pl_id_2='.$win.'))
            AND etap_id='.$etap_id.' 
            AND turnir_id='.$turnir_id.'
            AND (r.pair_number = 0 OR r.pair_number IS NULL OR r.pair_number = "")';
        $team_games = db_list($sql);
        
        $win_game = 0;  // Количество побед (матчей)
        $lose_game = 0; // Количество поражений (матчей)
        $win_set = 0;   // Общее количество выигранных игр (пар) в матчах
        $lose_set = 0;  // Общее количество проигранных игр (пар) в матчах
        
        foreach ($team_games as $rec) {
            if (empty($rec['win_player']) || empty($rec['lose_player'])) {
                continue;
            }

            $raw_set_1 = isset($rec['set_1']) ? trim((string)$rec['set_1']) : '';
            $raw_set_2 = isset($rec['set_2']) ? trim((string)$rec['set_2']) : '';

            $set_1 = ($raw_set_1 === 'W') ? 3 : (($raw_set_1 === 'L') ? 0 : (int)$raw_set_1);
            $set_2 = ($raw_set_2 === 'W') ? 3 : (($raw_set_2 === 'L') ? 0 : (int)$raw_set_2);

            if ($set_1 == $set_2) {
                continue;
            }

            if ($rec['pl_id_1'] == $win) {
                $team_win_sets = $set_1;
                $team_lose_sets = $set_2;
                $team_technical_loss = ($raw_set_1 === 'L');
            } else {
                $team_win_sets = $set_2;
                $team_lose_sets = $set_1;
                $team_technical_loss = ($raw_set_2 === 'L');
            }

            if ($team_win_sets > $team_lose_sets) {
                $win_game++;
            } else {
                $lose_game += $team_technical_loss ? 0 : 1;
            }

            $win_set += $team_win_sets;
            $lose_set += $team_lose_sets;
        }
        
        // Для команд: очки = победы * 2 + поражения * 1 (матчей)
        // grp_win_set и grp_lose_set - это общая сумма всех выигранных и проигранных игр (пар) во всех матчах
        $sql ='UPDATE '.T_ETAPS_PLAYER_MESTA.' SET 
            `grp_ochki`='.($win_game*2+$lose_game).', 
            grp_win_set='.$win_set.', 
            grp_lose_set='.$lose_set.' 
            WHERE turnir_id='.$turnir_id.' AND etap_id='.$etap_id.' AND player_id='.$win;
        db_query($sql);
    } else {
        // Обычный турнир (не командный) - используем старую логику
        $sql = ' select * from '.T_REITING. ' r where (r.lose_player='.$win.' or r.win_player='.$win.')  and etap_id='.$etap_id.' and  r.turnir_id='.$turnir_id;
        $aWin = db_list($sql);
        //s($sql);
        // посчитаем к-во побед и сетов
        $win_set=0;
        $lose_set=0;
        $win_game=0;
        $lose_game=0;
        foreach ($aWin as $rec)
        {
            // когда игрок выигрывал
            if ($rec['win_player']==$win)
            {

                $is_L=1;
                if ($rec['pl_id_1']==$win)
                {
                    if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                    $win_set += (int) $rec['set_1'];
                    $lose_set += (int) $rec['set_2'];

                }else
                {

                    $win_set += (int) $rec['set_2'];
                    $lose_set += (int) $rec['set_1'];
                  }
                if ($is_L>0)   $win_game++;
            }
            //когда игрок проигрывал
            if ($rec['lose_player']==$win)
            {
                $is_L=1;
                if ($rec['pl_id_1']==$win)
                {
                    if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                    $win_set += (int) $rec['set_1'];
                    $lose_set += (int) $rec['set_2'];

                }else
                {
                    if ($rec['set_2']=='L') {$rec['set_2']=0; $is_L=0;}
                    $win_set += (int) $rec['set_2'];
                    $lose_set += (int) $rec['set_1'];
                }
                if ($is_L>0)  $lose_game++;
            }
        }
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // выигравший игшрок
        $sql ='update  '.T_ETAPS_PLAYER_MESTA.' set `grp_ochki`='.($win_game*2+$lose_game).', grp_win_set='.$win_set.', 
        grp_lose_set='.$lose_set.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and player_id='.$win;

    /*
     $sql ='update  '.T_TURNIR_PLAYERS.' set `grp_ochki`='.($win_game*2+$lose_game).', grp_win_set='.$win_set.',
      grp_lose_set='.$lose_set.' where turnir_id='.$turnir_id.' and player_id='.$win;
    */
    db_query($sql);
    }
}

// Функция для пересчета очков и соотношения сетов для всех команд в группе
function recalculateAllTeamsInGroup($etap_id, $turnir_id, $group_num)
{
    // Получаем все команды в группе
    $sql = 'SELECT DISTINCT player_id FROM '.T_ETAPS_PLAYER_MESTA.' 
        WHERE etap_id='.$etap_id.' 
        AND turnir_id='.$turnir_id.' 
        AND `groups`='.$group_num.'
        AND player_id > 0';
    $teams = db_list($sql);
    
    if (!empty($teams)) {
        // Пересчитываем очки для каждой команды
        foreach ($teams as $team) {
            $team_id = $team['player_id'];
            
            // Проверяем, является ли это командой
            $is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_id, 'is_team');
            
            if (!empty($is_team) && $is_team == 1) {
                // Получаем все командные игры этой команды в группе
                $sql = 'SELECT r.* FROM '.T_REITING.' r 
                    WHERE (r.pl_id_1='.$team_id.' OR r.pl_id_2='.$team_id.')
                    AND etap_id='.$etap_id.' 
                    AND turnir_id='.$turnir_id.'
                    AND group_num='.$group_num.'
                    AND (r.pair_number = 0 OR r.pair_number IS NULL OR r.pair_number = "")';
                $team_games = db_list($sql);
                
                $win_game = 0;  // Количество побед (матчей)
                $lose_game = 0; // Количество поражений (матчей)
                $win_set = 0;   // Общее количество выигранных игр (пар) в матчах
                $lose_set = 0;  // Общее количество проигранных игр (пар) в матчах
                
                foreach ($team_games as $rec) {
                    if (empty($rec['win_player']) || empty($rec['lose_player'])) {
                        continue;
                    }

                    $raw_set_1 = isset($rec['set_1']) ? trim((string)$rec['set_1']) : '';
                    $raw_set_2 = isset($rec['set_2']) ? trim((string)$rec['set_2']) : '';

                    $set_1 = ($raw_set_1 === 'W') ? 3 : (($raw_set_1 === 'L') ? 0 : (int)$raw_set_1);
                    $set_2 = ($raw_set_2 === 'W') ? 3 : (($raw_set_2 === 'L') ? 0 : (int)$raw_set_2);

                    if ($set_1 == $set_2) {
                        continue;
                    }

                    if ($rec['pl_id_1'] == $team_id) {
                        $team_win_sets = $set_1;
                        $team_lose_sets = $set_2;
                        $team_technical_loss = ($raw_set_1 === 'L');
                    } else {
                        $team_win_sets = $set_2;
                        $team_lose_sets = $set_1;
                        $team_technical_loss = ($raw_set_2 === 'L');
                    }

                    if ($team_win_sets > $team_lose_sets) {
                        $win_game++;
                    } else {
                        $lose_game += $team_technical_loss ? 0 : 1;
                    }

                    $win_set += $team_win_sets;
                    $lose_set += $team_lose_sets;
                }
                
                // Обновляем очки и соотношение сетов для команды
                $sql ='UPDATE '.T_ETAPS_PLAYER_MESTA.' SET 
                    `grp_ochki`='.($win_game*2+$lose_game).', 
                    grp_win_set='.$win_set.', 
                    grp_lose_set='.$lose_set.' 
                    WHERE turnir_id='.$turnir_id.' AND etap_id='.$etap_id.' AND player_id='.$team_id.' AND `groups`='.$group_num;
                db_query($sql);
            }
        }
    }
}

function mesta_2x_minuska8($olimp16_num,$cnt_people,$etap_id,$win,$lose)
{
    switch ($olimp16_num)
    {
        case 7:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=1 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=2 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 12:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=4 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 13:
            if ($cnt_people==7)  $win =$lose;
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            if ($cnt_people>7)
            {
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=8 where etap_id='.$etap_id.' and player_id='.$lose;
                db_query($sql);
            }
            break;

        case 8:
        case 9:
            if ($cnt_people==7)
            {
                $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 10:
        case 11:
            if ($cnt_people==5)
            {
                $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 6:
            if ($cnt_people==3)
            {
                $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 14:
            if ($cnt_people==5)  $win =$lose;
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            if ($cnt_people>5)
            {
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=6 where etap_id='.$etap_id.' and player_id='.$lose;
                db_query($sql);
            }
            break;
    }
}
function mesta_olimp_8($olimp16_num,$cnt_people,$etap_id,$win,$lose)
{
    switch ($olimp16_num)
    {
        case 7:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=1 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=2 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 11:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=4 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 12:
            {
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
             $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=8 where etap_id='.$etap_id.' and player_id='.$lose;
                db_query($sql);
            }
            break;
  case 10:
            {
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
             $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=6 where etap_id='.$etap_id.' and player_id='.$lose;
                db_query($sql);
            }
            break;
        case 8:
        case 9:
            if ($cnt_people==7)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=7 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 1:
        case 2:
            if ($cnt_people==5)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=5 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 5:
        case 6:
            if ($cnt_people==3)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=3 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3     where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        //  case 8:
    }
}
function mesta_2x_minuska16($olimp16_num,$cnt_people,$etap_id,$win,$lose)
{
    // определение мест 2х минуска
    switch ($olimp16_num) {
        case 15:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=1 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=2 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 28:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=4 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 31:
            if ($cnt_people==13)  $win =$lose;
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=13 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            if ($cnt_people>13){
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=14 where etap_id='.$etap_id.' and player_id='.$lose;
                db_query($sql);
            }
            break;
        case 16:
        case 17:
        case 18:
        case 19:
            if ($cnt_people==13)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=13 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=13 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 37:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=15 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=16 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;

        case 30:
            if ($cnt_people==15)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=15 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=15 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 34:

            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=9 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);

            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set mesto_all=10 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);

        case 20:
        case 23:
        case 21:
        case 22:
            if ($cnt_people==9)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=9 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=9 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;

        case 38:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=11 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=12 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;


       // case 32:
        case 33:
            if ($cnt_people==11)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=11 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=11 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;

        case 36:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=6 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 35:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=8 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;

    }

}
function mesta_olimp_16($olimp16_num,$cnt_people,$etap_id,$win,$lose)
{
    // определение мест 2х минуска
    switch ($olimp16_num) {
        case 15:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=1 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=2 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 26:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=3 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=4 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 29:
            if ($cnt_people==13)  $win =$lose;
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=13 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            if ($cnt_people>13){
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=14 where etap_id='.$etap_id.' and player_id='.$lose;
              //  s($sql);
                db_query($sql);
            }
            break;
        case 16:
        case 17:
        case 18:
        case 19:
            if ($cnt_people==13)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=13 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=13 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 20:
        case 21:
            if ($cnt_people==11)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=11 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=11 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 32:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=15 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=16 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;

        case 27:
        case 28:
            if ($cnt_people==15)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=15 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=15 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;
        case 22:

            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=9 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);

            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set mesto_all=10 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);

        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
        case 8:
                if ($cnt_people==9)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=9 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=9 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;

        case 30:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=11 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=12 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 20:
        case 21:
            if ($cnt_people==11)
            {  $win =$lose;
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all="" where etap_id='.$etap_id.' and mesto_all=11 ';
                db_query($sql);
                $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=11 where etap_id='.$etap_id.' and player_id='.$win;
                db_query($sql);
            }
            break;

        case 25:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=5 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=6 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;
        case 31:
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=7 where etap_id='.$etap_id.' and player_id='.$win;
            db_query($sql);
            $sql = 'update '.T_ETAPS_PLAYER_MESTA.' set  mesto_all=8 where etap_id='.$etap_id.' and player_id='.$lose;
            db_query($sql);
            break;

    }

}
function statistic_player_online($PlayId,$turnir_id)
{
    // получим все результаты игр по данному турниру и игороку
    $sqlR = 'select * from '.T_REITING.' where (pl_id_1='.$PlayId.' or pl_id_2='.$PlayId.') and COALESCE(win_player,0)>0 and perenos_etap=0 and turnir_id='.$turnir_id;
    $allGames = db_list($sqlR);
 //   s($sqlR);
    $smDiff=0;$cntGames=0;$cntWins=0;$cntLose=0;$sumSet=0;$sumSetWins=0;$sumSetLose=0;

    if (!empty($allGames)) // если массив не пустой и игрок играл на данном турнире тогда естьсмысл продолжать
    {
//пройдем по массиву игр найдем всю статистику
        $diff=0; // дельта
        $cntGames=0;
        $cntWins=0;
        $cntLose=0;
        $smDiff=0;
        $sumSet=0;
        $sumSetWins=0;
        $sumSetLose=0;
       foreach($allGames as $g => $aGame)
        { $reiting_2=0;
            $set1 = 0;
            $set2 = 0;
            $Play2_id=0;
            $cntGames++;
            $diff=0;
            // определяем игрок в записе 1 или 2
            if ($aGame['pl_id_1']== $PlayId)
            {   $set1 = $aGame['set_1'];
                $set2 = $aGame['set_2'];
                $set1 = $set1=='W' ? 3 : $set1;
                $set2 = $set2=='W' ? 3 : $set2;
                $set1 = $set1=='L' ? 0 : $set1;
                $set2 = $set2=='L' ? 0 : $set2;
                //подсчет сыграных сетов
                $smSets1 = $set1=='W' ? 0 : $set1;
                $smSets2 = $set2=='W' ? 0 : $set2;
                $sumSet=$sumSet+($smSets1+$smSets2);
                $sumSetWins=$sumSetWins+ $smSets1;
                $sumSetLose=$sumSetLose+ $smSets2;

                $Play2_id = $aGame['pl_id_2'];
                $diff=$aGame['diff_1'];
                //   $reiting_2 = $allPlayers[$aGame['pl_id_2']]['reit'];
            }
            // определяем игрок в записе 1 или 2
            if ($aGame['pl_id_2']== $PlayId)
            {
                $set1 = $aGame['set_2'];
                $set2 = $aGame['set_1'];
                $set1 = $set1=='W' ? 3 : $set1;
                $set2 = $set2=='W' ? 3 : $set2;
                $set1 = $set1=='L' ? 0 : $set1;
                $set2 = $set2=='L' ? 0 : $set2;
                //подсчет сыграных сетов
                $smSets1 = $set1=='W' ? 0 : $set1;
                $smSets2 = $set2=='W' ? 0 : $set2;
                $sumSet=$sumSet+($smSets1+$smSets2);
                $sumSetWins=$sumSetWins+ $smSets1;
                $sumSetLose=$sumSetLose+ $smSets2;

                //   $reiting_2 = $allPlayers[$aGame['pl_id_1']]['reit'];
                $Play2_id = $aGame['pl_id_1'];
                $diff=$aGame['diff_2'];
            }
            //   if (!is_numeric($set2)) s('$set2='.$set2.'==');
            if ($set1-$set2>0) $cntWins++; else $cntLose++;
            $smDiff=$smDiff+$diff;

            // функция перерасчитает рейтинги и обновит таблицу ретинга по 2 игрокам
            //   list($diff1,$diff2) = $this->add_reiting_rec($PlayId,$Play2_id,$reiting,$reiting_2,$set1,$set2,$aGame['id']);


        } // end for $allGames


    } // end if $allGames
    $id_rec='';
    $sql = 'select id from '.T_TURNIR_PLAYERS.' where player_id='.$PlayId.' and turnir_id='.$turnir_id.' limit 1';
    //  s($sql);
    $id_rec=db_field($sql,'id');
    // s('$id_rec='.$id_rec);
    //  $reiting_end = ($reiting+$smDiff)<1 ? 1 : ($reiting+$smDiff);
    $diff_round= round($smDiff,0);
    $where = '
         diff='.$smDiff.',cnt_games='.$cntGames.',cnt_wins='.$cntWins.',cnt_lose='.$cntLose.
        ', cnt_sets='.$sumSet.', cnt_sets_win='.$sumSetWins.', cnt_sets_lose='.$sumSetLose.', diff_round='.$diff_round.'
            ';
    if (!empty($id_rec)) $sql= 'UPDATE '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id_rec;
 //   s($sql);
    db_query($sql);
    }
?>
