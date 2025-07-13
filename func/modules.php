<?php
/*test_create_table(T_PARTS,'parts');
test_create_table(T_MODULES,'modules');
function parts_list(){
    global $mTegsTextGlob, $parts, $language;
    $mTegsTextGlob['submenu'] = array(
                                'add' => array('module' => 'parts', 'action' => 'parts_add', 'post' => ''),
                                 );
   ;
   // $sql = 'SELECT *,(SELECT name FROM  `'.T_MODULES.'` m WHERE p.parts_modules_id=m.id) as module_name,(SELECT mname FROM  `'.T_MODULES.'` m WHERE p.parts_modules_id=m.id) as module_mname FROM `' .T_PARTS .'` p where parts_type=1 ORDER by p.sort_tree';

    $parts=get_tree_level(db_list( 'SELECT *,(SELECT name_'.$language.' as name FROM  `'.T_MODULES.'` m WHERE p.parts_modules_id=m.id) as module_name,(SELECT mname FROM  `'.T_MODULES.'` m WHERE p.parts_modules_id=m.id) as module_mname FROM `' .T_PARTS .'` p where parts_type=1  ORDER by level,sort'));
    // вывод html страници структуры дерва
    include_once ROOT_A .'modules/parts/html/parts_list.html';
}
*/




?>