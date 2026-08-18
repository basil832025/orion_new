<?php
 $turnir_id=poste('turnir_id');
 $etap_id=poste('etap_id');
 $grp=poste('grp');
 
   $sql ='SELECT id,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  reiting from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
(select  god_rogd from '.T_PLAYERS.' p where p.id=tp.player_id) as god_rogd
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  `groups`=0 and etap_id='.$etap_id.' and turnir_id='.$turnir_id;

$Anew = db_list($sql);
$cont=get_htmlTable($Anew,$turnir_id,$etap_id,$grp);
$mess='ssaas';
   Ajax(array('content' => $cont,
         'message_user' => '',
         'close_' => 0,
         'java_script' => '',
        'post_return' => '',
        )); exit;
        
function get_htmlTable($Anew,$turnir_id,$etap_id,$grp)
{
    $str='
    <table cellpadding="0" cellspacing="1" class="bordered" width="100%" border="0" id="parts_table_">
         <tbody>
         <tr>      
         <th style="text-align: center;width:250px">
          <span class="sort_cols" sort="name">ФИО Игрока </span>
         
        </th>
        <th style="text-align: center;width:20px">
        <span class="sort_cols" sort="god_rogd">Год рождения<span></span>
        </span></th>
        <th style="text-align: center;width:50px">
      
        <span class="sort_cols" sort="reiting">Рейтинг<span class="desc"></span></span>
         <div class="hide_elem filter_panel filter_panel_reiting">             
         </div></th>
         <th style="text-align: center;width:50px"><span>Выберите<span></span></span></th>
         </tr>';
         foreach ($Anew as $aVal)
         {
            $str.='<tr>
         <td style="padding-left:5px;" class="editTd" id="editTdElem--name--808">
         <span id="dataName--name--808">'.$aVal['name'].'</span></td>
         <td style="padding-left:5px;" class="editTd" id="editTdElem--god_rogd--808">
         <span id="dataName--god_rogd--808">'.$aVal['god_rogd'].'</span></td>
         <td style="padding-left:5px;" class="editTd" id="editTdElem--reiting--808">
         <span id="dataName--reiting--808">'.$aVal['reiting'].' ('.$aVal['reiting_ukraine'].')</span></td><td align="center">
         <a  href="javascript:parent.jQuery.fancybox.close();" 
         class="ajax_vibor" post_string="etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp.'&newplayer='.$aVal['id'].'" 
         wintype="0" module="etaps" action="addplayertogrp"  
         id="element_vibor_id_'.$aVal['id'].'">Добавить игрока</a>
         </td>
         </tr>';
         }
         
         $str.='</tbody></table>
    ';
    return $str;
}        
?>