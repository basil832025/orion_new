<?php
    // максимальное количество записей на странице, если не указано другое число, это по умолчанию
  define('PAGE_ITEMS',        9);
  // количество групп по-умолчанию для страниц
  define('PAGE_GROUPS',       12);

/*Данный класс генерирует форму для сайта или для админки*/
class WorkMaketSite{
protected $maket = '';


// записуется шаблон куда идет обработка
// конструктор формы считвает в массив инфо по даной форме шапку
//function  form_class($url='',$module_='', $form_num=1){
function  __construct(){

   
   $this->id_form=GlobalData::getIdForm();
   $this->post_action=GlobalData::getPostAction();
   $this->post_module=GlobalData::getPostModule();
   $this->aform=GlobalData::getAForm();
  // s(FIRST_URL);
   //send_error(p($form_master,1));
}

function start() {
   //   $is_admin=(!empty($is_admin)? $is_admin : $this->is_admin);
 //  $this->get_url(); // обработали url
if (CNT_ELEM==1 && file_exists(ROOT.URL_SITE.'module_html/html/'.FIRST_URL.'.html')){
    
}else{
   if (GlobalData::getParts()){
    //  $this->getModule();
     // $this->getFormMaster();
     // $this->getFormFealds();
      
   }else{
      $this->set404();// если не нашли такого урла то 404
   }
}

   if (FIRST_URL && FIRST_URL!='index.html'){
      /*            else{ // пока не помню для чего это делал ))))
      if (file_exists('../module_html'.URL_)){
      include '../module_html'.URL_;
      $return_html_module_  = ob_get_contents();
      ob_clean();
      //   list($return_html_module, $prod)=tegs($return_html_module_, 1);

      }
      }*/
   }
   return;
}


// получаем всю инфу по данном урле с таблицы разделов, если нет такого урла возвращаем false для 404 ошибки

//404 ошибка
function set404(){
   redirect(URL .'404.html');
}
function setMaket() {
   ob_start();
   $maket = $this->maket ? $this->maket : 'maket';
   // новая идея можно разные шаблоны отоброжать
         // обрабатываем общий шаблон
   if (file_exists(ROOT.URL_SITE.'module_html/html/'.FIRST_URL.'.html')){
        include_once ROOT.URL_SITE.'module_html/html/'.FIRST_URL.'.html';
   }else if (file_exists(ROOT.URL_SITE.'module_html/'.$maket.'.html')){
      include_once ROOT.URL_SITE.'module_html/'.$maket.'.html';
   }
   else{
      // нету не одного файла дла вывода логического выходим
      send_error('Нету ни шаблона, ни статического файла');
      exit;
   }  
   
   $this->maket_text  = ob_get_contents();

   ob_clean();
   if ($this->post_action && $this->post_module && file_exists(ROOT.URL_SITE.'module_html/'.$this->post_module.'/action/'.$this->post_action.'.bas')){
      //send_error('sss11111 '.$this->post_action);
               include ROOT.URL_SITE.'module_html/'.$this->post_module.'/action/'.$this->post_action.'.bas';
    } else {
      $oTegWork = new TegsWork(''); 
    //  $oTegWork->setModule_name($this->module_name);
      $oTegWork->tegs_work($this->maket_text);
     $this->maket_text = $oTegWork->getHtml(); 
   //$this->maket_text= $this->tegs_work($this->maket_text);
      // отобразить если нужно всплывающее окно корзины
   if (defined('CART_VIEW') && CART_VIEW==1){
   $this->cart_view();
   }
   echo $this->maket_text;
    }
   return;
}

// обработка окна корзины
function cart_view() {
    $cart_txt='';
    if (!empty($_SESSION['cart']['cnt_all'])){
        $cnt_cart=$_SESSION['cart']['cnt_all'];
        // получить url на каталог 
        $url_cat=db_field('select url from `'.T_PARTS.'` where parts_modules_id=10','url');
        $tov_text=($cnt_cart==1 ? 'товар' :($cnt_cart>1 && $cnt_cart<5 ? 'товара':'товаров') );
    $cart_txt='<div id="fixed">
            <div class="carta gallery_a"><a href="'.$url_cat.'/cart.html#inline_zakaz" rel="prettyPhoto[zakaz]" tov="0" id="cart_cnt">В корзине '.$cnt_cart.' '.$tov_text.'</a></div>
            <div class="mar15" id="cart_summa">на '.$_SESSION['cart']['summa_all'].' р.</div>
            <div class="zakaz_cart mar25"><a href="'.$url_cat.'/zakaz.html"> Оформить заказ</a></div>
            </div>
   </div>';
       preg_match('#^(.*)(<\s*body\s*>.*)<\s*/body\s*>.*#is',$this->maket_text,$aIni);
   $this->maket_text=(!empty($aIni[1]) ? $aIni[1].$aIni[2]."\r\n".$cart_txt."\r\n</body>": '');
       
    }
    return $cart_txt;
}




 
}

?>