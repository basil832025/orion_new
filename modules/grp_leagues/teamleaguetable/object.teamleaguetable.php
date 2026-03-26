<?php
// таблиця ліг для командних ліг
class TeamLeagueTableObject extends ObjectRT
{
    function init()
    {
        $league_id = poste('league_id');
        self::InitLeaguesMenu();
        self::$table_class = 'table_mob_turn';

        $url_back = 'leagues-list';
        if ($_SESSION['is_mobile']) {
            SystemClass::$Java_script_module = 'show_zag_left("#'.$url_back.'");';
        } else {
            $show_zag_left = 'show_zag_center();show_zag_left_big("#'.$url_back.'");';
            SystemClass::$Java_script_module = $show_zag_left;
        }

        if (!empty($league_id)) {
            $name_league = db_row('select name from `bs_leagues` where id='.(int)$league_id);
            $league_name = !empty($name_league['name']) ? $name_league['name'] : '';
            if ($_SESSION['is_mobile']) {
                $nameZ = '<div class="compare_zagl">Таблиця ліг "'.$league_name.'"</div>';
            } else {
                $nameZ = '<div class="poriv_zag">Таблиця ліг "'.$league_name.'"</div>';
            }
            self::$nameZList = $nameZ;
        }

        self::$aParent[0] = ['name_field'=>'league_id', 'type'=>'Hidden'];
    }
}
?>
