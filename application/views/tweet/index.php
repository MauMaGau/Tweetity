<p>Welcome to Tweetity</p>
<?php
	if(isset($error)&&!empty($error)){
?>
		<h3>An error has occurred!</h3>
		<ul id='tweetErrors'>
<?php
		foreach($error as $anError){
?>
			<li><p class='error'><?php echo $anError; ?></p></li>
<?php
		}
?>
		</ul>
<?php
	}else{

		//print_r($result);

?>
<!--Div that will hold the pie chart-->
    <div id="chart_div"></div>

<?php
	}
?>