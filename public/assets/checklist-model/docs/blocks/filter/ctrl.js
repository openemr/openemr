app.controller('Ctrl6', function($scope) {
    $scope.users = [
        {id: 1, name: 'Aaron'},
        {id: 2, name: 'David'},
        {id: 3, name: 'Moses'}
    ];

    $scope.selectedUsers = [];

    $scope.compareFn = function(obj1, obj2){
        return obj1.id === obj2.id;
    };

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