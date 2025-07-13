<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
///class Raschet_shtraphAction extends ActionModule
class Raschet_shtraphAction
{
    protected  $content = '';
    protected  $mess=  '';
    protected  $is_new_player = 0; // если новые игроки на туринре
    protected  $turnir_id = 0; // если новые игроки на туринре
    protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
    protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
    protected  $subMenu = array();
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента

    function init ($cron='')
    {
        if (empty($cron))
            if ( ($_SESSION['gt']['user_rule'] >= 10 || empty($_SESSION['gt']['user_login']))) {

                s('HAKKER_HAKKER');
                s($_POST);
                s($_SERVER['REMOTE_ADDR']);
                s($_SERVER['HTTP_USER_AGENT']);
                exit;
                return;
            }
        // сегоднешняя дата в формате mysql
        $today = date("Y-m-d");
        $today_first = date('Y-m-01');
        $last_day_mounth = strtotime("-1 day", strtotime($today_first));
        $last_day_mounth = date('Y-m-d', $last_day_mounth);
        //   s('$last_day_mounth='.$last_day_mounth);
        // запросим последнюю дату  расчета по турнирам защита от дублей
        $sql = 'SELECT end_dat FROM `bs_log_shtraph` order by end_dat desc limit 1';
        $last_dat_oper = db_field($sql, 'end_dat');
        //попробуем защиту от 2 нажатия
        $sql = 'SELECT end_dat FROM `bs_log_shtraph` WHERE date_start IS NOT NULL AND end_dat_time IS null order by end_dat desc limit 1';
        $last_dat_oper_begin = db_field($sql, 'end_dat');
        $last_dat_oper = !empty($last_dat_oper) ? $last_dat_oper : '2000-01-01';
        //   s('$last_dat_oper=' . $last_dat_oper);
        //  s('$today_first=' . $today_first);
        //  s('$last_dat_oper_begin=' . $last_dat_oper_begin);
        if (empty($last_dat_oper_begin) &&  $last_dat_oper < $today_first) {
            $sql = 'SELECT * FROM `bs_parametres` ';
            $a_param = db_list($sql);
            // пройдемся по парамтрах и загоним в массив кдюч значение
            $aParams = [];
            foreach ($a_param as $elem) $aParams[$elem['kod']] = $elem['value'];

            // количество месецев котрое нужно отнять
            $cnt_mounth = $aParams['mounth_cnt'];
            // количество топ игроков для штрафов
            $top = $aParams['top'];
            // процент штрафа
            $shtraph = $aParams['shtraph'];
            // терперь выберем игроков с топ 100 за последний месяц кто не посещал турниры
            $sql = 'SELECT 
          (SELECT end_reiting FROM bs_turnirplayers tp2,bs_turnirs t2
         WHERE tp2.turnir_id=t2.id AND t2.dat<"' . $today . '" AND  tp2.player_id=p.id -- AND virt=0  
			order by  tp2.turnir_id DESC  LIMIT 1) end_reit,
          p.*  FROM (SELECT *  from bs_players p ORDER BY p.reiting DESC LIMIT ' . $top . ') p 
WHERE p.ispara=0  AND 
p.id not in (SELECT  tp.player_id from bs_turnirs t, bs_turnirplayers tp
 WHERE  tp.turnir_id=t.id AND p.id= tp.player_id AND virt=0  and
  dat between date_format(DATE_SUB("' . $today . '", INTERVAL ' . $cnt_mounth . ' month),"%Y-%m-01")
            and date_add(last_day(date_sub("' . $today . '", INTERVAL 1 month)),interval "23:59:59" HOUR_SECOND) GROUP BY  tp.player_id) ';
            //  s($sql);
            $aPlayers = db_list($sql);
            if (!empty($aPlayers)) {
                $cn = count($aPlayers);
                // запишем в таблицу логирования расчетов рейтинга начало времени операции
                $sql = 'insert into bs_log_shtraph set date_start=now(), cnt_people=' . $cn . ', shtraph=' . $shtraph . ', cnt_mounth=' . $cnt_mounth . ', top=' . $top . '';
                db_query($sql);
                $last_id = db_insert_id();
                //    s('$last_id='.$last_id);
                // нужно создать шапку вирт турнираШтраф за невідвідування
                $sql='insert into bs_turnirs (dat, cnt_players, name, date_create, date_raschet, virt,shtraph, mounth_cnt, top) 
                                      values ("'.$last_day_mounth.'",'.$cn.',"Кореляція рейтингу за невідвідування турнірів",now(),now(),1,'.$shtraph.','.$cnt_mounth.','.$top.')';
                db_query($sql);
                $this->turnir_id = db_insert_id();

                //  пройдемся по товарищах и уменьшим рейтинг
                foreach ($aPlayers as $player){
                    //считаем процент штрафа
                    $player['end_reit'] = !empty($player['end_reit']) ? $player['end_reit'] : -1;
                    if ($player['end_reit']==-1) continue;
                    $diff = round($player['end_reit']*$shtraph/100,0);
                    //  s('pl_reating='.$player['reiting'].' straf='.round($player['reiting']*$shtraph/100,0).  ' diff='.$diff);
                    $reiting_end = $player['end_reit'] - $diff;
                    $sql ='insert into '.T_TURNIR_PLAYERS.' (player_id,turnir_id, beg_reiting,end_reiting,diff,diff_round,ispara)
                                                      values('.$player['id'].','.$this->turnir_id .','.$player['end_reit'].','.$reiting_end.','.$diff.','.$diff.',0)';
                    db_query($sql);
                }
                // завершим расчет и поставим время и дату выполнения чтобы в этом месяце повторно не расчитывались
                $sql='update bs_log_shtraph set end_dat="'.$today_first.'", end_dat_time=now() where id='.$last_id;
                db_query($sql);
                s('РОЗРАХУНОК ШТРАФІВ ЗАВЕРШЕНО');
            }
        } else {
            $this->mess='В цьому місяці Ви вже розраховували штрафи для гравців, спробуйте наступного місяця' ;
            // s($this->mess);
        }

//s($aPlayers);
        // s('rs');
        if (empty($cron))
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
        SystemClass::setModule('turnirs');
        if ($this->mess){
            // $this->mess = base64_encode($this->mess);
            //  $_SESSION['MESSAGE_AJAX']=$this->mess;
            //  s('mess_modal("'.$this->mess.'");');
            $_SESSION['JAVA_SCRIPT']=' mess_modal("'.$this->mess.'");';
            //    SystemClass::setJava_script('mess_modal("'.$this->mess.'");');
        }


        /// $_SESSION['JAVA_SCRIPT']=' mess_modal("'.$this->mess.'"); ';
        //   s('do');
        //    parent::list_show();
        //    s('posle');
        $post_return = 'turnirs-list';
        if ($this->turnir_id )
            $post_return = 'turnirsplayers-raschet-id='.$this->turnir_id ;
        else
            $post_return = 'turnirs-list';
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