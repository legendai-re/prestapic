    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });
    
    var containerApp = angular.module('containerApp',  ['ngRoute']);       
    
    containerApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
    

    // souscription au channel "/messages"
    
    containerApp.run(['$rootScope',function ($rootScope) {
        
    }])

    
    containerApp.controller('propositionsController', ['$scope', '$http', '$compile', '$location', function ($scope, $http, $compile, $location) {
            
            var pageProfileId = null;
            
            this.init = function(id){
                pageProfileId = id;
                getPropositions(1);                                
            }
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                      
            
            var getPropositions = function(page){
                document.getElementById('loadingGif').style.display = 'block';                
                var formAction = document.forms["pp_user_api_get_gallery_form"].action;
                
                $http.get(formAction+".html?userId="+pageProfileId+"&page="+page).
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
            
 
            $(window).scroll(function() {
                if($(nextLoadTrigger).offset() != null){
                var hT = $(nextLoadTrigger).offset().top,
                    hH = $(nextLoadTrigger).outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();
                }
                if (wS > (hT+hH-wH)){
                   getPropositions(nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });
                       
    }]);
    
