<?php
require_once __DIR__ . '/../../../teamplayers/func/func.teamplayers.php';

// класс для расчета рейтинга командных турниров
class RaschetAction extends ActionModule 
{  protected  $content = ''; 
  protected  $is_new_player = 0; // если новые игроки на туринре
  protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
  protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
  protected  $league_id = 0;
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
   
    function init ()
    {
       // s('tyt+rr');
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
        
        // Получаем league_id из параметров (передается через POST или GET)
        $turnir_id = teamplayers_request_param('turnir_id');
        if ($turnir_id <= 0) {
            $turnir_id = teamplayers_request_param('id');
        }
        if ($turnir_id > 0) {
            $this->id = $turnir_id;
        } else {
            $this->id = (int)$this->id;
        }
        $this->league_id = teamplayers_request_param('league_id', '');
        if ($this->league_id <= 0) {
            $this->league_id = teamplayers_resolve_league_id(0, $this->id);
        }

        if ($this->id <= 0) {
            window_mess('Помилка: не вказано турнір для перерахунку');
            $this->list_show_rs();
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

            $sql = 'select id,date_create,dat,virt,league_id from ' . T_TURNIRS . ' r WHERE (r.date_raschet IS not null or id=' . $this->id . ')
        AND dat>=(SELECT dat FROM ' . T_TURNIRS . ' WHERE id =' . $this->id . ') 
        order by dat,id';
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
            progressBar(0,'1/'.$all_rows,'turnirsteams','raschet');
        }
        // окно
        if ($all_rows>10 &&  $_SESSION['RASSCHET']['isFirst']==1) {
            $_SESSION['RASSCHET']['isFirst'] = 2;
            //   s('isFirst=1');
            progressBar(1,'1/'.$all_rows,'turnirsteams','raschet');
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
                    sql_raschet($turnir['id']);
                }
                // запишем дату и время когда делали расчет первый раз в date_create потом date_last_modif
                $set = !empty($turnir['date_create']) ? 'date_last_modif' : 'date_create';
                $sql = 'update ' . T_TURNIRS . ' set ' . $set . '=now() where id=' . $turnir['id'];
                db_query($sql);
                // расчитать места
                set_mesta_turnir($turnir['id']);
                recalculate_team_turnir_stats($turnir['id']);
                // рассчитаем очки для лиги
                if (!empty($turnir['league_id'])) set_points_turnir($turnir['league_id']);

                $nRow++;
                $prc = round($_SESSION['RASSCHET']['nowRow'] * 100 / $all_rows); //

                if ($nRow > 10 && $prc < 100) progressBar($prc, $k . '/' . $all_rows, 'turnirsteams', 'raschet');

            }
            if ($all_rows>10)   progressBar(100, '', 'turnirsteams', 'raschet');

        }
//        $this->sql_raschet();
        // расчет мест для команд (вместо игроков)
        // Для командных турниров считаем топ команд, а не топ игроков
        $sql='select * from '.T_PLAYERS.' where is_team=1 and not_use=0 order by reiting desc';
        $aMestaTeams=db_list($sql);
        $num=1;
  //----------------
        foreach ($aMestaTeams as $team)
        {
            db_query('update '.T_PLAYERS.' set num_reiting='.$num.' where id='.$team['id']);
             $num++;
        }
        db_query('update '.T_TURNIRS.' set date_raschet=now()  where id='.$this->id);


        /// END -------------расчет места для команд
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
        $this->id = (int)$this->id;
        if ($this->id <= 0) {
            $this->id = teamplayers_request_param('turnir_id');
        }
        if ($this->id <= 0) {
            $this->id = teamplayers_request_param('id');
        }

        if ($this->id <= 0) {
            SystemClass::setModule('turnirsteams');
            SystemClass::setPost_return('turnirsteams-list');
            parent::list_show();
            return;
        }

        $sql='SELECT virt FROM ' . T_TURNIRS . ' WHERE id =' . $this->id . '';
        $virt = db_field($sql,'virt');
        $turnir_id = $this->id;
        $league_id = !empty($this->league_id) ? (int)$this->league_id : teamplayers_request_param('league_id');
        
        // Если league_id не передан через POST, пытаемся получить из турнира
        if (empty($league_id) && !empty($turnir_id)) {
            $league_id = db_field('SELECT league_id FROM ' . T_TURNIRS . ' WHERE id=' . $turnir_id, 'league_id');
        }
        
        $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
        
        if ($virt==1){
            SystemClass::setModule('turnirs');
            $post_return = 'turnirs-list';
        }else
        {
            SystemClass::setModule('turnirsteams');
            $post_return = 'turnirsteams-list-&turnir_id='.$turnir_id.$menu_league;
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

