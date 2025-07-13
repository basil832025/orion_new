<?php
//s('turnirsUP');
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class LeaguesObject extends ObjectRT
{
    //$this-> = 'tree';
    function init()
    {
        $this->addFTL(array('name' => '№', 'type' => 'number', 'width' => '20', 'width_mob' => '22'));
        $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
        $this->addFTL(array('name' => 'Дата стоврення', 'name_mob' => 'Дата<br>стоврення', 'type' => 'date', 'width' => '50', 'name_field' => 'dat', 'no_slash' => 1, 'width_mob' => '49'));
        $this->addFTL(array('name' => 'Назва ліги', 'classAlign' => 'text-start', 'width' => '600',
            'type'=>'text', 'type' => 'get_func','function' => 'get_name_league',
            'name_field' => 'name', 'no_slash' => 1));
        $this->addFTL(array('name'=>'Статус','name_field'=>'status','bd_field'=>'status','width'=>'80','type'=>'prostspr'));

        $this->addFF(array('name'=>'Назва ліги','name_field'=>'name','required'=>'Назва ліги обов"язкова (мінімум 3 символа)', 'pattern'=>'.{3,}' ));
        $this->addFF(array('name'=>'Дата створення ліги','name_field'=>'dat','type'=>'date','required'=>'Дата ліги объязательна'));
        $this->addFF(array('name'=>'Статус','name_field'=>'status','type'=>'ProstSpr', 'id_spis'=>'7', 'bd_field'=>'status'));
        $this->setTableModule('bs_Leagues');
        self::$nameZ='';
        self::$nameZList='';
        // self::$nameZList='<span class="zzagl">Турніри</span>';
        self::$nameZList='Турніри';
        self::$nameZEdit='::Редагування турніру';
        if ($_SESSION['gt']['user_rule']<10)
            self::$submenu_list =array(
               // 'help' => array('menu_name'=>'Перерахувати штраф рейтингу','module' => 'turnirs', 'class' =>'mess_shtraph', 'mess' =>'Ви дійсно хочите розрахувати систему штрафів за минулий місяць?', 'action' => 'raschet_shtraph'),
                'back' => array('module' => 'Leagues', 'action' => 'list'),
            );
      //  self::InitLeaguesMenu();
        self::$submenu_edit = array(
            'back' => array('module' => 'Leagues', 'action' => 'list'),
            'save' => array('module' => 'Leagues', 'action' => 'edit_ok'),
        );
    }
}
function get_name_league($field,$id)
{
    $name='';
    $sql='select (SELECT teg FROM `bs_spr-spis-values` WHERE  STATUS=id) AS status,name    
  from bs_leagues r  where  r.id='.$id;
    $vData = db_row($sql);
   // $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
    // $date = new DateTimeImmutable($vData[$field]);
    if ($vData['status']=='finish' ){
        $class='blac_color';
        $title='Ліга завершена';
    }elseif($vData['status']=='active' ){
        $class='coral_color';
        $title='Ліга розпочата';
    }else{
        $class= 'green_color';
        $title='Ліна не розпочата';
    }
    if ($_SESSION['is_mobile']) {$class.=' f12 fw700 nopodch';};

     $turnirName=$vData['name'];

        $name ='<span  id="catalog_name_id_29" data-bs-toggle="tooltip" title="'.$title.'"><a href="#turnirs-list-league_id='.$id.'" class="'.$class.' ajax_send">'.$turnirName.'</a></span> ';

    return $name;
}