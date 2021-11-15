app.controller('Ctrl1', function($scope) {
  $scope.roles = [
    'guest', 
    'user', 
    'customer', 
    'admin'
  ];
  $scope.user = {
    roles: ['user']
  };
  $scope.checkAll = function() {
    $scope.user.roles = angular.copy($scope.roles);
  };
  $scope.uncheckAll = function() {
    $scope.user.roles = [];
  };
  $scope.checkFirst = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length); 
    $scope.user.roles.push('guest');
  };
});