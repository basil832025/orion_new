<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class RaschetAction extends ActionModule 
{  protected  $content = ''; 
  protected  $is_new_player = 0; // если новые игроки на туринре
  protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
  protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента

    function init ()
    {
        $turnir_id = poste('turnir_id');
        $this->id = !empty($this->id) ? $this->id : $turnir_id;
      //  s('tyt+rr'.$this->id);
      //  s(ROOT.'func/raschet_func.php');
        include_once ROOT.'func/raschet_func.php';
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {

            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        $sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.$this->id;
        $cnt_players = db_field($sql,'cnt');
        if (!empty($cnt_players) && $cnt_players>0)
        {
            $sql = 'update `'.T_TURNIRS.'` set cnt_players='.$cnt_players.' where id='.$this->id;
            //s($sql);
            db_query($sql);
        } //if (!empty($form['new_player'])){
    $this->is_first=1;
    if (!empty($this->id)){
      //  s('do_rasch');


        $_SESSION['RASSCHET']['nowRow'] = !empty($_SESSION['RASSCHET']['nowRow']) ? $_SESSION['RASSCHET']['nowRow'] : -1;
        //    s('DOOOOrasschet_all_sess');
        //   s($_SESSION['RASSCHET']);
        if (empty($_SESSION['RASSCHET']['TURNIRS'])) {

            $sql = 'select id,date_create,dat,virt,league_id,COALESCE((SELECT l.is_team_league FROM bs_leagues l WHERE id=r.league_id),0) AS is_team_league from ' . T_TURNIRS . ' r WHERE (r.date_raschet IS not null or id=' . $this->id . ')
        AND dat>=(SELECT dat FROM ' . T_TURNIRS . ' WHERE id =' . $this->id . ') 
        order by dat,id';
           //  s($sql);
            $aTurnirs = db_list($sql);
            $_SESSION['RASSCHET']['TURNIRS'] = $aTurnirs;
         //   s($_SESSION['RASSCHET']['TURNIRS'] );
        }else
            $aTurnirs =  $_SESSION['RASSCHET']['TURNIRS'];
        $all_rows =  count($aTurnirs);

        $this->is_first=1;
        // посылаем прогресс бар 1 чтобы сразу всплыло окно с прогрессбаром
        if ($all_rows>10 && empty($_SESSION['RASSCHET']['isFirst'])) {
            $_SESSION['RASSCHET']['isFirst'] = 1;
            //     s('isFirst=0');
            progressBar(0,'1/'.$all_rows,'turnirsplayers','raschet');
        }
        // окно
        if ($all_rows>10 &&  $_SESSION['RASSCHET']['isFirst']==1) {
            $_SESSION['RASSCHET']['isFirst'] = 2;
            //   s('isFirst=1');
            progressBar(1,'1/'.$all_rows,'turnirsplayers','raschet');
        }
        if ($_SESSION['RASSCHET']['nowRow']<($all_rows-1)) {
            $nRow = 0;
            //--------------------------------
            foreach ($aTurnirs as $k => $turnir) {
                if ($_SESSION['RASSCHET']['nowRow'] >= $k) continue;
                $_SESSION['RASSCHET']['nowRow'] = $k;

                //  s('do_rasch');
                // если это турнир списание штрафа
                if ($turnir['virt']==1) {
                    raschet_shtraph($turnir['id']);
                } else
                {
                    if ($turnir['is_team_league']>0) add_players_to_command_turnirs($turnir['id']);

                    sql_raschet($turnir['id'],$turnir['is_team_league']);
                }
                // запишем дату и время когда делали расчет первый раз в date_create потом date_last_modif
                $set = !empty($turnir['date_create']) ? 'date_last_modif' : 'date_create';
                $sql = 'update ' . T_TURNIRS . ' set ' . $set . '=now() where id=' . $turnir['id'];
                db_query($sql);
                // расчитать места
                set_mesta_turnir($turnir['id']);
                if ($turnir['is_team_league']>0) {
                    recalculate_team_turnir_stats($turnir['id']);
                }
                // рассчитаем очки для лиги
                if (!empty($turnir['league_id'])) set_points_turnir($turnir['league_id'],$turnir['is_team_league']);

                $nRow++;
                $prc = round($_SESSION['RASSCHET']['nowRow'] * 100 / $all_rows); //

                if ($nRow > 10 && $prc < 100) progressBar($prc, $k . '/' . $all_rows, 'turnirsplayers', 'raschet');

            }
            if ($all_rows>10)   progressBar(100, '', 'turnirsplayers', 'raschet');

        }
//        $this->sql_raschet();
        // расчет мест для игроков
         $sql='select * from '.T_PLAYERS.' where  ispara=0 and not_use=0 and is_team=0 order by reiting desc';
     //   else $sql='select * from '.T_PLAYERS.' where ispara=0 order by reiting desc';
        $aMestaPlayers=db_list($sql);
        $num=1;
  //----------------
        foreach ($aMestaPlayers as $player)
        {
            db_query('update '.T_PLAYERS.' set num_reiting='.$num.' where id='.$player['id']);
             $num++;
        }
        db_query('update '.T_TURNIRS.' set date_raschet=now()  where id='.$this->id);


        /// END -------------рачет места для игроков
        $this->is_first=0;

    }
    if (!empty($_SESSION['RASSCHET']))   unset($_SESSION['RASSCHET']);

        // s('rs');
    $this->list_show_rs();
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

      function list_show_rs()
    {   SystemClass::setAction('anyaction');
        $sql='SELECT id,date_create,dat,virt,league_id,COALESCE((SELECT l.is_team_league FROM bs_leagues l WHERE id=r.league_id),0) AS is_team_league FROM ' . T_TURNIRS . ' r WHERE id =' . $this->id . '';
        $turnir = db_row($sql);
      //  s($sql);
     //   s($turnir);
        $virt = $turnir['virt'];
        $Command = $turnir['is_team_league'];
      //  s('$Command='.$Command);
        $league_id = $turnir['league_id'];
        if ($virt==1){
            SystemClass::setModule('turnirs');
            $post_return = 'turnirs-list';
        }else if ($Command>0) {
            SystemClass::setModule('turnirsplayers');
            $post_return = 'turnirsplayers-list-&turnir_id='.$this->id.'&league_id='.$league_id;

        }else{
            SystemClass::setModule('turnirsplayers');
            $post_return = 'turnirsplayers-list-turnir_id='.$this->id;
        }


     //  $this->Java_script='reload_page_();';
  //   s('do');
       parent::list_show();
   //    s('posle');

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
