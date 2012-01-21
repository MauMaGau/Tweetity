<?php
	class Tweetmuch extends CI_Controller{

		public function __construct(){
			parent::__construct();
			$this->load->add_package_path(APPPATH.'third_party/tweetity/');
			$this->load->library('Tweetity');
            $this->load->library('gchart');
            $this->load->library('carabiner');
		}

		public function index($handle=null){
			$data = array('result'=>'');
            $headData['title'] = 'Tweet Density Analyser';
            
            $this->load->helper('form');
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('handle','Handle','required');
            
            if($this->form_validation->run()===false){
                if(!is_null($handle)){
                    $data['handle'] = $handle;
                    $this->tweetity->handle = $handle;
                }
                
                $this->page($data,$headData);
            }else{
                $this->tweetity->handle = $this->input->post('handle');
                $data['handle'] = $this->input->post('handle');

                $this->page($data,$headData);
            }            
			
		}
        
        private function page($data,$headData){
            
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
		public function ajax($handle='bbcbreaking'){
            $this->tweetity->handle = $handle;
			$chartData = $this->tweetity->getHistogramHourly();
            $this->gchart->makeChart($chartData);
            $data['result'] = $this->gchart->chartTable;

			$this->load->view('json/simple',$data);
		}
		
	}
?>