<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The "who's online" page.
// <http://vesuvius.local/gw/index.php?action=who>
function template_main() {
  return render_template('pages/general/who_is_online.twig');
}

// The credits page listing the people who wrote SMF.
// <http://vesuvius.local/gw/index.php?action=credits>
function template_credits() {
  return render_template('pages/article/credits.twig');
}
