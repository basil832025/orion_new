<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class SetGamesAction extends ActionModule 
{  protected  $content = ''; 
  protected  $subMenu = array();
  protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
  protected  $cnt_players=0; // количество игроков на турнире
  protected $aTurnVariants=array(); // 
  protected $aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
    function init ()
    {
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {

            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
     $turnir_id=poste('turnir_id');
      $etap_id=poste('etap_id');
      s('setgames');
    $this->raschet($turnir_id,$etap_id);
  /*      $sql='select count(*) as cn
  from '.T_REITING.' r  where  r.etap_id='.$etap_id;

        $cnt = db_field($sql,'cn');
        $sql='update '.T_ETAPS. ' set cnt_games='.$cnt .' where id='.$etap_id;
        db_query($sql);*/
    $this->show($turnir_id,$etap_id);
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
    function raschet($turnir_id,$etap_id)
    {
        $sql = 'select  t.*,(select cntGroups from '.T_TURNIR_VARIANTS.' v where v.id=group_id ) as cntGroups
       from '.T_ETAPS.' t
       where  t.id='.$etap_id;  
       //s($sql);
      $form =db_row($sql);
      $cnt_people=!empty($form['cnt_people']) ? $form['cnt_people'] : 0;
// s($form);
      $is_reiting_zmeyka =  !empty($form['is_reiting_zmeyka']) ? 1 :0 ;
   //   s('$is_reiting_zmeyka$is_reiting_zmeyka==='.$is_reiting_zmeyka);
$istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0;  
//$etap_id=poste('id');//s($form); 

 $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0';
$cn_results=db_field($sql,'cn'); 
//s($sql);
//если есть игры то ничего не делаем 
if ($cn_results==0) 
{
    $cn_results_win=0;
    if ($istochnik_posev>0 && $is_reiting_zmeyka==0)
    {
        // опредляем все ли игры сыграны в предущем этапе
        $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$istochnik_posev.' and turnir_id='.$turnir_id.' and COALESCE(win_player,0)=0';
        $cn_results_win=db_field($sql,'cn'); 
       // s($sql);
    }     
    
         // сначала удаяляем по этапу все взаимосвязи если нет еще игор сыгранных
 /*   $sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where turnir_id='.$turnir_id .' and etap_id='.$etap_id;
    db_query($sql);
*/
     // удалим предідущий варианты заполнения
        $sql ='delete from '.T_REITING.'  where turnir_id='.$turnir_id.' and etap_id='.$etap_id ;
        db_query($sql);    
    
      
    // если источник участники или какой то из этапов но посев змейкой не по рейтингу
    if ($istochnik_posev==0 || ($istochnik_posev>0 &&  $cn_results_win==0))
    {  //s('$form[type_eta]'.$form['type_etap']);
        switch ($form['type_etap']) 
        {
            case 1:
                $sql = 'select count(*) as cn from '.T_ETAPS_PLAYER_MESTA.' where `groups`>0 and player_id>0 and  etap_id='.$etap_id   ;
                $aPlayersCn = db_field($sql,'cn');
                // если количество участников этапа меньше или вообще еще нет то заполняем
                if ($cnt_people>$aPlayersCn)         setGroupsEtapPlayers($form,$turnir_id,$etap_id,1);
            setGroupsEtap($form,$turnir_id,$etap_id);
            break;
             case 2:
             case 3:
             case 4:
             case 5:
             $sql = 'select count(*) as cn from '.T_ETAPS_PLAYER_MESTA.' where num_posev_olimp>0 and player_id>0 and  etap_id='.$etap_id   ;
             $aPlayersCn = db_field($sql,'cn');
           // s('$cnt_people='.$cnt_people.' $aPlayersCn='.$aPlayersCn);
             // если количество участников этапа меньше или вообще еще нет то заполняем
             if ($cnt_people>$aPlayersCn) 
             {
               // s('tyt11');
                
                     // сначала удаяляем по этапу все взаимосвязи если нет еще игор сыгранных
                $sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where turnir_id='.$turnir_id .' and etap_id='.$etap_id;
                db_query($sql);
                 $sql = 'select t.is_reiting,t.is_reiting_w,t.group_id_old from '.T_TURNIRS.' t where t.id='.$turnir_id;
                 $aTurnirSetting = db_row($sql);
                 $is_reiting = $aTurnirSetting['is_reiting'] ? $aTurnirSetting['is_reiting'] : $aTurnirSetting['is_reiting_w'];
                                   
                if ($istochnik_posev>0) // если источник не участтники
                {
                                      // опредляем тип источника и всю  о нем инфу
                      $sql = 'select  t.*,(select cntGroups from '.T_TURNIR_VARIANTS.' v where v.id=group_id ) as cntGroups
                       from '.T_ETAPS.' t
                       where  t.id='.$istochnik_posev;  
                    //   s($sql);
                      $aIstochnikEtap =db_row($sql);
                      if (!empty($aIstochnikEtap))
                      {
                      //  s('11');
                     //   s($aIstochnikEtap['type_etap']);
                       // s('$is_reiting_zmeyka='.$is_reiting_zmeyka);
                           switch ($aIstochnikEtap['type_etap'])
                           {
                                case 1: // предыдущий этап это группы
                                //s('22');
                                getGroupEtapIstochnik($aIstochnikEtap,$etap_id,$form['cnt_people'],$form);
                                // доссем по рейтингу игроков
                                if ($is_reiting_zmeyka==0) 
                                {//s('33');
                                   $cntGroups= $aIstochnikEtap['cntGroups'];
                                   $diff_players =  $cnt_people % $cntGroups; // узнаем сколько дополнительный мест нужно посеять не кратное поличесву групп и участников
                                   $cnt_mest_group_bez_ost = (($cnt_people - $diff_players) / $cntGroups)+1; // сколько мест в группах сееем подряд
                                   if ($diff_players>0)
                                   {
                                         //  ,'.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit 
                                      
                                       $sql='SELECT e.*
                                        FROM `'.T_ETAPS_PLAYER_MESTA.'` e,`'.T_PLAYERS.'` p  
                                        where  etap_id='.$istochnik_posev.' and grp_mesto='.$cnt_mest_group_bez_ost.' and p.id=e.player_id
                                         order by  '.($is_reiting>0 ? 'reiting_ukraine desc, ' :'').' case when reiting>0 then reiting else start_reiting end desc limit '.$diff_players;
                                        //s($sql);
                                        $aPlayers=db_list($sql); 
                                        $num_posev_olimp = $cnt_people - $diff_players; 
                                         foreach ($aPlayers as $aMesto)
                                        {
                                            $player_id=$aMesto['player_id'];
                                            $num_posev_olimp++;
                                            $groups_pred = ',groups_pred='.$aMesto['groups'].', 
                                            grp_num_pred='.(!empty($aMesto['grp_mesto']) ? $aMesto['grp_mesto'] : $aMesto['grp_num']).',mesto_all_pred= '.$aMesto['mesto_all'].',etap_id_pred='.$aMesto['etap_id'];
                                            
                                            $where = 'turnir_id='.$turnir_id.',player_id='.$player_id.',etap_id='.$etap_id.',
                                            num_posev_olimp='.$num_posev_olimp. $groups_pred; 
                                            //     s($where) ;
                                            $sql ='insert into '.T_ETAPS_PLAYER_MESTA.'  SET '.$where  ;
                                            db_query($sql);   
                                        }
                                        
                                    }
                                }
                                
                                
                                break;
                            }   
                     }
                } 
                else
                {
                    //если источник участники
                    $sql = 'SELECT  '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit,tp.id as turn_id, p.*  
                    FROM `'.T_TURNIR_PLAYERS.'` tp,'.T_PLAYERS.' p where turnir_id='.$turnir_id.' and p.id=tp.player_id 
                    ORDER BY 1 desc, 2 desc '. ($cnt_people>0 ? ' limit '.$cnt_people : '');
                    $aPlayers = db_list($sql);
                   // s($sql);
                    $num_posev_olimp=0;
                    foreach ($aPlayers as $aMesto)
                    {
                        $player_id=$aMesto['id'];
                        $num_posev_olimp++;
                        
                        $where = 'turnir_id='.$turnir_id.',player_id='.$player_id.',etap_id='.$etap_id.',
                        num_posev_olimp='.$num_posev_olimp; 
                        //     s($where) ;
                        $sql ='insert into '.T_ETAPS_PLAYER_MESTA.'  SET '.$where  ;
                        db_query($sql);   
                    }
                }
             }
             set2minuska($form,$turnir_id,$etap_id,$form['type_etap']);
            //setGroupsEtap($form,$turnir_id);
            break;
           
             
           
        }
    }
}
      
        
    }
     function show($turnir_id,$etap_id)
    { //  s($_POST);
     // s($this->id);
     
        SystemClass::setAction('anyaction');
        SystemClass::setModule('etaps');
        //   parent::list_show();
          $post_return = 'etaps-list-turnir_id='.$turnir_id.'&etap_id='.$etap_id;
        SystemClass::setPost_return($post_return);
    //  s($sql);
     //  $this->Java_script='reload_page_();';
    
        
        // SystemClass::setJava_script($this->Java_script);
     
       // $objList = new ListTable();
        
     //   $objList->list_show();
    // //   $this->content=$objList->getContent();
     //   $this->subMenu=$objList->getSubMneu();
     //   $this->Java_script=$objList->getJavaScript();
        
    }

}
