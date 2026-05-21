<?php

class teamstatsAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';

    function init()
    {
        include_once ROOT.'func/raschet_func.php';

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
            SystemClass::setMessage_user('Не знайдено турнір для перерахунку статистики команд.');
            $this->finish_list();
            return;
        }

        $league_id = (int)poste('league_id');
        if ($league_id <= 0) {
            $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
        }

        recalculate_team_turnir_stats($turnir_id);

        SystemClass::setMessage_user('Статистику команд перераховано.');
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
