
var count_seat=0;
let price_seat = 500;
let arr_seats = [];
$('.legak').on('click', function (e) {
    e.preventDefault();
    seat = $(this).attr('seat'); // нажатое место
  //  console.log(seat)
    if ($(this).hasClass('selected')) {
        // существует
         $(this).removeClass('selected');
        del_arr_seat(seat);
        count_seat--;
        set_seat(count_seat);
    } else {
        // не существует
        $(this).addClass('selected');
        arr_seats.push(seat);
        count_seat++;
        set_seat(count_seat);
    }
console.log('res',arr_seats)
});
function set_seat(count_seat)
{
    sm= count_seat*price_seat;
 $('#cnt_seat').html(count_seat)
 $('#sum_seats').html(sm)
    if (count_seat==1) $('#ukrmisce').html('місце')
    if (count_seat==0 || count_seat>4) $('#ukrmisce').html('місць')
    if (count_seat>1 && count_seat<5) $('#ukrmisce').html('місця')
}
function del_arr_seat(dSeat){
    arr_seats.forEach(function(item, i){
        // ищем вхождение в массив
        // элемента, совпадающего с введенным
        if(item == dSeat){
            // удаление элемента по индексу
            arr_seats.splice(i, 1);
        }
    });
}