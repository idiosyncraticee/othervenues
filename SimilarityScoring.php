<?php

require_once "SongkickData.php";

class SimilarityScoring extends SongkickData {

    public $similarityScoresArray = array();
    private $dictionary = array();
    private $preferences = array();
    public $data_array = array();
    protected $called_class = "SimilarityScoring";
        
    public function __construct($arg1, $arg2) {
        $this->dictionary = (array) $arg1;
        $this->preferences = $arg2;
        $this->savePreferences();
    }

    public function savePreferences() {
      $storeData['scoring'] = $this->preferences;
      $storeData['date'] = time();
      file_put_contents("data/userQuery.txt", PHP_EOL . json_encode($storeData), FILE_APPEND );  
    }
    
    
    public function calculateSimilarityScores($scoringMethod, $excludeArtistArray) {
    
      #FOREACH ARTIST COMPUTE A MATCH SCORE AGAINST THE LIST OF VENUES
      foreach ($this->dictionary as $artist_id => $artist_array) {     
    
        if(array_key_exists($artist_id,$excludeArtistArray)) {
          continue;
        }
          if($scoringMethod === 'euclidean') {
            $score = $this->_calculateEuclideanScore($artist_id);
          } elseif($scoringMethod === 'euclideanNormalized') {
            $score = $this->_calculateEuclideanNormalizedScore($artist_id);
          }
    
          //echo "ARISTID $artist_id SCORE = $score\n<br>";
          $this->similarityScoresArray[$artist_id]=$score; 
      }
      
      #SORT ARRAY OF OTHER MATCHING ARTISTS
      arsort($this->similarityScoresArray);

    }
    
    private function _calculateEuclideanScore($artist2) {
      //foreach ( $venue_artist_matrix as $venue ) {
      //foreach ($venue_artist_matrix[$artist1] as $artist_id => $venue_array) {
        //echo "The artist_id is $artist_id\n<br>";
        
    
        $venue_score = array();
        $venue_list=array();
    
        #FIGURE OUT THE COMBINED ARRAY OF BOTH VENUE ARTISTS
        foreach ($this->preferences as $venue_id => $venue_array) {
          $venue_list[$venue_id]=1;
        }

        foreach ($this->dictionary{$artist2} as $venue_id => $venue_array) {
          //echo "VENUEID ARTIST 2 = $venue_id<br>";
          $venue_list[$venue_id]=1;
        }                  

        $sum_squared = 0;
        $i=0;
        foreach ($venue_list as $venue_id => $venue_array) {
          $i++;
          
          if (array_key_exists($venue_id, $this->preferences) && array_key_exists($venue_id, $this->dictionary[$artist2])) {
            $squared = pow($this->preferences[$venue_id]-$this->dictionary[$artist2][$venue_id],2);
            
          } elseif (array_key_exists($venue_id, $this->preferences)) {
            $value = $this->preferences[$venue_id];
            $squared = pow($value,2);
          } elseif (array_key_exists($venue_id, $this->dictionary[$artist2])) {
            $value = $this->dictionary[$artist2][$venue_id];
            $squared = pow($value,2);
          } else {
            echo "ERROR: SOMETHING SNUCK INTO THE VENUE ARRAY THAT SHOULDNT HAVE<br>";
          }
                                
          $sum_squared = $sum_squared + $squared;
        }    
        
        $metric = 1/(1+$sum_squared);

        #COMPUTE THE SUM OF SQUARES
        #foreach ($venue_list as $venue_id) {
        #  $venue_score[$venue_id]=1;
        #} 
        return $metric;   
    
    }
    
    private function _calculateEuclideanNormalizedScore($artist2) {
      //foreach ( $venue_artist_matrix as $venue ) {
      //foreach ($venue_artist_matrix[$artist1] as $artist_id => $venue_array) {
        //echo "The artist_id is $artist_id\n<br>";
        
    
        $venue_score = array();
        $venue_list=array();
        $totalVenuesPreferences=0;
        $totalVenuesArtist2=0;
    
        #FIGURE OUT THE COMBINED ARRAY OF BOTH VENUE ARTISTS
        foreach ($this->preferences as $venue_id => $venue_array) {
          $venue_list[$venue_id]=1;
          $totalVenuesPreferences++;
        }

        foreach ((array) $this->dictionary[$artist2] as $venue_id => $venue_array) {
          //echo "VENUEID ARTIST 2 = $venue_id<br>";
          $venue_list[$venue_id]=1;
          $totalVenuesArtist2++;
        }
          

        $sum_squared = 0;
        $i=0;
        foreach ($venue_list as $venue_id => $venue_array) {
          $i++;
          
          if (array_key_exists($venue_id, $this->preferences) && array_key_exists($venue_id, (array) $this->dictionary[$artist2])) {
            $squared = pow($this->preferences[$venue_id]/$totalVenuesPreferences-$this->dictionary[$artist2][$venue_id]/$totalVenuesArtist2,2);
            
          } elseif (array_key_exists($venue_id, $this->preferences)) {
            $value = $this->preferences[$venue_id]/$totalVenuesPreferences;
            $squared = pow($value,2);
          } elseif (array_key_exists($venue_id, $this->dictionary[$artist2])) {
            $value = $this->dictionary[$artist2][$venue_id]/$totalVenuesArtist2;
            $squared = pow($value,2);
          } else {
            echo "ERROR: SOMETHING SNUCK INTO THE VENUE ARRAY THAT SHOULDNT HAVE<br>";
          }
                                
          $sum_squared = $sum_squared + $squared;
        }    
        
        $metric = 1/(1+$sum_squared);

        #COMPUTE THE SUM OF SQUARES
        #foreach ($venue_list as $venue_id) {
        #  $venue_score[$venue_id]=1;
        #} 
        return $metric;   
    
    }   
        
}

//TESTING
require_once "VenuesArtists.php";
$venuesArtists = new VenuesArtists(12283,$apikey);
$venuesArtists->getMatching();
//print_r($venuesArtists->data_array); 
$desired_venues_array = array("5368963" => 1, "2302484" => 1);
    
$similarityScoring = new SimilarityScoring($venuesArtists->data_array, $desired_venues_array);
$similarityScoring->calculateSimilarityScores('euclideanNormalized',array());
print_r($similarityScoring->similarityScoresArray);