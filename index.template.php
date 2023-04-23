<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// Basic theme settings.
// Most of these don't really matter and don't do anything if you change them.
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

// The top part of the <html>.
function template_html_above() {
	return render_template('html_above.twig');
}

// The top part of the <body>.
function template_body_above() {
	return render_template('body_above.twig');
}

// The bottom part of the <body> and its closing tag.
function template_body_below() {
	return render_template('body_below.twig');
}

// The bottom part of the <html> and its closing tag.
function template_html_below() {
	return render_template('html_below.twig');
}
