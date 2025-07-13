<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class AvtorisAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    function init ()
    {
  $this->getAvtoris();
    
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
    function getAvtoris()
    {
    
        
 //s($sql);
    $content='
<div class="form-signin">
 <form id="form_edit_form" method="post"  >
<div class="form-floating">
      <input type="text" class="form-control" id="username" name="username" value="" placeholder="Логін">
      <label for="username">Логін</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" id="password" placeholder="Пароль" name="password" value="" onkeydown="javascript:if(event.keyCode == 13) adminEnter(1);">
      <label for="password">Пароль</label>
    </div>
    <button class="w-100 btn btn-lg btn-primary ajax_send" data-bs-dismiss="modal"  type="button" module="players" befor="adminEnter" post_string="login=1" >Вхід</button>
 </form>
 </div>

        
      ';

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