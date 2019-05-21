function createMessage( msg, status ) {
  if ( status ) {
    //msg = $( '<em>').html( msg );
    msg = '<em>'+msg+'</em>';
  }
  //$( '<li>' ).append( msg ).appendTo( '#messages' );
  $.ajax({
    url:'http://localhost/tag20/phpchat/chat.ajax.send.php',
    method:'POST',
    data:{ msg: msg },
    success:function( data ) {
      if ( letzterAbruf == 0 ) {
        letzterAbruf = data;
        getMessages(); // 1. Mal
      }
    },
    error: function( e ) {
      $( '<li>' )
        .append( '<b style="color:red">Verbindungsfehler</b>' )
        .appendTo( '#messages' );
    }
  })
}

function getMessages() {
  $.ajax({
    url:'http://localhost/tag20/phpchat/chat.ajax.get.php',
    method:'POST',
    data:{ ms: letzterAbruf },
    success:function( data ) {
      console.log( data );
      if ( data.timestamp != 0 ) {
        letzterAbruf = data.timestamp + 1;
      }
      for ( i in data.msgs ) {
        $( '<li>' ).html( data.msgs[i] ).appendTo( '#messages' );
      }
      setTimeout( function() { // in 10s wieder Nachrichten holen
        getMessages();
      },1000)
    },
    error: function( e ) {
      $( '<li>' )
        .append( '<b style="color:red">Verbindungsfehler</b>' )
        .appendTo( '#messages' );
    }
  });

}


var username = '';
var letzterAbruf = 0;

$( document ).ready( function() {

  $( 'form' ).on( 'submit', function( e ) {
    e.preventDefault();
    var userinput = $('#m').val();
    if ( userinput != '' ) {
      if ( username == '' ) {
        createMessage( userinput+' hat sich eingeloggt.', true );
        $( '#m' ).attr('placeholder','Deine Nachricht ...' );
        username = userinput;
      } else {
        switch ( userinput ) {
          case '/clear':
            $.ajax({
              url:'http://localhost/tag20/phpchat/chat.ajax.clear.php',
            });

            $( '#messages' ).empty().append(
              $( '<li>' ).html( '<em>Verlauf geleert</em>')
            );

            break;
          default:
            createMessage( '<b>'+username+' sagt:</b> '+userinput ,false );
        }
      }
    }
    $('#m').val('').trigger('focus');
  });

});
