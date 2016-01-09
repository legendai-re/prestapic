var appLoaded = false;
var readyForMessage = true;

var messageApp = angular.module('messageApp',  ['ngRoute', 'ngSanitize']);

messageApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
}]);

messageApp.service('FayeClient', function () {
            return new Faye.Client('http://alexandrejolly.com:3000/');
});


/* start app */
messageApp.run(['$rootScope', 'FayeClient', '$http',function ($rootScope, FayeClient, $http) {            
    $rootScope.currentUser = {};
    var formAction = document.forms["pp_message_api_get_current_user_form"].action;                                
    var threadToLoad = null;
    $rootScope.$on('showNewMessage', function(event, message){
        threadToLoad = message;    
        if(appLoaded){
            $rootScope.$emit('loadConversation', $rootScope.currentUser.threadList[threadToLoad.id]);
            $rootScope.currentThread = $rootScope.currentUser.threadList[threadToLoad.id];
        }
    });
        
    /* get inbox threads + current user info*/
    $http.get(formAction).
        then(function(response){
            $rootScope.currentUser = response.data;                                
            
            /* start message listener */
            FayeClient.subscribe('/messages/'+$rootScope.currentUser.id, function (message) {            
                $rootScope.$broadcast('newMessage', message);            
            });
            
            if(threadToLoad!=null){
                $rootScope.$emit('loadConversation', $rootScope.currentUser.threadList[threadToLoad.id]);
            }
            $(".chat-conversation").scrollTop($(".chat-conversation").height());
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
                $rootScope.currentUser.threadList[message.message.threadId] = message.newThread;
                $rootScope.currentUser.threadList[message.message.threadId].haveReceiveMessage = true;
            }else if(message.action == 'newMessage'){
                                        
            }            
            $scope.$apply();
        });
        
             
        /* when inbox thread is clicked, go to conversation controller */
        this.gotToConversation = function(thread){                                                                   
            $rootScope.$emit('loadConversation', thread);            
        };
               
}]);

/* search user controller */
messageApp.controller('searchController',[ '$scope', '$rootScope', '$http',  function ( $scope, $rootScope, $http) {   
        
        this.search = null;
        $scope.userSearchList = [];
        var formAction = document.forms["pp_user_api_get_search_user_form"].action;
        
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
                
        this.getThread = function(user){
            var thread ={                
                target: user
            }
            $rootScope.$emit('loadConversation', thread);               
        };
        
           
}]);

/* chat controller */
messageApp.controller('chatController',['$scope', '$rootScope', '$http', function ( $scope, $rootScope, $http) {                           
            
            $scope.conversation = [];
            $scope.messageContent = null;                                  
            
            var goDown = function(){
                    $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
            };
                
            /* load new conversation */
            $rootScope.$on('loadConversation', function (event, thread) {                               
                var threadFounded = true;
                $("#chatTitle").html(thread.target.name);
                if(thread.id == null){
                    // if loadConv from searchUser
                    threadFounded = false;                    
                   
                    for(var threadId in  $rootScope.currentUser.threadList) {                        
                        if($rootScope.currentUser.threadList[threadId].target.id == thread.target.id){                            
                            thread = $rootScope.currentUser.threadList[threadId];                            
                            threadFounded = true;  
                            $scope.currentThread = thread;
                            break;
                        }  
                    }                                    
                    if(!threadFounded){                        
                        $scope.currentThread = thread;
                    }     
                }                
                if(threadFounded){
                    if(!$rootScope.currentUser.threadList[thread.id].haveReceiveMessage && ($rootScope.currentUser.threadList[thread.id] == null || $rootScope.currentUser.threadList[thread.id].messageList.length > 0)){
                        $scope.currentThread = thread;
                    }else if(threadFounded){
                        // load from inbox
                        // GET CONVERSATION //
                        $rootScope.currentUser.threadList[thread.id].haveReceiveMessage = false;
                        readyForMessage = false;
                        $http.get($rootScope.currentUser.getConversationApiUrl+'?threadId='+thread.id+"&page=1").
                            then(function(response){
                                $scope.currentThread = thread;                               
                                // if the current message thread had new messages, set them viewed                                               
                                $rootScope.currentUser.threadList[thread.id].haveNewMessage = false;                                                                                                                                                                     
                                $rootScope.currentUser.threadList[thread.id].messageList = response.data.messages;                                                            
                                setTimeout(goDown,10);
                                readyForMessage = true;
                            },function(response) {
                                console.log("Request failed : "+response.statusText );                            
                            }
                        );
                    }
                };                
                if(!$scope.$$phase) {
                    $scope.$apply();
                    $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
                }
                setTimeout(goDown,10);
        });      
        
        this.loadMore = function(){            
            $scope.currentThread.page++;
            if($scope.currentThread.id != null){
                $http.get($rootScope.currentUser.getConversationApiUrl+'?threadId='+$scope.currentThread.id+"&page="+$scope.currentThread.page).
                    then(function(response){
                        for(var i=response.data.messages.length-1; i>-1; i--){
                            $rootScope.currentUser.threadList[$scope.currentThread.id].messageList.unshift(response.data.messages[i]);
                        }                        
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                            
                    }
                );
            }
        }
        
        /* when new message received, change inbox thread last message */
        $rootScope.$on('newMessage', function (event, message) {            
            if($rootScope.currentUser.threadList[message.message.threadId].messageList.length == 0)$rootScope.currentUser.threadList[message.message.threadId].haveReceiveMessage = true;
            $rootScope.currentUser.threadList[message.message.threadId].messageList.push(message.message);
            $rootScope.currentUser.threadList[message.message.threadId].lastMessage = message.message;
            $scope.$apply();
            $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
        });
                                      
        /* send message */
        
        var sendMessage = function(){            
            if($scope.messageContent){
                myData = {
                    threadId: $scope.currentThread.id,
                    targetId: $scope.currentThread.target.id,
                    messageContent: $scope.messageContent
                };

                if(readyForMessage){                    
                    /* add new message to conversation (client only) */
                    var newMessage = {
                        content: $scope.messageContent,
                        authorName: $rootScope.currentUser.name,
                        messageFromUs: true
                    }; 
                    
                    if($scope.currentThread.id != null){
                        $rootScope.currentUser.threadList[$scope.currentThread.id].messageList.push(newMessage);                        
                    }
                    
                    $http({
                           method: 'POST',
                           url: $rootScope.currentUser.postMessageApiUrl,                    
                           data: JSON.stringify(myData)
                    }). 
                       then(function(response) {
                            // if it's the first message with this person, thread have been created so handle it and add it to inbox 
                            if($scope.currentThread.id == null){
                                $scope.currentThread = response.data.newThread;
                                if($rootScope.currentUser.threadList[response.data.newThread.id]==null)$rootScope.currentUser.threadList[response.data.newThread.id] = response.data.newThread;                                
                            }
                       }, function(response) {
                           console.log("Request failed : "+response.statusText );                        
                       }
                    );
                                                                                                                           

                    $scope.messageContent = null;
                    setTimeout(goDown,10);                  
                }
            }
        };
        
        this.callSendMessage = function(){
            sendMessage();
        }               
        
        var linesNumber = 0;
        $('#chatTextArea').elastic();
        $('#chatTextArea').on('keydown', function(event) {
            if (event.keyCode == 13){
                if (!event.shiftKey){                    
                    sendMessage();                    
                }else{
                    setTimeout(goDown,10);
                }                
            }
        });               
        
        function lines()
        {
            var text = document.getElementById('chatTextArea');
            var cnt = (text.cols);

            var lineCount = (text.value.length / cnt);
            var lineBreaksCount = (text.value.split('\r\n'));
            //alert(lineBreaksCount.length);
            return Math.round(lineCount)+1;
        }
        
}]);



var isInSearch = false;

$('#searchButton').click(function(){
    if(!isInSearch){
        $('#inboxBlock').css("display", "none");
        $('#searchBlock').css("display", "inline-block");
        isInSearch = true;
    }else{
        $('#inboxBlock').css("display", "block");
        $('#searchBlock').css("display", "none");
        isInSearch = false;
    }    
});
