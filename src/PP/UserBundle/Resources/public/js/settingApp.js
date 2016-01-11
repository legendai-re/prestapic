    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });
    
    var containerApp = angular.module('containerApp',  ['ngRoute']);       
    
    containerApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);                      

    // souscription au channel "/messages"
    
    containerApp.run(['$rootScope', function ($rootScope, FayeClient) {
        
    }])

    containerApp.controller('settingController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {            
            if($('#notif_mode').is(":checked") == true)this.notificationMode = true;
            else this.notificationMode = false;
            
            this.patchNotificationMode = function(){                 
                var formAction = document.forms["pp_user_api_settings_patch_notification_mode_form"].action;
                
                var myData = {};
                
                $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify(myData)
                 }). 
                    then(function(response) {                            
                    }, function(response) {
                     console.log("Request failed : "+response.statusText );                        
                    }
                );   
            }            
            
    }]);
    
   
    
