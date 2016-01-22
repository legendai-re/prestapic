jQuery.fn.preventDoubleSubmission = function() {
  $(this).on('submit',function(e){
    var $form = $(this);

    if ($form.data('submitted') === true) {
      // Previously submitted - don't submit again
      e.preventDefault();
    } else {
      // Mark it so that the next submit can be ignored
      $form.data('submitted', true);
    }
  });
  // Keep chainability
  return this;
};

$(".addRequestForm").preventDoubleSubmission();

$("#sendRequestOverClose").click(function () {
   $('#sendRequestOver').fadeOut();  
});

$("html").click(function () {
   $('#sendRequestOver').fadeOut();   
});

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

var showBannerAlert = function(type, strongMessage, lightMessage){    
    $("#alert-banner").slideDown("slow");
    $("#alert-banner").addClass(type);
    $("#alert-banner-strong").html(strongMessage);
    $("#alert-banner-span").html(lightMessage); 
    setTimeout(function() {
        $("#alert-banner").slideUp("slow",  function() {$("#alert-banner").removeClass(type);});        
    }, 5000);
}

$(function(){
    var shrinkHeader = 80;
    $(window).scroll(function() {
      var scroll = getCurrentScroll();
        if ( scroll >= shrinkHeader ) {
             $('section.alert-banner').addClass('fixed');
          }
          else {
              $('section.alert-banner').removeClass('fixed');
          }
    });
    function getCurrentScroll() {
        return window.pageYOffset || document.documentElement.scrollTop;
    }
});