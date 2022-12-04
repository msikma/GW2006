<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

function template_init() {
	global $settings;

	$settings['use_default_images'] = 'never';
	$settings['doctype'] = 'html';
	$settings['theme_version'] = '2.0';
	$settings['use_tabs'] = true;
	$settings['use_buttons'] = true;
	$settings['separate_sticky_lock'] = true;
	$settings['strict_doctype'] = false;
	$settings['message_index_preview'] = false;
	$settings['require_theme_strings'] = false;
}

function template_html_above() {
	return render_template('html_above.twig');
}

function template_body_above() {
	return render_template('body_above.twig');
}

function template_body_below() {
	return render_template('body_below.twig');
}

function template_html_below() {
	return render_template('html_below.twig');
}
