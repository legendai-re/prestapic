    
    var IMAGE_REQUEST = 1;
    var PROPOSITION = 2;
    var USER = 3;
    angular.element(document).ready(function() {
        var myDiv2 = document.getElementById("containerApp");
        angular.bootstrap(myDiv2, ["containerApp"]);
    });

    var containerApp = angular.module('containerApp',  ['ngRoute']);
    
    containerApp.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);          
    
    containerApp.run(['$rootScope', '$http',function ($rootScope, $http) {            
        $rootScope.currentObject = {};
        var formAction = document.forms["pp_dashboard_report_api_get_reported_objects_form"].action;                                
        $http.get(formAction).
            then(function(response){
                $rootScope.reportObjects = response.data;                
            },function(response) {
                console.log(response);
                console.log("Request failed : "+response.statusText );                            
            }
        );

    }]);
    
    containerApp.controller('objectReportedController', ['$scope', '$rootScope', '$http', '$compile', '$location', function ($scope, $rootScope, $http, $compile, $location) {
        
        this.currentType = IMAGE_REQUEST;
        
        this.changeCurrentType = function(type){
            this.currentType = type;
            $rootScope.$emit('changeType', type);
        };
        
        this.getReportTicket = function(type, targetId){
                                  
            var formAction = document.forms["pp_dashboard_report_api_get_report_ticket_form"].action;                                
            $http.get(formAction+"?type="+type+"&targetId="+targetId).
                then(function(response){
                    switch(type){
                        case IMAGE_REQUEST:
                           $scope.reportObjects.imageRequestList[targetId].reportTicketList = response.data;
                           $scope.currentObject =  $scope.reportObjects.imageRequestList[targetId];
                           $scope.currentObject.type = IMAGE_REQUEST;
                           console.log($scope.currentObject);
                           $rootScope.$emit('changeCurrentObject', $scope.currentObject);
                           break;
                        case USER:
                          $scope.reportObjects.userList[targetId].reportTicketList = response.data;
                          $scope.currentObject =  $scope.reportObjects.userList[targetId];
                          $scope.currentObject.type = USER;
                          console.log($scope.currentObject);
                          $rootScope.$emit('changeCurrentObject', $scope.currentObject);
                          break;
                    }              
                },function(response) {
                    console.log(response);
                    console.log("Request failed : "+response.statusText );                            
                }
            );            
        };
        
    }]);
    
    containerApp.controller('reportController', ['$scope', '$rootScope', '$http', '$compile', '$location', function ($scope, $rootScope, $http, $compile, $location) {
        
        $scope.currentObject = null;
        
        $scope.disableData = {
            reasonId: 1,
            details: null
        };
        
        $rootScope.$on('changeCurrentObject', function(event, currentObject){
            $scope.currentObject = currentObject;
            $scope.disableData.ticketType = currentObject.type;
            $scope.disableData.targetId = currentObject.id;
        });
        
        $rootScope.$on('changeType', function(event, type){
            $scope.currentObject = null;
            $scope.disableData.ticketType = type;            
        });
        
        this.postDisableRequest = function(){                            
            var formAction = document.forms["pp_report_api_post_disable_ticket_form"].action;
            if($scope.disableData.targetId != null){
                console.log($scope.disableData);
                $scope.disableData.reasonId = parseInt($scope.disableData.reasonId);
                $http({
                    method: 'POST',
                    url: formAction,                    
                    data: JSON.stringify($scope.disableData)
                     }).
                    then(function(response){
                        switch($scope.disableData.ticketType){
                            case IMAGE_REQUEST:
                                $scope.currentObject = null;
                                $scope.reportObjects.userList[$scope.disableData.targetId] = null;
                            case USER:
                                $scope.currentObject = null;
                                $scope.reportObjects.userList[$scope.disableData.targetId] = null;
                        }
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
                );  
            }
        };
        
        this.patchIgnoreReport = function(){
            var formAction = document.forms["pp_report_api_patch_ignore_tickets_form"].action;
            $http({
                    method: 'PATCH',
                    url: formAction,                    
                    data: JSON.stringify($scope.disableData)
                     }).
                    then(function(response){
                        switch($scope.disableData.ticketType){
                            case IMAGE_REQUEST:
                                $scope.currentObject = null;
                                $scope.reportObjects.imageRequestList[$scope.disableData.targetId] = null;
                            case USER:
                                $scope.currentObject = null;
                                $scope.reportObjects.userList[$scope.disableData.targetId] = null;
                        }
                    },function(response) {
                        console.log("Request failed : "+response.statusText );                        
                    }
            );
        };
        
    }]);
