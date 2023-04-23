<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The main template for the post page.
function template_main() {
	return render_template('pages/posts/form_new_post.twig');
}

// Allows a post to be announced to the entire forum, to each member individually.
// <http://vesuvius.local/gw/index.php?action=announce;sa=selectgroup;topic=1234>
function template_announce() {
	return render_template('pages/posts/form_announce.twig');
}

// Template displaying the announcement progress.
// <http://vesuvius.local/gw/index.php?action=announce;sa=send>
function template_announcement_send() {
	return render_template('pages/posts/form_announce.twig', ['in_progress' => true]);
}

// The template for the spellchecker.
// Not implemented (and not linked to) since browsers have this built in now.
// <http://vesuvius.local/gw/index.php?action=spellcheck>
function template_spellcheck() {
	return render_template('pages/error_404.twig');
}

// Template that retrieves a post's quote and injects it into the quick reply box.
// Completely useless now that we can just do it with JS.
// <http://vesuvius.local/gw/index.php?action=quotefast>
function template_quotefast() {
	return render_template('pages/error_404.twig');
}
