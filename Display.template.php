<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// A thread page containing user posts.
// <http://vesuvius.local/gw/index.php?topic=1234.0>
function template_main() {
  return render_template('pages/objects/posts_index.twig', ['messages' => collect_posts()]);
}
