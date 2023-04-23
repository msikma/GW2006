<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use Cocur\Slugify\Slugify;

/**
 * Generates a slug from a string.
 */
function slug($str) {
  $slugify = new Slugify();
  return $slugify->slugify($str, '_');
}
