<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// The main search form.
// <http://vesuvius.local/gw/index.php?action=search>
function template_main() {
  return render_template('pages/search/search_form.twig');
}

// The search results.
// <http://vesuvius.local/gw/index.php?action=search2>
function template_results() {
  return render_template('pages/search/search_results.twig', ['posts' => collect_topics()]);
}
