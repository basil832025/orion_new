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
  $this->set_result_table();
     $this->Java_script.=' setResultWin();';
   
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
    function set_result_table()
    {
        $turnir_id = poste('turnir_id');  
        $table_id = poste('table_id');
        // узнаем настройки турнира
        $sql='select * from '.T_TURNIRS.' where id='.$turnir_id;
        $aOptionTurnir=db_row($sql);
      $newgame=poste('newgame');
        $where_log = 'table_game=-2';
        write_log_reiting('tables_setResultWin',$where_log,'click',$newgame);

        //  $etap_id = poste('etap_id');
        $sql='select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,pl_id_1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,pl_id_2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game,no_send,break_1,break_2, r.match_id, r.etap_id,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  r.turnir_id='.$turnir_id.' and pl_id_1>0 and pl_id_2>0  and id='.$newgame;
 //s($sql); and set_1=0 and   set_2=0 and r.table_game >0
    $aResults = db_row($sql);
   // отоброжать шаблон до 3з побед по умолчанию если не вібран не один шаблон віводим до3х побед
    //if (!empty($aOptionTurnir['is_shablon3']))
        $shablon =' <table class="big-table"><tr><td> </td><td><div class="variants_res vibres" rahun="30">3 : 0</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="31">3 : 1</div></td><td></td></tr>
        <tr><td></td><td><div class="variants_res vibres" rahun="32">3 : 2</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="23">2 : 3</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="13">1 : 3</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="03">0 : 3</div></td><td></td></tr></table>
       ';
        if (!empty($aOptionTurnir['is_shablon3']) && !empty($aOptionTurnir['is_shablon2']))
            $shablon =' <table class="big-table"><tr><td><div class="variants_res"><span class="vibres" rahun="30">3 : 0</span></div></td><td></td></tr>
        <tr><td><div class="variants_res vibres" rahun="31">3 : 1</div></td><td></td></tr>
        <tr><td><div class="variants_res vibres" rahun="32">3 : 2</div> </td><td><div class="variants_res vibres" rahun="20">2 : 0</div></td></tr>
        <tr><td><div class="variants_res vibres" rahun="23">2 : 3</div> </td><td><div class="variants_res vibres" rahun="21">2 : 1</div></td></tr>
        <tr><td><div class="variants_res vibres" rahun="13">1 : 3</div> </td><td><div class="variants_res vibres" rahun="12">1 : 2</div></td></tr>
        <tr><td><div class="variants_res vibres" rahun="03">0 : 3</div> </td><td><div class="variants_res vibres" rahun="02">0 : 2</div></td></tr>
       </table>';
        else  if (!empty($aOptionTurnir['is_shablon2']))
            $shablon ='<table class="big-table"> <tr><td> </td><td></td><td></td></tr>
        <tr><td></td>
        <td></td><td></td></tr>
        <tr><td></td><td><div class="variants_res vibres" rahun="20">2 : 0</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="21">2 : 1</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="12">1 : 2</div></td><td></td></tr>
        <tr><td> </td><td><div class="variants_res vibres" rahun="02">0 : 2</div></td><td></td></tr>
       </table>';

        //s($sql);
    //    s($_SESSION);
     if  ( !empty($_SESSION['is_mobile'])) {
         $content='
      <form id="form_edit_form" action="?" method="post" enctype="multipart/form-data"> 
    
     <div class="container">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="pleyerResult pleyerResult_right" id="player1"><span >'.$aResults['name1'].'</span></div>
                <div class="variants_res_vidm"><span class="vibres vibres_vidm" rahun="LW">Гравець 1 відмовився</span></div>
             </div>
     
       
            <div class="col-6">
                     <div class="pleyerResult pleyerResult_left" id="player2"><span >'.$aResults['name2'].'</span></div>
                     <div class="variants_res_vidm"><span class="vibres vibres_vidm" rahun="WL">Гравець 2 відмовився</span></div>
            </div>
        </div> 
        <div class="row justify-content-center">
               <div class="col text-center">
          <input class="text-field__input" maxlength="1" size="1" data-min="0" data-max="4" type="text" name="form[set_1]" id="res1" placeholder="0" value="">
           
         <input class="text-field__input" maxlength="1" size="1" data-min="0" data-max="4" type="text"  name="form[set_2]" id="res2" placeholder="0" value="">
         <input id="break_1" type="hidden"  name="form[break_1]"  value="'.$aResults['break_1'].'">
         <input id="break_2" type="hidden"  name="form[break_2]"  value="'.$aResults['break_2'].'">
         <input id="no_send" type="hidden"  name="form[no_send]"  value="'.$aResults['no_send'].'">
         <input id="pl_id_1" type="hidden"  name="form[pl_id_1]"  value="'.$aResults['pl_id_1'].'">
         <input id="pl_id_2" type="hidden"  name="form[pl_id_2]"  value="'.$aResults['pl_id_2'].'">
         <input id="id" type="hidden"  name="id"  value="'.$newgame.'">
       

            </div>
</div>
        <div class="row justify-content-center">
                 <div class="col ms-auto text-center">
                       '.$shablon.'
     
                </div>
        </div>
     <div class="row justify-content-center mt-4">
                <div class="col"><input data-bs-dismiss="modal" type="button" 
        class="button4 btn_finish_game" value="ЗАВЕРШИТИ МАТЧ" id="sendFinishGame" table_id="'.$table_id.'" gameid="'.$newgame.'" post_string="&turnir_id='.$turnir_id.'&table_id='.$table_id.'" 
          module="reiting" action="edit_ok"></div></div>
           <div class="row justify-content-center mt-2">
                <div class="col">
                  <input  type="button" data-bs-dismiss="modal" value="Відмінити гру" data-bs-dismiss="modal" id="cancelGame" class="cancelgame button13" gameid="'.$newgame.'" table_id="'.$table_id.'"
        post_string="&turnir_id='.$turnir_id.'&newgame='.$newgame.'&table_id='.$table_id.'" >
            </div></div>
        
    
    </div>  
      </form> 
      ';
     }else {
         $content = '
      <form id="form_edit_form" action="?" method="post" enctype="multipart/form-data"> 
    
     <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 ps-1">
                <div class="pleyerResult pleyerResult_right" id="player1"><span >' . $aResults['name1'] . '</span></div>
                <div class="variants_res_vidm"><span class="vibres vibres_vidm" rahun="LW">Гравець 1 відмовився</span></div>
             </div>
            <div class="col-md-1 me-2 ps-1">
          <input class="text-field__input" maxlength="1" size="1" data-min="0" data-max="4" type="text" name="form[set_1]" id="res1" placeholder="0" value="">
            </div>
            <div class="col-md-1 m-0 ps-1">
         <input class="text-field__input" maxlength="1" size="1" data-min="0" data-max="4" type="text"  name="form[set_2]" id="res2" placeholder="0" value="">
         <input id="break_1" type="hidden"  name="form[break_1]"  value="' . $aResults['break_1'] . '">
         <input id="break_2" type="hidden"  name="form[break_2]"  value="' . $aResults['break_2'] . '">
         <input id="no_send" type="hidden"  name="form[no_send]"  value="' . $aResults['no_send'] . '">
         <input id="pl_id_1" type="hidden"  name="form[pl_id_1]"  value="' . $aResults['pl_id_1'] . '">
         <input id="pl_id_2" type="hidden"  name="form[pl_id_2]"  value="' . $aResults['pl_id_2'] . '">
         <input id="id" type="hidden"  name="id"  value="' . $newgame . '">
       

            </div>
       
            <div class="col-md-5 ps-4">
                     <div class="pleyerResult pleyerResult_left" id="player2"><span >' . $aResults['name2'] . '</span></div>
                     <div class="variants_res_vidm"><span class="vibres vibres_vidm" rahun="WL">Гравець 2 відмовився</span></div>
            </div>
        </div> 
        <div class="row justify-content-center">
                 <div class="col-md-7 text-center">
                       ' . $shablon . '
     
                </div>
        </div>
     <div class="row justify-content-center mt-4">
                <div class="col"><input data-bs-dismiss="modal" type="button" 
        class="button4 btn_finish_game" value="ЗАВЕРШИТИ МАТЧ" id="sendFinishGame" table_id="' . $table_id . '" gameid="' . $newgame . '" post_string="&turnir_id=' . $turnir_id . '&table_id=' . $table_id . '" 
          module="reiting" action="edit_ok"></div>
                <div class="col">
                  <input  type="button" data-bs-dismiss="modal" value="Відмінити гру" data-bs-dismiss="modal" id="cancelGame" class="cancelgame button13" gameid="' . $newgame . '" table_id="' . $table_id . '"
        post_string="&turnir_id=' . $turnir_id . '&newgame=' . $newgame . '&table_id=' . $table_id . '" >
            </div>
        </div>
    
    </div>  
      </form> 
      ';
     }
 $content.='';
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
