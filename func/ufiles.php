<?php
if (file_exists('../config/access.php') && file_exists('../config/const.php')){
 include_once '../config/access.php';
 include_once '../config/const.php';
 include_once '../config/const_admin.php';

}else{
 die('Произошел крах системы нету одного или нескольких служебных файлов1!');
}
if (file_exists('main_func.php')){
 include_once 'main_func.php';

}else{
 die('Произошел крах системы нету одного или нескольких служебных файлов функций2!');
}

if (file_exists('error_func.php')){
 include_once 'error_func.php';
}else{
 die('Произошел крах системы нету файла обработки ошибок!');
}

// дополниетельные или вспомогательные функции
if (file_exists('dop_func.php')){
 include_once 'dop_func.php';

}
// подключений функций MYSQL
if (file_exists('mysql.php')){
 include_once 'mysql.php';

}else{
 die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
}

if (!empty($_GET['isupload']))
{
 s($_POST);
 s($_FILES);
 s('isupload');
 $output = ['status'=>'success','text'=>'OK files were processed!!!!!!!!.','initialPreview'=>"../upload/gold.png",
     'initialPreviewConfig'=>['caption'=> "gold.png",'size'=> 329892,'width'=> "120px",
         'url'=> "ufiles.php?isdelete=1",'key'=> 1],
     'initialPreviewShowDelete'=>true];


 echo json_encode($output);
}elseif(!empty($_GET['isdelete']))
{
 s('isdelete');

 s($_GET);
 s($_POST);
 s($_FILES);
 //$output = ['error'=>'Error while uploading images. Contact the system administrator'];
 $output = ['status'=>'success','text'=>'OK files were processed!!!!!!!!.'];
 echo json_encode($output);
}else
{


/*
if (file_exists('../config/access.php') && file_exists('../config/const.php')){
    include_once '../config/access.php';
    include_once '../config/const.php';
    include_once '../config/const_admin.php';

}else{
    die('Произошел крах системы нету одного или нескольких служебных файлов!');
}
if (file_exists('../func/main_func.php')){
    include_once '../func/main_func.php';

}else{
    die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
}
// подключений функций MYSQL
if (file_exists('../func/mysql.php')){
    include_once '../func/mysql.php';

}else{
    die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
}
global $name_field,$id,$module,$id_elem,$type_view;
    $id = gete('id') ? gete('id') : (poste('id') ? poste('id') :0);
    $id_elem=  gete('id_elem') ? gete('id_elem') : (poste('id_elem') ? poste('id_elem') :0);
    $type_view=  gete('type_view_') ? gete('type_view_') : (poste('type_view_') ? poste('type_view_') :1);

    $name_field = gete('name_field_') ? gete('name_field_'):(poste('name_field_') ? poste('name_field_') :'file');
      $module = gete('module_') ? gete('module_') : (poste('module_') ? poste('module_') :'');
      $cnt_files = gete('cnt_files') ? gete('cnt_files') : (poste('cnt_files') ? poste('cnt_files') :1);
  //$module_id=db_field('select id from `' .T_MODULES. '` where mname ="' .$module.'" limit 1','id');
// s($_POST);s($_FILES);  exit;
 include_once '../libs/class_upload.php';
// объект класса загрузки файлов
$upload_handler = new UploadHandler($options_uploads_class);
$upload_handler->set_result=0;
//s($_SERVER);s($_POST);exit;
switch ($_SERVER['REQUEST_METHOD']) {

    case 'POST':
// log_write($GLOBALS);
//s($_POST);
if (!empty($_REQUEST['delete'])){
	   $upload_handler->delete();
        exit;
}
         $upload_handler->post();
        exit;
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}
  //s($id);
if ($type_view>3) {
     $file_=db_list('select * from `' .T_FILES. '` where id_elem =' .$id_elem.' and field="'.$name_field.'"');
    // s('select * from `' .T_FILES. '` where id_elem =' .$id_elem.'');
    }else{
if (!empty($id)){
  // считываем с таблицы поле с файлом
  $file_=db_row('select * from `' .T_FILES. '` where id =' .$id.' limit 1');

}
$name_file='';
 if (!empty($file_)){
  $name_file = (strpos($file_['type'],"image/")!==false) ? (!empty($file_['img_mini']) ? DIR_IMAGES_.$file_['img_mini']  : URL_FILES_SITE_SMALL .$file_['name']) : file_type_name($file_['type'],$file_['name']);
  //s($name_file);
  }
  }*/

 $content = '
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Krajee JQuery Plugins - &copy; Kartik</title>

    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../plugins/bootstrap-icons/font/bootstrap-icons.css" crossorigin="anonymous">
    <link href="../plugins/fileinput/css/fileinput.css" media="all" rel="stylesheet" type="text/css"/>
    <script src="../js_/jquery-3.6.4.js" crossorigin="anonymous"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../plugins/fileinput/js/buffer.min.js" type="text/javascript"></script>
    <script src="../plugins/fileinput/js/filetype.min.js" type="text/javascript"></script>
    <script src="../plugins/fileinput/js/fileinput.js" type="text/javascript"></script>

</head>
<body>
<div class="file-loading">
    <input id="input-b6a" name="input-b6a" type="file" >
</div>

<script>
    $(document).ready(function() {
          let url = "../upload/3mesto.png"; //передаем переменную $file в javascript
   
        $("#input-b6a").fileinput({
           showUpload: false,
        showRemove: true,
        initialPreviewShowDelete: true,
        allowedFileExtensions: ["jpg", "png", "gif"],
        initialPreviewAsData: true, 
        initialPreview: [url], //наш файл
        initialPreviewConfig: [
           // {downloadUrl: url},
     {caption: "3mesto11.png", size: 329892, width: "120px", url: "ufiles.php?isdelete=1", key: 111},
           
        ],
     /*   showUpload: true,
       // dropZoneEnabled: false,
            theme: "fa5",
            maxFileCount: 1,
        uploadUrl: "ufiles.php?isupload=1&id=1234", // you must set a valid URL here else you will get an error
        deleteUrl: "ufiles.php?isdelete=1&id=1234", // you must set a valid URL here else you will get an error
        allowedFileExtensions: ["jpg", "png", "gif"],
        overwriteInitial: false,
        maxFileSize: 1000,
        maxFilesNum: 1,
            previewFileType: "image",

        initialPreviewShowDelete:true,
        initialPreviewAsData: true,
        initialPreview: [
                "../upload/3mesto.png",
             ],
            initialPreviewConfig: [
                {caption: "3mesto.png", size: 329892, width: "120px", url: "ufiles.php?isdelete=1", key: 1},
             ],
        //allowedFileTypes: ["image", "video", "flash"],
        slugCallback: function (filename) {
 return filename.replace("(", "_").replace("]", "_");
}
        */
        
         //   showUpload: false,
           // dropZoneEnabled: false,
           // maxFileCount: 1,
           // inputGroupClass: "input-group-lg",
           // initialPreviewAsData: true,
            //initialPreview: [
              //  "http://lorempixel.com/1920/1080/transport/1",
            // ],
            //initialPreviewConfig: [
              //  {caption: "transport-1.jpg", size: 329892, width: "120px", url: "{$url}", key: 1},
            // ]
        });

    });
</script>
</body>
</html>
';   //  <div class="file_upload_overall_progress"><div style="display:none;"></div></div>
echo $content;
}
exit;
?>
