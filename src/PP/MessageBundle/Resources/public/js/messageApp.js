var isInSearch = false;
var appLoaded = false;
var readyForMessage = true;

var messageApp = angular.module('messageApp',  ['ngRoute', 'ngSanitize']);

messageApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);        
}]);

messageApp.service('FayeClient', function () {
            return new Faye.Client('http://alexandrejolly.com:3000/');
});

var observe;
if (window.attachEvent) {
    observe = function (element, event, handler) {
        element.attachEvent('on'+event, handler);
    };
}
else {
    observe = function (element, event, handler) {
        element.addEventListener(event, handler, false);
    };
}

/* start app */
messageApp.run(['$rootScope', 'FayeClient', '$http',function ($rootScope, FayeClient, $http) {
        
    var text = document.getElementById('chatTextArea');
    var chatComposer = document.getElementById('chatComposer');
    function resize () {
        text.style.height = 'auto';       
        text.style.minHeight = (text.scrollHeight)+'px';
        chatComposer.style.minHeight = (text.scrollHeight)+'px';
        chatComposer.style.maxHeight = (text.scrollHeight)+'px';
        if($('#chatTextArea').height() < 500){
            text.style.maxHeight = (text.scrollHeight)+'px';
        }else{
            $('#chatTextArea').css("overflow", 'auto');
            text.style.maxHeight = 199+'px';
            text.style.minHeight = 199+'px';
            chatComposer.style.minHeight = 199+'px';
            chatComposer.style.maxHeight = 199+'px';
        }
       
        if($('#chatTextArea').val().length == 0){
            text.style.minHeight = 70+'px';
            text.style.maxHeight = 70+'px';  
            chatComposer.style.minHeight = 70+'px';
            chatComposer.style.maxHeight = 70+'px';
        }
        goDown();
    }        
    
    /* 0-timeout to get the already changed text */
    function delayedResize () {
        window.setTimeout(resize, 0);
    }
    observe(text, 'change',  delayedResize);
    observe(text, 'cut',     delayedResize);
    observe(text, 'paste',   delayedResize);
    observe(text, 'drop',    delayedResize);
    observe(text, 'keydown', delayedResize);
    
    text.focus();
    text.select();
    resize();          
    text.style.minHeight = '70px';
    
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
            
            var firstThread = $rootScope.currentUser.threadList[Object.keys($rootScope.currentUser.threadList)[0]];            
            if(threadToLoad!=null){
                $rootScope.$emit('loadConversation', $rootScope.currentUser.threadList[threadToLoad.id]);                
            }else if(firstThread){                             
                $rootScope.$emit('loadConversation', firstThread);
                if(MOBILE_MODE){
                    $("#chatContainer").css("margin-left", "0%");                       
                }
            }else{
                if(MOBILE_MODE){
                    $("#chatContainer").css("margin-left", "-100%");       
                }
                $("#no_message").css("display", "block");
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
            $('#inboxBlock').css("display", "block");
            $('#searchBlock').css("display", "none");
            isInSearch = false;
            var thread ={                
                target: user
            }
            $rootScope.$emit('loadConversation', thread);               
        };
        
           
}]);

var goDown = function(){
    $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
};

/* chat controller */
messageApp.controller('chatController',['$scope', '$rootScope', '$http', function ( $scope, $rootScope, $http) {                           
            
            $scope.conversation = [];
            $scope.messageContent = null;                                  
                                        
            /* load new conversation */
            $rootScope.$on('loadConversation', function (event, thread) {
                if(MOBILE_MODE){
                    $("#chatContainer").css("margin-left", "-100%");                    
                }
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
                            $rootScope.currentUser.selectedThreadId = thread.id;                            
                            break;
                        }  
                    }                                    
                    if(!threadFounded){                        
                        $scope.currentThread = thread;
                        $rootScope.currentUser.selectedThreadId = thread.id;
                        $rootScope.firstMessage = true;                        
                    }     
                }                
                if(threadFounded){
                    $rootScope.firstMessage = false;
                    if(!$rootScope.currentUser.threadList[thread.id].haveReceiveMessage && ($rootScope.currentUser.threadList[thread.id] == null || $rootScope.currentUser.threadList[thread.id].messageList.length > 0)){
                        $scope.currentThread = thread;  
                        $rootScope.currentUser.selectedThreadId = thread.id;
                    }else if(threadFounded){
                        // load from inbox
                        // GET CONVERSATION //
                        $rootScope.currentUser.threadList[thread.id].haveReceiveMessage = false;
                        readyForMessage = false;
                        $http.get($rootScope.currentUser.getConversationApiUrl+'?threadId='+thread.id+"&page=1").
                            then(function(response){
                                $scope.currentThread = thread;
                                $rootScope.currentUser.selectedThreadId = thread.id;
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
        
        this.backToInbox = function(){
            $("#chatContainer").css("margin-left", "0%");            
        }
        
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
        
        var sendMessage = function(messageText){
            if(messageText){
                myData = {
                    threadId: $scope.currentThread.id,
                    targetId: $scope.currentThread.target.id,
                    messageContent: messageText
                };

                if(readyForMessage){                    
                    /* add new message to conversation (client only) */
                    var newMessage = {
                        content: messageText,
                        authorName: $rootScope.currentUser.name,
                        messageFromUs: true
                    }; 
                    
                    if($scope.currentThread.id != null){
                        $rootScope.currentUser.threadList[$scope.currentThread.id].messageList.push(newMessage);
                        $rootScope.currentUser.threadList[$scope.currentThread.id].lastMessage = newMessage;
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
                                $rootScope.currentUser.selectedThreadId = $scope.currentThread.id;
                                if($rootScope.currentUser.threadList[response.data.newThread.id]==null)$rootScope.currentUser.threadList[response.data.newThread.id] = response.data.newThread;
                                $rootScope.firstMessage = false;
                            }
                       }, function(response) {
                           console.log("Request failed : "+response.statusText );                        
                       }
                    );
            
                    setTimeout(goDown,10);                  
                }
            }
        };
        
        this.callSendMessage = function(){
            var content = $('#chatTextArea').val();  
            var caret = getCaret($('#chatTextArea'));
            $('#chatTextArea').val(content.substring(0, caret - 1) + content.substring(caret, content.length));
            sendMessage($scope.messageContent);
            emptyChatTextArea();
            
        }               
        
        var linesNumber = 0;
        var text = document.getElementById('chatTextArea');
        var chatComposer = document.getElementById('chatComposer');           
        
        $('#chatTextArea').keyup(function (event) {
            if (event.keyCode == 13) {
                var content = this.value;  
                var caret = getCaret(this);          
                if(event.shiftKey){                    
                    this.value = content.substring(0, caret - 1) + "\n" + content.substring(caret, content.length);
                    event.stopPropagation();
                } else {
                    this.value = content.substring(0, caret - 1) + content.substring(caret, content.length);
                    sendMessage($scope.messageContent);
                    emptyChatTextArea();
                }
            }
        });
        
        var emptyChatTextArea = function(){
            setTimeout(goDown,10);
            $scope.messageContent = null;
            setTimeout(function(){
                $('#chatTextArea').html('');
                $('#chatTextArea').val().replace(/^(\r\n)|(\n)/,'');
                document.getElementById('chatTextArea').style.height = 'auto';
                text.style.minHeight = 70+'px';
                text.style.maxHeight = 70+'px';  
                chatComposer.style.minHeight = 70+'px';
                chatComposer.style.maxHeight = 70+'px';
            }, 10);
        }
        
        function getCaret(el) { 
            if (el.selectionStart) { 
                return el.selectionStart; 
            } else if (document.selection) { 
                el.focus();
                var r = document.selection.createRange(); 
                if (r == null) { 
                    return 0;
                }
                var re = el.createTextRange(), rc = re.duplicate();
                re.moveToBookmark(r.getBookmark());
                rc.setEndPoint('EndToStart', re);
                return rc.text.length;
            }  
            return 0; 
        }               
        
}]);


$('#searchButton').click(function(){
    $('#inboxBlock').css("display", "none");
    $('#searchBlock').css("display", "inline-block");
    isInSearch = true;    
});

$('#cancelSearchButton').click(function(){
    $('#inboxBlock').css("display", "block");
    $('#searchBlock').css("display", "none");
    isInSearch = false; 
});

