$("#pp_requestbundle_image_request_title").one('focus', function () {
   $('#requestFormRest').slideToggle();
   $('.content').removeClass("init");
});

$(".signInUp").click(function () {
   $('#signInUpOver').fadeIn();  
});

$("#signInUpOverClose").click(function () {
   $('#signInUpOver').fadeOut();  
});

    
    var headerApp = angular.module('headerApp',[]);       
    
    headerApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
        
    /* node */
    headerApp.service('FayeClient', function () {
            return new Faye.Client('http://localhost:3000/');
    })
    
    headerApp.run(['$rootScope', 'FayeClient', '$http',function ($rootScope, FayeClient, $http) {
        $rootScope.notifications = []        
               
        var form = document.forms["pp_notification_api_get_thread_form"];        
        if(form != null){
            var formAction = form.action;
            var notificationThreadSlug = null;

            $http.get(formAction).
                then(function(response) {
                    var jsonResponse = JSON.parse(response.data);
                    notificationThreadSlug = jsonResponse.notifThreadSlug;

                    FayeClient.subscribe('/notification/'+notificationThreadSlug, function (message) {            
                        $rootScope.$broadcast('notification', message);            
                    });

                }, function(response) {
                 console.log("Request failed : "+response.statusText );                        
                }
            );
        }
        
    }])
    
    headerApp.filter('reverse', function() {
        return function(items) {
          return items.slice().reverse();
        };
      });
    
    
    var notificationListIsOpen = false;
    var userMenuIsOpen = false;
    var filterListIsOpen = false;
    var haveOpenMessage = false;
    
    headerApp.controller('headerController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {
            
            var haveAllreadyOpen = false;            
            var showMoreNotificationUrl = null;
            $scope.showMoreNotification = true;
            
            this.showMessage = function(){
                if(!haveOpenMessage){
                    var messageAppContainer = document.getElementById("messageApp");                    
                    if(messageAppContainer != null ){
                        angular.element(document).ready(function() {       
                                angular.bootstrap(messageAppContainer, ["messageApp"]);
                        });
                        haveOpenMessage = true;
                    }
                }
            }
            
            var closeNotification = function(){
                 /* close notification list */
                document.getElementById('notificationList').style.display = 'none';
                $("#notificationButton").removeClass("alert");
                $("#notificationButton").removeClass("open");
                document.getElementById('notificationsNb').innerHTML = 0;
                
                 /* set the notification viewed in database */
                var setNotificationViewedForm = document.forms["pp_notification_api_patch_viewed_form"];
                if(setNotificationViewedForm!=null){

                    var myData = [];                    
                    $http({
                        method: 'PATCH',
                        url: setNotificationViewedForm.action,                    
                        data: JSON.stringify(myData)
                     }). 
                        then(function(response) {                                               

                        }, function(response) {
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );                        
                }
                
                /* set the notification viewed on client */
                for(var index in $scope.notifications) { 
                    if ($scope.notifications.hasOwnProperty(index)) {
                        $scope.notifications[index].isViewed = true;
                    }
                }

                notificationListIsOpen = false;                
            }
            
            this.showNotifications = function(){
                
                if(!notificationListIsOpen){
                    /* open notification list */
                    document.getElementById('notificationList').style.display = 'block';
                    document.getElementById('userMenu').style.display = 'none';
                    $("#notificationButton").addClass("open");
                    closeFilterList();
                    
                    /* load notification the first time */
                    if(!haveAllreadyOpen){                    
                        var getNotificationForm = document.forms["pp_notification_api_get_notification_form"];
                        if(getNotificationForm!=null){
                            $http.get(getNotificationForm.action    ).
                                then(function(response) {                                  
                                    showMoreNotificationUrl = response.data.showMoreApiUrl;
                                    for(var x=0; x<response.data.notifications.length; x++){                    
                                        $scope.notifications.push(response.data.notifications[x]);                                                        
                                    }                                
                                    if(!response.data.showMore)$scope.showMoreNotification = false;
                                    haveAllreadyOpen = true;                                
                                }, function(response) {
                                 console.log("Request failed : "+response.statusText );                        
                                }
                            );
                        }                    
                    }                   
                    
                    notificationListIsOpen = true;
                    userMenuIsOpen = false;

                }else{
                    closeNotification();
                }
            }
                        
            
            this.showMoreNotifications = function(){
                if(notificationListIsOpen && showMoreNotificationUrl!=null){                                
                                                                                                  
                    $http.get(showMoreNotificationUrl).
                        then(function(response) {                            
                            showMoreNotificationUrl = response.data.showMoreApiUrl;
                            for(var x=0; x<response.data.notifications.length; x++){                    
                                $scope.notifications.push(response.data.notifications[x]);                                
                            }
                            if(!response.data.showMore)$scope.showMoreNotification = false;                            
                        }, function(response) {
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );                                        
                }   
            }
            
            this.patchNotificationClicked = function(index){
                
                closeNotification();
                
                var currentNotif = $scope.notifications[index];
                var url = currentNotif.setClickedUrl;
               
                var myData = [];                
                
                if(!currentNotif.isCliked){
                    $http({
                        method: 'PATCH',
                        url: url,                    
                        data: JSON.stringify(myData)
                     }). 
                        then(function(response) {                                               

                        }, function(response) {
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );
                }
                $window.location.href = currentNotif.redirectUrl;
        
            }
            
            this.showUserMenu = function(){
                
                if(!userMenuIsOpen){
                    if(notificationListIsOpen)closeNotification();
                    document.getElementById('userMenu').style.display = 'block';
                    document.getElementById('filterList').style.display = 'none';
                    filterListIsOpen = false;
                    userMenuIsOpen = true;                                        
                }else{
                    document.getElementById('userMenu').style.display = 'none';
                    userMenuIsOpen = false;
                }                
            }
            
            this.showFilterList = function(){
                
                if(!filterListIsOpen){                    
                    if(notificationListIsOpen)closeNotification();
                    document.getElementById('filterList').style.display = 'block';
                    document.getElementById('userMenu').style.display = 'none';
                    userMenuIsOpen = false;
                    filterListIsOpen = true;
                    $("#filterButton").addClass("open");
                }else{
                    closeFilterList();
                }                
            }
            
            var closeFilterList = function(){
                if(filterListIsOpen){
                    document.getElementById('filterList').style.display = 'none';
                    $("#filterButton").removeClass("open");
                    filterListIsOpen = false;
                }
                document.getElementById('searchOptions').style.display = 'none';
            }
            
            this.showSearchOptions = function(){
                document.getElementById('searchOptions').style.display = 'block';                                
            }
            
            /* handle new notification */
            $scope.$on('notification', function (event, message) {                
                if(haveAllreadyOpen){
                    $scope.notifications.unshift(message.notification);
                    $scope.$apply();
                }                
                document.getElementById('notificationsNb').innerHTML = parseInt(document.getElementById('notificationsNb').innerHTML)+1;
                $("#notificationButton").addClass("alert");
            });                     
            
                /* hide menu on click out */
            $('html').click(function() {                
                if(notificationListIsOpen)closeNotification();
                if(document.getElementById('userMenu')!=null)document.getElementById('userMenu').style.display = 'none';                
                userMenuIsOpen = false;
                closeFilterList();                
            });

            $('.stopPropagation').click(function(event){
                event.stopPropagation();
            });
            
    }]);
    
    
    



    containerApp.directive(
                "bnLazySrc",
                ['$window', '$document',function( $window, $document ) {
                    // I manage all the images that are currently being
                    // monitored on the page for lazy loading.
                    var lazyLoader = (function() {
                        // I maintain a list of images that lazy-loading
                        // and have yet to be rendered.
                        var images = [];
                        // I define the render timer for the lazy loading
                        // images to that the DOM-querying (for offsets)
                        // is chunked in groups.
                        var renderTimer = null;
                        var renderDelay = 100;
                        // I cache the window element as a jQuery reference.
                        var win = $( $window );
                        // I cache the document document height so that
                        // we can respond to changes in the height due to
                        // dynamic content.
                        var doc = $document;
                        var documentHeight = doc.height();
                        var documentTimer = null;
                        var documentDelay = 2000;
                        // I determine if the window dimension events
                        // (ie. resize, scroll) are currenlty being
                        // monitored for changes.
                        var isWatchingWindow = false;
                        // ---
                        // PUBLIC METHODS.
                        // ---
                        // I start monitoring the given image for visibility
                        // and then render it when necessary.
                        function addImage( image ) {
                            images.push( image );
                            if ( ! renderTimer ) {
                                startRenderTimer();
                            }
                            if ( ! isWatchingWindow ) {
                                startWatchingWindow();
                            }
                        }
                        // I remove the given image from the render queue.
                        function removeImage( image ) {
                            // Remove the given image from the render queue.
                            for ( var i = 0 ; i < images.length ; i++ ) {
                                if ( images[ i ] === image ) {
                                    images.splice( i, 1 );
                                    break;
                                }
                            }
                            // If removing the given image has cleared the
                            // render queue, then we can stop monitoring
                            // the window and the image queue.
                            if ( ! images.length ) {
                                clearRenderTimer();
                                stopWatchingWindow();
                            }
                        }
                        // ---
                        // PRIVATE METHODS.
                        // ---
                        // I check the document height to see if it's changed.
                        function checkDocumentHeight() {
                            // If the render time is currently active, then
                            // don't bother getting the document height -
                            // it won't actually do anything.
                            if ( renderTimer ) {
                                return;
                            }
                            var currentDocumentHeight = doc.height();
                            // If the height has not changed, then ignore -
                            // no more images could have come into view.
                            if ( currentDocumentHeight === documentHeight ) {
                                return;
                            }
                            // Cache the new document height.
                            documentHeight = currentDocumentHeight;
                            startRenderTimer();
                        }
                        // I check the lazy-load images that have yet to
                        // be rendered.
                        function checkImages() {
                            // Log here so we can see how often this
                            // gets called during page activity.
                            var visible = [];
                            var hidden = [];
                            // Determine the window dimensions.
                            var windowHeight = win.height();
                            var scrollTop = win.scrollTop();
                            // Calculate the viewport offsets.
                            var topFoldOffset = scrollTop;
                            var bottomFoldOffset = ( topFoldOffset + windowHeight );
                            // Query the DOM for layout and seperate the
                            // images into two different categories: those
                            // that are now in the viewport and those that
                            // still remain hidden.
                            for ( var i = 0 ; i < images.length ; i++ ) {
                                var image = images[ i ];
                                if ( image.isVisible( topFoldOffset, bottomFoldOffset ) ) {
                                    visible.push( image );
                                } else {
                                    hidden.push( image );
                                }
                            }
                            // Update the DOM with new image source values.
                            for ( var i = 0 ; i < visible.length ; i++ ) {
                                visible[ i ].render();
                            }
                            // Keep the still-hidden images as the new
                            // image queue to be monitored.
                            images = hidden;
                            // Clear the render timer so that it can be set
                            // again in response to window changes.
                            clearRenderTimer();
                            // If we've rendered all the images, then stop
                            // monitoring the window for changes.
                            if ( ! images.length ) {
                                stopWatchingWindow();
                            }
                        }
                        // I clear the render timer so that we can easily
                        // check to see if the timer is running.
                        function clearRenderTimer() {
                            clearTimeout( renderTimer );
                            renderTimer = null;
                        }
                        // I start the render time, allowing more images to
                        // be added to the images queue before the render
                        // action is executed.
                        function startRenderTimer() {
                            renderTimer = setTimeout( checkImages, renderDelay );
                        }
                        // I start watching the window for changes in dimension.
                        function startWatchingWindow() {
                            isWatchingWindow = true;
                            // Listen for window changes.
                            win.on( "resize.bnLazySrc", windowChanged );
                            win.on( "scroll.bnLazySrc", windowChanged );
                            // Set up a timer to watch for document-height changes.
                            documentTimer = setInterval( checkDocumentHeight, documentDelay );
                        }
                        // I stop watching the window for changes in dimension.
                        function stopWatchingWindow() {
                            isWatchingWindow = false;
                            // Stop watching for window changes.
                            win.off( "resize.bnLazySrc" );
                            win.off( "scroll.bnLazySrc" );
                            // Stop watching for document changes.
                            clearInterval( documentTimer );
                        }
                        // I start the render time if the window changes.
                        function windowChanged() {
                            if ( ! renderTimer ) {
                                startRenderTimer();
                            }
                        }
                        // Return the public API.
                        return({
                            addImage: addImage,
                            removeImage: removeImage
                        });
                    })();
                    // ------------------------------------------ //
                    // ------------------------------------------ //
                    // I represent a single lazy-load image.
                    function LazyImage( element ) {
                        // I am the interpolated LAZY SRC attribute of
                        // the image as reported by AngularJS.
                        var source = null;
                        // I determine if the image has already been
                        // rendered (ie, that it has been exposed to the
                        // viewport and the source had been loaded).
                        var isRendered = false;
                        // I am the cached height of the element. We are
                        // going to assume that the image doesn't change
                        // height over time.
                        var height = null;
                        // ---
                        // PUBLIC METHODS.
                        // ---
                        // I determine if the element is above the given
                        // fold of the page.
                        function isVisible( topFoldOffset, bottomFoldOffset ) {
                            // If the element is not visible because it
                            // is hidden, don't bother testing it.
                            if ( ! element.is( ":visible" ) ) {
                                return( false );
                            }
                            // If the height has not yet been calculated,
                            // the cache it for the duration of the page.
                            if ( height === null ) {
                                height = element.height();
                            }
                            // Update the dimensions of the element.
                            var top = element.offset().top;
                            var bottom = ( top + height );
                            // Return true if the element is:
                            // 1. The top offset is in view.
                            // 2. The bottom offset is in view.
                            // 3. The element is overlapping the viewport.
                            return(
                                    (
                                        ( top <= bottomFoldOffset ) &&
                                        ( top >= topFoldOffset )
                                    )
                                ||
                                    (
                                        ( bottom <= bottomFoldOffset ) &&
                                        ( bottom >= topFoldOffset )
                                    )
                                ||
                                    (
                                        ( top <= topFoldOffset ) &&
                                        ( bottom >= bottomFoldOffset )
                                    )
                            );
                        }
                        // I move the cached source into the live source.
                        function render() {
                            isRendered = true;
                            renderSource();
                        }
                        // I set the interpolated source value reported
                        // by the directive / AngularJS.
                        function setSource( newSource ) {
                            source = newSource;
                            if ( isRendered ) {
                                renderSource();
                            }
                        }
                        // ---
                        // PRIVATE METHODS.
                        // ---
                        // I load the lazy source value into the actual
                        // source value of the image element.
                        function renderSource() {
                            element[ 0 ].src = source;
                        }
                        // Return the public API.
                        return({
                            isVisible: isVisible,
                            render: render,
                            setSource: setSource
                        });
                    }
                    // ------------------------------------------ //
                    // ------------------------------------------ //
                    // I bind the UI events to the scope.
                    function link( $scope, element, attributes ) {
                        var lazyImage = new LazyImage( element );
                        // Start watching the image for changes in its
                        // visibility.
                        lazyLoader.addImage( lazyImage );
                        // Since the lazy-src will likely need some sort
                        // of string interpolation, we don't want to
                        attributes.$observe(
                            "bnLazySrc",
                            function( newSource ) {
                                lazyImage.setSource( newSource );
                            }
                        );
                        // When the scope is destroyed, we need to remove
                        // the image from the render queue.
                        $scope.$on(
                            "$destroy",
                            function() {
                                lazyLoader.removeImage( lazyImage );
                            }
                        );
                    }
                    // Return the directive configuration.
                    return({
                        link: link,
                        restrict: "A"
                    });
                }]
            );


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

