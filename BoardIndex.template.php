<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// The list of forum categories and boards.
// <http://vesuvius.local/gw/index.php>
function template_main() {
  return render_template('pages/objects/forums_index.twig', [
    'calendar_birthdays' => get_birthdays_with_member_groups(),
    'categories' => collect_forum_categories(),
  ]);
}
