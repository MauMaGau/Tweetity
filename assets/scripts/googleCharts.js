      $.ajax({
		  url: 'http://192.168.0.20/sandbox/Tweetity/index.php/tweetmuch/ajax/histogram',
		  async: false,
		  success: function( response ) {
		  	ajaxOutput = JSON.parse(response);
		  }
	  });

	  results = ajaxOutput.response.histogram;
	  var arr = new Array();
	  x = 0;
	  for(i in results){
		  console.log(results[i]);
		  arr[x] = results[i];
		  x++;
	  }

alert(typeof(arr));

// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);

// Callback that creates and populates a data table,
// instantiates the chart, passes in the data and
// draws it.
drawChart(arr);
function drawChart(results) {

		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('number', 'Slices');
		data.addRows(results);

		// Set chart options
		var options = {'title':'How Much Pizza I Ate Last Night',
		               'width':400,
		               'height':300};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
		chart.draw(data, options);
}
