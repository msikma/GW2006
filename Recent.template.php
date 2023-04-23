<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The recent posts page.
// <http://vesuvius.local/gw/index.php?action=recent>
function template_main() {
	return render_template('pages/objects/generic_posts.twig', ['page_index_context' => get_page_index_context($modSettings['defaultMaxMessages']), 'per_page' => 10]);
}

// The unread posts page.
// <http://vesuvius.local/gw/index.php?action=unread>
// <http://vesuvius.local/gw/index.php?action=unread;all;start=0>
function template_unread() {
	return render_template('pages/general/recent_unread.twig', ['action' => 'unread', 'segments' => get_known_segments_list(['all'])]);
}

// The unread replies page.
// <http://vesuvius.local/gw/index.php?action=unreadreplies>
// <http://vesuvius.local/gw/index.php?action=unreadreplies;all;start=0>
function template_replies() {
	return render_template('pages/general/recent_unread.twig', ['action' => 'unreadreplies', 'segments' => get_known_segments_list(['all'])]);
}
