    
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
        
        
        var allowedChar = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9','-','_','.'];
        $scope.username = '';
        $scope.usernameValid = true;
        $scope.usernameMessage = '';
        $scope.email = '';
        $scope.emailValid = true;
        $scope.emailMessage = '';              
        
        $scope.firstPassword = '';
        $scope.secondPassword = '';
        $scope.passwordValid = true;
        $scope.passwordMessage = true;
        
        var timeoutUsername = null;
        var timeoutEmail = null;
       
        
        this.usernameTyping = function(){
            $scope.usernameValid = false;
            if (timeoutUsername !== null) {                
                clearTimeout(timeoutUsername);
            }
            
            timeoutUsername = setTimeout(function() {                
                checkUsername();
            }, 1500);
        };
        
        this.emailTyping = function(){
            $scope.emailValid = false;
            if (timeoutEmail !== null) {                
                clearTimeout(timeoutEmail);
            }
            
            timeoutEmail = setTimeout(function() {                
                checkEmail();
            }, 1500);
        };
        
        function checkEmail() {
            $scope.emailValid = false;
            $scope.emailValid = '';
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if(re.test($scope.email)){
                var formAction = document.forms["pp_user_api_get_email_exist_form"].action;                
                $http.get(formAction+"?email="+$scope.email).
                    then(function(response) {                                    
                        if(response.data.exist){
                            $scope.emailValid = false;
                            console.log("email already used");
                            $scope.emailValid = "already used"; 
                        }else{
                            $scope.emailValid = true;
                            console.log("email Ok");
                        }
                    }, function(response) {
                        $scope.emailValid = false;
                        console.log("Request failed : "+response.statusText );                        
                    }
                );
            }else{
               $scope.emailMessage = 'not a valid email';
               $scope.emailValid = false;
               console.log("email invalid");
            }
        }
        
        var checkUsername = function(){
            console.log("check");
            $scope.usernameValid = false;
            $scope.usernameMessage = '';
            if($scope.username != null && $scope.username != ''){
                if($scope.username.length > 30){
                    $scope.usernameValid = false;
                    $scope.usernameMessage = "to long"; 
                }else if($scope.username.length < 2){
                    $scope.usernameValid = false;
                    $scope.usernameMessage = "to short"; 
                }else{
                    $scope.usernameValid = true;
                    for(var i=0; i<$scope.username.length; i++){                    
                        if(allowedChar.indexOf($scope.username[i]) < 0){
                            $scope.usernameValid = false;
                            $scope.usernameMessage = "invalid only letters, number and - _ ."; ; 
                            break;
                        }
                    }
                    if($scope.usernameValid){
                        var formAction = document.forms["pp_user_api_get_username_exist_form"].action;                
                        $http.get(formAction+"?username="+$scope.username).
                            then(function(response) {                                    
                                if(response.data.exist){
                                    $scope.usernameValid = false;
                                    console.log("name already used");
                                    $scope.usernameMessage = "already used"; 
                                }else{
                                    $scope.usernameValid = true;
                                    $scope.usernameMessage = "OK";
                                    console.log("name Ok");
                                }
                            }, function(response) {
                                $scope.usernameValid = false;
                                console.log("Request failed : "+response.statusText );                        
                            }
                        );
                    }else{                        
                        console.log($scope.usernameMessage);
                    }
                }                 
            }
        }
        
        this.checkForm = function($event){
            if($scope.usernameValid && $scope.emailValid ){
                
                $scope.passwordValid = false;
                $scope.passwordMessage = "true";
                if($scope.firstPassword.length < 3){
                    $scope.passwordValid = false;
                    $scope.passwordMessage = "to short";
                    $event.preventDefault();
                }
                else if($scope.firstPassword == $scope.secondPassword){
                    
                }else{
                    $scope.passwordValid = false;
                    $scope.passwordMessage = "not match";
                    $event.preventDefault();
                }
            }else{
                $event.preventDefault();
            }
        }
        
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
                    }, 300);
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
                        setTimeout(function () {                        
                            $("#signUpContent").fadeIn();
                        }, 300);
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
                }, 300);
               
            });            
        };
            
    }]);
        