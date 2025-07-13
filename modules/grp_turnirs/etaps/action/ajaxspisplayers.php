<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class AjaxSpisPlayersAction extends ActionModule
{  protected  $content = '';
    protected  $subMenu = array();
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    protected  $cnt_players=0; // количество игроков на турнире
    protected $aTurnVariants=array(); //
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
        $this->get_spis_players();


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
    function get_spis_players()
    {
        $turnir_id = poste('turnir_id');
        $etap_id = poste('etap_id');
        $q= poste('q');
        $sql_q = !empty($q) && $q!='undefined' ? ' and name like "'.$q.'%" ' : '';

        $sql ='SELECT 
( case when p.reiting>0 then p.reiting else p.start_reiting end ) as beg_reit,
p.reiting_ukraine,p.name,city,god_rogd, player_id
 FROM bs_turnirplayers tp,'.T_PLAYERS.' p  where  turnir_id='.$turnir_id.'  and p.id=tp.player_id
 '.$sql_q.'
ORDER BY reiting_ukraine desc, beg_reit desc';
        $aPlayers_ALL = db_list($sql);
    //   s($sql);
    $json = ($aPlayers_ALL);
   // s($json);
        $this->content=$json;
        $_SESSION['JAVA_SCRIPT']='json';
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