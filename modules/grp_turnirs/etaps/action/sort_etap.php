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
        $this->turnir_id = (int)poste('turnir_id');
        $this->etap_id = (int)poste('etap_id');
        $oldPlayerRaw = poste('oldPlayer');
        $newPlayerRaw = poste('newPlayer');
        $oldPlayer = (int)$oldPlayerRaw;
        $newPlayer = (int)$newPlayerRaw;
        $grp= (int)poste('grp');
        $grpnum= (int)poste('grpnum');
        $mesto= (int)poste('mesto');
        $row_id = (int)poste('row_id');
        $hasSwapParams = ($newPlayerRaw !== '' && $newPlayerRaw !== null)
            && (
                ($oldPlayerRaw !== '' && $oldPlayerRaw !== null)
                || $row_id > 0
                || $mesto > 0
                || $grp > 0
            );

        if ($hasSwapParams)
        {
            $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$this->etap_id.' and turnir_id='.$this->turnir_id.' and COALESCE(win_player,0)>0';
            $cn_results=db_field($sql,'cn');
            if ($cn_results>0)
            {
                $_SESSION['MESSAGE_AJAX']='В даному турнірі зіграні є ігри! Міняти порядок неможна!';
               // window_mess('В даному турнірі зіграні є ігри! Міняти порядок неможна!');
            }else
            {
                if ($row_id > 0) {
                    $sqlTargetById = 'select id, num_posev_olimp, `groups`, grp_num from `'.T_ETAPS_PLAYER_MESTA.'` where id='.$row_id.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id.' limit 1';
                    $aTargetById = db_row($sqlTargetById);
                    if (!empty($aTargetById)) {
                        $mesto = (int)$aTargetById['num_posev_olimp'];
                        $grp = (int)$aTargetById['groups'];
                        $grpnum = (int)$aTargetById['grp_num'];
                    } else {
                        $row_id = 0;
                    }
                }

                if ($mesto <= 0 && $grp <= 0 && $oldPlayer > 0) {
                    $sqlTarget = 'select num_posev_olimp, `groups`, grp_num from `'.T_ETAPS_PLAYER_MESTA.'` where player_id='.$oldPlayer.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id.' limit 1';
                    $aTarget = db_row($sqlTarget);
                    if (!empty($aTarget)) {
                        $mesto = (int)$aTarget['num_posev_olimp'];
                        if ($mesto <= 0) {
                            $grp = (int)$aTarget['groups'];
                            $grpnum = (int)$aTarget['grp_num'];
                        }
                    }
                }

                if ($mesto > 0 || $grp > 0) {
                    // удалим предідущий варианты заполнения
                    $sql ='delete from '.T_REITING.'  where turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id ;
                    db_query($sql);

                    if ($row_id > 0) {
                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and id<>'.$row_id.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }

                        $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id='.$newPlayer.' where id='.$row_id.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                        db_query($sql);

                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and id<>'.$row_id.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }
                        $mesto = 0;
                        $grp = 0;
                    }

                    if (!empty($mesto) && $row_id <= 0) {
                        // если ставим конкретного гравця - сначала очищаем его старую позицию(и)
                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (num_posev_olimp<>'.$mesto.') and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }

                        // обновляем целевую позицию в сетке
                        $sql = 'select num_posev_olimp from `'.T_ETAPS_PLAYER_MESTA.'` where num_posev_olimp='.$mesto.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                        $num_pos = (int)db_field($sql,'num_posev_olimp');
                        if ($num_pos > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id='.$newPlayer.' where num_posev_olimp='.$mesto.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        } elseif ($newPlayer > 0) {
                            // для случаев, когда позиции нет в явном виде, переносим игрока на нужное место
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set num_posev_olimp='.$mesto.' where player_id='.$newPlayer.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }

                        // страховка от дублей по игроку в этапе
                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (num_posev_olimp<>'.$mesto.') and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }
                    } elseif ($row_id <= 0) {
                        // если ставим конкретного гравця - сначала очищаем его старую позицию(и)
                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (`groups`<>'.$grp.' or grp_num<>'.$grpnum.') and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }

                        // обновляем целевую ячейку группы (старый игрок автоматически становится несеяным)
                        $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id='.$newPlayer.' where `groups`='.$grp.' and grp_num='.$grpnum.' and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                        db_query($sql);

                        // страховка от дублей по игроку в этапе
                        if ($newPlayer > 0) {
                            $sql = 'update `'.T_ETAPS_PLAYER_MESTA.'` set player_id=0 where player_id='.$newPlayer.' and (`groups`<>'.$grp.' or grp_num<>'.$grpnum.') and turnir_id='.$this->turnir_id.' and etap_id='.$this->etap_id;
                            db_query($sql);
                        }
                    }
                }
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
