app.controller('Ctrl8', function($scope) {
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
  $scope.getRoles = function() {
    return $scope.user.roles;
  };
  $scope.check = function(value, checked) {
    var idx = $scope.user.roles.indexOf(value);
    if (idx >= 0 && !checked) {
      $scope.user.roles.splice(idx, 1);
    }
    if (idx < 0 && checked) {
      $scope.user.roles.push(value);
    }
  };
});