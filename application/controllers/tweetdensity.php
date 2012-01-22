<?php
    class Tweetdensity extends CI_Controller{

        public function __construct(){
            parent::__construct();
            $this->load->add_package_path(APPPATH.'third_party/tweetity/');
            $this->load->library('Tweetity');
            $this->load->library('gchart');
            $this->load->library('carabiner');
        }

        public function index($handle='null'){
            $data = array('result'=>'');
            $headData['title'] = 'Tweet Density Analyser';
            
            // FILTER THESE
            parse_str($_SERVER['QUERY_STRING'], $GET);

            $handle = $GET['handle'];
            $data['count'] = $GET['count'];
            $type = $GET['type'];
            
            $this->tweetity->handle = $handle;
            $data['handle'] = $handle;
            
            switch($type){
                case('html'): $this->page($data,array('title' => 'Tweet Density Analyser'));
                break;
                case('xml'): $this->xml($data);
                break;
                case('json'): $this->json($data);
                break;
                default: $this->page();
            }            
            
        }
        
        private function page($data,$headData){
            
            $this->carabiner->js('http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js');
            $this->carabiner->js('https://www.google.com/jsapi');
            $this->carabiner->js('tweetDensity.js');

            $this->load->view('templates/header',$headData);
            $this->load->view('tweet/tweetdensity',$data);
            $this->load->view('templates/footer');
        }
        
        private function xml($data){
            
            $this->load->library('xml',array('tweetdensity')); 
            
            $this->tweetity->count = $data['count'];
            $result = $this->tweetity->getHistogramCount();

            $i=0;
            foreach($result as $count){
                $xml['data'][] = array('hour'=>$i,'count'=>$count);
                $i++;
            }
            
            echo $this->xml->toXml($xml);
        }
        
        private function json($data){
            $this->tweetity->count = $data['count'];
            $result = $this->tweetity->getHistogramCount();

            $i=0;
            foreach($result as $count){
                $json['tweetdensity']['data'][] = array('hour'=>$i,'count'=>$count);
                $i++;
            }
            
            echo json_encode($json);
        }

        /**
              * ajax access
             *
             * @access public
             * @return JSON
              */
        public function ajaxHistogram($handle='davidtownsenduk',$period='hourly',$count=10){
            $this->tweetity->handle = $handle;
            $period = ucfirst($period);
            $method = "getHistogram$period";
            
            $this->tweetity->count = $count;
            $chartData = $this->tweetity->$method();
            
            $this->gchart->makeChart($chartData,$this->tweetity->tweets);
            $data['result'] = $this->gchart->chartTable;

            $this->load->view('json/simple',$data);
        }
        
        public function ajaxTweets($handle='davidtownsenduk'){
            $this->tweetity->handle = $handle;
            $this->tweetity->page = 1;
            $this->tweetity->offset = 0;
            $this->tweetity->perpage = 10;
            
            $data['result'] = $this->tweetity->getTweets();
            
            $this->load->view('json/simple',$data);
        }

    }
?>