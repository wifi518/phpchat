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
    if ( username != '' ) {
      writeMessage( e.data );
    }
  }

}

var buildMessage = function( type, msg ) {
  var mObject = {type:type, msg:msg }
  return JSON.stringify(mObject);
}



$( document ).ready( function() {
  startSocketConnection();

  $( 'form' ).on( 'submit', function( e ) {
    e.preventDefault();
    var userinput = $('#m').val();
    if ( userinput != '' ) {
      if( username == '' ) {
        username = userinput;

        websocket.send( buildMessage('login',username));
        $( '#m' ).attr( 'placeholder', 'Deine Nachricht...' );
      } else {
        switch( userinput ) {
          case '/exit':
            websocket.send( buildMessage('logout',''));
            username = '';
            $( '#m' ).attr( 'placeholder', 'Dein Name' );
          break;
          case '/users':
            websocket.send( buildMessage('userlist',''));
          break;
          default:
            websocket.send( buildMessage('message',userinput));
        
        }

      }
    }
    $('#m').val('').trigger('focus');
  });
});
