jQuery.fn.preventDoubleSubmission=function(){$(this).on("submit",function(b){var a=$(this);if(a.data("submitted")===true){b.preventDefault()}else{a.data("submitted",true)}});return this};$(".addRequestForm").preventDoubleSubmission();$("#pp_requestbundle_image_request_title").one("focus",function(){$("#requestFormRest").slideToggle();$(".content").removeClass("init")});$(".signInUp").click(function(){$("#signInUpOver").fadeIn()});$("#signInUpOverClose").click(function(){$("#signInUpOver").fadeOut()});var showBannerAlert=function(b,a,c){$("#alert-banner").slideDown("slow");$("#alert-banner").addClass(b);$("#alert-banner-strong").html(a);$("#alert-banner-span").html(c);setTimeout(function(){$("#alert-banner").slideUp("slow",function(){$("#alert-banner").removeClass(b)})},5000)};$(function(){var b=80;$(window).scroll(function(){var c=a();if(c>=b){$("section.alert-banner").addClass("fixed")}else{$("section.alert-banner").removeClass("fixed")}});function a(){return window.pageYOffset||document.documentElement.scrollTop}});