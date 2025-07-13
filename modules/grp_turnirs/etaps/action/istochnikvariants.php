<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class IstochnikVariantsAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
    function init ()
    {
     //    s('tytt2233');
   // s($this->module);
  //  s($this->action);

  //  $pp =SystemClass::getAPost();
  //  s('$_POST');    
  //  s($_POST); 
    $turnir_id = poste('turnir_id');   
    $sql='';
      $txt_sql= !empty($this->id) ? 'id<>'.$this->id.' and ' : '';
      $sql = 'SELECT * FROM `'.T_ETAPS.'` where '.$txt_sql.'  turnir_id='.$turnir_id;
  
    
    $this->show($sql);
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

     function show($sql)
    { //  SystemClass::setAction('anyaction');
      //  SystemClass::setModule('turnirsplayers');
      //    $post_return = 'turnirsplayers|list|wintype=1&turnir_id='.$this->id;
      //  SystemClass::setPost_return($post_return);
    //  s($sql);
     //  $this->Java_script='reload_page_();';
    //   parent::list_show($sql);
        
        // SystemClass::setJava_script($this->Java_script);
     
        $objList = new ListTable();
          $objList->sql_list($sql);
            $objList->shablon_list_header();
            $objList->list_header();
            
           // $this->list_header_filter();
//s($this->subMenu);
       $aFirst = array('-1'=> array('id'=>0, 'name_etap'=>'Гравці турніру'));
        
          $aData =  $objList->getaData();
          $aData = array_merge($aFirst,$aData);
          $objList->setaData($aData);
            $objList->data_list();
         
          //s($aData);
          
             $objList->Java_script.=' fancyImageShow();';

            $objList->content .= '</tr></table></div>';
            ActionModule::setContent($objList->getContent());
      // $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }

}