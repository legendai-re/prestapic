    
    angular.element(document).ready(function() {       
        var myDiv2 = document.getElementById("signApp");
        angular.bootstrap(myDiv2, ["signApp"]);
    });
    
    var signApp = angular.module('signApp',  ['ngRoute']);       
    
    signApp.config(['$locationProvider',function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);                 
    
    signApp.run(['$rootScope', '$location', function ($rootScope, $location) {
      
    }])

    signApp.controller('signController', ['$scope', '$http', '$compile', '$location', '$window', function ($scope, $http, $compile, $location, $window) {
            
        var haveLoadSignUpForm = false;                
        
        this.showSignUp = function(){                      
            if(!haveLoadSignUpForm){
                $("#signInContent").animate({ opacity: 0 },"normal",function(){
                   
                })
            }else{
                $("#signInContent").fadeOut(function(){                    
                    $("#signContainer").addClass('onsignup');
                    setTimeout(function () {                        
                        $("#signUpContent").fadeIn();
                    }, 500);
                })
            }
            
            if(!haveLoadSignUpForm){
                $("#signContainer").addClass('onsignup');
                haveLoadSignUpForm = true;
                var formAction = document.forms["pp_user_api_register_get_register_form"].action;                
                $http.get(formAction+".html").
                    then(function(response) {                                    
                        var signUpForm = angular.element(response.data);                        
                        $compile(signUpForm)($scope);                                              
                        angular.element( document.querySelector('#signUpFormContainer')).append(signUpForm);                        
                        $(".fos_user_registration_register").preventDoubleSubmission();
                        $("#signInContent").css("display", "none");
                        $("#signInContent").animate({ opacity: 10 });                        
                        $("#signUpContent").fadeIn();
                    }, function(response) {
                    console.log("Request failed : "+response.statusText );                        
                }
                );
            }
        }
        
        this.showSignIn= function(){                        
            $("#signUpContent").fadeOut(function(){               
                $("#signContainer").removeClass('onsignup');
                setTimeout(function () {                   
                   $("#signInContent").fadeIn();
                }, 500);
               
            });            
        };
            
    }]);
        