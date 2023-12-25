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
require_once('lib/hooks.php');
require_once('lib/tasks.php');

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
  global $twig, $settings, $context, $options, $scripturl, $txt, $modSettings, $forum_copyright, $forum_version;

  // Generate the context used to render this template.
  // This does a bunch of preprocessing for everything we need to be able to render templates.
  $render_context = get_render_context($template_context);

  // Pass on all data to Twig and render the requested template.
  // Note that all $settings values are exported directly, not in an array.
  print($twig->render($file, array_merge($settings, [
    'context' => array_merge($context, $render_context, $template_context),
    'forum_copyright' => sprintf($forum_copyright, $forum_version),
    'forum_version' => $forum_version,
    'modSettings' => $modSettings,
    'options' => $options,
    'scripturl' => $scripturl,
    'txt' => $txt,
  ])));
}
