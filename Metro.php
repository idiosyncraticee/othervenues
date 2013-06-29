<?php

#Metro IS A SUBCLASS OF SonkickData
require_once "SongkickData.php";

class Metro extends SongkickData {

    #FOR THIS CLASS THE queryArgument IS THE SEARCH STRING
    protected $queryArgument;
    private $apikey;
    public $data_array = array();
    protected $called_class = "Metro";
    
    public function __construct($arg1, $arg2) {
        $this->queryArgument = $arg1;
        $this->apikey = $arg2;
    }
        
    protected function _getMatchingFromApi () {

      //SONGKICK DOESNT HAVE A WAY TO GET A LIST OF VENUES BY CITY
      $curl_string = "http://api.songkick.com/api/3.0/search/locations.json?query=$this->queryArgument&apikey=$this->apikey";
      $this->_getJsonResponse($curl_string, $obj);
      
      foreach ($obj['resultsPage']['results']['location'] as $location ) {
       //print_r($location);
        //echo "<br><br>";
        $country = $location['city']['country']['displayName'];
        $state = isset($location['city']['state']['displayName']) ? $location['city']['state']['displayName'] : 'none';
        $city = $location['city']['displayName'];
        $sk_id = $location['metroArea']['id'];
        $full_location = "$city,$state,$country";
        
        $the_array=array("id"=>$sk_id,"city"=>$full_location);
        array_push($this->data_array, $the_array);
  
      }
  
      //SORT THE ARRAY ALPHABETICALLY BY displayName        
      $this->_aasort($this->data_array,"city");
      //REINDEX THE ARRAY SO THE SORT ORDER IS MAINTAINED BY THE JAVASCRIPT
      $this->data_array = array_values($this->data_array);
      
      $this->_writeCache();
          
    }    
}

//CREATE A NEW INSTANTIATION FOR PORTLAND
//metro = new Metro('San Antonio', $apikey);     
//$metro->getMatching();
//echo json_encode($metro->data_array);
