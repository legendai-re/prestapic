
angular.element(document).ready(function() {
    try {
        var myDiv2 = document.getElementById("popupPropApp");
    angular.bootstrap(myDiv2, ["popupPropApp"]);
    }catch(e){
        console.log("allready bootstraped");
    }    
});

var popupPropApp = angular.module('popupPropApp',  ['ngRoute']);

popupPropApp.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);
}]);

popupPropApp.run(['$rootScope', '$http', '$location', function ($rootScope, $http, $location) {   
        $rootScope.imagePath =  $location.$$path;
        
}]);

popupPropApp.controller('popupController', ['$scope', '$rootScope', '$http', '$compile', '$location', '$window', function ($scope, $rootScope, $http, $compile, $location, $window) {
    $scope.propositionLoaded = [];
    $scope.currentLocation = null;
    var initPath = false;
    
    $rootScope.$on('showPopup', function(event, message){            
        showPopup(message.id);
        if(!initPath){
            initPath = true;
            $scope.currentLocation = message.url;
        }
    });

    $scope.proposition = null;        
    
    var showPopup = function(id){
        $("#popupPropApp").css("display", "block");
        $("#propositionUpvoteButton").removeClass("animate");
       
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
        /*if($scope.propositionLoaded[id] == null){
        }else{                
            $scope.proposition = $scope.propositionLoaded[id];
            updateUpvote();
            $scope.$apply();
        }*/
    };

    var updateUpvote = function(){
        if(!$scope.proposition.connected){
             $("#propositionUpvoteButton").addClass("blocked");
        }else if($scope.proposition.author.isAuthor){
            $("#propositionUpvoteButton").addClass("blocked");
        }else if(!$scope.proposition.canUpvote){                
            $("#propositionUpvoteButton").addClass("voted");
        }
    };

    var canPropositionVote = [];
    this.postPropositionVote = function(propositionId){
        if(canPropositionVote[propositionId] == null && $scope.proposition.canUpvote){
            canPropositionVote[propositionId] = false;                
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
                $location.path($scope.currentLocation);
                $window.location.href = $location.$$absUrl;
            },function(response) {
                console.log("Request failed : "+response.statusText );                   
            }
        );
    };       
    
    this.showReportPopup = function(id, type){
        var message = {
            id: id,
            type: type
        }
        angular.element(document.getElementById('reportPopupApp')).scope().$emit('showPopup', message);                                                
    };
    
    this.close = function(){                                        
        $("#popupPropApp").css("display", "none");
        $("#propositionUpvoteButton").removeClass("voted");
        $("#propositionUpvoteButton").removeClass("blocked");
        $scope.proposition = null;  
        $location.path($scope.currentLocation);
    };

    /*var timeoutProposition = null;
    $(document).on('change', function() {
        if (timeoutProposition !== null) {
            $("#propActionTop").css("display", "block");
            $("#propActionBottom").css("display", "block");
            $("#propOverlay").css("display", "block");
            clearTimeout(timeoutProposition);
        }

        timeout = setTimeout(function() {                
            $("#propActionTop").css("display", "none");
            $("#propActionBottom").css("display", "none");
            $("#propOverlay").css("display", "none");
        }, 2000);
    });*/
    

}]);