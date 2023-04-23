<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// Generic list template used to display various data pages.
// <http://vesuvius.local/gw/index.php?action=profile;area=tracking;sa=activity;u=1>
function template_show_list($list_id = null) {
  return render_template('pages/objects/generic_list.twig', ['list_context' => get_list_context($list_id)]);
}
