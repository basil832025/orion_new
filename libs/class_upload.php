<?php
// определяем временную директорию, куда грузятся файлы на сервер
$tmp_dir = '';
$file_path_='';
$file_path_f='';
global $file_data; 
if (!isset($_SESSION['upload_tmp_dir'])){
    $tmp_dir_ok = false;

    $tmp_dir = ini_get('upload_tmp_dir');

    $tmp_dir_ok = true;

    // если директива upload_tmp_dir не задана в php.ini, то
    if (!is_dir($tmp_dir) || $tmp_dir==''){
        // искуссвенным путем определяем временную директорию в системе
        $tmp_dir = dirname(tempnam('127631782631827', 'foo'));
        s('tyt111='.$tmp_dir);
        if (!is_dir($tmp_dir)){
         //  send_error('Не удается определить временную директорию системы.');
            $tmp_dir_ok = false;
        }
    }

    // сохраняем значение временной директории в сессии
    if ($tmp_dir_ok){
         $_SESSION['upload_tmp_dir'] = $tmp_dir;
    }
}

if (!is_dir(DIR_FILES_SITE)){
    create_dir(DIR_FILES_SITE);
}if (!is_dir(DIR_FILES_SITE_SMALL)){
    create_dir(DIR_FILES_SITE_SMALL);
}if (!is_dir(DIR_FILES_SITE_MINI)){
    create_dir(DIR_FILES_SITE_MINI);
}
 // класс загрузки файлов
 class UploadHandler
{
    private $upload_dir;
    private $upload_url;
    private $thumbnails_dir;
    private $thumbnails_url;
    private $thumbnail_max_width;
    private $thumbnail_max_height;
    public $mini_max_width;
    public $mini_max_height;
    public $field_name;
    private $type_view;
    public $set_result=1;

    function __construct($options_uploads_class) {
        $this->upload_dir = $options_uploads_class['upload_dir'];
        $this->upload_url = $options_uploads_class['upload_url'];
        $this->thumbnails_dir = $options_uploads_class['thumbnails_dir'];
        $this->thumbnails_url = $options_uploads_class['thumbnails_url'];
        $this->mini_dir = $options_uploads_class['mini_dir'];
        $this->mini_url = $options_uploads_class['mini_url'];
        $this->thumbnail_max_width = $options_uploads_class['thumbnail_max_width'];
        $this->thumbnail_max_height = $options_uploads_class['thumbnail_max_height'];
         $this->mini_max_width = $options_uploads_class['mini_max_width'];
        $this->mini_max_height = $options_uploads_class['mini_max_height'];
        $this->field_name = $options_uploads_class['field_name'];
        $this->type_view = $options_uploads_class['type_view'];
        $this->mini_max_width = $options_uploads_class['mini_max_width'];
        $this->mini_max_height = $options_uploads_class['mini_max_height'];
        $this->module_id = $options_uploads_class['id_elem'];
        $this->module = $options_uploads_class['module'];
        $this->file_size = $options_uploads_class['file_size'];
        $this->name_file = $options_uploads_class['name_file'];
        $this->file_type = $options_uploads_class['file_type'];
        $this->file_data = $options_uploads_class['file_data'];
        $this->name_field = $options_uploads_class['name_field'];
    }

    public function post() {


// Получаем расширение файла
//$getMime = explode('.', $options_uploads_class['name_file']);
//$mime = end($getMime);

  
    $type_file= $this->file_type;   
    $size= $this->file_size;   
   //list($name_file_, $size, $type_file, $error_uplod)=upload_file($this->field_name,$this->upload_dir,$this->type_view);
   list($name_file_, $error_uplod)=upload_file_new($this->file_data,$this->name_file,$this->file_size,$this->file_type,$this->upload_dir, $this->type_view);
   
        $info['error']=$error_uplod;  //

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        //    header('Content-type: application/json');
        } else {
          //      header('Content-type: text/plain');
        }
        if (empty($error_uplod)){
       // это графичиские файлы и нужно выводить миниатюру
         if (strpos($type_file,"image/")!==false) {
           $is_img=1;
           if (file_exists($this->upload_dir.$name_file_)){
           list($bfile, $error_f)=upload_image_size($this->upload_dir.$name_file_,$this->upload_url.$name_file_, $this->thumbnails_dir, $this->thumbnail_max_width, $this->thumbnail_max_height) ;
           // для сайта размер изображения
           list($bfile_, $error_f_)=upload_image_size($this->upload_dir.$name_file_,$this->upload_url.$name_file_, $this->mini_dir, $this->mini_max_width, $this->mini_max_height) ;
               }else{
                    send_error('Не передался файл при загрузке!!!');               } 
                if (!$bfile){
                    $info['error']=$error_f;
                    send_error($error_f);
                }
                $info['thumbnail']= is_file($this->thumbnails_dir.$name_file_) ? $this->thumbnails_url.rawurlencode($name_file_) : '';
         }
         else {
           $is_img=0;
           $info['thumbnail']=file_type_name($type_file,$name_file_);
         }
         $info['name']=$name_file_;
            $info['size']=$size;

            $info['type']=$type_file;
            $info['url']= $this->upload_url.rawurlencode($name_file_);

   // запись в БД
   
        db_query('insert into `' .T_FILES. '` set name="'.$name_file_.'",
                    type="'.$type_file.'",
                    size='.$size.',
                    module="'.$this->module.'",
                    id_elem="'.$this->module_id.'",
                    field="'.$this->name_field.'",
                    is_img='.$is_img);
            $info['id']= db_insert_id();
         db_query('update `'.get_table_name($this->module).'` set '.$this->name_field.'='.$info['id'].' where id='.$this->module_id);
 
        }else{
           // s($error_uplod.'!!!');
            //$info->id=0;
        }

      // $info['name_field']= get('name_field_') ? get('name_field_') :'file';
     
     //   echo '../'.URL_FILES_SITE_.$name_file_.":uploaded:".$info['id'];
     //   exit;
        // echo array2json($info);
      }

    public  function delete() {
         $file_name=db_field('select name from `'.T_FILES.'` where id='.$_REQUEST['id'],'name');
         //$file_name = isset($_REQUEST['file']) ?  basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->upload_dir.$file_name;
        $thumbnail_path = $this->thumbnails_dir.$file_name;
        $mini_path = $this->mini_dir.$file_name;
         $success = is_file($file_path) && $file_name[0] !== '.' && delete_file($file_path);
         db_query('delete from `'.T_FILES.'` where id='.$_REQUEST['id'],'name');
         db_query('update `'.get_table_name($this->module).' set '.$this->name_field.'="" where id='.$this->module_id);
    if ($success && is_file($thumbnail_path)) {
            delete_file($thumbnail_path);
            delete_file($mini_path);
           }
        if ($this->set_result) 
       {
            return $file_name;            
       }else
       { 
        header('Content-type: application/json');
        echo array2json($success);
       }    
        
    }
}
function file_type_name($type_file,$name_file){
   $ext=strtolower(substr($name_file,(strrpos($name_file,".")+1)));

	  switch ($type_file) {
             case 'application/octet-stream':
             switch ($ext) {
               case 'rar':
                    return URL_A.'img/files_type/rar.png';
                 break;
               case 'zip':
                    return URL_A.'img/files_type/zip.png';
			     break;
               case 'ini':
                    return URL_A.'img/files_type/ini.png';
                 break;
                 case 'exe':
                    return URL_A.'img/files_type/exe.png';
                 break;
                   case 'dll':
                    return URL_A.'img/files_type/dll.png';
                 break;
                  case 'chm':
                    return URL_A.'img/files_type/chm.png';
                 break;
                 case 'psd':
                    return URL_A.'img/files_type/psd.png';
                 break;
                  case 'sql':
                    return URL_A.'img/files_type/txt.png';
                 break;
                   case 'mp4':
                      return URL_A.'img/files_type/mp4.png';
                 break;
                   case '3gp':
                    return URL_A.'img/files_type/3gp.png';
                 break;

               default :
                   return URL_A.'img/files_type/order.png';
             }
               break;
             case 'application/msword':
             if ($ext=='doc')
                 return URL_A.'img/files_type/doc.png';
            else
           		 return URL_A.'img/files_type/rtf.png';
               break;
             case 'application/vnd.ms-excel':
                   return URL_A.'img/files_type/xls.png';
               break;
             case 'application/pdf':
                   return URL_A.'img/files_type/pdf.png';
               break;
             case 'text/plain':
             	   return URL_A.'img/files_type/txt.png';
              	break;
         		case 'text/html':
         		    return URL_A.'img/files_type/html.png';
         		    break;
         	case 'text/css':
         		    return URL_A.'img/files_type/css.png';
         		    break;
           	case 'application/x-javascript':
         		    return URL_A.'img/files_type/js.png';
         		    break;
         		case 'audio/mpeg':
         		    return URL_A.'img/files_type/mp3.png';
         		    break;
         	case 'video/x-ms-wmv':
         		    return URL_A.'img/files_type/wmv.png';
         		    break;
         	case 'image/x-icon':
         		    return URL_A.'img/files_type/ico.png';
         		    break;
         	case 'video/avi':
         	case 'video/x-msvideo':
         		    return URL_A.'img/files_type/avi.png';
         		    break;
         	case 'video/mpeg':
         		    return URL_A.'img/files_type/mpg.png';
         		    break;
    default :
                   return URL_A.'img/files_type/mmm.png';
           }
}

?>