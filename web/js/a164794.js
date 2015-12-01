angular.element(document).ready(function(){var a=document.getElementById("containerApp");angular.bootstrap(a,["containerApp"])});var containerApp=angular.module("containerApp",["ngRoute"]);containerApp.config(["$locationProvider",function(a){a.html5Mode(true)}]);containerApp.service("FayeClient",function(){return new Faye.Client("http://alexandrejolly.com:3000/")});containerApp.run(function(a,b){b.subscribe("/messages",function(c){a.$broadcast("notification",c)})});containerApp.controller("profileController",["$scope","$http","$compile","$location","$window",function(k,j,g,h,c){function a(m){console.log(m.target);var o=m.target.files;for(var n=0,p;p=o[n];n++){if(!p.type.match("image.*")){continue}var l=new FileReader();l.onload=(function(q){return function(r){var s=document.getElementById("profileImageContainer");s.innerHTML=['<img src="',r.target.result,'" title="',escape(q.name),'"/>'].join("")}})(p);l.readAsDataURL(p)}}function e(m){console.log(m.target);var o=m.target.files;for(var n=0,p;p=o[n];n++){if(!p.type.match("image.*")){continue}var l=new FileReader();l.onload=(function(q){return function(r){var s=document.getElementById("coverImageContainer");s.innerHTML=['<img src="',r.target.result,'" title="',escape(q.name),'"/>'].join("")}})(p);l.readAsDataURL(p)}}this.patchFollow=function(){var l=document.forms.pp_user_api_patch_user_follow_form.action;var m={};j({method:"PATCH",url:l,data:JSON.stringify(m)}).then(function(n){var o=JSON.parse(n.data);document.getElementById("followButton").innerHTML=o.newValue},function(n){console.log("Request failed : "+n.statusText)})};var d=true;this.patchModerator=function(n){d=false;if($("#setModeratorButton").html()=="Set moderator"){$("#setModeratorButton").html("unset moderator")}else{$("#setModeratorButton").html("Set moderator")}var l=document.forms.pp_user_api_patch_moderator_form.action;var m={id:n};j({method:"PATCH",url:l,data:JSON.stringify(m)}).then(function(o){d=true},function(o){console.log("Request failed : "+o.statusText);d=true})};var f=false;this.getEditProfileForm=function(){if(!f){f=true;var l=document.forms.pp_user_api_get_edit_profile_form.action;j.get(l+".html").then(function(m){var n=angular.element(m.data);g(n)(k);$("#profileHeaderContainer").css("display","none");angular.element(document.querySelector("#editProfilContainer")).append(n);document.getElementById("pp_userbundle_profile_edit_profilImage_file").addEventListener("change",a,false);document.getElementById("pp_userbundle_profile_edit_coverImage_file").addEventListener("change",e,false);$(".editProfileForm").preventDoubleSubmission()},function(m){console.log("Request failed : "+m.statusText)})}else{b()}};this.reportData={ticketType:3,targetId:null,reasonId:1,details:null};var i=false;this.postReport=function(m){if(!i){i=true;this.reportData.targetId=m;console.log(this.reportData);var l=document.forms.pp_report_api_post_report_ticket_form.action;j({method:"POST",url:l,data:JSON.stringify(this.reportData)}).then(function(n){},function(n){console.log("Request failed : "+n.statusText)})}};this.postDisableRequest=function(m){if(!i){i=true;this.reportData.targetId=m;console.log(this.reportData);var l=document.forms.pp_report_api_post_disable_ticket_form.action;j({method:"POST",url:l,data:JSON.stringify(this.reportData)}).then(function(n){c.location.href=h.$$absUrl},function(n){console.log("Request failed : "+n.statusText)})}};var b=function(){$("#profileHeaderContainer").css("display","none");$("#editProfilContainer").css("display","block")};this.cancelEditProfile=function(){$("#profileHeaderContainer").css("display","block");$("#editProfilContainer").css("display","none")}}]);containerApp.controller("requestsController",["$scope","$http","$compile","$location",function(i,f,a,b){var d=null;this.init=function(j){d=j;c(1)};var e="#loadPageTrigger2";var g=2;var c=function(k){document.getElementById("loadingGif").style.display="block";var j=document.forms["pp_user_api_get_user_request_form_"+k].action;f.get(j+".html").then(function(l){var m=angular.element(l.data);a(m)(i);angular.element(document.querySelector("#loadPage"+k)).append(m);document.getElementById("loadingGif").style.display="none"},function(l){console.log("Request failed : "+l.statusText)})};var h=true;this.postRequestVote=function(l){if(h){h=false;$("#imageRequestUpvoteButton_"+l).addClass("voted");document.getElementById("imageRequestUpvoteButton_"+l).innerHTML=parseInt($("#imageRequestUpvoteButton_"+l).html())+1;var k={id:l};var j=document.forms.pp_request_api_patch_request_vote.action;f({method:"PATCH",url:j,data:JSON.stringify(k)}).then(function(m){h=true},function(m){console.log("Request failed : "+m.statusText);h=true})}};$(window).scroll(function(){if($(e).offset()!=null){var l=$(e).offset().top,m=$(e).outerHeight(),k=$(window).height(),j=$(this).scrollTop()}if(j>(l+m-k)){c(g);g++;e="#loadPageTrigger"+g}})}]);