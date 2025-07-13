<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class SetResultWinAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
  protected $aTurnVariants=array(); // 
  protected $aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
    function init ()
    {
     //    s('tytt2233');
   // s($this->module);
  //  s($this->action);
  /*  s($this->id);
   s($this->aParent); 
    s($this->table_module); 
    s($this->type_module);
    s($this->aEditField );
    s($_POST);*/
      
       $_SESSION['turnirs']['sort']='';
  $_SESSION['turnirs']['sort_type']='';
  $this->get_games_Active();
    $content='<table cellpadding="0" cellspacing="1" class="bordered" width="100%" border="0" id="parts_table_">
         <tbody>
    <tr>      
         <th style="text-align: center;width:250px">
        <span >ФИО Игрока 1</span>
       </th>
       <th style="text-align: center;width:50px">
       <span >Рейтинг</span>
       </th>
       <th style="text-align: center;width:50px">
       <span>Выберите<span></span></span></th>
     </tr>
     <tr>
       <td style="padding-left:5px;" class="editTd " id="editTdElem--name--1105">
            <span id="dataName--name--1105">Шемець Євген</span>
       </td>
       <td style="padding-left:5px;" class="editTd " id="editTdElem--reiting--1105">
            <span id="dataName--reiting--1105">0.00</span>
       </td>
       <td align="center">
            <a href="javascript:parent.jQuery.fancybox.close();" 
            class="element_vibor" field="player_id" result="Шемець Євген" id="element_vibor_id_1105">Выбрать</a>
       </td>
    </tr>
       
       
    </tbody></table>';
   
    $this->show();
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
    function get_games_Active()
    {
        $turnir_id = poste('turnir_id');  
      //  $etap_id = poste('etap_id');  
        
        $sql='select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,
  group_num, type_game, olimp16_num, etap_prim,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  r.turnir_id='.$turnir_id.' and pl_id_1>0 and pl_id_2>0 and set_1=0 and set_2=0';
 $aGames =  db_list($sql);
    $content='<table cellpadding="0" cellspacing="1" class="bordered" width="100%" border="0" id="parts_table_">
         <tbody>
    <tr>      
         <th style="text-align: center;width:200px">
        <span >ФИО Игрока 1</span>
       </th>
       <th style="text-align: center;width:200px">
        <span >ФИО Игрока 2</span>
       </th>
       <th style="text-align: center;width:200px">
       <span >Этап - стадия</span>
       </th>
       <th style="text-align: center;width:50px">
       <span>Выбирите игру<span></span></span></th>
     </tr>';
 foreach ($aGames as $game)
 {
     $olimp16_num=!empty($game['olimp16_num']) ? '('.$game['olimp16_num'].')' : ''; 
     $content.='  <tr>
       <td style="padding-left:5px;" class="editTd " >
            <span >'.$game['name1'].'</span>
       </td>
        <td style="padding-left:5px;" class="editTd " >
            <span >'.$game['name2'].'</span>
       </td>
         <td style="padding-left:5px;" class="editTd " >
            <span >'.$game['name_etap'].'::'.$game['etap_prim'].' '.$olimp16_num.'</span>
       </td>
       </td>
       <td align="center">
            <a href="javascript:parent.jQuery.fancybox.close();" 
            class="element_vibor" field="player_id" result="Шемець Євген" id="element_vibor_id_'.$game['id'].'">Начать игру</a>
       </td>
    </tr>';   
 }
 $content.='</tbody></table>';
  $this->content=$content;
    }
     function show()
    { //  SystemClass::setAction('anyaction');
      //  SystemClass::setModule('turnirsplayers');
      //    $post_return = 'turnirsplayers|list|wintype=1&turnir_id='.$this->id;
      //  SystemClass::setPost_return($post_return);
    //  s($sql);
     //  $this->Java_script='reload_page_();';
     //  parent::list_show($sql);
        
        // SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }

}