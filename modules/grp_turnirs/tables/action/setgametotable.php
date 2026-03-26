<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class SetGameToTableAction extends ActionModule 
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
        $where_log = 'table_game=-1';
        write_log_reiting('tables_setGameToTable',$where_log,'select',0);

   
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
        $table_id = poste('table_id');  
      //  $etap_id = poste('etap_id');  
        $is_team_league = (int)db_field('SELECT l.is_team_league FROM `bs_turnirs` t LEFT JOIN `bs_leagues` l ON l.id=t.league_id WHERE t.id='.(int)$turnir_id.' LIMIT 1', 'is_team_league');
     
     /*
     and not exists(select * 
  from '.T_REITING.' r2  where  r.turnir_id='.$turnir_id.' and r2.pl_id_1>0 and r2.pl_id_2>0 and r2.set_1=0 and r2.set_2=0
and r2.table_game>0 and (r.pl_id_1=r2.pl_id_1 or r.pl_id_1=r2.pl_id_2 or r.pl_id_2=r2.pl_id_2 or 
r.pl_id_2=r2.pl_id_1) )
     */   
        // Исключаем командные игры (где оба игрока имеют is_team = 1)
        // Для столов должны показываться только игры между отдельными игроками
        $sql='
        select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,
  group_num, type_game, olimp16_num, etap_prim, r.match_id, r.etap_id, r.pl_id_1, r.pl_id_2,
  (select count(*) 
  from '.T_REITING.' r2  where  r2.turnir_id='.$turnir_id.' and r2.pl_id_1>0 and r2.pl_id_2>0 and r2.set_1=0 and r2.set_2=0
and r2.table_game>0 and (r.pl_id_1=r2.pl_id_1 or r.pl_id_1=r2.pl_id_2 or r.pl_id_2=r2.pl_id_2 or 
 r.pl_id_2=r2.pl_id_1) ) as ogid,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap,table_game      
  from '.T_REITING.' r  
  WHERE r.turnir_id='.$turnir_id.' 
  AND r.pl_id_1>0 
  AND r.pl_id_2>0 
  AND r.set_1=0 
  AND r.set_2=0 
  AND r.break_1=0 
  AND r.break_2=0
  AND r.table_game=0
  AND NOT EXISTS (
    -- Исключаем командные игры: где оба игрока имеют is_team = 1
    SELECT 1 FROM bs_players p1, bs_players p2
    WHERE p1.id = r.pl_id_1 AND p2.id = r.pl_id_2
    AND p1.is_team = 1 AND p2.is_team = 1
  )
order by r.id';
 $aGames =  db_list($sql);
  if (!empty($aGames) && $is_team_league) {
      foreach ($aGames as $idx => $game) {
          $team_id_1 = !empty($game['match_id']) && !empty($game['etap_id']) ? db_field('SELECT team_id FROM `bs_team_lineups` WHERE match_id="'.addslashes($game['match_id']).'" AND etap_id='.(int)$game['etap_id'].' AND player_id='.(int)$game['pl_id_1'].' LIMIT 1', 'team_id') : 0;
          if (empty($team_id_1)) {
              $team_id_1 = db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_1'].' LIMIT 1', 'team_id');
          }
         if (!empty($team_id_1)) {
             $team_name_1 = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.(int)$team_id_1, 'name');
             if (!empty($team_name_1)) {
                 $aGames[$idx]['name1'] = $game['name1'].' (<span style="color:#1e6bd6;">'.$team_name_1.'</span>)';
             }
         }

         $team_id_2 = !empty($game['match_id']) && !empty($game['etap_id']) ? db_field('SELECT team_id FROM `bs_team_lineups` WHERE match_id="'.addslashes($game['match_id']).'" AND etap_id='.(int)$game['etap_id'].' AND player_id='.(int)$game['pl_id_2'].' LIMIT 1', 'team_id') : 0;
         if (empty($team_id_2)) {
             $team_id_2 = db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_2'].' LIMIT 1', 'team_id');
         }
          if (!empty($team_id_2)) {
              $team_name_2 = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.(int)$team_id_2, 'name');
              if (!empty($team_name_2)) {
                  $aGames[$idx]['name2'] = $game['name2'].' (<span style="color:#1e6bd6;">'.$team_name_2.'</span>)';
              }
          }
      }
  }
 //s($sql);
    $content='<table cellpadding="0" cellspacing="1" class="table table-condensed bordered3 viborPlayerTable table-hover table-bordered    border-light-subtle" width="95%" border="0" id="parts_table_">
         
         <thead class="th_color_rose">
  
    <tr>      
         <th style="text-align: center;width:200px">
        <span >ПІБ гравця 1</span>
       </th>
       <th style="text-align: center;width:200px">
        <span >ПІБ гравця 2</span>
       </th>
       <th style="text-align: center;width:200px">
       <span >Етап - стадія</span>
       </th>
       <th style="text-align: center;width:50px">
       <span>Вибиріть гру<span></span></span></th>
     </tr>
     </thead>
     <tbody>
     ';
 foreach ($aGames as $game)
 {
    $text_ogid = $game['ogid'] > 0 ? '<span class="vert_red">в очікуванні...</span>'  : '<a  href="#" 
       data-bs-dismiss="modal"  class="setgemtotable" post_string="&turnir_id='.$turnir_id.'&newgame='.$game['id'].'&table_id='.$table_id.'" 
          module="tables" action="settablegame"  
         id="element_vibor_id_'.$game['id'].'">Розпочати гру</a>';
     $olimp16_num=!empty($game['olimp16_num']) ? '('.$game['olimp16_num'].')' : ''; 
     $content.='  <tr>
       <td  class="align-middle" class="ms-2 " >
            <span class="align-middle">'.$game['name1'].'</span>
       </td>
        <td class="align-middle" style="padding-left:5px;" class="ms-2  " >
            <span class="align-middle">'.$game['name2'].'</span>
       </td>
         <td class="align-middle" style="padding-left:5px;" class="editTd " >
            <span class="align-middle">'.$game['name_etap'].'::'.$game['etap_prim'].' '.$olimp16_num.'</span>
       </td>
       </td>
       <td align="center" class="align-middle">
        '.$text_ogid.'
       
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
