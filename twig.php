<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use Twig\TwigFunction;
use Twig\TwigFilter;

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('vendor/autoload.php');
require_once('lib/context.php');
require_once('lib/mime.php');
require_once('lib/icon.php');
require_once('lib/pips.php');
require_once('lib/cache.php');
require_once('lib/posticons.php');
require_once('lib/emoticons.php');
require_once('lib/db.php');
require_once('lib/git.php');
require_once('lib/custom_fields.php');
require_once('lib/stats.php');
require_once('lib/prng.php');

global $twig;

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
/** Formats an integer number value to use commas for easier reading. */
$twig->addFunction(new TwigFunction('comma_format', function($number, $override_decimal_count = false) {
  return comma_format($number, $override_decimal_count);
}));
/** Returns a CSS class to color a username with their group color. */
$twig->addFunction(new TwigFunction('get_username_pip_class', function($member_group, $member_id, $is_inline = false) {
  return get_username_pip_class($member_group, $member_id, $is_inline);
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
/** Returns an alert dialog box, either as a string or as an attribute. */
$twig->addFunction(new TwigFunction('js_alert', function($message, $is_attr = false) {
  return $is_attr ? get_dialog_attr($message, 'alert') : get_dialog($message, 'alert') ;
}));
/** Returns a confirm dialog box, either as a string or as an attribute. */
$twig->addFunction(new TwigFunction('js_confirm', function($message, $is_attr = false) {
  return $is_attr ? get_dialog_attr($message, 'confirm') : get_dialog($message, 'confirm') ;
}));
/** Merges arrays while preserving numeric indices. */
$twig->addFilter(new TwigFilter('pmerge', function($base, $extension) {
  foreach ($extension as $key => $value) {
    $base[$key] = $value;
  }
  return $base;
}));

/**
 * Renders a given Twig template using all our standard passed in variables.
 * 
 * This will load a bunch of custom data, such as our emoticons and posticons,
 * as well as the SMF settings and context, and pass it on to the template.
 * 
 * The template is printed directly to output and no data is returned.
 * 
 * An additional $template_context array can be passed on to include in the context.
 */
function render_template($file, $template_context = []) {
  global $twig, $context, $settings, $options, $scripturl, $txt, $modSettings, $forum_copyright, $forum_version;
  
  // Assemble our custom data.
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
  $context['env'] = get_env_context();

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
    'topics_per_page' => $context['topics_per_page'],
    'messages_per_page' => $context['messages_per_page'],
  ];

  // Pass on all data to Twig and render the requested template.
  // Note that all $settings values are exported directly, not in an array.
  print($twig->render($file, array_merge($settings, [
    'context' => array_merge($context, $template_context),
    'forum_copyright' => sprintf($forum_copyright, $forum_version),
    'forum_version' => $forum_version,
    'modSettings' => $modSettings,
    'options' => $options,
    'scripturl' => $scripturl,
    'txt' => $txt,
  ])));
}
