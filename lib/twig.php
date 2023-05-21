<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use Twig\TwigFunction;
use Twig\TwigFilter;

require_once('vendor/autoload.php');

// Global Twig singleton.
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
