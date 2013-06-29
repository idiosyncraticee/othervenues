<?php


// echo basename(__FILE__);
// echo "<br>";
// echo $_SERVER['PHP_SELF'];
// echo "<br>";
// print_r($_SERVER);
// echo "<br>";
// if(preg_match('/' . basename(__FILE__) . '/', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
require_once "apikey.php";
require "Metro.php";
require "Venues.php";
require_once "VenueDetails.php";
require "VenuesArtists.php";
require "SimilarityScoring.php";
require "Recommendations.php";
#require "old_stuff.php";



#GET ALL OF THE PARAMETERS PASSED BY AJAX
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'none';
$search_city = isset($_GET['city']) ? $_GET['city'] : 'none';
$city_id = isset($_GET["city_id"]) ? $_GET['city_id'] : 'none';
$venue1 = isset($_GET["venue1"]) ? $_GET['venue1'] : 'none';
$venue2 = isset($_GET["venue2"]) ? $_GET['venue2'] : 'none';
$venue3 = isset($_GET["venue3"]) ? $_GET['venue3'] : 'none';
$debug_venue_id = isset($_GET["debug_venue_id"]) ? $_GET['debug_venue_id'] : 'none';

#GET A LIST OF CITIES BASED ON THE search_city
if (strcmp($mode,"cities")==0 && strcmp($search_city,'none')!=0) {
  //echo "PREPARE TO GET THE CITY ID<BR>\n";
  
  //CREATE A NEW INSTANTIATION
  $metro = new Metro($search_city,$apikey);
  $metro->getMatching();
    
  echo json_encode($metro->data_array);

#GET A LIST OF ALL THE VENUES IN THE CHOSEN CITY  
} else if (strcmp($mode,"venues")==0 && strcmp($city_id,'none')!=0 ) {

  //CREATE A NEW INSTANTIATION
  $venues1 = new Venues($city_id,$apikey);
  $venues1->getMatching();
    
  echo json_encode($venues1->data_array);

#GET A LIST OF MATCHING VENUES TO THE SELECTED VENUE
}  else if (strcmp($mode,"venue_match")==0 && strcmp($venue1,'none')!=0 && strcmp($city_id,'none')!=0) {                                

  #GET THE DATA ABOUT ALL OF THE VENUES AND ARTISTS THAT HAVE PLAYED THERE  
  $venuesArtists = new VenuesArtists($city_id,$apikey);
  $venuesArtists->getMatching();  
   
 
  #CREATE THE DESIRED VENUES ARRAY BASED ON USER PREFERENCES  
  $desiredVenuesArray = array($venue1 => 1, $venue2 => 1, $venue3 => 1);

  
  #GET THE SIMILARITY SCORES BETWEEN THE DESIRED VENUE ARRAY, AND EVERY ARTIST   
  $similarityScoring = new SimilarityScoring($venuesArtists->data_array, $desiredVenuesArray);
  #$similarityScoring->calculateSimilarityScores('euclidean');
  $similarityScoring->calculateSimilarityScores('euclideanNormalized',array());

  $venues1 = new Recommendations($venuesArtists->data_array,$desiredVenuesArray,$similarityScoring->similarityScoresArray, $apikey);
  $venues1->matchOtherArtists();
  $venues1->returnMatchingVenueInfo();
    
  //get_recommendations($venue_artist_matrix, $venues_to_match, $artist_match_scores_array);
  
  #GET THE VENUES THAT MATCH THE MOST SIMILAR BY SCORE BUT ARENT PART OF THE LIST
  #$best_matching_venues = array();
  
  #match_other_artists($venuesArtists->venueArtistMatrix,$desired_venues_array,$similarityScoring->similarityScoresArray, $best_matching_venues);

  #return_matching_venue_info($best_matching_venues, $apikey);

} else if (strcmp($mode,'none')==0) {
  echo "ERROR: MODE NOT PROVIDED";
  return;
}  else {
  echo "ERROR: WRONG PARAMETERS";
  return;
}

      





 