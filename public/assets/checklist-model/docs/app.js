var app = angular.module("app", ["checklist-model"]);

// links: http://plnkr.co/edit/3YNLsyoG4PIBW6Kj7dRK?p=preview
// links: http://stackoverflow.com/questions/14514461/how-can-angularjs-bind-to-list-of-checkbox-values

//not used now!
app.controller('Ctrl', function($scope) {
  $scope.addItem = function() {
    $scope.items.push({id: $scope.items.length, text: 'item '+$scope.items.length});
  };

  $scope.removeItem = function() {
    $scope.items.pop();
  };  

  $scope.changeItems = function() {
    //$scope.items[0].id = 123;
    $scope.items[0].text = 'item 123';
    $scope.items1[0] = 'item 123';
  };    

  $scope.reorder = function() {
    var t = $scope.items[2];
    $scope.items[2] = $scope.items[3];
    $scope.items[3] = t;
  };

  $scope.check = function() {
    $scope.user.values1 = [1,4];
  };   
});