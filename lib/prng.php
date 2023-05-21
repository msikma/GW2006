<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Returns a random integer using a string as seed.
 */
function getRandomIntBySeed($seedString, $min, $max) {
  $seed = xmur3($seedString);
  $prng = sfc32($seed(), $seed(), $seed(), $seed());
  return randInt($prng, $min, $max)();
}

/**
 * Returns a pseudo-random integer between [min, max) using a prng.
 */
function randInt($rand, $min, $max) {
  return function () use ($rand, $min, $max) {
    return floor($rand() * ($max - $min) + $min);
  };
}

/**
 * Performs an unsigned right bitwise shift.
 * 
 * Equivalent to Javascript's >>> operator.
 * 
 * <https://stackoverflow.com/a/43359819>
 */
function urs($a, $b) {
  if ($b >= 32 || $b < -32) {
    $m = (int)($b / 32);
    $b = $b - ($m * 32);
  }

  if ($b < 0) {
    $b = 32 + $b;
  }

  if ($b === 0) {
    return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
  }

  if ($a < 0) {
    $a = ($a >> 1);
    $a &= 0x7fffffff;
    $a |= 0x40000000;
    $a = ($a >> ($b - 1));
  }
  else {
    $a = ($a >> $b);
  }
  return $a;
}

/**
 * MurmurHash3's mixing function for creating seeds.
 * 
 * See: <https://stackoverflow.com/a/47593316>
 */
function xmur3($str) {
  $h = 1779033703 ^ strlen($str);
  for ($i = 0; $i < strlen($str); $i++) {
    $h = (($h ^ ord($str[$i])) * 3432918353);
    $h = ($h << 13) | urs($h, 19);
  }
  return function () use (&$h) {
    $h = (($h ^ urs($h, 16)) * 2246822507);
    $h = (($h ^ urs($h, 13)) * 3266489909);
    return urs(($h ^= urs($h, 16)), 0);
  };
}

/**
 * Simple Fast Counter implementation.
 * 
 * Part of the PractRand random number testing suite. Has a 128-bit state.
 * 
 * See: <https://stackoverflow.com/a/47593316>
 */
function sfc32($a, $b, $c, $d) {
  return function () use (&$a, &$b, &$c, &$d) {
    $a = urs($a, 0);
    $b = urs($b, 0);
    $c = urs($c, 0);
    $d = urs($d, 0);
    $t = ($a + $b) | 0;
    $a = ($b ^ urs($b, 9));
    $b = ($c + ($c << 3)) | 0;
    $c = (($c << 21) | urs($c, 11));
    $d = ($d + 1) | 0;
    $t = ($t + $d) | 0;
    $c = ($c + $t) | 0;
    return urs($t, 0) / 4294967296;
  };
}
