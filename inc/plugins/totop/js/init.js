$(document).ready(function(){
$("#backtop").hide();
  $(window).scroll(function () {
    if ($(this).scrollTop() > 400) {
      $('#backtop').fadeIn(200);
    } else {
      $('#backtop').fadeOut(200);
    }
  });
  $('.go-top').click(function () {
    $('html,body').animate({
      scrollTop: 0
    }, 1000);
    return false;
  });
});
