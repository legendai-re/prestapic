    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });
    
    var containerApp = angular.module('containerApp',  ['ngRoute']);       
    
    containerApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
    /* node */
    containerApp.service('FayeClient', function () {
            return new Faye.Client('http://alexandrejolly.com:3000/');
    })

    // souscription au channel "/messages"
    
    containerApp.run(['$rootScope','FayeClient', function ($rootScope, FayeClient) {
        FayeClient.subscribe('/messages', function (message) {
            $rootScope.$broadcast('notification', message);            
        });
    }])

    containerApp.controller('profileController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {

            function handleProfileFileSelect(evt) {
                    console.log(evt.target);
                    var files = evt.target.files; // FileList object
                   
                    for (var i = 0, f; f = files[i]; i++) {                                          
                      if (!f.type.match('image.*')) {
                        continue;
                      }

                      var reader = new FileReader();                                            
                      reader.onload = (function(theFile) {
                        return function(e) {
                          var div = document.getElementById('profileImageContainer');
                          div.innerHTML = ['<img src="', e.target.result,
                                            '" title="', escape(theFile.name), '"/>'].join('');                          
                          
                        };
                      })(f);
                      reader.readAsDataURL(f);                    
                }
            }
            
            function handleCoverFileSelect(evt) {
                    console.log(evt.target);
                    var files = evt.target.files; // FileList object
                   
                    for (var i = 0, f; f = files[i]; i++) {                                          
                      if (!f.type.match('image.*')) {
                        continue;
                      }

                      var reader = new FileReader();                                            
                      reader.onload = (function(theFile) {
                        return function(e) {
                          var div = document.getElementById('coverImageContainer');
                          div.innerHTML = ['<img src="', e.target.result,
                                            '" title="', escape(theFile.name), '"/>'].join('');                          
                          
                        };
                      })(f);
                      reader.readAsDataURL(f);                    
                }
            }
            
            
            this.patchFollow = function(){                
                var formAction = document.forms["pp_user_api_patch_user_follow_form"].action;
                
                var myData = {};
                
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                 }). 
                    then(function(response) {
                        var jsonResponse = JSON.parse(response.data);                            
                        document.getElementById('followButton').innerHTML = jsonResponse.newValue;
                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );   
            }
            
            var canBlock = true;
            this.patchBlock = function(id){                
                if(canBlock){                   
                    if($("#blockButton").html() == "block")$("#blockButton").html("unblock")
                    else $("#blockButton").html("block")
                    
                    canBlock = false;
                    var formAction = document.forms["pp_user_api_patch_blocked_form"].action;
                    var myData = {
                        idToBlock: id
                    };

                    $http({
                        method: 'PATCH',
                        url: formAction,                    
                        data: JSON.stringify(myData)
                     }). 
                        then(function(response) {
                            canBlock = true;
                        }, function(response) {
                            canBlock = true;
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );
                }
            }
            
            var canPatchModerator = true;
            this.patchModerator = function(id){
                canPatchModerator = false;
                if($("#setModeratorButton").html() == "Set moderator"){
                    $("#setModeratorButton").html("unset moderator");
                }else{
                     $("#setModeratorButton").html("Set moderator");
                }
                var formAction = document.forms["pp_user_api_patch_moderator_form"].action;                
                var myData = {
                    id: id
                };                
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                 }). 
                    then(function(response) {                       
                        canPatchModerator = true;
                    }, function(response) {
                        console.log("Request failed : "+response.statusText );
                        canPatchModerator = true;
                    }
                );   
            }                        
            
            var haveLoadEditForm = false;
            this.getEditProfileForm = function(){
                if(!haveLoadEditForm){
                    haveLoadEditForm = true;
                    var formAction = document.forms["pp_user_api_get_edit_profile_form"].action;                
                    $http.get(formAction+".html").
                        then(function(response) {                                    
                            var editForm = angular.element(response.data);                        
                            $compile(editForm)($scope);
                            $('#profileHeaderContainer').css("display", "none");
                            angular.element( document.querySelector('#editProfilContainer')).append(editForm);
                            document.getElementById('pp_userbundle_profile_edit_profilImage_file').addEventListener('change', handleProfileFileSelect, false);
                            document.getElementById('pp_userbundle_profile_edit_coverImage_file').addEventListener('change', handleCoverFileSelect, false);
                            $(".editProfileForm").preventDoubleSubmission();
                        }, function(response) {
                         console.log("Request failed : "+response.statusText );                        
                        }
                    );
                }else{
                    showEditProfile();
                }
            }            
            
            this.showReportPopup = function(id, type){                
                var message = {
                    id: id,
                    type: type
                }
                angular.element(document.getElementById('reportPopupApp')).scope().$emit('showPopup', message);                                                
            };                       
            
            var showEditProfile = function(){
                $('#profileHeaderContainer').css("display", "none");
                $('#editProfilContainer').css("display", "block");
            }
            
            this.cancelEditProfile = function(){
                $('#profileHeaderContainer').css("display", "block");
                $('#editProfilContainer').css("display", "none");
            }
    }]);
    
    containerApp.controller('requestsController', ['$scope', '$http', '$compile', '$location', function ($scope, $http, $compile, $location) {
            
            var pageProfileId = null;
            
            this.init = function(id){
                pageProfileId = id;
                getRequests(1);                                
            }
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                      
            
            var getRequests = function(page){
                document.getElementById('loadingGif').style.display = 'block';                
                var formAction = document.forms["pp_user_api_get_user_request_form_"+page].action;
                
                $http.get(formAction+".html").
                    then(function(response) {                                              
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage'+page)).append(newPage);  
                        document.getElementById('loadingGif').style.display = 'none';

                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );                                            
            }                       
            
            var readyForRequestVote = true;
            this.postRequestVote = function(id){
                if(readyForRequestVote){
                    readyForRequestVote=false;
                    $("#imageRequestUpvoteButton_"+id).addClass("voted");
                    document.getElementById('imageRequestUpvoteButton_'+id).innerHTML = parseInt($('#imageRequestUpvoteButton_'+id).html())+1;                            
                    var myData = {
                        id: id
                    }
                    var formAction = document.forms["pp_request_api_patch_request_vote"].action;
                    $http({
                        method: 'PATCH',
                        url: formAction,                    
                        data: JSON.stringify(myData)
                         }).               
                        then(function(response) {
                            readyForRequestVote = true;                            
                        }, function(response) {
                            console.log("Request failed : "+response.statusText );
                            readyForRequestVote = true;
                        }                                 
                    );
                }
            }
            
            this.showPopup = function(id){
                var message = {
                    id: id,
                    url: $location.$$absUrl
                }
                angular.element(document.getElementById('popupPropApp')).scope().$emit('showPopup', message);                                                
            };
            
            $(window).scroll(function() {
                if($(nextLoadTrigger).offset() != null){
                var hT = $(nextLoadTrigger).offset().top,
                    hH = $(nextLoadTrigger).outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();
                }
                if (wS > (hT+hH-wH)){
                   getRequests(nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });
                       
    }]);
    
