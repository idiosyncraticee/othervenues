<?php

require_once "SongkickData.php";

Class Recommendations extends SongkickData {

    private $search_string;
    protected $apikey;
    public $bestMatchingVenuesArray = array();
    
    public function __construct($arg1, $arg2, $arg3, $arg4) {
        $this->data_array = (array) $arg1;
        $this->desiredVenuesArray = $arg2;
        $this->similarityScoresArray = $arg3;
        $this->apikey = $arg4;
    }

    public function matchOtherArtists() {
    
      //echo "BEGIN MATCHING ARTISTS TO VENUES THE VENUES ARRAY\n<br>";
      #SETTINGS
      $artists_to_match = 10;
      $venues_to_match = 10;
      
      #COUNTERS
      $artist_match_counter = 0;
      $venue_match_counter = 0;
      
      #FLAGS
      $enough_venues = 0;
      
      #INITIALIZATION
      $venue_match_array = array();
      
      #TODO: DONT PULL TWICE 
      
      foreach ($this->similarityScoresArray as $artist_id => $artist_array) { #FOREACH ARTIST THAT IS SCORED.  NOTE THAT ARRAY IS ALREADY SORTED DESCENDING AT THIS POINT
        foreach ($this->data_array[$artist_id] as $venue_id => $venue_array) { #FOREACH VENUE FOR THIS ARTIST WITH THE HIGH MATCH SCORE
          #LOOP ABORT CRITERIA
          if($enough_venues) {
            break;
          }
          
          //echo "VID=$venue_id<br>\n";
          if (array_key_exists($venue_id,$this->desiredVenuesArray)) {  #IF THE ARTIST HAS ALREADY PLAYED AT THIS VENUE SKIP IT
            //echo "THE VENUE ARRAY ALREADY CONTAINS $venue_id, SAME AS $artist_id<br>\n";
            continue;
          } elseif (!array_key_exists($venue_id,$venue_match_array)) {
            //echo "GONNA SAY $venue_id IS A GOOD MATCH FOR THE ARRAY OF VENUES BASED ON ARTIST $artist_id<br>\n";
            $venue_match_array[$venue_id]=1;
          } else {
            
            $venue_match_array[$venue_id]+=1;
            //echo "VENUE MATCH FOR $venue_id FROM SOME OTHER ARTIST $artist_id<br>\n";
            $venue_match_counter++; 
            #IF THE MAXIMUM NUMBER OF VENUES TO MATCH IS GRABBED THEN WE ARE DONE
            if($venue_match_counter >= $venues_to_match) {
              $enough_venues=1;
            } 
          }
          $artist_match_counter++;
          if($artist_match_counter >= $artists_to_match) {
            break;
          } 
        }  
      
      }
      
      arsort($venue_match_array);
      
      $matchingVenuesCounter=0;
      foreach ($venue_match_array as $venue_id => $venue_array) {
        if($matchingVenuesCounter>=$this->numberVenuesToMatch) {
          continue;
        }                
        //echo "VENUEID $venue_id SCORE = $venue_match_array[$venue_id]  \n<br>";
        $this->bestMatchingVenuesArray[$venue_id]=$venue_match_array[$venue_id];

        $matchingVenuesCounter++;    
      }     
      //echo "END MATCHING TO VENUES\n<br>";  
    }

    public function returnMatchingVenueInfo() {
      //echo "GOING TO PRINT THE BEST MATCHING VENUE IN JSON<br>\n";
      
      $json_venue_array = array();

      foreach ($this->bestMatchingVenuesArray as $venue_id => $score ) {
          
          if(!isset($venue_id) || strcmp($venue_id, "")==0) {
            #FOR WHATEVER REASON THERE IS NO INFORMATION ABOUT THIS VENUE
            #TODO: TRACK DOWN WHY THIS CONDITION HAPPENS
            echo "ERROR: NO VENUEID DEFEINED, BUT THE SCORE IS $best_matching_venues[$venue_id]<br>\n";
            continue;
          }
          

          //echo "FOR THIS ROUND THE VENUE ID IS $venue_id<br>\n";
          //GET SOME BONUS VENUE INFO
          $venueInfo = new VenueDetails($venue_id,$this->apikey);
          $venueInfo->getMatching(); 

          $displayName = $venueInfo->data_array["displayName"];
          //echo "FOR THIS ROUND THE VENUE ID IS $venue_id AND THE DISPLAY NAME IS $displayName<br>\n";
          $this_json_venue_array=array("venueId"=>$venue_id, "displayName"=>$venueInfo->data_array["displayName"], "uri"=>$venueInfo->data_array["uri"], "website"=>$venueInfo->data_array["website"], "score"=>$this->bestMatchingVenuesArray[$venue_id]);          
          
          array_push($json_venue_array, $this_json_venue_array);
      }

      echo json_encode($json_venue_array);  
    }
        
}

// require_once "VenuesArtists.php";
// require_once "SimilarityScoring.php";
// require_once "VenueDetails.php";
// 
// $venuesArtists = new VenuesArtists('12283',$apikey);
// $venuesArtists->getMatching(); 
// 
// #CREATE THE DESIRED VENUES ARRAY BASED ON USER PREFERENCES  
// $desired_venues_array = array('35260' => 1, '2333739' => 1, '75075' => 1);
// 
//   
// #GET THE SIMILARITY SCORES BETWEEN THE DESIRED VENUE ARRAY, AND EVERY ARTIST   
// $similarityScoring = new SimilarityScoring($venuesArtists->data_array, $desired_venues_array);
// $similarityScoring->calculateSimilarityScores('euclideanNormalized',array());
// //print_r($similarityScoring->similarityScoresArray);  
// $venues1 = new Recommendations($venuesArtists->data_array,$desired_venues_array,$similarityScoring->similarityScoresArray, $apikey);
// $venues1->matchOtherArtists();
// print_r($venues1->bestMatchingVenuesArray);
//       
// $venues1->returnMatchingVenueInfo();

