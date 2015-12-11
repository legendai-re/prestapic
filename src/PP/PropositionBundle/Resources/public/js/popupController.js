/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    angular.element(document).ready(function() {
        var myDiv2 = document.getElementById("popupPropApp");
        angular.bootstrap(myDiv2, ["popupPropApp"]);
    });

    var popupPropApp = angular.module('popupPropApp',  ['ngRoute']);
    
    popupPropApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);
        
    popupPropApp.run(['$rootScope', '$http',function ($rootScope, $http) {   
            $rootScope.hello = "hello";
    }]);

    popupPropApp.controller('popupController', ['$scope', '$rootScope', '$http', '$compile', '$location', '$window', function ($scope, $rootScope, $http, $compile, $location, $window) {
        $scope.propositionLoaded = [];
        
        $rootScope.$on('showPopup', function(event, message){            
            showPopup(message.id);
        });
        
        $scope.proposition = null;
            
        var showPopup = function(id){
            $("#popupPropApp").css("display", "block");
            
            if($scope.propositionLoaded[id] == null){
                var formAction = document.forms["pp_proposition_api_get_proposition_form"].action;                              
                $http.get(formAction+".html?id="+id).
                    then(function(response) {
                        $scope.propositionLoaded[id] = response.data;
                        $scope.proposition = $scope.propositionLoaded[id];
                        updateUpvote();
                    }, function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            }else{                
                $scope.proposition = $scope.propositionLoaded[id];
                updateUpvote();
                $scope.$apply();
            }
        };
        
        var updateUpvote = function(){
            $("#propositionUpvoteButton").removeClass("animate");
            if(!$scope.proposition.canUpvote){                
                $("#propositionUpvoteButton").addClass("voted");
            }
        };
        
        var canPropositionVote = true;
        this.postPropositionVote = function(propositionId){
            if(canPropositionVote && $scope.proposition.canUpvote){
                canPropositionVote = false;
                $("#propositionUpvoteButton").addClass("animate");
                $("#propositionUpvoteButton").addClass("voted");
                document.getElementById('propositionUpvoteButton').innerHTML = parseInt($('#propositionUpvoteButton').html())+1;
                $scope.propositionLoaded[propositionId].upvoteNb++;
                $scope.propositionLoaded[propositionId].canUpvote = false;
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
                        //readyForPropositionVote = true;
                    },function(response) {
                        console.log("Request failed : "+response.statusText );
                        //readyForPropositionVote = true;
                    }
                );
            }
        }                
        
        this.patchDisable = function(){
            var myData = {
                id: $scope.proposition.id
            }
            var formAction = document.forms["pp_proposition_api_patch_disable_form"].action;
            $http({
                method: 'PATCH',
                url: formAction,                    
                data: JSON.stringify(myData)
                 }).
                then(function(response){
                    $window.location.href = $location.$$absUrl;
                },function(response) {
                    console.log("Request failed : "+response.statusText );                   
                }
            );
        };
        
        this.close = function(){
            $("#popupPropApp").css("display", "none");
            $("#propositionUpvoteButton").removeClass("voted");
            $scope.proposition = null;           
        };
        
        var timeout = null;
        
        $(document).on('mousemove', function() {
            if (timeout !== null) {
                $("#propActionTop").css("display", "block");
                $("#propActionBottom").css("display", "block");
                $("#propOverlay").css("display", "block");
                clearTimeout(timeout);
            }

            timeout = setTimeout(function() {                
                $("#propActionTop").css("display", "none");
                $("#propActionBottom").css("display", "none");
                $("#propOverlay").css("display", "none");
            }, 2000);
        });

    }]);