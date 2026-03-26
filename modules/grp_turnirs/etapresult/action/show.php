<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ShowAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $subMenu2 = array();
  protected  $aResults = array(); // результат игор для таблиц
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $etap_id = 0; // 
  protected  $turnir_id = 0; //

    function init ()
    {

        $this->turnir_id = poste('turnir_id');
        $this->etap_id = poste('etap_id');
        $ob = new ObjectRT(); // иницилизируем объект
     $ob->LoadObject(); // загружаем описание конкретного модуля (какие колонки табл будут отображаться, какие поля формы....)
      $this->subMenu= $ob->getSubmenuList(); 
      $this->subMenu2= $ob->getSubmenu2();

        $name_turnir =db_row('select name,dat  from `' . T_TURNIRS .
            '` where id=' . $this->turnir_id);
        $turnir_name = htmlspecialchars(stripslashes((string)$name_turnir['name']), ENT_QUOTES, 'UTF-8');
        $date = new DateTimeImmutable($name_turnir['dat']);
        $tdat = $date->format('d.m.Y');

        $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$this->turnir_id;
        $vData = db_row($sql);
        $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$this->turnir_id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
        if ($vData['cnt_g']>0  ){
            $title='';
        }elseif($Work_turnir>0){

            $title=' - в процесі';
        }else{

            $title=' - не розпочато';
        }

        if ($_SESSION['is_mobile'] )
            $nameZ='<div class="compare_zagl">'.$turnir_name.' ('.$tdat.$title. ')</div>';
        else
            $nameZ='<div class="poriv_zag">Результати турніру "'.$turnir_name.'" ('.$tdat.$title. ')</div>';

        SystemClass::setZaglModule($nameZ);

     //   SystemClass::setZaglModule($nameZ);

        $submenu_list =array(
            //filter' => array('module' => 'tovs'),
            //    'back' => array('module' => 'players', 'action' => 'list',  'post' => ''),

        );
        SystemClass::$submenu = $submenu_list;

      if (empty($this->etap_id)) {
        //если этапа еще нет ищем первый этап
        $sql = 'select id from '.T_ETAPS.' where turnir_id='.$this->turnir_id.' order by id limit 1';
      //  s($sql);
        $this->etap_id = db_field($sql,'id'); 
      }
      // если в итоге есть хоть один этап то делаем обработку вывода
    if (!empty($this->etap_id)){  
    //  s($this->etap_id);
     $sql = 'select * from '.T_ETAPS.' where id='.$this->etap_id;
      // s($sql);
        $aEtapOpt = db_row($sql);
       // wLog($aEtapOpt);
       

    // если это группы то выводим все что нужно для групп    
     if ($aEtapOpt['type_etap']==1) 
     {   
        // Проверяем, есть ли командные игры (по наличию match_id)
        $has_team_games = db_field('SELECT COUNT(*) FROM '.T_REITING.' WHERE turnir_id='.$this->turnir_id.' AND etap_id='.$this->etap_id.' AND (match_id IS NOT NULL AND match_id != "")', 'COUNT(*)');

        // Для type_etap==1 используем обычные таблицы (all_tables), но с поддержкой клика на счет для командных турниров
        // обрабатываем результаты
        if ($has_team_games > 0) {
            // Это командный турнир - используем all_results_table_comm для добавления кликабельных элементов
            $this->aResults= all_results_table_comm($this->etap_id,$this->turnir_id);
            if (!empty($this->aResults)) {
                foreach ($this->aResults as $grp => $aGrpResults) {
                    if (!empty($aGrpResults)) {
                        foreach ($aGrpResults as $pl1 => $aPl1Results) {
                            if (!empty($aPl1Results)) {
                                foreach ($aPl1Results as $pl2 => $aPl2Result) {
                                    if (!empty($aPl2Result['match_id'])) {
                                        break 3;
                                    }
                                    if (!empty($aPl2Result['itog']) && strpos($aPl2Result['itog'], 'onclick="showTeamMatchDetails') !== false) {
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // Обычный групповой турнир
            $this->aResults= all_results_table($this->etap_id,$this->turnir_id);
        }
        
        // Выводим обычные таблицы
        $javascript_temp = '';
        $this->content = all_tables($this->etap_id,$this->turnir_id,$this->aResults, $javascript_temp);
        

        // Добавляем JavaScript для модального окна, если он был сгенерирован
        // Проверяем $javascript_temp независимо от $has_team_games, так как all_tables() может обнаружить команды по-другому
        if (!empty($javascript_temp)) {
            $this->Java_script = (!empty($this->Java_script) ? $this->Java_script."\n" : '') . $javascript_temp;
        }
    }
    if ($aEtapOpt['type_etap']>1)
    {
        $this->aResults =   all_results_2xminuska($this->etap_id,$this->turnir_id);
      //  s($this->aResults);
        $key_first= array_key_first($this->aResults);
     //   s('$key_first='.$key_first);
        $_SESSION['is_para_minus_olimp'] = !empty($this->aResults[$key_first]['ispara']) ? 1 :0;
      //  $_SESSION['is_para_minus_olimp'] = 1;
        $aMesta =   Mesta_2xminuska($this->etap_id,$this->turnir_id);
        if ($aEtapOpt['cnt_people']==2)
        {
            $this->content = show_2xMinuska_2_pl($this->etap_id,$this->turnir_id,$this->aResults,$aMesta);
        }else
        {
          // если 2хминуска с розихришью всех мест
        if ($aEtapOpt['type_etap']==2)
        {
            // выводим таблицы
            if ($aEtapOpt['cnt_people']>8)
                $this->content = show_2xMinuska($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
            else
                $this->content = show_2xMinuska8($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
        }
        // если 2хминуска с розихришью всех мест
        if ($aEtapOpt['type_etap']==3)
        {
             // выводим таблицы
            if ($aEtapOpt['cnt_people']>8)
                $this->content = show_2xMinuska_to_2($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
            else
                $this->content = show_2xMinuska8_to_2($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
        }
        // если 2хминуска с розихришью всех мест
        if ($aEtapOpt['type_etap']==4)
        {
           // wLog($aEtapOpt['cnt_people'].' 44');
            // выводим таблицы
            if ($aEtapOpt['cnt_people']>8)
                $this->content = show_2xMinuska_to_1($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
            else
                $this->content = show_2xMinuska8_to_1($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
       }
//  if ($aEtapOpt['type_etap']==4)
//        {
//            // выводим таблицы
//            if ($aEtapOpt['cnt_people']>8)
//                $this->content = show_2xMinuska_to_1($this->etap_id,$this->turnir_id,$this->aResults,$aMesta);
//            else
//                $this->content = show_2xMinuska8_to_1($this->etap_id,$this->turnir_id,$this->aResults,$aMesta);
//        }
            if ($aEtapOpt['type_etap']==5)
            {
                // выводим таблицы
                if ($aEtapOpt['cnt_people']>8)
                    $this->content = show_Olimp($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
                else
                    $this->content = show_Olimp8($this->etap_id,$this->turnir_id,$this->aResults,$aMesta,$aEtapOpt['cnt_people']);
            }
            // если это группы для команд  то выводим все что нужно для групп
            if ($aEtapOpt['type_etap']==66)
            {
                // обрабатываем результаты
                $this->aResults= all_results_table_comm($this->etap_id,$this->turnir_id);
              //  s($this->aResults);
                // выводим таблицы (передаем $this->Java_script по ссылке для получения JavaScript)
                $javascript_temp = '';
                $this->content = all_tables_comm($this->etap_id,$this->turnir_id,$this->aResults, $javascript_temp);
                // Добавляем JavaScript для выполнения через eval
                if (!empty($javascript_temp)) {
                    $this->Java_script = (!empty($this->Java_script) ? $this->Java_script."\n" : '') . $javascript_temp;
                }
            }

        }
    }

    }
   // $this->list_show();
    }
    function getContent ()
    {
        return $this->content;
    }
    function getSubMenu ()
    {
        return  $this->subMenu;
    }
     function getSubMenu2 ()
    {
        return  $this->subMenu2;
    }
    function getJavaScript ()
    {
        return $this->Java_script;
    }
  

   
      function list_show_()
    {  // SystemClass::setAction('anyaction');
     //   SystemClass::setModule('groups');
     //  $this->Java_script='reload_page_();';
    //   parent::list_show();
          $post_return = 'groups|show|turnir_id='.$this->id;
        SystemClass::setPost_return($post_return);
       // $this->subMenu= self::$subMenu;
       
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
