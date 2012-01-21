<?php
    class gchart{
        
        public $chartTable;
        
        /**
              * Prepare data for GoogleChart
             *
             * @access public
             * @return JSONifiable Multidimensional Associative Array
              */
        public function makeChart($input){
            foreach($input as $k=>$v){
                $rows[] = array('c'=>array(array('v'=>$k),array('v'=>$v)));
            }

            $chartTable = array(
                'cols'=>array(
                    array('id'=>'day','label'=>'day','type'=>'string'),
                    array('id'=>'tweets','label'=>'Tweets','type'=>'number')

                ),
                'rows'=>$rows
            );

            $this->chartTable = $chartTable;
        }
        
    }
?>