

var clusterMaker = {
    data: getterSetter([], function(arrayOfArrays) {
        var n = arrayOfArrays[0].length;
        return (arrayOfArrays.map(function(array) {
            return array.length == n;
        }).reduce(function(boolA, boolB) { return (boolA & boolB) }, true));
    }),

    clusters: function() {
        var pointsAndCentroids = kmeans(this.data(), {k: this.k(), iterations: this.iterations() });
        var points = pointsAndCentroids.points;
        var centroids = pointsAndCentroids.centroids;

        return centroids.map(function(centroid) {
            return {
                centroid: centroid.location(),
                points: points.filter(function(point) { return point.label() == centroid.label() }).map(function(point) { return point.location() }),
            };
        });
    },
    k: getterSetter(undefined, function(value) { return ((value % 1 == 0) & (value > 0)) }),

    iterations: getterSetter(Math.pow(10, 3), function(value) { return ((value % 1 == 0) & (value > 0)) }),

};

/*if (typeof module !== 'undefined' && typeof module.exports !== 'undefined')
    module.exports = ClusterMaker;
else
    window.ClusterMaker = ClusterMaker;*/


function kmeans(data, config) {
    // default k
    var k = config.k || Math.round(Math.sqrt(data.length / 2));
    var iterations = config.iterations;

    // initialize point objects with data
    var points = data.map(function(vector) { return new Point(vector) });

    // intialize centroids randomly
    var centroids = [];
    for (var i = 0; i < k; i++) {
        centroids.push(new Centroid(points[i % points.length].location(), i));
    };

    // update labels and centroid locations until convergence
    for (var iter = 0; iter < iterations; iter++) {
        points.forEach(function(point) { point.updateLabel(centroids) });
        centroids.forEach(function(centroid) { centroid.updateLocation(points) });
    };

    // return points and centroids
    return {
        points: points,
        centroids: centroids
    };

};

// objects
function Point(location) {
    var self = this;
    this.location = getterSetter(location);
    this.label = getterSetter();
    this.updateLabel = function(centroids) {
        var distancesSquared = centroids.map(function(centroid) {
            return sumOfSquareDiffs(self.location(), centroid.location());
        });
        self.label(mindex(distancesSquared));
    };
};

function Centroid(initialLocation, label) {
    var self = this;
    this.location = getterSetter(initialLocation);
    this.label = getterSetter(label);
    this.updateLocation = function(points) {
        var pointsWithThisCentroid = points.filter(function(point) { return point.label() == self.label() });
        if (pointsWithThisCentroid.length > 0) self.location(averageLocation(pointsWithThisCentroid));
    };
};

// convenience functions
function getterSetter(initialValue, validator) {
    var thingToGetSet = initialValue;
    var isValid = validator || function(val) { return true };
    return function(newValue) {
        if (typeof newValue === 'undefined') return thingToGetSet;
        if (isValid(newValue)) thingToGetSet = newValue;
    };
};

function sumOfSquareDiffs(oneVector, anotherVector) {
    var squareDiffs = oneVector.map(function(component, i) {
        return Math.pow(component - anotherVector[i], 2);
    });
    return squareDiffs.reduce(function(a, b) { return a + b }, 0);
};

function mindex(array) {
    var min = array.reduce(function(a, b) {
        return Math.min(a, b);
    });
    return array.indexOf(min);
};

function sumVectors(a, b) {
    return a.map(function(val, i) { return val + b[i] });
};

function averageLocation(points) {
    var zeroVector = points[0].location().map(function() { return 0 });
    var locations = points.map(function(point) { return point.location() });
    var vectorSum = locations.reduce(function(a, b) { return sumVectors(a, b) }, zeroVector);
    return vectorSum.map(function(val) { return val / points.length });
};

function dist(coor1, coor2){
    return Math.sqrt(Math.pow(coor1[0]-coor2[0],2) + Math.pow(coor1[1]-coor2[1], 2));
}
function getMinimumIndex(arr){

    var minimum = Infinity;
    var minIndex = -1;

    for(var i=0; i<arr.length; i++){
        if(arr[i] < minimum) {
            minIndex = i;
            minimum = arr[i];
        }
    }
    return minIndex;
}

function getReducedFeatures(features, centroids){

    var numCluster = centroids.length;

    var clusters = Array(numCluster);
    for(var i=0; i<numCluster; i++){
        clusters[i] = Array()
    }

    features.forEach(feature => {

        var distances = [];
        centroids.forEach(centroid => {
            distances.push(dist(centroid, feature.geometry.coordinates));
        });

    clusters[getMinimumIndex(distances)].push(feature);
    });


    var newGeoJson = {type: 'FeatureCollection', features: []};
    clusters.forEach(cluster => {

        if(cluster.length == 0) return;
        //
        for(var j=1; j<cluster.length; j++){
            Object.keys(cluster[j]["properties"]).forEach(key => {
                cluster[0]["properties"][key] += cluster[j]["properties"][key];
            });
        }
        Object.keys(cluster[0]["properties"]).forEach(key => {
            cluster[0]["properties"][key] /= cluster.length;
        });
        newGeoJson.features.push(cluster[0]);
    });
    return newGeoJson;
}
/*
var response = '{\
    "status":true,\
    "code":100,\
    "message":{\
        "type":"FeatureCollection",\
        "features":\
        [\
            {"type":"Feature", "properties":{"co":0.532358},"geometry":{"type":"Point","coordinates":[-118.51936538768,34.692473219462]}},\
            {"type":"Feature","properties":{"co":0.902054},"geometry":{"type":"Point","coordinates":[-117.82930949728,34.264073760913]}},\
            {"type":"Feature","properties":{"co":0.321428},"geometry":{"type":"Point","coordinates":[-115.7545877477,34.417883904539]}},\
            {"type":"Feature","properties":{"co":0.424341},"geometry":{"type":"Point","coordinates":[-117.54537415573,34.933336118251]}},\
            {"type":"Feature","properties":{"co":0.365738},"geometry":{"type":"Point","coordinates":[-117.41274620186,34.170595128099]}},\
            {"type":"Feature","properties":{"co":0.0100625},"geometry":{"type":"Point","coordinates":[-115.81980360521,34.240009868872]}}\
        ]\
    }\
}';


var features = JSON.parse(response).message.features;

var coordinates = [];
features.forEach(feature => {
    coordinates.push(feature.geometry.coordinates);
});

clusterMaker.iterations(1000);
clusterMaker.data(coordinates);


var centroids = [];
clusterMaker.clusters().forEach(result => {
    centroids.push(result.centroid);
});

var reduced = getReducedFeatures(features, centroids);

console.log(JSON.stringify(reduced));*/

