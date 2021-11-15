app.controller('Ctrl6a', function($scope) {
    $scope.users = [
        {id: 1, name: 'Aaron'},
        {id: 2, name: 'David'},
        {id: 2, name: 'Moses'}
    ];

    $scope.selectedUsers = [];

    $scope.checkFirst = function() {
        $scope.selectedUsers.splice(0, $scope.selectedUsers.length, $scope.users[0]);
    };

    $scope.checkAll = function() {
        $scope.selectedUsers.splice(0, $scope.selectedUsers.length);
        for (var i in $scope.users) {
             $scope.selectedUsers.push($scope.users[i]);
        }
    };

    $scope.uncheckAll = function() {
        $scope.selectedUsers.splice(0, $scope.selectedUsers.length);
    }
});