



// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);

// Callback that creates and populates a data table,
// instantiates the chart, passes in the data and
// draws it.
function drawChart() {

	chartData = getHistogram();

		// Create the data table.
		var data = new google.visualization.DataTable(chartData);

		// Set chart options
		var options = {'title':'Tweets per month',
						'height':'800'
		               };

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
		chart.draw(data, options);
}

function getHistogram(){
    handle = $('#handle').html();
	$.ajax({
		  url: '/sandbox/Tweetity/index.php/tweetmuch/ajax/'+handle,
		  async: false,
		  success: function( response ) {
		  	ajaxOutput = $.parseJSON(response);
		  }
	  });
	  console.log(ajaxOutput);
	  return ajaxOutput;
}
