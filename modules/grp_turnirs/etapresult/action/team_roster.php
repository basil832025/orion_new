<?php

require_once dirname(__DIR__, 3).'/teamplayers/func/func.teamplayers.php';

class team_rosterAction extends ActionModule
{
    protected $content = '';

    function init()
    {
        $team_id = (int)poste('team_id');
        $turnir_id = (int)poste('turnir_id');
        $league_id = 0;

        if ($team_id <= 0) {
            $this->content = json_encode(array('error' => 'Не вказано команду'), JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($turnir_id > 0) {
            $turnir = db_row('SELECT t.league_id, l.is_team_league
                FROM `'.T_TURNIRS.'` t
                LEFT JOIN `bs_leagues` l ON l.id=t.league_id
                WHERE t.id='.$turnir_id.'
                LIMIT 1');
            $league_id = !empty($turnir['league_id']) ? (int)$turnir['league_id'] : 0;

            if (empty($turnir) || (int)$turnir['is_team_league'] !== 1) {
                $this->content = json_encode(array('error' => 'Доступно лише для командних ліг'), JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $team = db_row('SELECT id, name, is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_id.' LIMIT 1');
        if (empty($team) || (int)$team['is_team'] !== 1) {
            $this->content = json_encode(array('error' => 'Команду не знайдено'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $players = teamplayers_list(
            $team_id,
            $league_id,
            'p.id, p.name, p.reiting, p.reiting_ukraine'
        );

        $players_result = array();
        if (!empty($players)) {
            foreach ($players as $player) {
                $players_result[] = array(
                    'id' => (int)$player['id'],
                    'name' => !empty($player['name']) ? $player['name'] : '',
                    'reiting' => isset($player['reiting']) ? (string)$player['reiting'] : '',
                    'reiting_ukraine' => isset($player['reiting_ukraine']) ? (string)$player['reiting_ukraine'] : ''
                );
            }
        }

        $response = array(
            'success' => true,
            'team_id' => $team_id,
            'team_name' => !empty($team['name']) ? $team['name'] : '',
            'players' => $players_result
        );

        header('Content-Type: application/json; charset=utf-8');
        $this->content = json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    function getContent()
    {
        return $this->content;
    }
}

?>
