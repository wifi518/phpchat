<?php
  if ( !isset( $_POST['msg'] ) ) exit;

  $msg = $_POST['msg'];

  $filename = 'chat.txt';
  $fh = fopen( $filename, 'a');
  $time = time();
  $line = $time.';'.$msg.'
';
/*
  if ( strpos( $line, "/clear" ) !== false ) {
    $line = '';
  }
*/

  fwrite( $fh, $line );
  fclose( $fh );
  echo $time;
