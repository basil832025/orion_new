<?php


// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class Sort_etapAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $subMenu2 = array();
    protected $aResults = array(); // результат игор для таблиц
    protected $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    protected $etap_id = 0; //
    protected $turnir_id = 0; //

    function init()
    {
        $this->turnir_id = poste('turnir_id');
        $this->etap_id = poste('etap_id');
        $oldPlayer = poste('oldPlayer');
        $newPlayer = poste('newPlayer');
        $grp= poste('grp');
        $grpnum= poste('grpnum');
        $mesto= poste('mesto');

        if (isset($newPlayer) && isset($oldPlayer) && $newPlayer!=$oldPlayer)
        {
           // s('$oldPlayer='.$oldPlayer);
           // s('newPlayer='.$newPlayer);
            $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$this->etap_id.' and turnir_id='.$this->turnir_id.' and COALESCE(win_player,0)>0';
            $cn_results=db_field($sql,'cn');
            if ($cn_results>0)
            {
                $_SESSION['MESSAGE_AJAX']='В даному турнірі зіграні є ігри! Міняти порядок неможна!';
               // window_mess('В даному турнірі зіграні є ігри! Міняти порядок неможна!');
            }else
            {
                // удалим предідущий варианты заполнения
                $sql ='delete from '.T_REITING.'  where turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id ;
                db_query($sql);
                if (!empty($mesto))
                {
                    $sql = 'select num_posev_olimp from `'.T_ETAPS_PLAYER_MESTA.'` where num_posev_olimp='.$mesto.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                     $num_pos = db_field($sql,'num_posev_olimp');
                    // нового гравця замінюмо амість старого
                    if (!empty($num_pos))
                    {
                        $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id='.$newPlayer.' where num_posev_olimp='.$mesto.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                        db_query($sql);
                    }else
                    {
                        $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set num_posev_olimp='.$mesto.' where player_id='.$newPlayer.' and  turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                        db_query($sql);

                    }
                    // s($sql);
                    // старе місце нового гравя очищаємо
                    $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (num_posev_olimp<>'.$mesto.')  and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                    db_query($sql);
                }else
                {
                    // нового гравця замінюмо амість старого
                    $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id='.$newPlayer.' where groups='.$grp.' and grp_num='.$grpnum.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                    db_query($sql);
                    // s($sql);
                    // старе місце нового гравя очищаємо
                    $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (groups<>'.$grp.' or grp_num<>'.$grpnum.')  and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                    db_query($sql);
                }

              //  s($sql);

            }
        }
     //  $ob = new ObjectRT(); // иницилизируем объект
        $submenu_list =array(
            //filter' => array('module' => 'tovs'),
            'back' => array('module' => 'etaps', 'action' => 'list',  'post' => 'turnir_id='.poste('turnir_id')),
            'filter' => array('menu_name'=>'Заповнити ігри', 'module' => 'etaps', 'action' => 'setgames', 'post' => '&etap_id='.poste('etap_id').'&turnir_id='.poste('turnir_id').'&'),

        );
        SystemClass::$submenu = $submenu_list;
     //   $post_return = 'nomination-show-type='.$type.'&year='.$this->this_year.'&month='.$this->this_month.$TcITY.$TClub;
     //   SystemClass::setPost_return($post_return);


        //   $this->Java_script='select2_vibor("400px");';
        if ($_SESSION['is_mobile'] ) {
            $this->Java_script='chosen_vibor("75%");';
        }else
            $this->Java_script='chosen_vibor("500px");';
        SystemClass::setJava_script($this->Java_script);

        if (empty($this->etap_id)) {
            //если этапа еще нет ищем первый этап
            $sql = 'select id from ' . T_ETAPS . ' where turnir_id=' . $this->turnir_id . ' order by id limit 1';
            //  s($sql);
            $this->etap_id = db_field($sql, 'id');
        }
        // если в итоге есть хоть один этап то делаем обработку вывода
        if (!empty($this->etap_id)) {
            //  s($this->etap_id);
            $sql = 'select * from ' . T_ETAPS . ' where id=' . $this->etap_id;
            // s($sql);
            $aEtapOpt = db_row($sql);
            // если это группы то выводим все что нужно для групп
            if ($aEtapOpt['type_etap'] == 1) {
                // обрабатываем результаты
             //   $this->aResults = all_results_table($this->etap_id, $this->turnir_id);
                // выводим таблицы
                $this->content = all_tables($this->etap_id, $this->turnir_id, $this->aResults);
            }
            // если 2хминуска с розихришью всех мест
            if ($aEtapOpt['type_etap'] == 2 || $aEtapOpt['type_etap'] == 5) {
                $this->aResults = all_results_2xminuska($this->etap_id, $this->turnir_id);
                //   s($this->aResults);
             /*   $aMesta = Mesta_2xminuska($this->etap_id, $this->turnir_id);
                s($aMesta);
             */
                // выводим таблицы
                if ($aEtapOpt['cnt_people'] > 8)
                    $this->content = show_2xMinuska($this->etap_id, $this->turnir_id, $this->aResults);
                else
                    $this->content = show_2xMinuska8($this->etap_id, $this->turnir_id, $this->aResults);
            }
            // если 2хминуска с розихришью всех мест
            if ($aEtapOpt['type_etap'] == 3) {
                $this->aResults = all_results_2xminuska($this->etap_id, $this->turnir_id);
             //   $aMesta = Mesta_2xminuska($this->etap_id, $this->turnir_id);
                // выводим таблицы
                if ($aEtapOpt['cnt_people'] > 8)
                    $this->content = show_2xMinuska($this->etap_id, $this->turnir_id, $this->aResults);
                else
                    $this->content = show_2xMinuska8($this->etap_id, $this->turnir_id, $this->aResults);
            }
            // если 2хминуска с розихришью всех мест
            if ($aEtapOpt['type_etap'] == 4) {
                $this->aResults = all_results_2xminuska($this->etap_id, $this->turnir_id);
              //  $aMesta = Mesta_2xminuska($this->etap_id, $this->turnir_id);
                // выводим таблицы
                if ($aEtapOpt['cnt_people'] > 8)
                    $this->content = show_2xMinuska($this->etap_id, $this->turnir_id, $this->aResults);
                else
                    $this->content = show_2xMinuska8($this->etap_id, $this->turnir_id, $this->aResults);
            }
        }
        // $this->list_show();
    }

    function getContent()
    {
        return $this->content;
    }

    function getSubMenu()
    {
        return $this->subMenu;
    }

    function getSubMenu2()
    {
        return $this->subMenu2;
    }

    function getJavaScript()
    {

        return $this->Java_script;
    }


    function list_show_()
    {  // SystemClass::setAction('anyaction');
        //   SystemClass::setModule('groups');

        //   parent::list_show();
        $post_return = 'groups|show|turnir_id=' . $this->id;
        SystemClass::setPost_return($post_return);
        // $this->subMenu= self::$subMenu;


        // $objList = new ListTable();

        //   $objList->list_show();
        // //   $this->content=$objList->getContent();
        //   $this->subMenu=$objList->getSubMneu();
        //   $this->Java_script=$objList->getJavaScript();

    }
}
//echo 'dsjksd';
