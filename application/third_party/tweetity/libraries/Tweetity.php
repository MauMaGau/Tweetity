<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Tweetity Class
 *
 * Analyse density of tweets for a given user
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author        	Dave Townsend
 * @link			http://dtownsend.co.uk
 */
class Tweetity {

	public $handle = 'bbcbreaking';
	public $slice = '86400'; // Period in seconds. Default 86400 = 1 day.
	public $period = '30'; // Number of consecutive slices to get.

	public $result = false;

	private $feed_url = 'http://otter.topsy.com/';
	private $query = 'searchhistogram.json?q=from:bbcbreaking';

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

		$this->query = 'searchhistogram.json';
		$this->query .= '?q=from:'.$this->handle;
		$this->query .= '&slice='.$this->slice;
		$this->query .= '&period='.$this->period;

		$this->get();

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
    public function getHistogramSmartMonthly($months=12){
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

        public function getHistogramHourly($hours = 24){
            $this->slice = 1440; // Set splice as 1 hour
            $this->period = $hours;
            $this->getHistogram();

            $histogram = $this->result->response->histogram;

            // Make chart labels
            for($i=1;$i<=$hours;$i++){
                $chartLabels[] = $i;
            }
            $chartLabelsa = array_chunk($chartLabels,date('H'),true);
            $chartLabels = array_merge(array_splice($chartLabels,date('H')),$chartLabelsa[0]);
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
        private function getHistogramMonthly(){

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