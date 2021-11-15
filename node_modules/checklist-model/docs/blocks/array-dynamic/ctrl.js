app.controller('Ctrl9', function($scope) {
  $scope.roles = [
    {id: 1, text: 'guest'},
    {id: 2, text: 'user'},
    {id: 3, text: 'customer'},
    {id: 4, text: 'admin'},
  ];
  $scope.user = {
    roles: ['user']
  };
  $scope.checkAll = function() {
    $scope.user.roles = $scope.roles.map(function(item) { return item.text; });
  };
  $scope.uncheckAll = function() {
    $scope.user.roles = [];
  };
  $scope.checkFirst = function() {
    $scope.user.roles.splice(0, $scope.user.roles.length); 
    $scope.user.roles.push($scope.roles[0].text);
  };
});