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

	public $from = 'bbcbreaking';
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
	 	 * Retrieve histogram
		 *
		 * @access public
		 * @return void
	 	 */
	public function histogramCount(){

		$mintime = strtotime("2010-01-01");
		$maxtime = strtotime("2011-01-01");

		$this->query = 'searchhistogram.json';
		$this->query .= '?q=from:'.$this->from;
		//$this->query .= "&maxtime=$maxtime&mintime=$mintime";
		$this->query .= '&period=600';

		$this->get();

	}

}