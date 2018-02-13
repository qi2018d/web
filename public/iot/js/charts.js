// Load the Visualization API and the corechart package.
var air_chart_records = [[{label: 'Time', type: 'date'},
                    {label: 'CO', type: 'number'},
                    {label: 'NO2', type: 'number'},
                    {label: 'PM2.5', type: 'number'}]];

var heart_chart_records = [[{label: 'Time', type: 'date'},
                    {label: 'Heartbeats', type: 'number'}]];
var columnsToShow = [0];


// AIRQUALITY CHART
$.ajax({
    type:"GET",
    dataType: "json",
    url: "http://teamd-iot.calit2.net/api/data/read/charts/heart",
    contentType: "application/json",
    success: function(results) {
        results.message.forEach(function(record){
            heart_chart_records.push([new Date(record.timestamp), record.heartbeat]);
        });

        google.charts.load('current', {'packages':['line', 'controls']});
        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {
            var data = google.visualization.arrayToDataTable(heart_chart_records);


            var rangeFilter = new google.visualization.ControlWrapper({
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
            var lineChart = new google.visualization.ChartWrapper({
                'chartType': 'LineChart',
                'containerId': 'heart_chart_div',
                //'dataTable': heart_chart_records,
                'options': {
                    'width': '100%',
                    'height': 500,
                    'title': 'Historical Heart-related Data',
                    'subtitle': 'in beats per minute (BPM)',
                }
            });
            /*var chart = new google.charts.Line(document.getElementById('linechart_material'));
            chart.draw(data, google.charts.Line.convertOptions(options));*/

            var dashboard = new google.visualization.Dashboard(document.getElementById('heart_dashboard_div'));
            dashboard.bind(rangeFilter, lineChart);
            dashboard.draw(data);
        }
    }
});

// HEARTRATE CHART
$.ajax({
    type:"GET",
    dataType: "json",
    url: "http://teamd-iot.calit2.net/api/data/read/charts/air",
    contentType: "application/json",
    success: function(results) {
        results.message.forEach(function(record){
            air_chart_records.push([new Date(record.timestamp), record.co, record.no2, record.pm2_5]);
        });

        google.charts.load('current', {'packages':['line', 'controls']});
        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {
            var data = google.visualization.arrayToDataTable(air_chart_records);
            // var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));
            var categorySelector = new google.visualization.ControlWrapper({
                'controlType': 'CategoryFilter',
                'containerId': 'air_category_div',
                'dataTable': air_chart_records,
                'options': {
                    'filterColumnIndex': 1,
                    'values' : ['CO', 'NO2', 'PM2.5'],
                    'ui': {
                        'labelStacking': 'vertical',
                        'label': 'Sensor Selection',
                        'allowTyping': false,
                        'allowMultiple': false
                    }
                }
            });

            var rangeFilter = new google.visualization.ControlWrapper({
                'controlType': 'ChartRangeFilter',
                'containerId': 'air_range_div',
                'dataTable': air_chart_records,
                'options': {
                    'filterColumnIndex': 1,
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
            var lineChart = new google.visualization.ChartWrapper({
                'chartType': 'LineChart',
                'containerId': 'air_chart_div',
                'dataTable': air_chart_records,
                'options': {
                    'width': '100%',
                    'height': 500,
                    'title': 'Historical Air Quality Data',
                    'subtitle': 'in particles per million (PPM)',
                }
            });
            /*var chart = new google.charts.Line(document.getElementById('linechart_material'));
            chart.draw(data, google.charts.Line.convertOptions(options));*/
            // dashboard.bind(rangeFilter, lineChart);
            google.visualization.events.addListener(categorySelector, 'statechange', function () {
                var selectedSensor = categorySelector.getState().selectedValues[0];
                if (typeof selectedSensor != 'undefined'){
                    columnsToShow = [0];
                    //Finds which column to show
                    for (x = 0; x < data.getNumberOfColumns(); x++){
                        if (data.getColumnLabel(x) == selectedSensor){
                            columnsToShow[1] = x;
                            break;
                        }
                    }
                    //Changing color to "red" to maintain the color of the second column, even if it's shown alone.
                    if (x == 1){

                        lineChart.setOption('colors',['#3366cc']);
                        rangeFilter.setOption('ui.chartOptions.colors',['#3366cc']);
                    }else if(x == 2){

                        lineChart.setOption('colors',['#dc3912']);
                        rangeFilter.setOption('ui.chartOptions.colors',['#dc3912']);
                    }else if(x == 3){

                        lineChart.setOption('colors',['#ff9900']);
                        rangeFilter.setOption('ui.chartOptions.colors',['#ff9900']);
                    }
                    //Sets the view to the correct thing
                    rangeFilter.setView({ columns: columnsToShow });
                    lineChart.setView({ columns: columnsToShow });
                }
                else {

                    columnsToShow = [0, 1, 2, 3];

                    lineChart.setOption('colors', null);
                    rangeFilter.setOption('ui.chartOptions.colors', null);

                    rangeFilter.setView(null);
                    lineChart.setView(null);
                }
                lineChart.draw();
                rangeFilter.draw();
            });

            google.visualization.events.addListener(rangeFilter, 'statechange', function () {
                var state = rangeFilter.getState();
                var view = new google.visualization.DataView(data);
                var filteredRows = view.getFilteredRows([{ column: 0, minValue: state.range.start, maxValue: state.range.end }]);

                if(columnsToShow.length != 1) {

                    view.setColumns(columnsToShow);
                    lineChart.setView({ columns: columnsToShow, rows: filteredRows });
                } else {

                    lineChart.setView({ rows: filteredRows });
                }

                lineChart.draw();
            });

            lineChart.draw();
            rangeFilter.draw();
            categorySelector.draw();
        }
    }

});


