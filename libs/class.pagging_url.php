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
    var $separator = '&nbsp;&bull;&nbsp;';
    
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
    var $url = '';
    
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
      
      // Строим запрос 
    
      $this->sql.= " LIMIT ".($this->page_number-1)*$this->page_items.",".$this->page_items;
    }
    
    // Результат запроса 
    function getList(){
      return db_list($this->sql);
    }
    
    // ХТМЛ для постраничного вывода 
    
    function getHtmlPagging(){
      
      $this->prepare();

      // Если страниц меньше 2 разбивки нет
      if ($this->page_count<2) return "";
      
      // Строим переменные URL
      $vars = '';
     // $this->html .='<div class="pages_site"><br />';
      foreach($this->vars as $key=>$value) $vars.= $key."=".$value."&";
      
      // Ищем группу в которую входит страница 
      $group = intval($this->page_number/$this->page_link)===($this->page_number/$this->page_link)?$this->page_number/$this->page_link:intval($this->page_number/$this->page_link)+1;

      // Стороим код выбора страницы 
      if($group>1)
        $this->html.= '<a class="page"  href="'.$this->url.'/page_'.(($group-1)*$this->page_link).'">&larr;&nbsp;</a>';
      
      for($i=1;$i<=$this->page_link && $i+($group-1)*$this->page_link<=$this->page_count;$i++){
          if($i==1)$this->html.= '';
          else $this->html.= $this->separator;
          if(($i+($group-1)*$this->page_link)==$this->page_number) $this->html.='<span>'.($i+($group-1)*$this->page_link)."</span>";
          else $this->html.= '<a href="'.$this->url.'/page_'.($i+($group-1)*$this->page_link).'">'.($i+($group-1)*$this->page_link).'</a>';
          
      }

      $this->html.= ' ';
     // echo $this->page_link;
      if($group<$this->page_groups){
        $this->html.= '<a class="page" href="'.$this->url.'/page_'.($group*$this->page_link+1).'" >&rarr;</a>';
      }
      //$this->html .='</div>';
      return $this->html;
    }
    
    // Конструктор 
    function pagging($sql,$page){
      $this->sql = $sql;
      $this->page_number = $page;
    }
  }
?>