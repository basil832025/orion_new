<?php
function get_menu()
{


    $content = ' <table cellpadding="0" cellspacing="0" style="margin:20px">
<tr>';
 //   s($_SESSION['gt']['user_rule']);
    if ($_SESSION['gt']['user_rule'] == 1) {
        $content .= '<td width="100px" class="submenu_text">
    <a  href="#users-list"   class="ajax_send"><img width="70px" height="70px" alt="Користувачі" title="Користувачі" src="img/slug_small/prava_user.png" border="0"><br />Користувачі</a>
  <!--  <a  href="#" module="settings" action="users_list"  class="ajax_send"><img width="70px" alt="Управления пользователями" title="Управления пользователями" src="img/slug_small/uses.png" border="0"><br />Управления<br />пользователями</a>-->
</td>
<td width="100px" class="submenu_text">
    <a  class="ajax_send" href="#sprvalues-list-id_spis=3"><img width="70px" height="70px" alt="Клуби" title="Клуби" src="img/slug_small/club.png" border="0"><br />Клуби</a>
</td>
<td width="100px" class="submenu_text">
    <a  class="ajax_send" href="#sprvalues-list-id_spis=2" ><img width="70px" height="70px" alt="Групи" title="Групи" src="img/slug_small/uses.png" border="0"><br />Групи гравців</a>
</td>

<td width="100px" class="submenu_text">
    <a  class="ajax_send" href="#sprvalues-list-id_spis=4"><img width="70px" alt="Міста" title="Міста" src="img/slug_small/connect.png" border="0"><br />Міста</a>
</td>';
    }
    $content .= '    <td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#reports-counts_turnirs"><img width="70px" alt="Кількість відвідувань турнірів" title="Кількість відвідувань турнірів"
                                                                    src="img/reports.png" border="0"><br />Кількість відвідувань турнірів</a>
    </td>
<td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#reports-new_users"><img width="70px" alt="Нові відвідувачі" title="Нові відвідувачі"
                                                                    src="img/reports.png" border="0"><br />Нові відвідувачі</a>
    </td>
<td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#reports-statofyear"><img width="70px" alt="Статистика відвідувань за рік" title="Статистика відвідувань за рік"
                                                                    src="img/reports.png" border="0"><br />Статистика відвідувань за рік</a>
    </td>
    <td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#parametres-list"><img width="70px" alt="Параметри" title="Параметри"
                                                                    src="img/slug_small/nastr.png" border="0"><br />Параметри</a>
    </td>
  <td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#turnirsshtraph-list"><img width="70px" alt="Нарахування штрафів" title="Нарахування штрафів"
                                                                    src="img/slug_small/install.png" border="0"><br />Нарахування<br> штрафів</a>
    </td> 
    <td width="100px" class="submenu_text">
        <a  class="ajax_send" href="#sprvalues-list-id_spis=6"><img width="70px" alt="Етапи" title="Етапи"
                                                                    src="img/slug_small/faq.png" border="0"><br />Етапи</a>
    </td>
</tr>
</table>';
 return $content;
}
  ?>