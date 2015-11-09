
    
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
    
    
    

