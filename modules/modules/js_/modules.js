function send_edit_modules(){
    alert('tyt')

     str='';
      if (getOb('name_').value.length < 1){
          str='Введите название модуля!<br />'; 
      }
    //  if (getOb('date_') != null && getOb('date_').value!='' && !validdate(getOb('date_').value)){
     //     str='Не правильный формат даты для Сайта!'; 
    //  }
      if (str){
        window_div(str); 
        return false;
      }
      else
      return formElements('form_edit_form', ['content_', 'anons_']);
     
} 