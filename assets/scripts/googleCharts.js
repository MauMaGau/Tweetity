// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);

// Callback that creates and populates a data table,
// instantiates the chart, passes in the data and
// draws it.
function drawChart() {

    var dataType = getDataType();
	var chartData = getHistogram(dataType);
    

		// Create the data table.
		var data = new google.visualization.DataTable(chartData);

		// Set chart options
		var options = {'title':dataType+' tweets',
						'height':'800'
		               };

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
		chart.draw(data, options);
}

function getHistogram(dataType){
    console.log(dataType);
    var handle = $('#handle').attr('data-handle');
	$.ajax({
		  url: '/sandbox/Tweetity/index.php/tweetmuch/ajax/histogram/'+handle+'/'+dataType,
		  async: false,
		  success: function( response ) {
		  	ajaxOutput = $.parseJSON(response);
		  }
	  });
	  console.log(ajaxOutput);
	  return ajaxOutput;
}

function getTweets(){
    var handle = $('#handle').attr('data-handle');
    $.ajax({
          url: '/sandbox/Tweetity/index.php/tweetmuch/ajax/tweets/'+handle,
          async: false,
          success: function( response ) {
              ajaxOutput = $.parseJSON(response);
          }
      });
      //console.log(ajaxOutput);
      return ajaxOutput;
}

function getDataType(){
    return $('input[name=dataType]').val();
}

$(function(){
    var tweets = getTweets();
    for(var i=0;i<tweets.length;i++){
        //console.log(tweets[i]);
        $('#tweets').append('<li>['+tweets[i].created_at+'] '+tweets[i].text+'</li>');
    }
    
    $('.tweetType').click(function(){
        $('#dataType').attr('value',$(this).attr('id'));
        drawChart();
    })
})
