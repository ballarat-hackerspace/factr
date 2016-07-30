var doc = window.document;
var beforeopenEvent = false;
var openEvent = false;
var beforecloseEvent = false;
var closeEvent = false;
var slideout = new Slideout({
  'panel': doc.getElementById('panel'),
  'menu': doc.getElementById('menu')
});

angular.module('factr', ['toggle-switch'])
  .controller('AppCtrl', ['$scope', '$timeout', function($scope, $timeout) {
/* No Categories = all of them. */
    $scope.categories = [
      {"name": "Lights", "value": 0.2},
      {"name": "Dogs",   "value": 1.0},
      {"name": "Crime",  "value": 0.8},
    ];

    $scope.fact = "right next to you is the highest fine rate carparking spot in the area";
  }]);
