<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('vendor/autoload.php');
require_once('lib/db.php');
require_once('lib/data.php');
require_once('lib/html.php');
require_once('lib/util.php');

// Load .env data if available.
$dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__.'/..'));
$dotenv->safeLoad();

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
  $subjects = collect_messages('get_pmessage', ['subject'], true);
  $messages = collect_messages('get_pmessage', ['message']);
  $has_labels = check_message_labels($subjects);
  return [
    'subjects' => $subjects,
    'messages' => $messages,
    'has_labels' => $has_labels,
  ];
}

/**
 * Returns true if any private message in the array has one or more labels set.
 * 
 * By default all private messages have the "inbox" label, so we check for 2 or more.
 */
function check_message_labels($messages) {
  foreach ($messages as $message) {
    if (!empty($message['labels']) && count($message['labels']) > 1) {
      return true;
    }
  }
  return false;
}

/**
 * Takes the existing topics array in the context and modifies it slightly.
 * 
 * This is used to display direct links to moved topics.
 */
function get_decorated_topics() {
  global $context;

  // If we're not on a page that has topics, do nothing.
  if (empty($context['topics'])) {
    return $context['topics'];
  }

  $topics = [];
  foreach ($context['topics'] as $id => $data) {
    // If a topic's icon is set to 'moved', it means it's a redirect post to somewhere else.
    // We need to retrieve the post data in order to know where to link to.
    $is_move_redirect = $data['icon'] === 'moved';
    if ($is_move_redirect) {
      $data = add_topic_redirect_link($data);
    }
    $topics[$id] = $data;
  }
  return $topics;
}

/**
 * Adds data to a redirect topic that allows us to directly link users there from the thread index.
 */
function add_topic_redirect_link($topic) {
  $first_post = get_post_data($topic['first_post']['id']);
  $redirect = get_post_redirect_target($first_post);
  if (empty($redirect['not_found']) && (empty($first_post) || empty($redirect['topic_link']))) {
    return $topic;
  }
  return array_merge($topic, [
    '_is_redirect_topic' => true,
    '_redirects' => $redirect,
  ]);
}

/**
 * Returns the theme directory path.
 */
function get_theme_dir() {
  global $settings;

  return $settings['theme_dir'];
}

/**
 * Returns whether we're in development or in production mode.
 */
function get_env_mode() {
  $addr = $_SERVER['SERVER_ADDR'];
  $is_dev_addr = $addr === '127.0.0.1' || str_starts_with($addr, '10.0.1') || str_starts_with($addr, '192.168.0');
  parse_str($_SERVER['QUERY_STRING'], $query);
  $is_dev_env = $_ENV['MODE'] === 'DEVELOPMENT';
  $is_no_cache = isset($query['nocache']);
  return ($is_dev_env || $is_dev_addr || $is_no_cache) ? 'development' : 'production';
}

/**
 * Returns browser environment context.
 */
function get_env_context() {
  global $context;

  $ua = $_SERVER['HTTP_USER_AGENT'];
  $is_legacy_browser = ua_has_legacy_elements($ua) || $context['browser']['is_ie'];
  
  return [
    'is_legacy_browser' => $is_legacy_browser,
    'mode' => get_env_mode()
  ];
}

/**
 * Returns true if a given user agent string has elements indicating it's a legacy browser.
 * 
 * Example legacy user agent strings:
 * 
 *   Mozilla/5.0 (Windows; U; Windows 98; en-US; rv:1.8.1.24) Gecko/20210302 WinternightClassic/0.1.0
 *   Mozilla/5.0 (Windows; U; Win98; en-US; rv:1.8.1.24) Gecko/20100228 SeaMonkey/1.1.19
 *   Mozilla/4.0 (compatible; MSIE 5.0; Windows 98; DigExt)
 */
function ua_has_legacy_elements($ua) {
  $legacy = [
    'WinternightClassic',
    'SeaMonkey',
    'Mozilla/4.0',
    'Windows 98',
    'Windows 95',
    'Win98',
    'Win95',
  ];
  foreach ($legacy as $str) {
    if (strpos($ua, $str) !== false) {
      return true;
    }
  }
  return false;
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
 * Returns metadata for the current page.
 * 
 * This is used to display Open Graph information.
 * 
 * There are two different situations that this function accounts for:
 * it either returns metadata for a specific page on the site, such as a single topic
 * or a subforum, or it returns generic metadata that's not specific to any single page.
 * 
 * This should run only once, while generating the html_above template,
 * as that's the only place where the metadata can get used.
 */
function get_page_metadata($template_context) {
  global $context, $settings, $smcFunc;

  if (!$template_context['is_html_above']) {
    return null;
  }

  $description = null;
  $image = null;

  list($title, $title_full, $is_generic) = get_html_page_title();

  // If this value is set, it means we're viewing the thread index of a forum.
  $forum_description = $context['description'];

  // The site slogan; this is used when displaying a generic description.
  $site_description = $settings['site_slogan'];

  // The currently active post ID, if any.
  list($thread_id, $first_message_id, $message_id) = get_current_post_ids();

  if ($forum_description) {
    $description = $forum_description;
  }
  else if ($context['current_topic']) {
    // If we have a topic ID, use that to generate a description for this specific topic.
    $post_summary = get_post_summary($thread_id, $first_message_id, $message_id);
    $description = $post_summary['post_body_plain'];
    $image = $post_summary['first_image'];
  }
  else {
    // Fallback if all else fails.
    $description = $site_description;
  }

  $canonical_url = $context['canonical_url'];
  $parsed_url = parse_url($canonical_url);

  return [
    'title' => $title,
    'title_full' => $title_full,
    'description' => limit_meta_string($description, 160),
    'description_full' => $description,
    'image' => $image,
    'domain' => $parsed_url['host'],
    'canonical_url' => $canonical_url,
  ];
}

/**
 * Returns IDs for the current thread, its first message, and the current message.
 */
function get_current_post_ids() {
  global $context, $_SERVER;
  $thread_id = $context['current_topic'];
  if (empty($thread_id)) {
    return [null, null, null];
  }
  
  $first_message_id = $context['topic_first_message'] ? intval($context['topic_first_message']) : null;

  $uri = $_SERVER['REQUEST_URI'];
  preg_match('/\.msg([0-9]+)($|;|&)/m', $uri, $matches);
  if (empty($matches[1])) {
    return [$thread_id, $first_message_id, null];
  }
  $message_id = intval($matches[1]);
  return [$thread_id, $first_message_id, $message_id];
}

/**
 * Returns a summary of a given post.
 * 
 * This is used to create Open Graph tags.
 */
function get_post_summary($thread_id, $first_message_id, $message_id) {
  $target_message_id = empty($message_id) ? $first_message_id : $message_id;
  $post_data = get_post_data($target_message_id);

  $poster_name = $post_data['modified_name'] ?? $post_data['poster_name'];
  $post_body = parse_bbc($post_data['body'], 0, $target_message_id);
  $first_img = find_first_image($post_body);
  $post_plain = strip_tags($post_body);
  
  return [
    'thread_id' => $thread_id,
    'message_id' => $target_message_id,
    'post_body' => $post_body,
    'first_image' => $first_img,
    'post_body_plain' => $post_plain,
  ];
}

/**
 * Returns the page title for use in the <title> header tag.
 */
function get_html_page_title($limit = 60) {
  global $context;

  $info = get_page_title_info();

  // Return just the forum name in a few cases.
  if ($info['is_index'] || $info['is_generic']) {
    return ["{$context['forum_name']}", "{$context['forum_name']}", true];
  }

  // Otherwise, add the forum name to the title.
  $title = $context['page_title_html_safe'];
  $forum = $context['forum_name_html_safe'];
  
  // Substract the length of the static part of the title.
  $separator = ' - ';
  $static_len = strlen($forum) + strlen($separator);

  $items = [limit_meta_string($title, $limit - $static_len), $forum];
  $items_full = [$title, $forum];
  
  return [implode(' - ', $items), implode(' - ', $items_full), false];
}

/**
 * Gracefully limits a string to a specific length.
 */
function limit_meta_string($description, $limit) {
  $static = '..';
  if (strlen($description) > $limit) {
    return implode('', [substr($description, 0, $limit - strlen($static)), $static]);
  }
  return $description;
}

/**
 * Returns information about the current page's title.
 * 
 * This is required to know how exactly to modify the title in get_html_page_title().
 */
function get_page_title_info() {
  global $context, $txt;
  $index = sprintf($txt['forum_index'], $context['forum_name']);
  $is_index = $context['page_title_html_safe'] === $index;
  $is_generic = $context['page_title_html_safe'] === $context['forum_name'];
  return ['is_index' => $is_index, 'is_generic' => $is_generic];
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
function collect_messages($label, $args = [], $skip_reset = false) {
  global $context;

  $messages = [];
  $collector = $context[$label];
  if ($skip_reset || call_user_func_array($collector, array_merge($args, [true]))) {
    while ($message = call_user_func_array($collector, $args)) {
      $messages[] = $message;
    }
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

/**
 * Returns true if the current request is by an admin.
 */
function req_is_admin() {
  global $context;
  return $context['user']['is_admin'] === true;
}

/**
 * Returns true if the currently requested page is the changelog page.
 */
function is_changelog_page() {
  $query_string = $_SERVER['QUERY_STRING'];
  return strpos($query_string, 'area=changelog') !== false;
}

/**
 * Returns true if we're currently requesting a refresh of the changelog page.
 */
function is_changelog_refresh_request() {
  // Only admins are permitted to request a refresh.
  if (!req_is_admin()) {
    return false;
  }
  $query_string = $_SERVER['QUERY_STRING'];
  return strpos($query_string, 'area=changelog;forcerefresh') !== false;
}

/**
 * Removes the response prefix from a subject line, if it exists.
 */
function remove_response_prefix($subject) {
  global $context, $txt;
  $prefix = $txt['response_prefix'];
  $re = '/^'.preg_quote($prefix, '/').'/';
  return preg_replace($re, '', $subject, 1);
}

/**
 * Returns a URL from a query string array.
 */
function make_script_url($query = []) {
  global $scripturl;
  return $scripturl.(empty($query) ? '' : '?'.http_build_query($query));
}
