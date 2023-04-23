<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $emoticon_sets, $emoticon_sets_info, $emoticon_basepath;

// A reference to all our emoticons. This gets populated on initialization.
$emoticon_sets = null;
$emoticon_sets_info = null;
// The base path where all emoticons are located, from the theme directory.
$emoticon_basepath = '/images/__gwnew/emoticons';

/**
 * Returns the base emoticon set.
 * 
 * This set will be displayed on the reply page directly.
 */
function get_emoticon_base_set() {
  return 'base';
}

/**
 * Returns the base path where emoticons can be found.
 */
function get_emoticon_basepath() {
  global $emoticon_basepath;
  return $emoticon_basepath;
}

/**
 * Returns metadata about our emoticon sets.
 */
function get_emoticon_sets_info() {
  global $emoticon_sets_info;

  // Retrieve and cache the emoticon sets if we don't have them yet.
  if ($emoticon_sets_info !== null) {
    get_emoticons();
  }

  return $emoticon_sets_info;
}

/**
 * Returns all our emoticon sets.
 */
function get_emoticons() {
  global $settings, $emoticon_sets, $emoticon_sets_info;

  $emoticon_basepath = get_emoticon_basepath();

  // Return cached emoticons if we're already initialized.
  if ($emoticon_sets !== null) {
    return $emoticon_sets;
  }

  $sets = [];
  $sets_info = [];

  $emoticon_url = $settings['theme_url'].$emoticon_basepath;
  $emoticon_path = $settings['theme_dir'].$emoticon_basepath;
  $emoticon_data = json_decode(file_get_contents("$emoticon_path/info.json"), true);

  foreach ($emoticon_data['sets'] as $set) {
    $sets[$set['name']] = [];
    $sets_info[$set['name']] = [
      'name' => $set['name'],
      'display' => boolval($set['display']),
      'title' => $set['title'],
    ];
    $target = &$sets[$set['name']];

    foreach ($set['items'] as $emoticon) {
      $path = '/'.$emoticon['fn'];
      $codes = is_array($emoticon['code']) ? $emoticon['code'] : [$emoticon['code']];
      $target[] = [
        'value' => pathinfo($emoticon['fn'], PATHINFO_FILENAME),
        'code' => $codes,
        'name' => substr($codes[0], 1, -1),
        'size' => $emoticon['size'],
        'set' => $set['name'],
        // "Legacy" indicates that we render the emoticon but don't show it in the list.
        'legacy' => $emoticon['legacy'],
        'path' => $emoticon_path.$path,
        'url' => $emoticon_url.'/'.$set['name'].'/'.$path,
        'basename' => $emoticon['fn'],
      ];
    }
  }

  $emoticon_sets = $sets;
  $emoticon_sets_info = $sets_info;
  
  return $sets;
}

/**
 * Returns a flat list of emoticons, primarily for use by JS.
 * 
 * Also removes all unnecessary information.
 */
function get_flat_emoticons() {
  $emoticon_sets = get_emoticons();
  $emoticons = [];
  
  foreach ($emoticon_sets as $set) {
    foreach ($set as $emoticon) {
      // We don't pass on legacy emoticons to the JS.
      // TODO: rename legacy icons too.
      // TODO: get proper emoticon names like :) :( etc
      if ($emoticon['legacy']) {
        continue;
      }
      $emoticons[$emoticon['name']] = [
        'name' => $emoticon['name'],
        'codes' => $emoticon['code'],
        'set' => $emoticon['set'],
        'size' => $emoticon['size'],
        'file' => $emoticon['basename'],
      ];
    }
  }

  return $emoticons;
}
