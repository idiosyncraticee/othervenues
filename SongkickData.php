<?php

require_once "apikey.php";

class SongkickData {

  public $useCache = 1;
  protected $debugFlag = 1;
  protected $numberVenuesToMatch = 10;
    
  protected function _cacheName() {
    
    $callingClass = $this->called_class;
    #TODO: GET THIS WORKING FOR FILENAMES WITH SPACES IN THEM LIKE My Documents
    $filename = "caches/${callingClass}_cache_".$this->queryArgument.".txt";
    $filename = str_replace(" ", "_", $filename);
    
#    $this->metroCache = $filename;
    return $filename;  
  }

  public function getMatching() {
    
  if(!$this->useCache) {
    $this->_getMatchingFromApi();
  } else {
    #TRY TO GET THE CACHE.  IF IT DOESNT EXIST USE THE API
    if(!$this->_getMatchingFromCache()) {
      $this->_getMatchingFromApi();  
    }
  }
}
    
  protected function _getMatchingFromCache() {
    
    #DOES THE CACHE FILE EXIST?
    if (file_exists($this->_cacheName())) {
      $this->data_array = json_decode(file_get_contents($this->_cacheName() ), true);
      return 1;
    } else {
      return 0;
    }
  }

  protected function _writeCache() {

    file_put_contents($this->_cacheName(), json_encode($this->data_array));
  }

    
  protected function _getJsonResponse($curl_string, &$obj) {
    //THIS API CALL LOOK FOR UPCOMING EVENTS
    $curl_string = str_replace(" ", "%20", $curl_string);
    $curl_string = str_replace("+", "%20", $curl_string);
    $ch = curl_init($curl_string);
    //$fp = fopen("example_homepage.txt", "w");
    
    //curl_setopt($ch, CURLOPT_FILE, $fp);
    
    #DONT ECHO THE CURL RETURN
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    
    $contents = curl_exec($ch);
    curl_close($ch);
    //fclose($fp);
    
    $obj = json_decode($contents, true);
    //var_dump($obj->{'resultsPage'}->{'results'});
    if(strcmp($obj['resultsPage']['status'], "ok")!=0 ) {  
      echo "SOMETHING WENT WRONG IN THE QUERY AND THE STATUS IS ".$obj['resultsPage']['error']['message']."<br>\n";
      if(!$debugFlag) {
        echo "THE ISSUED QUERY IS: $curl_string";
        var_dump($obj);
      }
      die;
      return 0;
    } else {
      return 1;
    }
  }
              
  protected function _aasort (&$array, $key) {
      $sorter=array();
      $ret=array();
      reset($array);
      foreach ($array as $ii => $va) {
          $sorter[$ii]=$va[$key];
      }
      asort($sorter);
      foreach ($sorter as $ii => $va) {
          $ret[$ii]=$array[$ii];
      }
      $array=$ret;
  }

  protected function _multiArrayKeyExists($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
  }
        
}