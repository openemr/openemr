app.controller('Ctrl10', function($scope) {
  $scope.roles = [
    {id: 1, text: 'guest', Text: 'Guest'},
    {id: 2, text: 'user', Text: 'User'},
    {id: 3, text: 'customer', Text: 'Customer'},
    {id: 4, text: 'admin', Text: 'Admin'},
  ];
  $scope.user = {
    roles: ['user']
  };
  $scope.dynValue = "role.text";
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
  $scope.setChecklistValue = function() {
	  angular.element("[ng-controller='Ctrl10'] INPUT[type='checkbox']").attr("checklist-value", $scope.dynValue);
  };
});