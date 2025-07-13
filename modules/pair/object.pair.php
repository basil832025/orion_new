<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class PairObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {//$_SESSION['gt']['user_rule']s($_SESSION['gt']['user_rule']);
   // $this->addFTL(array('name'=>'№ заказа','type'=>'text','oper'=>'edit','width'=>'25','name_field'=>'player_id','bd_field'=>'player_id')); 
      $fio_search = poste('fio_search');

      self::$table_class='table_mob_turn_pair';
      $action=SystemClass::getAction();
     if (empty($fio_search) && $action=='list')
          $_SESSION['MESSAGE_AJAX']='<div class="input__wrapper"><svg class="input__icon_player"><use xlink:href="#poisk"></use></svg><input type="text" class="form-control" placeholder="Пошук пари" id="search_field_players" style="margin-left: 20px; width:425px;" speeds="0"    value="'.$fio_search.'"></div> ';


    $this->addFTL(array('name'=>'&nbsp;&nbsp;№&nbsp;&nbsp;','type'=>'number','width'=>'5'));
   
    $this->addFTL(array('name'=>'Редагу-<br />вати','type'=>'edit','width'=>'40'));
    $this->addFTL(array('name'=>'ПІБ пари','name_field'=>'name','bd_field'=>'name','filter'=>1,'classAlign' => 'text-start fw700'));
 //   $this->addFTL(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','width'=>'100','filter'=>1,'speedsearch'=>5));
 //  $this->addFTL(array('name'=>'Пол','name_field'=>'sex','bd_field'=>'sex','width'=>'30'));
   $this->addFTL(array('name'=>'К-ть ігор','name_mob'=>'К-ть<br />ігор','name_field'=>'cnt_games','bd_field'=>'cnt_games','width'=>'85'));
   $this->addFTL(array('name'=>'К-ть перемог','name_mob'=>'К-ть<br />пере<br>мог','name_field'=>'cnt_wins','bd_field'=>'cnt_wins','width'=>'80'));
   $this->addFTL(array('name'=>'К-сть пораз.','name_mob'=>'К-ть<br />пора<br>зок','name_field'=>'cnt_lose','bd_field'=>'cnt_lose','width'=>'80'));
   $this->addFTL(array('name'=>'% перемог','name_mob'=>'%<br />пере<br>мог','name_field'=>'proc_wins','bd_field'=>'proc_wins','width'=>'80'));
   $this->addFTL(array('name'=>'Р-нг мін','name_mob'=>'Р-нг<br />мін','name_field'=>'reiting_min','bd_field'=>'reiting_min','width'=>'80'));
   $this->addFTL(array('name'=>'Р-нг макс','name_mob'=>'Р-нг<br />макс','name_field'=>'reiting_max','bd_field'=>'reiting_max','width'=>'80'));
   $this->addFTL(array('name'=>'Р-нг AVG','name_mob'=>'Р-нг<br />AVG','name_field'=>'reiting_avg','bd_field'=>'reiting_avg','width'=>'80'));
   $this->addFTL(array('name'=>'К-ть<br />турнірів','name_mob'=>'К-ть<br />турні<br>рів','name_field'=>'cnt_turnirs','bd_field'=>'cnt_turnirs','width'=>'80'));
 //$this->addFTL(array('name'=>'Удалить','type'=>'delete','width'=>'40','name_field'=>'name')); 
 //   $this->addFTL(array('name'=>'Год<br /> рождения','name_field'=>'god_rogd','bd_field'=>'god_rogd','width'=>'30','filter'=>1));
  //  $this->addFTL(array('name'=>'Дата<br /> регистрации','name_field'=>'dat','bd_field'=>'dat','width'=>'70'));
    $this->addFTL(array('name'=>'Рейтинг<br /> ФНТУ (сума)','name_mob'=>'Р-нг<br />ФНТУ<br>(сума)','name_field'=>'reiting_ukraine','bd_field'=>'reiting_ukraine','width'=>'80'));
    $this->addFTL(array('name'=>'Стартовий<br />рейтинг','name_mob'=>'Старто<br />вий<br>р-нг','name_field'=>'start_reiting','bd_field'=>'start_reiting','width'=>'80'));
    $this->addFTL(array('name'=>'Рейтинг<br /> клубу','name_mob'=>'Р-нг<br />клубу','name_field'=>'reiting','bd_field'=>'reiting','width'=>'80'));
  //  $this->addFTL(array('name'=>'Подв.<br />оплаты','type'=>'plus_minus','name_field'=>'podtv','width'=>'80'));


$this->addFF(array('name'=>'ПІБ пари','name_field'=>'name','bd_field'=>'name','required'=>'ПІБ пари обов"язкове'));
//$this->addFF(array('name'=>'ФИО игрока Ligas','name_field'=>'name_ligas','bd_field'=>'name_ligas'));
//$this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));
//$this->addFF(array('name'=>'Год рождения','name_field'=>'god_rogd','bd_field'=>'god_rogd'));
//$this->addFF(array('name'=>'Город','name_field'=>'city','bd_field'=>'city'));
$this->addFF(array('name'=>'Дата реєстрації','name_field'=>'dat','type'=>'date','bd_field'=>'dat'));
$this->addFF(array('name'=>'Рейтинг ФНТУ','name_field'=>'reiting_ukraine','bd_field'=>'reiting_ukraine','required_custom'=>'onlyNumber'));
$this->addFF(array('name'=>'Рейтинг стартовий','name_field'=>'start_reiting','bd_field'=>'start_reiting','required_custom'=>'onlyNumber'));
$this->addFF(array('name'=>'Рейтинг клубу','name_field'=>'reiting','bd_field'=>'reiting', 'required_custom'=>'onlyNumber'));
//$this->addFF(array('name'=>'ID игрока Ligas Украины','name_field'=>'id_reiting','bd_field'=>'id_reiting'));
//$this->addFF(array('name'=>'Оплачен членский взнос','type'=>'Checkbox','name_field'=>'is_opl_reiting','bd_field'=>'is_opl_reiting'));
$this->addFF(array('name'=>'НЕ АКТИНВИЙ ГРАВЕЦЬ','type'=>'Checkbox','name_field'=>'not_use','bd_field'=>'not_use'));
//$this->addFF(array('name'=>'Пол m/f','name_field'=>'sex','bd_field'=>'sex','size'=>'1','maxlength'=>1));
$this->addFF(array('name'=>'Примітка','name_field'=>'prim','bd_field'=>'prim'));
//unset($_SESSION['players']['where']);
if  (empty($_SESSION['pair']['sort']))  $_SESSION['pair']['sort']='reiting';
if  (empty($_SESSION['pair']['sort_type']))  $_SESSION['pair']['sort_type']='desc';
      $strSearch='';
      if (!empty($fio_search))
      {
          //s($fio_search);
          $strSearch = ' AND name LIKE "%'.$fio_search.'%"';
          // $dop_where = $strSearch;
      }
       if ($_SESSION['gt']['user_rule']<>1)   $_SESSION['pair']['where']=' and ispara=1 and not_use=0 '.$strSearch;
      else $_SESSION['pair']['where']=' and ispara=1 '.$strSearch;

  $this->setTableModule(T_PLAYERS);
    
  self::$nameZ='';
 self::$nameZList='Пари';
 self::$nameZEdit='Редагування пари';
 
  self::$submenu_list =array( 
   //  'back' => array('module' => 'turnirs', 'action' => 'list'),
    );
 /*self::$submenu_list =array( 
  // 'filter' => array('module' => 'players'),
     );*/
   
  }
}