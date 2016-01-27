    
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
    
    containerApp.run(['$rootScope', '$location', function ($rootScope, $location) {
            
        $rootScope.currentPath = $location.$$path;
    }])

    containerApp.controller('profileController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {
            
            
            this.showProfileOptions = function(){
                 if($("#profileOptions").css("display") == "block"){
                     $('#profileOptions').css("display", "none");
                 }else{
                     $('#profileOptions').css("display", "block");
                 }
            }
            
            $('html').click(function() {                
                $('#profileOptions').css("display", "none");          
            });
            
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
                          div.innerHTML = ['<div class="cover-upload" style="background-image: url(', e.target.result,
                                            ')"></div>'].join('');                          
                          
                        };
                      })(f);
                      reader.readAsDataURL(f);                    
                }
            }
            
            var canFollow = true;
            this.patchFollow = function(){
                if(canFollow){
                    canFollow = false;
                    if($("#followButton").html() == "Follow")$("#followButton").html("Unfollow")
                    else $("#followButton").html("Follow")
                    var formAction = document.forms["pp_user_api_patch_user_follow_form"].action;
                    var myData = {};                    
                    $http({
                        method: 'PATCH',
                        url: formAction,                    
                        data: JSON.stringify(myData)
                     }). 
                        then(function(response) {
                            canFollow = true;                            
                        }, function(response) {
                            console.log("Request failed : "+response.statusText );
                            canFollow = true; 
                        }
                    );
                }
            }
            
            var canBlock = true;
            this.patchBlock = function(id){                
                if(canBlock){                   
                    if($("#blockButton").html() == "Block")$("#blockButton").html("Unblock")
                    else $("#blockButton").html("Block")
                    
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
                    $("#setModeratorButton").html("Unset moderator");
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
                            $("#editProfileForm").preventDoubleSubmission();
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
    
    var DISPLAY_REQUEST = 1;
    var DISPLAY_PROPOSITION = 2;
    containerApp.controller('requestsController', ['$rootScope', '$scope', '$http', '$compile', '$location', function ($rootScope, $scope, $http, $compile, $location) {
            
            var pageProfileId = null;
            this.contentToDisplay = null;
            var readyToChange = true;
            var getParams = "?";
            
            this.init = function(id, contentToDisplay){
                if(contentToDisplay == DISPLAY_REQUEST){
                    $('#widgetGallery').css("display", "block");
                }else{
                    $('#widgetGallery').css("display", "none");
                }
                this.contentToDisplay = contentToDisplay;
                pageProfileId = id;
                getRequests(1);                                
            }
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                                              
            
            var getRequests = function(page){
                document.getElementById('loadingGif').style.display = 'block';                
                var formAction = document.forms["pp_user_api_get_user_request"].action;
                
                $http.get(formAction+".html"+getParams+"&page="+page).
                    then(function(response) {                                              
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage1')).append(newPage);  
                        document.getElementById('loadingGif').style.display = 'none';

                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );                                            
            }                       
            
            this.update = function(contentToDisplay){                
                if(readyToChange){
                    if(contentToDisplay == DISPLAY_REQUEST){
                        $('#widgetGallery').css("display", "block");
                    }else{
                        $('#widgetGallery').css("display", "none");
                    }
                    $("#loadPage1").html("");
                    nextLoadTrigger = '#loadPageTrigger2';            
                    nextPage = 2;
                    getParams = "?content_to_display_profile="+contentToDisplay;
                    getRequests(1);
                    this.contentToDisplay = contentToDisplay;                                                            
                }
            }; 
            
            this.updateMode = function(mode){
                if(readyToChange){
                    this.contentToDisplay = mode;
                    $('.section').removeClass("selected");
                    $('#mode_'+mode).addClass("selected");
                    this.update(this.contentToDisplay);
                }
            }                        
            
            var requestsController = this;
            
            $("#widgetShowGalleryButton").click(function(){
                requestsController.updateMode(2);
            });
            
            var readyForRequestVote = true;
            var upvotedRequest = []
            this.postRequestVote = function(id){
                if(readyForRequestVote && upvotedRequest[id] == null){
                    readyForRequestVote=false;
                    upvotedRequest[id] = true;
                    $("#imageRequestUpvoteButton_"+id).addClass("animate");
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
                    url: $rootScope.currentPath
                }
                angular.element(document.getElementById('popupPropApp')).scope().$emit('showPopup', message);                                                
            };
            
            var readyForPropositionVote = true;
            var upvotedPropositions = [];
            this.postPropositionVote = function(propositionId){
                if(readyForPropositionVote && upvotedPropositions[propositionId] == null){
                    upvotedPropositions[propositionId] = true;
                    readyForPropositionVote = false;
                    $("#propositionUpvoteButton_"+propositionId).addClass("animate");
                    $("#propositionUpvoteButton_"+propositionId).addClass("voted");
                    document.getElementById('propositionUpvoteButton_'+propositionId).innerHTML = parseInt($('#propositionUpvoteButton_'+propositionId).html())+1; 
                    var myData = {
                        id: propositionId
                    }
                    var formAction = document.forms["pp_proposition_api_patch_proposition_vote_form"].action;
                    $http({
                        method: 'PATCH',
                        url: formAction,                    
                        data: JSON.stringify(myData)
                         }).
                        then(function(response){
                            readyForPropositionVote = true;
                        },function(response) {
                            console.log("Request failed : "+response.statusText );
                            readyForPropositionVote = true;
                        }
                    );
                }
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
    
    containerApp.controller('galleryController', ['$rootScope', '$scope', '$http', '$compile', '$location', '$window', function ($rootScope, $scope, $http, $compile, $location, $window) {
        
        this.showPopup = function(id){
            var message = {
                id: id,
                url: $rootScope.currentPath
            }
            angular.element(document.getElementById('popupPropApp')).scope().$emit('showPopup', message);                                                
        };
        
    }]);