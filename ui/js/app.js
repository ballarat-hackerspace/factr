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

// animation states
window['radar'] = {
  show_sweep: true,
  markers: []
};

angular.module('factr', ['toggle-switch'])
  .controller('AppCtrl', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {
    var url = 'http://52.62.213.165/factr/api/'; //services/categories

    $scope.categories = [];
    //$scope.fact = "right next to you is the highest fine rate carparking spot in the area";

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

    $scope.sayFact = function(message) {
      var msg = new SpeechSynthesisUtterance();
      var voices = window.speechSynthesis.getVoices();
      msg.voice = voices[10]; // Note: some voices don't support altering params
      msg.voiceURI = 'native';
      msg.volume = 1; // 0 to 1
      msg.rate = 1;   // 0.1 to 10
      msg.pitch = 1;  // 0 to 2
      msg.text = message;
      msg.lang = 'en-US';

      msg.onend = function(e) {
          console.log('Finished in ' + event.elapsedTime + ' seconds.');
      };

      speechSynthesis.speak(msg);
    };


    $scope.nextFact = function() {
      console.debug("Collecting position ...");
      window.radar.show_sweep = true;

      navigator.geolocation.getCurrentPosition(function(position) {
        // set default state to on
        var categories = [];

        angular.forEach($scope.categories, function(v) {
          if (v.state) {
            categories.push(v.id);
          }
        });

        var config = {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        };

        var max_results = Math.round(Math.random() * 5) + 3;

        var q = {
          categories:  categories,
          lat:         position.coords.latitude,
          lon:         position.coords.longitude,
          radius:      1000,
          max_results: max_results,
          time_period: 80
        };

        console.debug("Sending query: ", q);

        $http({
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          url:    url+'json/random',
          data: "request="+JSON.stringify(q)
        }).then(function(response) {
          window.radar.show_sweep = false;
          console.debug(response);

          $scope.facts = response.data.filter(function(v) { return Object.prototype.toString.call(v) === '[object Object]'; });
          console.debug($scope.facts);

          // build fake markers
          buildMarkers($scope.facts.length);
        },
        function(response) {
          console.error(response);
          window.radar.show_sweep = false;
        });
      },
      function(error) {
        console.error("Failed to get location: ", error);
        window.radar.show_sweep = false;
      });
    }
  }]);

var radar = document.getElementById('radar'),
    diameter = 300,
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

var markers = [];

var buildMarkers = function(size) {
  markers = [];

  for (var i=0; i<size; i++) {
    var rad = dToR(Math.random() * 360);
    var dist = Math.random() * radius;

    var x = Math.cos(rad) * dist;
    var y = Math.sin(rad) * dist;

    markers.push({x: x, y: y});
  }
}

var sweepAngle = 270,
    sweepSize = 2,
    sweepSpeed = 1.4,
    rings = 3,
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

  if (window.radar.show_sweep) {
    renderSweep();
  }
};


