<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class Import_ligasAction extends ActionModule 
{  
    protected  $content = ''; 
protected  $ligas_session = ''; 
  protected  $is_new_player = 0; // если новые игроки на туринре
  protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
  protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
  protected  $subMenu = array();
  protected  $aWinReit = []; // массив кто хоть раз выиграл с рейтингом с 0 рейтинга
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
   
    function init ()
    {
   /* s($this->module);
    s($this->action);
    s($this->id);
    s($this->aParent); 
    s($this->table_module); 
    s($this->type_module);
    s($this->aEditField );*/
   // s($_POST);
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {

            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        $turnir_id = poste('id');
        $league_id = poste('league_id');
        $this->id = !empty($this->id) ? $this->id : $turnir_id;
        $sql='select * from '.T_TURNIRS.' where id='.$this->id;
        $aTurnir = db_row($sql);
        if (!empty($aTurnir['is_no_send_ligas'])){
            // убрать чуваков с 0 рейтингом
            $this->put_no_reiting();
        }

        $this->get_idTokenGoogle();
         if (!empty($this->ligas_session))
         {
        $this->import_ligas();
        $this->add_players_ligas();
    }else
      window_mess('Не верный логин или пароль лигас');

    $this->list_show_lig();
    }
    function put_no_reiting(){
        // получим игроков без рейтинга и загоним их в массив
        $sql = 'SELECT player_id FROM bs_turnirplayers t, bs_players p WHERE p.id=t.player_id and p.is_team=0  AND p.reiting_ukraine=0 AND t.turnir_id='.$this->id;
        $aNoReiting_ = db_list($sql);
        $aNoReiting = [];
        if (!empty($aNoReiting_)){
            // загоним по ключам в массив чуваков без рейтинга
            foreach ($aNoReiting_ as $item){
                $aNoReiting[$item['player_id']]=1;
            }
        }
        s($aNoReiting);
        // ищем игроков с 0, которые выиграли у чуваков с рейтингом
        $sql = 'SELECT player_id FROM bs_turnirplayers t, bs_players p WHERE p.id=t.player_id and p.is_team=0 AND p.reiting_ukraine=0 
AND exists(SELECT * FROM bs_turnirplayers t1, bs_players p1, bs_reiting r WHERE p1.id=t1.player_id 
AND p1.reiting_ukraine>0 AND t1.turnir_id=t.turnir_id AND r.turnir_id=t1.turnir_id AND r.lose_player=t1.player_id 
AND r.win_player=t.player_id AND (r.set_1<>"W" and r.set_1<>"L"))
AND t.turnir_id='.$this->id;
        $aWinReit_ = db_list($sql);
        $this->aWinReit = [];
        if (!empty($aWinReit_)){
           foreach ($aWinReit_ as $item)
           {

               $this->addNoReitPlayers($item['player_id']);
           }
        }
        s('$aWinReitENDD'); s($this->aWinReit);
        $aResult = array_diff_assoc($aNoReiting,$this->aWinReit);
        s('$aResult'); s($aResult);
        // проставим no_send для игр
        $no_rachet = 1;
        db_query('Update '.T_REITING.' SET no_send=0  where turnir_id='.$this->id);
        if (!empty($aResult)){

            foreach ($aResult as $player => $item)
            {
                // поставим для игрока
             //   db_query('Update '.T_TURNIR_PLAYERS.' SET new_player=1 where turnir_id='.$this->id.' and player_id='.$player);
                ///уберем no_send для пред игр


                // проставим no_send для игр
                $where = '(pl_id_1='.$player.' or  pl_id_2='.$player.') and turnir_id='.$this->id;
             //   s($where);
                db_query('Update '.T_REITING.' SET no_send_auto=1, no_send='.$no_rachet .' where '.$where);
            }
        }


    }
    function addNoReitPlayers($player){
        $this->aWinReit[$player]=1;
    //    s($this->aWinReit);
        $sql = 'SELECT player_id FROM bs_turnirplayers t, bs_players p WHERE p.id=t.player_id and p.is_team=0 AND p.reiting_ukraine=0 
AND exists(SELECT * FROM bs_turnirplayers t1, bs_players p1, bs_reiting r WHERE p1.id=t1.player_id 
AND p1.reiting_ukraine=0 AND t1.turnir_id=t.turnir_id AND r.turnir_id=t1.turnir_id AND r.win_player=t.player_id 
AND r.lose_player='.$player.' AND (r.set_1<>"W" and r.set_1<>"L"))
AND t.turnir_id='.$this->id ;
        s($sql);
        $aLosePlayers=db_list($sql);
     //   s($aLosePlayers);
        if (!empty($aLosePlayers)){
            foreach ($aLosePlayers as $playerLose){
                $new_player = $playerLose['player_id'];
             //   s('new_play='.$new_player);
                if (empty($this->aWinReit[$new_player])) $this->addNoReitPlayers($new_player);
            }
        }
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
    function add_players_ligas()
    {
       // get_idTokenGoogle();
        $sql = 'select turnir_id_ligas,ligas_session from '.T_TURNIRS.' where id='.$this->id;
        $aTurn=db_row($sql);
        if (!empty($aTurn['turnir_id_ligas']) )
        { //&& !empty($aTurn['ligas_session'])
            $sql = 'SELECT p.* FROM '.T_PLAYERS.' p, '.T_TURNIR_PLAYERS.' t where t.player_id=p.id and p.is_team=0 and new_player=0 and t.turnir_id='.$this->id;
      //s($sql);
          $aPlayers = db_list($sql);
            foreach($aPlayers as $aPlay)
            {
                if (!empty($aPlay['id_reiting'])) 
                {
                    $this->addPlayerLigas($aPlay['id_reiting'],$aTurn['turnir_id_ligas'],$aTurn['ligas_session']);
                }
            }
        }
    }
    function get_idTokenGoogle()
    {
         if (!empty($_SESSION['gt']['ligas_login_email']) &&  !empty($_SESSION['gt']['ligas_password']))
        {
        $data = array(
    "email" => $_SESSION['gt']['ligas_login_email'],
    "password" => $_SESSION['gt']['ligas_password'],
    "returnSecureToken" => true
);		
//s($data);

$ch = curl_init('https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=AIzaSyCyn0yQ9XTwcuOaZBnIfpGadaMYSAZkC-I');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$res = curl_exec($ch);
curl_close($ch);
$res = json_decode($res, true);
$this->ligas_session = $res['idToken'];
}
  }  
function addPlayerLigas($id_reiting,$turnir_id_ligas,$ligas_session)
    {
     // get_idTokenGoogle();
      
        $ligas_session = $this->ligas_session;
         $data = array('0'=>$id_reiting);
       $data = json_encode($data); 
       $ch = curl_init();
$headers   = array();
//$headers[] = 'Cookie: ' . $cookie;
$headers[] = 'authorization: Bearer ' . $ligas_session;
$headers[] = 'accept: application/json, text/plain, */*' ;
$headers[] = 'Content-Type: application/json' ;
$headers[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36' ;
$headers[] = 'origin: https://ligas.io' ;
$headers[] = 'Content-Length: '  . strlen($data);

//s($headers);
//s($data);
//print_r($headers);
curl_setopt($ch, CURLOPT_URL, 'https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/participants');
//curl_setopt($ch, CURLOPT_URL, "https://ligas.io/");
//curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=AIzaSyCyn0yQ9XTwcuOaZBnIfpGadaMYSAZkC-I");
//curl_setopt($curl, CURLOPT_USERPWD, "1245640@gmail.com:20112012"); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
//s($headers);
//s($data);
$respond = curl_exec($ch);
curl_close($ch); 
    }
    function import_ligas()
    {
         $sql = 'SELECT p.* FROM '.T_PLAYERS.' p, '.T_TURNIR_PLAYERS.' t where t.player_id=p.id and p.is_team=0 and new_player=0 and t.turnir_id='.$this->id;
       // s($sql);//exit;      
       $aPlayers = db_list($sql);
         foreach($aPlayers as $Player)
       {
       // s($Player);
          if (!empty($Player['id_reiting'])) 
          {
             $aInfo_play_ligas = $this->get_ligs_player($Player['id_reiting']);
             $this->updateStatisticPlayer($Player['id'],$aInfo_play_ligas,$Player);
          }
       }
    
    }
    function get_ligs_player($PlayId)
    {
      //  s($PlayId);
      $url="https://ligas.io/api/organizations/uttf/users/".$PlayId;
$json = file_get_contents($url);
$data = json_decode($json, TRUE); 
$aPlayer=array();
foreach($data['fields'] as $val)
{
  //  print_r($val['key']);
  if (isset($val['value'])){
  if ($val['key']=='expire') $val['value'] =substr($val['value'],0,10);
   $aPlayer[$val['key']]= $val['value'];
   }
}
$aPlayer['fio'] = $aPlayer['surname'].' ' .$aPlayer['name'];  
$aPlayer['ranking'] = isset($aPlayer['ranking'])? $aPlayer['ranking'] : 0;
$aPlayer['sex'] = isset($aPlayer['sex'])? $aPlayer['sex'] : 'm';
    return $aPlayer;
    }
    function updateStatisticPlayer($PlayId,$aPlayer,$Player)
    {
  //  $name_ligas = empty($Player['name_ligas']) ? $aPlayer['fio'] 
   
  //  s($aPlayer);
    $is_opl_reiting = !empty($aPlayer['expire']) ? 1 :0;
    db_query('UPDATE '.T_PLAYERS.' SET 
    name_ligas="'.$aPlayer['fio'].'",
    god_rogd="'.$aPlayer['birthyear'].'",
    city="'.$aPlayer['city'].'",
    sex="'.$aPlayer['sex'].'",
    reiting_ukraine="'.$aPlayer['ranking'].'",
    is_opl_reiting='.$is_opl_reiting.'
     where id='.$PlayId);   
    }
   
      function list_show_lig()
    {   SystemClass::setAction('anyaction');
        SystemClass::setModule('turnirsplayers');
     //  $this->Java_script='reload_page_();';
       parent::list_show();
          $post_return = 'turnirsplayers-list-turnir_id='.$this->id.'&league_id='.poste('league_id');
        SystemClass::setPost_return($post_return);
      
        // SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }
}
//echo 'dsjksd'; 
?>