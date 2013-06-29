
//DISPLAY BLOCK FOR THE VENUE INFO
function makeVenueBlock (url, displayName, score, website, city) {
  
  //IF NOBODY HAS LINKED TO THE VENUE WEBSITE JUST GO STRAIGHT TO A GOOGLE SEARCH
  if(website === null) {
    website_link = "<a target='_blank' href='http://www.google.com/search?q="+escape(displayName)+" "+city+"'>"+displayName+"</a>";  
  } else {
    website_link = "<a target='_blank' href='"+website+"'>"+displayName+"</a>";  
  }
  var tableString = $('<tr><td>'+score+'</td><td>'+website_link+'</td></tr>');
  return tableString;
}                 