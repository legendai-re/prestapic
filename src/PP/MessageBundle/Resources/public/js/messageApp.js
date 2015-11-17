var appLoaded = false;

var haveMessageThread = false;
var currentMessageThreadId = null;
var targetUserId = null;
var postMessageUrl = null;
var currentUser = null;
var targetUser = null;
var currentThread = null;
var conversationUrl = null;
var getThreadUrl = null;
var conversationToLoad = null;

var readyForMessage = false;

var messageApp = angular.module('messageApp',  ['ngRoute']);

messageApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
}]);

messageApp.service('FayeClient', function () {
            return new Faye.Client('http://localhost:3000/');
});


/* start app */
messageApp.run(['$rootScope', 'FayeClient', '$http',function ($rootScope, FayeClient, $http) {
    console.log('run');
    
    var formAction = document.forms["pp_message_api_get_inbox_form"].action;                                
    var targetId = null;
    $rootScope.$on('showNewMessage', function(event, authorId){            ;
            targetId = authorId;
            if(appLoaded)$rootScope.$emit('loadConversation', authorId);
    });
        
    /* get inbox threads + current user info*/
    $http.get(formAction).
        then(function(response){
            currentUser = response.data.currentUser;
            $rootScope.currentUser = currentUser;
            $rootScope.inboxThreads = response.data.threads;
            conversationUrl = response.data.conversationUrl;
            postMessageUrl = response.data.postMessageUrl;
            getThreadUrl = response.data.getThreadUrl;
            
            /* start message listener */
            FayeClient.subscribe('/messages/'+currentUser.id, function (message) {            
                $rootScope.$broadcast('newMessage', message);            
            });
            
            if(targetId!=null){
                $rootScope.$emit('loadConversation', targetId);
            }
            
            appLoaded = true;
        },function(response) {
            console.log("Request failed : "+response.statusText );                            
        }
    );
            
        
}]);

/* inbox controller */
messageApp.controller('inboxController',['$scope', '$rootScope', '$http', function ($scope, $rootScope, $http) {                                           
        
        /* when new message received */
        $rootScope.$on('newMessage', function (event, message) {           
            if(message.action == 'newThread'){
                /* if thread whith de message author don't exist yet */
                /* add thread to inbox */
                $scope.inboxThreads.unshift(message.message)
                
            }else if(message.action == 'newMessage'){
                /* else just change the last message in inbox thread */
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
        
             
        /* when inbox thread is clicked, go to conversation controller */
        this.gotToConversation = function(thread){            
            $rootScope.$emit('loadConversation', thread.userId);
        };
               
}]);

/* search user controller */
messageApp.controller('searchController',[ '$scope', '$rootScope', '$http',  function ( $scope, $rootScope, $http) {   
        
        this.search = null;
        $scope.userSearchList = [];
        var formAction = document.forms["pp_message_api_get_search_user_form"].action;
        
        /* on text input change, search user */
        this.searchUser = function(){
            if(this.search.length > 0){                                                                               
                $http.get(formAction+'?search='+this.search).
                    then(function(response){
                        /* empty text input */
                        $scope.userSearchList = []; 
                        for(var x=0; x<response.data.users.length; x++){
                            /* push user to list */
                            $scope.userSearchList.push(response.data.users[x]);                                                        
                        }                            
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                            
                    }
                );                                            
            }else $scope.userSearchList = [];
        };
        
        /* when user in search list clicked get the message thread with him
         * if it doesn't exist yet create it, thten load conversation
         */
        this.getThread = function(user){                                                                       
            $rootScope.$emit('loadConversation', user.id);               
        };
        
           
}]);

/* chat controller */
messageApp.controller('chatController',['$scope', '$rootScope', '$http', function ( $scope, $rootScope, $http) {   
        
        this.messageContent = null;          
        
        /* load new conversation */
        $rootScope.$on('loadConversation', function (event, targetId) {
            readyForMessage = false;
            $("#chatTitle").html("");
            /* get message thread thread */
            $http.get(getThreadUrl+'?targetId='+targetId).
                then(function(response){
                    haveMessageThread = response.data.messageThreadFounded;
                    currentThread = response.data.messageThread;
                    targetUser = response.data.targetUser;
                    $("#chatTitle").html(targetUser.name);
                    /* 
                    * if the current message thread had new messages, set them viewed
                    */
                    if(haveMessageThread){
                        for(var i=0; i<$scope.inboxThreads.length; i++){
                            if($scope.inboxThreads[i].threadId == currentThread.threadId){
                                $scope.inboxThreads[i].haveNewMessage = false;                      
                                break;
                            }
                        }
                    }            
                    /* set conversation target user name */
                    $scope.currentThread = {
                        targetName: targetUser.name
                    };
                    
                    $scope.conversation = [];                                              
                                    
                    /* get conversation messages */
                    if(haveMessageThread){
                        $http.get(conversationUrl+"?threadId="+currentThread.threadId).
                            then(function(response){
                                $scope.conversation = response.data.messages;                                ;
                            },function(response) {
                                console.log("Request failed : "+response.statusText );                            
                            }
                        );
                    }                                        
                    
                    readyForMessage = true;
                },function(response) {
                    console.log("Request failed : "+response.statusText );                            
                }
            );
                        
        });
        
        /* when new message received, change inbox thread last message */
        $rootScope.$on('newMessage', function (event, message) {
            if(currentThread != null && message.message.threadId == currentThread.threadId){
                $scope.conversation.push(message.message);
                $scope.$apply();
            }
        });
                                      
        /* send message */
        this.sendMessage = function(){                                   
            var threadId = null;
            if(currentThread!=null)threadId = currentThread.threadId;
            myData = {
                haveMessageThread: haveMessageThread,
                targetUserId: targetUser.id,
                currentMessageThreadId: threadId,
                messageContent: this.messageContent
            };
            
            if(readyForMessage){
                $http({
                       method: 'POST',
                       url: postMessageUrl,                    
                       data: JSON.stringify(myData)
                }). 
                   then(function(response) {
                        /* if it's the first message with this person, thread have been created so handle it and add it to inbox */
                        if(!haveMessageThread){
                            currentThread = response.data.newThread;                            
                            haveMessageThread = true;
                            $scope.inboxThreads.unshift(response.data.newThread);                        
                        }
                   }, function(response) {
                       console.log("Request failed : "+response.statusText );                        
                   }
                );
            
            
                /* add new message to conversation (client only) */
                var newMessage = {
                    content: this.messageContent,
                    authorName: currentUser.name,
                    messageFromUs: true
                };                        
                $scope.conversation.push(newMessage);

                /* update last message in inbox thread */
                if(currentThread!=null){
                    for(var i=0; i<$scope.inboxThreads.length; i++){
                        if($scope.inboxThreads[i].threadId == currentThread.threadId){
                            $scope.inboxThreads[i].message = this.messageContent;  
                            $scope.inboxThreads[i].messageFromUs = true;
                            break;
                        }
                    }
                }

                this.messageContent = null;
            }
        };
               
}]);

