app.controller('Ctrl5', function($scope) {
  $scope.roles = {
    a: 'Administrator',
    c: 'Customer',
    g: 'Guest',
    u: 'User'
  };

  $scope.testValue = 'Im not changed yet!';
  $scope.imChanged = function(){
    $scope.testValue = $scope.user.roles.join(',');
  }
  $scope.shouldChange = function(key){
console.log("should change " + key);
    return key !== "g";
  }

  $scope.user = {
    roles: ['c']
  };

  $scope.checkFirst = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length);
    $scope.user.roles.push('a');
  };

  $scope.uncheckAll = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length);
  };

  $scope.checkAll = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length);
    for (var r in $scope.roles) {
      $scope.user.roles.push(r);
    }
  };

});