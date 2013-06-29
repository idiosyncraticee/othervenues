<?php

class CrossValidate {

    public $artistVenueMatrix = array();
    private $venueArtistMatrix = array();
    private $artistArray = array();
    private $numberArtistsToValidate = 10;
    private $numberVenuesToLearnOn = 2;
    private $maxLoops = 1000;
    #private $scoringMethod = "euclidean";
    private $scoringMethod = "euclideanNormalized";
    
    public function __construct($arg1, $arg2) {
      $this->venueArtistMatrix = $arg1;
      $this->apikey = $arg2;
    }

    public function buildArtistArray() {

      foreach ($this->venueArtistMatrix as $key => $value ) {
        array_push($this->artistArray, $key);
      }
    } 
    
    public function buildDesiredVenuesArray($venuesPlayedAt, &$preferedVenuesArray, &$leaveOneOutVenuesArray) {
      arsort($venuesPlayedAt);
      $counter = 0;
      foreach ($venuesPlayedAt as $key => $value ) {
        echo "\tTHE ARTIST HAS PLAYED AT $key $value TIMES<br>";
        
        #THE MAGNITUDE IS THE VALUE ASSIGNED FOR THE LEARNING
        # #FOR NOW THE MAGNITIDUE IS ALWAYS 1 OR 0        
        $magnitude = 1;
        if ($counter < $this->numberVenuesToLearnOn ) {
          $preferedVenuesArray[$key]=$magnitude;
          $counter++;
        } else {
          $leaveOneOutVenuesArray[$key]=$magnitude;
          $counter++;
        }                                        
      }
    }     
        
    public function runCrossValidation() {
    
      $this->buildArtistArray();
      $checkedArtistsArray=array();
      $loopCounter=0;
      
      for ($i=1; $i<=$this->numberArtistsToValidate; $i++) {
        $randomArtist = rand(0,count($this->venueArtistMatrix)-1);
        $artistId = $this->artistArray[$randomArtist];
        $numberVenuesPlayedAt = count($this->venueArtistMatrix[$artistId]);
        if($numberVenuesPlayedAt <= $this->numberVenuesToLearnOn+1 || array_key_exists($artistId,$checkedArtistsArray)) {
          #echo "ARTIST $artistId HAS PLAYED AT $numberVenuesPlayedAt VENUES<br>";
          #DONT COUNT THIS ONE
          $i--;
          continue;
        } else {
        //http://api.songkick.com/api/3.0/artists/6902264/calendar.json?apikey=8uYJjMmzAcuaegIO
          echo "ARTIST <a href='http://api.songkick.com/api/3.0/artists/".$artistId."/calendar.json?apikey=$this->apikey'>$artistId</a> HAS PLAYED AT $numberVenuesPlayedAt VENUES<br>";
          $checkedArtistsArray[$artistId]=1;
          #BUILD THE DESIRED VENUES ARRAY
          $preferedVenuesArray = array ();
          $leaveOneOutVenuesArray = array ();           
          $this->buildDesiredVenuesArray($this->venueArtistMatrix[$artistId], $preferedVenuesArray, $leaveOneOutVenuesArray);
          #print_r($preferedVenuesArray);
          #echo "<br><br>";
          #print_r($leaveOneOutVenuesArray);
          
          #GENERATE RECOMMENDATIONS BASED ON PREFERENCES
          $similarityScoring = new SimilarityScoring($this->venueArtistMatrix, $preferedVenuesArray);
          #$similarityScoring->calculateSimilarityScores('euclidean');
          
          $excludeArtistArray[$artistId]=1;          
          $similarityScoring->calculateSimilarityScores($this->scoringMethod, $excludeArtistArray);        
          $recommendations = new Recommendations($this->venueArtistMatrix,$preferedVenuesArray,$similarityScoring->similarityScoresArray, $this->apikey);
          $recommendations->matchOtherArtists();

          $hitCounter=0;
          $missCounter=0;
          #SEE IF ANY OF THE leaveOneOut IS PART OF THE RECOMMENDATION
          foreach ($leaveOneOutVenuesArray as $key => $value) {
            if(array_key_exists($key, $recommendations->bestMatchingVenuesArray)) {
              echo "THERE IS A MATCH FOR ARTIST $artistId AT VENUE $key WITH SCORE OF $value<br>";
              $hitCounter++;  
            } else {
              echo "NO MATCH FOR ARTIST $artistId AT VENUE $key WITH SCORE OF $value<br>";
              $missCounter++; 
            }
          }
          $countRecommendations = count($recommendations->bestMatchingVenuesArray);
          echo "FOR ARTIST $artistId THERE ARE $hitCounter HITS and $missCounter MISSES WITH $countRecommendations RECOMMENDATIONS TOTAL<br><br>";
          
                    
        }
        $loopCounter++;
        
        if($loopCounter > $this->maxLoops) {
          echo "NO ARTISTS WITH THAT MANY VENUES";
          return;
        }
      }
      
      
    }     

}

require "VenuesArtists.php";
require "SimilarityScoring.php";
require "Recommendations.php";
  
$venuesArtists = new VenuesArtists('12283', $apikey);
$venuesArtists->getMatching();



$crossValidate = new CrossValidate($venuesArtists->data_array, $apikey);
$crossValidate->runCrossValidation();


//print_r($venuesArtists->venueArtistMatrix); 