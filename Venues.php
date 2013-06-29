<?php

#Metro IS A SUBCLASS OF SonkickData
require_once "SongkickData.php";

class Venues extends SongkickData {
    
    #FOR THIS CLASS THE queryArgument IS THE CITY ID
    protected $queryArgument;
    private $apikey;
    public $data_array = array();
    protected $called_class = "Venues";
    
    
    public function __construct($arg1, $arg2) {
        $this->queryArgument = $arg1;
        $this->apikey = $arg2;
#        $this->_venueCacheName();
    }
        
    protected function _getMatchingFromApi() {

      #FIGURE OUT THE NUMBER OF PAGES
      $obj = array();
      $curl_string = "http://api.songkick.com/api/3.0/metro_areas/".$this->queryArgument."/calendar.json?apikey=".$this->apikey."&per_page=1";
      $this->_getJsonResponse($curl_string, $obj);
      
      if(!isset($obj['resultsPage']['results']['event'])) {
        echo "THERE SEEMS TO BE AN EVENT MISSING<br>";
        print_r($obj);
        die;
      }
      
      #SET THE NUMBER OF ENTRIES PER PAGE
      # MAX IS 50      
      $resultsPerPage = 50; 
      
      #GET THE TOTAL NUMBER OF ENTRIES             
      $totalEntries = $obj['resultsPage']['totalEntries'];

      #COMPUTE THE TOTAL NUMBER OF LOOPS
            
      $totalPage = ceil($totalEntries/$resultsPerPage);      
      #$totalPage = 2;
      
      #echo "totalPage    = $totalPage resultsPerPage = $resultsPerPage<br>";
      #GO THROUGH EACH PAGE LOOKING FOR UNIQUE VENUES
      $counter=1;
      for ($page=0; $page<=$totalPage; $page++) {
        unset($obj);
        $curl_string = "http://api.songkick.com/api/3.0/metro_areas/".$this->queryArgument."/calendar.json?apikey=".$this->apikey."&page=".$page."&per_page=".$resultsPerPage;
        $this->_getJsonResponse($curl_string, $obj);
                  
        # FOREACH EVENT ADD IT TO THE data_array
        # OVERWRITE AS NECESSARY SO ONLY UNIQUE VALUES ARE AVAILABLE AT THE END        
        foreach ($obj['resultsPage']['results']['event'] as $event ) {
 
          $venue = $event['venue']['displayName'];
          $sk_venue_id = $event['venue']['id'];
          
          #CHECK IF THE ENTRY ALREADY EXISTS 
          if (!$this->_multiArrayKeyExists($this->data_array,"id",$sk_venue_id)) {
            $the_array=array("id"=>$sk_venue_id,"venue"=>$venue);
            array_push($this->data_array, $the_array);
            #echo "NEW $counter $venue<br>";
            #$counter++;         
          } else {
            #echo "DUPLICATE $counter $venue<br>";
            #$counter++; 
          }      
        }
      }
          
      //SORT THE ARRAY ALPHABETICALLY BY displayName        
      $this->_aasort($this->data_array,"venue");
      //REINDEX THE ARRAY SO THE SORT ORDER IS MAINTAINED BY THE JAVASCRIPT
      $this->data_array = array_values($this->data_array);
      //print_r($this->data_array);
      
      #CREATE THE CACHE FILE
      $this->_writeCache();
      
    }
   


    //HIDDEN APIS
    //http://www.songkick.com/developer/venue-search


}

//CREATE A NEW INSTANTIATION FOR PORTLAND
//$venues1 = new Venues('12283', $apikey);
//$venues1->getMatching();
//echo json_encode($venues1->data_array);
