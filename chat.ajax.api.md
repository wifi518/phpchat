# Chat AJAX API

## Nachricht an Server schicken
- URL: http://localhost/tag20/phpchat/chat.ajax.send.php
- Request-Methode: POST
- Request-Format: x-www-urlencoded (Standard)
- Request-Daten:
  * msg [STRING]
- Response-Format: HTML
- Response-Daten:
  * Timestamp [s]

## hole Nachrichten von Server (abrufen)
- URL: http://localhost/tag20/phpchat/chat.ajax.get.php
- Request-Methode: POST
- Request-Format: x-www-urlencoded (Standard)
- Request-Daten:
  * s [Timestamp in s]
- Response-Format: JSON
- Response-Daten:
  * msgs [ARRAY mit STRING]
  * timestamp [s]

## lösche alle Nachrichten
- URL: http://localhost/tag20/phpchat/chat.ajax.clear.php
- Request-Methode: POST
- Request-Format: x-www-urlencoded (Standard)
- Request-Daten:
  * empty
- Response-Format: HTML
- Response-Daten:
  * empty
