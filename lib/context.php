<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('vendor/autoload.php');
require_once('lib/db.php');
require_once('lib/data.php');
require_once('lib/html.php');
require_once('lib/util.php');
require_once('lib/tasks.php');
require_once('lib/hooks.php');

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
 * Iterates through forum boards and returns the data with some adjustments.
 * 
 * As collect_forum_categories(), but for when viewing individual subforums.
 */
function collect_forum_boards($collect_permissions = true) {
  global $context;

  if (!$collect_permissions) {
    return $context['boards'];
  }
  
  $board_ids = [];

  foreach ($context['boards'] as $id => $board) {
    $board_ids[] = $id;
  }

  $board_data = get_board_member_groups($board_ids);

  foreach ($context['boards'] as $id => $board) {
    add_board_permissions_data($id, $board_data, $context['boards'][$id]);
  }

  return $context['boards'];
}

/**
 * Iterates through forum categories and returns the data with some adjustments.
 */
function collect_forum_categories($collect_permissions = true) {
  global $context;

  if (!$collect_permissions) {
    return $context['categories'];
  }
  
  $board_ids = [];

  foreach ($context['categories'] as $cat_id => $cat_data) {
    foreach ($cat_data['boards'] as $id => $board) {
      $board_ids[] = $id;
    }
  }

  $board_data = get_board_member_groups($board_ids);
  
  foreach ($context['categories'] as $cat_id => $cat_data) {
    foreach ($cat_data['boards'] as $id => $board) {
      add_board_permissions_data($id, $board_data, $context['categories'][$cat_id]['boards'][$id]);
    }
  }
  
  return $context['categories'];
}

/**
 * Adds additional member group permissions data to a board.
 * 
 * Used only by collect_forum_categories() and collect_forum_boards().
 */
function add_board_permissions_data($board_id, &$board_member_groups, &$board) {
  $data = $board_member_groups[$board_id];
  $hidden_to_guest = !in_array('guest', $data['member_groups']);
  $hidden_to_members = !in_array('regular_member', $data['member_groups']);
  $hidden_to_gmods = !in_array('global_moderator', $data['member_groups']);
  $hidden_to_all_but_admins = $hidden_to_guest && $hidden_to_members && $hidden_to_gmods;
  $hidden_to_some = $hidden_to_guest || $hidden_to_members || $hidden_to_gmods;

  $board['_permissions_profile'] = $data['profile'];
  $board['_member_groups'] = $data['member_groups'];
  $board['_permissions'] = [
    'non_default_profile' => $data['profile'] !== 'default',
    'only_admin' => $hidden_to_all_but_admins,
    'hidden_to_guest' => $hidden_to_guest,
    'hidden_to_members' => $hidden_to_members,
    'hidden_to_gmods' => $hidden_to_gmods,
    'hidden_to_some' => $hidden_to_some,
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

  // Fetch the locked status; this is used to determine if we display the topics
  // as "regular" locked topics, or as "locked due to old age".
  $topic_locked = get_topic_locked_status(array_keys($topics));
  foreach ($topic_locked as $id => $locked_status) {
    $topics[$id]['_locked'] = $locked_status;
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
  $is_dev_env = @$_ENV['MODE'] === 'DEVELOPMENT';
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
  return array_merge($_SESSION['search_cache'] ?? [], ['start' => intval(@$_GET['start'])]);
}

/**
 * Returns the linktree with custom changes applied.
 */
function get_linktree() {
  global $context, $scripturl;

  $linktree = $context['linktree'];

  // Replace the "help" page with "changelog".
  if (is_changelog_page()) {
    $linktree[1] = ['name' => 'Changelog', 'url' => $scripturl.'?action=help;area=changelog'];
  }
  
  return $linktree;
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
    // Clear out BB Code from the description.
    $description = strip_tags(parse_bbc($forum_description));
  }
  else if ($context['current_topic']) {
    // If we have a topic ID, use that to generate a description for this specific topic.
    $post_summary = get_post_summary($thread_id, $first_message_id, $message_id);
    $description = $post_summary['post_body_plain'];
    $description_unparsed = $post_summary['post_body_unparsed'];
    $image = $post_summary['first_image'];
    $icon = $post_summary['post_icon'];
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
    'description_unparsed' => $description_unparsed,
    'image' => $image,
    'domain' => $parsed_url['host'],
    'canonical_url' => $canonical_url,
    'icon' => $icon,
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
    'post_body_unparsed' => $post_data['body'],
    'post_icon' => $post_data['icon'],
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
    return implode('', [mb_substr($description, 0, $limit - strlen($static)), $static]);
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

  // Check what settings section we're viewing.
  $action = $context['menu_data_1']['current_action'];
  $area = $context['menu_data_1']['current_area'];

  return get_settings_groups($action, $area);
}

/**
 * Retrieves settings fields and groups them together.
 * 
 * This is pretty hacky.
 */
function get_settings_groups($action, $area) {
  // Retrieve all settings fields and custom fields.
  $settings = get_settings_fields();

  if ($action === 'profile') {
    if ($area === 'forumprofile') {
      $groups = group_profile_settings($settings);
    }
    else if ($area === 'account') {
      $groups = group_profile_account_settings($settings);
    }
    else if ($area === 'theme') {
      $groups = group_profile_theme_settings($settings);
    }
  }
  if ($action === 'pm') {
    if ($area === 'settings') {
      $groups = group_pm_settings($settings);
    }
  }
  
  if (empty($groups)) {
    // If our type isn't one of the above ones, we don't know what page this is.
    // Group the fields by "hr" fields as a fallback.
    $groups = group_by_hr($profile_fields);
  }
  
  // Filter out empty groups.
  $groups = filter_settings_groups($groups, true);

  return [
    'settings_action' => $action,
    'settings_area' => $area,
    'groups' => $groups
  ];
}

/**
 * Adds fields to a profile group.
 * 
 * This function is a bit complicated, but basically it's a selecting function.
 * It adds items by slug or by custom field type, and can add all unconsumed items to the _rest group.
 * 
 * Currently only used by group_profile_settings().
 */
function add_to_profile_group($fields_slug, $fields_custom_type, &$group, &$consumed, $items = [], $custom_match = null, $add_unused = false) {
  foreach ($items as $slug) {
    $field = @$fields_slug[$slug];
    if (isset($field) && !in_array($field['_slug'], $consumed)) {
      $group['items'][] = $field;
      $consumed[] = $slug;
    }
  }
  if (!empty($custom_match)) {
    $fields = @$fields_custom_type[$custom_match];
    if (!empty($fields)) {
      foreach ($fields as $field) {
        if (in_array($field['_slug'], $consumed)) {
          continue;
        }
        $group['items'][] = $field;
        $consumed[] = $field['_slug'];
      }
    }
  }
  if ($add_unused) {
    foreach ($fields_slug as $field) {
      if (!in_array($field['_slug'], $consumed)) {
        $group['items'][] = $field;
        if ($field['_slug']) {
          $consumed[] = $field['_slug'];
        }
      }
    }
  }
}

/**
 * Groups settings fields together for the profile page.
 */
function group_profile_settings($fields) {
  $consumed = [];
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
  $fields_slug = [];
  $fields_custom_type = [];
  foreach ($fields as $field) {
    $fields_slug[$field['_slug']] = $field;
    $fields_custom_type[$field['_custom_field_type']][] = $field;
  }
  
  add_to_profile_group($fields_slug, $fields_custom_type, $groups['user_core'], $consumed, ['avatar_choice', 'cust_avatar', 'cust_member', 'cust_gender', 'personal_text', 'bday1', 'location', 'gender'], 'profile_additional');
  add_to_profile_group($fields_slug, $fields_custom_type, $groups['social_media'], $consumed, ['icq', 'aim', 'msn', 'yim', 'website_title', 'website_url'], 'social_media');
  add_to_profile_group($fields_slug, $fields_custom_type, $groups['personal_text'], $consumed, ['usertitle', 'signature']);
  add_to_profile_group($fields_slug, $fields_custom_type, $groups['_rest'], $consumed, [], null, true);
  
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
 * Returns whether a given group ID is a premium member group.
 */
function is_premium_member_group_id($group_id, $include_mods = false) {
  $group_id = intval($group_id);
  if ($group_id === 0) return false; // Regular user
  if ($group_id === 1 && $include_mods) return true; // Administrator
  if ($group_id === 2 && $include_mods) return true; // Global Moderator
  if ($group_id === 3 && $include_mods) return true; // Moderator
  if ($group_id === 9) return true; // Premium Member
  // Not sure, but probably not premium.
  return false;
}

/**
 * Groups settings fields together for the profile page.
 */
function group_profile_account_settings($fields) {
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
    if (in_array($field['_slug'], ['member_name', 'real_name', 'date_registered', 'posts', 'id_group', 'id_group_2', 'email_address', 'hide_email', 'show_online'])) {
      $group_name = 'user_settings';
    }
    if (in_array($field['_slug'], ['passwrd1', 'passwrd2', 'secret_question', 'secret_answer'])) {
      $group_name = 'security';
    }
    $groups[$group_name]['items'][] = $field;
  }
  return $groups;
}

/**
 * Groups settings fields together for the theme (look and feel) page.
 */
function group_profile_theme_settings($fields) {
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
    if (in_array($field['_slug'], ['id_theme', 'theme_settings'])) {
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
function group_pm_settings($fields) {
  $groups = [
    'pm_settings' => ['name' => 'Private message settings'],
    '_rest' => [],
  ];
  foreach ($groups as $key => $group) {
    $groups[$key]['slug'] = $key;
    $groups[$key]['items'] = [];
  }
  foreach ($fields as $field) {
    $group_name = '_rest';
    if (in_array($field['_slug'], ['pm_prefs', 'pm_prefs_2', 'pm_prefs_3', 'pm_prefs_4', 'pm_prefs_5', 'pm_prefs_6', 'pm_prefs_7'])) {
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

  if (!function_exists('template_control_verification')) {
    return '';
  }

  // This works around a problem that arises only in our weird hacked in captcha.
  $verify_id = $context['visual_verification_id'];
  if (!isset($context['controls']['verification'][$verify_id]['questions'])) {
    $context['controls']['verification'][$verify_id]['questions'] = [];
  }

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
    $profile_fields[$key] = array_merge($field, ['_slug' => $key]);
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
 * Returns all ban groups with metadata.
 */
function get_ban_groups_metadata() {
  $ban_groups = get_ban_groups();

  foreach ($ban_groups as $id => $ban_group) {
    $special_group = null;
    if ($ban_group['name'] === 'Spam') {
      $special_group = 'spam';
    }
    $ban_groups[$id]['_special_group'] = $special_group;
  }

  return $ban_groups;
}

/**
 * Returns information about a member we're hoping to ban.
 */
function get_ban_suggestion_member_info($member_id = null) {
  $url_segments = get_smf_url_segments();
  $member_id = $member_id === null ? $url_segments['u'] : $member_id;
  if ($member_id == null) {
    return null;
  }
  $member_data = process_raw_member_data(get_member_data($member_id));
  return $member_data;
}

/**
 * Changes the raw member data from the database to be more like what SMF generates.
 * 
 * This allows us to do things such as display their profile preview.
 */
function process_raw_member_data($member_data) {
  global $scripturl;
  
  $member = [
    'id' => $member_data['id_member'],
    'name' => $member_data['real_name'],
    'email' => $member_data['email_address'],
    'show_email' => showEmailAddress(!empty($member_data['hide_email']), $member_data['id_member']),
    'ip' => $member_data['member_ip'],
    'registered_timestamp' => empty($member_data['date_registered']) ? 0 : forum_time(true, $member_data['date_registered']),
    'registered' => timeformat($member_data['date_registered']),
    'last_online' => $last_online,
    'posts' => comma_format($member_data['posts']),
    'is_activated' => $member_data['is_activated'] % 10 === 1,
  ];
  
  return array_merge($member_data, $member);
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
 * Parses and returns all URL segments of the current request.
 */
function get_smf_url_segments() {
  $query_string = $_SERVER['QUERY_STRING'].';';
  preg_match_all('/([^=;]+)(=([^=;]+))?(;|$)/m', $query_string, $matches);
  $segments = [];
  for ($n = 0; $n < count($matches); ++$n) {
    $key = $matches[1][$n];
    $value = !empty($matches[3][$n]) ? $matches[3][$n] : '';
    if (empty($key)) {
      continue;
    }
    $segments[$key] = $value === '' ? null : $value;
  }
  return $segments;
}

/**
 * Returns true if the currently requested page is the changelog page.
 */
function is_changelog_page() {
  $query_string = $_SERVER['QUERY_STRING'];
  return strpos($query_string, 'area=changelog') !== false;
}

/**
 * Returns true if we're currently requesting to install the theme prerequisites (tasks and hooks).
 */
function is_prerequisites_installation_request() {
  if (!req_is_admin()) {
    return false;
  }
  $query_string = $_SERVER['QUERY_STRING'];
  $is_remove = strpos($query_string, 'area=changelog;removeprerequisites') !== false;
  $is_install = strpos($query_string, 'area=changelog;installprerequisites') !== false;
  return [
    'value' => $is_remove || $is_install,
    'type' => $is_remove ? 'remove' : 'install',
  ];
}

/**
 * Performs any theme hooks and tasks installation that needs doing.
 */
function perform_theme_prerequisites_tasks() {
  $prerequisites = is_prerequisites_installation_request();

  if ($prerequisites['value']) {
    if ($prerequisites['type'] === 'remove') {
      remove_theme_prerequisites();
    }
    if ($prerequisites['type'] === 'install') {
      install_theme_prerequisites();
    }
  }
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
