<?php
// класс описывающий структуру модуля управления игроками команды
class TeamPlayersObject extends ObjectRT 
{   
  function init ()
  {
$team_id = poste('team_id');
$turnir_id = poste('turnir_id');
$league_id = poste('league_id');
$menu_team = !empty($team_id) ? '&team_id='.$team_id : '';

      self::$table_class='table_mob_turn';
if (!empty($team_id))
{
    $sql = 'select * from '.T_PLAYERS.' t where t.id='.$team_id.' and t.is_team=1';
    $aTeam= db_row($sql);
}

      // Если есть параметры турнира, возвращаемся на turnirsteams, иначе на teams
      if (!empty($turnir_id)) {
          $url_back = 'turnirsteams-list';
          if (!empty($league_id)) {
              $url_back .= '-&turnir_id='.$turnir_id.'&league_id='.$league_id;
          } else {
              $url_back .= '-&turnir_id='.$turnir_id;
          }
      } else {
          $url_back = 'teams-list';
      }
      if ($_SESSION['is_mobile'] ){
          SystemClass::$Java_script_module='show_zag_left("#'.$url_back.'");';
      }else{
          $show_zag_left='show_zag_center();show_zag_left_big("#'.$url_back.'");';
          SystemClass::$Java_script_module=$show_zag_left;
      }

// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20','width_mob'=>'22'));
      if (empty($virt))
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'20')); 

$this->addFTL(array('name'=>'ПІБ гравця','type'=>'text',
    'oper' => 'edit','target'=>true, 'width_mob'=>'130',
    'name_field'=>'name',
    'width'=>'200','bd_field'=>'name','classAlign'=>'text-start'));

$this->addFTL(array('name'=>'Телефон','name_field'=>'phone','type'=>'text',
    'width'=>'100','bd_field'=>'phone','classAlign'=>'text-center'));

$this->addFTL(array('name'=>'Місто','type'=>'ProstSpr',
    'width'=>'80','name_field'=>'city', 'bd_field'=>'city', 'id_spis'=>'4'));

$this->addFTL(array('name'=>'Рейтинг<br />клубу','type'=>'field',
    'width'=>'60','name_field'=>'reiting', 'bd_field'=>'reiting', 'classAlign'=>'text-center'));

$this->addFTL(array('name'=>'Рейтинг<br />ФНТУ','type'=>'field',
    'width'=>'60','name_field'=>'reiting_ukraine', 'bd_field'=>'reiting_ukraine', 'classAlign'=>'text-center'));

$this->addFTL(array('name'=>'Опл.<br />внес.','type'=>'text',
    'width'=>'40','name_field'=>'is_opl_reiting', 'bd_field'=>'is_opl_reiting', 'check_elem'=>1, 'classAlign'=>'text-center'));

      if (empty($virt)) {
          // Используем стандартное удаление с подтверждением
          $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'id'));
      }

//================================================================================================
// описание полей формы модуля при редактировании или добавления
     $this->addFF(array('name'=>'Команда','name_field'=>'team_id','type'=>'hidden'));
// Всегда добавляем скрытые поля для турнира и лиги, чтобы они передавались при сохранении
// Значения будут заполнены из POST или сессии при открытии формы
$this->addFF(array('name'=>'Турнір','name_field'=>'turnir_id','type'=>'hidden'));
$this->addFF(array('name'=>'Ліга','name_field'=>'league_id','type'=>'hidden'));
$this->addFF(array('name'=>'Гравець','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'player_id',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id',
                    'mess'=>'Виберіть гравця',
                     'where'=>' and is_team=0 and not_use=0 and ispara=0 ',
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'width'=> '980',
                    'required'=>'Гравець обов"язковий',
                    'speedsearch'=>array('min_letter'=>3,
                        'result_fields_dop'=>array('id','city','phone'),'table'=>T_PLAYERS,'where'=>' is_team=0 and not_use=0 and ispara=0 and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця','return_id_val'=>'name', 'name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Телефон','return_id_val'=>'phone','name_field'=>'phone','width'=>'100','filter'=>'1'),
                        array('name'=>'Місто','return_id_val'=>'city','name_field'=>'city','width'=>'80','filter'=>'1'),
                    )
                    ));
                                                                                   
// описание полей формы модуля при редактировании или добавления

  $this->setTableModule(T_PLAYERS);
      if (!empty($team_id)) {
          $name_team = db_row('select name from `' . T_PLAYERS .
              '` where id=' . $team_id . ' and is_team=1');
          
          if ($_SESSION['is_mobile'] )
              $nameZ='<div class="compare_zagl">Гравці команди "'.$name_team['name'].'"</div>';
          else
              $nameZ='<div class="poriv_zag">Гравці команди "'.$name_team['name'].'"</div>';

          self::$nameZList=$nameZ;
          self::$nameZEdit = 'Додавання гравця до команди "' . $name_team['name'] . '"';
      }

// Фильтр для получения игроков команды
if (!empty($team_id)) {
    if ($_SESSION['gt']['user_rule']<>1)   
        $_SESSION['teamplayers']['where']=' and is_team=0 and team_id='.$team_id.' and not_use=0 ';
    else 
        $_SESSION['teamplayers']['where']=' and is_team=0 and team_id='.$team_id.' ';
    $_SESSION['teamplayers']['sort_default']=' name asc';
}

if ($_SESSION['gt']['user_rule']<10 && empty($virt)) {
    // Если есть параметры турнира, возвращаемся на turnirsteams, иначе на teams
    if (!empty($turnir_id)) {
        $back_post = 'turnir_id='.$turnir_id;
        if (!empty($league_id)) {
            $back_post .= '&league_id='.$league_id;
        }
        self::$submenu_list =array( 
            'back' => array('module' => 'turnirsteams', 'action' => 'list', 'post' => $back_post),
        );
    } else {
        self::$submenu_list =array( 
            'back' => array('module' => 'teams', 'action' => 'list'),
        );
    }
}

// НЕ вызываем InitMainMenu() для этого модуля, так как он устанавливает подменю турниров
// Вместо этого используем стандартное главное меню без подменю турниров
if (empty($virt)) {
    // Инициализируем только главное меню без подменю турниров
    // InitMainMenu не вызываем, чтобы избежать подменю "Результаты", "Ігри" и т.д.
} else {
    $post_return = '&virt=1';
    SystemClass::setPost_return($post_return);
    $_SESSION['POST_RETURN'] =$post_return;
}

// НЕ устанавливаем post_return здесь, так как он будет установлен в sql/save.php
// Это предотвращает конфликты при формировании post_return из aParent в list_show()
  
  self::$aParent[0]= ['name_field'=>'team_id', 'table'=>T_PLAYERS, 'type'=>'Hidden'];
  // Сохраняем параметры турнира и лиги для корректного возврата
  $parent_index = 1;
  if (!empty($turnir_id)) {
      self::$aParent[$parent_index]= ['name_field'=>'turnir_id', 'type'=>'Hidden'];
      $parent_index++;
  }
  if (!empty($league_id)) {
      self::$aParent[$parent_index]= ['name_field'=>'league_id', 'type'=>'Hidden'];
  }
  }
}

?>
