    
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
                            $rootScope.$emit('changeCurrentObject', $scope.currentObject);
                            break;
                        case PROPOSITION:
                            $scope.reportObjects.propositionList[targetId].reportTicketList = response.data;
                            $scope.currentObject =  $scope.reportObjects.propositionList[targetId];
                            $scope.currentObject.type = PROPOSITION;
                            $rootScope.$emit('changeCurrentObject', $scope.currentObject);
                            break;
                        case USER:
                            $scope.reportObjects.userList[targetId].reportTicketList = response.data;
                            $scope.currentObject =  $scope.reportObjects.userList[targetId];
                            $scope.currentObject.type = USER;                          
                            $rootScope.$emit('changeCurrentObject', $scope.currentObject);
                            break;
                    }              
                },function(response) {                    
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
            var type = $scope.disableData.ticketType;
            switch(type){
                case IMAGE_REQUEST:
                    var promptValue = prompt("Enter \"YES\" to comfirm");
                    break;
                case PROPOSITION:
                    var promptValue = prompt("Enter \"YES\" to comfirm");
                    break;
                case USER:
                    var promptValue = prompt("Enter user name to comfirm");
                    break;
            }
            
            if((type == IMAGE_REQUEST && promptValue == "YES") || (type == USER && promptValue == $scope.currentObject.name) || (type == PROPOSITION && promptValue == "YES")){
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
                                    showBannerAlert("success", "Image request deleted !", "");
                                    break;
                                case PROPOSITION:
                                    $scope.currentObject = null;
                                    $scope.reportObjects.propositionList[$scope.disableData.targetId] = null;
                                    showBannerAlert("success", "Proposition deleted !", "");
                                    break;
                                case USER:
                                    $scope.currentObject = null;
                                    $scope.reportObjects.userList[$scope.disableData.targetId] = null;
                                    showBannerAlert("success", "User deleted !", "");
                                    break;
                            }
                        },function(response) {
                            console.log("Request failed : "+response.statusText );
                            showBannerAlert("danger", response.statusText, "");
                        }
                    );  
                }
            }else showBannerAlert("warning", "Wrong text entered", "");
        };
        
        this.patchIgnoreReport = function(){
            var formAction = document.forms["pp_report_api_patch_ignore_tickets_form"].action;
            if(confirm("Do you realy want to ignore reports ?")){
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
                                    showBannerAlert("success", "Image request's reports ignored !", "");
                                    break;
                                case PROPOSITION:
                                    $scope.currentObject = null;
                                    $scope.reportObjects.propositionList[$scope.disableData.targetId] = null;
                                    showBannerAlert("success", "Proposition reports ignored !", "");
                                    break;
                                case USER:
                                    $scope.currentObject = null;
                                    $scope.reportObjects.userList[$scope.disableData.targetId] = null;
                                    showBannerAlert("success", "User's reports ignored !", "");
                                    break;
                            }
                        },function(response) {
                            console.log("Request failed : "+response.statusText );
                            showBannerAlert("danger", response.statusText, "");
                        }
                );
            }
        };
        
    }]);
