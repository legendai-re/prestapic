    
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
        
        var formAction = document.forms["pp_dashboard_content_api_get_content_form"].action;                                
        $http.get(formAction).
            then(function(response){
                $rootScope.content = response.data;
            },function(response) {
                console.log(response);
                console.log("Request failed : "+response.statusText );                            
            }
        );

    }]);
    
    containerApp.controller('categoriesController', ['$scope', '$rootScope', '$http', '$compile', '$location', function ($scope, $rootScope, $http, $compile, $location) {
        
        $scope.newCategory = {
            name: null
        };
        
        this.postCategory = function(){            
            $scope.postCategoryError = null;
            if($scope.newCategory.name){
                if (confirm('Do you realy want to add category : '+$scope.newCategory.name+" ?")) {
                    $http({
                            method: 'POST',
                            url: $rootScope.content.postCategoryUrl,                    
                            data: JSON.stringify($scope.newCategory)
                             }).
                            then(function(response){
                                $rootScope.content.categories[response.data.id] = {id: response.data.id, name: $scope.newCategory.name};
                                $scope.newCategory.name = null;
                                $("#alert-banner").addClass("success");
                                $("#alert-banner-span").html("Category added !");                               
                            },function(response) {                            
                                $scope.newCategory.name = null;
                                $("#alert-banner").addClass("danger");
                                if(response.status == 409){
                                    $("#alert-banner-strong").html("Error 409 ");
                                    $("#alert-banner-span").html("Category already exist !");                                    
                                }else {
                                    $("#alert-banner-strong").html("Error ");
                                    $("#alert-banner-span").html("Server error !");
                                }                            
                            }
                    );
                }
            }else $scope.postCategoryError = "no value";
        };                
        
        this.patchCategory = function(id){
            var newName = prompt("Enter a new name", $rootScope.content.categories[id].name);
            if (newName != null) {
                $http({
                    method: 'PATCH',
                    url: $rootScope.content.patchCategoryUrl,                    
                    data: JSON.stringify({id: id, name: newName})
                     }).
                    then(function(response){
                        $rootScope.content.categories[id].name = newName;
                        $scope.postCategoryError = "Category Modified !";
                    },function(response) {                            
                        if(response.status == 409){
                            $scope.postCategoryError = "already exist";
                        }else $scope.postCategoryError = "server error"; 
                    }
                );
            }           
        }
        
        this.deleteCategory = function(id){
            var deleteName = prompt("Enter the category's name to delete it : ");
            if (deleteName == $rootScope.content.categories[id].name) {
                $http({
                    method: 'POST',
                    url: $rootScope.content.deleteCategoryUrl,                    
                    data: JSON.stringify({catId: id})
                     }).
                    then(function(response){
                        $rootScope.content.categories[id] = null;
                        $scope.postCategoryError = "Category deleted !";
                    },function(response) {                                                    
                    }
                );
           }else $scope.postCategoryError = "Do not match with category's name";          
       }
        
    }]);
    
    containerApp.controller('tagsController', ['$scope', '$rootScope', '$http', '$compile', '$location', function ($scope, $rootScope, $http, $compile, $location) {
        
        this.deleteTag = function(id){
            var deleteName = prompt("Enter the tag's name to delete it : ");
            if (deleteName == $rootScope.content.tags[id].name) {
                $http({
                    method: 'POST',
                    url: $rootScope.content.deleteTagUrl,                    
                    data: JSON.stringify({id: id})
                     }).
                    then(function(response){
                        $rootScope.content.tags[id] = null;
                        $scope.tagError = "Category deleted !";
                    },function(response) {                                                    
                    }
                );
           }else $scope.tagError = "Do not match with tag's name";          
       }
        
    }]);
    
    
