<?php

#Metro IS A SUBCLASS OF SonkickData
require_once "SongkickData.php";

class VenuesArtists extends SongkickData {

    #FOR THIS CLASS THE queryArgument IS THE CITY ID
    protected $queryArgument;
    private $apikey;
    public $data_array = array(); 
    protected $called_class = "VenuesArtists";
    
    public function __construct($arg1, $arg2) {
        $this->queryArgument = $arg1;
        $this->apikey = $arg2;

    }

    protected function _getMatchingFromApi () {

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
        
        $obj = array();
        $curl_string = "http://api.songkick.com/api/3.0/metro_areas/$this->queryArgument/calendar.json?apikey=$this->apikey&page=$page&per_page=".$resultsPerPage;
        $this->_getJsonResponse($curl_string, $obj);
      
        foreach ($obj['resultsPage']['results']['event'] as $event ) {
          if(isset($event['venue']['id'])) {
            $venue_id = $event['venue']['id'];
          
            foreach ($event['performance'] as $performance ) {
              $artist_id = $performance['artist']['id'];
              
              if(isset($this->data_array[$venue_id][$artist_id])) {
                $this->data_array[$artist_id][$venue_id]+=1;
              } else {
                $this->data_array[$artist_id][$venue_id]=1;
              }
          
            }
          } else {
            #TODO: FIGURE OUT WHY A VENUE ID WOULD BE MISSING
            #echo "ERROR: VENUE ID IS MISSING<br>";
          }
          
        }
      }      
      $this->_writeCache();
      
    }     
}
 
// $venuesArtists = new VenuesArtists(12283, $apikey);
// $venuesArtists->getMatching();
// echo json_encode($venuesArtists->data_array);

