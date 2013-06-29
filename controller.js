// JavaScript Document

var city_id = "";


$( document ).ready(function() {
    $("#loading").hide();
    $("#simulation_response").hide();
    $('.citySelect').hide();
    $('.venueSelects').hide();
    $('#venue2').hide();
    $('#venue3').hide();
    $.ajaxSetup({
        beforeSend: function(){
            // show gif here, eg:
            $("#loading").show();
        },
        complete: function(){
            // hide gif here, eg:
            $("#loading").hide();
        }
    });
  $( "#city_button" ).click(function( event ) {
   // Using the core $.ajax method
    $.ajax({
        // the URL for the request
        url: "songkick_api.php",
     
        // the data to send (will be converted to a query string)
        // TODO: SUPPORT SPACES IN THE CITY NAME LIKE SAN JOSE
        data: {
            mode: "cities",
            city: $("#city_text").val()
        },
     
        // whether this is a POST or GET request
        type: "GET",
     
        // the type of data we expect back
        dataType : "json",
     
        // code to run if the request succeeds;
        // the response is passed to the function
        success: function( json ) {
            $('#cbCity').empty();
            $( ".cityText" ).hide();
            $('.citySelect').show();
            $("select#cbCity").append( $("<option>")
                  .val(this.id)
                  .html("... pick one")
              );
            $.each(json, function() {                  
              $('#cbCity').val(json);
              $("select#cbCity").append( $("<option>")
                  .val(this.id)
                  .html(this.city)
              );  
            });
            
        },
     
        // code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, error_thrown ) {
            alert( "Sorry, there was a problem: "+status+" : "+error_thrown );
        },
     
    });
    event.preventDefault();
  });
  $('#cbCity').change(function(event) {

    $("select option:selected").each(function () {
              //str += $(this).val() + " ";
              city_id = $(this).val();
              
    });

    //DISPLAY INFO FOR THE VENUE AFTER IT IS SELECTED
    //TODO: MAKE THIS FOREACH venueBlockSelect INSTEAD OF HARDCODING IT
    
    //TURNED OFF THIS CODE BECAUSE WE DONT HAVE VERY MUCH VENUE INFO 
    //    BUT IT DOESNT MATTER BECAUSE A USER ALREADY KNOWS ABOUT THIS PLACE
//     $(".venueBlockSelect").each(function() {
//       $('#venue1').change(function(event) {
//         $("#venue1Table").empty();
//           var table_obj = $('#venue1Table');
//           //str += $(this).val() + " ";
//           var city_id = $(this).val();
//           //var cityName = $(this).name();
//          var tableResult = makeVenueBlock("XXX", $(this).val(), "this.score");
//          table_obj.append(tableResult);
//       });
//     });
    
   // Using the core $.ajax method
    $.ajax({
        // the URL for the request
        url: "songkick_api.php",
     
        // the data to send (will be converted to a query string)
        data: {
            mode: "venues",
            city_id: $(this).val()
        },
     
        // whether this is a POST or GET request
        type: "GET",
     
        // the type of data we expect back
        dataType : "json",
     
        // code to run if the request succeeds;
        // the response is passed to the function
        success: function( json ) {
             $('#venue1').empty();
             $('#venue2').empty();
             $('#venue3').empty();
             $('.venueSelects').show();
             $("select#venue1").append( $("<option>")
                  .val(this.id)
                  .html("... pick one")
              );
              $("select#venue2").append( $("<option>")
                  .val(this.id)
                  .html("... pick one")
              );
              $("select#venue3").append( $("<option>")
                  .val(this.id)
                  .html("... pick one")
              );
            $.each(json, function() {

              $('#venue1').val(json);
                    $("select#venue1").append( $("<option>")
                  .val(this.id)
                  .html(this.venue)
              );
              $('#venue2').val(json);
                    $("select#venue2").append( $("<option>")
                  .val(this.id)
                  .html(this.venue)
              ); 
              $('#venue3').val(json);
                    $("select#venue3").append( $("<option>")
                  .val(this.id)
                  .html(this.venue)
              );   
            });
            
        },
     
        // code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status ) {
            alert( "Sorry, there was a problem: "+status );
        },
     
    });
    event.preventDefault();
  });

  $('#venue1').change(function() {

    $("#simulation_button").toggleClass("disabled btn-success");
    $("#venue2").show();
    
  });
  $('#venue2').change(function() {
    $("#venue3").show();
  });
  
  $( "#simulation_button" ).click(function( event ) {
  

   // Using the core $.ajax method
    $.ajax({
        // the URL for the request
        url: "songkick_api.php",
     
        // the data to send (will be converted to a query string)
        data: {
            mode: "venue_match",
            venue1: $("#venue1").val(),
            venue2: $("#venue2").val(),
            venue3: $("#venue3").val(),
            city_id: $("#cbCity").val()
        },
     
        // whether this is a POST or GET request
        type: "GET",
     
        // the type of data we expect back
        dataType : "json",
     
        // code to run if the request succeeds;
        // the response is passed to the function
        success: function( json ) {
          //alert( "A MODICUM OF SUCCESS");
           $("#simulation_response").empty();
            $("#simulation_response").show();      
            var table_obj = $('#simulation_response');
            var scoreCounter=0;
            table_obj.append($('<tr><th>Score</th><th>Venue</th></tr>'));
            var city_name = $('#cbCity option:selected').text();

            $.each(json, function() {

                 //table_obj.append($('<tr><td>'+this.venueId+'</td><td>'+this.displayName+'</td><td>'+this.score+'</td></tr>'));
                  var tableResult = makeVenueBlock(this.url, this.displayName, this.score, this.website, city_name); 
                  table_obj.append(tableResult);

            }) 
            
        },
     
        // code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status ) {
            alert( "Sorry, there was a problem: "+status );
        },
     
    });
    event.preventDefault();
  });  
}); 