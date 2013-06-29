<?php

#Metro IS A SUBCLASS OF SonkickData
require_once "SongkickData.php";

class VenueDetails extends SongkickData {

    #FOR THIS CLASS THE queryArgument IS THE SEARCH STRING
    protected $queryArgument;
    private $apikey;
    public $data_array = array();
    protected $called_class = "VenueDetails";
    public $useCache = 1;
    
    public function __construct($arg1, $arg2) {
        $this->queryArgument = $arg1;
        $this->apikey = $arg2;
    }
    
    #TODO: MAKE THIS A CACHABLE FUNCTION AS WELL
    protected function _getMatchingFromApi () {

      $obj = array();
      $curl_string = "http://api.songkick.com/api/3.0/venues/$this->queryArgument.json?apikey=$this->apikey";
      $this->_getJsonResponse($curl_string, $obj);
      
      #$the_array=array("id"=>$sk_id,"city"=>$full_location);
              
      $this->data_array['displayName']=$obj['resultsPage']['results']['venue']['displayName'];
      $this->data_array['uri']=$obj['resultsPage']['results']['venue']['uri'];
      $this->data_array['website']=$obj['resultsPage']['results']['venue']['website'];
      $this->data_array['city']=$obj['resultsPage']['results']['venue']['metroArea']['displayName'];
    
      $this->_writeCache();
      
    
    }

    
   
}

//CREATE A NEW INSTANTIATION FOR PORTLAND
// $venueDetails = new VenueDetails(35260, $apikey);     
// $venueDetails->getMatching();
//   
// echo json_encode($venueDetails->data_array);
