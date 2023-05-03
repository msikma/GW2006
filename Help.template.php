<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The main help page. Also contains the changelog.
// <http://vesuvius.local/gw/index.php?action=help>
// <http://vesuvius.local/gw/index.php?action=help;area=changelog>
function template_manual() {
  // Hook in the changelog page.
  if (is_changelog_page()) {
    return render_template('pages/article/changelog.twig');
  }
  return render_template('pages/article/help.twig');
}

// The content of a help popup window.
// <http://vesuvius.local/gw/index.php?action=helpadmin;help=securityDisable_why>
function template_popup() {
  return render_template('pages/popup/help.twig', ['_popup_title' => 'Help']);
}
