<?php
class parametresObject extends ObjectRT
{
    //$this-> = 'tree';
    function init ()
    {

        // $this->postButton= !empty($id_spis)  ? '&id_spis='.$id_spis : '';
        //  s('$postButton='.$postButton);
        /************************************Информация по ребенку таблицах и полях*/
        $this->setTableModule('bs_parametres');
        $this->addFTL(['name'=>'№','type'=>'number', 'width'=>'20']);
        $this->addFTL(['name'=>'kod',
            'type'=>'field',
            'name_field'=>'kod',
            'width'=>'20']);
        $this->addFTL(['name'=>'Редагу-<br />вати',
            'type'=>'edit',
             'width'=>'40']);
        $this->addFTL(['name'=>'Назва параметру  ',
            'type'=>'field',
            'width'=>'300',
            'oper'=>'edit',
            //'name_parent'=>'id_spis',
            //  'lang_type'=>LANG, // данный параметр создается для тех полей которые зеркальные с языком
            'name_field'=>'name']);
        $this->addFTL(array('name' => 'Значення', 'type' => 'field', 'width' => '90', 'name_field' => 'value'));

        $this->addFTL(['name'=>'Видалити',
            'width'=>'60',
            'type'=>'delete',
            'name_field'=>'value']);

        // name - название поля рус, name_field  -  в БД и формы назв поля
        //type - тип вывода поля, (по умолч text)
        //width_left_col - ширина поля левой колнки,(по умолч 280)
        //align_left_col -выравнивание левой колнки, (по умолч right)
        // active - выводить ли поле, (по умолч 1)
        //readonly - 1 readonly, (по умолч 0)
        //rows- для редактора строчек (по умолч 15)   cols - для редактора колонок (по умолч 80)
        //  required - поле обязательное и текст если не правильно заполнено
        //  $this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));

        $this->addFF(['name'=>'kod',
            'name_field'=>'kod',
            'readonly'=>1
        ]);
        $this->addFF(['name'=>'Назва параметру ',
            'name_field'=>'name',
            'bd_field'=>'name',
            //   'lang_type'=>LANG,
            'required'=>'Назва обов\'язково']);


        $this->addFF(['name'=>'Значення параметру ',
            'name_field'=>'value',
            'bd_field'=>'value',
            //   'lang_type'=>LANG,
            'required'=>'Значення обов\'язково']);





        self::$nameZList='';
        self::$nameZEdit='';
        self::$TableWidth='700px';

        /*   self::$submenu_edit = array(
               'back' => array(
                   'module' => 'sprvalues',
                   'action' => 'list',
                   'class' => 'ajax_back',
                   'post' => $postButton),
               'save' => array(
                   'module' => 'sprvalues',
                   'action' => 'edit_ok',
                   'post' => $postButton),
           );
     */

        self::$submenu_list =array(
            'back' => array('module' => 'settings', 'action' => 'show', 'post' => ''),
            'add' => array('module' => 'sprvalues', 'action' => 'add', 'post' => ''),
        );
    }}
/*
 self::$submenu_edit =array(
   'back' => array('module' => 'sprvalues', 'action' => 'list', 'post' => ''),
    'save' => array('module' => 'sprvalues', 'action' => 'edit_ok', 'post' => ''),
    );
*/

?>