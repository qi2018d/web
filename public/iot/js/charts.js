// Global Variables for air-quality chart
var airLineChart;
var airCategoryFilter;
var filteredColumnIndices = [0];
var airRangeFilter;

// Array dataset for air-quality chart
var air_chart_records = [[{label: 'Time', type: 'date'},
    {label: 'CO', type: 'number'},
    {label: 'NO2', type: 'number'},
    {label: 'SO2', type: 'number'},
    {label: 'O3', type: 'number'},
    {label: 'PM2.5', type: 'number'}]];

// Global Variables for heart-related chart
var heartDashboard;
var heartLineChart;
var heartRangeFilter;
// Array dataset for heart-related chart
var heart_chart_records = [[{label: 'Time', type: 'date'},
    {label: 'Heartbeats', type: 'number'}]];


getOneDayAirData();
getOneDayHeartData();

google.charts.load('current', {'packages':['line', 'controls']});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(function(){
    drawAirChart();
    drawHeartChart();
});

function drawAirChart() {
    var data = google.visualization.arrayToDataTable(air_chart_records);

    airCategoryFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'air_category_div',
        'dataTable': air_chart_records,
        'options': {
            'filterColumnIndex': 1,
            'values': ['CO', 'NO2', 'SO2', 'O3', 'PM2.5'],
            'ui': {
                'labelStacking': 'vertical',
                'label': 'Sensor Selection',
                'allowTyping': false,
                'allowMultiple': false
            }
        }
    });
    // Create a range slider, passing some options
    airRangeFilter = new google.visualization.ControlWrapper({
        'controlType': 'ChartRangeFilter',
        'containerId': 'air_range_div',
        'dataTable': air_chart_records,
        'options': {
            'filterColumnIndex': 1,
            'ui': {
                'chartType': 'LineChart',
                'chartOptions': {
                    'chartArea': {'height': '100', 'width': '100%'},
                    'hAxis': {
                        'textPosition': 'out',
                        'gridlines': {
                            'count': 10,
                            'units': {
                                'days': {'format': ['MMM d']},
                                'hours': {'format': ['HH:mm', 'ha']}
                            }
                        },
                        'minorGridlines': {
                            'units': {
                                'hours': {'format': ['hh:mm:ss', 'ha']},
                                'minutes': {'format': ['HH:mm', ':mm']}
                            }
                        }
                    }
                }
            }
        }
    });

    airLineChart = new google.visualization.ChartWrapper({
        'chartType': 'LineChart',
        'containerId': 'air_chart_div',
        'dataTable': air_chart_records,
        'options': {
            'width': '100%',
            'height': 600,
            'title': 'Historical Air Quality Data',
            'titleTextStyle': {
                'color': '#000000',
                'fontName': 'Roboto',
                'fontSize': 20
            },
            'subtitle': 'in Air Quality Index (AQI)',
            'hAxis': {
                'title': 'Time',
                'titleTextStyle': {
                    'color': '#000000',
                    'fontName': 'Roboto',
                    'fontSize': 20
                },
                'textPosition': 'out',
                'gridlines': {
                    'count': -1,
                    'color': '#000000',
                    'units': {
                        'minutes': {'format': ['HH:mm:ss']},
                        'hours': {'format': ['M/d h a']},
                        'days': {'format': ['MMM d, yyyy']}
                    }
                },
                'minorGridlines': {
                    'units': {
                        'count': -1,
                        'minutes': {'format': ['H:mm']},
                        'hours': {'format': ['h a']},
                        'days': {'format': ['M/d']}
                    }
                }
            },
            'vAxis': {
                'title': 'Air Quality Index',
                'titleTextStyle': {
                    'color': '#000000',
                    'fontName': 'Roboto',
                    'fontSize': 20
                },
                'textPosition': 'out',
                'minValue': '0'
            }
        }
    });


    google.visualization.events.addListener(airCategoryFilter, 'statechange', function () {
        var selectedSensor = airCategoryFilter.getState().selectedValues[0];
        if (typeof selectedSensor != 'undefined') {
            filteredColumnIndices = [0];
            //Finds which column to show
            for (x = 0; x < data.getNumberOfColumns(); x++) {
                if (data.getColumnLabel(x) == selectedSensor) {
                    filteredColumnIndices[1] = x;
                    break;
                }
            }
            //Changing color to "red" to maintain the color of the second column, even if it's shown alone.
            if (x == 1) {

                airLineChart.setOption('colors', ['#3366cc']);
                airRangeFilter.setOption('ui.chartOptions.colors', ['#3366cc']);
            } else if (x == 2) {

                airLineChart.setOption('colors', ['#dc3912']);
                airRangeFilter.setOption('ui.chartOptions.colors', ['#dc3912']);
            } else if (x == 3) {

                airLineChart.setOption('colors', ['#ff9900']);
                airRangeFilter.setOption('ui.chartOptions.colors', ['#ff9900']);
            } else if (x == 4) {

                airLineChart.setOption('colors', ['#109618']);
                airRangeFilter.setOption('ui.chartOptions.colors', ['#109618']);
            } else if (x == 5) {

                airLineChart.setOption('colors', ['#990099']);
                airRangeFilter.setOption('ui.chartOptions.colors', ['#990099']);
            }
            //Sets the view to the correct thing
            airLineChart.setView({columns: filteredColumnIndices});
            airRangeFilter.setView({columns: filteredColumnIndices});
        }
        else {

            filteredColumnIndices = [0, 1, 2, 3, 4, 5];

            airLineChart.setOption('colors', null);
            airRangeFilter.setOption('ui.chartOptions.colors', null);

            airLineChart.setView(null);
            airRangeFilter.setView(null);

        }

        var state = airRangeFilter.getState();
        var view = new google.visualization.DataView(data);
        var filteredRows = view.getFilteredRows([{column: 0, minValue: state.range.start, maxValue: state.range.end}]);

        if (filteredColumnIndices.length != 1) {

            view.setColumns(filteredColumnIndices);
            airLineChart.setView({columns: filteredColumnIndices, rows: filteredRows});
        } else {

            airLineChart.setView({rows: filteredRows});
        }

        airRangeFilter.draw();
        airLineChart.draw();
    });

    google.visualization.events.addListener(airRangeFilter, 'statechange', function () {

        var state = airRangeFilter.getState();
        var view = new google.visualization.DataView(data);
        var filteredRows = view.getFilteredRows([{column: 0, minValue: state.range.start, maxValue: state.range.end}]);

        if (filteredColumnIndices.length != 1) {

            view.setColumns(filteredColumnIndices);
            airLineChart.setView({columns: filteredColumnIndices, rows: filteredRows});
        } else {

            airLineChart.setView({rows: filteredRows});
        }

        airLineChart.draw();
    });

    airLineChart.draw();
    airRangeFilter.draw();
    airCategoryFilter.draw();
}

// HEART-RELATED CHART
function drawHeartChart() {
    var data = google.visualization.arrayToDataTable(heart_chart_records);

    heartRangeFilter = new google.visualization.ControlWrapper({
        'controlType': 'ChartRangeFilter',
        'containerId': 'heart_range_div',
        //'dataTable': heart_chart_records,
        'options': {
            'filterColumnIndex': 0,
            'ui': {
                'chartType': 'LineChart',
                'chartOptions': {
                    'chartArea': {'height': '100', 'width' :'100%'},
                    'hAxis': {'textPosition': 'in'}
                }
            }
        }
    });

    // Create a range slider, passing some options
    heartLineChart = new google.visualization.ChartWrapper({
        'chartType': 'LineChart',
        'containerId': 'heart_chart_div',
        //'dataTable': heart_chart_records,
        'options': {
            'width': '100%',
            'height': 600,
            'title': 'Historical Heart-related Data',
            'titleTextStyle': {
                'color': '#000000',
                'fontName': 'Roboto',
                'fontSize': 20
            },
            'subtitle': 'in beats per minute (BPM)',
            'hAxis': {
                'title': 'Time',
                'titleTextStyle': {
                    'color': '#000000',
                    'fontName': 'Roboto',
                    'fontSize': 20
                },
                'textPosition': 'out',
                'gridlines': {
                    'count': -1,
                    'color': '#000000',
                    'units': {
                        'minutes': {'format': ['HH:mm:ss']},
                        'hours': {'format': ['M/d h a']},
                        'days': {'format': ['MMM d, yyyy']}
                    }
                },
                'minorGridlines': {
                    'units': {
                        'count': -1,
                        'minutes': {'format': ['H:mm']},
                        'hours': {'format': ['h a']},
                        'days': {'format': ['M/d']}
                    }
                }
            },
            'vAxis': {
                'title': 'Heart-rate (BPM)',
                'titleTextStyle': {
                    'color': '#000000',
                    'fontName': 'Roboto',
                    'fontSize': 20
                },
                'textPosition': 'in'
            }
        }
    });
    /*var chart = new google.charts.Line(document.getElementById('linechart_material'));
    chart.draw(data, google.charts.Line.convertOptions(options));*/

    heartDashboard = new google.visualization.Dashboard(document.getElementById('heart_dashboard_div'));
    heartDashboard.bind(heartRangeFilter, heartLineChart);
    heartDashboard.draw(data);
}

// functions for Air Quality Data Charts
function getCustomRangeAirData(){
    var from = $('#inputAirRangeFrom').val();
    var to = $('#inputAirRangeTo').val();

    if(from == 0 || to == 0){
        return;
    }

    getAirDataInRange(from, to);
}

function getOneHourAirData(){
    var now = Date.now();
    var to = now;
    var from = now - 3600000;

    $('#inputAirRangeFrom').val(convertTimestamp(from));
    $('#inputAirRangeTo').val(convertTimestamp(to));

    getAirDataInRange(new Date(from), new Date(to));
}

function getOneDayAirData(){
    var now = Date.now();
    var to = now;
    var from = now - 86400000 ;

    $('#inputAirRangeFrom').val(convertTimestamp(from));
    $('#inputAirRangeTo').val(convertTimestamp(to));

    getAirDataInRange(new Date(from), new Date(to));
}

function getOneWeekAirData(){
    var now = Date.now();
    var to = now;
    var from = now - 604800000;

    $('#inputAirRangeFrom').val(convertTimestamp(from));
    $('#inputAirRangeTo').val(convertTimestamp(to));

    getAirDataInRange(new Date(from), new Date(to));
}

function getAllAirData(){
    $.ajax({
        type:"GET",
        dataType: "json",
        url: "http://teamd-iot.calit2.net/api/data/read/charts/aqi",
        contentType: "application/json",
        success: function(results) {
            if (results.status == false){
                alert('Error [' + results.code + ']: There\'s something wrong!');
                return;
            }

            results.message.forEach(function(record){
                air_chart_records.push([new Date(record.timestamp), record.co, record.no2, record.pm2_5]);
            });
            if (typeof airLineChart != 'undefined')
                airLineChart.draw();
            if (typeof airRangeFilter != 'undefined')
                airRangeFilter.draw();
        }
    });
}

function getAirDataInRange(from, to){
    var request = {
        from: from,
        to: to
    };

    $.ajax({
        type:"POST",
        dataType: "json",
        data: JSON.stringify(request),
        url: "http://teamd-iot.calit2.net/api/data/read/charts/aqi/range",
        contentType: "application/json",
        success: function(results) {
            if (results.status == false){
                alert('Error [' + results.code + ']: There\'s something wrong!');
                return;
            }

            var gapInterval = 1800000; // 30 minutes
            air_chart_records = [[{label: 'Time', type: 'date'},
                {label: 'CO', type: 'number'},
                {label: 'NO2', type: 'number'},
                {label: 'SO2', type: 'number'},
                {label: 'O3', type: 'number'},
                {label: 'PM2.5', type: 'number'}]];

            results.message.forEach(function(record){
                if (air_chart_records.length > 1){
                    var lastIndex = air_chart_records.length - 1;
                    var newDate = new Date(record.timestamp);

                    // if record has time gap with gapInterval milliseconds
                    if ((newDate.getTime() - air_chart_records[lastIndex][0].getTime()) > gapInterval){
                        // insert dummy null data record for time gap
                        air_chart_records.push([new Date((newDate.getTime() - gapInterval)), null, null, null, null, null]);
                    }

                }

                air_chart_records.push([new Date(record.timestamp), record.co, record.no2, record.so2, record.o3, record.pm2_5]);
            });
            google.charts.setOnLoadCallback(drawAirChart);
        }
    });
}



// functions for Heart-related Data Charts
function getCustomRangeHeartData(){
    var from = $('#inputHeartRangeFrom').val();
    var to = $('#inputHeartRangeTo').val();

    if(from == 0 || to == 0){
        return;
    }

    getHeartDataInRange(from, to);
}

function getOneHourHeartData(){
    var now = Date.now();
    var to = now;
    var from = now - 3600000;

    $('#inputHeartRangeFrom').val(convertTimestamp(from));
    $('#inputHeartRangeTo').val(convertTimestamp(to));

    getHeartDataInRange(new Date(from), new Date(to));
}

function getOneDayHeartData(){
    var now = Date.now();
    var to = now;
    var from = now - 86400000 ;

    $('#inputHeartRangeFrom').val(convertTimestamp(from));
    $('#inputHeartRangeTo').val(convertTimestamp(to));

    getHeartDataInRange(new Date(from), new Date(to));
}

function getOneWeekHeartData(){
    var now = Date.now();
    var to = now;
    var from = now - 604800000;

    $('#inputHeartRangeFrom').val(convertTimestamp(from));
    $('#inputHeartRangeTo').val(convertTimestamp(to));

    getHeartDataInRange(new Date(from), new Date(to));
}

function getAllHeartData(){
    // HEARTRATE CHART
    $.ajax({
        type:"GET",
        dataType: "json",
        url: "http://teamd-iot.calit2.net/api/data/read/charts/heart",
        contentType: "application/json",
        success: function(results) {
            if (results.status == false){
                alert('Error [' + results.code + ']: There\'s something wrong!');
                return;
            }

            results.message.forEach(function(record){
                heart_chart_records.push([new Date(record.timestamp), record.heartbeat]);
            });
            if (typeof heartDashboard != 'undefined')
                heartDashboard.draw(heart_chart_records);
        }
    });
}

function getHeartDataInRange(from, to){
    var request = {
        from: from,
        to: to
    };

    $.ajax({
        type:"POST",
        dataType: "json",
        data: JSON.stringify(request),
        url: "http://teamd-iot.calit2.net/api/data/read/charts/heart/range",
        contentType: "application/json",
        success: function(results) {
            if (results.status == false){
                alert('Error [' + results.code + ']: There\'s something wrong!');
                return;
            }

            var gapInterval = 1800000; // 30 minutes

            heart_chart_records = [[{label: 'Time', type: 'date'},
                {label: 'Heartbeats', type: 'number'}]];

            results.message.forEach(function(record){
                if (heart_chart_records.length > 1){
                    var lastIndex = heart_chart_records.length - 1;
                    var newDate = new Date(record.timestamp);

                    // if record has time gap with gapInterval milliseconds
                    if ((newDate.getTime() - heart_chart_records[lastIndex][0].getTime()) > gapInterval){
                        // insert dummy null data record for time gap
                        heart_chart_records.push([new Date(record.timestamp), null]);
                    }

                }

                heart_chart_records.push([new Date(record.timestamp), record.heartbeat]);
            });
            google.charts.setOnLoadCallback(drawHeartChart);
        }
    });
}



// additional functions
function convertTimestamp(timestamp) {
    var d = new Date(timestamp),	// Convert the passed timestamp to milliseconds
        yyyy = d.getFullYear(),
        mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
        dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
        hh = d.getHours(),
        h = hh,
        min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
        ampm = 'AM',
        time;

    if (hh > 12) {
        h = hh - 12;
        ampm = 'PM';
    } else if (hh === 12) {
        h = 12;
        ampm = 'PM';
    } else if (hh == 0) {
        h = 12;
    }

    // ie: 2013-02-18, 8:35 AM
    time = yyyy + '-' + mm + '-' + dd ;// ', ' + h + ':' + min + ' ' + ampm;

    return time;
}


