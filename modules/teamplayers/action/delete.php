<?php
require_once __DIR__ . '/../func/func.teamplayers.php';
// Кастомное удаление для teamplayers - обнуляем team_id вместо физического удаления
$id = poste('id');
$team_id = teamplayers_request_param('team_id', 'TEAMPLAYERS_SAVE_TEAM_ID');
$turnir_id = teamplayers_request_param('turnir_id', 'TEAMPLAYERS_SAVE_TURNIR_ID');
$league_id = teamplayers_request_param('league_id', 'TEAMPLAYERS_SAVE_LEAGUE_ID');
$league_id = teamplayers_resolve_league_id($league_id, $turnir_id);

// Если team_id не в POST, пытаемся получить из URL или сессии
if (empty($team_id)) {
    // Пытаемся получить из GET параметров
    $team_id = !empty($_GET['team_id']) ? $_GET['team_id'] : '';
    // Если нет, пытаемся получить из текущего URL (hash может содержать team_id)
    if (empty($team_id) && !empty($_SERVER['HTTP_REFERER'])) {
        if (preg_match('/team_id=(\d+)/', $_SERVER['HTTP_REFERER'], $matches)) {
            $team_id = $matches[1];
        }
    }
    // Если все еще нет, пытаемся получить из сессии фильтра
    if (empty($team_id) && !empty($_SESSION['teamplayers']['where'])) {
        if (preg_match('/team_id=(\d+)/', $_SESSION['teamplayers']['where'], $matches)) {
            $team_id = $matches[1];
        }
    }
}

if (!empty($id)) {
    if (!empty($league_id)) {
        db_query('DELETE FROM `'.T_TEAM_PLAYERS_LEAGUE.'` WHERE league_id='.(int)$league_id.' AND team_id='.(int)$team_id.' AND player_id='.(int)$id);
    } else {
        db_query('UPDATE `'.T_PLAYERS.'` SET team_id=NULL WHERE id='.$id);
    }
    
    // Устанавливаем редирект на список игроков команды
    if (!empty($team_id)) {
        $_SESSION['TEAMPLAYERS_DELETE_TEAM_ID'] = $team_id;
        // Сохраняем параметры турнира для корректного возврата
        if (!empty($turnir_id)) {
            $_SESSION['TEAMPLAYERS_DELETE_TURNIR_ID'] = $turnir_id;
        }
        if (!empty($league_id)) {
            $_SESSION['TEAMPLAYERS_DELETE_LEAGUE_ID'] = $league_id;
        }
        
        // Формируем post_return с параметрами турнира, если они есть
        $post_return = 'teamplayers-list-team_id='.$team_id;
        if (!empty($turnir_id)) {
            $post_return .= '&turnir_id='.$turnir_id;
        }
        if (!empty($league_id)) {
            $post_return .= '&league_id='.$league_id;
        }
        
        // Устанавливаем RedirectUrl через метод-сеттер
        ObjectRT::setRedirectUrl(array(
            'module' => 'teamplayers',
            'action' => 'list',
            'post_return' => $post_return
        ));
        SystemClass::setAction('list');
    }
} else {
    // Если нет ID игрока, показываем ошибку
    window_mess('Помилка: не вказано ID гравця');
}

// Вызываем стандартный list_show() для отображения обновленного списка
$this->list_show();

// После list_show() устанавливаем правильный post_return и post_return_noMA
if (!empty($_SESSION['TEAMPLAYERS_DELETE_TEAM_ID'])) {
    $team_id_return = $_SESSION['TEAMPLAYERS_DELETE_TEAM_ID'];
    $turnir_id_return = !empty($_SESSION['TEAMPLAYERS_DELETE_TURNIR_ID']) ? $_SESSION['TEAMPLAYERS_DELETE_TURNIR_ID'] : '';
    $league_id_return = !empty($_SESSION['TEAMPLAYERS_DELETE_LEAGUE_ID']) ? $_SESSION['TEAMPLAYERS_DELETE_LEAGUE_ID'] : '';
    
    $post_return_final = 'teamplayers-list-team_id='.$team_id_return;
    
    // Добавляем параметры турнира, если они есть
    if (!empty($turnir_id_return)) {
        $post_return_final .= '&turnir_id='.$turnir_id_return;
    }
    if (!empty($league_id_return)) {
        $post_return_final .= '&league_id='.$league_id_return;
    }
    
    // Формируем post_return_noMA с параметрами для использования в ссылках (add, edit)
    $post_return_noMA = '&team_id='.$team_id_return;
    if (!empty($turnir_id_return)) {
        $post_return_noMA .= '&turnir_id='.$turnir_id_return;
    }
    if (!empty($league_id_return)) {
        $post_return_noMA .= '&league_id='.$league_id_return;
    }
    
    // Устанавливаем post_return_noMA для использования в ссылках добавления/редактирования
    SystemClass::setPost_return_noMA($post_return_noMA);
    
    // Обновляем subMenu['add']['post'] с правильными параметрами для кнопки "+"
    if (!empty($this->subMenu['add']['post'])) {
        // Извлекаем старые параметры из post (если есть)
        $old_post = $this->subMenu['add']['post'];
        // Формируем новый post с параметрами турнира
        if (strpos($old_post, 'team_id=') !== false && strpos($old_post, 'turnir_id=') === false) {
            // Если есть team_id, но нет turnir_id, добавляем параметры турнира
            $this->subMenu['add']['post'] = $old_post;
            if (!empty($turnir_id_return)) {
                $this->subMenu['add']['post'] .= '&turnir_id='.$turnir_id_return;
            }
            if (!empty($league_id_return)) {
                $this->subMenu['add']['post'] .= '&league_id='.$league_id_return;
            }
        } else {
            // Если параметров нет, используем post_return_noMA
            $this->subMenu['add']['post'] = $post_return_noMA;
        }
    } else {
        // Если subMenu['add']['post'] пустой, устанавливаем из post_return_noMA
        $this->subMenu['add']['post'] = $post_return_noMA;
    }
    
    SystemClass::setPost_return($post_return_final);
    
    // Сохраняем параметры турнира в сессии для использования при следующем запросе
    if (!empty($turnir_id_return)) {
        $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id_return;
    }
    if (!empty($league_id_return)) {
        $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id_return;
    }
    
    // Очищаем временные переменные сессии после использования
    unset($_SESSION['TEAMPLAYERS_DELETE_TEAM_ID']);
    if (!empty($_SESSION['TEAMPLAYERS_DELETE_TURNIR_ID'])) {
        unset($_SESSION['TEAMPLAYERS_DELETE_TURNIR_ID']);
    }
    if (!empty($_SESSION['TEAMPLAYERS_DELETE_LEAGUE_ID'])) {
        unset($_SESSION['TEAMPLAYERS_DELETE_LEAGUE_ID']);
    }
}

?>
