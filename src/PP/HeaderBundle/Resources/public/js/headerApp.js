



var headerApp = angular.module('headerApp',[]);       

headerApp.config(['$locationProvider',function ($locationProvider) {
    $locationProvider.html5Mode(true);
}]);          

/* node */
headerApp.service('FayeClient', function () {
        return new Faye.Client('http://alexandrejolly.com:3000/');
});


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

}]);

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
        
        $(".fixeBodyHover").mouseover(function() { $("#body").css("position", "fixed");});
        $(".fixeBodyHover").mouseout(function() { $("#body").css("position", "absolute");});
        
        var haveLoadNewRequestForm = false;
        var haveAllreadyOpen = false;            
        var showMoreNotificationUrl = null;
        $scope.showMoreNotification = true;
        
        this.showNewRequestForm = function(){
            var getRequestForm = document.forms["pp_request_api_get_request_form"];
            if(!haveLoadNewRequestForm){
                haveLoadNewRequestForm = true;
                $http.get(getRequestForm.action+".html").
                    then(function(response) {
                        $("#sendRequestContent").html(response.data);                           
                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );
            }
            $('#sendRequestOver').fadeIn();           
        }
        
        var closeAll = function(){
            if(messageIsOpen)closeMessage();
            if(notificationListIsOpen)closeNotification();
            if(filterListIsOpen)closeFilterList();
            if(userMenuIsOpen)closeUserMenu();
            $('#searchUser').css("display", "none");
        };

        this.showMessage = function(){
            closeAll();            
            var messageAppContainer = document.getElementById("messageApp");
            document.getElementById("body").style.position = "fixed";   
            if(messageAppContainer != null ){                
                patchIsInMessage(true);
                if(!haveOpenMessage){                         
                    angular.bootstrap(messageAppContainer, ["messageApp"]);                    
                    haveOpenMessage = true;
                }                                                           
                messageAppContainer.style.display = 'block';                    
                messageIsOpen = true;                                        
            }                
        };

        var closeMessage = function(){
            document.getElementById("body").style.position = "relative";
            if(document.getElementById('messageApp')!=null)document.getElementById('messageApp').style.display = 'none';
            patchIsInMessage(false);
            messageIsOpen = false;
        };

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
        };

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
        };

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
                                $("#loadingNotif").css("display", "none");
                                showMoreNotificationUrl = response.data.showMoreApiUrl;
                                if(response.data.notifications.length == 0)$("#noNotification").css("display", "block");
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
        };


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
        };

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
                var thread ={ 
                    id: currentNotif.messageThreadId,
                    target: {
                        id: currentNotif.authorId,
                        name: currentNotif.authorName,
                        image: currentNotif.authorImg
                    }
                }               
                angular.element(document.getElementById('messageApp')).scope().$emit('showNewMessage', thread);
            }
        };

        this.showUserMenu = function(){                
            if(!userMenuIsOpen){
                closeAll();                    
                document.getElementById('userMenu').style.display = 'block';   
                userMenuIsOpen = true;
            }else{
                closeAll();
            }                
        };

        var closeUserMenu = function(){
            document.getElementById('userMenu').style.display = 'none';
            userMenuIsOpen = false;
        };

        this.showFilterList = function(){                
            if(!filterListIsOpen){
                closeAll();
                document.getElementById('filterList').style.display = 'block';                   
                filterListIsOpen = true;
                $("#filterButton").addClass("open");
            }else{
                closeFilterList();
            }                
        };

        var closeFilterList = function(){
            if(filterListIsOpen){
                document.getElementById('filterList').style.display = 'none';
                $("#filterButton").removeClass("open");
                filterListIsOpen = false;
            }
            document.getElementById('searchOptions').style.display = 'none';
        };

        this.showSearchOptions = function(){
            document.getElementById('searchOptions').style.display = 'block';
            $('#searchUser').css("display", "block");
        };

        /* handle new notification */
        $scope.$on('notification', function (event, message) {                
            if(haveAllreadyOpen){
                $scope.notifications.unshift(message.notification);
                $scope.$apply();
                $("#noNotification").css("display", "none");
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
        
        function wireUpEvents() {
                function goodbye(e) {
                  angular.element(document.getElementById('headerController')).scope().leave();
                }
                window.onbeforeunload=goodbye;
        }
        $(document).ready(function() {
          wireUpEvents();
        });
        
        $scope.leave = function(){
            closeAll();
        }

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
        };
        
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
        };
        
        this.tagStrToArray = function(){
            var tempTagList = [];
            if(this.tagsListStr){
                this.tagsListStr = this.tagsListStr.toLowerCase()+',';
                this.tagsListStr = this.tagsListStr.replace(' ', '');
                var actualTag = '';
                var tagCharArray = this.tagsListStr.split('');
                
                tagCharArray.forEach(function(myChar) {
                    if(myChar != ','){
                        actualTag+=myChar;
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
        
        this.conceringMe = false;
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
            
            var concerningMeParam = '';
            if(this.conceringMe){
                concerningMeParam = 'me=true'
            }
            
            $window.location.href = submitFormUrl+'?'+searchQueryParam+'&'+tagListParam+'&'+catListParam+'&'+concerningMeParam;
        };
}]); 


