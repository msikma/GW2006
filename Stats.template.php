<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The forum stats page.
// <http://vesuvius.local/gw/index.php?action=stats>
// <http://vesuvius.local/gw/index.php?action=stats;collapse=202212#m202212>
function template_main() {
  return render_template('pages/general/forum_stats.twig');
}
