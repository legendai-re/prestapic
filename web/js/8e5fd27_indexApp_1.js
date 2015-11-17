    
    angular.element(document).ready(function() {
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });

    var containerApp = angular.module('containerApp',  ['ngRoute']);
    
    containerApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
                        
    containerApp.controller('requestsController', ['$scope', '$http', '$compile', '$location', function ($scope, $http, $compile, $location) {                                                                                                 
            
            var getParams = "?";
            var searchQueryParam = 'search_query=';
            var tagListParam = 'tags='; 
            var catListParam = 'categories=';
            this.init = function(){
                if($location.search().search_query != null){
                    searchQueryParam += $location.search().search_query;  
                }
                if($location.search().tags != null){
                    tagListParam += $location.search().tags;  
                }
                if($location.search().categories != null){
                    catListParam += $location.search().categories;  
                }
                getParams += searchQueryParam+'&'+tagListParam+'&'+catListParam;
                getRequests(1);                                
            }
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                                  
            
            var getRequests = function(page){
                var formAction = document.forms["pp_request_api_get_request_form_"+page].action;
                document.getElementById('loadingGif').style.display = 'block';                    
                $http.get(formAction+".html"+getParams).
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
            
            function convertToSlug(Text)
            {
                return Text
                    .toLowerCase()
                    .replace(/ /g,'-')
                    .replace(/[^\w-]+/g,'')
                    ;
            }
    }]);
       