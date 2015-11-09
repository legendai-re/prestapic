
var haveMessageThread = false;
var currentMessageThreadId = null;
var targetUserId = null;
var postMessageUrl = null;
var currentUser = null;

var messageApp = angular.module('messageApp',  ['ngRoute']);

messageApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
}])

messageApp.service('FayeClient', function () {
            return new Faye.Client('http://localhost:3000/');
})

messageApp.run(['$rootScope', 'FayeClient', '$http',function ($rootScope, FayeClient, $http) {
    console.log('run');
    
    var formAction = document.forms["pp_message_api_get_inbox_form"].action;                                

    $http.get(formAction).
        then(function(response){
            currentUser = response.data.currentUser;
            $rootScope.currentUser = currentUser;
            $rootScope.inboxThreads = response.data.threads;
            
            FayeClient.subscribe('/messages/'+currentUser.id, function (message) {            
                $rootScope.$broadcast('newMessage', message);            
            });
            
        },function(response) {
            console.log("Request failed : "+response.statusText );                            
        }
    );    
}]);

messageApp.controller('inboxController',['$scope', '$rootScope', '$http', function ($scope, $rootScope, $http) {   
        this.test = "inbox !"
        
        $rootScope.$on('newMessage', function (event, message) {
            if(message.action == 'newThread'){               
                $scope.inboxThreads.unshift(message.message)                
            }else if(message.action == 'newMessage'){                
                for(var i=0; i<$scope.inboxThreads.length; i++){
                    if($scope.inboxThreads[i].threadId == message.message.threadId){
                        if(currentMessageThreadId!=message.message.threadId)$scope.inboxThreads[i].haveNewMessage = true;
                        $scope.inboxThreads[i].message = message.message.content;                       
                        break;
                    }
                }                               
            }
            
            $scope.$apply();
        });
        
        this.gotToConversation = function(thread){
            var message = {
                messageThreadFounded: true,
                getConversationUrl: thread.getConversationUrl,
                currentMessageThreadId: thread.threadId,
                postMessageUrl: thread.postMessageUrl,
                targetUserId: thread.userId,
                targetUserName: thread.userName
            };

            $rootScope.$emit('loadConversation', message);
        }
               
}]);

messageApp.controller('searchController',[ '$scope', '$rootScope', '$http',  function ( $scope, $rootScope, $http) {   
        
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
        
        
        this.getThread = function(user){                                                                           
                        
            targetUserId = user.id;                                  
            $http.get(user.getThreadApiUrl).
                then(function(response){                                        
                    haveMessageThread = response.data.messageThreadFounded;
                    currentMessageThreadId = response.data.messageThreadId;
                    postMessageUrl = response.data.postMessageThreadUrl;
                    
                    var message = {
                        messageThreadFounded: response.data.messageThreadFounded,
                        getConversationUrl: response.data.getConversationUrl,
                        currentMessageThreadId: response.data.messageThreadId,
                        postMessageUrl: response.data.postMessageThreadUrl,
                        targetUserId: user.id,
                        targetUserName: user.name
                    };
                    
                    $rootScope.$emit('loadConversation', message);
                },function(response) {
                    console.log("Request failed : "+response.statusText );                            
                }
            );                                            

        }
        
           
}]);

messageApp.controller('chatController',['$scope', '$rootScope', '$http', function ( $scope, $rootScope, $http) {   
        
        this.messageContent = null;  
        $scope.currentThread = {
            targetName: null
        };
        
        $rootScope.$on('loadConversation', function (event, message) {
            $scope.conversation = [];
            postMessageUrl = message.postMessageUrl;
            targetUserId = message.targetUserId;
            currentMessageThreadId = message.currentMessageThreadId;
            haveMessageThread = message.messageThreadFounded;                        
            
            for(var i=0; i<$scope.inboxThreads.length; i++){
                if($scope.inboxThreads[i].threadId == currentMessageThreadId){
                    $scope.inboxThreads[i].haveNewMessage = false;                      
                    break;
                }
            }
            
            $scope.currentThread = {
                targetName: message.targetUserName
            };
            
            
            if(message.messageThreadFounded){
                $http.get(message.getConversationUrl+"?threadId="+message.currentMessageThreadId).
                    then(function(response){
                        $scope.conversation = response.data.messages;
                        console.log($scope.conversation);
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                            
                    }
                );
            }
        });
        
        $rootScope.$on('newMessage', function (event, message) {
            if(message.message.threadId == currentMessageThreadId){
                $scope.conversation.push(message.message);
                $scope.$apply();
            }
        });
        
        
                      
        
        this.sendMessage = function(){                                   
            
            myData = {
                haveMessageThread: haveMessageThread,
                targetUserId: targetUserId,
                currentMessageThreadId: currentMessageThreadId,
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
                        $scope.inboxThreads.unshift(response.data.newThread);                        
                    }
               }, function(response) {
                   console.log("Request failed : "+response.statusText );                        
               }
            );
               
            var newMessage = {
                content: this.messageContent,
                authorName: currentUser.name,
                messageFromUs: true
            };
                        
            $scope.conversation.push(newMessage);
                        
            for(var i=0; i<$scope.inboxThreads.length; i++){
                if($scope.inboxThreads[i].threadId == currentMessageThreadId){
                    $scope.inboxThreads[i].message = this.messageContent;                   
                    break;
                }
            }            
            
            this.messageContent = null;
        }
               
}]);

