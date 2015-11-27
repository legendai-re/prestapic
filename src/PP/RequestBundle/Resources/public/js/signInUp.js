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