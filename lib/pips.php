<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $pip_styles, $pip_mappings;

// All data pertaining to pip styles.
$pip_styles = [
  // Colors (and some other associated styles).
  'colors' => [
    'red' => [
      'colors' => ['#e5383f'],
    ],
    'orange' => [
      'colors' => ['#d26f1e', '#f07c1c'],
    ],
    'yellow' => [
      'colors' => ['#a49200', '#e9be00'],
    ],
    'green' => [
      'colors' => ['#3c9509', '#4fcd05'],
    ],
    'mint' => [
      'colors' => ['#118d38', '#10c148'],
    ],
    'cyan' => [
      'colors' => ['#278cd2', '#37adff'],
    ],
    'blue' => [
      'colors' => ['#4244ee', '#5557ff'],
    ],
    'purple' => [
      'colors' => ['#9e4aff'],
    ],
    'pink' => [
      'colors' => ['#e021a3'],
    ],
    'rose' => [
      'colors' => ['#e33e70'],
    ],
    'white' => [
      'colors' => ['#646c6c', '#b5c3c2'],
    ],
    'gray' => [
      'colors' => ['#000000', '#3d4443', '#7b8584'],
      'weight' => 'normal',
    ],
    'light_gray' => [
      'colors' => ['#3d4443', '#7b8584'],
      'weight' => 'normal',
    ],
    'gold' => [
      'colors' => ['#e89304', '#e07c12', '#f6b328'],
    ],
    'warmgold' => [
      'colors' => ['#e89304', '#e07c12', '#f6b328'],
    ],
  ],
  // Special styles that override the regular behavior of pips.
  'specials' => [
    'rainbow' => [
      'pip_array' => ['red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'pink'],
      'color' => 'red',
    ],
    'lgbt_flag' => [
      'pip_array' => ['red', 'orange', 'yellow', 'green', 'blue', 'purple'],
      'color' => 'pink',
    ],
    'transgender_flag' => [
      'pip_array' => ['cyan', 'cyan', 'rose', 'rose', 'white', 'white', 'rose', 'rose', 'cyan', 'cyan'],
      'color' => 'cyan',
    ],
    'prizes' => [
      'pip_array' => ['gold', 'silver', 'bronze'],
      'color' => 'gold',
    ],
  ],
  // The different base images that can be used, e.g. lego_gray, pip_red.png, etc.
  'types' => [
    'pip',
    'lego',
  ],
];

// The mappings that can be used to determine what pips a given member group or ID should get.
$pip_mappings = [
  // Maps specific member groups to a pip style.
  'member_group_map' => [
    'Guest' => [
      'default' => [
        'type' => 'pip',
        'color' => 'light_gray',
        // For simple inline lists, we don't apply styles to this group.
        'ignored_inline' => true,
      ],
    ],
    'Member' => [
      'default' => [
        'type' => 'pip',
        'color' => 'gray',
        // For simple inline lists, we don't apply styles to this group.
        'ignored_inline' => true,
      ],
    ],
    'Premium Member' => [
      'default' => [
        'type' => 'lego',
        'color' => 'gold',
      ],
      'pink' => [
        'type' => 'lego',
        'color' => 'pink',
      ],
      'mint' => [
        'type' => 'lego',
        'color' => 'mint',
      ],
    ],
    'Global Moderator' => [
      'default' => [
        'type' => 'lego',
        'color' => 'blue',
      ],
    ],
    'Moderator' => [
      'default' => [
        'type' => 'lego',
        'color' => 'green',
      ],
    ],
    'Administrator' => [
      'default' => [
        'type' => 'lego',
        'color' => 'red',
      ],
    ],
  ],
  // Maps specific member IDs to a pip style.
  'member_id_map' => [
    1 => [
      'default' => [
        'type' => 'lego',
        'color' => 'red',
        'special' => 'rainbow',
      ]
    ],
  ]
];

/**
 * Resolves the member group value.
 */
function resolve_member_group($group_name, $default = 'Member') {
  return $group_name ?: $default;
}

/**
 * Ignores some member groups (actually, only regular members and guests) for some operations.
 */
function is_ignored_member_group($member_group, $sub_selector = 'default') {
  global $pip_mappings;
  return $pip_mappings['member_group_map'][resolve_member_group($member_group)][$sub_selector]['ignored_inline'] ?: false;
}

/**
 * Returns the pip styles to use for a given member.
 */
function get_pip_style_data($member_group, $member_id, $premium_color = null) {
  global $pip_styles, $pip_mappings;

  // Pick the pip styles for this particular member ID/group.
  // The Premium Color (subselector) is chosen by the member in their profile options.
  // If a given subselector is unavailable, fall back to the default style.
  $sub_selector = $premium_color ? slug($premium_color) : 'default';
  $group_map = $pip_mappings['member_group_map'][$member_group][$sub_selector];
  $id_map = @$pip_mappings['member_id_map'][$member_id][$sub_selector];
  if (!isset($group_map)) {
    $group_map = $pip_mappings['member_group_map'][$member_group]['default'];
    @$id_map = $pip_mappings['member_id_map'][$member_id]['default'];
  }

  // Go with the ID styles if they exist, or group styles otherwise.
  $style_data = !empty($id_map) ? $id_map : $group_map;

  // Insert the special data if it's set.
  if (@$style_data['special']) {
    $style_data = array_merge($style_data, $pip_styles['specials'][$style_data['special']]);
  }

  return $style_data;
}

/**
 * Returns a class value to use for a username.
 */
function get_username_pip_class($member_group, $member_id, $premium_color = null, $is_inline = false) {
  $member_group = resolve_member_group($member_group);
  $member_id = intval($member_id);
  $style_data = get_pip_style_data($member_group, $member_id, $premium_color);

  // If we're ignoring this member group for 
  if ($is_inline && is_ignored_member_group($member_group)) {
    return '';
  }

  return _make_username_pip_class($style_data);
}

/**
 * Returns a class string that can be used to properly color a username.
 * 
 * This only returns classes for only the color, as only that applies to usernames.
 */
function _make_username_pip_class($style_data) {
  $items = [];
  foreach (['color'] as $key) {
    $items[] = 'pip_'.$key.'_'.$style_data[$key];
  }
  return implode(' ', $items);
}

/**
 * Returns a class string that denotes what pips are being displayed.
 */
function _make_pip_images_class($style_data, $amount = 1) {
  $items = [];
  foreach (['color', 'type', 'special'] as $key) {
    if (@$style_data[$key]) {
      $items[] = 'pip_'.$key.'_'.$style_data[$key];
    }
  }
  if ($amount && !@$style_data['special']) {
    $items[] = 'pip_amount_'.$amount;
  }
  return implode(' ', $items);
}

/**
 * Returns a series of pip images for a given user by their post count.
 */
function get_user_pip_images_from_posts($posts, $member_group, $member_id, $premium_color = null) {
  return _get_user_pip_images(_calculate_pip_amount(intval($posts)), $member_group, $member_id, $premium_color);
}

/**
 * Returns a series of pip images for a given user by their group stars.
 */
function get_user_pip_images_from_stars($stars_html, $member_group, $member_id, $premium_color = null) {
  return _get_user_pip_images(_extract_pip_amount($stars_html), $member_group, $member_id, $premium_color);
}

/**
 * Returns the base URL for all pip images.
 */
function get_pip_base_url() {
  global $settings;
  return $settings['images_url'].'/pips/';
}

/**
 * Returns a series of pip images for a given user.
 * 
 * Use get_user_pip_images_from_posts() or get_user_pip_images_from_stars().
 */
function _get_user_pip_images($amount, $member_group, $member_id, $premium_color = null) {
  $member_group = resolve_member_group($member_group);
  $member_id = intval($member_id);
  $style_data = get_pip_style_data($member_group, $member_id, $premium_color);
  
  return _create_pips($amount, $style_data);
}

/**
 * Returns a single pip <img> tag.
 */
function _create_pip_img($type, $color) {
  $base_url = get_pip_base_url();
  $image = $type.'_'.$color.'.png';
  return '<img src="'.$base_url.$image.'" alt="Pip" class="pixel" />';
}

/**
 * Returns a series of pip <img> tags.
 */
function _create_pips($amount, $style_data) {
  $class = _make_pip_images_class($style_data, $amount);
  $pips = [];

  if (@$style_data['pip_array']) {
    // If we're displaying a special set of pips (e.g. the rainbow pips), iterate over them.
    foreach ($style_data['pip_array'] as $array_item_color) {
      $pips[] = _create_pip_img($style_data['type'], $array_item_color);
    }
  }
  else {
    // Otherwise, display the desired amount in the given color.
    for ($n = 0; $n < $amount; ++$n) {
      $pips[] = _create_pip_img($style_data['type'], $style_data['color']);
    }
  }

  return '<span class="pips '.$class.'">'.implode('', $pips).'</span>';
}

/**
 * Calculates the base number of pips to display for a given number of posts.
 * 
 * This value is then clamped by _calculate_pip_amount().
 */
function _calculate_pip_base_level($posts) {
  // 1 pip per 100 posts, up to 400 posts.
  if ($posts < 400) {
    $count = $posts / 100;
  }
  // 1 pip per 200 posts, up to 1000 posts.
  else if ($posts < 1000) {
    $count = (($posts - 400) / 200) + 4;
  }
  // 1 pip per 1000 posts.
  else {
    $count = (($posts - 1000) / 1000) + 7;
  }

  return floor($count);
}

/**
 * Calculates the number of pips to display for a given number of posts.
 */
function _calculate_pip_amount($posts, $max = 10, $min = 0) {
  $count = _calculate_pip_base_level($posts);
  return min(max($count, $min), $max);
}

/**
 * Extracts the number of pips from a user's group stars.
 */
function _extract_pip_amount($stars_html, $member_group, $member_id) {
  // Highly experimental way to check the number of pips.
  return substr_count($stars_html, 'alt="*"');
}

/**
 * Returns a single CSS style for a given pip color.
 */
function get_pip_css_style($name, $color, $weight, $pseudoselector, $element = '') {
  $selector = $element.'.pip_color_'.$name.$pseudoselector;
  $styles = [];
  if ($color) {
    $styles[] = 'color: '.$color.';';
  }
  if ($weight) {
    $styles[] = 'font-weight: '.$weight.';';
  }
  $styles = implode("\n", $styles);
  return $selector." {\n".$styles."\n}";
}

/**
 * Returns all pip CSS styles.
 */
function get_all_pip_css_styles() {
  global $pip_styles;

  $styles = [];
  foreach ($pip_styles['colors'] as $name => $data) {
    $styles[] = get_pip_css_style($name, $data['colors'][0], $data['weight'], '');
    if ($data['colors'][1]) {
      $styles[] = get_pip_css_style($name, $data['colors'][1], $data['weight'], ':hover', 'a');
    }
  }
  return implode("\n", $styles);
}
