
angular.element(document).ready(function() {       
    var myDiv2 = document.getElementById("containerApp");
    angular.bootstrap(myDiv2, ["containerApp"]);
});

var containerApp = angular.module('containerApp',  ['ngRoute']);

containerApp.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);        
}]);                 

containerApp.controller('requestController',['$scope', '$http', function ($scope, $http) {                                                                                                                                                                                                                                          

        var readyForRequestVote = true;
        this.postRequestVote = function(id){
            if(readyForRequestVote){
                readyForRequestVote=false;
                $("#imageRequestUpvoteButton").addClass("voted");
                document.getElementById("imageRequestUpvoteButton").innerHTML = parseInt($('#imageRequestUpvoteButton').html())+1;                            
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
        this.reportData = {
            ticketType: 1,
            imageRequestId: null,
            reasonId: null,
            details: null
        };
        var haveAlreadyReport = false;
        
        // send report //
        this.postReport = function(id){
            //if(!haveAlreadyReport){
                haveAlreadyReport = true;
                this.reportData.imageRequestId = id;
                console.log(this.reportData);
                var formAction = document.forms["pp_report_api_post_report_ticket_form"].action;
                $http({
                    method: 'POST',
                    url: formAction,                    
                    data: JSON.stringify(this.reportData)
                     }).
                    then(function(response){                        
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            //}
        };
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
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage'+page)).append(newPage);                        
                        document.getElementById('loadingGif').style.display = 'none';
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
        }

        var readyForPropositionVote = true;
        this.postPropositionVote = function(propositionId){
            if(readyForPropositionVote){
                readyForPropositionVote = false;
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
        }
        
        var haveSelectImage = false;
        this.postRequestSelect = function(propositionId){
            var myData = {}

            var formAction = document.forms["pp_proposition_api_patch_request_select_form_"+propositionId].action;
            if(!haveSelectImage){
                haveSelectImage = true;
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

