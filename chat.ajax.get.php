<?php
  if ( !isset( $_POST['ms'] ) ) exit;

  header( 'Content-Type:application/json' );

  $ms = $_POST['ms'];

  $filename = 'chat.txt';
  $fh = fopen( $filename, 'r');
  if ( filesize( $filename ) == 0 ) {
    echo '{"msgs":[],"timestamp":0}';
    exit;
  }
  $content = fread( $fh, filesize( $filename ) );
  fclose( $fh );

  $lines = explode( "\n", $content );
  foreach( $lines as $k => $line ) {
    if ( $line == '' ) continue;
    $lines[$k] = explode( ';', $line );
  }

  $response = new stdClass(); // leeres Objekt
  $response->msgs = array();
  $response->timestamp = 0;

  foreach( $lines as $line ) {
    if ( $line == '' ) continue; // letzte Zeile ist leer
    if ( $line[0] < $ms ) continue;
    $response->msgs[] = $line[1];
    $response->timestamp = (int) $line[0];
  }


  echo json_encode( $response );
