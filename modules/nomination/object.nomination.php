<?php


// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class NominationObject extends ObjectRT
{
    //$this-> = 'tree';
    function init ()
    {


        self::$nameZ='"Результаты этапов::';
        self::$nameZList='';
        if ($_SESSION['gt']['user_rule']<10)
            self::$submenu_list =array(
                // 'filter' => array('module' => 'tovs'),
                // 'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>'print_page(\'print_group\');'),
              //  'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>' print();'),

                // 'prava_user' => array('menu_name'=>'Рассчитать места', 'module' => 'groups', 'action' => 'raschetmest', 'post' => 'id='.poste('id')),

                //  'report_ok' => array('menu_name'=>'Пересчитать рейтинг по данному туриниру','module' => 'turnirsplayers', 'action' => 'raschet', 'post' => 'id='.poste('id')),
            //    'back' => array('module' => 'turnirs', 'action' => 'list'),
            );
        //s('ttyyy');
        $this->getSubMenu2Data();
        /*self::$subMenu2 = array('1'=>
                               array(
                                       'name'=>'Группы',
                                       'href'=>'#turnirs|list',
                                                              ),
                               '2'=>
                               array(
                                       'name'=>'Первый финал',
                                       'href'=>'#turnirsplayers|list|turnir_id='.poste('id'),
                                   ),
                                  '3'=>
                               array(
                                       'name'=>'Второй финал ',
                                       'href'=>'#groups|show|id='.poste('id'),
                                ),
                               );

        */
        self::$nameZ='Гравці турніру ';
        self::$nameZList='(статистика гравців)';
    //    self::InitMainMenu();
        // s(self::$submenu_list);
   /*     self::$aParent= array('name_field'=>'turnir_id',
            'table'=>T_TURNIRS,
            'type'=>'Hidden'
        );*/
    }
    function getSubMenu2Data()
    {
      $type = poste('type');
        $attr_month= poste('month');
        $attr_year= poste('year');
        $city = poste('city');
        $club = poste('club');

        $TcITY= !empty($city) ? '&city='.$city : '';
        $TClub= !empty($club) ? '&club='.$club : '';
       // $post_return = 'nomination-show-type='.$type.'&year='.$this->this_year.'&month='.$this->this_month.$TcITY.$TClub;

        $dop_post='';
            if (!empty($attr_month)&&!empty($attr_year)) $dop_post='&year='.$attr_year.'&month='.$attr_month;
        $submenu2 = array();
        if (empty($type) || 'pm'==$type)  $submenu2Temp['class'] = 'active_menu_turnirs'; else   $submenu2Temp['class'] = '';
        $submenu2Temp['name']='Прогрес місяця';
        $submenu2Temp['href']='#nomination-show-type=pm'.$dop_post.$TcITY.$TClub;
        $submenu2[]=$submenu2Temp;
        if (!empty($type) && 'bp'==$type)  $submenu2Temp['class'] = 'active_menu_turnirs '; else   $submenu2Temp['class'] = ' ';
        $submenu2Temp['name']='Найактивніший гравець';
        $submenu2Temp['href']='#nomination-show-type=bp'.$dop_post.$TcITY.$TClub;
        $submenu2[]=$submenu2Temp;

        /*   $sql = 'SELECT * FROM `'.T_ETAPS.'` where turnir_id='.poste('turnir_id');
           $etapsArr = db_list($sql);
           if (!empty($etapsArr))
           {
               $fir =1 ;
               foreach ($etapsArr as $val){
                   $submenu2Temp['name'] = $val['name_etap'];
                   if (empty($etap_id) && $fir==1)
                   {
                       $submenu2Temp['class'] = 'red_color';
                       $fir =0 ;
                   }
                   else
                       if (!empty($etap_id) && $val['id']==$etap_id)  $submenu2Temp['class'] = 'red_color'; else   $submenu2Temp['class'] = 'black_color';

                   $submenu2Temp['href'] = '#etapresult-show-etap_id='.$val['id'].'&turnir_id='.poste('turnir_id');
                   $submenu2[] =$submenu2Temp;
               }
           }*/
        if ($_SESSION['is_mobile'] ) {
            SystemClass::$menuTurnirs_module = $submenu2;
        }
    }

}
?>