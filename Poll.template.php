<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

function template_main() {
  return render_template('pages/posts/form_edit_poll.twig');
}
