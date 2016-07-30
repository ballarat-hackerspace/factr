var doc = window.document;
var beforeopenEvent = false;
var openEvent = false;
var beforecloseEvent = false;
var closeEvent = false;
var slideout = new Slideout({
  panel: doc.getElementById('panel'),
  menu: doc.getElementById('menu'),
  padding: 300
});

angular.module('factr', ['toggle-switch'])
  .controller('AppCtrl', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {
    var url = 'http://52.62.213.165/factr/api/'; //services/categories

    $scope.categories = [];
    $scope.fact = "right next to you is the highest fine rate carparking spot in the area";

    // initialise categories
    $http.get(url+'services/categories').then(function(response) {
      console.debug(response);
      $scope.categories = response.data;

      // set default state to on
      angular.forEach($scope.categories, function(v) {
        v['state'] = true;
      });
    },
    function(response) {
      console.error(response);
    });

    $scope.nextFact = function() {
      console.debug("Collecting position ...");
      navigator.geolocation.getCurrentPosition(function(position) {
        // set default state to on
        var categories = [];
        angular.forEach($scope.categories, function(v) {
          if (v.state) {
            categories.push(v.id);
          }
        });

        var q = {
          categories: categories,
          lat: position.coords.latitude,
          lon: position.coords.longitude,
          radius: 1000,
          max_results: 10,
          time_period: 80
        }

        console.debug("Sending query: ", q);
        $http.post(url+'json/random').then(function(response) {
          console.debug(response);
        },
        function(response) {
          console.error(response);
        });
      },
      function(error) {
        console.error("Failed to get location: ", error);
      });
    };
  }]);



var radar = document.getElementById('radar'),
    diameter = 320,
    radius = diameter / 2,
    padding = 14;

var ctx = Sketch.create({
  container: radar,
  fullscreen: false,
  width: diameter,
  height: diameter
});

var dToR = function(degrees) {
  return degrees * (Math.PI / 180);
};

var facts = [
  {
    "fact": "right next to you is the highest fine rate carparking spot in the area",
    "category": "Carpark",
    "long": 141.12313131,
    "lat": -37.12313131,
    "quip": "doesn't that make you think?",
    "data_attributes": {
      "fine": "2100",
      "date": "30/7/2016",
    }
  },
  {
    "fact": "The lamp next to you has an 100 Watt globe",
    "category": "Lights",
    "long": 141.22313131,
    "lat": -37.22313131,
    "quip": "well I'll be!",
    "sound": "http://somewhere.com/playme1.wav",
    "data_attributes": {
      "watts": "2100",
      "serviced": "30/7/2016",
    }
  }
];

var markers = [
  { x: -100, y: 80},
  { x: 100, y: -50},
  { x: 60, y: 20},
];

var buildFactMarkers = function(facts) {
};

var sweepAngle = 270,
    sweepSize = 2,
    sweepSpeed = 1.2,
    rings = 2,
    hueStart = 120,
    hueEnd = 170,
    hueDiff = Math.abs(hueEnd - hueStart),
    saturation = 50,
    lightness = 40,
    lineWidth = 2,
    gradient = ctx.createLinearGradient(radius, 0, 0, 0);

gradient.addColorStop(0, 'hsla(' + hueStart + ', ' + saturation + '%, ' + lightness + '%, 1)');
gradient.addColorStop(1, 'hsla(' + hueEnd + ', ' + saturation + '%, ' + lightness + '%, 0.1)');

var renderRings = function() {
  for(var i=0; i<rings; i++) {
    ctx.beginPath();
    ctx.arc(radius, radius, ((radius - (lineWidth / 2)) / rings) * (i + 1), 0, TWO_PI, false);
    ctx.strokeStyle = 'hsla(' + (hueEnd - (i * (hueDiff / rings))) + ', ' + saturation + '%, ' + lightness + '%, 0.1)';
    ctx.lineWidth = lineWidth;
    ctx.stroke();
  };
};

var renderMarkers = function() {
  for(var i=0, l=markers.length; i<l; i++) {
    ctx.beginPath();
    ctx.arc(radius+markers[i].x, radius+markers[i].y, 4, 0, TWO_PI, false);
    ctx.fillStyle = '#660000';
    ctx.fill();
    ctx.strokeStyle = '#440000';
    ctx.lineWidth = 1;
    ctx.stroke();
  };
};

var renderSweep = function() {
  ctx.save();
  ctx.translate(radius, radius);
  ctx.rotate(dToR(sweepAngle));
  ctx.beginPath();
  ctx.moveTo(0, 0);
  ctx.arc(0, 0, radius, dToR(-sweepSize), dToR(sweepSize), false);
  ctx.closePath();
  ctx.fillStyle = gradient;
  ctx.fill();
  ctx.restore();
};

ctx.clear = function() {
  ctx.globalCompositeOperation = 'destination-out';
  ctx.fillStyle = 'hsla(0, 0%, 0%, 0.1)';
  ctx.fillRect(0, 0, diameter, diameter);
};

ctx.update = function(){
  sweepAngle += sweepSpeed;
};

ctx.draw = function(){
  ctx.globalCompositeOperation = 'lighter';

  renderRings();
  renderMarkers();
  renderSweep();
};
