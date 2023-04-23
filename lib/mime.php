<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $mime_types;

// A list of MIME types by file extension.
$mime_types = json_decode(file_get_contents(__DIR__.'/mime.json'), true);

/**
 * Guesses the MIME type for a file by its extension.
 * 
 * If the MIME type cannot be guessed, a default is returned instead.
 */
function guess_mime_type($filename, $default = 'application/octet-stream') {
  global $mime_types;

  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $mime_value = $mime_types[$ext];
  $mime = empty($mime_value) ? $default : $mime_value;
  return [$mime, $ext];
}
