<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class get_reitingAction extends ActionModule
{
    protected  $content = '';
    protected  $ligas_session = '';
    protected  $is_new_player = 0; // если новые игроки на туринре
    protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
    protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
    protected  $ArrEtaps = array();
    protected  $subMenu = array();
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента

    function init ()
    {
        $this->import_ligas();
        $this->list_show_lig();
    }
    function getContent ()
    {
        return $this->content;
    }
    function getSubMneu ()
    {
        return  $this->subMenu;
    }
    function getJavaScript ()
    {

        return $this->Java_script;
    }
    function import_ligas()
    { //SELECT * FROM bs_players WHERE COALESCE(id_reiting,'')<>''
        $sql = 'SELECT p.* FROM '.T_PLAYERS.' p  where COALESCE(id_reiting,"")<>""';
        // s($sql);//exit;
        $aPlayers = db_list($sql);
        foreach($aPlayers as $Player)
        {
            // s($Player);
            if (!empty($Player['id_reiting']))
            {
                $aInfo_play_ligas = $this->get_ligs_player($Player['id_reiting']);
                if (!empty($aInfo_play_ligas))
                $this->updateStatisticPlayer($Player['id'],$aInfo_play_ligas,$Player);
            }
        }

    }
    function get_ligs_player($PlayId)
    {
        $aPlayer=[];
        //  s($PlayId);
        $url="https://ligas.io/api/organizations/uttf/users/".$PlayId;
        $json = file_get_contents($url);
        if (!empty($json))
        {
            $data = json_decode($json, TRUE);
            if (!empty($data))
            {
                foreach($data['fields'] as $val)
                {
                    //  print_r($val['key']);
                    if (isset($val['value'])){
                        if ($val['key']=='expire') $val['value'] =substr($val['value'],0,10);
                        $aPlayer[$val['key']]= $val['value'];
                    }
                }
                $aPlayer['fio'] = $aPlayer['surname'].' ' .$aPlayer['name'];
                $aPlayer['ranking'] = isset($aPlayer['ranking'])? $aPlayer['ranking'] : 0;
                $aPlayer['sex'] = isset($aPlayer['sex'])? $aPlayer['sex'] : 'm';

            }
            }

        return $aPlayer;
    }
    function updateStatisticPlayer($PlayId,$aPlayer,$Player)
    {
        //  $name_ligas = empty($Player['name_ligas']) ? $aPlayer['fio']

        //  s($aPlayer);
        $is_opl_reiting = !empty($aPlayer['expire']) ? 1 :0;
        db_query('UPDATE '.T_PLAYERS.' SET 
    name_ligas="'.$aPlayer['fio'].'",
    god_rogd="'.$aPlayer['birthyear'].'",
    city="'.$aPlayer['city'].'",
    sex="'.$aPlayer['sex'].'",
    ligas_photo="'.$aPlayer['image'].'",
    reiting_ukraine="'.$aPlayer['ranking'].'",
    is_opl_reiting='.$is_opl_reiting.'
     where id='.$PlayId);
    }
    function list_show_lig()
    {   SystemClass::setAction('anyaction');
        SystemClass::setModule('players');
        //  $this->Java_script='reload_page_();';
        //   s('do');
        parent::list_show();
        //    s('posle');
        $post_return = 'players-list';
        SystemClass::setPost_return($post_return);




        // SystemClass::setJava_script($this->Java_script);

        // $objList = new ListTable();

        //   $objList->list_show();
        // //   $this->content=$objList->getContent();
        //   $this->subMenu=$objList->getSubMneu();
        //   $this->Java_script=$objList->getJavaScript();

    }
}
//echo 'dsjksd';
?>