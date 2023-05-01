<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/events.php');
require_once('lib/html.php');
require_once('lib/util.php');

/**
 * Collects all posts on a page.
 */
function collect_posts() {
  return collect_messages('get_message');
}

/**
 * Collects all topics on a page.
 */
function collect_topics() {
  return collect_messages('get_topics');
}

/**
 * Collects all private messages on a page.
 */
function collect_pms() {
  return collect_messages('get_pmessage');
}

/**
 * Returns the theme directory path.
 */
function get_theme_dir() {
  global $settings;

  return $settings['theme_dir'];
}

/**
 * Returns the search cache.
 * 
 * This is stored as session data and can be used to construct the page number links.
 */
function get_search_cache() {
  return array_merge($_SESSION['search_cache'] ?? [], ['start' => intval($_GET['start'])]);
}

/**
 * Returns the buttons we need to display in the top menu.
 */
function get_menu_buttons() {
  global $context;

  // Use the SMF provided menu buttons by default.
  $menu_buttons = $context['menu_buttons'];

  // Remove unwanted items.
  unset($menu_buttons['calendar']);

  return $menu_buttons;
}

/**
 * Returns the current area's settings fields and groups them together.
 */
function get_current_settings_group() {
  global $context;

  // Check what area we're in.
  $area = $context['menu_data_1']['current_area'];

  return get_settings_groups($area);
}

/**
 * Retrieves settings fields and groups them together.
 * 
 * This is pretty hacky.
 */
function get_settings_groups($type) {
  // Retrieve all settings fields and custom fields.
  $settings = get_settings_fields();
  
  if ($type === 'forumprofile') {
    $groups = group_profile_settings($settings);
  }
  else if ($type === 'account') {
    $groups = group_account_settings($settings);
  }
  else if ($type === 'theme') {
    $groups = group_theme_settings($settings);
  }
  else if ($type === 'pmprefs') {
    $groups = group_pmprefs_settings($settings);
  }
  else {
    // If our type isn't one of the above ones, we don't know what page this is.
    // Group the fields by "hr" fields as a fallback.
    $groups = group_by_hr($profile_fields);
  }
  
  // Filter out empty groups.
  $groups = filter_settings_groups($groups, true);

  return [
    'settings_type' => $type,
    'groups' => $groups
  ];
}

/**
 * Groups settings fields together for the profile page.
 */
function group_profile_settings($fields) {
  $groups = [
    'user_core' => ['name' => 'Personal settings'],
    'social_media' => ['name' => 'Social media'],
    'personal_text' => ['name' => 'Personal text'],
    '_rest' => [],
  ];
  foreach ($groups as $key => $group) {
    $groups[$key]['slug'] = $key;
    $groups[$key]['items'] = [];
  }
  foreach ($fields as $field) {
    $group_name = '_rest';
    if (in_array($field['_slug'], ['personalized_picture', 'personal_text', 'birthdate', 'location', 'gender'])) {
      $group_name = 'user_core';
    }
    if (in_array($field['_slug'], ['icq', 'aim', 'msn', 'yim', 'website_title', 'website_url']) || $field['_custom_field_type'] === 'social_media') {
      $group_name = 'social_media';
    }
    if (in_array($field['_slug'], ['custom_title', 'signature'])) {
      $group_name = 'personal_text';
    }
    $groups[$group_name]['items'][] = $field;
  }
  // Sort the social media items so the custom fields go first.
  uasort($groups['social_media']['items'], function ($a, $b) {
    if ($a['_is_custom_field'] && !$b['_is_custom_field']) {
      return -1;
    }
    if ($b['_is_custom_field'] && !$a['_is_custom_field']) {
      return 1;
    }
    return 0;
  });
  return $groups;
}

/**
 * Groups settings fields together for the profile page.
 */
function group_account_settings($fields) {
  $groups = [
    'user_settings' => ['name' => 'User settings'],
    '_rest' => [],
    'security' => ['name' => 'Security'],
  ];
  foreach ($groups as $key => $group) {
    $groups[$key]['slug'] = $key;
    $groups[$key]['items'] = [];
  }
  foreach ($fields as $field) {
    $group_name = '_rest';
    if (in_array($field['_slug'], ['username', 'name', 'date_registered', 'posts', 'primary_membergroup', 'additional_membergroups', 'email', 'allow_users_to_email_me', 'show_others_my_online_status'])) {
      $group_name = 'user_settings';
    }
    if (in_array($field['_slug'], ['choose_password', 'verify_password', 'secret_question', 'answer'])) {
      $group_name = 'security';
    }
    $groups[$group_name]['items'][] = $field;
  }
  return $groups;
}

/**
 * Groups settings fields together for the theme (look and feel) page.
 */
function group_theme_settings($fields) {
  $groups = [
    'theme' => ['name' => 'Theme settings'],
    'time' => ['name' => 'Time settings'],
    '_rest' => [],
  ];
  foreach ($groups as $key => $group) {
    $groups[$key]['slug'] = $key;
    $groups[$key]['items'] = [];
  }
  foreach ($fields as $field) {
    $group_name = '_rest';
    if (in_array($field['_slug'], ['current_theme', 'theme_settings'])) {
      $group_name = 'theme';
    }
    if (in_array($field['_slug'], ['time_format', 'time_offset'])) {
      $group_name = 'time';
    }
    $groups[$group_name]['items'][] = $field;
  }
  return $groups;
}

/**
 * Groups settings fields together for the PM preferences page.
 */
function group_pmprefs_settings($fields) {
  $groups = [
    'pm_settings' => [],
    '_rest' => [],
  ];
  foreach ($groups as $key => $group) {
    $groups[$key]['slug'] = $key;
    $groups[$key]['items'] = [];
  }
  foreach ($fields as $field) {
    $group_name = '_rest';
    if (in_array($field['_slug'], ['pm_settings'])) {
      $group_name = 'pm_settings';
    }
    $groups[$group_name]['items'][] = $field;
  }
  return $groups;
}

/**
 * Filters out some unwanted values from the groups.
 */
function filter_settings_groups($groups, $keep_rest = false) {
  foreach ($groups as $group_key => $group) {
    foreach ($group['items'] as $key => $field) {
      // Filter out all "hr" groups that are normally used to separate groups.
      if ($field['type'] === 'hr') {
        unset($groups[$group_key]['items'][$key]);
      }
    }
    if (count($group['items']) === 0) {
      unset($groups[$group_key]);
    }
  }
  // Normally we discard all unwanted items.
  if (!$keep_rest) {
    unset($groups['_rest']);
  }
  return $groups;
}

/**
 * Groups a number of profile fields by the existence of "hr" groups.
 * 
 * Only used as fallback.
 */
function group_by_hr($fields) {
  $groups = [];
  
  $group = [];
  foreach ($fields as $field) {
    if ($field['_is_custom_field']) {
      // Skip all custom fields in the fallback.
      continue;
    }
    if ($field['type'] === 'hr') {
      // Ignore if there's multiple hr items in a row.
      if (count($group) === 0) {
        continue;
      }
      $groups[] = ['items' => $group];
      $group = [];
    }
    else {
      $group[] = $field;
    }
  }
  if (count($group) !== 0) {
    $groups[] = ['items' => $group];
  }

  // Add a temporary/fallback label.
  for ($n = 0; $n < count($groups); ++$n) {
    $placeholder_label = 'Group '.($n + 1);
    $groups[$n]['label'] = $placeholder_label;
  }

  return $groups;
}

/**
 * Retrieves profile fields and expands callbacks.
 * 
 * This also injects our own custom social media fields.
 */
function get_settings_fields() {
  global $context;
  
  $profile_fields = get_expanded_profile_fields();
  $custom_fields = get_expanded_custom_fields();

  return array_merge($profile_fields, $custom_fields);
}

/**
 * Returns the visual verification form data (captcha) for the registration form.
 */
function get_visual_verification() {
  global $context;
  ob_start();
  template_control_verification($context['visual_verification_id'], 'all');
  $content = ob_get_clean();
  return $content;
}

/**
 * Retrieves profile fields and expands callbacks.
 * 
 * This prepares the data so it can be easily consumed in a Twig template.
 * 
 * Used in preparation for get_profile_fields().
 */
function get_expanded_profile_fields($extract_profile_field_data = true) {
  global $context, $txt;

  $profile_fields = [];

  foreach ($context['profile_fields'] as $key => $field) {
    $_field = $field;
    $extracted_data = [];
    if ($field['type'] == 'callback') {
      // Run the callback and steal the generated HTML.
      if (isset($field['callback_func']) && function_exists('template_profile_' . $field['callback_func'])) {
        $callback_func = 'template_profile_' . $field['callback_func'];
        ob_start();
        $callback_func();
        $content = ob_get_clean();
        $_field['_callback_result'] = $content;
      }
      // Now that we have the field's HTML, split it up into a label and value.
      if (isset($_field['_callback_result']) && $extract_profile_field_data) {
        $extracted_data = extract_profile_field_data($_field['_callback_result']);
      }
    }

    if ($field['type'] === 'select') {
      // If 'options' is not an array, assume it's a function and run it.
      if (!is_array($field['options'])) {
        $_field['options'] = eval($field['options']);
      }
    }

    if (!count($extracted_data)) {
      $profile_fields[$key] = $_field;
    }
    else {
      // If we extracted multiple fields from a callback, add multiple profile fields.
      for ($n = 0; $n < count($extracted_data); ++$n) {
        $data = $extracted_data[$n];
        $sub_key = $n === 0 ? $key : $key.'_'.($n + 1);
        $sub_field = $_field;
        $sub_field['label'] = $data['label'];
        $sub_field['subtext'] = $data['subtext'];
        $sub_field['content'] = $data['content'];
        $profile_fields[$sub_key] = $sub_field;
      }
    }
  }

  // Add a _slug value for identification.
  // Note: custom fields get given one in convert_custom_to_profile_field().
  foreach ($profile_fields as $key => $field) {
    $label_field = $field['label'];
    if (empty($label_field)) $label_field = $field['label'];
    if (empty($label_field)) $label_field = $field['callback_func'];
    $profile_fields[$key] = array_merge($field, ['_slug' => slug($label_field)]);
  }

  return $profile_fields;
}

/**
 * Extracts information from the "page_index" value from the context.
 */
function get_page_index_context($per_page) {
  global $context;
  return extract_page_index_data($context['page_index'], $per_page);
}

/**
 * Returns the letter under which these members are filed in the member list.
 * 
 * E.g. if the last member's name starts with "e" or "E", this returns "e".
 * 
 * If the member's name doesn't start with a letter, "a" is returned.
 */
function get_start_letter($members) {
  if (empty($members)) {
    return 'a';
  }
  $letters = range('a', 'z');
  $last_member = end($members);
  $last_name = htmlspecialchars_decode($last_member['name']);
  $first_letter = strtolower($last_name[0]);
  
  if (in_array($first_letter, $letters)) {
    return $first_letter;
  }

  return 'a';
}

/**
 * Collects all posts or topics on a page.
 * 
 * We need to do this before loading the Twig template, as we can't
 * run the callback inside the Twig template code.
 */
function collect_messages($label) {
  global $context;

  $messages = [];
  $collector = $context[$label];
  while ($message = $collector()) {
    $messages[] = $message;
  }

  return $messages;
}

/**
 * Returns a list of segments if they appear in the URL.
 */
function get_known_segments_list($segments) {
  $query = $_SERVER['QUERY_STRING'];
  $known = [];
  foreach ($segments as $item) {
    preg_match_all("/{$item};/m", $query, $fields, PREG_SET_ORDER, 0);
    if (isset($fields[0])) {
      $known[] = $item;
    }
  }
  return $known;
}

/**
 * Retrieves context about the current member search page.
 * 
 * Since the "fields" value is not exposed in the $context variable, we have to fetch it from the URL.
 */
function get_member_search_context() {
  $query = $_SERVER['QUERY_STRING'];
  preg_match_all('/fields=(.*);/m', $query, $fields, PREG_SET_ORDER, 0);
  return ['fields' => !empty($fields[0]) ? $fields[0][1] : null];
}

/**
 * Retrieves tab context used in the admin panel.
 * 
 * This has mostly been copied from SMF code, with some small tweaks.
 */
function get_menu_context() {
  global $context;
  $menu_id = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
  $menu_context = &$context['menu_data_' . $menu_id];
  $tab_context = &$menu_context['tab_data'];
  $selected_tab = null;

  foreach ($context['tabs'] as $id => $tab) {
    if (!empty($tab['disabled'])) {
      $tab_context['tabs'][$id]['disabled'] = true;
      continue;
    }

    if (!isset($tab_context['tabs'][$id])) {
      $tab_context['tabs'][$id] = array('label' => $tab['label']);
    }
    elseif (!isset($tab_context['tabs'][$id]['label'])) {
      $tab_context['tabs'][$id]['label'] = $tab['label'];
    }

    // Has a custom URL defined in the main admin structure?
    if (isset($tab['url']) && !isset($tab_context['tabs'][$id]['url'])) {
      $tab_context['tabs'][$id]['url'] = $tab['url'];
    }
    // Any additional paramaters for the url?
    if (isset($tab['add_params']) && !isset($tab_context['tabs'][$id]['add_params'])) {
      $tab_context['tabs'][$id]['add_params'] = $tab['add_params'];
    }
    // Has it been deemed selected?
    if (!empty($tab['is_selected'])) {
      $tab_context['tabs'][$id]['is_selected'] = true;
    }
    // Does it have its own help?
    if (!empty($tab['help'])) {
      $tab_context['tabs'][$id]['help'] = $tab['help'];
    }
    // Is this the last one?
    if (!empty($tab['is_last']) && !isset($tab_context['override_last'])) {
      $tab_context['tabs'][$id]['is_last'] = true;
    }
  }

  // Find the selected tab
  foreach ($tab_context['tabs'] as $sa => $tab) {
    if (!empty($tab['is_selected']) || (isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa)) {
      $selected_tab = $tab;
      $tab_context['tabs'][$sa]['is_selected'] = true;
    }
  }

  return [
    'menu_id' => $menu_id,
    'menu_context' => $menu_context,
    'tab_context' => $tab_context,
    'selected_tab' => $selected_tab,
  ];
}

/**
 * Returns context for list pages.
 */
function get_list_context($list_id = null) {
  global $context;

  $list_id = $list_id === null ? $context['default_list'] : $list_id;

  return [
    'list_context' => $context[$list_id],
    'list_id' => $list_id
  ];
}

/**
 * Returns member birthdays with the member groups included.
 */
function get_birthdays_with_member_groups() {
  global $context;
  return add_birthday_member_groups($context['calendar_birthdays']);
}
