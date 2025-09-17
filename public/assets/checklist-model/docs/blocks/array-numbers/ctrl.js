app.controller('NumbersCtrl', function($scope) {
  $scope.numbers = [
    1,
    2,
    3,
    4
  ];
  $scope.user = {
    numbers: [2]
  };

  $scope.checkAll = function() {
    $scope.user.numbers = angular.copy($scope.numbers);
  };
  $scope.uncheckAll = function() {
    $scope.user.numbers = [];
  };
  $scope.checkFirst = function() {
    $scope.user.numbers.splice(0, $scope.user.numbers.length);
    $scope.user.numbers.push(1);
  };
});