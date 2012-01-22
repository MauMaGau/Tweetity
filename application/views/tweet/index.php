<p>Welcome to Tweetmuch</p>
<?php
    echo date('jS ga');
    echo date('H:i:s');
?>
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
<p id='handle' data-handle='<?php if(isset($handle)){echo $handle;}?>'><a href='http://www.twitter.com/<?php if(isset($handle)){echo $handle;} ?>'>@<?php if(isset($handle)){echo $handle;} ?></a></p>
<section>
    <h2>Recent Tweets</h2>
    <ul id="tweets"></ul>
</section>
<section>
    <h2>Tweets over duration</h2>
    <input type='button' class='tweetType' id='hourly' value='Last Day'><br>
    <input type='button' class='tweetType' id='monthly' value='Last Year'>
    <input type='hidden' name='dataType' id='dataType' value='hourly'>
    <div id="chart_div"></div>
</section>
<?php
	}
?>