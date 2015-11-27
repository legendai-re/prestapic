    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });
    
    var containerApp = angular.module('containerApp',  ['ngRoute']);       
    
    containerApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
    containerApp.controller('searchResultController', ['$scope', '$http', '$compile', '$location', function ($scope, $http, $compile, $location) {
         
            var getParams = "?";
            var nameParam = 'name=';           
            this.init = function(){
                if($location.search().name != null){
                    nameParam += $location.search().name;  
                }               
                getParams += nameParam;
                getUsers(1);                                
            };
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                                  
            
            var getUsers = function(page){
                var formAction = document.forms["pp_user_api_get_search_user_view_form"].action;
                document.getElementById('loadingGif').style.display = 'block';                    
                $http.get(formAction+".html"+getParams+"&page="+page).
                    then(function(response) {                        
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage'+page)).append(newPage);  
                        document.getElementById('loadingGif').style.display = 'none';
                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );                                            
            };                       
                       
            $(window).scroll(function() {
                if($(nextLoadTrigger).offset() != null){                    
                    var hT = $(nextLoadTrigger).offset().top,
                        hH = $(nextLoadTrigger).outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();
                }
                if (wS > (hT+hH-wH)){                    
                   getUsers(nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });
            
    }]);
    
    
    
