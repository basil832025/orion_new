<?php
$turnir_id=poste('turnir_id');
$form=poste('form');
$etap_id=poste('id');
$sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
$aTurnir= db_row($sql);
$is_command = $aTurnir['is_command'];
    $is_reiting_zmeyka =  isset($form['is_reiting_zmeyka']) ? 1 :0 ;
    $istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0;  
    $form['group_id'] = !empty($form['group_id']) ? $form['group_id'] : 0;
    $form['is_perenos'] = !empty($form['is_perenos']) ? 1 : 0;
    //s($istochnik_posev);
    // сколько игроков будет участовать в данном этапе, если 0 то все игроки турнира
    //$cnt_people=!empty($form['cnt_people']) ? $form['cnt_people'] : 0;

if ($is_command)
    if ($form['cnt_people'] % 2 !== 0) {
    window_mess('В командних змаганях повино приймати парна кількість спортсменів');
    }else{
        $sql = 'SELECT COUNT(*) AS cn,is_command_num FROM bs_turnirplayers WHERE turnir_id='.$turnir_id.' GROUP BY is_command_num ORDER BY is_command_num';
        $aComPeople=db_list($sql);
      //  s($aComPeople);
       $cnt_etap = $aComPeople[0]['cn']+$aComPeople[1]['cn'];
       if ($cnt_etap<>$form['cnt_people']) window_mess('Кількість на етапі '.$form['cnt_people'].' не свіпадає по кількості по учасниках '.$cnt_etap);
        if (!empty($aComPeople) && $aComPeople[0]['cn']!=$aComPeople[1]['cn'])window_mess('Не рівна кількість спортсменів по командах');
    }
    if (empty($form['type_etap']))  window_mess('Заповніть поле Варіанти!');
    // проверка при тип этапа групп выбор типа групп 
   if ($form['type_etap']==1 && (empty($form['group_id']) && empty($form['cnt_grp'])))   window_mess('Заповніть поле кількість груп!');
   if ($form['type_etap']==1 && !empty($form['cnt_grp']))  
   {
      $cn_koff = $form['cnt_people'] / $form['cnt_grp'];
      $cn_koff_korr = floor($form['cnt_people'] / 3);
      //s('$cn_koff_korr='.$cn_koff_korr. ' $aEtap[cnt_people]='.$form['cnt_people']. ' $cn_koff='.$cn_koff);
      if ($cn_koff<3) window_mess('Груп максимум для такой кількості гравців повинно бути  не більше '.$cn_koff_korr);
   }
//  window_mess('Заполните поле Варианты групп или укажите кл-во групп!');
if ($form['type_etap']>1 && $form['cnt_people']<2)   window_mess('Мінімальна кількість гравців для сітки 2!');
  //  window_mess('Заполните поле Варианты групп или укажите кл-во групп!');
   if ($form['type_etap']>1 && $form['cnt_people']>16)   window_mess('Максимальна кількість гравців для сітки 16!');
  if ($istochnik_posev>0) // если источник не участтники
            {
                $sql = 'select  count(*) as cn from bs_etaps_players_mesta t  where  t.etap_id='.$istochnik_posev;
                $cnt = db_field($sql,'cn'); // реальное количество людей на этапе
               //
              //   s('$cnt1='.$form['cnt_people']+$form['mesto_from']-1);
              $diff = $cnt-($form['cnt_people']+$form['mesto_from']-1);
                if ($diff<0) window_mess('Ви виходите за ліміт гравців з попереднього етапу на '.$diff.' гравців!') ;

           }
$sql = 'select  count(*) as cn from '.T_TURNIR_PLAYERS.' t  where  t.turnir_id='.$turnir_id;
$cnt = db_field($sql,'cn'); // реальное количество людей на этапе
$diff =$cnt- ($form['cnt_people']+$form['mesto_from']-1);
if ($diff<0) window_mess('Ви виходите за ліміт гравців  на '.$diff.' гравців!') ;

if (!empty($etap_id)) //  если это не добавление нового этапа
{

    // узнаем предідущие значения туринра
        $sql = 'select  t.* from '.T_ETAPS.' t  where  t.id='.$etap_id;  
        $aEtap =db_row($sql); 
      //  s($aEtap);
      //  s($form);
     $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0 and perenos_etap=0';
    $cn_results=db_field($sql,'cn'); 
    //если есть игры то ничего не делаем 
    $aEtap['type_etap'] = !empty($aEtap['type_etap']) ? $aEtap['type_etap'] : 0;
    $aEtap['cnt_people'] = !empty($aEtap['cnt_people']) ? $aEtap['cnt_people'] : 0;
    if ($cn_results!=0) 
    {
         //  s($form);
         
      //  s($aEtap);($aEtap['is_reiting_zmeyka']!=$is_reiting_zmeyka) ||
        if(  ($aEtap['istochnik_posev']!=$istochnik_posev) 
            or ($aEtap['type_etap']!=$form['type_etap']) or ($aEtap['cnt_people']!=$form['cnt_people'])
            or ($aEtap['group_id']!=$form['group_id']) or ($aEtap['mesto_from']!=$form['mesto_from'])
            or ($aEtap['cnt_grp']!=$form['cnt_grp'])
            or ($aEtap['is_perenos']!=$form['is_perenos'])
         ) 
       {
          window_mess('В даному турнірі є ігри! Змінювати параметри вже неможна, бо будуть помилки  в турнірі!');
       } 
    }else
    {

        // если произошли измениния котроые могут повлиять на посев в сетке то удалим посев и снова посеем
         if( ($aEtap['is_reiting_zmeyka']!=$is_reiting_zmeyka) || ($aEtap['istochnik_posev']!=$istochnik_posev) 
            or ($aEtap['type_etap']!=$form['type_etap']) or ($aEtap['cnt_people']!=$form['cnt_people'])
            or ($aEtap['mesto_from']!=$form['mesto_from']) or ($aEtap['cnt_grp']!=$form['cnt_grp'])
             or ($aEtap['is_perenos']!=$form['is_perenos'])
         ) 
         {
         // сначала удаяляем по этапу все взаимосвязи если нет еще игор сыгранных
            $sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where turnir_id='.$turnir_id .' and etap_id='.$etap_id;
            db_query($sql);

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
         //   s('$form[type_etap]='.$form['type_etap']);
                      
         }
        
        
        //------------------код изминения для финалов остальных по птичке змейке или рейтингу
        if ($istochnik_posev>0)
        {
            $sql = 'select  t.* from '.T_ETAPS.' t  where  t.istochnik_posev='.$istochnik_posev.' order by id';  
            $aEtap =db_list($sql); 
          //  s($sql);
            $cnEtaps=count($aEtap);
            if ($cnEtaps>1) // если этапов большего одного тогда будем заменять для дочерних финалов признак
            {
                $i=1;
                $ismess=0;
                foreach ($aEtap as $aEt)
                {
                   // s($aEt);
                    
                    if ($i==1) 
                    {  
                       $idEtapsFirst = $aEt['id'];
                       if ($etap_id==$idEtapsFirst) 
                        {
                            $is_reiting_zmeyka_etaps =  $is_reiting_zmeyka ; 
                           
                        }else
                        {
                            $is_reiting_zmeyka_etaps =  $aEt['is_reiting_zmeyka']>0 ? 1 :0 ; 
                       
                        }
                    }
                    else
                    {
                       if ($etap_id==$idEtapsFirst) 
                       {
                         if ($is_reiting_zmeyka_etaps!=$aEt['is_reiting_zmeyka']) 
                         {
                            $sql = 'update '.T_ETAPS.' set is_reiting_zmeyka='.$is_reiting_zmeyka_etaps.' where id='.$aEt['id'];
                            db_query($sql);
                         }
                            
                       }
                       else
                       {
                      //  s('$is_reiting_zmeyka_etaps='.$is_reiting_zmeyka_etaps.'  $aEt[is_reiting_zmeyka]='.$aEt['is_reiting_zmeyka']);
                           if ($is_reiting_zmeyka_etaps!=$is_reiting_zmeyka) 
                           {
                               $ismess=1; 
                           }
                       }  
                    }
                  
                    $i++;
                }
                // 
                if ($ismess>0) window_mess('Меняйте признак Рейтинг/Змейка в первом этапе данного источника (Например в 1 финале)'); 
    
            }
        }
        //========================
    }
}
/*

   if ($istochnik_posev>0) // если источник не участтники
   {
      // опредляем тип источника и всю  о нем инфу
      $sql = 'select  t.*,(select cntGroups from '.T_TURNIR_VARIANTS.' v where v.id=group_id ) as cntGroups
       from '.T_ETAPS.' t
       where  t.id='.$istochnik_posev;  
      // s($sql);
      $aIstochnikEtap =db_row($sql);
    //  if ($cnt_people==1)
   }   
*/

?>