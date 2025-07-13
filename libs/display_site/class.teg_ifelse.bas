<?php
// класс обрабатывает теги служебные
class TegIfElse //extends WorkMaketSite
{
 private $html = ''; // текст для вывода обработанные
 private $aData = array(); // модуль

 public function __construct($html='',$aData=array()) // конструктор
 {
    $this->html = $html;
    $this->aData = $aData;
    $this->workTeg();
 }
function workTeg()
{ 
  if (preg_match_all('#<\s*if(\d+)(\s+[^>]*)?>(.*?)(?:<else\1>(.*?))?<\s*/if\1\s*>#is',$this->html, $aContens)){
               // p($aContens);
                if (!empty($aContens[2])){
                   // p($aContens[2]);
                 foreach ($aContens[2] as $kif => $vif) {
               $text ='';
               
               
             //  p($this->varible); echo  "ifif \n\n";
               //    p($aContens);
               // цикл обработка повторяещего кода с обработкой тегов
               $parametrs_teg = (!empty($vif) ? $vif: '');
               $parametrs_teg=preg_replace('#\s*([a-z_]+)="(.*)"\s*#is','$' .'\\1="\\2"; ',$parametrs_teg);
              // echo    $parametrs_teg;
               eval($parametrs_teg);
               $is = isset($is) ? $is :'';
              // echo $is.'<br>';
              
             // обработка переменных в выражении
               if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$is,$aIs)){
              foreach ($aIs[1] as $key => $value) {
$text='';          // echo $value.'<br>';
                  if (!empty($aIs[2][$key])) {$txt= 'GlobalData::getVal("'.trim($value).'","'.trim($aIs[2][$key]).'")';   }
                  else{
                  $txt= 'GlobalData::getVal("'.trim($value).'")';
                  }
                 $is=preg_replace('/#.*?#/is',$txt,$is,1);
                 // echo $is.'<br>';
                }
                   
              // p(GlobalData::getVal("aData"));       
             }
  
             eval('
                  $oIs = new TegsWork();
                  $oIs->aData=$this->aData;
             
                if ('.$is.'){
                $oIs->tegs_work((!empty($aContens[3]['.$kif.']) ? $aContens[3]['.$kif.'] : ""));
               
             }else{
                 $oIs->tegs_work((!empty($aContens[4]['.$kif.']) ? $aContens[4]['.$kif.'] : ""));
             }    
             $text .= $oIs->getHtml();         ');
                 
            // echo $text."*****\n\n";
            $this->html = preg_replace('#<\s*if(\d+)(\s+[^>]*)?>(.*)<\s*/if\1\s*>#is', $text, $this->html,1);
                 }// конец цикла 
                }
            }
 }
 function getHtml()
 {
    return $this->html;
 }
 }