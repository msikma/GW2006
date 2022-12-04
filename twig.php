<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('vendor/autoload.php');

global $twig;
$loader = new \Twig\Loader\FilesystemLoader("{$settings['theme_dir']}/templates");
$twig = new \Twig\Environment($loader, [
  //'cache' => "{$settings['theme_dir']}/cache",
	'cache' => false,
]);

function render_template($file) {
	global $twig, $context, $settings, $options, $scripturl, $txt, $modSettings, $forum_copyright, $forum_version;
	print($twig->render($file, array_merge($settings, [
		'context' => $context,
		'forum_copyright' => sprintf($forum_copyright, $forum_version),
		'modSettings' => $modSettings,
		'options' => $options,
		'scripturl' => $scripturl,
		'txt' => $txt,
	])));
}
