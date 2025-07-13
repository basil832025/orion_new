<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class AddmAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
   
    function init ()
    {
   /* s($this->module);
    s($this->action);
    s($this->id);
    s($this->aParent); 
    s($this->table_module); 
    s($this->type_module);
    s($this->aEditField );*/
  //  s($_POST);
    $sql = 'select id,name,reiting FROM '.T_PLAYERS.' p where 
not EXISTS(select * from '.T_TURNIR_PLAYERS.' t where t.player_id=p.id and t.turnir_id='.$this->id.')';
    $this->list_show($sql);
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
     function list_show($sql)
    { //  SystemClass::setAction('anyaction');
        SystemClass::setModule('turnirsplayers');
          $post_return = 'turnirsplayers|list|wintype=1&turnir_id='.$this->id;
        SystemClass::setPost_return($post_return);
      
     //  $this->Java_script='reload_page_();';
       parent::list_show($sql);
        
        // SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }

}