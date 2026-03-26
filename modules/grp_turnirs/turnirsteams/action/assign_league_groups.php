<?php

class assign_league_groupsAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';

    function init()
    {
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login']))) {
            SystemClass::setMessage_user(MESS_NO_ACCESS);
            $this->finish_list();
            return;
        }

        $turnir_id = (int)poste('turnir_id');
        if ($turnir_id <= 0 && !empty($this->id)) {
            $turnir_id = (int)$this->id;
        }

        if ($turnir_id <= 0) {
            SystemClass::setMessage_user('Не знайдено турнір для розподілу команд.');
            $this->finish_list();
            return;
        }

        $turnir = db_row('SELECT id, league_id, is_team_qual, team_leagues_count FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id);
        if (empty($turnir)) {
            SystemClass::setMessage_user('Турнір не знайдено.');
            $this->finish_list($turnir_id);
            return;
        }

        $league_id = (int)poste('league_id');
        if ($league_id <= 0 && !empty($turnir['league_id'])) {
            $league_id = (int)$turnir['league_id'];
        }

        if ($league_id <= 0) {
            SystemClass::setMessage_user('Не знайдено лігу для розподілу команд.');
            $this->finish_list($turnir_id);
            return;
        }

        if (empty($turnir['is_team_qual'])) {
            SystemClass::setMessage_user('Цей турнір не позначений як відбірковий.');
            $this->finish_list($turnir_id, $league_id);
            return;
        }

        $leagues_count = !empty($turnir['team_leagues_count']) ? (int)$turnir['team_leagues_count'] : 0;
        if ($leagues_count < 2) {
            SystemClass::setMessage_user('Вкажіть кількість ліг після відбору (мінімум 2).');
            $this->finish_list($turnir_id, $league_id);
            return;
        }

        if (!auto_assign_league_groups($turnir_id, $league_id, $leagues_count)) {
            SystemClass::setMessage_user('У турнірі немає команд для розподілу.');
            $this->finish_list($turnir_id, $league_id);
            return;
        }

        $counts = array();
        $sql_counts = 'SELECT group_num, COUNT(*) as cnt FROM `bs_league_team_groups` WHERE league_id='.$league_id.' GROUP BY group_num';
        $rows_counts = db_list($sql_counts);
        if (!empty($rows_counts)) {
            foreach ($rows_counts as $row) {
                $g = !empty($row['group_num']) ? (int)$row['group_num'] : 0;
                if ($g > 0) {
                    $counts[$g] = !empty($row['cnt']) ? (int)$row['cnt'] : 0;
                }
            }
        }

        $parts = array();
        for ($i = 1; $i <= $leagues_count; $i++) {
            $parts[] = 'Ліга '.$i.': '.(!empty($counts[$i]) ? $counts[$i] : 0);
        }

        SystemClass::setMessage_user('Команди розподілено по лігам. '.implode(', ', $parts));
        $this->finish_list($turnir_id, $league_id);
    }

    private function finish_list($turnir_id = 0, $league_id = 0)
    {
        SystemClass::setAction('anyaction');
        SystemClass::setModule('turnirsteams');
        parent::list_show();
        $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
        if (!empty($turnir_id)) {
            $post_return = 'turnirsteams-list-&turnir_id='.$turnir_id.$menu_league;
            SystemClass::setPost_return($post_return);
            SystemClass::setPost_return_noMA('&turnir_id='.$turnir_id.$menu_league);
        }
    }

    function getContent()
    {
        return $this->content;
    }
    function getSubMneu()
    {
        return $this->subMenu;
    }
    function getJavaScript()
    {
        return $this->Java_script;
    }
}

?>
