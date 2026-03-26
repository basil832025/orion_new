<?php
// класс возвращает и обрабатывает для вывода поля формы
class ActionModule 
{
  protected  $module = 'players';
  protected  $action = 'list';
  protected  $id = '';
  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $subMenu2 = array();
  protected  $mainMenu = array();
  protected $aSpecField = array(); // спец массив например пренадлежность
  protected $aParent = array(); // массив родителя
  protected $type_module = ''; // тип модуля например дерововидный
  protected $aData = array(); // массив данных запроса 
  protected $table_module = ''; // главная таблица модуля 
  protected  $close = '1'; 
  protected  $aEditField = '';
  protected  $id_aParent = '';
  protected  $name_aParent = '';
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  
  function __construct()
    {
  $this->module= SystemClass::getModule();

    $this->action= SystemClass::getAction();

    $this->id = poste('id');
    $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule();
    $this->type_module = ObjectRT::getTypeModule();
    $this->aEditField = ObjectRT::getAEditField();
    }
   function init()
    {    global $aModulesSettings;
        
        // если нет действия значит это список      
        if (!$this->action || $this->action == 'parts_list' || $this->action=='list' || $this->action=='home') {
            $this->action = 'list_show';
        } 

        if (($this->action=='edit' || $this->action=='edit_ok' || $this->action=='add') && ($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {
            $this->action='list';
            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
        }
        $grp_module = !empty($aModulesSettings[$this->module]['path']) ? $aModulesSettings[$this->module]['path'] : '';    // запуск модуля


      // если есть доп функции подгружаем их
        //  (!empty($this->module) ? $this->module :'').'/func/func.'  .$this->module .'.php'))
        if (file_exists('modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/func/func.'  .$this->module .'.php'))

         {
              include_once 'modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/func/func.'  .$this->module .'.php';
           //   include_once 'modules/'.(!empty($this->module) ? $this->module :'').'/func/func.'  .$this->module .'.php';

         }
      
       //обработка тригера
      // if (file_exists('modules/'.(!empty($this->module) ? $this->module :'').'/triger/befor.'  .$this->action .'.php'))
       if (file_exists('modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/triger/befor.'  .$this->action .'.php'))
         {
              include_once 'modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/triger/befor.'  .$this->action .'.php';
           //   include_once 'modules/'.(!empty($this->module) ? $this->module :'').'/triger/befor.'  .$this->action .'.php';

         }
  
           // если есть в папке файл со спец дествием то выполняем его
        // if (file_exists('modules/'.(!empty($this->module) ? $this->module :'').'/action/'  .$this->action .'.php'))
         if (file_exists('modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/action/'  .$this->action .'.php'))
         {
             include_once 'modules/'.(!empty($grp_module) ? $grp_module :$this->module).'/action/'  .$this->action .'.php';
          //   include_once 'modules/'.(!empty($this->module) ? $this->module :'').'/action/'  .$this->action .'.php';
           // тут вызываем объект класса с дополнительными обработками или операцияит
        
        
        
        
        $classAction = $this->action.'Action'; // класс с которого нужно прочитать настройки
    if (class_exists($classAction)) {     
          $obAct = new $classAction();
          $obAct->init();   
        //$objEdit->form_show();
        $cont = $obAct->getContent();
       $subm = $obAct->getSubMenu();
        $subm2 = $obAct->getSubMenu2();
        $mainm = $obAct->getMainMenu();
        $javas= $obAct->getJavaScript();
        if (!empty($cont))  $this->content=$cont;
        if (!empty($subm))  $this->subMenu=$subm;
        if (!empty($subm2))  $this->subMenu2=$subm2;
        if (!empty($mainm))  $this->mainMenu=$mainm;
        if (!empty($javas))  $this->Java_script=$javas;
        }
        }
        else{
        $this->setPostReturn(); // обработка родительской связи с данным модулем
       // if ($this->type_module=='form') $this->action='edit';
            // если сущесвует метод действие, то вызываем его
            if (method_exists($this, $this->action)) {
                call_user_func(array($this, $this->action));
            }
        }
              //обработка тригера после
       if ($this->action!='edit_ok') {
         //  if (file_exists('modules/' . (!empty($this->module) ? $this->module : '') . '/triger/after.' . $this->action . '.php')) {
           if (file_exists('modules/' . (!empty($grp_module) ? $grp_module :$this->module) . '/triger/after.' . $this->action . '.php')) {
               include_once 'modules/' . (!empty($grp_module) ? $grp_module :$this->module) . '/triger/after.' . $this->action . '.php';
            //   include_once 'modules/' . (!empty($this->module) ? $this->module : '') . '/triger/after.' . $this->action . '.php';

           }
       }
    }
// вывод таблицы модуля тоесть список таблицы модуля
// ПОКА ВРЕМЕНО ОСТАВИМ, НО ДАЛЬШЕ БУДЕТ ГЛОБАЛЬНО ПЕРЕДЕЛАНА ФИЛЬТРАЦИЯ В КЛАСЕ СПИСКА
    function list_show($sql='')
    {
        $objList = new ListTable();
        $objList->list_show($sql);
        $this->content=$objList->getContent();
        $this->subMenu=$objList->getSubMenu();
        $this->subMenu2=$objList->getSubMenu2();
        $this->mainMenu=$objList->getMainMenu();
        $this->Java_script=$objList->getJavaScript();
     //   wLog($this->content);
    }
// операция очистка фильтров
    function clear_filter()
    {
       $this->list_show(); 
    }
// ДЕЙСТВИЕ ДОБАВЛЕНИЕ В МОДУЛЬ (ТАБЛИЦУ) НОВОГО ЭЛЕМЕНТА      
     function add()
    {
        $objAdd = new FormAdd();
        $objAdd->addForm();
        $this->content=$objAdd->getContent();
        $this->subMenu=$objAdd->getSubMenu();
        $this->subMenu2=$objAdd->getSubMenu2();
        $this->Java_script=$objAdd->getJavaScript();
  
    }
// РЕДАКТИРОВАНИЕ ЭЛЕМЕНТА МОДУЛЯ    
  function edit()
    {
        $objEdit = new FormEdit();
        $objEdit->form_show();
        $this->content=$objEdit->getContent();
        $this->subMenu=$objEdit->getSubMenu();
        $this->subMenu2=$objEdit->getSubMenu2();
        $this->Java_script=$objEdit->getJavaScript();
   
    }
// СОХРАНЕНИЕ ФОРМЫ ПОСЛЕ ДОБАВЛЕНИЯ ИЛИ РЕДАКТИРОВАНИЕ СУЩЕСТВУЮЩЕГО ЭЛЕМЕНТА    
  function edit_ok()
    { global $aModulesSettings;
      $grp_module = !empty($aModulesSettings[$this->module]['path']) ? $aModulesSettings[$this->module]['path'] : '';    // запуск модуля

        $objEditOk = new FormSave();
        $objEditOk->Save();
        if (file_exists('modules/' . (!empty($grp_module) ? $grp_module :$this->module) . '/triger/after.edit_ok.php')) {
            include_once 'modules/' . (!empty($grp_module) ? $grp_module :$this->module) . '/triger/after.edit_ok.php';
      }
        if (SystemClass::getIsAjax()!=2)
        {
         $RedirectUrl = ObjectRT::getRedirectUrl();
         $post_return_custom = '';
         if (!empty($RedirectUrl))
         {
           SystemClass::setAction($RedirectUrl['action']);
        SystemClass::setModule($RedirectUrl['module']);
        //   parent::list_show();
          $post_return_custom = !empty($RedirectUrl['post_return']) ?  $RedirectUrl['post_return'] :'';
          // Очищаем post_return_noMA, чтобы list_show() не формировал неправильный post_return
          // из aParent, так как мы хотим использовать кастомный post_return
          if (!empty($post_return_custom)) {
              SystemClass::setPost_return_noMA('');
          }
          //  self::setJava_script('redirect_url("'.URL.$RedirectUrl.'");');
         }else{
            // Если нет RedirectUrl, явно устанавливаем action='edit_ok' для корректного редиректа
            SystemClass::setAction('edit_ok');
         }
            
            // Если есть RedirectUrl, сохраняем team_id до вызова list_show()
            $team_id_for_return = '';
            if (!empty($RedirectUrl) && !empty($post_return_custom)) {
                // Извлекаем team_id из post_return или из POST или из сессии (для teamplayers)
                if (preg_match('/team_id=(\d+)/', $post_return_custom, $matches)) {
                    $team_id_for_return = $matches[1];
                } else {
                    // Пытаемся получить из POST
                    $team_id_for_return = poste('team_id');
                    // Если нет в POST, проверяем сессию (для модуля teamplayers)
                    if (empty($team_id_for_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'])) {
                        $team_id_for_return = $_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'];
                    }
                }
            } elseif ($this->module == 'teamplayers') {
                // Если нет RedirectUrl, но это модуль teamplayers, пытаемся получить team_id
                $team_id_for_return = poste('team_id');
                if (empty($team_id_for_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'])) {
                    $team_id_for_return = $_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'];
                }
            }
            
            // Сохраняем параметры турнира и лиги до list_show(), чтобы они были доступны после
            $turnir_id_before_list = poste('turnir_id');
            $league_id_before_list = poste('league_id');
            if (empty($league_id_before_list)) {
                $form_post_before_list = poste('form');
                if (is_array($form_post_before_list) && !empty($form_post_before_list['league_id'])) {
                    $league_id_before_list = $form_post_before_list['league_id'];
                }
            }

            // Для модуля turnirsteams сохраняем параметры в post_return_noMA ДО list_show()
            // чтобы кнопка "+" оставалась с параметрами турнира
            if ($this->module == 'turnirsteams' && (!empty($turnir_id_before_list) || !empty($league_id_before_list))) {
                $post_noMA_updated = SystemClass::getPost_return_noMA();
                if (empty($post_noMA_updated)) {
                    $post_noMA_updated = '';
                }
                if (strpos($post_noMA_updated, 'turnir_id=') === false && !empty($turnir_id_before_list)) {
                    $post_noMA_updated .= '&turnir_id='.$turnir_id_before_list;
                }
                if (strpos($post_noMA_updated, 'league_id=') === false && !empty($league_id_before_list)) {
                    $post_noMA_updated .= '&league_id='.$league_id_before_list;
                }
                SystemClass::setPost_return_noMA($post_noMA_updated);
                $_SESSION['TURNIRSTEAMS_SAVE_TURNIR_ID'] = $turnir_id_before_list;
                $_SESSION['TURNIRSTEAMS_SAVE_LEAGUE_ID'] = $league_id_before_list;
            }
            
            $this->list_show();

            // Для модуля turnirs после сохранения в рамках ліги
            // возвращаемся в список этой же лиги.
            if ($this->module == 'turnirs' && !empty($league_id_before_list)) {
                SystemClass::setPost_return_noMA('&league_id='.(int)$league_id_before_list);
                SystemClass::setPost_return('turnirs-list-&league_id='.(int)$league_id_before_list);
            }
            
            // Для модуля teamplayers после list_show() сохраняем параметры турнира в post_return_noMA
            // чтобы они были доступны для ссылок добавления/редактирования
            if ($this->module == 'teamplayers' && (!empty($turnir_id_before_list) || !empty($league_id_before_list))) {
                $team_id_post = poste('team_id');
                $current_post_noMA = SystemClass::getPost_return_noMA();
                // Если в post_return_noMA еще нет параметров турнира, добавляем их
                if (!empty($team_id_post)) {
                    // Проверяем, есть ли уже team_id в current_post_noMA
                    $has_team_id = strpos($current_post_noMA, 'team_id=') !== false;
                    
                    // Формируем обновленный post_return_noMA
                    if (empty($current_post_noMA) || !$has_team_id) {
                        // Если post_return_noMA пустой или нет team_id, создаем новый
                        $post_noMA_updated = '&team_id='.$team_id_post;
                        if (!empty($turnir_id_before_list)) {
                            $post_noMA_updated .= '&turnir_id='.$turnir_id_before_list;
                        }
                        if (!empty($league_id_before_list)) {
                            $post_noMA_updated .= '&league_id='.$league_id_before_list;
                        }
                    } else {
                        // Если team_id уже есть, добавляем только недостающие параметры
                        $post_noMA_updated = $current_post_noMA;
                        if (strpos($post_noMA_updated, 'turnir_id=') === false && !empty($turnir_id_before_list)) {
                            $post_noMA_updated .= '&turnir_id='.$turnir_id_before_list;
                        }
                        if (strpos($post_noMA_updated, 'league_id=') === false && !empty($league_id_before_list)) {
                            $post_noMA_updated .= '&league_id='.$league_id_before_list;
                        }
                    }
                    SystemClass::setPost_return_noMA($post_noMA_updated);
                }
            }
            
            // После list_show() ОБЯЗАТЕЛЬНО переопределяем post_return, если был установлен RedirectUrl
            // Это нужно, так как list_show() перезаписывает post_return из aParent или postButton
            if (!empty($RedirectUrl) && !empty($team_id_for_return)) {
                // Формируем правильный post_return в формате: module-action-param=value
                $post_return_final = 'teamplayers-list-team_id='.$team_id_for_return;
                
                // Добавляем параметры турнира и лиги, если они есть
                $turnir_id_return = poste('turnir_id');
                if (empty($turnir_id_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'])) {
                    $turnir_id_return = $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'];
                }
                if (!empty($turnir_id_return)) {
                    $post_return_final .= '&turnir_id='.$turnir_id_return;
                }
                
                $league_id_return = poste('league_id');
                if (empty($league_id_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'])) {
                    $league_id_return = $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'];
                }
                if (!empty($league_id_return)) {
                    $post_return_final .= '&league_id='.$league_id_return;
                }
                
                // Очищаем все старые значения, чтобы избежать дублирования
                SystemClass::setPost_return('');
                // НЕ устанавливаем $_SESSION['POST_RETURN'] здесь, так как $Post_return__ уже был взят в returnResultAjax()
                // и это может привести к дублированию. Используем только SystemClass::setPost_return()
                
                // Формируем post_return_noMA с параметрами для использования в ссылках (add, edit)
                $post_return_noMA = '&team_id='.$team_id_for_return;
                if (!empty($turnir_id_return)) {
                    $post_return_noMA .= '&turnir_id='.$turnir_id_return;
                } elseif (!empty($turnir_id_before_list)) {
                    $post_return_noMA .= '&turnir_id='.$turnir_id_before_list;
                }
                if (!empty($league_id_return)) {
                    $post_return_noMA .= '&league_id='.$league_id_return;
                } elseif (!empty($league_id_before_list)) {
                    $post_return_noMA .= '&league_id='.$league_id_before_list;
                }
                // Устанавливаем post_return_noMA для использования в ссылках добавления/редактирования
                SystemClass::setPost_return_noMA($post_return_noMA);
                
                // Обновляем subMenu['add']['post'] с правильными параметрами для кнопки "+"
                if (!empty($this->subMenu['add']['post'])) {
                    // Извлекаем старые параметры из post (если есть)
                    $old_post = $this->subMenu['add']['post'];
                    // Формируем новый post с параметрами турнира
                    if (strpos($old_post, 'team_id=') !== false && strpos($old_post, 'turnir_id=') === false) {
                        // Если есть team_id, но нет turnir_id, добавляем параметры турнира
                        $this->subMenu['add']['post'] = $old_post;
                        if (!empty($turnir_id_return)) {
                            $this->subMenu['add']['post'] .= '&turnir_id='.$turnir_id_return;
                        } elseif (!empty($turnir_id_before_list)) {
                            $this->subMenu['add']['post'] .= '&turnir_id='.$turnir_id_before_list;
                        }
                        if (!empty($league_id_return)) {
                            $this->subMenu['add']['post'] .= '&league_id='.$league_id_return;
                        } elseif (!empty($league_id_before_list)) {
                            $this->subMenu['add']['post'] .= '&league_id='.$league_id_before_list;
                        }
                    } else {
                        // Если параметров нет, используем post_return_noMA
                        $this->subMenu['add']['post'] = $post_return_noMA;
                    }
                } else {
                    // Если subMenu['add']['post'] пустой, устанавливаем из post_return_noMA
                    $this->subMenu['add']['post'] = $post_return_noMA;
                }
                
                // Устанавливаем post_return в SystemClass (главное место для JSON ответа)
                SystemClass::setPost_return($post_return_final);
                // НЕ устанавливаем в $_SESSION['POST_RETURN'], чтобы избежать дублирования
                // Сохраняем параметры турнира в сессии для использования при следующем запросе
                // (они будут использованы в setPostReturn() при следующем запросе)
                if (!empty($turnir_id_return)) {
                    $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id_return;
                } elseif (!empty($turnir_id_before_list)) {
                    $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id_before_list;
                }
                if (!empty($league_id_return)) {
                    $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id_return;
                } elseif (!empty($league_id_before_list)) {
                    $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id_before_list;
                }
                // Очищаем team_id из сессии, так как он уже в URL
                if (!empty($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'])) {
                    unset($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID']);
                }
                // wLog('edit_ok FINAL post_return: '.$post_return_final);
            } elseif ($this->module == 'teamplayers' && !empty($team_id_for_return)) {
                // Даже если нет RedirectUrl, но есть team_id для teamplayers, устанавливаем post_return
                $post_return_final = 'teamplayers-list-team_id='.$team_id_for_return;
                
                // Добавляем параметры турнира и лиги, если они есть
                $turnir_id_return = poste('turnir_id');
                if (empty($turnir_id_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'])) {
                    $turnir_id_return = $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'];
                }
                if (!empty($turnir_id_return)) {
                    $post_return_final .= '&turnir_id='.$turnir_id_return;
                }
                
                $league_id_return = poste('league_id');
                if (empty($league_id_return) && !empty($_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'])) {
                    $league_id_return = $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'];
                }
                if (!empty($league_id_return)) {
                    $post_return_final .= '&league_id='.$league_id_return;
                }
                
                // Очищаем все старые значения, чтобы избежать дублирования
                SystemClass::setPost_return('');
                
                // Формируем post_return_noMA с параметрами для использования в ссылках (add, edit)
                $post_return_noMA = '&team_id='.$team_id_for_return;
                if (!empty($turnir_id_return)) {
                    $post_return_noMA .= '&turnir_id='.$turnir_id_return;
                } elseif (!empty($turnir_id_before_list)) {
                    $post_return_noMA .= '&turnir_id='.$turnir_id_before_list;
                }
                if (!empty($league_id_return)) {
                    $post_return_noMA .= '&league_id='.$league_id_return;
                } elseif (!empty($league_id_before_list)) {
                    $post_return_noMA .= '&league_id='.$league_id_before_list;
                }
                // Устанавливаем post_return_noMA для использования в ссылках добавления/редактирования
                SystemClass::setPost_return_noMA($post_return_noMA);
                
                // Обновляем subMenu['add']['post'] с правильными параметрами для кнопки "+"
                if (!empty($this->subMenu['add']['post'])) {
                    // Извлекаем старые параметры из post (если есть)
                    $old_post = $this->subMenu['add']['post'];
                    // Формируем новый post с параметрами турнира
                    if (strpos($old_post, 'team_id=') !== false && strpos($old_post, 'turnir_id=') === false) {
                        // Если есть team_id, но нет turnir_id, добавляем параметры турнира
                        $this->subMenu['add']['post'] = $old_post;
                        if (!empty($turnir_id_return)) {
                            $this->subMenu['add']['post'] .= '&turnir_id='.$turnir_id_return;
                        } elseif (!empty($turnir_id_before_list)) {
                            $this->subMenu['add']['post'] .= '&turnir_id='.$turnir_id_before_list;
                        }
                        if (!empty($league_id_return)) {
                            $this->subMenu['add']['post'] .= '&league_id='.$league_id_return;
                        } elseif (!empty($league_id_before_list)) {
                            $this->subMenu['add']['post'] .= '&league_id='.$league_id_before_list;
                        }
                    } else {
                        // Если параметров нет, используем post_return_noMA
                        $this->subMenu['add']['post'] = $post_return_noMA;
                    }
                } else {
                    // Если subMenu['add']['post'] пустой, устанавливаем из post_return_noMA
                    $this->subMenu['add']['post'] = $post_return_noMA;
                }
                
                // Устанавливаем только в SystemClass, не в сессию, чтобы избежать дублирования
                SystemClass::setPost_return($post_return_final);
                // Сохраняем параметры турнира в сессии для использования при следующем запросе
                if (!empty($turnir_id_return)) {
                    $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id_return;
                } elseif (!empty($turnir_id_before_list)) {
                    $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id_before_list;
                }
                if (!empty($league_id_return)) {
                    $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id_return;
                } elseif (!empty($league_id_before_list)) {
                    $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id_before_list;
                }
                // Очищаем team_id из сессии, так как он уже в URL
                if (!empty($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'])) {
                    unset($_SESSION['TEAMPLAYERS_SAVE_TEAM_ID']);
                }
            } else {
                // Если нет RedirectUrl, но есть параметры турнира, также сохраняем их в post_return_noMA
                // для использования в ссылках добавления/редактирования
                if ($this->module == 'teamplayers') {
                    $team_id_post = poste('team_id');
                    $turnir_id_post = poste('turnir_id');
                    $league_id_post = poste('league_id');
                    
                    // Если параметры есть в POST, сохраняем их в post_return_noMA
                    if (!empty($team_id_post)) {
                        $post_noMA = '&team_id='.$team_id_post;
                        if (!empty($turnir_id_post)) {
                            $post_noMA .= '&turnir_id='.$turnir_id_post;
                        } elseif (!empty($turnir_id_before_list)) {
                            $post_noMA .= '&turnir_id='.$turnir_id_before_list;
                        }
                        if (!empty($league_id_post)) {
                            $post_noMA .= '&league_id='.$league_id_post;
                        } elseif (!empty($league_id_before_list)) {
                            $post_noMA .= '&league_id='.$league_id_before_list;
                        }
                        
                        // Получаем текущий post_return_noMA и добавляем параметры, если их там нет
                        $current_post_noMA = SystemClass::getPost_return_noMA();
                        if (!empty($current_post_noMA)) {
                            // Если в текущем post_return_noMA уже есть team_id, добавляем только недостающие параметры
                            if (strpos($current_post_noMA, 'turnir_id=') === false && !empty($turnir_id_post)) {
                                $current_post_noMA .= '&turnir_id='.$turnir_id_post;
                            } elseif (strpos($current_post_noMA, 'turnir_id=') === false && !empty($turnir_id_before_list)) {
                                $current_post_noMA .= '&turnir_id='.$turnir_id_before_list;
                            }
                            if (strpos($current_post_noMA, 'league_id=') === false && !empty($league_id_post)) {
                                $current_post_noMA .= '&league_id='.$league_id_post;
                            } elseif (strpos($current_post_noMA, 'league_id=') === false && !empty($league_id_before_list)) {
                                $current_post_noMA .= '&league_id='.$league_id_before_list;
                            }
                            SystemClass::setPost_return_noMA($current_post_noMA);
                        } else {
                            SystemClass::setPost_return_noMA($post_noMA);
                        }
                    }
                }
            }

        }
        
    }
    // удаления изображения
    function deleteImage()
    {
         $file_arr=db_row('select name,module,field,id_elem from `'.T_FILES.'` where id='.$_POST['key']);
        $file_name=$file_arr['name'];
        //$file_name = isset($_REQUEST['file']) ?  basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = DIR_FILES_SITE.$file_name;
        $thumbnail_path = DIR_FILES_SITE_SMALL.$file_name;
        $mini_path = DIR_FILES_SITE_MINI.$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && delete_file($file_path);
        db_query('delete from `'.T_FILES.'` where id='.$_POST['key'],'name');
        db_query('update '.get_table_name($file_arr['module']).' set '.$file_arr['field'].'="" where id='.$file_arr['id_elem']);
        if ($success && is_file($thumbnail_path)) {
            delete_file($thumbnail_path);
            delete_file($mini_path);
        }
        $output = ['status'=>'success','text'=>'OK files were processed!!!!!!!!.'];
        echo json_encode($output);
        exit;
    }
    //УДАЛЕНИЕ ЭЛЕМЕНТА МОДУЛЯ
  function delete()
    {
          /* if ($_POST['ajax_method']==2) {
            echo 'ok';
             $this->Java_script = 'test_();';
      
        }else*/
        {
            $sql = 'delete from `' . $this->table_module . '`    WHERE id=' . $this->id;
            db_query($sql);
            // вывод html страници структуры дерва
            $this->list_show();
        }
    }
  // функция для быстрого сохранения редактируемого поля в БД, возвращает OK или ERROR без пояснений, в джаваскрипте нужно выделять па пару секунд цветом рамку поля зеленое гуд и красное фальш 
  function saveElementInput()
  { 
    if ($_SESSION['gt']['user_rule']>=10)     return;
    $nameField = poste('nameField');
    $NewvalField = poste('NewvalField');
    $table = $this->table_module;
    $aElem = ObjectRT::getColListPoField($nameField);
      $NewvalField = $aElem['type'] =='date' ? date_for_sql_format($NewvalField) : $NewvalField;
    $id_table = $this->id;
    $aTable = ObjectRT::getTablePoSynon($aElem['bd_field_syn']);
    $table = (!empty($aTable['table'])) ? $aTable['table'] : $this->table_module;// если это не связующая таблица то основаня
    
    if ($aElem['bd_field_syn'] && $table != $this->table_module) { // если есть сложная связь между несколькими таблицами, то найдем родительскую таблицу
                // ищем id связующей таблицы
              $oSelect = new SqlSelect();
              $id_table =  $oSelect->getIdUnionTable($aElem['bd_field_syn']);
        }
  
    $sql = 'update `' . $table . '` SET '.$aElem['bd_field_short_name'].' = "' . $NewvalField .
                    '" WHERE id = ' . $id_table;

           if (db_query($sql) ) {
               if ($this->module == 'reiting') {
                   $updated_field = !empty($aElem['bd_field_short_name']) ? $aElem['bd_field_short_name'] : $nameField;
                   if (in_array($updated_field, array('set_1', 'set_2', 'break_1', 'break_2'), true)) {
                       $game = db_row('SELECT id, turnir_id, etap_id, pl_id_1, pl_id_2, set_1, set_2, break_1, break_2 FROM '.T_REITING.' WHERE id='.(int)$id_table);
                       if (!empty($game)) {
                           $_POST['form'] = array(
                               'pl_id_1' => $game['pl_id_1'],
                               'pl_id_2' => $game['pl_id_2'],
                               'set_1' => $game['set_1'],
                               'set_2' => $game['set_2'],
                               'break_1' => $game['break_1'],
                               'break_2' => $game['break_2']
                           );
                           $_POST['id'] = $game['id'];
                           $_POST['turnir_id'] = $game['turnir_id'];
                           $_POST['etap_id'] = $game['etap_id'];
                           if (file_exists('modules/grp_turnirs/reiting/triger/after.edit_ok.php')) {
                               include_once 'modules/grp_turnirs/reiting/triger/after.edit_ok.php';
                           }
                       }
                   }
               }
               $this->setContent('OK');
           } else  {
               $this->setContent('ERROR');
           }
  }  
  // поиск по первых буквах по полю
  function searchFirstLetter()
  {
    $nameField =  poste('nameField');
    $nameField = preg_replace('|form\[(.+)\]|',"\\1",$nameField);
    $NewvalField = poste('NewvalField');
    $table_p= poste('table');
    $where= poste('where');

      $where = !empty($where) ? base64_decode($where) : '' ;
    $result_fields_dop = poste('result_fields_dop');
    $table = $this->table_module;
    $aElem = ObjectRT::getEditPoField($nameField); 
    $id_table = $this->id;
    if (empty($table_p)) {
        $aTable = ObjectRT::getTablePoSynon($aElem['bd_field_syn']);
        $table_p = (!empty($aTable['table'])) ? $aTable['table'] :'';
     }
     $table = (!empty($table_p)) ? $table_p : $this->table_module;// если это не связующая таблица то основаня
  
 /*   if ($aElem['bd_field_syn'] && $table != $this->table_module) { // если есть сложная связь между несколькими таблицами, то найдем родительскую таблицу
                // ищем id связующей таблицы
              $oSelect = new SqlSelect();
              $id_table =  $oSelect->getIdUnionTable($aElem['bd_field_syn']);
        }*/
   $result_fields_dop_id=[];  
  $result_fields_dop_field=[];  
  if (!empty($result_fields_dop))
  {
  $result_fields_dop= json_decode(base64_decode($result_fields_dop));
  foreach ($result_fields_dop as $element => $value) {
    if (is_numeric($element)) {
         $result_fields_dop_field[]=$value;
         $aResField[$value]=$value;
    } else {
          $result_fields_dop_field[]=$value;
         $aResField[$value]=$element;  }
}
  
  $result_fields_dop = implode(',',$result_fields_dop_field);
}
  $where = (!empty($where) && $where!='undefined') ? $where : '';

    $sql = 'select  id,'.$nameField.($result_fields_dop?','.$result_fields_dop:'').' from `' . $table . '` m WHERE '.(!empty($where) ? $where :'') .$nameField.' LIKE "' . $NewvalField.'%"';

    $aElem = db_list($sql);
    $res ='';
    $aJson = [];
    $aResNew = [];
    if (!empty($aElem))
    {
   //  $aResField = $result_fields_dop_id;
  //   $aResField = explode(',',$result_fields_dop);
        
    foreach ($aElem as $v)
     {    $resFields='';  
         if (!empty($aResField)){
            $fis=1;
             $aJson = [];
            foreach ($aResField as $key => $vf)
            { 
               if ($key!='id') $aJson[$vf] =$v[$key]; 
            //   if ($fis==1) $fis = 0 ; else $resFields .=',';
            //   $resFields .=$v[$vf];     
            }
         }
          $jsonReturn='';
       //   if (!empty($aJson)) $jsonReturn = ' jsonReturn='.base64_encode(json_encode($aJson));
          if (!empty($aJson)) $jsonReturn = base64_encode(json_encode($aJson));
          //'jsonReturn'=>$jsonReturn,
         $aResNew[]= ['label'=>$v[$nameField], 'value'=>$v['id'],'jsonReturn'=>$jsonReturn];

         $res =base64_encode(json_encode($aResNew));
         //$res .='<option '.$jsonReturn.' value="'.$v['id'].'">'.$v[$nameField].'</option>';

     } 
     }  

               
              
           if ($res ) $this->setContent($res); else  $this->setContent('NO');
    
  }
  // функция вернет по яксу текст для всплывающего окна и массив полей, которые нужо заменит если ответять позитивно
  function searchFirstLetterFieldsMess()
  {
   $nameField =  poste('nameField');
    $nameField = preg_replace('|form\[(.+)\]|',"\\1",$nameField);
    $NewvalField = poste('NewvalField');
    $table = $this->table_module;
    $aElem = ObjectRT::getEditPoField($nameField); 
    $old_id_table = $this->id;
    $id_table='';
    $aTable='';
    if (!empty($aElem['bd_field_syn']))
    $aTable = ObjectRT::getTablePoSynon($aElem['bd_field_syn']);
    $table = (!empty($aTable['table'])) ? $aTable['table'] : $this->table_module;// если это не связующая таблица то основаня
      if ($aElem['bd_field_syn'] && $table != $this->table_module) { // если есть сложная связь между несколькими таблицами, то найдем родительскую таблицу
                // ищем id связующей таблицы
              
              
             // $id_table =  $oSelect->getIdUnionTable($aElem['bd_field_syn']);
        }
        
        $old_fields= array();
        $new_fields = array();
     // ищем в шаблоне все старые значения   
     if (preg_match_all('#{(.*?)\$\$old}#is',$aElem['messPodtvZamens'],$aOldVal))
     {
        $oSelect = new SqlSelect();
        if (!empty($aOldVal[1]))
        {
            
            $aOldValReg =array();    
            foreach($aOldVal[1] as $k => $v){
               $old_fields[$v] = ObjectRT::getEditPoField($v); 
               $aOldValReg[$v] = $aOldVal[0][$k];
            } 
        }
         $oSelect->workFields($old_fields);
         $oSelect->setWhere(' and '.ObjectRT::getTableModuleSynon().'.id='.$old_id_table);
        $aData= $oSelect->resultList();
        if (!empty($aData)){
            foreach($aData[0] as $k =>$v)
            {
               if (!empty($aOldValReg[$k])) {
                  $aElem['messPodtvZamens']=str_replace($aOldValReg[$k],$v,$aElem['messPodtvZamens']);
                } 
            }
        }
           
     } // конец замены старых значений  
     // ищем новые значени
         if (preg_match_all('#{(.*?)}#is',$aElem['messPodtvZamens'],$aNewVal))
     {
        $oSelect = new SqlSelect();
        if (!empty($aNewVal[1]))
        {
            $aNewValReg =array();    
            foreach($aNewVal[1] as $k => $v){
               $new_fields[$v] = ObjectRT::getEditPoField($v); 
               $aNewValReg[$v] = $aNewVal[0][$k];
            } 
        }
         $oSelect->workFields($new_fields);
         $oSelect->setWhere(' and '.ObjectRT::getTableModuleSynon().'.id='.$NewvalField);
        $aData= $oSelect->resultList();

        if (!empty($aData)){
            foreach($aData[0] as $k =>$v)
            {
               if (!empty($aNewValReg[$k])) {
                  $aElem['messPodtvZamens']=str_replace($aNewValReg[$k],$v,$aElem['messPodtvZamens']);
                } 
            }
        }
           
     } // конец замены старых значений  
    if (!empty($aElem['messPodtvZamens'])) $this->setContent($aElem['messPodtvZamens']); else  $this->setContent('NO');
  }
// ВЫВОД ДЕРЕВА В ВСПЛЫВАЮЩЕМ ОКНЕ    
  function tree_window()
    {
          //---------------------------------------------------------------------
        $id_spis = poste('id_spis_');
        $table = poste('table');
        $id = poste('id');
        $where =' and active = 1';
       // $where = !empty($id) && !empty($id) ? ' and id='.$id : '';
        // обязательн исключаем себя и соих потомков, чтобы не сделать цикличность
        $form = get_tree_level(db_list('SELECT * FROM `' . (!empty($table) ? $table : $this->
            table_module) . '` where 1=1 '.$where.'  ORDER by sort'), 1, 0, $id);
           $this->close = '0';
        if (!empty($this->type_module) && $this->type_module=='tree')
            $first_name = 'Корень';
        else
            $first_name = '';
        $this->Java_script = 'spis_select("' . (!empty($this->thVdata['mess']) ? $this->thVdata['mess'] :'') .
            '", "' . $id_spis . '")';
        include_once ROOT_A . 'html/select_spis.html';
           $this->content= ob_get_contents();
         ob_clean();
    }

// ВЫВОД В ВСПЛЫВАЮЩЕМ ОКНЕ СПИСКА С ПРОСТЫХ СПАВОЧНИКОВ    
  function Prost_Spr()
    {
            global $language;
        //---------------------------------------------------------------------
        $id_spis = poste('id_spis');
        $id_value = poste('id_value');
        $mess = poste('mess');
        // обязательн исключаем себя и соих потомков, чтобы не сделать цикличность
        $sql = 'SELECT *, value as name FROM `' . T_SPRLIST_VALUES .
            '` where id_spis='.$id_value.' and active=1   ORDER by name';
        $form = db_list($sql);
        $this->close = '0';
        $first_name = '';
        $this->Java_script = 'spis_select("' . $mess .
            '", "' . $id_spis . '")';
        include_once ROOT_A . 'html/select_spis.html';
        $this->content= ob_get_contents();
         ob_clean();

    }
   function plus_minus()
    {
        $field = poste('field');
        $sql = 'select ' . $field . ' from `' . $this->table_module . '`  WHERE id=' . $this->
            id;
        $is_field = db_field($sql, $field);
        $is_field = $is_field > 0 ? 0 : 1;
        $sql = "update `" . $this->table_module . "`  SET " . $field . "=" . $is_field .
            " WHERE id=" . $this->id;
        db_query($sql);
        // вывод html страници структуры дерва
        $this->list_show();
    }
    
    function sort()
    {
        $feald = poste('pid');
        $parent_id = poste($feald);
        $save = poste('save');
         $aCols = ObjectRT::getAColList(); 

 $postButton = SystemClass::getPost_return_noMA();
          

          // пройдемся по заголовкам таблицы
                foreach ($aCols as $val) {
                    $type_field = !empty($val['type']) ? strtolower($val['type']) : 'text';
                    //  $BDfield = !empty($val['bd_field']) ? $val['bd_field'] :'';
                    $field_name = !empty($val['bd_field']) ? $val['bd_field'] :(!empty($val['name_field']) ? ObjectRT::getTableModuleSynon().'.'.$val['name_field'] : '');
                 //   $this->lang_type = !empty($val['lang_type']) ? $val['lang_type'] : '';
                    $table = !empty($val['table']) ? $val['table'] : '';
                  //  $this->module_sql = !empty($val['module']) ? $val['module'] : '';
                    $out_result_field = !empty($val['out_result_field']) ? $val['out_result_field'] : (!empty($val['name_field']) ? $val['name_field'] : '');
                    $parent_field = !empty($val['parent_field']) ? $val['parent_field'] : '';
                    //$this->id_value = !empty($val['id_value']) ? $val['id_value'] : '';
                    $name_field_sql = !empty($val['name_field_sql']) ? $val['name_field_sql'] : '';
             if ($type_field=='sort' && $feald==$field_name) break;

     }

     $fields_sql='';
   if (!empty($table) && !empty($out_result_field) && !empty($parent_field)) 
   { 
   $fields_sql = 
     '(select pp.'. (!empty($out_result_field) ? $out_result_field : $field_name) 
      .' from `' .  $table. '` pp 
      where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($parent_field) 
      ? $parent_field : $field_name) . ') as ' . $out_result_field  ;
}
$name_field_sql = !empty($name_field_sql) ? $name_field_sql : $feald.'=' . $parent_id;
        /*$parent_id = !empty($part['parent_id']) ? $part['parent_id'] : 0;
        $part_id = !empty($part['part_id']) ? $part['part_id'] : 0;  */
        if (!empty($save)) {
            $list = poste('list');
            $count = 1;
            foreach ($list as $idval) {
                db_query('UPDATE `' . $this->table_module . '` SET '.$feald.' = ' . $count .
                    ' WHERE id = ' . $idval);
                  
                $count++;
            }
            //$_SESSION['kernel']['action'] = 'parts_list';
            //$_SESSION['kernel']['module'] = 'parts';
            //redirect_Ajax($this->module,$postButton);    
            mess('Данные сортировки успешно сохранены!', 'list', $this->module, 1,1,$postButton);

            /*action
            redirect  */
        } else {
            $sql = 'SELECT '.ObjectRT::getTableModuleSynon().'.*,'.$fields_sql.' 
            FROM `' . $this->table_module . '` '.ObjectRT::getTableModuleSynon().' where '.$name_field_sql .
                ' ORDER by '.$feald;
             $form = db_list($sql);
            $first_name = '';
             $this->close = '0';
            $this->close = '0';
            $not_click = 1; // без кликов
            $sort_type = 1;
            $mess = 'Перемістіть мишкой елементи для сортування';
             $this->Java_script =
                'spis_sort("Отсортуйте елементи", "sort","' . $this->module . '","&pid='.$feald.'&'.$feald.'=' . $parent_id .$postButton.
                '");sort_();';
            include_once ROOT_A . 'html/select_spis.html';
               $this->content= ob_get_contents();
         ob_clean();
        }
    }  
  function setPostReturn()
  {

          if (!empty($this->aParent)) 
          { 
            $post_return='';
            foreach($this->aParent as $key => $vParent)
            {
                if (!empty($vParent['name_field'])) 
                    {
                        $this->id_aParent = $this->getPostReturnId($key);
                        $this->name_aParent = $vParent['name_field'];
                    }
             $post_return .='&'. (!empty($this->name_aParent) ? $this->name_aParent . '="' . $this->id_aParent . '"' : '');
                            $post_return = str_replace('"','',$post_return);  
           }
           
           // Для модуля teamplayers и turnirsteams добавляем параметры турнира, если они есть в POST или сессии
           if ($this->module == 'teamplayers' || $this->module == 'turnirsteams') {
               $turnir_id_param = poste('turnir_id');
               $league_id_param = poste('league_id');
               
               // Если параметры не в POST, пытаемся получить из сессии
               if (empty($turnir_id_param)) {
                   if ($this->module == 'teamplayers' && !empty($_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'])) {
                       $turnir_id_param = $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'];
                   } elseif ($this->module == 'turnirsteams' && !empty($_SESSION['TURNIRSTEAMS_SAVE_TURNIR_ID'])) {
                       $turnir_id_param = $_SESSION['TURNIRSTEAMS_SAVE_TURNIR_ID'];
                   }
               }
               if (empty($league_id_param)) {
                   if ($this->module == 'teamplayers' && !empty($_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'])) {
                       $league_id_param = $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'];
                   } elseif ($this->module == 'turnirsteams' && !empty($_SESSION['TURNIRSTEAMS_SAVE_LEAGUE_ID'])) {
                       $league_id_param = $_SESSION['TURNIRSTEAMS_SAVE_LEAGUE_ID'];
                   }
               }
               
               if (!empty($turnir_id_param) && strpos($post_return, 'turnir_id=') === false) {
                   $post_return .= '&turnir_id='.$turnir_id_param;
               }
               if (!empty($league_id_param) && strpos($post_return, 'league_id=') === false) {
                   $post_return .= '&league_id='.$league_id_param;
               }
           }
           
             SystemClass::setPost_return_noMA($post_return);
             $post_return = $this->module.'-list-'.$post_return;
             
              SystemClass::setPost_return($post_return);
              
        } 
  }  
  function getPostReturnId ($key)
  {
    $id_aParent= '';

            $vParent = $this->aParent[$key]; 
            $post_field = !empty($vParent['post_field']) ? poste($vParent['post_field']) : '';
            $id_aParent = (!empty($post_field)) ? $post_field : poste($vParent['name_field']);

      return  $id_aParent; 
  }
  function getNameAperent($key)
  {
    return (!empty($this->aParent[$key]['name_field']) ? $this->aParent[$key]['name_field'] : '' );
  }
  function getNameATable($key)
  {
    return (!empty($this->aParent[$key]['table']) ? $this->aParent[$key]['table'] : '' );
  }
  function getNameALang($key)
  {
    return (!empty($this->aParent[$key]['lang_type']) ? $this->aParent[$key]['lang_type'] : '' );
  }
  // присвавивает внешний контент переменной класса
  function setContent($content)
  {
     $this->content = $content;
  }    
  // возвращает контен модуля обработаного
  function getContent()
  {
    return $this->content;
  }   
  function getSubMenu()
  {
    return $this->subMenu;
  } 
   function getSubMenu2()
  {
    return $this->subMenu2;
  } 
   function getMainMenu()
  {
    return $this->mainMenu;
  } 
   function setMainMenu($mainMenu)
  {
     $this->mainMenu = $mainMenu;
  }  
  
    function setSubMenu2($subMenu2)
  {
     $this->subMenu2 = $subMenu2;
  } 
  function getaData()
  {
    return $this->aData;
  }
  function setaData($aData)
  {
    $this->aData=$aData;
  }  


 function getJavaScript()
  {
    return $this->Java_script;
  } 
 
  function setJavaScript($JavaScript)
  {
     $this->Java_script = $JavaScript;
  }  
  function getClose()
  {
    return $this->close;
  }  
  function setClose($close)
  {
     $this->close = $close;
  }     
}

?>
