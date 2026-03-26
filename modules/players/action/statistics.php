<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class StatisticsAction extends ActionModule
{  protected  $content = '';
    protected  $subMenu = array();
    protected  $subMenu2 = array();
    protected  $aResults = array(); // результат игор для таблиц
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    protected  $etap_id = 0; //
    protected  $turnir_id = 0; //
    protected  $page_number = 0; //
    protected  $page_count = 100; //

    function init ()
    {
        //    s('ststa');
        $id=poste('id');
        $compare_id=poste('compare_id');
  //      s('$compare_id='.$compare_id.' $id='.$id);
        $page_number=poste('page_number');
    //    $page_count=poste('page_items');
        $page_number = !empty($page_number) ? $page_number : 1;
        $this->page_number = $page_number;
      //  $this->page_count = !empty($page_count) ? $page_count : 10;
      //  s($_POST);
        $txt_sql='';
        if (!empty($compare_id)) $txt_sql = '   (SELECT COUNT(*) from bs_reiting t WHERE ((pl_id_1='.$id.' AND pl_id_2='.$compare_id.') OR  (pl_id_1='.$compare_id.' AND pl_id_2='.$id.')) AND win_player='.$id.') as cnPlay_win, 
        (SELECT COUNT(*) from bs_reiting t WHERE ((pl_id_1='.$id.' AND pl_id_2='.$compare_id.') OR  (pl_id_1='.$compare_id.' AND pl_id_2='.$id.')) AND lose_player='.$id.') as cnPlay_lose, ';
        $sql = 'select (select f.name from '.T_FILES.' f where f.id=p.photo) as photo_name ,
        (select value from `'.T_SPRLIST_VALUES.'` sv where sv.id=p.grp) as grp_name,
        (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$id.' AND mesto=1 order by id) as mesto1, 
        (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$id.' AND mesto=2 order by id) as mesto2, 
        (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$id.' AND mesto=3 order by id) as mesto3,
        '.$txt_sql.'
        p.* from '.T_PLAYERS. ' p where id='.$id;
        $aPlayer = db_row($sql);
        $zagl = !empty($_SESSION['is_mobile']) ? $aPlayer['name'] : '';
        $sql ='SELECT t.dat,t.name,t.league_id AS tur_league_id,tp.* from '.T_TURNIR_PLAYERS.' tp , '.T_TURNIRS.' t 
        WHERE t.date_raschet IS not null and t.id=tp.turnir_id AND tp.player_id='.$id.' order by dat desc,t.id desc';
        $aTurnirs = db_list($sql,$page_number,0,0,'&id='.$id);
       // s($sql);
       // s($aTurnirs);
        //   s($aPlayer);

        if ($_SESSION['is_mobile'] ){
            $show_zag_left='show_zag_left("#players-list");';
            $width = '100%';
        }else{
            $width = '425px';
            $show_zag_left='show_zag_center();';
            $show_zag_left = !empty($compare_id) ? $show_zag_left.'show_zag_left_big("#players-list");' : $show_zag_left;
        }
        if (empty($compare_id))
        {
            $jdata = array(
                ['Дата', 'Рейтинг']
            );
            $cn = count($aTurnirs);
            for ($i=$cn-1;$i>=0; $i--) {
                if ($cn==0  || ($i>=0 && round($aTurnirs[$i]['end_reiting'])>0))
                if (!empty($aTurnirs[$i]['end_reiting']) && !empty($aTurnirs[$i]['dat']))
                    $jdata[]  =[date_for_firebird_format($aTurnirs[$i]['dat']),round($aTurnirs[$i]['end_reiting'])];
            }
            //      s($jdata);
            $jsonData = json_encode($jdata);

              // s($jsonData);
            if (!empty($aTurnirs))
            $this->Java_script.='jsonData='.$jsonData.'; player_graphik();chosen_vibor("'.$width.'","Гравець для порівняння");'.$show_zag_left;
            else
                    $this->Java_script.='chosen_vibor("'.$width.'","Гравець для порівняння");'.$show_zag_left;


        }else       $this->Java_script.=$show_zag_left;

        // s($this->Java_script);
        SystemClass::setJava_script($this->Java_script);
        if (!empty($compare_id))
        {
            $sql = 'select (select f.name from '.T_FILES.' f where f.id=p.photo) as photo_name ,
        (select value from `'.T_SPRLIST_VALUES.'` sv where sv.id=p.grp) as grp_name,
          (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$compare_id.' AND mesto=1 order by id) as mesto1, 
        (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$compare_id.' AND mesto=2 order by id) as mesto2, 
        (SELECT COUNT(*) AS cn from bs_turnirplayers t where player_id='.$compare_id.' AND mesto=3 order by id) as mesto3, 
       (SELECT COUNT(*) from bs_reiting t WHERE ((pl_id_1='.$id.' AND pl_id_2='.$compare_id.') OR  (pl_id_1='.$compare_id.' AND pl_id_2='.$id.')) AND win_player='.$compare_id.') as cnPlay_win, 
        (SELECT COUNT(*) from bs_reiting t WHERE ((pl_id_1='.$id.' AND pl_id_2='.$compare_id.') OR  (pl_id_1='.$compare_id.' AND pl_id_2='.$id.')) AND lose_player='.$compare_id.') as cnPlay_lose, 
      
        p.* from '.T_PLAYERS. ' p where id='.$compare_id;
            s($sql);
            $aCompare = db_row($sql);
            $this->content='<div class="container">';
            $this->ComparePlayer($aPlayer,$aCompare);
            $sql ='SELECT t.dat,t.name,
       (SELECT NAME FROM bs_players p WHERE p.id=pl_id_1) AS name_1,
(SELECT NAME FROM bs_players p WHERE p.id=pl_id_2) AS name_2,
       r.* from '.T_REITING.' r , '.T_TURNIRS.' t WHERE  t.id=r.turnir_id and 
             ((pl_id_1='.$id.' AND pl_id_2='.$compare_id.') OR  (pl_id_1='.$compare_id.' AND pl_id_2='.$id.')) 
             AND win_player>0  order by dat,t.id';
            $aTurnirs = db_list($sql,$page_number,0,0,'&id='.$id);
s($sql);
            $this->content .= $this->getTurnirsCompare($id,$aTurnirs);
            $this->content.='</div>';
            $_SESSION['MESSAGE_AJAX']='';

        }
        else
        {   $this->content='<div class="container">';
            $this->MainPlayer($aPlayer);
            $this->content .= $this->getTurnirs($aTurnirs);
            $this->content.='</div>';
            $sql = 'SELECT * FROM `' . T_PLAYERS .
                '` where id<>'.$id.' and not_use=0 and ispara=0  ORDER by name';
            $aPlayers = db_list($sql);
            $left_zag =  (!$_SESSION['is_mobile'] )  ? '<div class="stat_zag_left ajax_send" href="#players-list"> <img src="img\left_zn_poisk.png" width="24px"></div>' : '';
            $sSpisPlayer = $left_zag.'  <select class="chosen-select " tabindex="5" name=fio_search" id="ComparePlayer">';
            foreach ($aPlayers as $player)
            {
                // s($player);
                $selected ='';

                $strReiting =  !empty($player['reiting']) || $player['reiting_ukraine']>0 ?
                    '('.$player['reiting'].'-РКлубу)' : '';
                $sSpisPlayer.='
        <option selected="selected" id="opt_0'.'" value="0"></option>';

                $sSpisPlayer.='
        <option '.$selected.' id="opt_'.$player['id'].'" value="'.$player['id'].'">'.$player['name'].' <span class="f10">'.$strReiting.'</span></option>';

            }
            $sSpisPlayer.=  '</select>';

            $_SESSION['MESSAGE_AJAX']=$sSpisPlayer;

        }



        // s($content);
        //     SystemClass::setPost_return($post_return);

        if (!empty($compare_id)){
            if ($_SESSION['is_mobile'] ){
                $zagl2= '<div class="compare_zagl">'.$aPlayer['name'].' VS<br>'.$aCompare['name'].'</div>';

            }else{
                $zagl2 ='<div class="poriv_zag"> Порівняння статистики гравців :: '.$aPlayer['name'].' VS '.$aCompare['name'].'</div>';
            }
            SystemClass::setZaglModule($zagl2);
        }

        else
        SystemClass::setZaglModule($zagl);
     //   SystemClass::setZaglModule('Статистика по гравцю :: '.$aPlayer['name']);
        /*     $submenu_list =array(
                 //filter' => array('module' => 'tovs'),
                 'back' => array('module' => 'settings', 'action' => 'show',  'post' => ''),
                 'filter' => array('menu_name'=>'Експорт в Excel результатів',
                     'http' => 'modules/reports/action/toexcel_counts_turnirs.php?dbeg='.$dbeg.'&dend='.$dend)

             );*/
        //     SystemClass::$submenu = $submenu_list;
       // $post_return = 'reports-counts_turnirs-type=';
        //SystemClass::setPost_return($post_return);
        $tct_compare = !empty($compare_id) ? '&compare_id='.$compare_id : '';
        $post_return = 'players-statistics-id='.$id.$tct_compare;
        //    s('$post_return='.$post_return);
        SystemClass::setPost_return($post_return);
        //   SystemClass::setZaglModule('Звіт::Кількість відвідуваннь турнірів');
        $submenu_list =array(
            //filter' => array('module' => 'tovs'),
        //    'back' => array('module' => 'players', 'action' => 'list',  'post' => ''),

        );
        SystemClass::$submenu = $submenu_list;


    }

    function ComparePlayer1 ($aPlayer=[])
    {
        $birthd = substr($aPlayer['birthday'],0,4);
        if ($birthd!='0000')
        {
            //   s($birthd);
            $date = new DateTimeImmutable($aPlayer['birthday']);
            $birthd =   $date->format('Y');
        }else
            $birthd = $aPlayer['god_rogd'];
        $sex = !empty($aPlayer['sex']) && $aPlayer['sex']=='f' ? 'f' : 'm';
        $photo = 'img/no_photo_'.$sex.'.png';
        if (!empty($aPlayer['photo_name'])) {
            $photo = "uploads/files_site/mini/".$aPlayer['photo_name'];
        }else if (!empty($aPlayer['ligas_photo']))
            $photo = $aPlayer['ligas_photo'];
        if (!$_SESSION['is_mobile'] ){
            $imgsize=160;
            $width_med = '40px';
            $aPlayer['name'] = str_replace('    ',' ',trim($aPlayer['name']));
            $aPlayer['name'] = str_replace('   ',' ',trim($aPlayer['name']));
            $aPlayer['name'] = str_replace('  ',' ',trim($aPlayer['name']));
            $aPlayer['name'] = str_replace(' ','<br>',trim($aPlayer['name']));
        }else{
            $width_med = '32px';

            $imgsize=103;
        }
        $birthd = !empty($birthd) ? $birthd :'';
        $proc_wins = !empty($aPlayer['proc_wins']) ? $aPlayer['proc_wins'] :'0';

        $content= '
<div class="container comparing_player">
                <div class="row">
                    <div class="col-4">
                       <div class="avatar-wrapper" style="width: '.$imgsize.'px; height: '.$imgsize.'px;  ">
             <img class="avatarplayer" width="'.$imgsize.'px" height="'.$imgsize.'px"  src="'.$photo.'" style="opacity: 1;">
        </div> 
                    </div>
                    <div class="col-8 ">
                    <div class="info-profile1 ">
                 
          <h1 class="info-value"><div class="name">'.$aPlayer['name'].'</div></h1>
         <div class="info-cont">
         <div class="info-descr">Група: '.$aPlayer['grp_name'].'</div>
         <div class="info-descr">Рік народження: '.$birthd.'</div>
         <div class="info-descr">Місто: '.$aPlayer['city'].'</div>
         </div> 
                    </div>
                    </div>
                
                </div>
                <div class="row ">
                <div class="lineup"></div>
                    <div class="col-4">
                     <div class="medal-set2">
            <div class="badge2 medal">
            <img src="img/1mesto6.png" width="'.$width_med.'">
           
            <span class="score"> - '.$aPlayer['mesto1'].'</span> </div>
            <div class="badge2 medal">
            <img src="img/2mesto6.png" width="'.$width_med.'">
          <span class="score"> - '.$aPlayer['mesto2'].'</span></div>
            <div class="badge2 medal">
            <img src="img/3mesto6.png" width="'.$width_med.'">
            <span class="score"> - '.$aPlayer['mesto3'].'</span></div>
           
         </div> 
                    </div>
                    <div class="col-8">
            <div class="info-wrapper1">
           <div class="all-matches">
             <span class="info-descr">Зіграно турнірів:</span>
             <span class="info-value">'.$aPlayer['cnt_turnirs'].'</span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Зіграно ігор:</span>
                <span class="info-value">'.$aPlayer['cnt_games'].' <i class="i-arrowforward"></i></span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Перемог:</span>
                <span class="info-value ">'.$aPlayer['cnt_wins'].'</span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Поразок:</span>
                <span class="info-value ">'.$aPlayer['cnt_lose'].'</span>
            </span>
         </div>
         <div class="all-matches">
         <span class="stats-wrap">
                <span class="info-descr">Процент перемог:</span>
                <span class="info-value">'.$proc_wins.'%</span>
            </span>
        </div>
        
    </div>
            <div class="info-reiting1">
       <div class="badge-wrapper">
            <span >Рейтинг клубу: '.round($aPlayer['reiting']).'</span>
        </div>
        <div class="badge-wrapper">
            <span >Рейтинг ФНТУ: '.$aPlayer['reiting_ukraine'].'</span>
         </div>
         </div>
                    </div>
                </div>
            ';
        if (!$_SESSION['is_mobile'] )
        $content.= '
<div class="row">
<div class="container comparing_player_megdu">
                <div class="row">
                    <div class="col">
                     <div class="badge-wrapper">
            <span >Статистика між гравцями:</span>
        </div>
        <div class="stat_megd">
                            <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Перемог - </span>
                <span class="info-value_win ">'.$aPlayer['cnPlay_win'].'</span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Поразок - </span>
                <span class="info-value ">'.$aPlayer['cnPlay_lose'].'</span>
            </span>
         </div>
         </div>
                    </div>
                    </div>
             </div>
             </div>
            ';
        $content.= '</div>';
        return $content;
    }
    function ComparePlayer ($aPlayer=[],$aCompare=[])
    {
        $birthd = substr($aPlayer['birthday'],0,4);
        if ($birthd!='0000')
        {
            //   s($birthd);
            $date = new DateTimeImmutable($aPlayer['birthday']);
            $birthd =   $date->format('Y');
        }else
            $birthd = $aPlayer['god_rogd'];

        $birthd2 = substr($aCompare['birthday'],0,4);
        if ($birthd2!='0000')
        {
            //   s($birthd);
            $date = new DateTimeImmutable($aCompare['birthday']);
            $birthd2 =   $date->format('Y');
        }else
            $birthd2 = $aCompare['god_rogd'];
        $photo = 'img/no-photo-icon-22.png';
        $photo2 = 'img/no-photo-icon-22.png';
        if (!empty($aPlayer['photo_name'])) {
            $photo = "uploads/files_site/mini/".$aPlayer['photo_name'];
        }else if (!empty($aPlayer['ligas_photo']))
            $photo = $aPlayer['ligas_photo'];
        if (!empty($aCompare['photo_name'])) {
            $photo2 = "uploads/files_site/mini/".$aCompare['photo_name'];
        }else if (!empty($aCompare['ligas_photo']))
            $photo2 = $aCompare['ligas_photo'];
        if ($_SESSION['is_mobile'] )
            $this->ComparePlayerHtmlMob($aPlayer,$aCompare);
       else
        $this->ComparePlayerHtml($aPlayer,$aCompare);
//s('sdsds');



    }
    function stat_players($aPlayer,$aCompare)
    {
        /*       <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Поразок - </span>
                <span class="info-value_lose ">'.$aPlayer['cnPlay_lose'].'</span>
            </span>
         </div>*/
        $content= '
            <div class="badge-wrapperStat">
            <span >Статистика між гравцями:</span>
        </div> 
        <div class="container comparing_player_megdu">
                <div class="row align-items-center">
                    <div class="col ">
                  <div class="all-matches  align-items-center justify-content-center">
                  <div class="all_matches_inner">
            <span class="stats-name align-middle">
               '.$aPlayer['name'].'
            </span>
            </div>
         </div>         
        <div class="stat_megd">
                            <div class=" align-middle text-center">
            <span class="stats-wrap">
                 <span class="info-value_win ">'.$aPlayer['cnPlay_win'].'</span>
            </span>
         </div>
  
         </div>
                    </div>
                    
                    
                    
                          <div class="col">
                  <div class="all-matches align-items-center justify-content-center">
                   <div class="all_matches_inner">
            <span class="stats-name align-middle">
               '.$aCompare['name'].'
            </span>
         </div>         
         </div>         
        <div class="stat_megd">
                            <div class=" align-middle text-center">
            <span class="stats-wrap">
                 <span class="info-value_win ">'.$aCompare['cnPlay_win'].'</span>
            </span>
         </div>
       
         </div>
                    </div>
                    </div>
             </div>';
        return $content;
    }
    function ComparePlayerHtmlMob($aPlayer,$aCompare){
        $this->content='
         <section class="player-card-wrapper_compare">
    <div class="player-card container">
    <div class="row justify-content-center">
        <div class="col">
            '.$this->ComparePlayer1($aPlayer).'
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col vs_vertalighn ">
            <div class="VS">VS</div>
        </div>
        </div>
        <div class="row">
        <div class="col">
            '.$this->ComparePlayer1($aCompare).'
        </div>
        </div>
         <div class="row">
        <div class="col">
            '.$this->stat_players($aPlayer,$aCompare).'
        </div>
        </div>
     </div>
     </section>
        ';
    }
    function ComparePlayerHtml($aPlayer,$aCompare){
        $this->content.='
 <div class="row justify-content-center">
        <div class="col">
    <div class="player-card">
   
            '.$this->ComparePlayer1($aPlayer).'
      
        <div class="vs_vertalighn ">
            <span class="VS">VS</span>
        </div>
            '.$this->ComparePlayer1($aCompare).'
        </div>
        </div>
     </div>
    
        ';
    }

    function MainPlayerHtml ($aPlayer=[]){
      //  s($aPlayer);
        $is_pol_vnesok = !empty($aPlayer['is_opl_reiting']) || $aPlayer['rokiv']<18 ? '<img src="img/active.png" width="20px">': '<img src="img/delete.gif" width="20px">';
        $birth = !empty($aPlayer['birthd']) ? $aPlayer['birthd'] :'';
        $proc_wins = !empty($aPlayer['proc_wins']) ? $aPlayer['proc_wins'] :'0';
        $this->content.='
 <section class="player-card-wrapper">
    <div class="player-card player-card1 container">
    <div class="row">
        <div class="col-5">
            <div class="container">
                <div class="row">
                    <div class="col-4">
                       <div class="avatar-wrapper" style="width: 160px; height: 160px; ">
             <img class="avatarplayer" width="160px" height="160px" src="'.$aPlayer['photo'] .'" style="opacity: 1;">
        </div> 
                    </div>
                    <div class="col-8 ">
                    <div class="info-profile ">
                 
          <h1 class="info-value"><div class="name">'.$aPlayer['name'].'</div></h1>
         <div class="info-cont">
         <div class="info-descr">Група: '.$aPlayer['grp_name'].'</div>
         <div class="info-descr">Рік народження: '.$birth.'</div>
         <div class="info-descr">Місто: '.$aPlayer['city'].'</div>
         <div class="info-descr">ID Ligas: '.$aPlayer['id_reiting'].'</div>
         <div class="info-descr">Cплачений внесок: '.$is_pol_vnesok.'</div>
         </div> 
                    </div>
                    </div>
                
                </div>
                <div class="row ">
                <div class="lineup"></div>
                    <div class="col-4">
                     <div class="medal-set2">
            <div class="badge2 medal">
            <img src="img/1mesto6.png" width="40px">
           
            <span class="score"> - '.$aPlayer['mesto1'].'</span> </div>
            <div class="badge2 medal">
            <img src="img/2mesto6.png" width="40px">
          <span class="score"> - '.$aPlayer['mesto2'].'</span></div>
            <div class="badge2 medal">
            <img src="img/3mesto6.png" width="40px">
            <span class="score"> - '.$aPlayer['mesto3'].'</span></div>
           
         </div> 
                    </div>
                    <div class="col-8">
            <div class="info-wrapper">
           <div class="all-matches">
             <span class="info-descr">Зіграно турнірів:</span>
             <span class="info-value">'.$aPlayer['cnt_turnirs'].'</span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Зіграно ігор:</span>
                <span class="info-value">'.$aPlayer['cnt_games'].' <i class="i-arrowforward"></i></span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Перемог:</span>
                <span class="info-value ">'.$aPlayer['cnt_wins'].'</span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Поразок:</span>
                <span class="info-value ">'.$aPlayer['cnt_lose'].'</span>
            </span>
         </div>
         <div class="all-matches">
         <span class="stats-wrap">
                <span class="info-descr">Процент перемог:</span>
                <span class="info-value">'.$proc_wins.'%</span>
            </span>
        </div>
        
    </div>
            <div class="info-reiting">
       <div class="badge-wrapper">
            <span >Рейтинг клубу: '.round($aPlayer['reiting']).'</span>
        </div>
        <div class="badge-wrapper">
            <span >Рейтинг ФНТУ: '.$aPlayer['reiting_ukraine'].'</span>
         </div>
         </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-7">
        <div class="info-wrapper_gr">
        <div class="name20">Приріст рейтингу гравця</div>
    <div id="player_chart" style="width: 813px; height: 400px"></div>

    </div>
        </div>
    
    </div>
    </div>
 </section>
';
    }
    function MainPlayerHtmlMob ($aPlayer=[]){
      //  s($aPlayer);
        $width_graphik = $_SESSION['width_body']<360 ? 330 : 390;
        $is_pol_vnesok = !empty($aPlayer['is_opl_reiting']) || $aPlayer['rokiv']<18  ? '<img src="img/active.png" width="20px">': '<img src="img/delete.gif" width="20px">';
        $birth = !empty($aPlayer['birthd']) ? $aPlayer['birthd'] :'';
        $proc_wins = !empty($aPlayer['proc_wins']) ? $aPlayer['proc_wins'] :'0';
        $this->content='

 <section class="player-card-wrapper">
    <div class="player-card player-card1 container">
    <div class="row">
        <div class="col">
            <div class="container">
                <div class="row mob_card_player">
                    <div class="col-4">
                       <div class="avatar-wrapper" style="width: 103px; height: 103px; ">
             <img class="avatarplayer" width="103px" height="103px" src="'.$aPlayer['photo'] .'" style="opacity: 1;">
        </div> 
                    </div>
                    <div class="col-8 ">
                    <div class="info-profile ">
                 
          <h1 class="info-value"><div class="name">'.$aPlayer['name'].'</div></h1>
         <div class="info-cont">
         <div class="info-descr">Група: '.$aPlayer['grp_name'].'</div>
         <div class="info-descr">Рік народження: '.$birth.'</div>
         <div class="info-descr">Місто: '.$aPlayer['city'].'</div>
         <div class="info-descr">ID Ligas: '.$aPlayer['id_reiting'].'</div>
         <div class="info-descr">Cплачений внесок: '.$is_pol_vnesok.'</div>
         </div> 
                    </div>
                    </div>
                
                </div>
                <div class="row ">
                <div class="lineup"></div>
                    <div class="col-4">
                     <div class="medal-set2">
            <div class="badge2 medal">
            <img width="32px" src="img/1mesto6.png" width="40px">
           
            <span class="score"> - '.$aPlayer['mesto1'].'</span> </div>
            <div class="badge2 medal">
            <img width="32px" src="img/2mesto6.png" width="40px">
          <span class="score"> - '.$aPlayer['mesto2'].'</span></div>
            <div class="badge2 medal">
            <img width="32px" src="img/3mesto6.png" width="40px">
            <span class="score"> - '.$aPlayer['mesto3'].'</span></div>
           
         </div> 
                    </div>
                    <div class="col-8">
            <div class="info-wrapper">
           <div class="all-matches">
             <span class="info-descr">Зіграно турнірів:</span>
             <span class="info-value">'.$aPlayer['cnt_turnirs'].'</span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Зіграно ігор:</span>
                <span class="info-value">'.$aPlayer['cnt_games'].' <i class="i-arrowforward"></i></span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Перемог:</span>
                <span class="info-value ">'.$aPlayer['cnt_wins'].'</span>
            </span>
         </div>
         <div class="all-matches">
            <span class="stats-wrap">
                <span class="info-descr">Поразок:</span>
                <span class="info-value ">'.$aPlayer['cnt_lose'].'</span>
            </span>
         </div>
         <div class="all-matches">
         <span class="stats-wrap">
                <span class="info-descr">Процент перемог:</span>
                <span class="info-value">'.$proc_wins.'%</span>
            </span>
        </div>
        
    </div>
            <div class="info-reiting">
       <div class="badge-wrapper">
            <span >Рейтинг клубу: '.round($aPlayer['reiting']).'</span>
        </div>
        <div class="badge-wrapper">
            <span >Рейтинг ФНТУ: '.$aPlayer['reiting_ukraine'].'</span>
         </div>
         </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-7">
      
        </div>
    
    </div>
    <div class="row">
    <div class="col">
      <div class="info-wrapper2">
        <div class="name20">Приріст рейтингу гравця</div>
    <div id="player_chart" style="width: '.$width_graphik.'px; height: 250px"></div>

    </div>
</div>
    </div>
 </section>
';
    }
    function MainPlayer ($aPlayer=[])
    {
        $birthday = isset($aPlayer['birthday']) ? (string)$aPlayer['birthday'] : '';
        $birthd = $birthday !== '' ? substr($birthday,0,4) : '';
        if ($birthd!='0000' && $birthday !== '')
        {
            try {
                $date = new DateTimeImmutable($birthday);
                $aPlayer['birthd'] =   $date->format('Y');
            } catch (Exception $e) {
                $aPlayer['birthd'] = !empty($aPlayer['god_rogd']) ? $aPlayer['god_rogd'] : date('Y');
            }
        }else
            $aPlayer['birthd'] = !empty($aPlayer['god_rogd']) ? $aPlayer['god_rogd'] : date('Y');
        $today_Y = date("Y");
        $aPlayer['rokiv'] = $today_Y - $aPlayer['birthd'];
        $sex = !empty($aPlayer['sex']) && $aPlayer['sex']=='f' ? 'f' : 'm';
        $aPlayer['photo'] = 'img/no_photo_'.$sex.'.png';
        if (!empty($aPlayer['photo_name'])) {
            $aPlayer['photo']  = "uploads/files_site/mini/".$aPlayer['photo_name'];
        }else if (!empty($aPlayer['ligas_photo']))
            $aPlayer['photo']  = $aPlayer['ligas_photo'];
        $aPlayer['id_reiting'] = !empty($aPlayer['id_reiting']) ? $aPlayer['id_reiting'] : '-';
//s('sdsds');

        if ($_SESSION['is_mobile'] )
        $this->MainPlayerHtmlMob($aPlayer);
        else
        $this->MainPlayerHtml($aPlayer);

    }
    function getTurnirsCompare($id,$aTurnirs=[]){
        //  <th scope="col" class="text-center align-middle">#</th>
        //
        if ($_SESSION['is_mobile'] ) {
            $cl_font='fs14';
            $name_dat='Дата<br>турніра';
        } else{
            $name_dat='Дата турніра';
            $cl_font='f12';
        }
        $content='<div class="otst_nameTabl"> <h4 class="text-center pad_cen_game_name"><span class=" text-dark ">Ігри між гравцями</span></h4></div> ';
        $content .='

<div class="container table_megdu_players"><table class="table table-condensed bordered3 table-hover table-bordered    border-light-subtle">
        
  <thead class="th_color_rose">
    <tr class="h-50">
      <th scope="col" class="h-50 text-center align-middle f12 ws30 fw600 p0" >'.$name_dat.'</th>
      <th scope="col" class=" h-50 text-center align-middle f12 fw600">Назва турніру</th>
      <th scope="col" class="h-50 text-center align-middle f12 ws140 fw600">Переможець</th>

    
    </tr> 
   
  </thead>
  <tbody>';

        $content1='
<h4 class="text-center m-2"><span class="badge bg-light text-dark ">Ігри між гравцями</span></h4> ';
        $content1 .='<table class="table">
        
  <thead>
    <tr>
      <th scope="col">Дата<br>турніра</th>
      <th scope="col" class="text-center">Назва турніру</th>
      <th scope="col" class="text-center">Переможець</th>
    </tr> 
   
  </thead>
  <tbody>';

        //   $n=1*$this->page_number;
        $n = ($this->page_number==1) ? 1 : ($this->page_number-1)*$_SESSION[$this->module][$this->action]['page_items']+1;

        $name='';$sm_turnirs=0;
        $cn_players=0;
        foreach ($aTurnirs as $user)
        {
            if ($user['pl_id_1']==$id) {
                $set = $user['set_1'] .':'.$user['set_2'];
                $name = $user['name_1'];
            } else
            {   $name = $user['name_2'];
                $set = $user['set_2'] .':'.$user['set_1'];
            }
            if ($user['win_player']==$user['pl_id_1']) {
                $name = $user['name_1'];
            } else
            {   $name = $user['name_2'];
            }
            $content.='<tr class="fs14 ">
      <td class="text-center align-middle pddat"><span class="break '.$cl_font.'">'.date_for_firebird_format($user['dat']).'</span></td>
      <td ><a target="_blank" class="fs14" href="#etapresult-show-turnir_id='.$user['turnir_id'].'">'.$user['name'].'</a></td>
      <td class="text-center align-middle"><span class="'.$cl_font.' fs14" >'.$name.'<br>'.$set.'</span></td>
      
    </tr>';
            $n++;
        }
        $content .='</tbody>
</table>
  ';
        //  ;
        $content .= (!empty($_SESSION['pagging_html']) ? $_SESSION['pagging_html'] : '').'
</div>';
        return $content;
    }
    function getTh_table(){
        if ($_SESSION['is_mobile'] ){
            return  $content ='<div class="big-table">
<div class="container-fluid">
<table class="table bordered2 table-sm table-hover table-bordered table_mob_turn rounded-pill  border-light-subtle">
        
  <thead class="th_color_rose "> <tr class="hMob_stat">
      <th scope="col" class="text-center align-middle">&nbsp;&nbsp;&nbsp;№&nbsp;&nbsp;</th>
      <th scope="col" class="px_my-2 text-center align-middle">Дата<br>турніра</th>
      <th scope="col"  class="text-start align-middle "><span class="pdl4"> Назва турніру</span></th>
      <th scope="col"  class="text-center align-middle"><span class="rotate-sm-90"> Початко<br>вий р-нг</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">Кінцевий<br>р-нг</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">Приріст<br>р-нгу</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">К-ть<br>матчів</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">К-ть<br>перемог</span></th>
      <th scope="col"   class="text-center align-middle" ><span class="rotate-sm-90">К-сть<br>поразок</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">К-сть<br>сетів</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">Балів за<br>лігу</span></th>
      <th scope="col"   class="text-center align-middle"><span class="rotate-sm-90">Місце в<br>турнірі</span></th>
    
    </tr> </thead>
  <tbody>';

        }else
       return  $content ='<div class="big-table">
<div class="container-fluid">
<table class="table bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
        
  <thead class="th_color_rose"> <tr >
      <th scope="col" class="text-center align-middle">&nbsp;&nbsp;#&nbsp;&nbsp;</th>
      <th scope="col" class="text-center align-middle">Дата турніра</th>
      <th scope="col" class="text-start align-middle">Назва турніру</th>
      <th scope="col" class="text-center">Початковий<br>рейтинг</th>
      <th scope="col" class="text-center">Кінцевий<br>рейтинг</th>
      <th scope="col" class="text-center">Приріст<br>рейтингу</th>
      <th scope="col" class="text-center">Кількість<br>матчів</th>
      <th scope="col" class="text-center">Кількість<br>перемог</th>
      <th scope="col" class="text-center">Кількість<br>поразок</th>
      <th scope="col" class="text-center">Кількість<br>сетів</th>
      <th scope="col" class="text-center">Балів за<br>лігу</th>
      <th scope="col" class="text-center">Місце<br>в турнірі</th>
    
    </tr> </thead>
  <tbody>';
    }
    //table_mob_turn
    function getTurnirs($aTurnirs=[])
    {
        $content='<div class="mbot20"><h4 class="text-center "><span class="name20">Відвідані турніри</span></h4></div>';
       $content.=$this->getTh_table();
    $n = ($this->page_number==1) ? 1 : ($this->page_number-1)*$_SESSION[$this->module][$this->action]['page_items']+1;

        $name='';$sm_turnirs=0;
        $cn_players=0;
       // s($aTurnirs);
        foreach ($aTurnirs as $user)
        {
          //  s($user);
            $league_id = $user['tur_league_id']>0 ? '&league_id='.$user['tur_league_id'] : '';
            $league_points = ((int)$user['tur_league_id'] > 0) ? (string)$user['points'] : '';
            $content.='<tr class="f14">
      <th class="align-middle text-center" scope="row">'.$n.'</th>
      <td class="px_my-2 align-middle text-center">'.date_for_firebird_format($user['dat']).'</td>
      <td class="px_my-2 text-start"><a target="_blank" href="#etapresult-show-turnir_id='.$user['turnir_id'].$league_id.'">'.$user['name'].'</a></td>
      <td class="align-middle text-center">'.round($user['beg_reiting']).'</td>
      <td class="align-middle text-center">'.round($user['end_reiting']).'</td>
      <td class="align-middle text-center "><span class="coral_color">'.round($user['end_reiting']-$user['beg_reiting']).'</span></td>
      <td class="align-middle text-center">'.$user['cnt_games'].'</td>
      <td class="align-middle text-center">'.$user['cnt_wins'].'</td>
      <td class="align-middle text-center">'.$user['cnt_lose'].'</td>
      <td class="align-middle text-center">'.$user['cnt_sets'].'</td>
      <td class="align-middle text-center">'.$league_points.'</td>
      <td class="align-middle text-center">'.$user['mesto'].'</td>
      
    </tr>';
            $n++;
        }
        $content .='</tbody>
</table></div></div>';
        //  ;
        $content .= (!empty($_SESSION['pagging_html']) ? $_SESSION['pagging_html'] : '');
        return $content;
    }

    function getContent ()
    {
        return $this->content;
    }
}
