var markers = [];
var infoWindows = [];
var markerIcons = [];

function clearFeatures(){
    map.data.forEach(function(feature){
        map.data.remove(feature);
    });
}

function getCircle(zoom, radius, aqi) {
    var weight;
    if (zoom >= 10){
        weight = 3;
    }
    else if (zoom >=7){
        weight = 1.5;
    }
    else {
        weight = 0.7;
    }


    return {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: colorSelector(aqi),
        fillOpacity: .5,
        scale: Math.pow(zoom, 0.35) * radius * weight,
        strokeColor: 'white',
        strokeWeight: .5
    };
}

function clearMarkers(markers){
    markers.forEach(function(marker){
        marker.setMap(null);
    });
}

function getAirData(viewport){
    $.ajax({
        type:"POST",
        dataType: "json",
        url: "http://teamd-iot.calit2.net/api/data/read/maps/geojson/now",
        data: JSON.stringify(viewport),
        contentType: "application/json",
        success: function(results) {
            if (results.status == false){
                alert('Error [' + results.code + ']: There\'s something wrong!');
                return;
            }

            var geojson = results.message;
            var features = results.message.features;

            // removes all circles on maps
            if(typeof mapClusterData != 'undefined')
                mapClusterData.setMap(null);

            // closes all InfoWindows on maps
            for(var index in infoWindows){
                infoWindows[index].close();
            }

            clearMarkers(markers);
            markers = [];
            infoWindows = [];

            if(features.length == 0)
                return;

            var zoom = map.getZoom();

            if (zoom >= 13) {
                // if user zoom in, display markers
                drawMarkersOnMaps(geojson);
            }
            else /*if (zoom >= 8)*/{
                // else map does marker clustering
                drawCentroidOnMaps(features, zoom);
            }
        }
    });
}

// draw markers on google maps
function drawMarkersOnMaps(geoJson){
    // set markerIcon's options


    var data = new google.maps.Data();

    // make markers with GeoJson features
    data.addGeoJson(geoJson);
    data.forEach(function (feature) {

        feature.getGeometry().forEachLatLng(function (latLng) {
            markers.push(new google.maps.Marker({ position: latLng, icon: markerIconSelector(getDominantAQI(feature)), map: map }));
        });

        infoWindows.push(new google.maps.InfoWindow({ content: setContentString(feature), maxWidth: 300 }));
    });

    // mapping markers with each InfoWindow
    markers.forEach(function(marker, index){
        marker.addListener('click', function() {
            infoWindows[index].open(map, marker);
        });
    });
}

// draw centroid on google maps calculated by k-means algorithm
function drawCentroidOnMaps(features, zoom){
    var coordinates = [];

    mapClusterData = new google.maps.Data();

    features.forEach(feature => {
        coordinates.push(feature.geometry.coordinates);
    });

    // make centroids with k-means algorithm
    clusterMaker.k(6);
    clusterMaker.iterations(2500);
    clusterMaker.data(coordinates);

    var centroids = [];
    clusterMaker.clusters().forEach(result => {
        centroids.push(result.centroid);
    });

    // store newly created centroid features
    var reduced = getReducedFeatures(features, centroids);
    var filtered = reduced.features.filter(function(element, index, self) {
        return index === self.indexOf(element);
    });
    reduced.features = filtered;

    // draw centroids on google maps
    mapClusterData.addGeoJson(reduced);
    mapClusterData.setStyle(function(feature) {
        var radius = feature.getProperty('radius');
        var dominantAQI = getDominantAQI(feature);
        if (feature.getProperty('data_len') == 1) {
            return {
                title: feature.getProperty('radius'),
                icon: markerIconSelector(dominantAQI)
            };
        }
        else return {
            title: feature.getProperty('radius'),
            icon: getCircle(zoom, radius, dominantAQI)
        };
    });

    /*mapClusterData.forEach(function (feature){
        if (feature.getProperty('data_len') == 1){
            feature.getGeometry().forEachLatLng(function (latLng) {
                markers.push(new google.maps.Marker({ position: latLng, icon: markerIconSelector(getDominantAQI(feature)), map: map }));
            });
            infoWindows.push(new google.maps.InfoWindow({ content: setContentString(feature), maxWidth: 300 }));
        }
    });*/
    // mapping markers with each InfoWindow
    markers.forEach(function(marker, index){
        marker.addListener('click', function() {
            infoWindows[index].open(map, marker);
        });
    });

    google.maps.event.addListener(mapClusterData, 'click', function(event){
        event.feature.getGeometry().forEachLatLng(function(latLng){
            var index = infoWindows.push(new google.maps.InfoWindow({ content: setContentString(event.feature), maxWidth: 300, position: latLng })) - 1;
            infoWindows[index].open(map);
        });
    });


    mapClusterData.setMap(map);
}

// additional functions
function setContentString(feature){
    var pm2_5 = feature.getProperty('pm2_5');
    var co = feature.getProperty('co');
    var no2 = feature.getProperty('no2');
    var so2 = feature.getProperty('so2');
    var o3 = feature.getProperty('o3');
    var timestamp = feature.getProperty('timestamp');

    // for centroid (does not have timestamp);
    if (typeof timestamp == 'number')
        timestamp = '';

    // body content string of InfoWindow
    var contentString =
        '<h1 style="font-size: 1.5em; padding-bottom: 10px"> ' +  timestamp + '</h1>' +
        '<h2>O3</h2>' +
        '<div class="progress" style="width: 300px">'+
        '<div class="progress-bar ' + progressBarSelector(o3) + '" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="500" style="width: ' + o3 / 5 + '%">'+
        Math.floor(o3) +
        '</div>'+
        '</div>'+
        '<h2>CO</h2>' +
        '<div class="progress" style="width: 300px">'+
        '<div class="progress-bar ' + progressBarSelector(co) + '" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="500" style="width: ' + co / 5 + '%">'+
        Math.floor(co) +
        '</div>'+
        '</div>'+
        '<h2>NO2</h2>' +
        '<div class="progress" style="width: 300px">'+
        '<div class="progress-bar ' + progressBarSelector(no2) + '" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="500" style="width: ' + no2 / 5 + '%">'+
        Math.floor(no2) +
        '</div>'+
        '</div>'+
        '<h2>SO2</h2>' +
        '<div class="progress" style="width: 300px">'+
        '<div class="progress-bar ' + progressBarSelector(so2) + '" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="500" style="width: ' + so2 / 5 + '%">'+
        Math.floor(so2) +
        '</div>'+
        '</div>' +
        '<h2>PM2.5</h2>' +
        '<div class="progress" style="width: 300px">'+
        '<div class="progress-bar ' + progressBarSelector(pm2_5) + '" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="500" style="width: ' + pm2_5 / 5 + '%">'+
        Math.floor(pm2_5) +
        '</div>'+
        '</div>';


    return contentString;
}

function progressBarSelector(value){
    if (value >= 0 && value <= 50){
        return 'progress-bar-good';
    }
    else if (value > 50 && value <= 100){
        return 'progress-bar-moderate';
    }
    else if (value > 100 && value <= 150){
        return 'progress-bar-unhealthy-s';
    }
    else if (value > 150 && value <= 200){
        return 'progress-bar-unhealthy';
    }
    else if (value > 200 && value <= 300){
        return 'progress-bar-unhealthy-v';
    }
    else if (value > 300 && value <= 500){
        return 'progress-bar-hazardous';
    }
    else {
        return 'progress-bar-hazardous';
    }
}

function colorSelector(value){
    if (value >= 0 && value <= 50){
        return '#38EA34';
    }
    else if (value > 50 && value <= 100){
        return '#FFFA4D';
    }
    else if (value > 101 && value <= 150){
        return '#FFAC56';
    }
    else if (value > 150 && value <= 200){
        return '#FF5A41';
    }
    else if (value > 200 && value <= 300){
        return '#991C59';
    }
    else if (value > 300 && value <= 500){
        return '#63171D';
    }
    else {
        return '#63171D';
    }
}

function markerIconSelector(value){
    if (value >= 0 && value <= 50){
        return markerIcons[0];
    }
    else if (value > 50 && value <= 100){
        return markerIcons[1];
    }
    else if (value > 101 && value <= 150){
        return markerIcons[2];
    }
    else if (value > 150 && value <= 200){
        return markerIcons[3];
    }
    else if (value > 200 && value <= 300){
        return markerIcons[4];
    }
    else if (value > 300 && value <= 500){
        return markerIcons[5];
    }
    else {
        return markerIcons[5];
    }
}

function getDominantAQI(feature){
    var dominantAQI = -1;
    var list = ['co', 'no2', 'so2', 'pm2_5', 'o3'];

    list.forEach(function(sensor){
        var value = feature.getProperty(sensor);
        if (dominantAQI < value)
            dominantAQI = value;
    });

    return dominantAQI;
}