<?php
$form = poste('form');
$turnir_id=poste('turnir_id');
$is_reiting_zmeyka =  isset($form['is_reiting_zmeyka']) ? 1 :0 ;
$istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0;
$form['is_perenos'] = !empty($form['is_perenos']) ? 1 : 0;
$etap_id=poste('id');
if (empty($etap_id)) 
{
    $sql= 'select id from '.T_ETAPS. ' order by id desc limit 1';
    $etap_id = db_field($sql,'id');
      if ($istochnik_posev>0) // если источник не участтники
            {
                         
                   $sql = 'select  t.*,(select cntGroups from '.T_TURNIR_VARIANTS.' v where v.id=group_id ) as cntGroups
                   from '.T_ETAPS.' t
                   where  t.id='.$istochnik_posev;  
                //   s($sql);
                  $aIstochnikEtap =db_row($sql);
                  
                  if (!empty($aIstochnikEtap) && $form['type_etap']!=1) //
                  {
                       switch ($aIstochnikEtap['type_etap'])
                       {
                            case 1: // предыдущий этап это группы
                            getGroupEtapIstochnik($aIstochnikEtap,$etap_id,$form['cnt_people'],$form);
                            break;
                        }   
                 }
            } 
   // s('etap_id='.$test);
}
//s($form); 
//s('$is_reiting_zmeyka='.$is_reiting_zmeyka); 
//s('$istochnik_posev='.$istochnik_posev); 



 $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0 and perenos_etap=0';
$cn_results=db_field($sql,'cn'); 
//если есть игры то ничего не делаем 
if ($cn_results==0) 
{
     if ($form['type_etap']==1 || $form['type_etap']==66) // если этот этап группы заполним спорсменов
            {
                $sql = 'select  count(*) as cn from bs_etaps_players_mesta t  where  t.etap_id='.$etap_id;
                $cnt = db_field($sql,'cn'); // реальное количество людей на этапе
                if ($form['cnt_people']!=$cnt)
                    setGroupsEtapPlayers($form,$turnir_id,$etap_id);
            }
         // сначала удаяляем по этапу все взаимосвязи если нет еще игор сыгранных
 /*   $sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where turnir_id='.$turnir_id .' and etap_id='.$etap_id;
    db_query($sql);
*/
     // удалим предідущий варианты заполнения
        $sql ='delete from '.T_REITING.'  where turnir_id='.$turnir_id.' and etap_id='.$etap_id ;
        db_query($sql);    
    
      
    // если источник участники или какой то из этапов но посев змейкой не по рейтингу
   // if ($istochnik_posev==0 || ($istochnik_posev>0 &&  $is_reiting_zmeyka==1))
    {  
     //   s($form);
        switch ($form['type_etap']) 
        {
            case 1:
            case 66:
            setGroupsEtap($form,$turnir_id,$etap_id);
            break;
             case 2:
             set2minuska($form,$turnir_id,$etap_id);
            //setGroupsEtap($form,$turnir_id);
            break;
             case 3:
             set2minuska($form,$turnir_id,$etap_id,3);
            //setGroupsEtap($form,$turnir_id);
            break;
             case 4:
             set2minuska($form,$turnir_id,$etap_id,4);
            //setGroupsEtap($form,$turnir_id);
            break;
            case 5:
                set2minuska($form,$turnir_id,$etap_id,5);
                //setGroupsEtap($form,$turnir_id);
                break;
        }
    }
}
/*
$sql='select count(*) as cn     
  from '.T_REITING.' r  where  r.etap_id='.$etap_id;

$cnt = db_field($sql,'cn');
$sql='update '.T_ETAPS. ' set cnt_games='.$cnt .' where id='.$etap_id;
db_query($sql);*/



?>