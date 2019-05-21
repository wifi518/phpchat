var username = '';
var websocket;

function writeMessage( msg ) {
  $( '<li>' ).html( msg ).appendTo( '#messages' );
}

function startSocketConnection() {
  websocket = new WebSocket("ws://localhost:5001");
  websocket.onopen = function() {
    writeMessage( 'Verbindung zum Server hergestellt.' );
  }
  websocket.onclose = function() {
    writeMessage( 'Verbindung zum Server getrennt.' );
  }
  websocket.onerror = function() {
    writeMessage( 'Verbindungsfehler.' );
  }
  websocket.onmessage = function( e ) {
    writeMessage( e.data );
  }
}

$( document ).ready( function() {
  startSocketConnection();
  
  $( 'form' ).on( 'submit', function( e ) {
    e.preventDefault();
    var userinput = $('#m').val();
    if ( userinput != '' ) {
      if( username == '' ) {
        username = userinput;

        websocket.send( username + ' hat sich eingeloggt.' );
      } else {
        websocket.send( '<b>'+username+' sagt:</b>' + userinput );
      }
    }
    $('#m').val('').trigger('focus');
  });
});
