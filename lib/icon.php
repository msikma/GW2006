<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/mime.php');

global $mime_icons, $type_icons;

$mime_icons = [
  'application/gzip' => 'compressed.png',
  'application/json' => 'json.png',
  'application/mac-binhex40' => 'binhex.png',
  'application/msword' => 'quill.png',
  'application/octet-stream' => 'binary.png',
  'application/pdf' => 'pdf.png',
  'application/postscript' => 'a.png',
  'application/postscript' => 'ps.png',
  'application/vnd.rn-realmedia' => 'movie.png',
  'application/x-7z-compressed' => 'compressed.png',
  'application/x-dvi' => 'dvi.png',
  'application/x-rar-compressed' => 'compressed.png',
  'application/x-tar' => 'tar.png',
  'application/x-tar' => 'tar.png',
  'application/x-tex' => 'tex.png',
  'application/zip' => 'compressed.png',
  'audio/midi' => 'sound2.png',
  'image/svg+xml' => 'svg.png',
  'model/vrml' => 'world.png',
  'text/html' => 'layout.png',
  'text/plain' => 'text.png',
  'text/x-c' => 'c.png',
  'text/x-fortran' => 'f.png',
  'text/x-pascal' => 'p.png',
  'text/x-uuencode' => 'uu.png',
  'text/xml' => 'xml.png',
  'video/mp4' => 'movie.png',
  'video/quicktime' => 'movie.png',
  'video/x-matroska' => 'movie.png',
  'video/x-msvideo' => 'movie.png',
];

$type_icons = [
  'text' => 'text.png',
  'image' => 'image.png',
  'audio' => 'sound.png',
  'video' => 'movie.png',
];

/**
 * Returns an icon filename and its associated MIME type for a given filename.
 */
function get_filetype_icon($filename, $default = 'binary.png') {
  global $mime_icons, $type_icons;

  list($mime, $ext) = guess_mime_type($filename);

  $parts = explode('/', $mime);
  $base = $parts[0];
  $type = empty($mime) ? $base : $mime;

  $icon_by_mime = $mime_icons[$mime];
  $icon_by_type = $type_icons[$base];

  if ($icon_by_mime) {
    return [$icon_by_mime, $type];
  }
  else if ($icon_by_type) {
    return [$icon_by_type, $type];
  }
  else {
    return [$default, $type];
  }
}
