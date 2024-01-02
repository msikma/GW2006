<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use Twig\TwigFunction;
use Twig\TwigFilter;

require_once('lib/tasks.php');
require_once('lib/history.php');
require_once('lib/prerequisites.php');
require_once('vendor/autoload.php');

// Global Twig singleton.
global $twig;
global $generated_context;

// Sets up the Twig renderer. Use cache only if we're in production mode.
$loader = new Twig\Loader\FilesystemLoader("{$settings['theme_dir']}/templates");
$twig = new Twig\Environment($loader, [
  'cache' => get_env_mode() === 'production' ? "{$settings['theme_dir']}/cache" : false,
]);
// Add extension for rendering international dates.
$twig->addExtension(new Twig\Extra\Intl\IntlExtension());

/** Returns posticon image data by icon name. */
$twig->addFunction(new TwigFunction('find_posticon', function($icon_name, $is_system = false) {
  return find_posticon($icon_name, $is_system);
}));
/** Returns an icon that can be used to represent a specific filetype. */
$twig->addFunction(new TwigFunction('get_filetype_icon', function($filename) {
  return get_filetype_icon($filename);
}));
/** Returns a tabindex that increments by one each time. */
$twig->addFunction(new TwigFunction('tabindex', function() {
  if (!isset($GLOBALS['gw_tabindex'])) {
    $GLOBALS['gw_tabindex'] = 0;
  }
  $GLOBALS['gw_tabindex'] += 1;
  return $GLOBALS['gw_tabindex'];
}));
/** Formats an integer number value to use commas for easier reading. */
$twig->addFunction(new TwigFunction('comma_format', function($number, $override_decimal_count = false) {
  return comma_format($number, $override_decimal_count);
}));
/** Returns a CSS class to color a username with their group color. */
$twig->addFunction(new TwigFunction('get_username_pip_class', function($member_group, $member_id, $is_inline = false) {
  return get_username_pip_class($member_group, $member_id, $is_inline);
}));
/** Returns some data about the member's avatar. */
$twig->addFunction(new TwigFunction('get_avatar_options', function($member) {
  $custom = $member['options']['cust_avatar'];
  $options = [
    'is_retina' => $custom === 'Retina (high dpi)',
    'is_pixelart' => $custom === 'Pixel art (no smoothing)',
  ];
  return $options;
}));
/** Returns a set of pip image tags using the original SMF generated star icons. */
$twig->addFunction(new TwigFunction('get_user_pip_images_from_stars', function($stars_html, $member_group, $member_id) {
  return get_user_pip_images_from_stars($stars_html, $member_group, $member_id);
}));
/** Returns a set of pip image tags calculated from the user's post count. */
$twig->addFunction(new TwigFunction('get_user_pip_images_from_posts', function($posts, $member_group, $member_id) {
  return get_user_pip_images_from_posts($posts, $member_group, $member_id);
}));
/** Returns a letter from a number; used for the member list. */
$twig->addFunction(new TwigFunction('get_legacy_ipb_tags', function($post_body_unparsed) {
  $tags = [];
  preg_match_all('/\[legacy_ipb_tag\](.+?)\[\/legacy_ipb_tag\]/s', $post_body_unparsed, $matches);
  foreach ($matches[1] as $match) {
    $tags[] = ['value' => $match];
  }
  return $tags;
}));
/** Returns a letter from a number; used for the member list. */
$twig->addFunction(new TwigFunction('get_letter', function($n) {
  return chr(64 + $n);
}));
/** Returns an SVG pie chart for use on the user stats page. */
$twig->addFunction(new TwigFunction('get_pie_chart', function($percent) {
  return get_percentage_pie_chart($percent);
}));
/** Returns the highest vote percentage for an array of poll options. */
$twig->addFunction(new TwigFunction('get_max_poll_percentage', function($options) {
  return max(array_column($options, 'percent'));
}));
/** Returns a line chart indicating the user's relative activity by time of day. */
$twig->addFunction(new TwigFunction('get_user_activity_chart', function($user_posts_by_time) {
  return get_user_activity_chart($user_posts_by_time);
}));
/** Returns a clock emoji for the hour of the day. */
$twig->addFunction(new TwigFunction('get_clock_emoji', function($hour) {
  $clocks = array('🕛', '🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚');
  return $clocks[$hour % 12];
}));
/** Parses a string as BBC and returns HTML. */
$twig->addFunction(new TwigFunction('parse_bbc', function($str) {
  return parse_bbc($str);
}));
/** Removes the response prefix of a subject if it's there'. */
$twig->addFunction(new TwigFunction('remove_response_prefix', function($subject) {
  return remove_response_prefix($subject);
}));
/** Returns a pseudorandomly generated number for a given message label name. */
$twig->addFunction(new TwigFunction('get_label_color_n', function($name) {
  return getRandomIntBySeed($name, 1, 10);
}));
/** Returns either a link for a link-like string, or the string itself. */
$twig->addFunction(new TwigFunction('get_link_or_text', function($url) {
  if (filter_var($url, FILTER_VALIDATE_URL)) {
    $parsed = parse_url($url);
    $display = @$parsed['host'].@$parsed['path'];
    return '<a href="'.$url.'" rel="nofollow">'.$display.'</a>';
  }
  return $url;
}));
/** Returns an alert dialog box, either as a string or as an attribute. */
$twig->addFunction(new TwigFunction('js_alert', function($message, $is_attr = false) {
  return $is_attr ? get_dialog_attr($message, 'alert') : get_dialog($message, 'alert') ;
}));
/** Returns a confirm dialog box, either as a string or as an attribute. */
$twig->addFunction(new TwigFunction('js_confirm', function($message, $is_attr = false) {
  return $is_attr ? get_dialog_attr($message, 'confirm') : get_dialog($message, 'confirm') ;
}));
/** Returns an indicator for what era a post came from by its post date. */
$twig->addFunction(new TwigFunction('get_gw_era', function($post_unixtime) {
  $era = get_gw_era_from_timestamp($post_unixtime);
  if (empty($era)) {
    return 'unknown';
  }
  return $era['slug'];
}));
/** Runs the PHP unserialize function on a variable. */
$twig->addFilter(new TwigFilter('unserialize', function($var) {
  return unserialize($var);
}));
/** Merges arrays while preserving numeric indices. */
$twig->addFilter(new TwigFilter('pmerge', function($base, $extension) {
  foreach ($extension as $key => $value) {
    $base[$key] = $value;
  }
  return $base;
}));

/**
 * Creates the Twig context array used for rendering templates.
 */
function get_render_context($template_context = []) {
  global $context, $settings, $generated_context;

  // We might call this function several times, so the generated context is cached.
  if (!empty($generated_context)) {
    return $generated_context;
  }

  // If an admin just requested the scheduled tasks and hooks to be installed, do so now.
  perform_theme_prerequisites_tasks();
  
  // Assemble our custom data.
  $context['linktree'] = get_linktree();
  $context['page_metadata'] = get_page_metadata($template_context);
  $context['topics'] = get_decorated_topics();
  $context['emoticons_base_url'] = get_emoticons_base_url();
  $context['emoticons_metadata'] = get_emoticons_metadata();
  $context['posticon_basepath'] = get_posticon_basepath();
  $context['posticon_context'] = find_posticon($context['icon']);
  $context['posticons'] = get_posticons();
  $context['git_info'] = get_git_info();
  $context['pip_styles'] = get_all_pip_css_styles();
  $context['gw_custom_fields'] = get_gw_metadata();
  $context['search_cache'] = get_search_cache();
  $context['menu_buttons'] = get_menu_buttons();
  $context['theme_prerequisites'] = get_theme_prerequisites_status();
  $context['env'] = get_env_context();
  $context['url_segments'] = get_smf_url_segments();

  // Use our custom pip count algorithm.
  $context['use_gw_pip_count'] = true;
  
  // Show that user IPs are being logged.
  $context['show_ip_logged'] = true;

  // Public theme data is needed to add our own emoticon functionality.
  $context['public_theme_data'] = [
    'emoticons_base_url' => $context['emoticons_base_url'],
    'emoticons_metadata' => $context['emoticons_metadata'],
  ];
  // The public context is exported to JS.
  $context['public_context'] = [
    'forum_name' => $context['forum_name_html_safe'],
    'theme_url' => $settings['theme_url'],
    'images_url' => $settings['images_url'],
    'character_set' => $context['character_set'],
    'topics_per_page' => $context['topics_per_page'],
    'messages_per_page' => $context['messages_per_page'],
  ];

  $generated_context = $context;

  return $context;
}
