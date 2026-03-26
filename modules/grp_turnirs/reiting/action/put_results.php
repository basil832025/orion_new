<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class put_resultsAction extends ActionModule 
{  
    protected  $content = ''; 
    protected  $ligas_session = ''; 
  protected  $is_new_player = 0; // если новые игроки на туринре
  protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
  protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
  protected  $ArrEtaps = array();
  protected  $subMenu = array();
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
    //s($_POST);
     $turnir_id = (int)poste('id');
     if ($turnir_id <= 0) {
         $turnir_id = (int)poste('turnir_id');
     }
     if ($turnir_id > 0) {
          $this->id = $turnir_id;
      }

     $this->get_idTokenGoogle();
     if (!empty($this->ligas_session))
         $this->put_results_ligas();
         else
         window_mess('Не верный логин или пароль лигас');
       
    
    $this->list_show_lig();
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
        function get_idTokenGoogle()
    {
      //  s($_SESSION);
        if (empty($_SESSION['gt']['ligas_login_email']) || empty($_SESSION['gt']['ligas_password'])) {
            return;
        }

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
$this->ligas_session = !empty($res['idToken']) ? $res['idToken'] : '';
}
}
    function put_results_ligas()
    {
        $turnir_id = (int)$this->id;
        if ($turnir_id <= 0) {
            $turnir_id = (int)poste('id');
        }
        if ($turnir_id <= 0) {
            $turnir_id = (int)poste('turnir_id');
        }
        if ($turnir_id <= 0) {
            return;
        }

        $this->id = $turnir_id;
        $sql = 'select turnir_id_ligas,ligas_session from '.T_TURNIRS.' where id='.$turnir_id;
        $aTurn=db_row($sql);
        if (!empty($aTurn['turnir_id_ligas']) )
        { //&& !empty($aTurn['ligas_session'])
            $base_sql = 'select (select p.name_ligas from bs_players p where p.id=r.pl_id_1) as participant1Name ,
(r.id) as local_game_id,
(select p.id_reiting from bs_players p where p.id=r.pl_id_1) as participant1,
(select p.name_ligas from bs_players p where p.id=r.pl_id_2) as participant2Name,
(select p.id_reiting from bs_players p where p.id=r.pl_id_2) as participant2,
 r.set_1 ,r.set_2, r.break_1, r.break_2, r.win_player, r.lose_player, r.pl_id_1, r.pl_id_2
 from  bs_reiting r where ((set_1>0 or set_2>0 ) ) and no_send=0
                       AND EXISTS(SELECT * FROM bs_players p WHERE p.is_team=0 AND r.pl_id_1=p.id)
                       and (perenos_etap=0 or perenos_etap is null) and r.turnir_id='.$turnir_id;

            $sql = $base_sql.' and (no_send=0 or no_send is null) ORDER BY id';
          $aResults = db_list($sql);

          if (empty($aResults)) {
              $sql = $base_sql.' ORDER BY id';
              $aResults = db_list($sql);
          }
          $id=1;
          if (!empty($aResults))
          {
            $this->GetProtokolLigas($aTurn['turnir_id_ligas']);
            if (!empty($this->ArrEtaps))
            {
                foreach ($this->ArrEtaps as $key =>$val)
                {
                    if ($val['type']=='protocol') $this->DelProtokolLigas($aTurn['turnir_id_ligas'],$key);
                }
            }
            $this->addProtokolLigas(count($aResults),$aTurn['turnir_id_ligas']);
            foreach($aResults as $aPlay)
            {
                if (!empty($aPlay)) 
                {
                    $this->addPlayerLigas($aPlay,$aTurn['turnir_id_ligas'],$aTurn['ligas_session'],$id);
                }
                $id++;
            }
            }
        }
    }
  
   function GetProtokolLigas($turnir_id_ligas)
    {
      $ligas_session = $this->ligas_session;  
if (!empty($ligas_session))
{
 //s($data);
       //     $data = json_encode($data); 
         //   s($data);
       $ch = curl_init();
$headers   = array();
//$headers[] = 'Cookie: ' . $cookie;
$headers[] = 'authorization: Bearer ' . $ligas_session;
$headers[] = 'accept: application/json, text/plain, */*' ;
$headers[] = 'Content-Type: application/json' ;
$headers[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36' ;
$headers[] = 'origin: https://ligas.io' ;
//$headers[] = 'Content-Length: '  . strlen($data);

//s($headers);
//s('https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/0');
curl_setopt($ch, CURLOPT_URL, 'https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages');

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$respond = curl_exec($ch);
$respond = json_decode($respond, true);
$this->ArrEtaps=$respond;
//s('get');
//ss($respond);
curl_close($ch); 
}
    }    

   function DelProtokolLigas($turnir_id_ligas,$id)
    {
      $ligas_session = $this->ligas_session;  
//s('$id='.$id);
 //s($data);
       //     $data = json_encode($data); 
         //   s($data);
       $ch = curl_init();
$headers   = array();
//$headers[] = 'Cookie: ' . $cookie;
$headers[] = 'authorization: Bearer ' . $ligas_session;
$headers[] = 'accept: application/json, text/plain, */*' ;
$headers[] = 'Content-Type: application/json' ;
$headers[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36' ;
$headers[] = 'origin: https://ligas.io' ;
//$headers[] = 'Content-Length: '  . strlen($data);

//s($headers);
//s('https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/0');
curl_setopt($ch, CURLOPT_URL, 'https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/'.$id);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$respond = curl_exec($ch);
//$respond = json_decode($respond, true);
//$this->ArrEtaps=$respond;
//s('get');
//ss($respond);
curl_close($ch); 
    }       
  function addProtokolLigas($cntGames,$turnir_id_ligas)
    {
      $ligas_session = $this->ligas_session;  
       // s($aPlay);
     //   $status= ($aPlay['set_1']>$aPlay['set_2']) ? 3 : 4;
        
  $data = array(
  "size"=> $cntGames,
  "final"=> false,
  "placesShift"=> 0,
  "groupsCount"=> 0,
  "winPoints"=> 2,
  "drawPoints"=> 1,
  "lossPoints"=> 0,
  "refusePoints"=> 0,
  "groups"=> [],
  "name"=> "Протокол",
  "type"=> "protocol",
  "_loading"=> true
);

 //s($data);
            $data = json_encode($data); 
         //   s($data);
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
//s('https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/0');
curl_setopt($ch, CURLOPT_URL, 'https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/0');

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$respond = curl_exec($ch);
//s('$respond');
//s($respond);
curl_close($ch); 
    }   
    
    function addPlayerLigas($aPlay,$turnir_id_ligas,$ligas_session,$id)
    {
      $ligas_session = $this->ligas_session;  
      if (empty($aPlay['participant1']) || empty($aPlay['participant2'])) {
          return;
      }

      $set_1 = trim((string)$aPlay['set_1']);
      $set_2 = trim((string)$aPlay['set_2']);
      if (($set_1 === '' || $set_1 === '0') && ($set_2 === '' || $set_2 === '0')) {
          if (!empty($aPlay['break_1']) && empty($aPlay['break_2'])) {
              $set_1 = 'L';
              $set_2 = 'W';
          } elseif (!empty($aPlay['break_2']) && empty($aPlay['break_1'])) {
              $set_1 = 'W';
              $set_2 = 'L';
          } elseif (!empty($aPlay['win_player']) && !empty($aPlay['lose_player'])) {
              if ((int)$aPlay['win_player'] === (int)$aPlay['pl_id_1']) {
                  $set_1 = 'W';
                  $set_2 = 'L';
              } elseif ((int)$aPlay['win_player'] === (int)$aPlay['pl_id_2']) {
                  $set_1 = 'L';
                  $set_2 = 'W';
              }
          }
      }

      if ($set_1 === 'W' || $set_2 === 'L' || ((is_numeric($set_1) && is_numeric($set_2) && (float)$set_1 > (float)$set_2))) {
          $status = 3;
      } else {
          $status = 4;
      }

        $data = array(
 "stageIndex"=>0, 
 "id"=>$id,
 "index"=>0,
 "stageName"=>"результаты",
 "seeding1"=>0,
 "seeding2"=>0,
 "iteration"=>0,
 "round"=>-1,
 "place"=> null,
 "status"=>$status,
 "participant1"=>$aPlay['participant1'],
 "participant2"=>$aPlay['participant2'],
 "participant1Name"=>$aPlay['participant1Name'],
 "participant2Name"=>$aPlay['participant2Name'],
 "place"=>null,
 "result"=>$set_1.':'.$set_2,
 
 );
 //s($data);
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

//print_r($headers);
curl_setopt($ch, CURLOPT_URL, 'https://ligas.io/api/tournaments/'.$turnir_id_ligas.'/stages/0/games/'.$id);
//curl_setopt($ch, CURLOPT_URL, "https://ligas.io/");
//curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=AIzaSyCyn0yQ9XTwcuOaZBnIfpGadaMYSAZkC-I");
//curl_setopt($curl, CURLOPT_USERPWD, "1245640@gmail.com:20112012"); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$respond = curl_exec($ch);
curl_close($ch);
    }
  
      function list_show_lig()
    {
        $turnir_id = (int)$this->id;
        if ($turnir_id <= 0) {
            $turnir_id = (int)poste('id');
        }
        if ($turnir_id <= 0) {
            $turnir_id = (int)poste('turnir_id');
        }
        if ($turnir_id <= 0) {
            return;
        }

        $this->id = $turnir_id;
        // После выгрузки возвращаемся к списку игр текущего турнира,
        // а не к модулю players (иначе в reiting подтягивается players.where).
        SystemClass::setAction('list');
        SystemClass::setModule('reiting');
        $_POST['turnir_id'] = $turnir_id;

        $league_id = (int)poste('league_id');
        if ($league_id <= 0) {
            $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
        }
        if ($league_id > 0) {
            $_POST['league_id'] = $league_id;
        }

      //  $this->Java_script='reload_page_();';
        parent::list_show();

        $post_no_ma = 'turnir_id='.$turnir_id.($league_id > 0 ? '&league_id='.$league_id : '');
        SystemClass::setPost_return_noMA('&'.$post_no_ma);
        SystemClass::setPost_return('reiting-list-'.$post_no_ma);
      
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
