app.controller('Ctrl7', function($scope) {
  $scope.roles = [
    {id: 1, text: 'guest'}
  ];
  $scope.user = {
    roles: [$scope.roles[0]]
  };
  $scope.checkAll = function() {
    $scope.user.roles = angular.copy($scope.roles);
  };
  $scope.uncheckAll = function() {
    $scope.user.roles = [];
  };
  $scope.checkFirst = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length); 
    $scope.user.roles.push($scope.roles[0]);
  };
});