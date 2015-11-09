    
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
            return new Faye.Client('http://localhost:3000/');
    })

    // souscription au channel "/messages"
    
    containerApp.run(function ($rootScope, FayeClient) {
        FayeClient.subscribe('/messages', function (message) {
            $rootScope.$broadcast('notification', message);            
        });
    })

    containerApp.controller('profileController', ['$scope', '$http', '$compile', '$location', function ($scope, $http, $compile, $location) {
            
           
            
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
                        console.log('loadPage : '+page)                        
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage'+page)).append(newPage);  
                        document.getElementById('loadingGif').style.display = 'none';

                    }, function(response) {
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
                   getRequests(nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });
                       
    }]);
    
