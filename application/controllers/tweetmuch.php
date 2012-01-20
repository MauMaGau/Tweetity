<?php
	class Tweetmuch extends CI_Controller{

		public function index(){


			$result = $this->getHistogram();

			if(!$result){
				$data['error'][] = 'No results';
			}else{
				$data['result'] = $result;
			}



			$this->load->remove_package_path();

			$headData['title'] = 'Tweet Density Analyser';
			$this->load->library('carabiner');
			$this->carabiner->js('http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js');
			$this->carabiner->js('https://www.google.com/jsapi');
			$this->carabiner->js('googleCharts.js');

			$this->load->view('templates/header',$headData);
			$this->load->view('tweet/index',$data);
			$this->load->view('templates/footer');
		}

		/**
		 	 * ajax access
			 *
			 * @access public
			 * @return JSON
		 	 */
		public function ajax($var){
			$var = 'get'.ucfirst($var);
			$data['result'] = $this->$var();
			$this->load->view('json/simple',$data);
		}


		/**
		 	 * Get Histogram from tweetity library
			 *
			 * @access public
			 * @return void
		 	 */
		private function getHistogram(){
			$this->load->add_package_path(APPPATH.'third_party/tweetity/');
			$this->load->library('Tweetity');
			$this->tweetity->histogramCount();
			return $this->tweetity->result;
		}
	}
?>