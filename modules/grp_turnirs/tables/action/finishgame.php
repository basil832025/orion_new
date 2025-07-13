<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class FinishGameAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
  protected $aTurnVariants=array(); // 
 // protected $aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
    function init ()
    {
     $turnir_id=poste('turnir_id');
      $newgame=poste('newgame');
      $table_id=poste('table_id');
      $this->form= SystemClass::getAFormPost();
    //s('$this->form');
  //  s($this->form);
   // s($etap_id);
  // $sql = 'update '.T_REITING.'  set table_game='.$table_id.', start_game="0", table_game=0 where id='.$newgame;
////   s($sql);
  // db_query($sql);
    $this->show($turnir_id);
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
    
     function show($turnir_id)
    {   //s($_POST);
     // s($this->id);
   //  s('ddd='.$turnir_id);
     //  s('$etap_id11='.$etap_id);
        SystemClass::setAction('anyaction');
        SystemClass::setModule('tables');
        //   parent::list_show();
          $post_return = 'tables|show|turnir_id='.$turnir_id;
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