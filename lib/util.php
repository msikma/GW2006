<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use \Random\Randomizer;
use Cocur\Slugify\Slugify;

/**
 * Generates a slug from a string.
 */
function slug($str) {
  $slugify = new Slugify();
  return $slugify->slugify($str, '_');
}

/**
 * Generates an amount of randomly assigned integers without duplicates.
 */
function generate_random_numbers($amount, $min, $max) {
  $randomizer = new Randomizer();
  $list = range(1, $max);
  $list = $randomizer->shuffleArray($list);
  return array_slice($list, 0, $amount);
}

/**
 * Shuffles an array randomly and returns the shuffled array.
 */
function shuffle_array_securely($array) {
  $randomizer = new Randomizer();
  return $randomizer->shuffleArray($array);
}
