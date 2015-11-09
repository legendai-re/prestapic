
var haveMessageThread = false;
var messageThreadId = null;
var targetUserId = null;
var postMessageUrl = null;

var messageApp = angular.module('messageApp',  ['ngRoute']);

messageApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
}])

messageApp.run(['$rootScope', '$http',function ($rootScope, $http) {
    console.log('run');
    
    var formAction = document.forms["pp_message_api_get_inbox_form"].action;                                

    $http.get(formAction).
        then(function(response){
            console.log(response.data.messages);
            $rootScope.inboxMessages = response.data.messages;
        },function(response) {
            console.log("Request failed : "+response.statusText );                            
        }
    );
    
}]);

messageApp.controller('inboxController',['$scope', '$http', function ($scope, $http) {   
        this.test = "inbox !"
               
}]);

messageApp.controller('newMessageController',[ '$scope', '$http',  function ( $scope, $http) {   
        
        this.search = null;
        $scope.userSearchList = [];
        var waiting = false;
        this.searchUser = function(){
            if(this.search.length > 0){
                console.log(this.search);
                
                var formAction = document.forms["pp_message_api_get_search_user_form"].action;                                

                $http.get(formAction+'?search='+this.search).
                    then(function(response){
                        console.log(response.data);
                        $scope.userSearchList = [] 
                       for(var x=0; x<response.data.users.length; x++){                    
                            $scope.userSearchList.push(response.data.users[x]);                                                        
                        }                            
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                            
                    }
                );                                            
            }else $scope.userSearchList = [];
        }
        
        this.getThread = function(index){                                                                           
            
            var selectedUser = $scope.userSearchList[index];
            targetUserId = selectedUser.id;
            
            $http.get(selectedUser.getThreadApiUrl).
                then(function(response){                                        
                    haveMessageThread = response.data.messageThreadFounded;
                    messageThreadId = response.data.messageThreadId;
                    postMessageUrl = response.data.postMessageThreadUrl;
                },function(response) {
                    console.log("Request failed : "+response.statusText );                            
                }
            );                                            

        }
        
           
}]);

messageApp.controller('chatController',['$scope', '$http', function ( $scope, $http) {   
        
        this.messageContent = null;
        
        this.sendMessage = function(){
            console.log('haveMessageThread : '+haveMessageThread)
            console.log('messageThreadId : '+messageThreadId)
            console.log('postMessageThreadUrl : '+postMessageUrl)
            console.log('targetUserId : '+targetUserId)
             
            myData = {
                haveMessageThread: haveMessageThread,
                targetUserId: targetUserId,
                messageThreadId: messageThreadId,
                messageContent: this.messageContent
            };

            $http({
                   method: 'POST',
                   url: postMessageUrl,                    
                   data: JSON.stringify(myData)
            }). 
               then(function(response) {
                    if(!haveMessageThread){
                        console.log(response);
                        haveMessageThread = true;
                        messageThreadId = response.data.messageThreadId;
                    }
               }, function(response) {
                   console.log("Request failed : "+response.statusText );                        
               }
           );
             
        }
               
}]);

