<p>Welcome to Tweetmuch</p>

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
<input type='hidden' name='dataType' value='count'>
<p id='handle' data-handle='<?php if(isset($handle)){echo $handle;}?>'><a href='http://www.twitter.com/<?php if(isset($handle)){echo $handle;} ?>'>@<?php if(isset($handle)){echo $handle;} ?></a></p>
<p id='count' data-count='<?php if(isset($count)){echo $count;}?>'><?php if(isset($count)){echo $count;}?></p>
<section>
    <h2>Recent Tweets</h2>
    <ul id="tweets"></ul>
</section>
<section>
    <h2>Tweets over duration</h2>
    <input type='hidden' name='dataType' id='dataType' value='hourly'>
    <div id="chart_div"></div>
</section>
<?php
    }
?>