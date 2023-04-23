<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// Displays a sortable listing of all members registered on the forum.
// <http://vesuvius.local/gw/index.php?action=mlist>
function template_main() {
	global $modSettings, $context;
  return render_template('pages/memberlist/member_list.twig', [
		'page_index_context' => get_page_index_context($modSettings['defaultMaxMembers']),
		'start_letter' => get_start_letter($context['members']),
		'member_search_context' => get_member_search_context()
	]);
}

// A page allowing people to search the member list.
// <http://vesuvius.local/gw/index.php?action=mlist;sa=search>
function template_search() {
  return render_template('pages/memberlist/member_search.twig');
}
