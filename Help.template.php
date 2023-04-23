<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The main help page.
// <http://vesuvius.local/gw/index.php?action=help>
function template_manual() {
  return render_template('pages/article/help.twig');
}

// The content of a help popup window.
// <http://vesuvius.local/gw/index.php?action=helpadmin;help=securityDisable_why>
function template_popup() {
  return render_template('pages/popup/help.twig', ['_popup_title' => 'Help']);
}
