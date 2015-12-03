    
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
                                showBannerAlert("success", "Well done !", "Category added");
                            },function(response) {                            
                                $scope.newCategory.name = null;                                
                                if(response.status == 409){
                                    showBannerAlert("danger", "Error 409 ! ", "Category already exist");                               
                                }else {
                                    showBannerAlert("danger", "Error "+response.status+" !", "");
                                }                            
                            }
                    );
                }
            }else showBannerAlert("warning", "Warning", "You must enter some value");;
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
                        showBannerAlert("success", "Well done !", "Category Modified");
                    },function(response) {                            
                        if(response.status == 409){
                            showBannerAlert("danger", "Error 409 ! ", "Category already exist");  
                        }else showBannerAlert("danger", "Error "+response.status+" !", "");
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
                        showBannerAlert("success", "Well done !", "Category deleted ");                        
                    },function(response) {
                        showBannerAlert("danger", "Error "+response.status+" !", "");
                    }
                );
           }else showBannerAlert("warning", "Warning", "Text entered not match with category's name");          
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
                        showBannerAlert("success", "Well done !", "Tag deleted ");
                    },function(response) {
                        showBannerAlert("danger", "Error "+response.status+" !", "");
                    }
                );
           }else showBannerAlert("warning", "Warning", "Text entered not match with tag's name");          
       }
        
    }]);
    
    
