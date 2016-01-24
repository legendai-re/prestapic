    
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
        $scope.passwordOneMessage = '';
        $scope.passwordTwoMessage = '';
        
        var timeoutUsername = null;
        var timeoutEmail = null;
       
        
        this.usernameTyping = function(){            
            $scope.usernameValid = false;
            if (timeoutUsername !== null) {                
                clearTimeout(timeoutUsername);
            }            
            timeoutUsername = setTimeout(function() {
                resetUsernameMessage();
                checkUsername();
            }, 1000);
        };
        
        this.emailTyping = function(){            
            $scope.emailValid = false;
            if (timeoutEmail !== null) {                
                clearTimeout(timeoutEmail);
            }            
            timeoutEmail = setTimeout(function() {
                resetEmailMessage();
                checkEmail();
            }, 1000);
        };
        
        function resetUsernameMessage(){
            $("#signup_username_error").removeClass("success");
            $("#signup_username_error").removeClass("danger");
        }
        
        function resetEmailMessage(){
            $("#signup_email_error").removeClass("success");
            $("#signup_email_error").removeClass("danger");
        }
        
        function checkEmail() {            
            $scope.emailValid = false;
            $scope.emailMessage = '';
            var re = /^[a-zA-Z0-9.\-_]+@[a-zA-Z0-9.\-_]+\.[a-z\.]{2,6}$/;
            if($scope.email != null && $scope.email != ''){
                if(re.test($scope.email)){
                    var formAction = document.forms["pp_user_api_get_email_exist_form"].action;                
                    $http.get(formAction+"?email="+$scope.email).
                        then(function(response) {                                    
                            if(response.data.exist){
                                $scope.emailValid = false;                            
                                $scope.emailMessage = "This email is already registered"; 
                                $("#signup_email_error").addClass("danger");
                            }else{
                                $scope.emailValid = true;
                                $scope.emailMessage = "";
                                $("#signup_email_error").addClass("success");
                            }
                            if(!$scope.$$phase) {
                                $scope.$apply();
                            }
                        }, function(response) {
                            $scope.emailValid = false;
                            console.log("Request failed : "+response.statusText );                        
                        }
                    );
                }else{
                   $scope.emailMessage = 'Enter a valid email.';
                   $scope.emailValid = false;
                   $("#signup_email_error").addClass("danger");               
                }
            }
            if(!$scope.$$phase) {
                $scope.$apply();
            }
        }
        
        var checkUsername = function(){            
            $scope.usernameValid = false;
            $scope.usernameMessage = '';
            if($scope.username != null && $scope.username != ''){
                if($scope.username.length > 30){
                    $scope.usernameValid = false;
                    $scope.usernameMessage = "Username too long (max 30 characters).";
                    $("#signup_username_error").addClass("danger");
                }else if($scope.username.length < 2){
                    $scope.usernameValid = false;
                    $scope.usernameMessage = "Username too short (min 2 characters)."; 
                    $("#signup_username_error").addClass("danger");
                }else{
                    $scope.usernameValid = true;
                    for(var i=0; i<$scope.username.length; i++){                    
                        if(allowedChar.indexOf($scope.username[i]) < 0){
                            $scope.usernameValid = false;
                            $scope.usernameMessage = "Enter a valid username you can user letters, numbers, scores, underscores and dot.";
                            $("#signup_username_error").addClass("danger");
                            break;
                        }
                    }
                    if($scope.usernameValid){
                        var formAction = document.forms["pp_user_api_get_username_exist_form"].action;                
                        $http.get(formAction+"?username="+$scope.username).
                            then(function(response) {                                    
                                if(response.data.exist){
                                    $scope.usernameValid = false;                                    
                                    $scope.usernameMessage = "This username has already been taken."; 
                                    $("#signup_username_error").addClass("danger");
                                }else{
                                    $scope.usernameValid = true;
                                    $scope.usernameMessage = "";                                    
                                    $("#signup_username_error").addClass("success");
                                }
                                if(!$scope.$$phase) {
                                    $scope.$apply();
                                }
                            }, function(response) {
                                $scope.usernameValid = false;
                                console.log("Request failed : "+response.statusText );                        
                            }
                        );
                    }
                }                 
            }
            if(!$scope.$$phase) {
                $scope.$apply();
            }
        }
        
        this.checkForm = function($event){            
            
            $("#signup_password_one_error").removeClass("danger");
            $("#signup_password_two_error").removeClass("danger");
            $scope.passwordValid = false;
            $scope.passwordOneMessage = "";
            $scope.passwordTwoMessage = "";
            
            if($scope.firstPassword != null && $scope.firstPassword != ""){
                if($scope.firstPassword.length < 5){
                    $scope.passwordValid = false;
                    $scope.passwordOneMessage = "Password too short (min 5 characters).";
                    $event.preventDefault();
                    $("#signup_password_one_error").addClass("danger");
                }
                else if($scope.firstPassword == $scope.secondPassword){
                    $scope.passwordValid = true;
                }else{
                    $scope.passwordValid = false;
                    $scope.passwordTwoMessage = "Passwords do not match.";
                    $("#signup_password_two_error").addClass("danger");
                    $event.preventDefault();
                }
            }
            
            if($scope.usernameValid && $scope.emailValid && $scope.passwordValid){
                
                
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
        