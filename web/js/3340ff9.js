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
var messageIsOpen = false;

headerApp.controller('headerController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {

        var haveAllreadyOpen = false;            
        var showMoreNotificationUrl = null;
        $scope.showMoreNotification = true;

        var closeAll = function(){
            if(messageIsOpen)closeMessage();
            if(notificationListIsOpen)closeNotification();
            if(filterListIsOpen)closeFilterList();
            if(userMenuIsOpen)closeUserMenu();
        }

        this.showMessage = function(){
            closeAll();
            var messageAppContainer = document.getElementById("messageApp");
            document.getElementById("body").style.position = "fixed";   
            if(messageAppContainer != null ){
                patchIsInMessage(true);
                if(!haveOpenMessage){
                    angular.element(document).ready(function() {       
                            angular.bootstrap(messageAppContainer, ["messageApp"]);
                    });
                    haveOpenMessage = true;
                }                                                           
                messageAppContainer.style.display = 'block';                    
                messageIsOpen = true;                                        
            }                
        }

        var closeMessage = function(){
            document.getElementById("body").style.position = "relative";
            if(document.getElementById('messageApp')!=null)document.getElementById('messageApp').style.display = 'none';
            patchIsInMessage(false);
            messageIsOpen = false;
        }

        var patchIsInMessage = function(mode){
            var isInMessageForm = document.forms["pp_user_api_patch_is_in_message_form"];
            if(isInMessageForm!=null){
                var myData = {
                    mode: mode
                };                    
                $http({
                    method: 'PATCH',
                    url: isInMessageForm.action,                    
                    data: JSON.stringify(myData)
                 }). 
                    then(function(response) {                                               

                    }, function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );                        
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
                closeAll();
                /* open notification list */
                document.getElementById('notificationList').style.display = 'block';                   
                $("#notificationButton").addClass("open");                    

                /* load notification the first time */
                if(!haveAllreadyOpen){                    
                    var getNotificationForm = document.forms["pp_notification_api_get_notification_form"];
                    if(getNotificationForm!=null){
                        $http.get(getNotificationForm.action).
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
                closeAll();
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
            closeAll();
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
            /* if not a message */
            if(currentNotif.type != 4){
                $window.location.href = currentNotif.redirectUrl;
            }else{
                this.showMessage();                                        
                angular.element(document.getElementById('messageApp')).scope().$emit('showNewMessage', currentNotif.authorId);
            }

        }

        this.showUserMenu = function(){                
            if(!userMenuIsOpen){
                closeAll();                    
                document.getElementById('userMenu').style.display = 'block';   
                userMenuIsOpen = true;
            }else{
                closeAll();
            }                
        }

        var closeUserMenu = function(){
            document.getElementById('userMenu').style.display = 'none';
            userMenuIsOpen = false;
        }

        this.showFilterList = function(){                
            if(!filterListIsOpen){
                closeAll();
                document.getElementById('filterList').style.display = 'block';                   
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
            closeAll();                
        });

        $('.stopPropagation').click(function(event){
            event.stopPropagation();
        });

}]);

headerApp.controller('filtersController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {
        
        this.showFiltersTags = function(){
            closeAll();
            document.getElementById('filtersTags').style.display = 'block';
            $("#filtersTagsButton").addClass("open");
        };
        
        this.showFiltersCat = function(){
            closeAll();
            document.getElementById('filtersCat').style.display = 'block';
            $("#filtersCatButton").addClass("open");
        };
        
        var closeAll = function(){
            document.getElementById('filtersTags').style.display = 'none';
            document.getElementById('filtersCat').style.display = 'none';
            $("#filtersCatButton").removeClass("open");
            $("#filtersTagsButton").removeClass("open");
        };
        
        /*//////////////////////////////
         *     CATEGORIES MANAGEMENT
         */
        this.categoriesList = [];
        this.addCategory = function(catId, catName){
            if(this.categoriesList[catId] == null){                
                this.categoriesList[catId] = catName;
            }else{                
                delete this.categoriesList[catId];
            }            
            console.log(this.categoriesList);
        }
        
        /// END OF CATEGORIES MANAGEMENT
        ////////////////////////////////
        
        
        /*//////////////////////////
         *     TAGS MANAGEMENT
         */
        this.tagsListClicked = [];
        this.tagsListStr = null;
        this.tagList = [];
        
        this.addTag = function(tagId, tagName){
            if(this.tagsListClicked[tagId] == null){
                $("#tag_"+tagId).addClass("choosen");
                this.tagsListClicked[tagId] = tagName;
            }else{
                $("#tag_"+tagId).removeClass("choosen");
                delete this.tagsListClicked[tagId];
            }            
            console.log(this.tagsListClicked);
        };
        
        this.tagStrToArray = function(){
            var tempTagList = [];
            if(this.tagsListStr){
                this.tagsListStr = this.tagsListStr.toLowerCase()+',';
                this.tagsListStr = this.tagsListStr.replace(' ', '');
                var actualTag = '';
                var tagCharArray = this.tagsListStr.split('');
                
                tagCharArray.forEach(function(char) {
                    if(char != ','){
                        actualTag+=char;
                    }else{
                        if(actualTag){
                            tempTagList.push(actualTag);
                        }
                        actualTag = '';
                    }
                });
            }
            this.tagsListClicked.forEach(function(tag) {
                tempTagList.push(tag);
            });                        

            var uniqueTags = [];
            $.each(tempTagList, function(i, el){
                if($.inArray(el, uniqueTags) === -1) uniqueTags.push(el);
            });

            this.tagList = uniqueTags;
            
        };
        /// END OF TAGS MANAGEMENT
        //////////////////////////
        
        this.searchQuery = '';
        //////////////////////////
        //     SUBMIT SEARCH
        this.submitForm = function(){
            this.tagStrToArray();
            
            var submitFormUrl = document.getElementsByName("search_action")[0].value;
            var searchQueryParam = 'search_query='+this.searchQuery;
            var tagListParam = 'tags=';            
            for(var i =0; i<this.tagList.length; i++){
                tagListParam += this.tagList[i];
                if(i<this.tagList.length-1)tagListParam += '+';
            }
            var catListParam = 'categories=';
            for(var i =0; i<this.categoriesList.length; i++){
                if(this.categoriesList[i]){
                    catListParam += this.categoriesList[i];
                    if(i<this.categoriesList.length-1)catListParam += '+';
                }
            }
            $window.location.href = submitFormUrl+'?'+searchQueryParam+'&'+tagListParam+'&'+catListParam;
        }
}]); 



containerApp.directive("bnLazySrc",["$window","$document",function(n,e){function r(n){function e(e,r){if(!n.is(":visible"))return!1;null===u&&(u=n.height());var t=n.offset().top,i=t+u;return r>=t&&t>=e||r>=i&&i>=e||e>=t&&i>=r}function r(){c=!0,i()}function t(n){o=n,c&&i()}function i(){n[0].src=o}var o=null,c=!1,u=null;return{isVisible:e,render:r,setSource:t}}function t(n,e,t){var o=new r(e);i.addImage(o),t.$observe("bnLazySrc",function(n){o.setSource(n)}),n.$on("$destroy",function(){i.removeImage(o)})}var i=function(){function r(n){s.push(n),v||u(),z||l()}function t(n){for(var e=0;e<s.length;e++)if(s[e]===n){s.splice(e,1);break}s.length||(c(),f())}function i(){if(!v){var n=b.height();n!==d&&(d=n,u())}}function o(){for(var n=[],e=[],r=g.height(),t=g.scrollTop(),i=t,o=i+r,u=0;u<s.length;u++){var l=s[u];l.isVisible(i,o)?n.push(l):e.push(l)}for(var u=0;u<n.length;u++)n[u].render();s=e,c(),s.length||f()}function c(){clearTimeout(v),v=null}function u(){v=setTimeout(o,h)}function l(){z=!0,g.on("resize.bnLazySrc",a),g.on("scroll.bnLazySrc",a),m=setInterval(i,p)}function f(){z=!1,g.off("resize.bnLazySrc"),g.off("scroll.bnLazySrc"),clearInterval(m)}function a(){v||u()}var s=[],v=null,h=100,g=$(n),b=e,d=b.height(),m=null,p=2e3,z=!1;return{addImage:r,removeImage:t}}();return{link:t,restrict:"A"}}]);


    /*containerApp.directive(
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
            );*/

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

