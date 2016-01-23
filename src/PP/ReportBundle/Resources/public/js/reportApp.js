
angular.element(document).ready(function() {
    try {
        var myDiv2 = document.getElementById("reportPopupApp");
        angular.bootstrap(myDiv2, ["reportPopupApp"]);
    }catch(e){
        console.log("allready bootstraped");
    }
});

var reportPopupApp = angular.module('reportPopupApp',  ['ngRoute']);

reportPopupApp.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);
}]);

reportPopupApp.run(['$rootScope', '$http',function ($rootScope, $http) {           
        
}]);

reportPopupApp.controller('reportController', ['$scope', '$rootScope', '$http', '$compile', '$location', '$window', function ($scope, $rootScope, $http, $compile, $location, $window) {    
        
    var haveAlreadyReport = false;
    var formLoaded = false;
    var ticketType = null;
    var targetId = null;
    
    $rootScope.$on('showPopup', function(event, message){
        ticketType = message.type;
        targetId = message.id;                  
        var formAction = document.forms["pp_report_api_get_report_form"].action;                              
        $http.get(formAction+".html?type="+message.type).
            then(function(response) {                    
                 var reportForm = angular.element(response.data);                        
                $compile(reportForm)($scope);                             
                angular.element( document.querySelector('#reportPopupApp')).html(reportForm);
                showPopup();
            }, function(response) {
                console.log("Request failed : "+response.statusText );                        
            }
        );       
    });   

    var showPopup = function(){
        $('#reportPopupApp').css("display", "block");                
    };
    
    this.reportData = {
        ticketType: ticketType,
        targetId: targetId,
        reasonId: "default",
        details: null
    };
    
    // send report //
    this.postReport = function(){
        $('#reportContainer').html("");
        $('#reportContainerAfter').html("Thanks, your report has been sent");
        this.reportData.ticketType = ticketType;
        this.reportData.targetId = targetId;
        console.log(this.reportData);
        var formAction = document.forms["pp_report_api_post_report_ticket_form"].action;
        $http({
            method: 'POST',
            url: formAction,                    
            data: JSON.stringify(this.reportData)
             }).
            then(function(response){                        
            },function(response) {
                console.log("Request failed : "+response.statusText );                        
            }
        );       
    };
    
    this.close = function(){
        $('#reportPopupApp').css("display", "none");                                                
    };
     
}]);