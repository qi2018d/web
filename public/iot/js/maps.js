var markers = [];
var infoWindows = [];

var mapClusterData;

function clearFeatures(){
    map.data.forEach(function(feature){
        map.data.remove(feature);
    });
}

function getCircle(zoom, concentration) {
    return {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: 'red',
        fillOpacity: .2,
        scale: Math.pow(zoom, 0.35) * concentration * 10,
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
        url: "http://teamd-iot.calit2.net/api/data/read/maps/geojson",
        data: JSON.stringify(viewport),
        contentType: "application/json",
        success: function(results) {
            var geojson = results.message;
            var features = results.message.features;

            if(typeof mapClusterData != 'undefined')
                mapClusterData.setMap(null);

            for(var index in infoWindows){
                infoWindows[index].close();
            }

            clearMarkers(markers);
            markers = [];
            infoWindows = [];

            if(features.length == 0)
                return;

            var zoom = map.getZoom();
            // if user zoom in, display markers
            if (zoom >= 13) {
                var data = new google.maps.Data();

                data.addGeoJson(geojson);
                data.forEach(function (feature) {

                    feature.getGeometry().forEachLatLng(function (latLng) {
                        markers.push(new google.maps.Marker({ position: latLng, map: map }));
                    });

                    infoWindows.push(new google.maps.InfoWindow({ content: setContentString(feature), maxWidth: 300 }));
                });

                markers.forEach(function(marker, index){
                    marker.addListener('click', function() {
                        infoWindows[index].open(map, marker);
                    });
                });
                return;

            } else {
                // else map does marker clustering
                var coordinates = [];
                mapClusterData = new google.maps.Data();

                features.forEach(feature => {
                    coordinates.push(feature.geometry.coordinates);
                });


                clusterMaker.k(6);
                clusterMaker.iterations(2500);
                clusterMaker.data(coordinates);

                var centroids = [];
                clusterMaker.clusters().forEach(result => {
                    centroids.push(result.centroid);
                });

                var reduced = getReducedFeatures(features, centroids);

                mapClusterData.addGeoJson(reduced);
                mapClusterData.setStyle(function(feature) {
                    var concentration = feature.getProperty('radius');
                    return {
                        title: feature.getProperty('radius'),
                        icon: getCircle(zoom, concentration)
                    };
                });


                google.maps.event.addListener(mapClusterData, 'click', function(event){
                    event.feature.getGeometry().forEachLatLng(function(latLng){
                        var index = infoWindows.push(new google.maps.InfoWindow({ content: setContentString(event.feature), maxWidth: 300, position: latLng })) - 1;
                        infoWindows[index].open(map);
                    });
                });

                mapClusterData.setMap(map);
            }

        }
    });
}


function setContentString(feature){
    var contentString =
        '<h2>CO</h2>' +
        '<div class="progress" style="width: 300px">'+
            '<div class="progress-bar progress-bar-good" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="500" style="width: ' + feature.getProperty('co') * 60 + '%">'+
                '<span class="sr-only">' + feature.getProperty('co') * 60 + '</span>'+
            '</div>'+
        '</div>'+
        '<h2>NO2</h2>' +
        '<div class="progress" style="width: 300px">'+
            '<div class="progress-bar progress-bar-moderate" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="500" style="width: ' + feature.getProperty('no2') * 60 + '%">'+
        '<span class="sr-only">' + feature.getProperty('no2') * 60 + '</span>'+
            '</div>'+
        '</div>'+
        '<h2>SO2</h2>' +
        '<div class="progress" style="width: 300px">'+
            '<div class="progress-bar progress-bar-unhealthy-s" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="500" style="width: ' + feature.getProperty('so2') * 60 + '%">'+
                '<span class="sr-only">' + feature.getProperty('so2') * 60 + '</span>'+
            '</div>'+
        '</div>'+
        '<h2>PM2.5</h2>' +
        '<div class="progress" style="width: 300px">'+
            '<div class="progress-bar progress-bar-unhealthy-v" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="500" style="width: ' + feature.getProperty('pm2_5') * 60 + '%">'+
                '<span class="sr-only">' + feature.getProperty('pm2_5') * 60 + '</span>'+
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
    else if (value > 101 && value <= 150){
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