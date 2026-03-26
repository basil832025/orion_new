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
      $turnir_id = poste('turnir_id');
        $league_id = poste('league_id');
        $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
    // mess('HELLO','tables','show',0,10);
   //  redirect_Ajax('tables','tables|show|turnir_id='.$turnir_id,'STATUSok');
      
      $sql = 'select tables,dat,selected_tables from '.T_TURNIRS.' t where t.id='.$turnir_id;
$aTables = db_row($sql);
$tables_cnt = $aTables['tables'];
$dat = $date = date('Y-m-d');
        $name_turnir =db_row('select name,dat  from `' . T_TURNIRS .
            '` where id=' . $turnir_id);
        $turnir_name = htmlspecialchars(stripslashes((string)$name_turnir['name']), ENT_QUOTES, 'UTF-8');
        $date = new DateTimeImmutable($name_turnir['dat']);
        $tdat = $date->format('d.m.Y');
   //     self::$nameZList='<div class="poriv_zag"> Гравці турніру (статистика гравців) "' . $name_turnir['name'] . '" (' . $tdat . ') </div>';

        $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$turnir_id;
        $vData = db_row($sql);
        $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$turnir_id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
        if ($vData['cnt_g']>0  ){
            $title='';
        }elseif($Work_turnir>0){

            $title=' - в процесі';
        }else{

            $title=' - не розпочато';
        }
        $show_zag_left = '';
        if ($_SESSION['is_mobile'] )
            $nameZ='<div class="compare_zagl">Столи турніру "'.$turnir_name.' ('.$tdat.$title. ')"</div>';
        else
            $nameZ='<div class="poriv_zag">Столи турніру "'.$turnir_name.'" ('.$tdat.$title. ')</div>';
        SystemClass::setZaglModule($nameZ);
    // выводим таблицы
    $this->content = getTablesAll($tables_cnt,$turnir_id,$dat,false,[],$aTables['selected_tables']);
        if ($_SESSION['is_mobile'] ){

         }else{
            $show_zag_left='show_zag_center();show_zag_left_big("#turnirs-list'.$menu_league.'");';
            }
     $this->Java_script.=' getTables();show_zag_left("#turnirs-list'.$menu_league.'");'.$show_zag_left;
         SystemClass::setJava_script($this->Java_script);
    

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
       $this->Java_script.=' getTables();';
          SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }
}
//echo 'dsjksd'; 
?>
