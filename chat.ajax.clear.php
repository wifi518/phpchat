<?php
  $filename = 'chat.txt';
  $fh = fopen( $filename, 'w');
  fwrite( $fh, '' );
  fclose( $fh );
