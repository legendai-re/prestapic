$("#pp_requestbundle_image_request_title").one('focus', function () {
   $('#requestFormRest').slideToggle();
   $('.content').removeClass("init");
});

$(".signInUp").click(function () {
   $('#signInUpOver').fadeIn();  
});

$("#signInUpOverClose").click(function () {
   $('#signInUpOver').fadeOut();  
});