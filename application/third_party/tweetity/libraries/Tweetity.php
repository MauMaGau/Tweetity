<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Tweetity Class
 *
 * Analyse density of tweets for a given user
 *
 * @package            CodeIgniter
 * @subpackage        Libraries
 * @category        Libraries
 * @author            Dave Townsend
 * @link            http://dtownsend.co.uk
 */
class Tweetity {

    public $handle = 'bbcbreaking';
    public $slice = 86400; // Period in seconds. Default 86400 = 1 day.
    public $period = 30; // Number of consecutive slices to get.
    public $page = 1;
    public $offset = 0;
    public $perpage = 100;
    

    public $result = false;

    private $feed_bin = array(
        'otter'=>'http://otter.topsy.com/',
        'twitter'=>'http://search.twitter.com/'
    );
    private $feed_url = 'http://otter.topsy.com/';
    private $query = 'searchhistogram.json?q=from:davidtownsenduk';

    public $count = 10;
    private $timeBoundary;
    private $timeCount = array();
    public $tweets = array();
    private $toUnset = array();
    
    /**
          * Contact API and get results
         *
         * @access protected
         * @return void
          */
    protected function get($start_date=false,$end_date=false){

        // Get tweets
        $tweets = json_decode(@file_get_contents( $this->feed_url.$this->query ));

        if(!$tweets){
            return false;
        }

        $this->result = $tweets;
    }

    /**
          * Build query and retrieve histogram
         *
         * @access public
         * @return void
          */
    public function histogramCount(){

        $this->feed_url = $this->feed_bin['otter'];
        $this->query = 'searchhistogram.json';
        $this->query .= '?q=from:'.$this->handle;
        $this->query .= '&slice='.$this->slice;
        $this->query .= '&period='.$this->period;

        $this->get();

    }
    
    private function search(){
        $this->feed_url = $this->feed_bin['twitter'];
        $this->query = 'search.json';
        $this->query .= '?q=from:'.$this->handle;

        $this->get();
    }
    
    public function getTweets(){
        $this->search();
        $list = $this->result->results;
        return $list;
    }

    private function getHistogram(){
            $this->histogramCount();
    }

    /**
              * Get Monthly Histogram
             *
             * @access public
             * @return array - chartData chartLabels=>chartValues
              */
    public function getHistogramMonthly($months=12){
            $totalDays = date('d');
            $monthBoundaries[] = array('date'=>date('M Y'),'start'=>$totalDays,'end'=>$totalDays,'monthDays'=>$totalDays);
            $months = $months - 1;
            for($i=1;$i<=$months;$i++){

                $monthDate = strtotime(date("Y-m-d")." - $i months");
                $daysInMonth = date('t',$monthDate);

                $monthBoundaries[] = array('date'=>date('M Y',$monthDate),'start'=>$totalDays,'end'=>$totalDays+$daysInMonth,'monthDays'=>$daysInMonth);
                $totalDays = $totalDays + $daysInMonth;
            }

            $this->slice='86400';
            $this->period=$totalDays;
            $this->getHistogram();

            $histogram = $this->result->response->histogram;

            foreach($monthBoundaries as $month){
                $monthData[$month['date']] = array_slice($histogram,$month['start'],$month['monthDays']);

                $monthTotal = array_sum($monthData[$month['date']]);

                $chartValues[] = $monthTotal;

                $chartLabels[] = $month['date'];
            }

            $chartData = array_combine($chartLabels,$chartValues);

            return $chartData;
        }
        
        /**
              * Get HOURLY Histogram.
             *
             * @access public
             * @return Array - [hour]=>tweets
              */
        public function getHistogramHourly($hours=24){
            $this->timeBoundary = date('Y-m-d H:i:s',strtotime("-$hours hours"));
            for($i=0;$i<$hours;$i++){ // There's a mstery hour coming from somewhere... timezones or some shit. starting on 1 bodges it.
                $this->timeCount[date('jS ga',strtotime("-".($i+1)." hours"))] = 0;
            }

            $this->feed_url = $this->feed_bin['twitter'];
            $this->query = 'search.json?q=';
            $this->query .= 'from%3A'.$this->handle;
            $this->query .= '%20since%3A'.date('Y-m-d',strtotime("-$hours hours"));
            
            $this->get();

            //Discard any tweets beyond search time
            array_walk($this->result->results,array($this,'twitFormatResults'));
            foreach($this->toUnset as $key){
                array_splice($this->result->results,$key+1);
                break;
            }
            $results = $this->result->results;
            
            
            //Is there a next page url, and have we reached the start of our search period yet?
            $i=0;
            while(count($this->result->results)==$this->result->results_per_page && isset($this->result->next_page) &&$i<10){
                $this->query = 'search.json'.$this->result->next_page;
                $this->get();

                array_walk($this->result->results,array($this,'twitFormatResults'));
                foreach($this->toUnset as $key){
                    array_splice($this->result->results,$key+1);
                    break;
                }
            
                $results = array_merge($results,$this->result->results);
                $i++;
            }
            
            //$this->result->results = $results;
            
            return $this->timeCount;
        }

        public function getHistogramCount($count=10){

            $count = $this->count;
            $this->feed_url = $this->feed_bin['twitter'];
            $this->query = 'search.json?q=';
            $this->query .= 'from:'.$this->handle;
            if($count<=100){
                $this->query .= '&rpp='.$count;
            }else $this->query .= '&rpp=100';

            $this->get();

            array_walk($this->result->results,array($this,'twitFormatResultsDensity'));
            
            $results = $this->result->results;

            //Is there a next page url?
            $i=0;
            while(count($this->result->results)==$this->result->results_per_page && isset($this->result->next_page) && count($results)<$count&&$i<5){
                $this->query = 'search.json'.$this->result->next_page;

                $this->get();

                array_walk($this->result->results,array($this,'twitFormatResultsDensity'));
                
                $results = array_merge($results,$this->result->results);
                $i++;
            }
            
            $this->timeCount = $this->twitFillTimes($this->timeCount);

            return $this->timeCount;
        }
        
        
        private function formatDate(&$tweet){
            $tweet->firstpost_date = date('Y-m-d H:i',$tweet->firstpost_date);
        }
        
        private function twitFormatResults(&$tweet,$key){
            //print_r($tweet);
            $createdAt = strtotime($tweet->created_at.'-1 hour'); // Why -1 hour? Answers on a postcard plx.
            $niceCreatedAt = date('jS ga',$createdAt);
            
            if($createdAt<strtotime($this->timeBoundary)){
                $this->toUnset[]=$key;
                return;
            }elseif(isset($this->timeCount[$niceCreatedAt])){
                $this->timeCount[$niceCreatedAt]++;
            }else{
                $this->timeCount[$niceCreatedAt] = 1;
            }
            $tweet->fcreated_at = date('jS M y g:ia',$createdAt);
            
            $index = array_search($niceCreatedAt,array_keys($this->timeCount));
            if(isset($this->tweets[$index])){
                $this->tweets[$index] = "\r\n ".date('H:i',$createdAt).' '. $tweet->text.$this->tweets[$index];
            }else{
                $this->tweets[$index] = "\r\n ".date('H:i',$createdAt).' '.$tweet->text;
            }
            
        }
        

        private function twitFormatResultsDensity(&$tweet,$key){
            //print_r($tweet);
            //$createdAt = date('Y-m-d H:00',strtotime($tweet->created_at.' -1 hour')); // Why -1 hour? Answers on a postcard plx.
            $createdAt = strtotime(date('Y-m-d H:00',strtotime($tweet->created_at.' -1 hour'))); //
            
            $this->timeCount[] = $createdAt;
        }
        
        private function twitFillTimes($timeCount){

            $arr_size = count($timeCount);
            //$earliest_time = strtotime($timeCount[$arr_size-1]);
            
            $earliest_time = strtotime(date("Y-m-d H:00:00",$timeCount[$arr_size-1]));
            
            //$earliest_time = date('Y-m-d H:00',$earliest_time);
            

            $timeCounts = array_count_values($timeCount);
            
            $now = time();
            $x = $earliest_time;
            $i=0;
            while($x < $now){
                
                
                if(isset($timeCounts[$x])){
                    $times[$i] = $timeCounts[$x];
                }else $times[$i] = 0;
                
                
                //$x = date('Y-m-d H:00',strtotime($x.' +1 hour'));
                //$x = strtotime($x.' +1 hour');
                $x = $x + 3600;
                $i++;
            }

            //foreach($times as $k=>$v){
            //    $times[$k] = count(array_keys($timeCount,$k));
            //}
            return $times;
        }
        
// OLD METHODS
        
        /**
              * Get HOURLY Histogram. Deprecated. Returns only 'high-ranking' tweets. Damn you otter-api.
             *
             * @access public
             * @return void
              */
        public function DEPRECATEDgetHistogramHourly($hours = 24){
            $this->slice = 1440; // Set splice as 1 hour
            $this->period = $hours;
            $this->getHistogram();

            $histogram = $this->result->response->histogram;

            // Make chart labels
            for($i=0;$i<=($hours)-1;$i++){
                $chartLabels[] = $i;
            }
            if(date('H')>0){
                $chartLabelsa = array_chunk($chartLabels,date('H'),true);
                $chartLabels = array_merge(array_splice($chartLabels,date('H')),$chartLabelsa[0]);
            }
            
            $chartLabels = array_reverse($chartLabels);
            $chartData = array_combine($chartLabels,$histogram);

            return $chartData;
        }

        /**
              * Get Monthly Histogram (a bit shit and not used, just included for nostalgia)
             *
             * @access public
             * @return void
              */
        private function DEPRECATEDgetHistogramMonthly(){

            $day = 86400;

            // Get results for partial of this month (up to now)
            $secondstoget = $day*date("d");
            $this->tweetity->period=1;
            $this->tweetity->slice=$secondstoget;
            $result = $this->getHistogram();
            $histogram[0] = $result->response->histogram;


            // Calculate seconds to get for each month
            $months = 11;
            $totalCount = $histogram[0];
            $i=1;
            while($months>=$i){
                $m = strtotime(date("Y-m-01")." - $i month");
                $daysinmonth = date("t",$m);
                $secondstoget = $secondstoget + ($day * $daysinmonth);

                $this->tweetity->period=1;
                $this->tweetity->slice=$secondstoget;

                $result = $this->getHistogram();

                $monthCount = $result->response->histogram;

                $histogram[] = $monthCount - $totalCount;
                $totalCount = $monthCount;

                $i++;
            }

            $keys = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');

            $thisMonth = date("n");
            $keysa = array_chunk($keys,($thisMonth),true);
            $keys = array_slice($keys,$thisMonth);
            $keys = (array_merge(array_reverse($keysa[0]),array_reverse($keys)));

            $histogram = array_combine($keys,$histogram);

            $chartTable = $this->makeChart($histogram);

            return $chartTable;
        }

}