app.controller("myNotesCtrl",function($scope,$http){
	$scope.note = "";
	$scope.getLeft = function() {
		return 100 - $scope.note.length;
	};
	$scope.save = function() {
		window.alert('Saved!');
	};
	$scope.clear = function() {
		$scope.note = "";
	};
});