
angular.element(document).ready(function() {       
    var myDiv2 = document.getElementById("containerApp");
    angular.bootstrap(myDiv2, ["containerApp"]);
});

var containerApp = angular.module('containerApp',  ['ngRoute']);

containerApp.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);        
}]);

containerApp.run(['$rootScope', '$http', '$location',function ($rootScope, $http, $location) {
    var pathInit = false;
    if(!pathInit){
        pathInit = true;
        $rootScope.currentPath = $location.$$path;
    }
    
}]);

containerApp.controller('requestController',['$scope', '$http', '$location', '$window', function ($scope, $http, $location, $window) {                                                                                                                                                                                                                                                  
        
        var readyForRequestVote = true;
        this.postRequestVote = function(id){
            if(readyForRequestVote){
                readyForRequestVote=false;
                $("#imageRequestUpvoteButton_"+id).addClass("animate");
                $("#imageRequestUpvoteButton_"+id).addClass("voted");
                document.getElementById("imageRequestUpvoteButton_"+id).innerHTML = parseInt($('#imageRequestUpvoteButton_'+id).html())+1;                            
                var myData = {
                    id: id
                };
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
        };
        
        this.showReportPopup = function(id, type){
            var message = {
                id: id,
                type: type
            }
            angular.element(document.getElementById('reportPopupApp')).scope().$emit('showPopup', message);                                                
        };
        
        this.reportData = {
            ticketType: 1,
            targetId: null,
            reasonId: 1,
            details: " "
        };       
        
        var haveAlreadyReport = false;
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
            console.log("hello");
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

containerApp.controller('propositionsController', ['$rootScope', '$scope', '$http', '$compile', '$window', '$location', function ($rootScope, $scope, $http, $compile, $window, $location) {                                                                                                 
        
        
        var imageRequestId = null;

        this.init = function(id){
            getPropositions(id, 1);
            imageRequestId = id;
        };

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
        
        if(document.getElementById('pp_propositionbundle_proposition_image_file')!=null)document.getElementById('pp_propositionbundle_proposition_image_file').addEventListener('change', handlePropositionFileSelect, false);
        function handlePropositionFileSelect(evt) {
            var files = evt.target.files; // FileList object

            for (var i = 0, f; f = files[i]; i++) {
                if (!f.type.match('image.*')) {
                    continue;
                }

                var reader = new FileReader();                                            
                reader.onload = (function(theFile) {
                    return function(e) {
                        $("#dragzoneUploaded").css("display", "block");
                        var div = document.getElementById('uploaded_proposition');
                        div.innerHTML = ['<div class="img-preview" style="background-image: url(', e.target.result,')"></div>'].join('');
                    };
                })(f);
                reader.readAsDataURL(f);                    
            }
        }

        
        
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
                id: id,
                url: $rootScope.currentPath
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

containerApp.controller('uploadController', ['$scope', '$http', '$compile', '$window', '$location', function ($scope, $http, $compile, $window, $location) {
    this.cancelDragZoneUpload = function(){
        document.getElementById("pp_propositionbundle_proposition_image_file").value = "";
        $("#dragzoneUploaded").css("display", "none");
    }
}]);

containerApp.controller('commentsController', ['$scope', '$http', '$compile', '$window', '$location', function ($scope, $http, $compile, $window, $location) {
        
        var requestId = null;
        $scope.commentList = [];
        
        this.init = function(requestId){            
            getComments(1, requestId);
        };
        
        var getComments = function(page, requestId){
            var formAction = document.forms["pp_request_api_get_comments_form"].action;
            
            $http.get(formAction+".html?page="+page+"&requestId="+requestId).
                then(function(response){                    
                    $scope.commentThread = response.data;
                    for(var i=0; i<response.data.comments.length; i++){
                        $scope.commentList.unshift(response.data.comments[i]);
                    }
                    $scope.currentUser = response.data.currentUser;
                },function(response) {
                    console.log("Request failed : "+response.statusText );                        
                }
            );
        };
        
        $scope.comment = {};
        var canPostComment = true;
        
        this.postComment = function(requestId){
            console.log("hello");
            if(canPostComment){                
                var newComment = {
                    content: $scope.comment.content,
                    author: {
                        id: $scope.currentUser.id,
                        image: $scope.currentUser.image,
                        name: $scope.currentUser.name,
                        url: $scope.currentUser.url
                    }
                };                
                $scope.commentList.push(newComment);
                
                canPostComment = false;
                $scope.comment.requestId = requestId;
                console.log($scope.comment);
                var formAction = document.forms["pp_request_api_post_comment_form"].action;
                $http({
                    method: 'POST',
                    url: formAction,                    
                    data: JSON.stringify($scope.comment)
                     }).                
                    then(function(response){                        

                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
                $scope.comment.content = "";
            }
        };
}]);
