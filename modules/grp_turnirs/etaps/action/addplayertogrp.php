<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class AddPlayerToGrpAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
  protected $aTurnVariants=array(); // 
 // protected $aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
    function init ()
    {
     $turnir_id=poste('turnir_id');
      $etap_id=poste('etap_id');
      $grp=poste('grp');
      $newplayer=poste('newplayer');
   // s($grp);
   // s($etap_id);
    $this->raschet($turnir_id,$etap_id,$grp,$newplayer);
    $this->show($turnir_id,$etap_id);
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
    function raschet($turnir_id,$etap_id,$grp,$newplayer)
    {
       $sql='select count(*) as cn FROM `'.T_ETAPS_PLAYER_MESTA.'` tp where etap_id='.$etap_id.' and `groups`='.$grp.' ';
       $cn_v_grp = db_field($sql,'cn');
       $cn_v_grp++;
       $sql = 'update  `'.T_ETAPS_PLAYER_MESTA.'` set `groups`='.$grp.', grp_num='.$cn_v_grp.' where id='.$newplayer;
       db_query($sql);
       $sql = 'update  `'.T_ETAPS.'` set cnt_people=cnt_people+1 where id='.$etap_id;
       db_query($sql);
        $sql = 'select  * from '.T_ETAPS.' t where  t.id='.$etap_id.'  ';
        $form = db_row($sql);
      //  s($form);
       // сохраняем старые результаты и обнуляем этап потом удалим
       $sql = 'update '.T_REITING.' set turnir_id_old='.$etap_id.', etap_id=0 where etap_id='.$etap_id.' ' ;
       db_query($sql);
//s($sql);
       setGroupsEtap($form,$turnir_id,$etap_id);
       // теперь восстановим результаты
        $sql = 'select  * from '.T_REITING.' t where  turnir_id_old='.$etap_id.' and (COALESCE(lose_player,0)>0 or table_game>0 )';
        $aOldRes = db_list($sql);
   //     s($aOldRes);
        $cn= count($aOldRes);
        if ($cn>0)
        { 
   
        foreach  ($aOldRes as $result)
        { $set = 'diff_1="'.$result['diff_1'].'",diff_2="'.$result['diff_2'].'",
        set_1='.$result['set_1'].',set_2='.$result['set_2'].',break_1='.$result['break_1'].',break_2='.$result['break_2'].',
        win_player="'.$result['win_player'].'",lose_player="'.$result['lose_player'].'", no_send='.$result['no_send'].', 
        table_game="'.$result['table_game'].'",start_game="'.$result['start_game'].'",end_game="'.$result['end_game'].'"';
            $sql= 'update '.T_REITING.' set '.$set.' where type_game=1 and etap_id='.$etap_id.' 
        and ((pl_id_1='.$result['pl_id_1'].' and pl_id_2='.$result['pl_id_2'].') or (pl_id_1='.$result['pl_id_2'].'
         and pl_id_2='.$result['pl_id_1'].')) ';
       db_query($sql);
//s($sql);
    }
 }
        // удаляем старый результаты
        $sql = 'delete from '.T_REITING.'  where etap_id=0 and turnir_id_old='.$etap_id;
        db_query($sql);
    }
     function show($turnir_id,$etap_id)
    {   //s($_POST);
     // s($this->id);
     //  s('$etap_id11='.$etap_id);
        SystemClass::setAction('anyaction');
        SystemClass::setModule('etapresult');
        //   parent::list_show();
          $post_return = 'etapresult-show-turnir_id='.$turnir_id.'&etap_id='.$etap_id;
        SystemClass::setPost_return($post_return);
    //  s($sql);
     //  $this->Java_script='reload_page_();';
    
        
        // SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }

}