<?php
  if ( !isset( $_POST['ms'] ) ) exit;

  $ms = $_POST['ms'];

  $filename = 'chat.txt';
  $fh = fopen( $filename, 'r');
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

  header( 'Content-Type:application/json' );
  echo json_encode( $response );
