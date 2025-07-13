<?php
 // Класс для реализации разбивки по страницам 
class Pagging{


    
    // Массив с переменными URL 
    var $vars = array('' => '');
    // Количество элементов на странице 
    var $page_items = 2;
    
    // Количество линков на одной странице 
    var $page_link = 5;    
    
    // Разделитель линков на страницы
    //var $separator = ' <span style=color:#666666;font-size:10px;>&middot;</span> ';
    var $separator = '';
    
    // Общее количество элементов в запросе 
    var $items = 0;
    // Номер активной страници
    var $page_number = 0;
    // Количество страниц 
    var $page_count = 0;
    // Количество групп линков 
    var $page_groups = 0;    
    // Запрос 
    var $sql = '';
    // Конечный ХТМЛ 
    var $html = '';    
    // Результат запроса 
    var $list = 0;
    var $url_p = '';
    public function __construct($sql,$page,$postHtef='') // конструктор
    {
    //     s($_POST);
         $this->sql = $sql;
        $module = poste('module');
        $action = poste('action');
        $this->module =$module;
        $this->action =$action;
        $this->page_number = $page;
        $page_items = SystemClass::getAPost('page_items');
        $page_number= SystemClass::getAPost('page_number');
        $this->url_p = !empty($postHtef) ? $postHtef : SystemClass::getPost_return();
       // $this->url_p .='.$page_items;
       // s('$this->url_p='.$this->url_p );

        $_SESSION[$this->module][$this->action]['page_items']= $page_items ? $page_items : (!empty($_SESSION[$this->module][$this->action]['page_items']) ? $_SESSION[$this->module][$this->action]['page_items'] : PAGE_ITEMS);
        //$_SESSION[$this->module]['page_items']= PAGE_ITEMS;
      //  s('pacount='.$_SESSION[$this->module][$this->action]['page_items']);
        $_SESSION[$this->module][$this->action]['page_number']= $page_number ? $page_number : (!empty($_SESSION[$this->module][$this->action]['page_number']) ? $_SESSION[$this->module][$this->action]['page_number'] : 1);
        $this->page_link = PAGE_GROUPS;

    }
    function prepare(){
      
      // Получаем количество элементов 
      $sql = explode('from',$this->sql);
      $sql = "SELECT COUNT(*) FROM ".$sql[1];
      $this->items = db_field($sql,'COUNT(*)');
      
      // Рассчитываем количество страниц 
      if(intval($this->items/$this->page_items)==$this->items/$this->page_items)
        $this->page_count = $this->items/$this->page_items;
      else 
        $this->page_count = intval($this->items/$this->page_items)+1;
        
      // Рассчитываем кол-во групп страниц 
      $this->page_groups = $this->page_count/$this->page_link;
      $this->page_groups = intval($this->page_groups)==$this->page_groups?$this->page_groups:1+intval($this->page_groups);
      
      // Проверяем текущю страницу 
      $this->page_number = $this->page_number<=$this->page_count?$this->page_number:1;
   //   s('$this->page_number$this->page_number='.$this->page_number);
      // Строим запрос 
    
      $this->sql.= " LIMIT ".($this->page_number-1)*$this->page_items.",".$this->page_items;
      //send_error($this->sql);
    }
    
    // Результат запроса 
    function getList(){
      return db_list($this->sql);
    }
    
    // ХТМЛ для постраничного вывода 
      function getHtmlPagging(){

          $module = poste('module');
          $action = poste('action');

         // s($module);
         // s($action);
          $this->page_items = !empty($_SESSION[$module][$this->action]['page_items']) ? $_SESSION[$module][$this->action]['page_items'] : 1;
//s($this->page_items);
$this->prepare();
          // Ищем группу в которую входит страница
          $group = intval($this->page_number/$this->page_link)===($this->page_number/$this->page_link)?$this->page_number/$this->page_link:intval($this->page_number/$this->page_link)+1;

          // Если страниц меньше 2 разбивки нет
          if ($this->page_items<10) return "";
          $apageGrpActiv = [];
          $apageGrpActiv[0]= !empty($_SESSION[$module][$this->action]['page_items']) && $_SESSION[$module][$this->action]['page_items'] == 10 ? 'active_grp' : '';
          $apageGrpActiv[1]= !empty($_SESSION[$module][$this->action]['page_items']) && $_SESSION[$module][$this->action]['page_items'] == 20 ? 'active_grp' : '';
          $apageGrpActiv[2]= !empty($_SESSION[$module][$this->action]['page_items']) && $_SESSION[$module][$this->action]['page_items'] == 50 ? 'active_grp' : '';
          $apageGrpActiv[3]= !empty($_SESSION[$module][$this->action]['page_items']) && $_SESSION[$module][$this->action]['page_items'] == 100 ? 'active_grp' : '';

          if ($_SESSION['is_mobile']){
              $text = '<div class="padding_main_mob ">
<div class="paging_num_mob">
 <a class="nav-link_pag dropdown-toggle" data-bs-toggle="dropdown" val="'.$_SESSION[$this->module][$this->action]['page_items'].'" module="'.$this->module.'" action="'.$this->action.'" post_string="'.$this->url_p.'" class="slct" role="button" aria-expanded="false">'.$_SESSION[$this->module][$this->action]['page_items'].'</a>
    <ul class="dropdown-menu">
      <li ><a class="dropdown-item page_grp" num="100" module="'.$this->module.'" action="'.$this->action.'" post_string="'.$this->url_p.'">100</a></li>
      <li ><a class="dropdown-item page_grp" num="50" module="'.$this->module.'" action="'.$this->action.'" post_string="'.$this->url_p.'">50</a></li>
      <li <a class="dropdown-item page_grp" num="20" module="'.$this->module.'" action="'.$this->action.'" post_string="'.$this->url_p.'" >20</a></li>
      <li <a class="dropdown-item page_grp"  num="10" module="'.$this->module.'" action="'.$this->action.'" post_string="'.$this->url_p.'">10</a></li>
      
    </ul></div>';
              $text.= '<div class="paging paging_block_center">';

              // Ищем группу в которую входит страница
              $group = intval($this->page_number / $this->page_link) === ($this->page_number / $this->page_link) ? $this->page_number / $this->page_link : intval($this->page_number / $this->page_link) + 1;

              // Стороим код выбора страницы
              if ($group > 1)
                  $text .= '
<a class="previous page_num" num="' . (($group - 1) * $this->page_link) . '"><svg class="pag_left"> <use width="16px" height="16px" xlink:href="#pad_left"></use> </svg></a>';

              for ($i = 1; $i <= $this->page_link && $i + ($group - 1) * $this->page_link <= $this->page_count; $i++) {

                  if (($i + ($group - 1) * $this->page_link) == $this->page_number) $text .= '<a class="active page_num" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";
                  else $text .= '<a class="page_num" module="' . $this->module . '" action="'.$this->action.'" post_string="' . $this->url_p . '" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";

              }

              $this->html .= ' ';

              if ($group < $this->page_groups) {
                  $text .= '<a class="next page_num" module="' . $this->module . '" action="'.$this->action.'" title="Ctrl + →" num="' . ($group * $this->page_link + 1) . '"><svg class="pag_left"> <use width="16px" height="16px" xlink:href="#pad_right"></use> </svg></a>
';
              }
              $text.='</div>';
          }else {
              $text = '<div class="padding_main">
<div class="paging paging_block_center">';


              // Стороим код выбора страницы
              if ($group > 1)
                  $text .= '<a class="begin_page page_num" num="1" module="' . $this->module . '" action="' . $action . '" post_string="' . $this->url_p . '">В початок</a>
<a class="previous page_num" num="' . (($group - 1) * $this->page_link) . '">-' . PAGE_GROUPS . '</a>';

              for ($i = 1; $i <= $this->page_link && $i + ($group - 1) * $this->page_link <= $this->page_count; $i++) {

                  if (($i + ($group - 1) * $this->page_link) == $this->page_number) $text .= '<a class="active page_num" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";
                  else $text .= '<a class="page_num" module="' . $module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";

              }

              $this->html .= ' ';

              if ($group < $this->page_groups) {
                  $text .= '<a class="next page_num" module="' . $module . '" action="' . $action . '" title="Ctrl + →" num="' . ($group * $this->page_link + 1) . '">+' . PAGE_GROUPS . '</a>
<a class="end_page page_num" module="' . $module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="' . $this->page_count . '">В кінець ' . $this->page_count . '</a>';
              }
              $text .= '</div>

<div class="paging_block_left"><div class="pad_text"> К-ть рядків на сторінці:</div><div class="paging paging_num">
<a class="page_grp ' . $apageGrpActiv[0] . '" module="' . $this->module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="10">10</a>
<a class="page_grp ' . $apageGrpActiv[1] . '" module="' . $this->module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="20">20</a>
<a class="page_grp ' . $apageGrpActiv[2] . '" module="' . $this->module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="50">50</a>
<a class="page_grp ' . $apageGrpActiv[3] . '" module="' . $this->module . '" action="' . $action . '" post_string="' . $this->url_p . '" num="100">100</a>
</div>
</div></div>';
          }


          return $text;

          //      return (!empty($_SESSION['pagging_html']) ? $_SESSION['pagging_html'] : '');
      }
    function getHtmlPagging_(){
      
   //   $this->prepare();

      // Если страниц меньше 2 разбивки нет
      if ($this->page_count<2) return "";
      $_SESSION['kernel']['POST_']=$_POST;
      // Строим переменные URL
      $vars = '';
      $this->html .='<div class="parent"><span><ul>';
      foreach($this->vars as $key=>$value) $vars.= $key."=".$value."&";
      
      // Ищем группу в которую входит страница 
      $group = intval($this->page_number/$this->page_link)===($this->page_number/$this->page_link)?$this->page_number/$this->page_link:intval($this->page_number/$this->page_link)+1;

      // Стороим код выбора страницы 
      if($group>1)
        $this->html.= '<li><a class="page ajax_send" href="#'.$this->url_p.'&page_id='.(($group-1)*$this->page_link).'"><span style="font-size:9px">< </span></a></li>';
      
      for($i=1;$i<=$this->page_link && $i+($group-1)*$this->page_link<=$this->page_count;$i++){
          if($i==1)$this->html.= '';
          else $this->html.= $this->separator;
          if(($i+($group-1)*$this->page_link)==$this->page_number) $this->html.="<li>".($i+($group-1)*$this->page_link)."</li>";
          else $this->html.= '<li><a class="ajax_send" href="#'.$this->url_p.'&page_id='.($i+($group-1)*$this->page_link).'">'.($i+($group-1)*$this->page_link).'</a></li>';
          
      }

      $this->html.= ' ';
      
      if($group<$this->page_groups){
        $this->html.= '<a class="page ajax_send" href="#'.$this->url_p.'&page_id='.($group*$this->page_link+1).'" ><span style="font-size:9px"> ></span></a>';
      }
      $this->html .='</ul></span></div>';
      return $this->html;
    }
    
    // Конструктор 
  /*  function pagging($sql,$page){
        s('pagging');
     $this->url_p = SystemClass::getPost_return();  
      $this->sql = $sql;
      $this->page_number = $page;
    }*/
  }
?>