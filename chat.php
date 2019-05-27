<?php

  class ChatHandler {

    function send( $message, $who ) {
      global $clientSocketArr;
      //$message = $this->seal( json_encode( array('msg'=>$message) ) );

      $message = $this->seal( $message );

      $len = strlen( $message );
      foreach( $clientSocketArr as $socketId => $clientSocket ) {
        if ( $who == 'all' || $who ==  $socketId ) {
          @socket_write( $clientSocket, $message, $len );
        }
      }
      return true;
    }

    function unseal($socketData) {
  		$length = ord($socketData[1]) & 127;
  		if($length == 126) {
  			$masks = substr($socketData, 4, 4);
  			$data = substr($socketData, 8);
  		}
  		elseif($length == 127) {
  			$masks = substr($socketData, 10, 4);
  			$data = substr($socketData, 14);
  		}
  		else {
  			$masks = substr($socketData, 2, 4);
  			$data = substr($socketData, 6);
  		}
  		$socketData = "";
  		for ($i = 0; $i < strlen($data); ++$i) {
  			$socketData .= $data[$i] ^ $masks[$i%4];
  		}
  		return $socketData;
  	}

  	function seal($socketData) {
  		$b1 = 0x80 | (0x1 & 0x0f);
  		$length = strlen($socketData);

  		if($length <= 125)
  			$header = pack('CC', $b1, $length);
  		elseif($length > 125 && $length < 65536)
  			$header = pack('CCn', $b1, 126, $length);
  		elseif($length >= 65536)
  			$header = pack('CCNN', $b1, 127, $length);
  		return $header.$socketData;
  	}

  	function doHandshake($received_header,$client_socket_resource, $host_name, $port) {
  		$headers = array();
  		$lines = preg_split("/\r\n/", $received_header);
  		foreach($lines as $line)
  		{
  			$line = chop($line);
  			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
  			{
  				$headers[$matches[1]] = $matches[2];
  			}
  		}

  		$secKey = $headers['Sec-WebSocket-Key'];
  		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
  		$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
  		"Upgrade: websocket\r\n" .
  		"Connection: Upgrade\r\n" .
  		"WebSocket-Origin: $host_name\r\n" .
  		"WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
  		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
  		socket_write($client_socket_resource,$buffer,strlen($buffer));
  	}

  }
  $chatHandler = new ChatHandler();

  define( 'HOST', 'localhost' );
  define( 'PORT', '5001' );
  $null = NULL;

  $clientid = 1;

  $socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
  socket_set_option( $socket, SOL_SOCKET, SO_REUSEADDR, 1);
  socket_bind( $socket, 0, PORT );
  socket_listen( $socket );

  $clientSocketArr = array( $socket );

  echo 'SocketServer auf Port 5001 läuft.
';

  //print_r( $clientSocketArr );

  $socketUser = array();

  while( true ) {
    //sleep(1);
    $newSocketArr = $clientSocketArr;
    socket_select( $newSocketArr, $null, $null, 0, 10);
    if ( in_array( $socket, $newSocketArr ) ) {
      echo "neue Verbindung\n";
      $newSocket = socket_accept( $socket );
      $clientSocketArr[$clientid++] = $newSocket;

      $header = socket_read( $newSocket, 1024 );
      $chatHandler->doHandshake( $header, $newSocket, HOST, PORT);

      $newIndex = array_search( $socket, $newSocketArr );
      unset( $newSocketArr[$newIndex] );

    }
    foreach( $newSocketArr as $socketID => $res ) {
      while( @socket_recv( $res, $socketData, 1024, 0 ) >= 1 ) {

        $message = $chatHandler->unseal( $socketData );

        echo "Client #".$socketID." Nachricht: ".$message."\n";
        $msg = json_decode( $message );

        if ( !isset( $msg->type  ) ) break; // wenn Browserfenster geschlossen wird

        $who = 'all';
        switch( $msg->type ) {
          case 'logout':
            $message4client = $socketUser[ $socketID ]. ' hat sich ausgeloggt.';
            unset($socketUser[$socketID]);
          break;
          case 'login':
              $socketUser[ $socketID ] = $msg->msg;
              $message4client =  $socketUser[ $socketID ]. ' hat sich eingeloggt.';
          break;
          case 'message':
            $message4client = '<b>'.$socketUser[ $socketID ].' sagt:</b> '.$msg->msg;
          break;
          case 'userlist':
              $message4client = 'eingeloggte User: '.implode(',',$socketUser );
              $who = $socketID;
          break;
        }

        $chatHandler->send( $message4client, $who );
        break 2;
      }

      // quit
      $socketData = @socket_read($res, 1024, PHP_NORMAL_READ);
      if ($socketData === false) {
        $chatHandler->send(  $socketUser[ $socketID ]. ' hat sich ausgeloggt.', 'all')  ;
        unset($clientSocketArr[$socketID]);
        unset($socketUser[$socketID]);
      }//
    }



  }
  socket_close( $socket );
