    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });
    
    var containerApp = angular.module('containerApp',  ['ngRoute']);
    
    containerApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
    }]);                 
                        
    containerApp.controller('requestController',['$scope', '$http', function ($scope, $http) {                                                                                                                                                                                                                                          
                        
            this.postRequestVote = function(id){                                                  
                var myData = {}
                var formAction = document.forms["pp_request_api_patch_request_vote"].action;
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                     }).               
                    then(function(response) {
                        var jsonResponse = JSON.parse(response.data);    
                        if(jsonResponse.succes){                        
                            $("#imageRequestUpvoteButton").addClass("voted");
                            document.getElementById('imageRequestUpvoteButton').innerHTML = jsonResponse.upvote;
                        }
                    }, function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }                                 
                );
            }
    }]);
    
    containerApp.controller('propositionsController', ['$scope', '$http', '$compile', '$window', function ($scope, $http, $compile, $window) {                                                                                                 
            
            var imageRequestId = null;
            
            this.init = function(id){
                getPropositions(id, 1);
                imageRequestId = id;
            }
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;
                        
            
            var getPropositions = function(imageRequestId, page){                    
                    document.getElementById('loadingGif').style.display = 'block';                    
                    var formAction = document.forms["pp_request_api_get_request_proposition_form_"+page].action;
                    $http.get(formAction+".html").
                        then(function(response){
                            console.log('loadPage : '+page);                         
                            var newPage = angular.element(response.data);                        
                            $compile(newPage)($scope);                             
                            angular.element( document.querySelector('#loadPage'+page)).append(newPage);                        
                            document.getElementById('loadingGif').style.display = 'none';
                        },function(response) {
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );
            }
            
            this.postPropositionVote = function(propositionId){
                var myData = {}
                var formAction = document.forms["pp_proposition_api_patch_proposition_vote_form_"+propositionId].action;
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                     }).
                    then(function(response){                     
                        var jsonResponse = JSON.parse(response.data);    
                        if(jsonResponse.succes){                        
                            $("#propositionUpvoteButton_"+propositionId).addClass("voted");
                            document.getElementById('propositionUpvoteButton_'+propositionId).innerHTML = jsonResponse.upvote;
                        }
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            }
            
            this.postRequestSelect = function(propositionId){
                var myData = {}
                
                var formAction = document.forms["pp_proposition_api_patch_request_select_form_"+propositionId].action;
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                     }).                
                    then(function(response){ 
                        
                        var jsonResponse = JSON.parse(response.data);    
                        if(jsonResponse.succes){                        
                            $window.location.href = jsonResponse.redirect;
                        }
                        
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            }
                       
            $(window).scroll(function() {
                if($(nextLoadTrigger).offset() != null){
                var hT = $(nextLoadTrigger).offset().top,
                    hH = $(nextLoadTrigger).outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();
                }
                if (wS > (hT+hH-wH)){
                   getPropositions(imageRequestId, nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });
                                           
    }]);  

