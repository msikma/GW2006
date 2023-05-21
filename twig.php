<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('vendor/autoload.php');
require_once('lib/context.php');
require_once('lib/mime.php');
require_once('lib/icon.php');
require_once('lib/pips.php');
require_once('lib/cache.php');
require_once('lib/twig.php');
require_once('lib/posticons.php');
require_once('lib/emoticons.php');
require_once('lib/db.php');
require_once('lib/git.php');
require_once('lib/custom_fields.php');
require_once('lib/stats.php');
require_once('lib/prng.php');

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
