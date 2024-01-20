<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $posticon_sets, $posticon_basepath;

// A reference to all our posticons. This gets populated on initialization.
$posticon_sets = null;
// The base path where all posticons are located, from the theme directory.
$posticon_basepath = '/images/posticons';

/**
 * Returns the base path where posticons can be found.
 */
function get_posticon_basepath() {
  global $posticon_basepath;
  return $posticon_basepath;
}

/**
 * Returns all our posticon sets.
 */
function get_posticons() {
  global $posticon_sets, $settings;

  $posticon_basepath = get_posticon_basepath();

  // Return cached posticons if we're already initialized.
  if ($posticon_sets !== null) {
    return $posticon_sets;
  }

  $sets = [];

  $posticon_url = $settings['theme_url'].$posticon_basepath;
  $posticon_path = $settings['theme_dir'].$posticon_basepath;
  $posticon_data = json_decode(file_get_contents("$posticon_path/info.json"), true);
  
  foreach ($posticon_data['sets'] as $set) {
    $sets[$set['name']] = [];
    $target = &$sets[$set['name']];

    foreach ($set['items'] as $posticon) {
      $path = '/'.(@$set['system'] ? 'system/' : '').$posticon['fn'];
      $target[] = [
        'value' => pathinfo($posticon['fn'], PATHINFO_FILENAME),
        'name' => $posticon['name'],
        'size' => $posticon['size'],
        'set' => $set['name'],
        'path' => $posticon_path.$path,
        'url' => $posticon_url.$path,
        'basename' => $posticon['fn'],
        'legacy' => $posticon['legacy'],
      ];
    }
  }

  $posticon_sets = $sets;
  
  return $sets;
}

/**
 * Finds a certain posticon and returns it.
 * 
 * If the posticon isn't found, it returns the "unknown" system post icon instead.
 * 
 * By default, all posts are set to the "xx" posticon, which is the "no icon" one.
 */
function find_posticon($value, $is_system = false, $test_icons = false) {
  $sets = get_posticons();

  if ($is_system) {
    return find_system_posticon("system_$value", $value);
  }

  foreach ($sets as $posticons) {
    foreach ($posticons as $icon) {
      if ($icon['value'] === $value || ($test_icons && rand(0, 30) === 0)) {
        return $icon;
      }
    }
  }
  return find_system_posticon('system_unknown', $value);
}

/**
 * Finds a system posticon.
 * 
 * These are posticons that cannot be selected when making a topic.
 */
function find_system_posticon($value, $original_value = null) {
  $sets = get_posticons();

  foreach ($sets['system'] as $icon) {
    if ($icon['value'] === $value) {
      // Mark the icon as being a system icon. We especially need to know if this is the "unknown" fallback.
      return array_merge(
        $icon,
        [
          'resolved_value' => $icon['value'],
          'is_system' => true,
          'is_unknown' => $value === 'system_unknown',
          'value' => $original_value,
        ]
      );
    }
  }
}
