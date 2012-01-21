<p>Welcome to Tweetity</p>

<?php 
    echo validation_errors();
    echo form_open('tweetmuch');
        
?>

    <label for='handle'>Handle</label>
    <input type='text' name='handle'><br>
    
    <input type='submit' value='Search'>
    </form>
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
?>
<p id='handle'><?php if(isset($handle)){echo $handle;} ?></p>
<!--Div that will hold the pie chart-->
    <div id="chart_div"></div>

<?php
	}
?>