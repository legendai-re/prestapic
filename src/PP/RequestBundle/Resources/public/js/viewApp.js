
angular.element(document).ready(function() {       
    var myDiv2 = document.getElementById("containerApp");
    angular.bootstrap(myDiv2, ["containerApp"]);
});

var containerApp = angular.module('containerApp',  ['ngRoute']);

containerApp.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);        
}]);                 

containerApp.controller('requestController',['$scope', '$http', '$location', '$window', function ($scope, $http, $location, $window) {                                                                                                                                                                                                                                                  
        
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
                    }, function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }                                 
                );
            }
        }
        this.reportData = {
            ticketType: 1,
            targetId: null,
            reasonId: 1,
            details: null
        };
        var haveAlreadyReport = false;
        
        // send report //
        this.postReport = function(id){
            if(!haveAlreadyReport){
                haveAlreadyReport = true;
                this.reportData.targetId = id;
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
            }
        };
        console.log($location);
        this.postDisableRequest = function(id){
            if(!haveAlreadyReport){
                haveAlreadyReport = true;
                this.reportData.targetId = id;
                console.log(this.reportData);
                var formAction = document.forms["pp_report_api_post_disable_ticket_form"].action;
                $http({
                    method: 'POST',
                    url: formAction,                    
                    data: JSON.stringify(this.reportData)
                     }).
                    then(function(response){
                        $window.location.href = $location.$$absUrl;
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            }
        };
        
        this.getEditForm = function(id){
            var formAction = document.forms["pp_request_api_get_edit_request_form"].action;
                $http.get(formAction+".html?id="+id).
                    then(function(response){                                               
                        $("#requestContent").css("display", "none");
                        $("#editRequestContent").append(response.data);
                        $("#editRequestContent").css("display", "block");
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
        };
}]);

containerApp.controller('propositionsController', ['$scope', '$http', '$compile', '$window', function ($scope, $http, $compile, $window) {                                                                                                 
        
        
        var imageRequestId = null;

        this.init = function(id){
            getPropositions(id, 1);
            imageRequestId = id;
        }

        var nextLoadTrigger = '#loadPageTrigger3';            
        var nextPage = 3;

        
        var getPropositions = function(imageRequestId, page){                    
                document.getElementById('loadingGif').style.display = 'block';                    
                var formAction = document.forms["pp_request_api_get_request_proposition_form_"+page].action;
                $http.get(formAction+".html").
                    then(function(response){
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage'+page)).append(newPage);                        
                        document.getElementById('loadingGif').style.display = 'none';
                        if($('#showMoreButton') != null && page == 1){
                            $('#showMoreButton').css("display", "inline-block");
                        }
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
        }
        
        this.showMore = function(){
            getPropositions(imageRequestId, 2);
            $('#showMoreButton').css("display", "none");
        };
        
        var readyForPropositionVote = true;
        var votedProposition = [];
        this.postPropositionVote = function(propositionId){
            if(readyForPropositionVote && votedProposition[propositionId] == null ){
                votedProposition[propositionId] = true;
                readyForPropositionVote = false;
                $("#propositionUpvoteButton_"+propositionId).addClass("voted");
                $("#propositionUpvoteButton_"+propositionId).addClass("animate");
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
        
        this.showPopup = function(id){
            var message = {
                id: id
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
               getPropositions(imageRequestId, nextPage);
               nextPage++;
               nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
            }
        });

}]);  

