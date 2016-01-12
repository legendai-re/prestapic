        
    var DISPLAY_PROPOSITION = 2;
    var DISPLAY_REQUEST_PENDING = 3;
    var DISPLAY_REQUEST_CLOSED = 4;
        
    angular.element(document).ready(function() {
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });

    var containerApp = angular.module('containerApp',  ['ngRoute']);
    
    containerApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
    containerApp.run(['$rootScope', '$http', '$location',function ($rootScope, $http, $location) {               
            $rootScope.currentPath = $location.$$path;                        
    }]);

    containerApp.controller('requestsController', ['$scope', '$rootScope', '$http', '$compile', '$location', function ($scope, $rootScope, $http, $compile, $location) {                                                                                                 
            
            this.displayMode = null;
            this.contentToDisplay = null;
            var getParams = "?";
            var searchQueryParam = 'search_query=';
            var tagListParam = 'tags='; 
            var catListParam = 'categories=';
            var concerningMeParam = 'me=false';
            this.init = function(mode, contentToDisplay){
                this.displayMode = mode;
                this.contentToDisplay = contentToDisplay;
                if($location.search().search_query != null){
                    searchQueryParam += $location.search().search_query;  
                }
                if($location.search().tags != null){
                    tagListParam += $location.search().tags;  
                }
                if($location.search().categories != null){
                    catListParam += $location.search().categories;  
                }
                 if($location.search().me != null){
                    concerningMeParam = "me=true";  
                }
                getParams += searchQueryParam+'&'+tagListParam+'&'+catListParam+'&'+concerningMeParam;
                this.update(contentToDisplay);                              
            };
            
            var nextLoadTrigger = '#loadPageTrigger2';            
            var nextPage = 2;                                  
            
            var getRequests = function(page){
                var formAction = document.forms["pp_request_api_get_request_form_"+page].action;
                document.getElementById('loadingGif').style.display = 'block';                    
                $http.get(formAction+".html"+getParams).
                    then(function(response) {
                        
                        var newPage = angular.element(response.data);                        
                        $compile(newPage)($scope);                             
                        angular.element( document.querySelector('#loadPage1')).append(newPage);                        
                        document.getElementById('loadingGif').style.display = 'none';

                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );                                            
            };                       
            
            this.showContentToDisplayFilters = function(){
                $("#contentToDisplayFilters").css("display", "block");
            }
            
            this.update = function(contentToDisplay){
                $("#loadPage1").html("");
                nextLoadTrigger = '#loadPageTrigger2';            
                nextPage = 2;
                getParams = "?"+searchQueryParam+'&'+tagListParam+'&'+catListParam+'&'+concerningMeParam+"&display_mode="+this.displayMode+"&content_to_display="+contentToDisplay;
                getRequests(1);
               
                this.contentToDisplay = contentToDisplay;                                
                removeContentModeClass();
                $('#content_mode_'+contentToDisplay).addClass("selected");
                $("#contentToDisplayFilters").css("display", "none");
                if(contentToDisplay == DISPLAY_PROPOSITION){
                    $('#contentToDisplaySelected').html("Propositions");
                }else{
                    $('#contentToDisplaySelected').html("Requests");
                }
            };                        
            
            $('html').click(function() {                
                $("#contentToDisplayFilters").css("display", "none");             
            });
            
            var removeContentModeClass = function(){
                $('#content_mode_3').removeClass("selected");
                $('#content_mode_4').removeClass("selected");
                $('#content_mode_2').removeClass("selected");
            }
            
            this.updateMode = function(mode){
                this.displayMode = mode;
                $('.section').removeClass("selected");
                $('#mode_'+mode).addClass("selected");
                this.update(this.contentToDisplay);
            }
            
            var readyForRequestVote = true;
            var upvotedRequest = [];
            this.postRequestVote = function(id){
                if(readyForRequestVote && upvotedRequest[id] == null){
                    readyForRequestVote=false;
                    upvotedRequest[id] = true;
                    $("#imageRequestUpvoteButton_"+id).addClass("animate");
                    $("#imageRequestUpvoteButton_"+id).addClass("voted");
                    document.getElementById('imageRequestUpvoteButton_'+id).innerHTML = parseInt($('#imageRequestUpvoteButton_'+id).html())+1;                            
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
            
            var readyForPropositionVote = true;
            var upvotedPropositions = [];
            this.postPropositionVote = function(propositionId){
                if(readyForPropositionVote && upvotedPropositions[propositionId] == null){
                    upvotedPropositions[propositionId] = true;
                    readyForPropositionVote = false;
                    $("#propositionUpvoteButton_"+propositionId).addClass("animate");
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
            };            
            
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
                   getRequests(nextPage);
                   nextPage++;
                   nextLoadTrigger = '#loadPageTrigger'+nextPage;                                
                }
            });                       
    }]);
   