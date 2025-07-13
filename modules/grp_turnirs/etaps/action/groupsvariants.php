<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class GroupsVariantsAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
  protected $aTurnVariants=array(); // 
  protected $aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
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
      $turnir_id = poste('turnir_id');  
       $_SESSION['turnirs']['sort']='';
  $_SESSION['turnirs']['sort_type']='';
    $sql='';
    
    if (empty($_SESSION['etaps']['cnt_people'])) {
    $sql_ = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.$turnir_id;
      $this->cnt_players = db_field($sql_,'cnt');
      } else 
        $this->cnt_players = $_SESSION['etaps']['cnt_people'];
          
      $sql = 'SELECT * FROM `'.T_TURNIR_VARIANTS.'` where type=1 and cntPlayers='.$this->cnt_players;
      //$sql='';
      // турнир не меньше 3 человек
   if ($this->cnt_players>2) 
    {
      $sql = 'SELECT * FROM `'.T_TURNIR_VARIANTS.'` where type=1 and cntPlayers='.$this->cnt_players;
      $this->aTurnVariants = db_list($sql);
   if (empty($this->aTurnVariants))  $this->raschet();
    $sql = 'SELECT * FROM `'.T_TURNIR_VARIANTS.'` where type=1 and cntPlayers='.$this->cnt_players;
    }
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
    function raschet()
    {
      //  s(4 %2);
     //   s(7 % 3);
        // пройдемся по всем вариантам игр 
        //           16 
      foreach($this->aCntPlayers as $cnPlay => $cnGames)
      {
      ///  s($cnPlay);
       // s($cnGames);
        $cn_players= $this->cnt_players; 
        $CnGrp=0;
        // проверяем может делиться без остачи сразу на
        if ($cn_players  % $cnPlay == 0) $CnGrp = $cn_players / $cnPlay;
        if ($CnGrp>0) 
        {
            $cnG=$CnGrp*$cnGames;
          $sql = 'insert into `'.T_TURNIR_VARIANTS.'` SET  cntPlayers='.$cn_players.', groups1='.$CnGrp.'  ,
          people1= '.$cnPlay.', groups2=0, people2=0, type=1, cntGames='.$cnG.',cntGroups='.$CnGrp.', itogo= "'.$CnGrp.'x'.$cnPlay.' = '.$cnG.' игр"';  
      // s($sql);
      db_query($sql);
        }
        // если группы 4 и вышеxесть смысл дальше считать или уже кратности нет
        if ($cnPlay>3) 
        {
        $minPlay = $cnPlay-1;
        $m=0; $n=0;
        while ($cn_players>=$minPlay)  
        {
            $cn_players=$cn_players-$cnPlay;
            $m++;
            // если делится без остатка
          if ($cn_players>0 && ($cn_players  % $minPlay == 0)) {
                $n = $cn_players / $minPlay;
                 $cnG=$m*$cnGames+$n*$this->aCntPlayers[$minPlay];
         
                  $sql = 'insert into `'.T_TURNIR_VARIANTS.'` SET  cntPlayers='.$this->cnt_players.', 
                  groups1='.$m.'  ,
          people1= '.$cnPlay.', groups2='.$n.', people2='.$minPlay.', type=1, cntGames='.$cnG.',cntGroups='.($n+$m).',
           
          itogo= "'.$m.'x'.$cnPlay.' '.$n.'x'.$minPlay.' = '.$cnG.' игр"';  
        db_query($sql);
            }
          
       //  s('$cn_players='.$cn_players); 
         //  s('$m='.$m);
        //   s('$n='.$n);
           
        }
        
      }  
     } 
     // s($sql);
     // s($aTurnVariants);
          
    }
     function show($sql)
    { //  SystemClass::setAction('anyaction');
      //  SystemClass::setModule('turnirsplayers');
      //    $post_return = 'turnirsplayers|list|wintype=1&turnir_id='.$this->id;
      //  SystemClass::setPost_return($post_return);
    //  s($sql);
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