<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// List of threads in a subforum.
// <http://vesuvius.local/gw/index.php?board=8.0>
function template_main() {
  return render_template('pages/objects/threads_index.twig', [
    'boards' => collect_forum_boards(),
  ]);
}
