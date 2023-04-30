<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_generic_menu_dropdown_above() {
  return render_template('pages/admin/menu.twig', ['menu_context' => get_menu_context()]);
}

// Ends the content div for the admin page.
function template_generic_menu_dropdown_below() {
  // Nothing here.
}
